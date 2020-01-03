<?php declare(strict_types = 1);

use NOrmGenerator\ClassModelGenerator\DbAccessMySqlGenerator;
use NOrmGenerator\ClassModelGenerator\MetaDriverGenerator;

require __DIR__ . '/../vendor/autoload.php';

$tempDir=__DIR__.'/tmp/';
$robotLoader=new \Nette\Loaders\RobotLoader();
$robotLoader->addDirectory(__DIR__.'/../src');
$robotLoader->setTempDirectory(__DIR__.'/tmp/');
$robotLoader->register();

$dsn='mysql:host=localhost;dbname=nette_db';
$user='root';
$password='1234';


$config = [
	'overwrite'=> true,
	'deleteGenFile'=> true,
	'dbNamespace'=> 'Model\DbAccess',
	'dbDir'=> __DIR__.'/output/model/nORM/DbAccess',
	'dbPrefix'=> 'Db',
	'dbSuffix'=> '',
	'dbListPrefix'=> 'Db',
	'dbListSuffix'=> '',

	'driverNamespace'=> 'Model\DbDriver',
	'driverDir'=> __DIR__.'/output/model/nORM/meta',
	'driverClassPrefix'=> 'Meta',
	'driverClassSuffix'=> '',
	'driverClassListPrefix'=> 'Meta',
	'driverClassListSuffix'=> 'List',

	'entityNamespace'=> 'Model\Entity',
	'entityDir'=> __DIR__.'/output/model/nORM/entity',
	'entityRowPrefix'=> '',
	'entityRowSuffix'=> 'Row',
	'entityListPrefix'=> '',
	'entityListSuffix'=> 'List'
];





$storage = new Nette\Caching\Storages\FileStorage($tempDir);
$connection = new Nette\Database\Connection($dsn, $user, $password);
$structure = new Nette\Database\Structure($connection, $storage);
$conventions = new Nette\Database\Conventions\DiscoveredConventions($structure);
$context = new Nette\Database\Context($connection, $structure, $conventions, $storage);


$metaDriverGenerator= new MetaDriverGenerator($config,$context);
$entityGenerator = new DbAccessMySqlGenerator($config,$context);



$metaDriverGenerator->generateMetaDrivers();

$datebase=$metaDriverGenerator->getDatabaseName();

$forienKeyList =$metaDriverGenerator->getForeinKeyList($datebase);

$entityGenerator->generateFromForienKeyList($forienKeyList);
