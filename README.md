# WHDoctrineUtilityBundle
 A Symfony bundle which handles configuration of my `willherzog/doctrine-generic` library and, by default, enables a listener to Symfony's `kernel.response` event which allows for "lazy" flushing of the Doctrine entity manager. It also allows for automatic injection of the Doctrine entity manager into database migration classes which need it.

## Usage

### Lazy Flushing

 To use the "lazy" flushing feature, set an attribute on the *main* Symfony `Request` object (because only the main request is supported) which uses `WHDoctrineUtilityBundle::REQUEST_ATTR_FLUSH_REQUIRED` as its name/key and boolean `true` as its value:

```php
use Symfony\Component\HttpFoundation\RequestStack;
use WHSymfony\WHDoctrineUtilityBundle\WHDoctrineUtilityBundle;

/* ... */

/** @var RequestStack $requestStack */
$request = $requestStack->getMainRequest();

$request->attributes->set(WHDoctrineUtilityBundle::REQUEST_ATTR_FLUSH_REQUIRED, true);
```

 If you're using a Doctrine entity manager other than the default one, set another `Request` attribute using `WHDoctrineUtilityBundle::REQUEST_ATTR_ENTITY_MANAGER` as the name/key and an instance of `Doctrine\ORM\EntityManagerInterface` as the value.
 (For now only one entity manager can be specified, but I'll consider adding support for specifying more than one at some point—if I do, it will probably be done based on the FQCN for a given entity.)

### Entity-Manager-Aware Migrations

If you use Doctrine Migrations and need the Doctrine entity manager in your migration classes (e.g. for the optional `->postUp()`/`->postDown()` methods), simply implement this bundle's `EntityManagerAwareInterface` (and, optionally, the `EntityManagerAwareTrait`):

```php
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

use WHSymfony\WHDoctrineUtilityBundle\DependencyInjection\EntityManagerAwareInterface;
use WHSymfony\WHDoctrineUtilityBundle\DependencyInjection\EntityManagerAwareTrait;

final class Version20240812120000 extends AbstractMigration implements EntityManagerAwareInterface
{
	use EntityManagerAwareTrait;

    /* ... */
}
```

If you do make use of the above trait, your class will be able to access an instance of `Doctrine\ORM\EntityManagerInterface` via `$this->entityManager`.

## Installation

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Applications that use Symfony Flex

Open a command console, enter your project directory and execute:

```console
$ composer require willherzog/symfony-doctrine-utility-bundle
```

### Applications that don't use Symfony Flex

#### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require willherzog/symfony-doctrine-utility-bundle
```

#### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    WHSymfony\WHDoctrineUtilityBundle\WHDoctrineUtilityBundle::class => ['all' => true],
];
```

## Configuration

If you need to disable this bundle's response listener entirely (used for the "lazy" flushing feature), you can do so as follows:

```yaml
# config/packages/wh_doctrine.yaml

wh_doctrine:
    enable_kernel_response_listener: false
```

Similarly, to disable the entity-manager-aware migrations feature, do the following:

```yaml
# config/packages/wh_doctrine.yaml

wh_doctrine:
    enable_entity_manager_aware_migrations: false
```

However, if you don't have doctrine/migrations (or doctrine/doctrine-migrations-bundle) as a dependency of your project, this feature won't be enabled regardless of this setting.
