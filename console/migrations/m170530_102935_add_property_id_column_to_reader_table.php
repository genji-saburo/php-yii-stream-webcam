<?php

use yii\db\Migration;

/**
 * Handles adding propery_id to table `reader`.
 */
class m170530_102935_add_property_id_column_to_reader_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->dropColumn('reader', 'property_id');
        $this->addColumn('reader', 'property_id', $this->integer());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('reader', 'property_id');
    }
}
