<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 15.04.2016
 */
return
[
    'exportExport' =>
    [
        "label"     => \Yii::t('skeeks/export', "Export / Import"),
        "img"       => ['\skeeks\cms\export\assets\ExportAsset', 'icons/export.png'],

        'priority'  => 400,

        'items' =>
        [
            "export" =>
            [
                "label"     => \Yii::t('skeeks/export', "Export"),
                "img"       => ['\skeeks\cms\export\assets\ExportAsset', 'icons/export.png'],
                "url"       => ["cmsExport/admin-export-task"],

                'items' =>
                [
                    [
                        "label"     => \Yii::t('skeeks/export', "All kinds of exports"),
                        "img"       => ['\skeeks\cms\export\assets\ExportAsset', 'icons/export.png'],
                        "url"       => ["cmsExport/admin-export-task"],
                    ]
                ],
            ],
        ]
    ]
];