<?php

use yii\db\Migration;

class m170222_204121_alertRawJson extends Migration {

    public function up() {
        $this->addColumn('{{%alert}}', 'raw_json', $this->text());
    }

    public function down() {
        return $this->dropColumn('{{%alert}}', 'raw_json');
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
