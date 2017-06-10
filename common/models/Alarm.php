<?php

namespace common\models;

use Yii;
use common\models\Property;
use common\models\Alert;

/**
 * This is the model class for table "alarm".
 *
 * @property integer $id
 * @property string $details
 * @property string $type
 * @property integer $user_id
 * @property integer $alert_id
 * @property string $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $deleted_at
 * @property User $user
 * @property Alarm $alarm
 */
class Alarm extends ARModel {

    const STATUS_ACTIVE = 'active';
    const STATUS_DISABLED = 'disabled';

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'alarm';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        $rulesArr = [
            [['status'], 'default', 'value' => self::STATUS_ACTIVE],
            [['details', 'type'], 'default', 'value' => ''],
            [['user_id', 'alert_id', 'created_at', 'updated_at', 'deleted_at'], 'integer'],
            [['details'], 'string', 'max' => 500],
            [['type'], 'string', 'max' => 50],
            [['status'], 'string', 'max' => 10]
        ];
        return array_merge($rulesArr, parent::rules());
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'details' => 'Details',
            'type' => 'Type',
            'user_id' => 'User ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }

    /**
     * Returns associated user
     * 
     * @return User
     */
    public function getUser() {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * Returns associated alert
     * 
     * @return Alert
     */
    public function getAlert() {
        return $this->hasOne(Alert::className(), ['id' => 'alert_id']);
    }

    /**
     * Returns active Alarms
     * 
     * @return type
     */
    public static function getActiveAlarms() {
        return self::find()->andWhere(['status' => self::STATUS_ACTIVE]);
    }

    /*
     * Add all triggers before saving
     */

    public function beforeSave($insert) {
        if ($this->isNewRecord && $this->validate()) {
            if ($this->alert) {
                $phoneArr = [];

                if (isset($this->alert->camera->property->phone1))
                    $phoneArr[] = $this->alert->camera->property->phone1;
                if (isset($this->alert->camera->property->phone2))
                    $phoneArr[] = $this->alert->camera->property->phone2;
                if (isset($this->alert->camera->property->phone3))
                    $phoneArr[] = $this->alert->camera->property->phone3;

                foreach ($phoneArr as $phone) {
                    //  Send notification
                    $twilioService = Yii::$app->Yii2Twilio->initTwilio();
                    try {
                        $message = $twilioService->account->messages->create(
                                $phone
                                , [
                            "from" => Yii::$app->params['twillio.from_number'],
                            "body" => Yii::$app->params['twillio.alarm_message'],
                        ]);
                    } catch (\Twilio\Exceptions\RestException $e) {
                        //echo $e->getMessage();
                        //  Do nothing while error
                    }
                }
            }
        }
        return parent::beforeSave($insert);
    }

}
