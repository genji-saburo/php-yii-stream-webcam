<?php

use yii\db\Migration;

class m161227_173006_userRoleFields extends Migration {

    public function up() {
        $this->addColumn('{{%user}}', 'role', $this->integer()->notNull());
    }

    public function down() {
        if ($this->dropColumn('{{%user}}', 'role'))
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
