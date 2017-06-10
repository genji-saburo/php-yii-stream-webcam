<?php

use yii\db\Migration;

class m161222_154308_camera extends Migration {

    public function up() {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%camera}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(50)->notNull(),
            'address' => $this->string(100)->notNull(),
            'port' => $this->string(5)->notNull(),
            'login' => $this->string(50)->notNull(),
            'password' => $this->string(50)->notNull(),
            'serial_number' => $this->string(50)->notNull(),
            'property_id' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
                ], $tableOptions);
    }

    public function down() {
        return $this->dropTable('{{%camera}}');
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
