<?php

use yii\db\Migration;

/**
 * Handles adding tag_id to table `tag`.
 * Has foreign keys to the tables:
 *
 * - `property`
 */
class m170601_123318_add_tag_id_column_to_tag_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->dropColumn('tag', 'tag_id');
        $this->addColumn('tag', 'tag_id', $this->string(50));

        // creates index for column `property_id`
        $this->createIndex(
            'idx-tag-property_id',
            'tag',
            'property_id'
        );

        // add foreign key for table `property`
        $this->addForeignKey(
            'fk-tag-property_id',
            'tag',
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
            'fk-tag-property_id',
            'tag'
        );

        // drops index for column `property_id`
        $this->dropIndex(
            'idx-tag-property_id',
            'tag'
        );

        $this->dropColumn('tag', 'tag_id');
        $this->dropColumn('tag', 'property_id');
    }
}
