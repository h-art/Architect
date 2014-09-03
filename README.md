# Laravel Architect

Architect is an administration package for the [Laravel](http://laravel.com/) PHP framework. Think about Architect as somethink like [Sonata](http://sonata-project.org/) for Symfony2.

## Installation

### Step zero: include Architect

Include Architect in your `composer.json` file to use it. Just pick a version number from [Packagist](https://packagist.org/packages/hart/architect) and add a row in your "require" section. Done!

### Step one: update your composer.json

Your `composer.json` file must be edited to autoload your administration classes. We strongly recommend to use the [PSR-4](http://www.php-fig.org/psr/psr-4/) notation for autoloading your classes.

```
...
        "psr-4": {
            "Vendor\\Admin\\": "Vendor/Admin/"
        }
...
```

### Step two: add the service provider

Add the Architect service provider in your `app.php` file, as you can see from this example

```php
    'providers' => array(
        ...
        'Illuminate\Cache\CacheServiceProvider',
        ...
        'Hart\Architect\ArchitectServiceProvider',
    ),
```

*Vendor* and *Admin* can obviously changed depending on your needs.

### Step three: publish the package configuration

From your project's root directory use the command

    $ php artisan config:publish hart/architect

to export package configuration. The configuration will be copied in your `app/config/packages/hart/architect` directory, and the default name is `config.php`.

### Step four: edit the configuration

Now you will need to edit the previous file. The only two settings are

```
<?php

return array (

    // namespace for admin classes (add also in composer.json)
    'admin_classes_namespace' => 'Vendor\\Admin\\',

    // admin classes names
    'admin_classes' => array(
        'PostAdmin',
        'TagAdmin',
        'CommentAdmin'
    ),

);
```

Add as many Admin classes as you want.
