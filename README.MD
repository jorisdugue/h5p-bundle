# H5PBundle
Bundle to integrate H5P into Symfony. This bundle is a port of the H5P Drupal module. For more info about H5P see [H5P.org](https://h5p.org)

For Symfony 3.X => [H5PBundle for Symfony 2.X and 3.X](https://github.com/studit/h5p-bundle/tree/193db01f03584a131b011e7ab8ed9973b1496b6d)

This bundle was tested on Symfony 4.0 and Symfony 5.0

Installation
------------

Install with composer
``` bash
composer require studit/h5p-bundle
```

Enable the bundle in `AppKernel.php`
``` php
$bundles = array(
    // ...
    new Studit\H5PBundle\StuditH5PBundle(),
)
```
**Warning for Windows** : You need launch the cmd in administrator

Add the H5P assets to the bundle
``` bash
php bin/console h5p-bundle:IncludeAssetsCommand
php bin/console assets:install --symlink
```
and reexcute ...
```bash
php bin/console h5p-bundle:IncludeAssetsCommand
```
  

Add required tables and relations to the database
``` bash
php app/console doctrine:schema:update --force 
```
or 
````bash
php app/console make:migrations
````

Enable the routing in `routing.yml`
``` yaml
studit_h5p.demo:
    resource: "@StuditH5PBundle/Resources/config/routing_demo.yml"
    prefix:   /

studit_h5p:
    resource: "@StuditH5PBundle/Resources/config/routing.yml"
    prefix:   /
```

studit_h5p.demo is optional. It can be used as an example how to use H5P within Symfony and test if this bundle is working properly.

Configuration
-------------

Configure the bundle in `config.yml`. (Watch for the underscore between h5 and p)
``` yml
studit_h5_p:
    use_permission: true # This is false by default to let the demo work out of the box.
    storage_dir: h5p # Location to store all H5P libraries and files
    web_dir: public # Location of the public web directory
```
For all configurations see [Configuration.php](DependencyInjection/Configuration.php)

Usage
-------------

First add a virtual host that points to you project. Then in your browser go to `http://<your virtualhost>/h5p/list`

Todo
-------------

Not everything is ported yet. The following things still need to be done:
* Upload library. Currently only H5P default libraries can be selected from Hub.
* Download package
* Store usage data and points