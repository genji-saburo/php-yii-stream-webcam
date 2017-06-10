<?php

use yii\db\Migration;

class m170227_081732_cameraLog extends Migration {

    public function up() {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%camera_log}}', [
            'id' => $this->primaryKey(),
            'camera_id' => $this->integer()->notNull(),
            'status' => $this->string(50)->notNull(),
            'created_at' => $this->integer()->notNull(),
                ], $tableOptions);
    }

    public function down() {
        return $this->dropTable('{{%camera_log}}');
    }

    /*
      // Use safeUp/safeDown to run migration code within a transaction
      public function safeUp()
      {
      }

      public function safeDown()
      {
      }
     */
}
