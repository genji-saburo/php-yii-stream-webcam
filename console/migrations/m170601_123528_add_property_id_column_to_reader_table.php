<?php

use yii\db\Migration;

/**
 * Handles adding property_id to table `reader`.
 * Has foreign keys to the tables:
 *
 * - `property`
 */
class m170601_123528_add_property_id_column_to_reader_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        // creates index for column `property_id`
        $this->createIndex(
            'idx-reader-property_id',
            'reader',
            'property_id'
        );

        // add foreign key for table `property`
        $this->addForeignKey(
            'fk-reader-property_id',
            'reader',
            'property_id',
            'property',
            'id',
            'CASCADE'
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        // drops foreign key for table `property`
        $this->dropForeignKey(
            'fk-reader-property_id',
            'reader'
        );

        // drops index for column `property_id`
        $this->dropIndex(
            'idx-reader-property_id',
            'reader'
        );

        $this->dropColumn('reader', 'property_id');
    }
}
