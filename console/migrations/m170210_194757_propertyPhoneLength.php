<?php

use yii\db\Migration;

class m170210_194757_propertyPhoneLength extends Migration {

    public function up() {
        //  Fix bug with phone length
        $this->alterColumn('{{%property}}', 'phone1', $this->string(50)->notNull());
    }

    public function down() {
        $this->alterColumn('{{%property}}', 'phone1', $this->string(5)->notNull());
        return true;
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
