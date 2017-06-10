<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "comment".
 *
 * @property integer $id
 * @property string $camera_id
 * @property string $message
 * @property integer $user_id
 * @property integer $created_at
 * @property integer $updated_at
 * * @property integer $deleted_at
 * @property Camera $camera
 * @property User $user
 */
class Comment extends \common\models\ARModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'comment';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        $rulesArr = [
            [['camera_id', 'message',], 'required'],
            [['user_id', 'camera_id',], 'integer'],
            [['message'], 'string', 'max' => 255],
        ];
        return array_merge($rulesArr, parent::rules());
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'camera_id' => 'Camera ID',
            'message' => 'Message',
            'user_id' => 'User ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Returns associated camera
     * 
     * @return Camera
     */
    public function getCamera() {
        return $this->hasOne(Camera::className(), ['id' => 'camera_id']);
    }

    /**
     * Returns associated user
     * @return User
     */
    public function getUser() {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

}
