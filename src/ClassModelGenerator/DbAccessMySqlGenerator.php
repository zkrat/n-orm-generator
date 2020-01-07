<?php


namespace NOrmGenerator\ClassModelGenerator;


use NOrmGenerator\ClassModelGenerator\Exception\ForeignKeyException;
use NOrmGenerator\ClassModelGenerator\File\FileSaver;
use NOrmGenerator\ClassModelGenerator\Helpers\StringManipulator;
use NOrmGenerator\ClassModelGenerator\Logger\ILogger;
use NOrmGenerator\ClassModelGenerator\Meta\MySqlPhpProperty;
use Model\DbDriver\MetaTableColumnsForeinKeysMySqlDriverList;
use NOrmGenerator\ClassModelGenerator\Variables\IVariable;
use NOrmGenerator\ClassModelGenerator\Variables\MetaVariable;
use NOrmGenerator\ClassModelGenerator\Variables\MySql\Geometry;
use Model\DbDriver\MetaTableColumnsForeinKeysMySqlDriver;
use Model\DbDriver\MetaTableColumnsMySqlDriver;
use Model\DbDriver\MetaTableColumnsMySqlDriverList;
use Model\DbDriver\MetaTableMySqlDriver;
use Model\Entity\BinariesList;
use Model\Entity\BinariesRow;
use Model\Entity\NumbersRow;
use Model\Entity\TableAssocList;
use Model\Entity\TableAssocRow;
use Nette\Database\Context;
use Nette\Database\Table\Selection;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpNamespace;



class DbAccessMySqlGenerator extends CoreGenerator {
	use TraitGetterSetter;


	/**
	 * @var EntityMySqlGenerator
	 */
	protected $dbEntity;
	const DB_MANAGER_NAME='DbAccessManager';




	/**
	 * @var PhpNamespace
	 */
	private $dbAccessManager;

	/**
	 * @var \Nette\PhpGenerator\ClassType
	 */
	private $dbAccessClass;

	/**
	 * @var MetaTableColumnsMySqlDriverList|null
	 */
	protected $extraMetaTableColumnsMySqlDriverList=null;


	/**
	 * @var bool
	 */
	private $geoProperty=false;


	public function __construct($config,Context $db,ILogger $logger=null ) {
		parent::__construct($config,$db );
		$this->metaVariable = MetaVariable::createFomCofiguration($this->metaDb);

		$this->dbEntity= new EntityMySqlGenerator($config,$db,$logger,$this );
		$this->dbAccessManager=new PhpNamespace($this->metaDb->getNamespace());
		$this->dbAccessManager->addUse(Context::class);

		$this->dbAccessClass= $this->dbAccessManager->addClass(DbAccessMySqlGenerator::DB_MANAGER_NAME);
		$this->createConstrucor($this->dbAccessClass,false);
		$this->enableGetterGenerator();
	}


	public function generateFromMetaTableColoumnsMySqlDriverList( MetaTableColumnsMySqlDriverList $metaTableColoumnsMySqlDriverList ,ForeignKeyList $forienKeyList) {

		$this->checkExtraProperty($metaTableColoumnsMySqlDriverList);
		$this->dbEntity->generateFromMetaTableColoumnsMySqlDriverList($metaTableColoumnsMySqlDriverList,$forienKeyList);
		$this->generateDbAccessClass($metaTableColoumnsMySqlDriverList,$forienKeyList);

	}

	private function generateDbAccessClass( MetaTableColumnsMySqlDriverList $metaTableColoumnsMySqlDriverList,ForeignKeyList $forienKeyList ) {
		$metaTableColoumnsMySqlDriver =$metaTableColoumnsMySqlDriverList->firstItem();
		/**
		 * @var MetaTableColumnsMySqlDriver $metaTableColoumnsMySqlDriver
		 */

		$tableName=$metaTableColoumnsMySqlDriver->getTableName();


		$class=$this->createDbClass($metaTableColoumnsMySqlDriver);
		$this->createGetterFromMetaTableColoumnsMySqlDriver($this->dbAccessClass,$metaTableColoumnsMySqlDriver);
		$this->addSubClassToConstructor($metaTableColoumnsMySqlDriver);



		$this->createMethodGetAllClassList($class,$metaTableColoumnsMySqlDriver);
		$this->createExtraMetaTableColumnsMySqlDriverList($class);


		$this->addIdSelectList($class,$tableName);
		$this->TESTcheckSubMethodDev($metaTableColoumnsMySqlDriver, $class,$forienKeyList);

		$dbClassName =$this->metaVariable->getClassRowName($metaTableColoumnsMySqlDriver);
		FileSaver::create($this->metaDb,$class->getNamespace(),$dbClassName )->saveFile();
		$this->resetExtra();

	}


	private function TESTcheckSubMethodDev(MetaTableColumnsMySqlDriver $metaTableColoumnsMySqlDriver, ClassType $class,ForeignKeyList $forienKeyList){
		$this->createTableForeign($metaTableColoumnsMySqlDriver,$class,$forienKeyList);
		$this->createTableReference($metaTableColoumnsMySqlDriver,$class,$forienKeyList);

	}

	protected function createTableForeign(MetaTableColumnsMySqlDriver $metaTableColoumnsMySqlDriver, ClassType $class,ForeignKeyList $forienKeyList):void{
		$tableName=$metaTableColoumnsMySqlDriver->getTableName();
		$array =$forienKeyList->getTableForeignKeyArray($tableName);

		foreach ($array as $metaTableColumnsForeinKeysMySqlDriver){
				/**
				 * @var MetaTableColumnsForeinKeysMySqlDriver $metaTableColumnsForeinKeysMySqlDriver
				 */
				$this->createForeignGetMethod($class,$metaTableColumnsForeinKeysMySqlDriver);
		}
	}



	protected function createTableReference(MetaTableColumnsMySqlDriver $metaTableColoumnsMySqlDriver, ClassType $class,ForeignKeyList $forienKeyList):void{
		$tableName=$metaTableColoumnsMySqlDriver->getTableName();

		$array =$forienKeyList->getTableReferenceArray($tableName);

		foreach ( $array as $metaTableColumnsForeinKeysMySqlDriver ) {
			/**
			 * @var MetaTableColumnsForeinKeysMySqlDriver $metaTableColumnsForeinKeysMySqlDriver
			 */
			$this->createReferenceGetMethod($class,$metaTableColumnsForeinKeysMySqlDriver);


		}

	}

	/**
	 *   +----------------+                   +--------------------------+
	 *   |                |                   |                          |
	 *   |   $tableName   |-------N:1-------- |   $referencedTableName   |
	 *   |                |                   |                          |
	 *   +----------------+                   +--------------------------+
	 *
	 * @param ClassType $class
	 * @param MetaTableColumnsForeinKeysMySqlDriver $metaTableColumnsForeinKeysMySqlDriver
	 */
	protected function createReferenceGetMethod(ClassType $class,MetaTableColumnsForeinKeysMySqlDriver $metaTableColumnsForeinKeysMySqlDriver){

		$referencedTableName = $metaTableColumnsForeinKeysMySqlDriver->getReferencedTableName();
		$tableName = $metaTableColumnsForeinKeysMySqlDriver->getTableName();


		$refClassList = $this->dbEntity->getMetaVariable()->getClassListFromString( $tableName);

		$refClassListNamespace = $this->dbEntity->getMetaVariable()->getClassListFromString( $tableName,true);
		$refClassListVariable = $this->dbEntity->getMetaVariable()->getClassListVariableFromString( $tableName,false);
		$refClassListVariableDollar = $this->dbEntity->getMetaVariable()->getClassListVariableFromString( $tableName);
		$methodRefClassList=$this->getRefClassMethod($refClassList);

		if (!$class->hasMethod($methodRefClassList))
			$method= $class->addMethod($methodRefClassList);
		else
			$method= $class->getMethod($methodRefClassList);


		$method->addParameter($refClassListVariable)->setType($refClassListNamespace);
		$methodGetIKds=$this->dbEntity->getMetaVariable()->getClassListGetAllIdsArrayMethodFromString($referencedTableName);

		$classListVariableDollar2=$this->dbEntity->getMetaVariable()->getClassListVariableFromString($referencedTableName);

		$methodGetAllTableAssocList=$this->dbEntity->getMetaVariable()->getClassListMethodAllByIdFromString($referencedTableName);
		$method->addBody($classListVariableDollar2.' = $this->'.$methodGetAllTableAssocList.'('.$refClassListVariableDollar.'->'.$methodGetIKds.'());');
		$refClassListX= $this->dbEntity->getMetaVariable()->getClassListFromString( $referencedTableName);

		$assocMethod2= $this->getAssocClassMethod($refClassListX);
		$method->addBody($refClassListVariableDollar.'->'.$assocMethod2.'('.$classListVariableDollar2.');');
	}

	/**
	 *   +----------------+                   +--------------------------+
	 *   |                |                   |                          |
	 *   |   $tableName   |-------N:1-------- |   $referencedTableName   |
	 *   |                |                   |                          |
	 *   +----------------+                   +--------------------------+
	 *
	 * @param ClassType $class
	 * @param MetaTableColumnsForeinKeysMySqlDriver $metaTableColumnsForeinKeysMySqlDriver
	 */
	protected function createForeignGetMethod(ClassType $class,MetaTableColumnsForeinKeysMySqlDriver $metaTableColumnsForeinKeysMySqlDriver){

		$referencedTableName = $metaTableColumnsForeinKeysMySqlDriver->getReferencedTableName();
		$tableName = $metaTableColumnsForeinKeysMySqlDriver->getTableName();
		$refColumn=$metaTableColumnsForeinKeysMySqlDriver->getColumnName();





		$assocRefClassListMethod=$this->dbEntity->getMetaVariable()->getClassListFromString($referencedTableName);
		$assocClassListMethod=$this->dbEntity->getMetaVariable()->getClassListFromString($tableName);
		$assocClassListVariableDollar=$this->dbEntity->getMetaVariable()->getClassListVariableFromString($tableName);
		$refClassListVariableName=$this->dbEntity->getMetaVariable()->getClassListVariableFromString($referencedTableName,false);
		$refClassListVariableNameDollar=$this->dbEntity->getMetaVariable()->getClassListVariableFromString($referencedTableName);
		$refClassListVariable=$this->dbEntity->getMetaVariable()->getClassListVariableFromString($referencedTableName);
		$assocRefClassList=$this->dbEntity->getMetaVariable()->getClassListFromString($referencedTableName,true);
		$getAllIdsMethod=$this->dbEntity->getMetaVariable()->getClassListGetAllIdsArrayMethodFromString($referencedTableName);

		if ($class->hasMethod($this->getAssocClassMethod($assocRefClassListMethod)))
			$methodAssocc = $class->getMethod($this->getAssocClassMethod($assocRefClassListMethod));
		else{
			$methodAssocc = $class->addMethod($this->getAssocClassMethod($assocRefClassListMethod));
			$methodAssocc->addParameter($refClassListVariableName)->setType($assocRefClassList);

			if (!in_array($assocRefClassList,$class->getNamespace()->getUses()))
				$class->getNamespace()->addUse($assocRefClassList);

		}
		$methodAssocc->addBody(PHP_EOL.PHP_EOL);




		$assocSubClass2=$this->getAssocClassMethod($assocRefClassListMethod);


		$methodAssocc->addBody('$row = $this->getAll()->where(\''.$refColumn.'\','.$refClassListVariable.'->'.$getAllIdsMethod.'());//change here');
		$methodAssocc->addBody($assocClassListVariableDollar.' = '.$assocClassListMethod.'::createFromResult($row);');
		$methodAssocc->addBody($assocClassListVariableDollar.'->'.$assocSubClass2.'('.$refClassListVariableNameDollar.');');
		$methodAssocc->addBody($assocClassListVariableDollar.'->'.$assocSubClass2.'('.$refClassListVariableNameDollar.');');

	}





	protected function createDbClass(MetaTableColumnsMySqlDriver $metaTableColoumnsMySqlDriver):ClassType{
		$phpNamespace =$this->getDbTablePhpNamespace($metaTableColoumnsMySqlDriver);
		$tableName=$metaTableColoumnsMySqlDriver->getTableName();



		$dbClassName =$this->metaVariable->getClassRowName($metaTableColoumnsMySqlDriver);
		$class=$phpNamespace->addClass($dbClassName);

		$class->addConstant('TABLE',$tableName);
		$class->addExtend(BaseModel::class);
		$this->createConstrucor($class,true,DbAccessMySqlGenerator::DB_MANAGER_NAME);
		return $class;
	}


	protected function getDbTablePhpNamespace(MetaTableColumnsMySqlDriver $metaTableColoumnsMySqlDriver):PhpNamespace{

		$tableName=$metaTableColoumnsMySqlDriver->getTableName();
		$phpNamespace=new PhpNamespace($this->metaDb->getNamespace());
		$phpNamespace->addUse(Context::class);
		$phpNamespace->addUse(BaseModel::class);
		$classListNamespace =$this->dbEntity->getMetaVariable()->getClassListFromString($tableName,true);
		$phpNamespace->addUse($classListNamespace);
		$phpNamespace->addUse($this->metaVariable->getClassWithNamespace(DbAccessMySqlGenerator::DB_MANAGER_NAME));
		return  $phpNamespace;
	}

	protected function createMethodGetAllClassList(ClassType $class,MetaTableColumnsMySqlDriver $metaTableColoumnsMySqlDriver){
		$tableName=$metaTableColoumnsMySqlDriver->getTableName();
		$classListNamespace =$this->dbEntity->getMetaVariable()->getClassListFromString($tableName,true);
		$classList =$this->dbEntity->getMetaVariable()->getClassListFromString($tableName);

		$method=$class->addMethod('getAll'.$classList);
		$method->setReturnType($classListNamespace);
		$method->addBody('$row = $this->getAll();');
		$method->addBody($this->getReturnList($classList));
		$method->addComment('@var '.$classList);
	}


	protected function addIdSelectList(ClassType $class,string $tableName){
		$classList =$this->dbEntity->getMetaVariable()->getClassListFromString($tableName);
		$classListNamespace =$this->dbEntity->getMetaVariable()->getClassListFromString($tableName,true);
		$method=$class->addMethod('getAll'.$classList.'ByIds')->setReturnType($classListNamespace);
		$method->addParameter('ids')->setType('array');
		$method->addBody('$row= $this->getAll()->where(\'id\',$ids);');
		$method->addBody($this->getReturnList($classList));

	}

	private function getReturnList(string $listClassName,$variableName='$row'):string {
		return 'return '.$listClassName.'::createFromResult('.$variableName.');';
	}

	protected function resetExtra(){
		$this->extraMetaTableColumnsMySqlDriverList =null;
		$this->geoProperty                          =false;
	}



	private function createConstrucor( ClassType $class , $createParent=true,$parentTypeHint=null):Method {
		if (!$class->hasMethod('__construct')){
			$constructor = $class->addMethod('__construct');
			if(!$class->hasProperty('db')){
				$property=$class->addProperty('db');
				$property->setComment('@var Context $db');
			}
			$constructor->addParameter('db')
			            ->setType(Context::class);

			if (is_string($class->getExtends()) && $class->getExtends()==BaseModel::class || is_array($class->getExtends()) && in_array(BaseModel::class,$class->getExtends()))
				$constructor->addBody('parent::__construct($db);');
			else
				$constructor->addBody('$this->db = $db;');

		}else{
			$constructor = $class->getMethod('__construct');
		}
		if($createParent){
			$class->addProperty('parent')->addComment('@var '.$parentTypeHint);
			$parent=$constructor->addParameter('parent');
			if(!is_null($parentTypeHint))
				$parent->setType($this->metaVariable->getClassWithNamespace($parentTypeHint));

			$constructor->addBody('$this->parent = $parent;');
		}
		return $constructor;
	}

	private function addSubClassToConstructor(MetaTableColumnsMySqlDriver $metaTableColoumnsMySqlDriver){
		$dbClassName =$this->metaVariable->getClassRowName($metaTableColoumnsMySqlDriver);
		$dbPropertyName=$this->metaVariable->getClassRowVariableName($metaTableColoumnsMySqlDriver,false);
		$dbConstructor=$this->dbAccessClass->getMethod('__construct');
		$dbConstructor->addBody('$this->'.$dbPropertyName. ' = new '.$dbClassName.'($db ,$this);');
	}

	/**
	 * @param ForeignKeyList $forienKeyList
	 *
	 * @throws ForeignKeyException
	 */
	public function generateFromForienKeyList( ForeignKeyList $forienKeyList ) {

		$datebase= $forienKeyList->getDatebase();
		$metaDriverGenerator=$forienKeyList->getMetaDriverGenerator();
		$metaTableMySqlDriverList=$forienKeyList->getMetaTableMySqlDriverList();

		foreach ($metaTableMySqlDriverList as $metaTableMySqlDriver){
			/**
			 * @var MetaTableMySqlDriver $metaTableMySqlDriver
			 */

			$metaTableColoumnsMySqlDriverList=$metaDriverGenerator->getMetaTableColumnsMySqlDriverList($datebase,$metaTableMySqlDriver->getName());
			if ($metaTableColoumnsMySqlDriverList instanceof MetaTableColumnsMySqlDriverList){

				$this->generateFromMetaTableColoumnsMySqlDriverList($metaTableColoumnsMySqlDriverList,$forienKeyList);

			}
			elseif ($metaTableColoumnsMySqlDriverList instanceof MetaTableColumnsForeinKeysMySqlDriverList){
				$this->generateFromMetaTableColumnsForeinKeysMySqlDriverList($metaTableColoumnsMySqlDriverList,$forienKeyList);
			}

			else
				throw new ForeignKeyException(get_class($metaTableColoumnsMySqlDriverList). '  not found');

		}

		FileSaver::create($this->metaDb,$this->dbAccessManager,DbAccessMySqlGenerator::DB_MANAGER_NAME)->saveFile();
	}

	/**
	 * @return MetaTableColumnsMySqlDriverList|null
	 */
	public function getExtraMetaTableColumnsMySqlDriverList(): ?MetaTableColumnsMySqlDriverList {
		return $this->extraMetaTableColumnsMySqlDriverList;
	}

	/**
	 * @return bool
	 */
	public function hasExtraMetaTableColumnsMySqlDriverList(): bool {
		return $this->extraMetaTableColumnsMySqlDriverList instanceof MetaTableColumnsMySqlDriverList;
	}

	private function checkExtraProperty( MetaTableColumnsMySqlDriverList $metaTableColoumnsMySqlDriverList ) {

		if ($this->extraMetaTableColumnsMySqlDriverList=== $metaTableColoumnsMySqlDriverList )
			return null;

		foreach ($metaTableColoumnsMySqlDriverList as $metaTableColoumnsMySqlDriver){
			/***
			 * @var MetaTableColumnsMySqlDriver $metaTableColoumnsMySqlDriver
			 */
			$mySqlPhpProperty=MySqlPhpProperty::create($metaTableColoumnsMySqlDriver);
			if($mySqlPhpProperty->isSubclassOf( \Geometry::class ) || $mySqlPhpProperty->implementsInterface(IVariable::class)){
				$this->extraMetaTableColumnsMySqlDriverList =$metaTableColoumnsMySqlDriverList;
				$this->geoProperty=true;
			}else{
				//TODO: not set variable type
				// specila db output
				//dumpx($metaTableColoumnsMySqlDriver->getColumnType().': '.$type);
			}
		}



	}


	private function createExtraMetaTableColumnsMySqlDriverList( ClassType $class ) {
		if($this->hasExtraMetaTableColumnsMySqlDriverList()){
			$methodGetAll=$class->addMethod('getAll')->setReturnType(Selection::class);
			$methodGetAll->addBody('$result= parent::getAll();');
			foreach  ($this->extraMetaTableColumnsMySqlDriverList as $metaTableColoumnsMySqlDriver){
				/**
				 * @var MetaTableColumnsMySqlDriver $metaTableColoumnsMySqlDriver
				 */

				$mySqlPhpProperty=MySqlPhpProperty::create($metaTableColoumnsMySqlDriver);

				if($mySqlPhpProperty->isSubclassOf(\Geometry::class)){
					$mySqlPhpProperty->overwriteReflectionClass(Geometry::class);
				}
				$columnName=$mySqlPhpProperty->getColumnSelection();
				$methodGetAll->addBody('$result->select(\''.$columnName.'\');');
			}
			$methodGetAll->addBody('return $result;');
		}
	}


	protected function addExtraFeatures( ClassType $class ) {
		// TODO: Implement addExtraFeatures() method.
	}

	/**
	 * @return bool
	 */
	public function hasGeoProperty(): bool {
		return $this->geoProperty;
	}
}