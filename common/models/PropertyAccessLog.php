<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "property_access_log".
 *
 * @property integer $id
 * @property integer $property_id
 * @property integer $check_result
 * @property string $pin_code
 * @property integer $created_at
 * @property integer $updated_at
 * @property Property $property
 */
class PropertyAccessLog extends ARModel {
    
    /**
     * Interval to fire an alert
     */
    const PIN_TYPING_INTERVAL = 300;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'property_access_log';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        $rulesArr = [
            [['created_at', 'updated_at'], 'default', 'value' => time()],
            [['check_result'], 'boolean'],
            [['property_id',], 'integer'],
            [['pin_code'], 'string', 'max' => 10],
        ];
        return array_merge($rulesArr, parent::rules());
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'property_id' => 'Property ID',
            'check_result' => 'Check Result',
            'pin_code' => 'Pin Code',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Generate new event and saves it into DB
     * @param type $propertyId
     * @param type $checkResult
     * @param type $pinCode
     * @return boolean
     */
    public static function addAccessLog($propertyId, $checkResult, $pinCode) {
        $property = Property::findOne($propertyId);
        $result = false;
        if ($property) {
            $model = new PropertyAccessLog();
            $model->property_id = $property->id;
            $model->check_result = $checkResult;
            $model->pin_code = $pinCode;
            $result = $model->save();
        }
        return $result;
    }

    /**
     * Returns associated property
     * @return Property
     */
    public function getProperty() {
        return $this->hasOne(Property::className(), ['id' => 'property_id']);
    }

    /**
     * Returns time passed from last code
     * @return type
     */
    public function getTimePassed($textMode = true) {
        if (!$textMode)
            return (time() - $this->created_at);
        $dateCreated = new \DateTime();
        $dateCreated->setTimestamp($this->created_at);
        $interval = date_create('now')->diff($dateCreated);
        if ($v = $interval->y >= 1)
            return $interval->y . ' ' .
                    $this->pluralWord($interval->y, 'year', 'years', 'years');
        if ($v = $interval->m >= 1)
            return $interval->m . ' ' .
                    $this->pluralWord($interval->m, 'month', 'months', 'months');
        if ($v = $interval->d >= 1)
            return $interval->d . ' ' .
                    $this->pluralWord($interval->d, 'day', 'days', 'days');
        if ($v = $interval->h >= 1)
            return $interval->h . ' ' .
                    $this->pluralWord($interval->h, 'hour', 'hours', 'hours');
        if ($v = $interval->i >= 1)
            return $interval->i . ' ' .
                    $this->pluralWord($interval->i, 'minute', 'minutes', 'minutes');
        return 'Less than minute';
    }

    /**
     * Returns word with proper ending
     * @param type $n
     * @param type $form1 Например "day"
     * @param type $form2 Например "days"
     * @param type $form5 Например "days"
     * @return type
     */
    private function pluralWord($n, $form1, $form2, $form5) {
        $n = abs($n) % 100;
        $n1 = $n % 10;
        if ($n > 10 && $n < 20)
            return $form5;
        if ($n1 > 1 && $n1 < 5)
            return $form2;
        if ($n1 == 1)
            return $form1;
        return $form5;
    }

}
