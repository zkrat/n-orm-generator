<?php


namespace NOrmGenerator\ClassModelGenerator;


use NOrmGenerator\ClassModelGenerator\File\FileSaver;
use NOrmGenerator\ClassModelGenerator\MethodGenerator\AddClassRowMethodGenerator;
use NOrmGenerator\ClassModelGenerator\MethodGenerator\ArrayKeysMethodMethodGenerator;
use NOrmGenerator\ClassModelGenerator\MethodGenerator\AssocClassMethodGenerator;
use NOrmGenerator\ClassModelGenerator\MethodGenerator\CreateFromResultMethodGenerator;
use NOrmGenerator\ClassModelGenerator\MethodGenerator\GetIteratorMethodGenerator;
use NOrmGenerator\ClassModelGenerator\MethodGenerator\MetaCreateFromResultMethodGenerator;
use NOrmGenerator\DataCollection\DataCollection;
use NOrmGenerator\ClassModelGenerator\Helpers\StringManipulator;
use NOrmGenerator\ClassModelGenerator\Logger\ILogger;
use NOrmGenerator\ClassModelGenerator\Logger\TextLogger;
use NOrmGenerator\ClassModelGenerator\Meta\Traits\TraitMethodBuilder;
use NOrmGenerator\TracyAddon\BarReport;
use NOrmGenerator\ClassModelGenerator\Variables\MetaVariable;
use NOrmGenerator\ClassModelGenerator\Variables\MetaVariableConfiguration;

use Model\DbDriver\MetaTableColumnsForeinKeysMySqlDriver;
use Model\DbDriver\MetaTableColumnsMySqlDriverList;
use Nette\Database\Context;
use Nette\Database\ResultSet;
use Nette\Database\Row;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\ClassType;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use Tracy\Debugger;

abstract class CoreGenerator {

	use TraitGetterSetter;
	use TraitMethodBuilder;



	/**
	 * @var Context
	 */
	protected $db;

	/**
	 * @var IMetaSqlQuery
	 */
	protected $metaSqlQuery;

	/**
	 * @var array
	 */
	protected $config;


	protected $parent=null;


	/**
	 * @var MetaVariableConfiguration
	 */
	protected $metaDb;
	/**
	 * @var MetaVariableConfiguration
	 */
	protected $metaDriver;

	/**
	 * @var MetaVariableConfiguration
	 */
	protected $metaEntity;

	private static $doneDelete=null;
	/**
	 * @var MetaVariable
	 */
	protected  $metaVariable;

	/**
	 * @var ILogger
	 */
	protected $logger;


	public function __construct($config,Context $db ,ILogger $logger=null, $parent=null) {
		$this->config =$config;
		$this->parent =$parent;
		$this->metaDb=MetaVariableConfiguration::createDb($config);
		$this->metaDriver=MetaVariableConfiguration::createDriver($config);
		$this->metaEntity=MetaVariableConfiguration::createEntity($config);
		if (is_null(self::$doneDelete)){
			$this->deleteDir($this->metaDb->getDir());
			$this->deleteDir($this->metaDriver->getDir());
			$this->deleteDir($this->metaEntity->getDir());
		}

		\geoPHP::load('x');

		$this->db=$db;
		$this->metaSqlQuery=MetaQueryBuilder::createMetaSqlQuery($db);
		$this->logger= $logger instanceof ILogger ? $logger : new TextLogger();
	}

	private function deleteDir($dir){
		if($this->metaDb->isDeleteGenFile() && is_dir($dir)){
			foreach (Finder::find('*')->in($dir) as $file => $fileInfo){
				unlink($file);
			}
			self::$doneDelete=true;
		}

	}

	/**
	 * @return bool
	 */
	public function hasParent():bool {
		return !is_null($this->parent);
	}
	private static $test=0;



	protected function createMetaClassListFromTableName( string $tableName,$id='' ):PhpNamespace {
		return $this->createClassList($tableName,Row::class,$id);
	}

	protected function createClassListFromTableName( string $tableName,$id='' ,ForeignKeyList $forienKeyList=null):PhpNamespace {
		return $this->createClassList($tableName,ActiveRow::class,$id,$forienKeyList);
	}


	protected function createClassList(string $tableName,string $typeRow,string $id='',ForeignKeyList $forienKeyList=null ):PhpNamespace {
		$classList=$this->metaVariable->getClassListFromString($tableName);
		$metaVariableConfiguration=$this->metaVariable->getMetaVariableConfiguration();

		$phpNamespace=new PhpNamespace($metaVariableConfiguration->getNamespace());
		$phpNamespace->addUse(DataCollection::class);
		$myClassRow=$phpNamespace->addClass($classList);



		switch ($typeRow){
			case ActiveRow::class:
				CreateFromResultMethodGenerator::quickGenerate($myClassRow,$this->metaVariable,$tableName);
				break;
			case Row::class:
				MetaCreateFromResultMethodGenerator::quickGenerate($myClassRow,$this->metaVariable,$tableName);
				break;
		}
		GetIteratorMethodGenerator::quickGenerate($myClassRow,$this->metaVariable,$tableName);

		$array = $forienKeyList instanceof ForeignKeyList ?  $forienKeyList->getTableForeignKeyArray($tableName) : [];
		AddClassRowMethodGenerator::quickGenerate($myClassRow,$this->metaVariable,$tableName,['id'=>$id,'array'=>$array]);
		AssocClassMethodGenerator::quickGenerate($myClassRow,$this->metaVariable,$tableName,['array'=>$array]);
		ArrayKeysMethodMethodGenerator::quickGenerate($myClassRow,$this->metaVariable,$tableName,['array'=>$array]);

		FileSaver::create($metaVariableConfiguration,$phpNamespace,$classList)->saveFile();

		return $phpNamespace;

	}

	public function createAssocClassMethod(ClassType $class ,MetaTableColumnsForeinKeysMySqlDriver $metaTableColumnsForeinKeysMySqlDriver,$classRowName){

		$tableName=$metaTableColumnsForeinKeysMySqlDriver->getReferencedTableName();
		$subClassListVariable=$this->metaVariable->getClassListVariableFromString($tableName,false);
		$subClassListDollarVariable=$this->metaVariable->getClassListVariableFromString($tableName);
		$subClassRowDollarVariable=$this->metaVariable->getClassRowVariableNameFromString($tableName);
		$subClassRow=$this->metaVariable->getClassRowNameFromString($tableName);

		$fullSubClassList=$this->metaVariable->getClassListFromString($tableName,true);
		$subMethodName=$this->getAssocClassMethod($this->metaVariable->getClassListFromString($tableName));

		$subMethod=$class->addMethod($subMethodName);
		$subMethod->addParameter($subClassListVariable)->setType($fullSubClassList);
		$subMethod->addBody('foreach ('.$subClassListDollarVariable.' as '.$subClassRowDollarVariable.'){');
		$subMethod->addBody(ConstantDefinition::TAB1.'/**');
		$subMethod->addBody(ConstantDefinition::TAB1.'* @var '.$subClassRow.' '.$subClassRowDollarVariable);
		$subMethod->addBody(ConstantDefinition::TAB1.'*/'.PHP_EOL);

		$dataProperty=$this->getDataProperty($tableName);
		$classRowDollarVariable=$this->metaVariable->getClassRowVariableNameFromString($metaTableColumnsForeinKeysMySqlDriver->getTableName());
		// todo: PrimaryKey id by referce table


		$subMethod->addBody(ConstantDefinition::TAB1.'if(isset($this->'.$dataProperty.'['.$subClassRowDollarVariable.'->getId()])){');
		$subMethod->addBody(ConstantDefinition::TAB2.'$array = $this->'.$dataProperty.'['.$subClassRowDollarVariable.'->getId()];');

		$subMethod->addBody(ConstantDefinition::TAB2.'/**');
		$subMethod->addBody(ConstantDefinition::TAB2.'* @var '.$classRowName.'[] $array');
		$subMethod->addBody(ConstantDefinition::TAB2.'*/'.PHP_EOL);
		$subMethod->addBody(ConstantDefinition::TAB2.' foreach ($array as '.$classRowDollarVariable.'){');

		$subMethod->addBody(ConstantDefinition::TAB3.'/**');
		$subMethod->addBody(ConstantDefinition::TAB3.'* @var '.$classRowName.' '.$classRowDollarVariable);
		$subMethod->addBody(ConstantDefinition::TAB3.'*/'.PHP_EOL);
		$subMethod->addBody(ConstantDefinition::TAB3.$classRowDollarVariable.'->set'.$subClassRow.'('.$subClassRowDollarVariable.');');
		$subMethod->addBody(ConstantDefinition::TAB3.$subClassRowDollarVariable.'->'.$this->getAddClassMethod($classRowName).'('.$classRowDollarVariable.');');

		$subMethod->addBody(ConstantDefinition::TAB2.'}');
		$subMethod->addBody(ConstantDefinition::TAB1.'}');
		$subMethod->addBody('}');

	}

	public function getDataPropertyByClass(ClassType $class ,string $tableName):string {
		if ($class->getName()==$this->metaVariable->getClassListFromString($tableName)){
			// fix property
			$property=$this->getDataProperty();
		}else{
			$property=$this->getDataProperty($tableName);
		}
		return $property;
	}

	/**
	 * @param ClassType $class
	 * @param string $tableName
	 * @param string $property - remove
	 */
	private function createArrayKeysMethod(ClassType $class ,string $tableName, string $property ) {
		$property=$this->getDataPropertyByClass($class,$tableName);


		$getAllIdsMethod=$this->metaVariable->getClassListGetAllIdsArrayMethodFromString($tableName);
		$getIdsMethod=$class->addMethod($getAllIdsMethod);
		$getIdsMethod->setBody('return array_keys($this->'.$property.');');
	}




	/**
	 * @return ILogger
	 */
	public function getLogger(): ILogger {
		return $this->logger;
	}

	/**
	 * @param ILogger $logger
	 */
	public function setLogger( ILogger $logger ): void {
		$this->logger = $logger;
	}

	/**
	 * @return bool
	 */
	abstract public function hasExtraMetaTableColumnsMySqlDriverList(): bool;

	/**
	 * @return MetaTableColumnsMySqlDriverList|null
	 */
	abstract public function getExtraMetaTableColumnsMySqlDriverList(): ?MetaTableColumnsMySqlDriverList;

	/**
	 * @param ClassType $class
	 *
	 * @return mixed
	 */
	abstract protected function addExtraFeatures( ClassType $class );


}