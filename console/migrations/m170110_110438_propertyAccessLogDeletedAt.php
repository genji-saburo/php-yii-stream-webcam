<?php

use yii\db\Migration;

class m170110_110438_propertyAccessLogDeletedAt extends Migration {

    public function up() {
        $this->addColumn('{{%property_access_log}}', 'deleted_at', $this->integer());
    }

    public function down() {
        if ($this->dropColumn('{{%property_access_log}}', 'deleted_at'))
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
