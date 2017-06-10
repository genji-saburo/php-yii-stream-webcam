<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "log".
 *
 * @property integer $id
 * @property string $name
 * @property string $details
 * @property integer $user_id
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer camera_id
 * @property integer property_id
 * @property integer alert_id
 * @property User $user
 * @property Alert $alert
 * @property Camera $camera
 * @property Property $property
 */
class Log extends \yii\db\ActiveRecord {

    const LOG_USER_LOGIN = 'User login';
    const LOG_USER_LOGOUT = 'User logout';
    const LOG_USER_ONLINE = 'User online';
    const LOG_ALARM_USER_INIT = 'Alarm enabled by user';
    const LOG_ALERT_NOT_FOUND = 'Alert camera not found';
    const LOG_CAMERA_ASSIGN = 'Camera assigned';
    const LOG_CAMERA_VIEW = 'Camera view';
    const LOG_CAMERA_CLOSED = 'Camera closed';
    const LOG_CAMERA_SKIPPED = 'Camera skipped';
    const LOG_API_PROPERTY_AUTH = 'API: Property authentication';
    const LOG_API_CAMERY_TYPING = 'API: Pin code input';
    const LOG_API_PROPERTY_STATUS = 'API: Property status';
    const LOG_API_PROPERTY_CONTROL_ON = "API: Property control on";
    const LOG_API_PROPERTY_CONTROL_OFF = "API: Property control off";

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'log';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['created_at', 'updated_at'], 'default', 'value' => time()],
            [['details'], 'default', 'value' => ''],
            [['name', 'details',], 'required'],
            [['user_id', 'created_at', 'updated_at', 'camera_id', 'property_id', 'alert_id'], 'integer'],
            [['name'], 'string', 'max' => 50],
            [['details'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'details' => 'Details',
            'user_id' => 'User ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'property_id' => 'Property Id',
            'camera_id' => 'Camera Id',
            'alert_id' => 'Alert Id'
        ];
    }

    /**
     * Adds new log to the DataBase
     * 
     * @param type $name
     * @param type $details
     * @param type $user_id
     * @param type $camera_id
     * @param type $property_id
     * @param type $alert_id
     * @return boolean
     */
    public static function add($name, $details, $user_id = null, $camera_id = null, $property_id = null, $alert_id = null) {
        $model = new self();
        $model->name = $name;
        $model->details = $details;
        $model->user_id = $user_id;
        $model->camera_id = $camera_id;
        $model->property_id = $property_id;
        $model->alert_id = $alert_id;

        if ((Yii::$app instanceof yii\web\Application) && !Yii::$app->user->isGuest && $id = Yii::$app->user->getId()) {
            $model->user_id = $id;
        }
        else{
            $model->user_id = 0;    //  System user
        }

        $result = $model->save();

        return $result;
    }

    /**
     * Returns associated user
     * @return type
     */
    public function getUser() {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * Returs last login Datetime string or Message, if never logged in
     * @param type $userId
     * @return string
     */
    public static function getLastLoginTime($userId = null) {
        $model = null;
        if ($userId) {
            $model = self::find()->andWhere(['user_id' => $userId])->orderBy(['id' => SORT_DESC])->one();
        } else {
            $user = Yii::$app->user->getIdentity();
            if ($user)
                $model = self::find()->andWhere(['user_id' => $user->id])->orderBy(['id' => SORT_DESC])->one();
        }
        if ($model)
            return Yii::$app->formatter->asDatetime($model->created_at);
        else
            return "Never logged in";
    }

    /**
     * Returns related camera
     * @return Camera
     */
    public function getCamera() {
        return $this->hasOne(Camera::className(), ['id' => 'camera_id']);
    }

    /**
     * Returns related property
     * @return Property
     */
    public function getProperty() {
        return $this->hasOne(Property::className(), ['id' => 'property_id']);
    }

    /**
     * Returns related alert
     * @return Property
     */
    public function getAlert() {
        return $this->hasOne(Alert::className(), ['id' => 'alert_id']);
    }

    /**
     * Returns number of unique users left logs within last seconds
     * 
     * @param type $logName
     * @param type $secondsInterval
     * @return integer
     */
    public static function getCount($logName, $secondsInterval) {
        return self::find()->andWhere(['name' => $logName])->andWhere("created_at > UNIX_TIMESTAMP(NOW()) - $secondsInterval")->groupBy('user_id')->count();
    }

    /**
     * Returns lates user's logs 
     * 
     * @param type $user_id
     * @return array
     */
    public static function getUserLogs($user_id) {
        $logs = [];
        $user = User::findOne($user_id);
        if ($user) {
            $logs = (new \yii\db\Query())
                    ->select("*")
                    ->from('log')
                    ->andWhere(['user_id' => $user_id])
                    ->andWhere('created_at > UNIX_TIMESTAMP(NOW()) - 300')
                    ->orderBy(['id' => SORT_DESC, 'created_at' => SORT_DESC])
                    ->groupBy('name')
                    ->all();
        }
        return $logs;
    }

    /**
     * Do all required actions if needed before saving
     * @param type $insert
     * @return type
     */
    public function beforeSave($insert) {
        if ($this->isNewRecord) {
            if ($this->name === self::LOG_CAMERA_VIEW && $this->alert) {
                $this->alert->updateAttributes(['updated_at' => time()]);
            }
        }

        return parent::beforeSave($insert);
    }

}
