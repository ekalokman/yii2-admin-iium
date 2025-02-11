RBAC Manager for Yii 2
======================
GUI manager for RBAC (Role Base Access Control) Yii2. Easy to manage authorization of user :smile:.

[![Latest Unstable Version](https://poser.pugx.org/ekalokman/yii2-admin-iium/v/unstable)](https://packagist.org/packages/ekalokman/yii2-admin-iium)
[![Total Downloads](https://poser.pugx.org/ekalokman/yii2-admin-iium/downloads.png)](https://packagist.org/packages/ekalokman/yii2-admin-iium)
[![Daily Downloads](https://poser.pugx.org/ekalokman/yii2-admin-iium/d/daily)](https://packagist.org/packages/ekalokman/yii2-admin-iium)
[![License](https://poser.pugx.org/ekalokman/yii2-admin-iium/license)](https://packagist.org/packages/ekalokman/yii2-admin-iium)
[![Reference Status](https://www.versioneye.com/php/ekalokman:yii2-admin-iium/reference_badge.svg)](https://www.versioneye.com/php/ekalokman:yii2-admin-iium/references)
[![Build Status](https://img.shields.io/travis/ekalokman/yii2-admin-iium.svg)](http://travis-ci.org/ekalokman/yii2-admin-iium)
[![Dependency Status](https://www.versioneye.com/php/ekalokman:yii2-admin-iium/dev-master/badge.png)](https://www.versioneye.com/php/ekalokman:yii2-admin-iium/dev-master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ekalokman/yii2-admin-iium/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ekalokman/yii2-admin-iium/?branch=master)
[![Code Climate](https://img.shields.io/codeclimate/github/ekalokman/yii2-admin-iium.svg)](https://codeclimate.com/github/ekalokman/yii2-admin-iium)

Documentation
-------------
> **Important: If you install version 3.x, please see [this readme](https://github.com/ekalokman/yii2-admin-iium/blob/3.master/README.md#upgrade-from-2x).**


- [Change Log](CHANGELOG.md).
- [Authorization Guide](http://www.yiiframework.com/doc-2.0/guide-security-authorization.html). Important, read this first before you continue.
- [Basic Configuration](docs/guide/configuration.md)
- [Basic Usage](docs/guide/basic-usage.md).
- [User Management](docs/guide/user-management.md).
- [Using Menu](docs/guide/using-menu.md).
- [Api](https://ekalokman.github.io/yii2-admin-iium/index.html).

Installation
------------

### Install With Composer

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require ekalokman/yii2-admin-iium "~1.0"
or
php composer.phar require ekalokman/yii2-admin-iium "~2.0"
```

or for the dev-master

```
php composer.phar require ekalokman/yii2-admin-iium "2.x-dev"
```

Or, you may add

```
"ekalokman/yii2-admin-iium": "~2.0"
```

to the require section of your `composer.json` file and execute `php composer.phar update`.

### Install From the Archive

Download the latest release from here [releases](https://github.com/ekalokman/yii2-admin-iium/releases), then extract it to your project.
In your application config, add the path alias for this extension.

```php
return [
    ...
    'aliases' => [
        '@mdm/admin' => 'path/to/your/extracted',
        // for example: '@mdm/admin' => '@app/extensions/mdm/yii2-admin-iium-2.0.0',
        ...
    ]
];
```

[**More...**](docs/guide/configuration.md)

[screenshots](https://goo.gl/r8RizT)
