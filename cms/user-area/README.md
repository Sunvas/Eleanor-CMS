### English
This directory stores template files for userspace of the site (HTML markup with variables). Description of template formats with examples is available [here](https://github.com/Sunvas/eleanor-php-library-examples/tree/main/2-templates-engine).
Files located directly inside this directory are file templates. Subdirectories:
* `includes` - files included directly by templates;
* `l10n` - localization files. Format is described in the constructor of class `./cms/library/classes/l10n.php`;
* `unit-*` - directories with unit templates;

### Русский язык
В этом каталоге хранится шаблонизатор пользовательской части сайта (HTML разметка с переменными). Описание форматов шаблонизатора с примерами доступно [здесь](https://github.com/Sunvas/eleanor-php-library-examples/tree/main/2-templates-engine).
Файлы расположенные непосредственно внутри этого каталога реализуют шаблонизатор на файлах. Подкаталоги:
* `includes` - файлы, подключаемые напрямую шаблонами;
* `l10n` - языковые файлы. Формат описан в конструкторе класса `./cms/library/classes/l10n.php`;
* `unit-*` - каталоги с шаблонами юнитов;