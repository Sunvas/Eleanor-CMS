[Русская версия](README.ru.md)

# PHP Enums Directory

This directory is intended for storing PHP enumerations (`enum`) used by the Eleanor CMS.

## Autoloader behavior

The autoloader serves only the `CMS\Enums` namespace. When an enumeration is requested, the autoloader converts the enumeration name into a file name by transforming it to lowercase and additionally checking its kebab-case variant. For example, requesting:

```php
CMS\Enums\TestEnum
```

will cause the autoloader to sequentially check:

```text
testenum.php
test-enum.php
```

Subdirectories are supported. Each subdirectory represents an additional namespace level inside `CMS\Enums`. For example, requesting:

```php
CMS\Enums\Test\SomeStatus
```

will make the autoloader search for:

```text
./test/somestatus.php
./test/some-status.php
```

---

## Rules and requirements

### 1. File naming

The autoloader supports both naming styles:

```text
testenum.php
test-enum.php
```

File names are case-sensitive and must be written in lowercase.

### 2. Namespace and enum name independence

The autoloader resolves enumerations exclusively by file name. The actual namespace and enumeration name declared inside the file do not participate in file resolution and may therefore be arbitrary. This allows the directory to be used as an adapter layer for third-party libraries or external enumerations.

### 3. Returning fully qualified enum names (FQCN)

If the enumeration declared inside the file does not match the expected enumeration name inside `CMS\Enums`, the file must return the fully qualified class name (FQCN):

```php
return \Vendor\Package\Status::class;
```

Example:

```php
<?php
namespace Vendor\Package;

enum Status: string
{
    case ACTIVE='ACTIVE';
    case DISABLED='DISABLED';
}

return \Vendor\Package\Status::class;
```

After loading the file, the autoloader will:
- use the enumeration directly if it already belongs to the `CMS\Enums` namespace;
- otherwise automatically create an alias inside `CMS\Enums`.

### 4. Automatic alias creation

Enumerations are not required to belong to the `CMS\Enums` namespace. If a file returns an FQCN from another namespace, the autoloader automatically creates an alias inside `CMS\Enums`, providing access to the enumeration through a unified autoloading mechanism. For example:

```php
CMS\Enums\Test\SomeStatus
```

may internally refer to:

```php
\External\Library\StatusEnum
```

### 5. Optional return statement

If the enumeration is already declared in the expected namespace and its name matches the file name, the file may omit the `return` statement. For example, the file:

```text
test/some-status.php
```

may directly contain:

```php
<?php
namespace CMS\Enums\Test;

enum SomeStatus: string
{
    case ACTIVE='ACTIVE';
}
```

In this case the autoloader will resolve the enumeration automatically without requiring an explicit FQCN return value.