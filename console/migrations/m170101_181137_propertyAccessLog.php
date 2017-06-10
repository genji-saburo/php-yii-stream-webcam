<?php

use yii\db\Migration;

class m170101_181137_propertyAccessLog extends Migration {

    public function up() {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%property_access_log}}', [
            'id' => $this->primaryKey(),
            'property_id' => $this->integer(),
            'check_result' => $this->boolean(),
            'pin_code' => $this->string(10),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
                ], $tableOptions);
    }

    public function down() {
        return $this->dropTable('{{%property_access_log}}');
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
