<?php
return [

    
    'components' =>
    [
        'cmsAgent' => ['jobTargets' => ['export.execute' => \skeeks\cms\export\jobs\ExportScheduleTarget::class]],
        'jobQueueFactory' => ['queues' => ['exports' => []]],
        'jobRegistry' => ['types' => [
            'export.execute' => [
                'type' => 'export.execute',
                'title' => 'Экспорт',
                'handler' => \skeeks\cms\export\jobs\ExportJobHandler::class,
                'queue' => 'exports',
                'permission' => \skeeks\cms\rbac\CmsManager::PERMISSION_ROLE_ADMIN_ACCESS,
                'overlapPolicy' => 'skip',
                'resourceKey' => [\skeeks\cms\export\jobs\ExportJobHandler::class, 'resource'],
                'dedupKey' => [\skeeks\cms\export\jobs\ExportJobHandler::class, 'key'],
                'timeout' => 7200,
                'leaseSeconds' => 180,
                'maxAttempts' => 1,
            ],
        ]],
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
