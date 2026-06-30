[Русский язык](README.ru.md)

This directory is the local [Composer](https://getcomposer.org) workspace for Eleanor CMS. Composer [installation commands](https://getcomposer.org/download/) should be run from this directory, and the downloaded `composer.phar` file should be placed here.

Composer will store its configuration files, lock file, downloaded packages, and generated autoloader here.

Example package installation:

```bash
cd cms/composer
php composer.phar require vendor/package
```

After that, Composer may create files and directories such as `composer.json`, `composer.lock`, and `vendor/` in this directory.

Installed packages can be loaded from CMS code through Composer autoloader:

```php
require CMS.'composer/vendor/autoload.php';
```