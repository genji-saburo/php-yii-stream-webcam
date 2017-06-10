<?php

use yii\db\Migration;

class m170216_074432_cameraAuthType extends Migration {

    public function up() {
        $this->addColumn('{{%camera}}', 'auth_type', $this->string(20));
    }

    public function down() {
        if ($this->dropColumn('{{%camera}}', 'auth_type'))
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
