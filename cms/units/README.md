### English
This is a directory containing units (formerly modules) — components that implement logic of the website. Each unit consists of:
 * File named `unit_name.php` that must return an object that implements interfaces by which system determines the features of the unit. For example, if a unit is accessible from the user space, it must implement the `UserSpace` interface; if accessible from the dashboard, it must implement the `Dashboard` interface; if it should be run in the background - the `Cron` interface, etc. Since unit files can be included multiple times, it is recommended to keep them small and move all logic to a directory with the same name.
 * The `unit_name` directory is used to store sophisticated unit logic. Presence of this directory and including files from it is entirely at the discretion of the unit developer.

Access to the unit object can be obtained via same name dynamic property of the `$Shared` object.

All static files of the unit must be placed in the `static/unit_name` directory.

### Русский язык
Это каталог с юнитами (ранее модулями) - компонентами, реализующими логику работы сайта. Каждый юнит состоит из:
 * Файл вида `unit_name.php` который должен возвращать объект, который реализует интерфейсы по которым система определяет возможности юнита. Например, если юнит доступен из пользовательской части, они должен реализовать интерфейс `UserSpace`; если доступен из дашборда - должен реализовать интерфейс `Dashboard`; если должен запускаться в фоне - интерфейс `Cron` и т.п. Поскольку файлы юнитов могут включаются по несколько раз, рекомендуется делать их небольшими, вынося всю логику в одноимённый каталог.
 * Каталог `unit_name` для хранения сложной логики юнита. Наличие этого каталога и включение файлов из него остаётся полностью на усмотрение разработчика юнита.

Доступ к объекту юнита можно осуществить через одноимённое динамическое свойство объекта `$Shared`.

Все статические файлы юнита должны быть размещены в каталоге `static/unit_name`.