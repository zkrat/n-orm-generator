<?php

namespace NOrmGenerator\ClassModelGenerator;

use NOrmGenerator\ClassModelGenerator\File\FileSaver;
use NOrmGenerator\ClassModelGenerator\Meta\IMetaTableColumnsForeinKeys;
use NOrmGenerator\DataCollection\DataCollection;
use NOrmGenerator\ClassModelGenerator\Exception\ForeignKeyException;
use NOrmGenerator\ClassModelGenerator\Meta\MetaPropertyGenerator;
use NOrmGenerator\ClassModelGenerator\Helpers\StringManipulator;
use NOrmGenerator\ClassModelGenerator\Traits\TraitClassFill;
use NOrmGenerator\ClassModelGenerator\Variables\MetaVariable;
use Model\DbDriver\MetaTableColumnsForeignKeysMySqlDriver;
use Model\DbDriver\MetaTableColumnsForeignKeysMySqlDriverList;
use Model\DbDriver\MetaTableColumnsForeinKeysMySqlDriverList;
use Model\DbDriver\MetaTableColumnsMySqlDriverList;
use Model\DbDriver\MetaTableMySqlDriverList;
use Nette\Database\Context;
use Nette\Database\IRow;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;

class MetaDriverGenerator extends CoreGenerator {

	const DB_DATABASE_PREFIX='Database';


	const DB_TABLE_PREFIX='Table';

	const DB_COLUMN_PREFIX='TableColumns';

	const DB_FOREIN_KEYS_PREFIX='TableColumnsForeinKeys';




	protected $databaseName=null;


	public function __construct($config,Context $db ,$parent=null) {
		parent::__construct($config,$db ,$parent);
		$this->metaVariable = MetaVariable::createFomCofiguration($this->metaDriver);

	}

	public function generateMetaDrivers(){
		$this->generateMetaDatabaseClass();
		$this->generateMetaTableClasses();
		$this->generateMetaTableColumsClasses();
		$this->generateMetaTableColumsForenKeysClasses();
	}


	/**
	 * @return mixed
	 */
	public function getDatabaseName() {
		$this->setupDatabaseName();
		return $this->databaseName;
	}


	protected function setupDatabaseName() {
		if(is_null($this->databaseName)){
			$database=$this->db->query($this->metaSqlQuery->getActualDatabaseName())->fetch();
			if ($database && isset($database->db))
				$this->databaseName=$database->db;
		}
		return $this->databaseName;
	}

	public function getDriverName(){

		$className=get_class($this->db->getConnection()->getSupplementalDriver());

		return StringManipulator::getClassnameWithoutNamespace($className);

	}


	private function generateMetaDatabaseClass() {
		$query=$this->metaSqlQuery->getDatabaseQuery($this->getDatabaseName());
		$this->logger->message($query);

		$dbFetch=$this->db->query($query)->fetch();
		$metaDatabaseClass= MetaDriverGenerator::DB_DATABASE_PREFIX . $this->getDriverName();
		$this->generateClassFromRow($dbFetch,$metaDatabaseClass);

	}

	/**
	 *
	 */
	private function generateMetaTableClasses() {
		$query=$this->metaSqlQuery->getTablesQuery($this->getDatabaseName());
		$this->logger->message($query);
		$row=$this->db->query($query)->fetch();

		$metaDatabaseClass=$this->metaVariable->getClassRowNameFromString(MetaDriverGenerator::DB_TABLE_PREFIX.$this->getDriverName());
		$metaDatabaseClass=MetaDriverGenerator::DB_TABLE_PREFIX.$this->getDriverName();
		$this->generateClassFromRow($row,$metaDatabaseClass);
	}

	private function generateMetaTableColumsClasses() {
		$row =$this->db->query($this->metaSqlQuery->getTablesQuery($this->getDatabaseName()))->fetch();
		if ($row instanceof  IRow){
			$query=$this->metaSqlQuery->getTableQuery($this->getDatabaseName(),$row->Name);
			$this->logger->message($query);
			$rowTable=$this->db->query($query)->fetch();
			$metaDatabaseClass=MetaDriverGenerator::DB_COLUMN_PREFIX.$this->getDriverName();
			$this->generateClassFromRow($rowTable,$metaDatabaseClass);

		}
	}

	private function generateMetaTableColumsForenKeysClasses() {
		$rows =$this->db->query($this->metaSqlQuery->getTablesQuery($this->getDatabaseName()));
		$array=[];
		$rowTable=null;
		foreach ($rows as $row){
			$query=$this->metaSqlQuery->getTableForenKeyQuery($this->getDatabaseName(),$row->Name);

			$rowTable=$this->db->query($query)->fetch();
			if(is_null($rowTable))
				continue;
			else{
				$this->logger->message($query);
				break;
			}

		}
		$metaDatabaseClass=MetaDriverGenerator::DB_FOREIN_KEYS_PREFIX.$this->getDriverName();
		$implemets=[IMetaTableColumnsForeinKeys::class];
		$this->generateClassFromRow($rowTable,$metaDatabaseClass,$implemets);

	}


	/**
	 * @param IRow|null $row
	 * @param string $className
	 * @param string[] $implemets
	 */

	private function generateClassFromRow(IRow $row=null,string $className,array $implemets=[]){
		if ($row instanceof IRow){
			$arrayRow=(array) $row;
			$this->generateClassFromArray($arrayRow,$className,$implemets);
		}
	}

	/**
	 * @param array $arrayRow
	 * @param string $className
	 * @param string[] $implemets
	 */
	private function generateClassFromArray(array $arrayRow,string $className,array $implemets=[]){
		$classRow=$this->metaVariable->getClassRowNameFromString($className);

		$propertyGenerator= new MetaPropertyGenerator($this->metaDriver,$classRow);
		$propertyGenerator->enableGetterGenerator();
		$propertyGenerator->enableSetterGenerator();
		$propertyGenerator->addTrait(TraitClassFill::class);

		foreach ($arrayRow as $propertyName => $value){
			$propertyGenerator->addProperty($propertyName , $value);
		}

		$class=$propertyGenerator->getClass();

		$propertyGenerator->addImplementArray($implemets);


		$classList =$this->metaVariable->getClassListFromString($className);
		$namespaoceClassList =$this->metaVariable->getClassListFromString($className,true);

		$class->addProperty('parent')->addComment('@var '.$classList);

		$method=$class->addMethod('setParent');
		$parameter=$this->metaVariable->addDolar($classList,false);
		$parameterDollar=$this->metaVariable->addDolar($classList);

		$method->addParameter($parameter)->setType($this->metaVariable->getClassListFromClassName($className,true));

		$method->addBody('$this->parent= '.$parameterDollar.';');

		$method2=$class->addMethod('getParent');
		$method2->setReturnType($namespaoceClassList);
		$method2->addBody('return $this->parent;');
		if ($propertyGenerator->getPhpNamespace() instanceof PhpNamespace){
			$classRow =$this->metaVariable->getClassRowNameFromString($className);

			FileSaver::create($this->metaDriver,$propertyGenerator->getPhpNamespace(),$classRow)->saveFile();

			$tableName=StringManipulator::camelCaseToUnderscore($className);

			$this->createMetaClassListFromTableName($tableName);
		}

	}


	public function getMetaTableMySqlDriverList($database):DataCollection {
		$query=$this->metaSqlQuery->getTablesQuery($database);
		$rows=$this->db->query($query);
		$class= $this->metaDriver->getNamespace().'\MetaTableMySqlDriverList';

		return $class::createFromResult($rows);
	}


	public function getMetaTableColumnsMySqlDriverList($database,$table):DataCollection {
		$query=$this->metaSqlQuery->getTableQuery($database,$table);
		$rows=$this->db->query($query);
		$class= $this->metaDriver->getNamespace().'\MetaTableColumnsMySqlDriverList';
		$driver=$this->getDriverName();
		$class=$this->metaVariable->getClassListFromClassName(MetaDriverGenerator::DB_COLUMN_PREFIX.$driver,true);
		return $class::createFromResult($rows);
	}



	public function getMetaTableColumnsForeignKeyMySqlDriverList($database,$table):DataCollection {
		$query=$this->metaSqlQuery->getTableForenKeyQuery($database,$table);
		$rows=$this->db->query($query);

		$driver=$this->getDriverName();
		$class=$this->metaVariable->getClassListFromClassName(MetaDriverGenerator::DB_FOREIN_KEYS_PREFIX.$driver,true);

		return $class::createFromResult($rows);
	}

	/**
	 * @param string $datebase
	 *
	 * @return ForeignKeyList
	 * @throws ForeignKeyException
	 */
	public function getForeinKeyList(string $datebase ):ForeignKeyList {
		$metaTableMySqlDriverList=$this->getMetaTableMySqlDriverList($datebase);

		if ($metaTableMySqlDriverList instanceof MetaTableMySqlDriverList){
			$foreignKeyList= new ForeignKeyList($metaTableMySqlDriverList,$this,$datebase);
			return  $foreignKeyList;
		}else{
			throw new ForeignKeyException(get_class($metaTableMySqlDriverList). ' not implemented');
		}
	}


	/**
	 * @return bool
	 */
	public function hasExtraMetaTableColumnsMySqlDriverList(): bool {
		return false;
	}

	/**
	 * @return MetaTableColumnsMySqlDriverList|null
	 */
	public function getExtraMetaTableColumnsMySqlDriverList(): ?MetaTableColumnsMySqlDriverList {
		return null;
	}

	/**
	 * @return MetaVariable
	 */
	public function getMetaVariable(): MetaVariable {
		return $this->metaVariable;
	}

	protected function addExtraFeatures( ClassType $class ) {
		// TODO: Implement addExtraFeatures() method.
	}
}