<?php

use yii\db\Migration;

class m161226_082645_ARModelDeleteStatusUpdates extends Migration {

    public function up() {
        $this->addColumn('{{%camera}}', 'deleted_at', $this->integer());
        $this->addColumn('{{%property}}', 'deleted_at', $this->integer());
        $this->addColumn('{{%user}}', 'deleted_at', $this->integer());
    }

    public function down() {
        if ($this->dropColumn('{{%camera}}', 'deleted_at'))
            return false;
        if ($this->dropColumn('{{%property}}', 'deleted_at'))
            return false;
        if ($this->dropColumn('{{%user}}', 'deleted_at'))
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
