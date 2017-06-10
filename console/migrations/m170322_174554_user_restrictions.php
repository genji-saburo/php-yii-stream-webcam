<?php

use yii\db\Migration;

class m170322_174554_user_restrictions extends Migration {

    public function up() {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%user_restriction}}', [
            'id' => $this->primaryKey(),
            'ip' => $this->string(15)->notNull(),
            'action' => $this->integer()->notNull(),
            'user_id' => $this->integer(),
            'created_at' => $this->integer()->notNull(),
                ], $tableOptions);
    }

    public function down() {
        return $this->dropTable('{{%user_restriction}}');
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
