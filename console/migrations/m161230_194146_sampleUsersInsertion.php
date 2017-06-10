<?php

use yii\db\Migration;

class m161230_194146_sampleUsersInsertion extends Migration {

    public function up() {
        /*  Default password: password1 */
        $this->delete('{{%user}}', ['id' => 1]);
        $this->insert('{{%user}}', [
            'id' => '1',
            'username' => 'admin',
            'auth_key' => 'vJPDhYtzKp7pGBlPjx6jXUq7BF09CCw2',
            'password_hash' => '$2y$13$kDa52p111eOm2hEzVsctg.sSICYn7Pc2FApc60IpovD7QWkuDa6Se',
            'email' => Yii::$app->params['adminEmail'],
            'status' => '10',
            'role' => '100',
            'created_at' => '1471086410',
            'updated_at' => '1471086410'
        ]);
    }

    public function down() {
        return $this->delete('{{%user}}', ['id' => 1]);
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
