<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */

use yii\db\Migration;

class m200701_111000__update_data__export_task extends Migration
{
    public function safeUp()
    {
        $subQuery = $this->db->createCommand("
            UPDATE 
                `export_task` as c
            SET 
                c.cms_site_id = (select cms_site.id from cms_site where cms_site.is_default = 1)
        ")->execute();
    }

    public function safeDown()
    {
        echo "m200701_111000__update_data__export_task cannot be reverted.\n";
        return false;
    }
}