<?php
return [
    'controllerMap' => [
        'migrate' => [
            'migrationPath' => [
                '@skeeks/cms/export/migrations',
            ],
        ],
    ],
    
    'modules' =>
    [
        'cmsExport' => [
            'controllerNamespace'   => 'skeeks\cms\export\console\controllers'
        ]
    ]
];