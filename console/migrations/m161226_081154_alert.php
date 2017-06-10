<?php

use yii\db\Migration;

class m161226_081154_alert extends Migration {

    public function up() {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%alert}}', [
            'id' => $this->primaryKey(),
            'type' => $this->string(50)->notNull(),
            'ip' => $this->string(15)->notNull(),            
            'user_id' => $this->integer(),
            'status' => $this->string(15)->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'deleted_at' => $this->integer(),
                ], $tableOptions);
    }

    public function down() {
        $this->dropTable('{{%alert}}');
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
