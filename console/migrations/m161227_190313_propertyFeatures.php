<?php

use yii\db\Migration;

class m161227_190313_propertyFeatures extends Migration {

    public function up() {
        $this->addColumn('{{%property}}', 'owner_name', $this->string(70)->notNull());
        $this->addColumn('{{%property}}', 'phone_police', $this->string(30)->notNull());
        $this->addColumn('{{%property}}', 'address', $this->string(100)->notNull());
    }

    public function down() {
        if ($this->dropColumn('{{%property}}', 'owner_name'))
            return false;
        if ($this->dropColumn('{{%property}}', 'phone_police'))
            return false;
        if ($this->dropColumn('{{%property}}', 'address'))
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
