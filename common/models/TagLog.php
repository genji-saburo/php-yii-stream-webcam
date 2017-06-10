<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "tag_log".
 *
 * @property integer $id
 * @property integer $tag_id
 * @property integer $reader_id
 * @property integer $is_authorised
 * @property integer $created_at
 * @property integer $updated_at
 */
class TagLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tag_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tag_id', 'reader_id', 'is_authorised', 'created_at', 'updated_at'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tag_id' => 'Tag ID',
            'reader_id' => 'Reader ID',
            'is_authorised' => 'Is Authorised',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
    
    public static function saveData($data)
    {
        if(empty($data))
            return false;
        
        foreach($data as $d) {
            $tagLog = new static;
            $tagLog->tag_id = $d['tag_id'];
            $tagLog->reader_id = $d['reader_id'];
            $tagLog->is_authorised = $d['is_authorised'];
            $tagLog->created_at = $d['created_at'];
            $tagLog->updated_at = $d['updated_at'];
            $tagLog->save();
        }
        return true;
    }
}
