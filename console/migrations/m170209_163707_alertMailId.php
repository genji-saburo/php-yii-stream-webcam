<?php

use yii\db\Migration;

class m170209_163707_alertMailId extends Migration {

    public function up() {
        $this->addColumn('{{%alert}}', 'mail_id', $this->integer());
    }

    public function down() {
        return $this->dropColumn('{{%alert}}', 'mail_id');
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
