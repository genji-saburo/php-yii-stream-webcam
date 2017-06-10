<?php

use yii\db\Migration;

class m161228_161503_cameraImage extends Migration {

    public function up() {
        $this->addColumn('{{%camera}}', 'image', $this->string(50));
    }

    public function down() {
        if ($this->dropColumn('{{%camera}}', 'image'))
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
