[Русская версия](README.ru.md)

# Abstract PHP Classes Directory

This directory is intended for storing abstract PHP classes used by the Eleanor CMS.

## Autoloader behavior

The autoloader serves only the `CMS\Abstracts` namespace. When a class is requested, the autoloader converts the class name into a file name by transforming it to lowercase and additionally checking its kebab-case variant. For example, requesting:

```php
CMS\Abstracts\TestClass
```

will cause the autoloader to sequentially check:

```text
testclass.php
test-class.php
```

Subdirectories are supported. Each subdirectory represents an additional namespace level inside `CMS\Abstracts`. For example, requesting:

```php
CMS\Abstracts\Test\SomeFeature
```

will make the autoloader search for:

```text
./test/somefeature.php
./test/some-feature.php
```

---

## Rules and requirements

### 1. File naming

The autoloader supports both naming styles:

```text
testclass.php
test-class.php
```

File names are case-sensitive and must be written in lowercase.

### 2. Namespace and class name independence

The autoloader resolves classes exclusively by file name. The actual namespace and class name declared inside the file do not participate in file resolution and may therefore be arbitrary. This allows the directory to be used as an adapter layer for third-party libraries or external abstract classes.

### 3. Returning fully qualified class names (FQCN)

If the class declared inside the file does not match the expected class name inside `CMS\Abstracts`, the file must return the fully qualified class name (FQCN):

```php
return \Vendor\Package\ClassName::class;
```

Example:

```php
<?php
namespace Vendor\Package;

abstract class ClassName
{
    // ...
}

return \Vendor\Package\ClassName::class;
```

After loading the file, the autoloader will:
- use the class directly if it already belongs to the `CMS\Abstracts` namespace;
- otherwise automatically create an alias inside `CMS\Abstracts`.

### 4. Automatic alias creation

Classes are not required to belong to the `CMS\Abstracts` namespace. If a file returns an FQCN from another namespace, the autoloader automatically creates an alias inside `CMS\Abstracts`, providing access to the class through a unified autoloading mechanism. For example:

```php
CMS\Abstracts\Test\SomeFeature
```

may internally refer to:

```php
\External\Library\FeatureClass
```

### 5. Optional `return` statement

If the class is already declared in the expected namespace and its name matches the file name, the file may omit the `return` statement. For example, the file:

```text
test/some-feature.php
```

may directly contain:

```php
<?php
namespace CMS\Abstracts\Test;

abstract class SomeFeature
{
    // ...
}
```

In this case the autoloader will resolve the class automatically without requiring an explicit FQCN return value.