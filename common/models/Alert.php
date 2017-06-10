<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "alert".
 *
 * @property integer $id
 * @property string $type
 * @property string $ip
 * @property integer $user_id
 * @property integer $camera_id
 * @property integer $mail_id
 * @property string $status
 * @property Camera $camera
 * @property string $raw_json
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $deleted_at
 * @property User $user
 */
class Alert extends ARModel {

    /**
     * Maximum time for user to accept the alert in seconds
     */
    const TIME_ASSIGN_LIMIT = 20;

    /**
     * Key name for memcached
     */
    const ALERT_MEMCACHED_MAIL_ID = "AlerMailId";

    /**
     * EMAIL for the alerts
     */
    const ALERT_MAIL = "system@platform.patroleum.net";

    /**
     * Waiting for an agent
     */
    const STATUS_PENDING = 'pending';

    /**
     * User started watching
     */
    const STATUS_ACCEPTED = 'accepted';

    /**
     * System assigned to the availeable user and waiting for him
     */
    const STATUS_ASSIGNED = 'assigned';

    /**
     * User marked alert as viewed
     */
    const STATUS_VIEWED = 'viewed';

    /**
     * Status of alerts, that shouldnt be viewed
     */
    const STATUS_BOUNCED = 'bounced';

    /**
     * Main event tipe recieving from camera
     */
    const EVENT_VIDEO_MOTION = 'VideoMotion';

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'alert';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        $rulesArr = [
            [['mail_id', 'raw_json'], 'default', 'value' => 0],
            [['created_at', 'updated_at'], 'default', 'value' => time()],
            ['status', 'default', 'value' => self::STATUS_PENDING],
            [['ip'], 'default', 'value' => ''],
            [['type', 'status'], 'required'],
            [['user_id', 'created_at', 'updated_at', 'deleted_at', 'camera_id'], 'integer'],
            [['type'], 'string', 'max' => 50],
            [['ip'], 'string', 'max' => 15],
            [['status'], 'string', 'max' => 15],
        ];
        return array_merge($rulesArr, parent::rules());
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'ip' => 'Ip',
            'user_id' => 'User ID',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }

    /**
     * Returns associated camera
     * @return Camera
     */
    public function getCamera() {
        return $this->hasOne(Camera::className(), ['id' => 'camera_id']);
    }

    /**
     * Returns associated user
     * 
     * @return type
     */
    public function getUser() {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * Adds new alert info into DataBase, if no camera found saves log
     * @param type $serialNumber
     * @return integet|boolean alert_id or false
     */
    public static function add($serialNumber, $ip, $eventType, $status = self::STATUS_PENDING, $mailId = null, $rawJson = '') {
        $camera = Camera::find()->andWhere(['serial_number' => $serialNumber])->one();
        if ($camera) {
            $model = new self();
            $model->camera_id = $camera->id;
            $model->ip = $ip;
            $model->status = $status;
            $model->type = $eventType;
            $model->mail_id = $mailId;
            $model->raw_json = $rawJson;
            return ($model->save() ? $model->id : false);
        } else {
            Log::add(Log::LOG_ALERT_NOT_FOUND, json_encode(['serial_number' => $serialNumber, 'ip' => $ip, 'event_type' => $eventType]));
            return false;
        }
    }

    /**
     * Returns AQ to retrieve all user's alerts
     * 
     * @param \common\models\common\models\User $user
     * @return \yii\db\ActiveQuery
     */
    public static function getUserAlerts(common\models\User $user = null) {
        if ($user) {
            return self::find()->andWhere(['user_id' => $user->id]);
        } else {
            $curUser = Yii::$app->user->getIdentity();
            if ($curUser)
                return self::find()->andWhere(['user_id' => $curUser->id]);
            else
                return self::find()->andWhere(false);
        }
    }

    /**
     * Returns alert which are processing or pending
     * 
     * @return Alert
     */
    public static function getActive() {
        $alerts = self::find()
                ->andWhere(['not', ['status' => self::STATUS_BOUNCED]])
                ->andWhere(['not', ['status' => self::STATUS_VIEWED]])
                ->orderBy([self::tableName() . '.id' => SORT_DESC]);
        return $alerts;
    }

    /**
     * Returns whether alert is still active 
     * @return type
     */
    public function isActive() {
        $activeArr = [self::STATUS_ACCEPTED, self::STATUS_ASSIGNED];
        return in_array($this->status, $activeArr);
    }

    /**
     * Check all logs for the given user and try to assign any availeable or timedout alert to him
     * 
     * @param type $userId
     * @return type
     */
    public static function assignAlertToAgent($userId) {
        $curAlert = null;
        $curUser = User::findOne($userId);
        if ($curUser) {
            //  Get all assigned alerts
            $logsId = (new \yii\db\Query)
                    ->select(['alert_id'])
                    ->from('log')
                    ->andWhere(['user_id' => $curUser->id])
                    ->andWhere(['name' => Log::LOG_CAMERA_ASSIGN])
                    ->groupBy(['alert_id'])
                    ->all();
            $logsIdArr = array_map(function($item) {
                return ($item['alert_id'] ? $item['alert_id'] : '0');
            }, $logsId);

            $curAlert = Alert::find()
                    ->andWhere(['status' => Alert::STATUS_PENDING])
                    ->andWhere(['or', ['<>', 'user_id', $curUser->id], 'ISNULL(user_id)'])
                    ->andWhere(['not in', 'id', $logsIdArr])
                    ->orderBy(['id' => SORT_ASC])
                    ->one();

            if (!$curAlert) {
                $curAlert = Alert::find()
                        ->andWhere(['not in', 'id', $logsIdArr])
                        ->andWhere(['or', ['status' => Alert::STATUS_ASSIGNED], ['status' => Alert::STATUS_ACCEPTED]])
                        ->andWhere(['or', ['<>', 'user_id', $curUser->id], 'ISNULL(user_id)'])
                        ->andWhere("(UNIX_TIMESTAMP(NOW()) - " . Alert::TIME_ASSIGN_LIMIT . ") > updated_at")
                        ->orderBy(['id' => SORT_ASC])
                        ->one();
            }
            //  Check if agent alreade worked with this alert
            //  Use logs to determine if user skipped it
            /*
              if ($curAlert) {
              $logCount = Log::find()
              ->andWhere(['user_id' => $curUser->id])
              ->andWhere(['alert_id' => $curAlert->id])
              ->andWhere(['name' => Log::LOG_CAMERA_ASSIGN])
              ->count();
              if ($logCount > 0)
              $curAlert = null;
              }
             */
            //  ---------------------------------------------
        }
        return $curAlert;
    }

    /**
     * Retrieve online user sessions from memcache and send notification via WS
     * Remove user session from active stack
     */
    public function assignWSAlertUsingMemcache() {

        $result = false;

        $memcache = new \Memcache();
        $memcache->connect('localhost', 11211) or die("Could not connect");
        $activeUserArray = $memcache->get(LogStream::MEMCACHE_WATCH_WAIT_LIST);
        $activeUserIdArr = array_keys($activeUserArray);
        //var_dump($activeUserArray);
        if ($activeUserIdArr && is_array($activeUserIdArr) && count($activeUserIdArr) > 0) {
            foreach ($activeUserIdArr as $userId) {

                //  Get all assigned alerts
                $logsId = (new \yii\db\Query)
                        ->select(['alert_id'])
                        ->from('log')
                        ->andWhere(['user_id' => $userId])
                        ->andWhere(['name' => Log::LOG_CAMERA_ASSIGN])
                        ->groupBy(['alert_id'])
                        ->all();
                $logsIdArr = array_map(function($item) {
                    return ($item['alert_id'] ? $item['alert_id'] : '0');
                }, $logsId);

                if (in_array($this->id, $logsIdArr) || $this->user_id == $userId)
                    continue;

                if ($userId) {
                    $userConnectionId = array_pop($activeUserArray[$userId]);
                    if ($userConnectionId) {
                        //  Assign alert to an agent and send it to him
                        $this->user_id = $userId;
                        $this->status = self::STATUS_ASSIGNED;
                        $result = $this->save();
                        //getWatchNotificationJsonecho $this->id . PHP_EOL;
                        //var_dump($userId);
                        //var_dump($userConnectionId);
                        $context = new \ZMQContext();
                        $socket = $context->getSocket(\ZMQ::SOCKET_PUSH, 'my pusher');
                        $socket->connect("tcp://localhost:5555");
                        $socket->send(LogStream::getWatchNotificationJson($userId, $this->id, $userConnectionId));
                        //  Remove connection from waiting list
                        if (count($activeUserArray[$userId]) === 0)
                            unset($activeUserArray[$userId]);
                        $memcache->set(LogStream::MEMCACHE_WATCH_WAIT_LIST, $activeUserArray);
                    }
                }
                break;
            }
        }

        return $result;
    }

}
