<?php

use yii\db\Migration;

class m161228_122708_alertDetails extends Migration {

    public function up() {
        $this->addColumn('{{%alert}}', 'camera_id', $this->integer());
    }

    public function down() {
        if ($this->dropColumn('{{%alert}}', 'camera_id'))
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
