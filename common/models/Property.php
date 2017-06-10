<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "property".
 *
 * @property integer $id
 * @property string $name
 * @property string $owner_name
 * @property string $pin_code
 * @property string $address
 * @property string $phone1
 * @property string $phone2
 * @property string $phone3
 * @property string $auth_key
 * @property string $security_status
 * @property string $phone_police
 * @property string $coord_lat
 * @property integer $coord_lng
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $deleted_at
 * @property Camera $cameras
 * @property PropertyAccessLog propertyAccessLog
 */
class Property extends ARModel {

    /**
     * Api post input name for auth_key
     */
    const API_NAME_AUTH_KEY = "auth_key";

    /**
     * Api post input name for pin_code
     */
    const API_NAME_PIN_CODE = "pin_code";

    /**
     * Api post name for the property id
     */
    const API_NAME_ID = 'prop_id';

    /**
     * Api post name for the log name
     */
    const API_NAME_LOG_NAME = "camera_log_name";

    /**
     * Api post name for the log details
     */
    const API_NAME_LOG_DETAILS = "camera_log_details";
    const STATUS_CONTROL_NORMAL = 0;
    const STATUS_CONTROL_CHECKED = 10;
    const STATUS_CONTROL_SWITCHED_OFF = -10;
    const STATUS_CONTROL_NORMAL_TEXT = "System is under control";
    const STATUS_CONTROL_CHECKED_TEXT = "Pin verified. System is under control";
    const STATUS_CONTROL_SWITCHED_OFF_TEXT = "Control disabled";

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'property';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        $rulesArr = [
            [['security_status'], 'default', 'value' => self::STATUS_CONTROL_NORMAL],
            [['updated_at'], 'defaultTime', 'skipOnEmpty' => false],
            [['created_at', 'updated_at'], 'default', 'value' => time()],
            [['coord_lat', 'coord_lng', 'auth_key'], 'default', 'value' => ''],
            [['phone1', 'phone2', 'phone3', 'address', 'phone_police', 'owner_name'], 'default', 'value' => ''],
            [['name', 'pin_code',], 'required'],
            [['created_at', 'updated_at', 'security_status'], 'integer'],
            [['name', 'phone2', 'phone3', 'coord_lat', 'coord_lng', 'auth_key'], 'string', 'max' => 50],
            [['pin_code'], 'string', 'max' => 10],
            [['phone1'], 'string', 'max' => 50],
        ];
        return array_merge($rulesArr, parent::rules());
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'pin_code' => 'Pin Code',
            'phone1' => 'Phone1',
            'phone2' => 'Phone2',
            'phone3' => 'Phone3',
            'coord_lat' => 'Coord Lat',
            'coord_lng' => 'Coord Lng',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Returns options arr ['id' => 'name']
     * Show all the properties currently availeable
     */
    public static function getOptionsArr() {
        $propertyArr = ['prompt' => 'Select Property'];

        foreach (self::find()->each() as $property) {
            $propertyArr[$property->id] = "#" . $property->id . ' - ' . $property->name;
        }
        return $propertyArr;
    }

    /**
     * Returns AQ to retrieve all cameras of the Property
     */
    public function getCameras() {
        return $this->hasMany(Camera::className(), ['property_id' => 'id']);
    }

    /**
     * Generate unique auth key, write in into auth_key property and returns it
     * @return string
     */
    public function generateAuthKey() {
        $this->auth_key = uniqid("auth{$this->id}");
        return $this->auth_key;
    }

    /**
     * Returns Property by given authKey, if key not found or empty string, returns null
     * @param type $authKey
     * @return Property
     */
    public static function findByAuthKey($authKey) {
        if ($authKey === '')
            return null;
        else
            return self::find()->andWhere(['auth_key' => $authKey])->one();
    }

    /**
     * Returns current property status
     * @return string
     */
    public function getStatusText() {
        $statusText = "";
        switch ($this->security_status) {
            case self::STATUS_CONTROL_CHECKED:
                $statusText = self::STATUS_CONTROL_CHECKED_TEXT;
                break;
            case self::STATUS_CONTROL_NORMAL:
                $statusText = self::STATUS_CONTROL_NORMAL_TEXT;
                break;
            case self::STATUS_CONTROL_SWITCHED_OFF:
                $statusText = self::STATUS_CONTROL_SWITCHED_OFF_TEXT;
                break;
            default :
                break;
        }
        return $statusText;
    }

    /**
     * Returns arr [id => STATUS_DESCRIPTION]
     */
    public static function getSecuriryStatusArr() {
        return [self::STATUS_CONTROL_NORMAL => self::STATUS_CONTROL_NORMAL_TEXT,
            self::STATUS_CONTROL_CHECKED => self::STATUS_CONTROL_CHECKED_TEXT,
            self::STATUS_CONTROL_SWITCHED_OFF => self::STATUS_CONTROL_SWITCHED_OFF_TEXT];
    }

    /**
     * Returns latest access log
     */
    public function getPropertyAccessLog() {
        return $this->hasOne(PropertyAccessLog::className(), ['property_id' => 'id'])->orderBy(['id' => SORT_DESC]);
    }

    /**
     * Returns AQ to retrieve the comments for all property cameras
     * @return \yii\db\ActiveQuery
     */
    public function getComments() {
        $cameraIdArr = [];
        foreach ($this->cameras as $curCamera) {
            $cameraIdArr[] = $curCamera->id;
        }
        $commentAQ = Comment::find()->andWhere(['in', 'camera_id', $cameraIdArr])->orderBy(['id' => SORT_DESC]);
        return $commentAQ;
    }

}
