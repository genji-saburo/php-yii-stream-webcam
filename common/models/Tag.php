<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "tag".
 *
 * @property integer $id
 * @property integer $tag_id
 * @property integer $property_id
 * @property string $username
 * @property string $phone
 * @property string $image
 * @property integer $access_level
 * @property string $access_interval
 */
class Tag extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tag';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['property_id'], 'required'],
            [['property_id', 'access_level'], 'integer'],
            [['username', 'access_interval'], 'string', 'max' => 255],
            [['tag_id', 'phone', 'image'], 'string', 'max' => 50],
            [['image'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg']
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
            'property_id' => 'Property',
            'username' => 'User Name',
            'phone' => 'User Phone Number',
            'image' => 'User Photo',
            'access_level' => 'Access Level',
            'access_interval' => 'Access Interval',
        ];
    }
    
    /**
     * Return associated record
     * @return Property
     */
    public function getProperty() {
        return $this->hasOne(Property::className(), ['id' => 'property_id']);
    }
	
	public function getTaglog() {
        return $this->hasMany(TagLog::className(), ['tag_id' => 'id']);
    }
    
    public static function values($name)
    {
        $data = [
            'access_level' => [1 => 'Any Time', 2 => 'Certain Time'],
        ];

        return $data[$name];
    }
}
