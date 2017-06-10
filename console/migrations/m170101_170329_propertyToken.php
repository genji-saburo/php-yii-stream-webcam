<?php

use yii\db\Migration;

class m170101_170329_propertyToken extends Migration {

    public function up() {
        $this->addColumn('{{%property}}', 'auth_key', $this->string(50));
    }

    public function down() {
        if ($this->dropColumn('{{%property}}', 'auth_key'))
            return false;
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
