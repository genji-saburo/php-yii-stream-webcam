<?php

use yii\db\Migration;

class m161222_172244_property extends Migration {

    public function up() {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%property}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(50)->notNull(),
            'pin_code' => $this->string(10)->notNull(),
            'phone1' => $this->string(5)->notNull(),
            'phone2' => $this->string(50)->notNull(),
            'phone3' => $this->string(50)->notNull(),
            'coord_lat' => $this->string(50)->notNull(),
            'coord_lng' => $this->string(50)->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
                ], $tableOptions);
    }

    public function down() {
        return $this->dropTable('{{%property}}');
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
