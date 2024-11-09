# H5PBundle

Bundle to integrate H5P into Symfony. This bundle is a port of the H5P Drupal module. For more info about H5P
see [H5P.org](https://h5p.org)

This bundle was tested on :

- Symfony 4.X
- Symfony 5.X,
- Symfony 6.X,
- Symfony 7.X-dev

| Version Supported | Symfony 3                                                                 | Symfony 4 | Symfony 5 | Symfony 6 | Symfony 7 |
|-------------------|---------------------------------------------------------------------------|-----------|-----------|-----------|-----------|
| 3.X               | &#x274C;                                                                  | &#x274C;  | &#x274C;  | &#x2705;  | &#x2705;  |
| 2.X               | &#x274C;                                                                  | &#x274C;  | &#x2705;  | &#x2705;  | &#x274C;  |
| 1.X               | [H5PBundle for Symfony 2.X and 3.X](https://github.com/Emmedy/h5p-bundle) | &#x2705;  | &#x2705;  | &#x274C;  | &#x274C;  |

Prerequisite
------------

You need install doctrine annotations and orm before running this installation

```bash
composer require orm
composer require doctrine/annotations
```

Installation
------------

Install with composer

``` bash
composer require jorisdugue/h5p-bundle
```

Enable the bundle in `AppKernel.php`

```php
return [
// ...
    \Studit\H5PBundle\StuditH5PBundle::class => ['all' => true]
]
```

**Warning for Windows** : You need launch the cmd in administrator or using option ``-c``

Add the H5P assets to the bundle

``` bash
php bin/console h5p-bundle:IncludeAssetsCommand
php bin/console assets:install --symlink
```

and reexecute ...

```bash
# For linux and mac only or windows admin
php bin/console h5p-bundle:IncludeAssetsCommand
# For all without admin perms
php bin/console h5p-bundle:IncludeAssetsCommand -c
```

Add required tables and relations to the database

``` bash
php bin/console doctrine:schema:update --force 
```

or

````bash
php bin/console make:migrations
````

Enable the routing in `routes.yaml`

```yaml
studit_h5p.demo:
  resource: "@StuditH5PBundle/Resources/config/routing_demo.yml"
  prefix: /

studit_h5p:
  resource: "@StuditH5PBundle/Resources/config/routes.yaml"
  prefix: /
```

studit_h5p.demo is optional. It can be used as an example how to use H5P within Symfony and test if this bundle is
working properly.

Configuration
-------------

For SF4:
Configure the bundle in `services.yaml`. (Watch for the underscore between h5 and p)

```yaml
parameters:
  studit_h5_p:
    use_permission: true # This is false by default to let the demo work out of the box.
    storage_dir: / # Location to store all H5P libraries and files
    web_dir: public # Location of the public web directory
    export: 3 #for all
    embed: 3 #for all
```

For Symfony 5:
Configure the bundle in `packages > h5pBundle.yml`.

```yaml
studit_h5_p:
  use_permission: true # This is false by default to let the demo work out of the box.
  storage_dir: / # Location to store all H5P libraries and files
  web_dir: public # Location of the public web directory
  export: 3 #for all
  embed: 3 #for all
```

For all configurations see [Configuration.php](DependencyInjection/Configuration.php)

if you are using permissions here is the current list of permissions in the bundle :

| Permission                               | Description                                                                                  |
|------------------------------------------|----------------------------------------------------------------------------------------------|
| ROLE_H5P_DOWNLOAD_ALL                    | Download the H5P packages                                                                    |
| ROLE_H5P_EMBED_ALL                       | Embed/Share to everyone the H5P                                                              |
| ROLE_H5P_CREATE_RESTRICTED_CONTENT_TYPES | Restrict for user the type install                                                           |
| ROLE_H5P_UPDATE_LIBRARIES                | Allow/Disallow the update of package H5P                                                     |
| ROLE_H5P_INSTALL_RECOMMENDED_LIBRARIES   | Allow/Disallow to install libraries                                                          |
| ROLE_H5P_COPY_ALL                        | Allow/Disallow to copy H5P (If you want to Download, you must install to have this role too) |

Usage
-------------

First add a virtual host that points to you project. Then in your browser go to `http://<your virtualhost>/h5p/list`

Todo
-------------
Working:

- Store usage data and points (only if user is connected)
- Download a H5P
- Upload H5P
- Update / Install H5P library
- Store usage data and points

Not everything is ported yet. The following things still need to be done:

* Upload library. Currently only H5P default libraries can be selected from Hub. (need custom h5p for testing )

Changelog:
-------------

- Using dev version with restrict tag for prevent Break Change
- Implement missing road and resolve compatibility of H5P-editor
- Fix bug and update the readme
- Fix many bug ... and update to SF5 :)
- Fix bug with missing link img
- Fix Download package
- Store usage data and points

Developing:
-------------
Run the static analyzer like that:

```sh
php -d memory_limit=-1 vendor/bin/phpstan.phar analyze .
```
