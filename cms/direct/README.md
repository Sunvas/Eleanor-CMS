### English
When alphanumeric request like `^[a-z\d\-_.]$` is received and there is no unit to match such slug, the file of the same name from this directory will be included.
For example, when requesting /example, the file cms/direct/example.php will be included.

### Русский язык
Когда на сайт поступает буквенно-цифровой запрос `^[a-z\d\-_.]$` и в системе отсутствует юнит для обработки этого запроса, управление будет передано одноимённому файлу из этого каталога.
Например, при запросе /example, обработка будет передана в файл cms/direct/example.php