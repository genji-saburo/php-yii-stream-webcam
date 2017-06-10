<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use \common\models\Alert;
use \common\models\Camera;
use \common\models\Property;
use common\models\Log;
use yii\db\Query;
use \common\models\CameraLog;

class CronController extends Controller {

    /**
     * Move dropped video alerts to the other agents
     */
    public function actionCheckAcceptedAlerts() {
        $alerts = Alert::find()
                ->andWhere(['status' => Alert::STATUS_ACCEPTED]);
        //  Check each alert and if user is down, change status to pending        
        foreach ($alerts->each(50) as $alert) {
            $lastLog = Log::find()
                    ->andWhere(['name' => Log::LOG_CAMERA_VIEW])
                    ->andWhere(['user_id' => $alert->user_id])
                    ->andWhere(['alert_id' => $alert->id])
                    ->orderBy(['id' => SORT_DESC])
                    ->one();
            if ($lastLog && (time() - $lastLog->created_at > Alert::TIME_ASSIGN_LIMIT)) {
                $alert->status = Alert::STATUS_PENDING;
                $alert->save();
                $this->stdout("#{$alert->id} alert status has been changed to pending" . PHP_EOL);
            }
        }
    }

    /**
     * Process alert email and create new alerts
     */
    public function actionCheckAlertEmail() {
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
        $this->stdout("Checking all the email from mailbox {$mailTo} starting from id {$lastId}" . PHP_EOL);
        $alertMailArr = $db->createCommand("SELECT * FROM messages Where id > {$lastId} ORDER BY id ASC")
                ->queryAll();
        $mailId = 0;
        foreach ($alertMailArr as $mail) {
            $mailId = $mail['id'];
            $this->stdout("Checking mail " . $mail['id'] . PHP_EOL);
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
                preg_match("/IP Address: ([^\r\n]+)/", $body, $alarmIP);
                if ($alarmIP && isset($alarmIP[1]))
                    $alarmIPval = trim($alarmIP[1]);
                else
                    $alarmIP = '127.0.0.1';
                preg_match("/Alarm Device Name: ([^\r\n]+)/", $body, $alarmSN);
                if ($alarmSN && isset($alarmSN[1])) {
                    $camera = Camera::findOne(['serial_number' => trim($alarmSN[1])]);
                    if ($camera) {
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

                                $this->stdout("New alert found in mail #" . $mailId . PHP_EOL);
                                $alertId = Alert::add($camera->serial_number, $alarmIPval, Alert::EVENT_VIDEO_MOTION, Alert::STATUS_PENDING, $mailId);
                                if ($alertId && ($alertObj = Alert::findOne($alertId)))
                                    $alertObj->assignWSAlertUsingMemcache();
                            } else {
                                $this->stdout("Dublicated alert found in mail #" . $mailId . PHP_EOL);
                            }
                        } else {
                            $this->stdout("Given Event doesn't fire an alarm in mail #" . $mailId . PHP_EOL);
                        }
                    }
                }
            }
        }
        if ($mailId)
            $memcache->set(Alert::ALERT_MEMCACHED_MAIL_ID, $mailId);
        //$memcache->set(Alert::ALERT_MEMCACHED_MAIL_ID, 0);
        $this->stdout("Mail check finished" . PHP_EOL);
    }

    /**
     * Check each camera for current status and save logs
     */
    public function actionCheckCameraStatus() {
        $online = 0;
        $offline = 0;
        foreach (Camera::find()->each(100) as $camera) {
            $sn = $camera->apiGetSerialNo();
            if (is_null($sn) || ($sn != $camera->serial_number)) {
                CameraLog::addLog($camera->id, CameraLog::STATUS_OFFLINE);
                $offline++;
            } else {
                CameraLog::addLog($camera->id, CameraLog::STATUS_ONLINE);
                $online++;
            }
        }
        $this->stdout("Camera status check finished, found: online=" . $online . '; offline=' . $offline . PHP_EOL);
    }

    /**
     * Check dropped alerts and sends it to the agents
     */
    public function actionCheckDroppedAlerts() {
        $activeAlerts = Alert::find()
                ->andWhere(['status' => Alert::STATUS_PENDING])
                ->orderBy(['id' => SORT_ASC]);
        foreach ($activeAlerts->each(100) as $alert) {
            $alert->assignWSAlertUsingMemcache();
            $this->stdout("Alert#" . $alert->id . PHP_EOL);
            
        }
        $droppedAlerts = Alert::find()
                ->andWhere(['or', ['status' => Alert::STATUS_ASSIGNED], ['status' => Alert::STATUS_ACCEPTED]])
                ->andWhere("(UNIX_TIMESTAMP(NOW()) - " . Alert::TIME_ASSIGN_LIMIT . ") > updated_at")
                ->orderBy(['id' => SORT_ASC]);
        foreach ($droppedAlerts->each(100) as $alert) {
            $alert->assignWSAlertUsingMemcache();
            $this->stdout("Alert#" . $alert->id . PHP_EOL);
        }
    }

}
