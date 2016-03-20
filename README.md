# Logger

The SlaxWeb\Logger component is only a simple wrapper around the Seldaek/monolog
library to provide an easier integration into the SlaxWeb\Framework. It provides
only a Service Provider for the Pimple Dependency Injection Container, and a
Factory for instantiation of the logger library. The Logger component also
depends on the Config component of the SlaxWeb\Framework.

Installation
============

Easiest method of installation is through composer, to install just edit your
composer.json file to contain:
```json
{
    "require": {
        "slaxweb/logger": "~0.2"
    }
}
```

Usage
=====

To instantiate the logger through the Factory you first need to instantiate the
Config component, as the Factory will require it to successfully instantiate the
logger. The Config component has to provide the following three configuration
items:
* logger.name - Used by Monolog as the name of the logger
* logger.loggerType - Used to determine which Monolog Logger Handler to use for
the logger, value needs to match the name of the Handler class, currently only
StreamHandler is supported
* logger.handlerArgs.<logger.loggerType> - Argument list that are going to be
passed to the Handler instantiation

When all of the above is set, you may safely call the **Factory::init** method,
and you will retrieve an instance of the logger in return.
```php
$logger = \SlaxWeb\Logger\Factory::init($config);
```

If you wish to use the Service Provider, then you need to make sure that the
Config components Service Provider is registered prior to trying to accessing
the logger definition. Of course, the above configuration items are required as
well. All that remains to do is to register the Service Provider of the logger,
and it will define the logger instantiation to the **logger.service** key name.
```php
$container->register(new \SlaxWeb\Logger\Service\Provider);
$container["logger.service"]->addInfo("Logger loaded");
```
