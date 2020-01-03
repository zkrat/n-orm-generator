<?php
namespace NOrmGenerator\Extension;

use NOrmGenerator\Console\OrmCreateCommand;
use Contributte\Console\DI\ConsoleExtension;
use Nette\DI\CompilerExtension;
use Nette\PhpGenerator\ClassType;
use NOrmGenerator\Extension\Exception\NOrmExtensionException;

class NOrmExtension extends CompilerExtension{


	/**
	 * @var bool
	 */
	private $cliMode;
	/**
	 * @var ConsoleExtension
	 */
	private $consoleExtension;


	protected $config = [
		'overwrite'=> true,
		'deleteGenFile'=> true,
		'dbNamespace'=> 'Model\DbAccess',
//	'dbDir'=> %appDir%/model/nORM/DbAccess
	'dbPrefix'=> 'Db',
	'dbSuffix'=> '',
	'dbListPrefix'=> 'Db',
	'dbListSuffix'=> '',

	'driverNamespace'=> 'Model\DbDriver',
//	'driverDir'=> %appDir%/model/nORM/meta
	'driverClassPrefix'=> 'Meta',
	'driverClassSuffix'=> '',
	'driverClassListPrefix'=> 'Meta',
	'driverClassListSuffix'=> 'List',

	'entityNamespace'=> 'Model\Entity',
//	'entityDir'=> %appDir%/model/nORM/entity
	'entityRowPrefix'=> '',
	'entityRowSuffix'=> 'Row',
	'entityListPrefix'=> '',
	'entityListSuffix'=> 'List'

	];

	public function __construct(bool $cliMode = false){
		$this->cliMode = $cliMode;
	}


	public function loadConfiguration() {

		if (!isset($this->config['entityDir']))
			throw new NOrmExtensionException(sprintf(NOrmExtensionException::MSG_MISSING_OUTPUT_PATH,'entityDir'),NOrmExtensionException::MISSING_OUTPUT_PATH);

	    if (!isset($this->config['dbDir']))
		    throw new NOrmExtensionException(sprintf(NOrmExtensionException::MSG_MISSING_OUTPUT_PATH,'dbDir'),NOrmExtensionException::MISSING_OUTPUT_PATH);
		if (!isset($this->config['driverDir']))
			throw new NOrmExtensionException(sprintf(NOrmExtensionException::MSG_MISSING_OUTPUT_PATH,'driveDir'),NOrmExtensionException::MISSING_OUTPUT_PATH);


		parent::loadConfiguration();
		$containerBuilder=$this->getContainerBuilder();
		$containerBuilder->addDefinition($this->prefix('ormCreateCommand'))
		                 ->setType(OrmCreateCommand::class)
		                 ->setArguments([
		                 	'name'=>null,
			                 'config'=>$this->config
		                 ]);



		$this->consoleExtension = new ConsoleExtension($this->cliMode);
		$this->consoleExtension->setCompiler($this->compiler, $this->prefix('consoleExtension'));
		$config= new \stdClass();
		$config->name='consoleExtension';
		$config->version=null;
		$config->catchExceptions=null;
		$config->autoExit=null;
		$config->helperSet=null;
		$config->lazy=true;
		$config->helpers=[];
		$config->url=null;

		$this->consoleExtension->setConfig($config);
		$this->consoleExtension->loadConfiguration();
	}

	public function beforeCompile() {
		parent::beforeCompile();
		$this->consoleExtension->beforeCompile();
	}

	public function afterCompile( ClassType $class ) {
		parent::afterCompile( $class );
		$this->consoleExtension->afterCompile($class);
	}

}