SkeekS CMS export
===================================

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist skeeks/cms-export "*"
```

or add

```
"skeeks/cms-export": "*"
```

Configuration app
----------

```php

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
            'class'         => 'skeeks\cms\export\ExportModule',
        ]
    ]

```

##Links
* [Web site](http://en.cms.skeeks.com)
* [Web site (rus)](http://cms.skeeks.com)
* [Author](http://skeeks.com)
* [ChangeLog](https://github.com/skeeks-cms/cms-export/blob/master/CHANGELOG.md)


___

> [![skeeks!](https://skeeks.com/img/logo/logo-no-title-80px.png)](https://skeeks.com)  
<i>SkeekS CMS (Yii2) — fast, simple, effective!</i>  
[skeeks.com](https://skeeks.com) | [cms.skeeks.com](https://cms.skeeks.com)