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
        ],
        
        'authManager' => [
            'config' => [
                'roles'       => [
                    [
                        'name'  => \skeeks\cms\rbac\CmsManager::ROLE_ADMIN,
                        'child' => [
                            'permissions' => [
                                "cmsExport/admin-export-task",
                            ],
                        ],
                    ],
                ],
                'permissions' => [
                    [
                        'name'        => 'cmsExport/admin-export-task',
                        'description' => "Импорт",
                    ],
                ],
            ],
        ],
    ],

    'modules' =>
    [
        'cmsExport' => [
            'class'         => 'skeeks\cms\export\ExportModule',
        ]
    ]
];