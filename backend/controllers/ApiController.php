<?php

namespace backend\controllers;

use common\models\Alert;
use common\models\Camera;
use common\models\Log;
use common\models\User;
use common\models\Property;
use common\models\PropertyAccessLog;
use yii\helpers\Json;
use common\models\Alarm;
use common\models\LogStream;
use Yii;

class ApiController extends \yii\web\Controller {

    const API_ACTION_ALARM = 'alarm';
    const API_ACTION_ALERT = 'alert';

    /**
     * @inheritdocs
     */
    public function beforeAction($action) {
        \Yii::$app->controller->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    /**
     * Process camera alers request
     */
    public function actionCameraAlert() {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $dataJsonStr = \Yii::$app->getRequest()->getRawBody();
        $postDataJson = \Yii::$app->getRequest()->getRawBody();

        $postData = Json::decode($postDataJson);

        if ($postData) {
            try {
                $eventCode = isset($postData['EventInfo']['EventCode']) ? $postData['EventInfo']['EventCode'] : '';   //  'VideoMotion' - required event
                $channel = isset($postData['EventInfo']['Channel']) ? $postData['EventInfo']['Channel'] : '';
                $time = isset($postData['EventInfo']['Time']) ? $postData['EventInfo']['Time'] : '';
                $serial = isset($postData['EventInfo']['Serial']) ? $postData['EventInfo']['Serial'] : '';
                //$eventName = isset($postData['EventInfo']['data']['Event']) ? $postData['EventInfo']['data']['Event'] : '';
                //$fileName = isset($postData['EventInfo']['data']['File']) ? $postData['EventInfo']['data']['File'] : '';
                if (Camera::isAlertEvent($eventCode)) {
                    $camera = Camera::findOne(['serial_number' => $serial]);
                    if ($camera) {
                        $propertyCameraArr = [];
                        if ($camera->property) {
                            foreach ($camera->property->cameras as $curCamera) {
                                $propertyCameraArr[] = $curCamera->id;
                            }
                        } else {
                            $propertyCameraArr = [$camera->id];
                        }
                        //  Check for the active alarm for the same property
                        $getAlertQuery = Alert::getActive()->andWhere(['in', 'camera_id', $propertyCameraArr]);
                        $alertCount = $getAlertQuery->count();
                        if ($alertCount == 0) {
                            $alertId = Alert::add($serialNumber, $ip, Alert::EVENT_VIDEO_MOTION, Alert::STATUS_PENDING, null, $dataJsonStr);
                            if ($alertId && ($alertObj = Alert::findOne($alertId)))
                                $alertObj->assignWSAlertUsingMemcache();
                            //  Try to assign new alert to next agent
                            //  Send notification to the agent
                            //LogStream::sendMessage(LogStream::getWatchNotificationJson(, $alertId));
                        } else {
                            return ['result' => false, 'message' => 'Alert has been previously fired for this property'];
                        }
                    }
                } else {
                    //Alert::add($serialNumber, $ip, Alert::EVENT_VIDEO_MOTION, Alert::STATUS_BOUNCED);    //  Uncomment when decide what to do with other event types
                    return ['result' => false, 'message' => 'Given event type doen\'t fire an alert'];
                }
            } catch (\Exception $e) {
                return ['result' => false, 'message' => 'Unexpected error while parsing event info'];
            }
        }

        return ['result' => true];
    }

    /**
     * Validate post data, if data correct returns auth_key for the property
     * User::API_USER_LOGIN,
     * User::API_USER_PASSWORD
     * Property::API_NAME_ID
     * 
     * @return Json ['result' => boolean, Property::API_NAME_AUTH_KEY => string, 'message' => string]
     */
    public function actionGetAuthKey() {
        $propertyId = \Yii::$app->getRequest()->post(Property::API_NAME_ID, '');
        $userLogin = \Yii::$app->getRequest()->post(User::API_USER_LOGIN, '');
        $userPassword = \Yii::$app->getRequest()->post(User::API_USER_PASSWORD, '');

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $user = User::findByUsernameOrEmail($userLogin);
        if ($user && $user->validatePassword($userPassword)) {
            $property = Property::findOne($propertyId);
            if ($property) {
                $authKey = $property->generateAuthKey();
                if ($property->save()) {
                    Log::add(Log::LOG_API_PROPERTY_AUTH, '{"property_id":"' . $propertyId . '"}', $user->id);
                    return ['result' => true, Property::API_NAME_AUTH_KEY => $authKey, Property::API_NAME_PIN_CODE => $property->pin_code, 'message' => $property->getStatusText()];
                }
            }
            return ['result' => false, 'message' => 'Icorrect property data'];
        }
        return ['result' => false, 'message' => 'Incorrect user credentials'];
    }

    /**
     * Try to turn off control for the property, if code recently accepted
     * 
     * @return type
     */
    public function actionControlTurnOff() {
        $checkResult = false;
        $message = 'PIN expired. Try again';
        $authKey = \Yii::$app->getRequest()->post(Property::API_NAME_AUTH_KEY, '');
        $property = Property::findByAuthKey($authKey);
        if ($property) {
            $accessLog = PropertyAccessLog::find()
                    ->andWhere(['property_id' => $property->id])
                    ->andWhere(['check_result' => 1])
                    ->andWhere(['>', 'created_at', 'NOW() - ' . \Yii::$app->params['API_PIN_EXPIRE_SECONDS'] . ' SEC'])
                    ->orderBy(['id' => SORT_DESC])
                    ->one();
            if ($accessLog && $accessLog->check_result) {
                $property->security_status = Property::STATUS_CONTROL_SWITCHED_OFF;
                $property->save();
                Log::add(Log::LOG_API_PROPERTY_CONTROL_OFF, json_encode(['property_id' => $property->id, 'auth_key' => $authKey]));
                $checkResult = true;
                $message = 'Control disabled';
            }
        }
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return ['result' => $checkResult, 'message' => $message];
    }

    /**
     * Try to turn on control for the given property
     * 
     * @return type
     */
    public function actionControlTurnOn() {
        $checkResult = false;
        $message = 'Unexpected error. Try again';
        $authKey = \Yii::$app->getRequest()->post(Property::API_NAME_AUTH_KEY, '');
        $property = Property::findByAuthKey($authKey);
        if ($property) {
            $property->security_status = Property::STATUS_CONTROL_NORMAL;
            $property->save();
            $checkResult = true;
            $message = 'Turned on successfully';
            Log::add(Log::LOG_API_PROPERTY_CONTROL_ON, json_encode(['property_id' => $property->id, 'auth_key' => $authKey]));
        } else {
            $message = 'AuthKey is incorrect';
        }
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return ['result' => $checkResult, 'message' => $message];
    }

    /**
     * Check the property PIN by given POST value: Property::API_NAME_AUTH_KEY and Property::API_NAME_PIN_CODE
     * 
     * @return Json ['result' => boolean, 'message' => string]
     */
    public function actionCheckPin() {
        $checkResult = false;
        $authKey = \Yii::$app->getRequest()->post(Property::API_NAME_AUTH_KEY, '');
        $pinCode = \Yii::$app->getRequest()->post(Property::API_NAME_PIN_CODE, '');
        $property = Property::findByAuthKey($authKey);
        if ($property) {
            $checkResult = ($property->pin_code === $pinCode);
            PropertyAccessLog::addAccessLog($property->id, $checkResult, $pinCode);
            LogStream::getSocketConnection()->send(LogStream::getPropertyWatchJson($property->id , ['logUpdate' => true, 'pin' => [
                            'required' => $property->propertyAccessLog->getTimePassed(false) > PropertyAccessLog::PIN_TYPING_INTERVAL,
                            'status' => $checkResult,
                            'time' => $property->propertyAccessLog->getTimePassed(),
                            'timepassed' => $property->propertyAccessLog->getTimePassed(false)
            ]]));
        }
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return ['result' => $checkResult, 'message' => ($checkResult ? 'Code accepted' : 'Incorrect PIN')];
    }

    /**
     * Returns property status for the given auth key
     * @return type
     */
    public function actionGetPropertyStatus() {
        $checkResult = false;
        $authKey = \Yii::$app->getRequest()->post(Property::API_NAME_AUTH_KEY, '');
        if ($authKey) {
            $property = Property::findByAuthKey($authKey);
            $message = "";
            if ($property) {
                $checkResult = true;
                $message = $property->getStatusText();
                Log::add(Log::LOG_API_PROPERTY_STATUS, '{"property_id":"' . $property->id . '", "auth_key": "' . $authKey . '"}');
            } else {
                $message = "Incorrect " . Property::API_NAME_AUTH_KEY;
            }
        } else {
            $message = "No " . Property::API_NAME_AUTH_KEY . 'given';
        }
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return ['result' => $checkResult, 'message' => $message];
    }

    /**
     * Returs all property config in JSON
     * @return string
     */
    public function actionGetSettings() {
        $authKey = \Yii::$app->getRequest()->post(Property::API_NAME_AUTH_KEY, '');
        $message = "";
        $resultArr = [
            'result' => false,
            'message' => '',
            'status' => '',
            'action' => '',
        ];
        if ($authKey) {
            $property = Property::findByAuthKey($authKey);
            if ($property) {
                $resultArr['result'] = true;
                $resultArr['status'] = $property->getStatusText();
                //  Retrieve required action
                $alertCount = Alert::getActive()->leftJoin('camera', 'camera.id = alert.camera_id')->andWhere(['camera.property_id' => $property->id])->count();
                if ($alertCount)
                    $resultArr['action'] = self::API_ACTION_ALERT;
                $alarmCount = Alarm::find()->leftJoin('alert', 'alarm.alert_id = alert.id')->leftJoin('camera', 'camera.id = alert.camera_id')->andWhere(['alarm.status' => Alarm::STATUS_ACTIVE, 'camera.property_id' => $property->id])->count();
                if ($alarmCount)
                    $resultArr['action'] = self::API_ACTION_ALARM;
            } else {
                $resultArr['message'] = "Incorrect " . Property::API_NAME_AUTH_KEY;
            }
        } else {
            $resultArr['message'] = "No " . Property::API_NAME_AUTH_KEY . 'given';
        }
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $resultArr;
    }
    
    /**
     * Add Logs in the table tag_log
     * @return bool
     */
    public function actionTagLog()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $jsonArray = \Yii::$app->getRequest()->getRawBody();

        $jsonArrayDecode = Json::decode($jsonArray);
        
        if($jsonArrayDecode) {
            $arr = [];
            $reader = \common\models\Reader::find()->where('serial_number=:serial_number', [':serial_number' => $jsonArrayDecode['DevSN']])->one()->id;
            
            if(!empty($reader)) {
                foreach($jsonArrayDecode['AttenceID'] as $json) {
                    $tag = \common\models\Tag::find()->where('tag_id=:tag_id', [':tag_id' => $json['ID']])->one();
                    $reader_authorised = \common\models\Reader::find()->where('serial_number=:serial_number AND property_id=:property_id', [':serial_number' => $jsonArrayDecode['DevSN'], ':property_id' => $tag->property_id])->one()->id;
                    if(!empty($reader_authorised))
                        $is_authorised = 1;
                    else 
                        $is_authorised = 0;
                    
                    $arr[] = [
                        'tag_id' => $tag->id, 
                        'reader_id' => $reader, 
                        'is_authorised' => $is_authorised, 
                        'created_at' => time(),
                        'updated_at' => time(),
                    ];
                }
                if( \common\models\TagLog::saveData($arr)) {
					$authorised = [];
					$not_authorised = [];
					
					$realTime = time();
					$findTime = $realTime - 30;
					
					$tagLogs = \common\models\TagLog::find()->select(['tag_id', 'reader_id', 'is_authorised'])->where(['>', 'created_at', $findTime])->all();
					if(!empty($tagLogs)) {
						foreach($tagLogs as $tagLog) {
							if($tagLog->is_authorised == 1)
								$authorised[] = $tagLog->tag_id;
							else 
								$not_authorised[] = $tagLog->tag_id;
						}
						$authorised = array_unique($authorised);
						$not_authorised = array_unique($not_authorised);
						
						$tagsAuthorised = \common\models\Tag::find()->where(['IN', 'id', $authorised])->asArray()->all();
						$tagsNotAuthorised = \common\models\Tag::find()->where(['IN', 'id', $not_authorised])->asArray()->all();
						
						LogStream::getSocketConnection()->send(LogStream::getTagLogJson($tagsAuthorised, $tagsNotAuthorised));
					}
					
                    return true;
				}
            }
        }
    }

    /**
     * Stores camera logs
     * @return type
     */
    public function actionWriteLog() {
        $authKey = \Yii::$app->getRequest()->post(Property::API_NAME_AUTH_KEY, '');
        $result = false;
        if ($authKey) {
            $property = Property::findByAuthKey($authKey);
            if ($property) {
                $logName = \Yii::$app->getRequest()->post(Property::API_NAME_LOG_NAME, '');
                $logDetails = \Yii::$app->getRequest()->post(Property::API_NAME_LOG_DETAILS, '');
                if ($logName && $logDetails) {
                    Log::add($logName, $logDetails, null, null, $property->id);
                    if ($logName === Log::LOG_API_CAMERY_TYPING)
                        LogStream::getSocketConnection()->send(LogStream::getPropertyWatchJson($property->id, ['pinTyping' => true]));

                    $result = true;
                }
            }
        }
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return ['result' => $result];
    }

}
