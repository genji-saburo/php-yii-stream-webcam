<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "reader".
 *
 * @property integer $id
 * @property integer $property
 * @property string $name
 * @property string $address
 * @property integer $type
 * @property string $serial_number
 * @property integer $status
 */
class Reader extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'reader';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['property_id'], 'required'],
            [['property_id', 'type', 'status'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['address'], 'string', 'max' => 100],
            [['serial_number'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'property_id' => 'Property',
            'name' => 'Name',
            'address' => 'Address',
            'type' => 'Type',
            'serial_number' => 'Serial Number',
            'status' => 'Status',
        ];
    }
	
    /**
     * Return associated record
     * @return Property
     */
    public function getProperty() {
        return $this->hasOne(Property::className(), ['id' => 'property_id']);
    }
	
    public static function values($name)
    {
        $data = [
            'status' => [1 => 'Enabled', 2 => 'Disabled'],
            'type' => [1 => 'Model1', 2 => 'Model2', 3 => 'Model3'],
        ];

        return $data[$name];
    }
}
