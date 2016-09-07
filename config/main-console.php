<?php
return [

    'components' =>
    [
        'cmsExport' => [
            'class'     => 'skeeks\cms\export\ExportComponent',
        ],

        'i18n' => [
            'translations' =>
            [
                'skeeks/export' => [
                    'class'             => 'yii\i18n\PhpMessageSource',
                    'basePath'          => '@skeeks/cms/export/messages',
                    'fileMap' => [
                        'skeeks/export' => 'main.php',
                    ],
                ]
            ]
        ]
    ],

    'modules' =>
    [
        'cmsExport' => [
            'class'                 => 'skeeks\cms\export\ExportModule',
            'controllerNamespace'   => 'skeeks\cms\export\console\controllers'
        ]
    ]
];