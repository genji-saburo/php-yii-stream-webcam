<?php

namespace common\models;

use Yii;

/**
 * Class responsible for safe delete function
 */
class ARModel extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['created_at', 'updated_at'], 'default', 'value' => time()],
            [['deleted_at'], 'integer'],
            [['updated_at'], 'defaultTime', 'skipOnEmpty' => false],
        ];
    }

    /**
     * Sets current time to the given attribute
     * @param type $attributeName
     */
    public function defaultTime($attributeName) {
        $this->setAttribute($attributeName, time());
    }

    /**
     * @inheritdoc
     */
    public static function find() {
        return parent::find()->andWhere(['IS', self::tableName() . '.deleted_at', null]);
    }

    /**
     * @inheritdoc
     */
    public function delete() {
        return $this->updateAttributes([ self::tableName() . '.deleted_at' => time()]);
    }

    /**
     * Convert secconds into hours, days, etc
     * @param type $seconds
     * @return type
     */
    public static function beautifySeconds($seconds) {
        $dt = new \DateTime('@' . $seconds);
        $resultStr = '';
        $timeStarted = false;
        if ($dt->format('z')) {
            $resultStr .= $dt->format('z') . 'd. ';
            $timeStarted = true;
        }
        if ($dt->format('G') || $timeStarted) {
            $resultStr .= $dt->format('G') . 'h. ';
            $timeStarted = true;
        }
        if ($dt->format('i') || $timeStarted) {
            $resultStr .= $dt->format('i') . 'm. ';
            $timeStarted = true;
        }
        $resultStr .= $dt->format('s') . 's.';
        return $resultStr;
    }

}
