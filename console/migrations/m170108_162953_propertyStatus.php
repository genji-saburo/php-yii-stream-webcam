<?php

use yii\db\Migration;

class m170108_162953_propertyStatus extends Migration {

    public function up() {
        $this->addColumn('{{%property}}', 'security_status', $this->integer());
    }

    public function down() {
        if ($this->dropColumn('{{%property}}', 'security_status'))
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
