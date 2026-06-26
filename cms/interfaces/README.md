[Русская версия](README.ru.md)

# PHP Interfaces Directory

This directory is intended for storing PHP interfaces used by the Eleanor CMS.

## Autoloader behavior

The autoloader serves only the `CMS\Interfaces` namespace. When an interface is requested, the autoloader converts the interface name into a file name by transforming it to lowercase and additionally checking its kebab-case variant. For example, requesting:

```php
CMS\Interfaces\TestInterface
```

will cause the autoloader to sequentially check:

```text
testinterface.php
test-interface.php
```

Subdirectories are supported. Each subdirectory represents an additional namespace level inside `CMS\Interfaces`. For example, requesting:

```php
CMS\Interfaces\Test\SomeFeatureInterface
```

will make the autoloader search for:

```text
./test/somefeatureinterface.php
./test/some-feature-interface.php
```

---

## Rules and requirements

### 1. File naming

The autoloader supports both naming styles:

```text
testinterface.php
test-interface.php
```

File names are case-sensitive and must be written in lowercase.

### 2. Namespace and interface name independence

The autoloader resolves interfaces exclusively by file name. The actual namespace and interface name declared inside the file do not participate in file resolution and may therefore be arbitrary. This allows the directory to be used as an adapter layer for third-party libraries or external interfaces.

### 3. Returning fully qualified interface names (FQCN)

If the interface declared inside the file does not match the expected interface name inside `CMS\Interfaces`, the file must return the fully qualified interface name (FQCN):

```php
return \Vendor\Package\SomeInterface::class;
```

Example:

```php
<?php
namespace Vendor\Package;

interface SomeInterface
{
    public function execute():void;
}

return \Vendor\Package\SomeInterface::class;
```

After loading the file, the autoloader will:

- use the interface directly if it already belongs to the `CMS\Interfaces` namespace;
- otherwise automatically create an alias inside `CMS\Interfaces`.

### 4. Automatic alias creation

Interfaces are not required to belong to the `CMS\Interfaces` namespace. If a file returns an FQCN from another namespace, the autoloader automatically creates an alias inside `CMS\Interfaces`, providing access to the interface through a unified autoloading mechanism. For example:

```php
CMS\Interfaces\Test\SomeFeatureInterface
```

may internally refer to:

```php
\External\Library\FeatureInterface
```

### 5. Optional `return` statement

If the interface is already declared in the expected namespace and its name matches the file name, the file may omit the `return` statement. For example, the file:

```text
test/some-feature-interface.php
```

may directly contain:

```php
<?php
namespace CMS\Interfaces\Test;

interface SomeFeatureInterface
{
    public function execute():void;
}
```

In this case the autoloader will resolve the interface automatically without requiring an explicit FQCN return value.