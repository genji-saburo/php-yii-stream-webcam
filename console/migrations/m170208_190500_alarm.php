<?php

use yii\db\Migration;

class m170208_190500_alarm extends Migration {

    public function up() {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%alarm}}', [
            'id' => $this->primaryKey(),
            'details' => $this->string(500)->notNull(),
            'type' => $this->string(50)->notNull(),
            'user_id' => $this->integer(),
            'alert_id' => $this->integer(),
            'status' => $this->string(10),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'deleted_at' => $this->integer(),
                ], $tableOptions);
    }

    public function down() {
        return $this->dropTable('{{%alarm}}');
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
