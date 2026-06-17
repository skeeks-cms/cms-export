<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 15.04.2016
 */
return
[
    'exportImport' =>
    [
        "label"     => \Yii::t('skeeks/export', "Export / Import"),

        'priority'  => 400,

        'items' =>
        [
            "export" =>
            [
                "label"     => \Yii::t('skeeks/export', "Export"),
                "img"       => ['\skeeks\cms\assets\CmsAsset', 'images/icons/admin-menu/export-import.svg'],
                "url"       => ["cmsExport/admin-export-task"],
            ],
        ]
    ]
];
