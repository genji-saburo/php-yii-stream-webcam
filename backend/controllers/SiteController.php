<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use common\models\User;
use common\models\Log;
use \common\models\Alert;
use common\models\Camera;
use common\models\CameraLog;
use yii\helpers\Html;
use common\models\LogStream;

/**
 * Site controller
 */
class SiteController extends AccessController {

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions() {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex() {
        $user = Yii::$app->user->getIdentity();
        switch ($user->role) {
            case User::ROLE_ADMIN:
                return $this->render('index_admin');
            case User::ROLE_AGENT:
                return $this->render('index_agent');
            default :
                return $this->render('index_admin');
        }
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin() {
        Yii::$app->layout = 'main-login';
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            Log::add(Log::LOG_USER_LOGIN, 'Main login action');
            return $this->goBack();
        } else {
            return $this->render('login', [
                        'model' => $model,
            ]);
        }
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout() {
        Log::add(Log::LOG_USER_LOGOUT, 'Main logout action');
        Yii::$app->user->logout();
        return $this->goHome();
    }

    /**
     * Password reset action
     */
    public function actionPassword() {
        $model = new \common\models\PasswordResetForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                $user = Yii::$app->user->getIdentity();
                $user->setPassword($model->password_new);
                if ($user->save()) {
                    Yii::$app->getSession()->setFlash('success', 'Password has been changed.');
                    return $this->redirect('/');
                } else {
                    Yii::$app->getSession()->setFlash('success', 'There is an error while password update.');
                    return $this->redirect('/');
                }
            }
        }
        return $this->render('password', ['model' => $model]);
    }

    /**
     * Alert watch interface
     */
    public function actionWatch($alert_id = null, $camera_id = null, $video_wall = false) {
        $curUser = Yii::$app->user->getIdentity();

        if (is_null($alert_id)) {
            //  If no alerts found and agent just finished another one, show him new alert without question
            $latestWatchFinishLog = Log::find()->andWhere(['user_id' => $curUser->id, 'name' => Log::LOG_CAMERA_CLOSED])->orderBy(['id' => SORT_DESC])->one();
            if ($latestWatchFinishLog && (time() - $latestWatchFinishLog->created_at) < 5) {
                //  Get all assigned alerts to do not show skipped ones
                $model = Alert::assignAlertToAgent($curUser->id);
                if ($model) {
                    $model->user_id = $curUser->id;
                    $model->status = Alert::STATUS_ASSIGNED;
                    $model->save();
                    Log::add(Log::LOG_CAMERA_ASSIGN, '{}', $curUser->id, $model->camera_id, $model->camera->property->id, $model->id);
                }
            }
            if (!isset($model) || !$model)
                return $this->render('watch_wait');
        }
        else {
            $model = \common\models\Alert::findOne($alert_id);
        }

        if ($model) {
            //  Check if user has access to this alert
            if ($curUser->role === User::ROLE_ADMIN || ($model->user_id == $curUser->id && $model->isActive())) {
                $property = $model->camera->property;
				
                if ($camera_id)
                    $camera = \common\models\Camera::findOne($camera_id);
                if (!isset($camera) || !$camera)
                    $camera = $model->camera;
                if ($curUser->role != User::ROLE_ADMIN) {
                    //  Mark alert as accepted
                    $model->status = \common\models\Alert::STATUS_ACCEPTED;
                    //  Uncomment for real work
                    $model->save();
                    //  ----------------------
                }
                Log::add(Log::LOG_CAMERA_VIEW, "{\"camera_id\":{$camera->id}}", null, $camera->id, $property->id, $model->id);  //  Log camera before access
                if ($video_wall && $property) {
                    Yii::$app->layout = 'video_wall';
                    return $this->render('watch_video_wall', ['alert' => $model, 'camera' => $camera, 'property' => $property]);
                } else
                    return $this->render('watch', ['alert' => $model, 'camera' => $camera, 'property' => $property]);
            }
            else {
                Yii::$app->session->setFlash('warning', 'You don\'t have an access to the requested alert');
                return $this->redirect(['site/watch']);
            }
        } else {
            Yii::$app->session->setFlash('info', 'We cannot find requested alert');
            return $this->redirect(['site/watch']);
        }
    }

    /**
     * Returns JSON with admin's dashboard data
     */
    public function actionDashboardData() {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $resultArr = ['result' => true];

        $resultArr['CameraLog']['online'] = CameraLog::find()->andWhere(['status' => CameraLog::STATUS_ONLINE])->andWhere('created_at > UNIX_TIMESTAMP(NOW()) - 60')->groupBy(['camera_id'])->count();
        $resultArr['CameraLog']['all'] = Camera::find()->count();
        $resultArr['CameraLog']['html'] = '';
        $offlineCameraLogs = CameraLog::find()->andWhere(['status' => CameraLog::STATUS_OFFLINE])->andWhere('created_at > UNIX_TIMESTAMP(NOW()) - 60')->groupBy(['camera_id'])->all();
        foreach ($offlineCameraLogs as $cameraLog) {
            $onlineLog = CameraLog::find()->andWhere(['camera_id' => $cameraLog->camera_id, 'status' => CameraLog::STATUS_ONLINE])->orderBy(['id' => SORT_DESC])->one();
            if ($onlineLog)
                $onlineTime = \Yii::$app->formatter->asDatetime($onlineLog->created_at);
            else
                $onlineTime = 'Never';
            $resultArr['CameraLog']['html'] .= Html::tag('div', Html::tag('div', Html::tag('div', '<i class="fa fa-warning" style="color: red;"></i> Camera: ' . Html::a($cameraLog->camera->name, ['/camera/view', 'id' => $cameraLog->camera_id]) . '  is offline', ['class' => 'lead']) . Html::tag('div', 'Last check time: ' . \Yii::$app->formatter->asDatetime($cameraLog->created_at)), ['class' => 'col-xs-6']) .
                            Html::tag('div', 'Last successfull check: ' . $onlineTime, ['class' => 'col-xs-6'])
                            , ['class' => 'row']);
        }
        return $resultArr;
    }

    public function actionTest() {
        
        $ip = Yii::$app->request->getUserIP();
        if(\common\models\UserRestriction::validateIp($ip))
            return "true";
        else
            return "false";
        
        return '';
        
        return $this->render('test', ['type' => $videoType]);

        $res = '';

        $db = new \yii\db\Connection([
            'dsn' => 'mysql:host=localhost;dbname=' . Yii::$app->params['ALERT_MAIL_DB'],
            'username' => Yii::$app->params['ALERT_MAIL_DB_USERNAME'],
            'password' => Yii::$app->params['ALERT_MAIL_DB_PASSWORD'],
            'charset' => 'utf8',
        ]);
        $memcache = new \Memcache();
        $memcache->connect('localhost', 11211) or die("Could not connect");
        $cachedId = $memcache->get(Alert::ALERT_MEMCACHED_MAIL_ID);
        if ($cachedId)
            $lastId = $cachedId;
        else {
            $alert = Alert::find()
                    ->where(['not', ['mail_id' => null]])
                    ->orderBy(['id' => SORT_DESC])
                    ->one();
            if ($alert)
                $lastId = $alert->mail_id;
            else
                $lastId = 0;
        }
        $mailTo = Alert::ALERT_MAIL;
        $res .= "Checking all the email from mailbox {$mailTo} starting from id {$lastId}" . PHP_EOL;
        $alertMailArr = $db->createCommand("SELECT * FROM messages Where id > {$lastId} ORDER BY id ASC")
                ->queryAll();
        $mailId = 0;
        foreach ($alertMailArr as $mail) {
            $mailId = $mail['id'];
            $res .= "Checking mail " . $mail['id'] . PHP_EOL;
            //  Retrive message body from raw stream and try to decode it from base_64
            $parser = new \PhpMimeMailParser\Parser();
            $parser->setText($mail['body']);
            $body = $parser->getMessageBody('text');
            if ($body) {
                preg_match("/Alarm Event: ([^\r\n]+)/", $body, $alarmEvent);
                if ($alarmEvent && isset($alarmEvent[1]))
                    $alarmEventVal = trim($alarmEvent[1]);
                else
                    $alarmEventVal = '';
                $res .= "Alarm event val: $alarmEventVal";
                preg_match("/IP Address: ([^\r\n]+)/", $body, $alarmIP);
                if ($alarmIP && isset($alarmIP[1]))
                    $alarmIPval = trim($alarmIP[1]);
                else
                    $alarmIP = '127.0.0.1';
                $res .= "Alarm ip: $alarmIPval";
                preg_match("/Alarm Device Name: ([^\r\n]+)/", $body, $alarmSN);
                if ($alarmSN && isset($alarmSN[1])) {
                    $res .= "Alarm SN: " . trim($alarmSN[1]);
                    $camera = \common\models\Camera::findOne(['serial_number' => trim($alarmSN[1])]);
                    if ($camera) {
                        $res .= "Alarm ID: " . $camera->id;
                        $propertyCameraArr = [];
                        if ($camera->property) {
                            foreach ($camera->property->cameras as $curCamera) {
                                $propertyCameraArr[] = $curCamera->id;
                            }
                        } else {
                            $propertyCameraArr = [$camera->id];
                        }
                        if ($alarmEventVal && Camera::isAlertEvent($alarmEventVal)) {
                            //  Check for the active alarm for the same property
                            $getAlertQuery = Alert::getActive()->andWhere(['in', 'camera_id', $propertyCameraArr]);
                            $alertCount = $getAlertQuery->count();
                            if ($alertCount == 0) {
                                $res .= "New alert found in mail #" . $mailId . PHP_EOL;
                                Alert::add($camera->serial_number, $alarmIPval, Alert::EVENT_VIDEO_MOTION, Alert::STATUS_PENDING, $mailId);
                            } else {
                                $res .= "Dublicated alert found in mail #" . $mailId . PHP_EOL;
                            }
                        } else {
                            $res .= "Given Event doesn't fire an alarm in mail #" . $mailId . PHP_EOL;
                        }
                    }
                }
            }
        }
        $res .= "Mail check finished";
        return $res;
    }

}
