<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "camera_log".
 *
 * @property integer $id
 * @property integer $camera_id
 * @property string $status
 * @property integer $created_at
 * @property Camera $camera
 */
class CameraLog extends \yii\db\ActiveRecord {
    
    const STATUS_ONLINE = "online";
    const STATUS_OFFLINE = "offline";

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'camera_log';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['created_at'], 'default', 'value' => time()],
            [['camera_id', 'status', 'created_at'], 'required'],
            [['camera_id', 'created_at'], 'integer'],
            [['status'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'camera_id' => 'Camera ID',
            'status' => 'Status',
            'created_at' => 'Created At',
        ];
    }
    
    /**
     * Creates new camera status log
     * 
     * @param type $cameraId
     * @param type $status
     * @return type
     */
    public static function addLog($cameraId, $status){
        $model = new self();
        $model->camera_id = $cameraId;
        $model->status = $status;
        return $model->save();
    }
    
    /**
     * Returns associated camera
     * 
     * @return Camera
     */
    public function getCamera(){
        return $this->hasOne(Camera::className(), ['id' => 'camera_id']);
    }

}
