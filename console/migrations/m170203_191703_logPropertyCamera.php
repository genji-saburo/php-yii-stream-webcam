<?php

use yii\db\Migration;

class m170203_191703_logPropertyCamera extends Migration {

    public function up() {
        $this->addColumn('{{%log}}', 'camera_id', $this->integer());
        $this->addColumn('{{%log}}', 'property_id', $this->integer());
        $this->addColumn('{{%log}}', 'alert_id', $this->integer());
    }

    public function down() {
        $this->dropColumn('{{%log}}', 'camera_id');
        $this->dropColumn('{{%log}}', 'property_id');
        $this->dropColumn('{{%log}}', 'alert_id');
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
