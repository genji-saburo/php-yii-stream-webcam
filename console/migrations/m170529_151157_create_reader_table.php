<?php

use yii\db\Migration;

/**
 * Handles the creation of table `reader`.
 */
class m170529_151157_create_reader_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('reader', [
            'id' => $this->primaryKey(),
            'property_id' => $this->integer(1),
            'name' => $this->string(),
            'address' => $this->string(100),
            'type' => $this->integer(1),
            'serial_number' => $this->string(50),
            'status' => $this->integer(1),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('reader');
    }
}
