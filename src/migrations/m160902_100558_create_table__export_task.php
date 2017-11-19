<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 30.08.2016
 */
use yii\db\Schema;
use yii\db\Migration;

class m160902_100558_create_table__export_task extends Migration
{
    public function safeUp()
    {
        $tableExist = $this->db->getTableSchema("{{%export_task}}", true);
        if ($tableExist)
        {
            return true;
        }

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable("{{%export_task}}", [
            'id'                    => $this->primaryKey(),

            'created_by'            => $this->integer(),
            'updated_by'            => $this->integer(),

            'created_at'            => $this->integer(),
            'updated_at'            => $this->integer(),

            'type'                  => $this->string(20)->comment('Export type (csv, xml)'),

            'name'                  => $this->string(255)->comment('Name'),
            'description'           => $this->text()->comment('description'),

            'component'             => $this->string(255)->notNull(),
            'component_settings'    => $this->text(),

        ], $tableOptions);

        $this->createIndex('export_task__updated_by', '{{%export_task}}', 'updated_by');
        $this->createIndex('export_task__created_by', '{{%export_task}}', 'created_by');
        $this->createIndex('export_task__created_at', '{{%export_task}}', 'created_at');
        $this->createIndex('export_task__updated_at', '{{%export_task}}', 'updated_at');

        $this->createIndex('name', '{{%export_task}}', 'name');

        $this->addForeignKey(
            'export_task__created_by', "{{%export_task}}",
            'created_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );

        $this->addForeignKey(
            'export_task__updated_by', "{{%export_task}}",
            'updated_by', '{{%cms_user}}', 'id', 'SET NULL', 'SET NULL'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey("export_task__created_by", "{{%export_task}}");
        $this->dropForeignKey("export_task__updated_by", "{{%export_task}}");

        $this->dropTable("{{%export_task}}");
    }
}