
NOrmGenerator for Nette/Database - alfa version
===============================================

```
        composer require zkrat/n-orm-generator:dev-master
```

file content of console.config.neon:

```
extensions:
        #	console2: Contributte\Console\DI\ConsoleExtension(%consoleMode%)
        n.orm.console: NOrmGenerator\Extension\NOrmExtension(%consoleMode%)

n.orm.console:
#require
        dbDir: %appDir%/model/nORM/DbAccess
        driverDir: %appDir%/model/nORM/meta
        entityDir: %appDir%/model/nORM/entity
#options
        overwrite: true
        deleteGenFile: true

        dbNamespace: Model\DbAccess

        dbPrefix: Db
        dbSuffix: ''
        dbListPrefix: Db
        dbListSuffix: ''

        driverNamespace: Model\DbDriver

        driverClassPrefix: 'Meta'
        driverClassSuffix: ''
        driverClassListPrefix: 'Meta'
        driverClassListSuffix: List

        entityNamespace: Model\Entity

        entityRowPrefix: ''
        entityRowSuffix: Row
        entityListPrefix: ''
        entityListSuffix: List
```

add to nette bootstrap.php
```php
if (PHP_SAPI === 'cli') {
    // include console and console commands only if application is accessed through console
    $configurator->addConfig(__DIR__ . '/config/console.neon');
}
```