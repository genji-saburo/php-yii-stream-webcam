<?php

use yii\db\Migration;

/**
 * Handles the creation of table `tag`.
 */
class m170530_102405_create_tag_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('tag', [
            'id' => $this->primaryKey(),
            'tag_id' => $this->integer(11),
            'property_id' => $this->integer(11),
            'username' => $this->string(),
            'phone' => $this->string(50),
            'image' => $this->string(50),
            'access_level' => $this->integer(1),
            'access_interval' => $this->string(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('tag');
    }
}
