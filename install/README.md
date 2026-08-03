# System installer

[Русская версия](README.ru.md)

This directory contains the Eleanor CMS installer.

The system can be installed:

- through a web browser using `index.php`;
- from the command line using `cli.php`.

## Browser installation

Open the `install` directory through a web browser and follow the installer instructions.

## CLI installation

Copy the sample configuration to `install.json` and adjust its values:

```shell
cp install.sample.json install.json
```

> [!WARNING]
> The installer drops and recreates existing database tables with the same names. Any data stored in those tables will be lost.

Run the installer:

```shell
php cli.php install
```

The installer validates the configuration and system requirements before making any changes. If validation fails, the installation will not start.
By default, the installer uses `install.json`. A different configuration file may be specified as the second argument:

```shell
php cli.php install path/to/config.json
```

To display the license, run:

```shell
php cli.php license
```

### Configuration validation

The optional `dry-run` command performs the same validation without making any changes:

```shell
php cli.php dry-run
```

A different configuration file may also be specified:

```shell
php cli.php dry-run path/to/config.json
```

To display CLI usage information, run:

```shell
php cli.php help
```

## Configuration

The `install.sample.json` file contains a minimal installation configuration.

### MySQL

The `mysql` section defines the database connection:

- `host` — MySQL host. The `p:` prefix enables a persistent connection;
- `port` — MySQL port;
- `username` — MySQL username;
- `password` — MySQL password;
- `database` — database name.

A `null` value for `host`, `port`, `username`, or `password` causes the corresponding connection parameter to use its default value from `php.ini`.

### Administrator

The `admin` section defines the initial site administrator:

- `username` — administrator username;
- `password` — administrator password.

### Site

The `site` section defines the site title, description, primary language, and localization mode:

- `dir` — URL path of the site relative to the domain root;
- `title` — site title;
- `description` — site description;
- `l10n` — primary language code written to the `L10N` constant;
- `l10ns` — localization mode and additional language codes written to the `L10NS` constant.

The type and value of `l10ns` select one of three localization modes.

#### Strictly monolingual mode

Set `l10ns` to `null`:

```json
{
  "site": {
    "dir": "/",
    "title": "Site title",
    "description": "Site description",
    "l10n": "en",
    "l10ns": null
  }
}
```

In this mode:

- the site uses only the language specified by `l10n`;
- `title` and `description` must be strings;
- localized data is stored directly in the database;
- multilingual mode cannot be enabled later without a database migration.

#### Monolingual mode prepared for additional languages

Set `l10ns` to an empty array:

```json
{
  "site": {
    "dir": "/",
    "title": {
      "en": "Site title"
    },
    "description": {
      "en": "Site description"
    },
    "l10n": "en",
    "l10ns": []
  }
}
```

In this mode:

- the site initially uses only the language specified by `l10n`;
- no additional languages are initially enabled;
- `title` and `description` must be objects indexed by language code;
- localized data is stored as JSON objects;
- additional languages may be enabled later without changing the database storage format.

#### Multilingual mode

Set `l10ns` to an array containing the additional language codes:

```json
{
  "site": {
    "dir": "/",
    "title": {
      "ru": "Название сайта",
      "en": "Site title"
    },
    "description": {
      "ru": "Описание сайта",
      "en": "Site description"
    },
    "l10n": "ru",
    "l10ns": ["en"]
  }
}
```

In this mode:

- `l10n` specifies the primary language;
- `l10ns` lists the additional languages and must not include `l10n`;
- `title` and `description` must be objects indexed by language code;
- the primary and every additional language must have corresponding localized values.

The type of `l10ns` determines the database storage format. Do not manually change it between `null` and an array after installation without performing the required database migration.
The list of additional languages may be shortened. Before adding a new language, add the corresponding translation files and database fields.

### hCaptcha

The `hcaptcha` section defines the hCaptcha credentials:

- `sitekey` — hCaptcha sitekey;
- `secret` — hCaptcha secret.

## Command-line overrides

Configuration values may be overridden using the `--section-key=value` format:

```shell
php cli.php install --mysql-host=db.internal --mysql-password="secret"
```

Command-line options take precedence over values read from the JSON configuration file.
In a multilingual configuration, string values for `site.title` and `site.description` are applied to all enabled languages. This allows these values to be overridden from the command line without passing language-indexed objects.
When `--mysql-password` or `--admin-password` is specified without a value, the installer prompts for the password interactively.

## Reinstallation

The `install.lock` file blocks the installer after the system has been installed.
Delete it only when the system must be installed again:

```shell
rm install.lock
```

Removing this file does not undo the existing installation or delete its database data.

## After installation

Do not commit production passwords, hCaptcha secrets, or other sensitive installation data to a public repository.
After successful installation, it is recommended to delete this directory together with all its contents.