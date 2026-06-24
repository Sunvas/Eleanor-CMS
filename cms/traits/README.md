[Русская версия](README.ru.md)

# PHP Traits Directory

This directory is intended for storing PHP traits used by the Eleanor CMS.

## Autoloader behavior

The autoloader serves only the `CMS\Traits` namespace. When a trait is requested, the autoloader converts the trait name into a file name by transforming it to lowercase and additionally checking its kebab-case variant. For example, requesting:

```php
CMS\Traits\TestTrait
```

will cause the autoloader to sequentially check:

```text
testtrait.php
test-trait.php
```

Subdirectories are supported. Each subdirectory represents an additional namespace level inside `CMS\Traits`. For example, requesting:

```php
CMS\Traits\Test\SomeFeatureTrait
```

will make the autoloader search for:

```text
./test/somefeaturetrait.php
./test/some-feature-trait.php
```

---

## Rules and requirements

### 1. File naming

The autoloader supports both naming styles:

```text
testtrait.php
test-trait.php
```

File names are case-sensitive and must be written in lowercase.

### 2. Namespace and trait name independence

The autoloader resolves traits exclusively by file name. The actual namespace and trait name declared inside the file do not participate in file resolution and may therefore be arbitrary. This allows the directory to be used as an adapter layer for third-party libraries or external traits.

### 3. Returning fully qualified trait names (FQCN)

If the trait declared inside the file does not match the expected trait name inside `CMS\Traits`, the file must return the fully qualified class name (FQCN):

```php
return \Vendor\Package\SomeTrait::class;
```

Example:

```php
<?php
namespace Vendor\Package;

trait SomeTrait
{
    protected function helper():void
    {
        // ...
    }
}

return \Vendor\Package\SomeTrait::class;
```

After loading the file, the autoloader will:

- use the trait directly if it already belongs to the `CMS\Traits` namespace;
- otherwise automatically create an alias inside `CMS\Traits`.

### 4. Automatic alias creation

Traits are not required to belong to the `CMS\Traits` namespace. If a file returns an FQCN from another namespace, the autoloader automatically creates an alias inside `CMS\Traits`, providing access to the trait through a unified autoloading mechanism. For example:

```php
CMS\Traits\Test\SomeFeatureTrait
```

may internally refer to:

```php
\External\Library\FeatureTrait
```

### 5. Optional `return` statement

If the trait is already declared in the expected namespace and its name matches the file name, the file may omit the `return` statement. For example, the file:

```text
test/some-feature-trait.php
```

may directly contain:

```php
<?php
namespace CMS\Traits\Test;

trait SomeFeatureTrait
{
    protected function helper():void
    {
        // ...
    }
}
```

In this case the autoloader will resolve the trait automatically without requiring an explicit FQCN return value.