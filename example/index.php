<?php declare(strict_types = 1);



require __DIR__ . '/../vendor/autoload.php';
use NOrmGenerator\ClassModelGenerator\DbAccessMySqlGenerator;
use NOrmGenerator\ClassModelGenerator\MetaDriverGenerator;
use Nette\Neon\Neon;
use Model\DbAccess\DbAccessManager;

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

\Tracy\Debugger::$logDirectory=__DIR__.'/log';



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
$dbManager =new DbAccessManager($context);

$binariesList=$dbManager->getDbBinaries()->getAllBinariesList();





echo 'BinariList:'.$binariesList->count().PHP_EOL;
echo 'DatetimeTable:'.$dbManager->getDbDatetimeTable()->getAllDatetimeTableList()->count().PHP_EOL;
echo 'Geos:'.$dbManager->getDbGeos()->getAllGeosList()->count().PHP_EOL;
echo 'Lists:'.$dbManager->getDbLists()->getAllListsList()->count().PHP_EOL;
echo 'Numbers:'.$dbManager->getDbNumbers()->getAllNumbersList()->count().PHP_EOL;
echo 'Strings:'.$dbManager->getDbStrings()->getAllStringsList()->count().PHP_EOL;
echo 'TableAssoc:'.$dbManager->getDbTableAssoc()->getAllTableAssocList()->count().PHP_EOL;
