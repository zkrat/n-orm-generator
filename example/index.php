<?php declare(strict_types = 1);



require __DIR__ . '/../vendor/autoload.php';
use NOrmGenerator\ClassModelGenerator\DbAccessMySqlGenerator;
use NOrmGenerator\ClassModelGenerator\MetaDriverGenerator;
use Nette\Neon\Neon;


$tempDir=__DIR__.'/tmp/';
$robotLoader=new \Nette\Loaders\RobotLoader();
$robotLoader->addDirectory(__DIR__.'/../src');
$robotLoader->setTempDirectory(__DIR__.'/tmp/');
$robotLoader->register()->rebuild();

$content=file_get_contents(__DIR__.'/config.neon');
$content=str_replace('%appDir%',__DIR__.'/output',$content);

$config=Neon::decode($content);
$config=$config['n.orm.console'];



$dsn='mysql:host=localhost;dbname=nette_db';
$user='root';
$password='1234';





$storage = new Nette\Caching\Storages\FileStorage($tempDir);
$connection = new Nette\Database\Connection($dsn, $user, $password);
$structure = new Nette\Database\Structure($connection, $storage);
$conventions = new Nette\Database\Conventions\DiscoveredConventions($structure);
$context = new Nette\Database\Context($connection, $structure, $conventions, $storage);


$metaDriverGenerator= new MetaDriverGenerator($config,$context);


$entityGenerator = new DbAccessMySqlGenerator($config,$context);


$metaDriverGenerator->hasExtraMetaTableColumnsMySqlDriverList();

$metaDriverGenerator->generateMetaDrivers();

$datebase=$metaDriverGenerator->getDatabaseName();

$forienKeyList =$metaDriverGenerator->getForeinKeyList($datebase);

$entityGenerator->generateFromForienKeyList($forienKeyList);
