<?php

use yii\db\Migration;

/**
 * Handles the creation of table `tag_log`.
 */
class m170601_122701_create_tag_log_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('tag_log', [
            'id' => $this->primaryKey(),
            'tag_id' => $this->integer(),
            'reader_id' => $this->integer(),
            'is_authorised' => $this->integer(1),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('tag_log');
    }
}
