[English](README.md)

Этот каталог является локальной рабочей областью [Composer](https://getcomposer.org) для Eleanor CMS. [Команды установки](https://getcomposer.org/download/) Composer следует выполнять из этого каталога, а загруженный файл `composer.phar` размещать здесь.

Composer будет хранить здесь свои конфигурационные файлы, lock-файл, загруженные пакеты и сгенерированный автозагрузчик.

Пример установки пакета:

```bash
cd cms/composer
php composer.phar require vendor/package
```

После этого Composer может создать в этом каталоге файлы и каталоги вроде `composer.json`, `composer.lock` и `vendor/`.

Установленные пакеты можно подключить из кода CMS через автозагрузчик Composer:

```php
require CMS.'composer/vendor/autoload.php';
```