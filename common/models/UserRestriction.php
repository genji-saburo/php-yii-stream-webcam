<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_restriction".
 *
 * @property integer $id
 * @property string $ip
 * @property integer $action
 * @property integer $user_id
 * @property integer $created_at
 */
class UserRestriction extends \yii\db\ActiveRecord {

    const ACTION_BLOCK = 0;
    const ACTION_ALLOW = 1;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'user_restriction';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            ['created_at', 'default', 'value' => time()],
            [['ip', 'action'], 'required'],
            [['action'], 'in', 'range' => [self::ACTION_ALLOW, self::ACTION_BLOCK]],
            [['action', 'user_id', 'created_at'], 'integer'],
            [['ip'], 'string', 'max' => 15],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'ip' => 'Ip',
            'action' => 'Action',
            'user_id' => 'User ID',
            'created_at' => 'Created At',
        ];
    }

    /**
     * Validates given ip
     * @param type $ip
     * @return boolean
     */
    public static function validateIp($ip) {
        $ipArr = explode('.', $ip);
        foreach ($ipArr as $key => $ipBlock) {
            if (strlen($ipBlock) == 2) {
                $ipArr[$key] = "0" . $ipBlock;
            } else if (strlen($ipBlock) == 1) {
                $ipArr[$key] = "00" . $ipBlock;
            }
        }
        $ip = implode(".", $ipArr);
        $ipPattern = preg_replace("/(\d)/", "[$1|0]", $ip);
        $blockResult = self::find()->where("ip REGEXP '$ipPattern'")->andWhere(['action' => self::ACTION_BLOCK])->count();
        $allowResult = self::find()->where("ip REGEXP '$ipPattern'")->andWhere(['action' => self::ACTION_ALLOW])->count();
        return ($allowResult && !$blockResult || (!$allowResult && !$blockResult));
    }

}
