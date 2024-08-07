# WHDoctrineUtilityBundle
 A Symfony bundle which handles configuration of my `willherzog/doctrine-generic` library and, by default, enables a listener to Symfony's `kernel.response` event which allows for "lazy" flushing of the Doctrine entity manager.

 To use the "lazy" flushing feature, set an attribute on the *main* Symfony `Request` object (because only the main request is supported) which uses `WHDoctrineUtilityBundle::REQUEST_ATTR_FLUSH_REQUIRED` as its name/key and boolean `true` as its value.

 If you're using a Doctrine entity manager other than the default one, set another `Request` attribute using `WHDoctrineUtilityBundle::REQUEST_ATTR_ENTITY_MANAGER` as the name/key and an instance of `Doctrine\ORM\EntityManagerInterface` as the value.
 (For now only one entity manager can be specified, but I'll consider adding support for specifying more than one at some point—if I do, it will probably be done based on the FQCN for a given entity.)


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

If you need to disable this bundle's response listener entirely (e.g. for testing), you can do so as follows:

```yaml
# config/packages/wh_doctrine.yaml

wh_doctrine:
    enable_kernel_response_listener: false
```