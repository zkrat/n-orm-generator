<?php


namespace NOrmGenerator\ClassModelGenerator\Variables;


use NOrmGenerator\ClassModelGenerator\Helpers\StringManipulator;
use Model\DbDriver\MetaTableColumnsMySqlDriver;

class MetaVariable {

	/**
	 * @var string
	 */
	private $tableName;

	/**
	 * @var string
	 */
	private $tableNameCamelCase;
	/**
	 * @var MetaVariableConfiguration
	 */
	private $metaVariableConfiguration;

	public function __construct(MetaVariableConfiguration $metaVariableConfiguration) {
		$this->metaVariableConfiguration =$metaVariableConfiguration;
	}



	public static function createFomCofiguration( MetaVariableConfiguration $metaEntity ):MetaVariable {
		return new static($metaEntity);
	}

	public function getClassRowName(MetaTableColumnsMySqlDriver $metaTableColoumnsMySqlDriver,$includeNamespace=false):string{
		return $this->getClassRowNameFromString($metaTableColoumnsMySqlDriver->getTableName(),$includeNamespace);
	}

	public function getClassRowNameGetMethodFromString(string $tableName):string{
		return 'get'.$this->getClassRowNameFromString($tableName);
	}

	public function getClassRowNameGetMethod(MetaTableColumnsMySqlDriver $metaTableColoumnsMySqlDriver):string{
		return  $this->getClassRowNameGetMethodFromString($metaTableColoumnsMySqlDriver->getTableName());
	}

	public function getClassRowNameFromString(string $tableName,bool $includeNamespace=false):string{
		$classRow=$this->underscoreToCamelCase($tableName,true);
		$classRow= $this->metaVariableConfiguration->getClassPrefix() . $classRow;
		if($includeNamespace===true)
			$classRow=$this->metaVariableConfiguration->getNamespace().'\\'.$classRow;
		return $classRow.$this->metaVariableConfiguration->getClassSuffix();
	}


	public function getClassRowVariableName(MetaTableColumnsMySqlDriver $metaTableColoumnsMySqlDriver,bool $includeDollar=true):string{
		return $this->getClassRowVariableNameFromString($metaTableColoumnsMySqlDriver->getTableName(),$includeDollar);
	}

	public function getClassRowVariableNameFromString(string $tableName, bool $includeDollar=true):string{
		$var=$this->getClassRowNameFromString($tableName);
		return  $this->addDolar($var,$includeDollar);
	}
	public function getClassListVariableFromString(string $tableName,bool $includeDollar=true):string{
		$var=$this->getClassListFromString($tableName);
		return  $this->addDolar($var,$includeDollar);
	}

	public function addDolar(string $var,bool $includeDollar=true){
		$var=lcfirst($var);
		if($includeDollar)
			return '$'.$var;
		else
			return $var;
	}
	public function getClassListGetAllIdsArrayMethodFromString(string $tableName):string {
		return 'get'.$this->getClassListFromString($tableName).'Ids';
	}

	public function getClassListMethodAllByIdFromString(string $tableName):string{
		return 'getAll'.$this->getClassListFromString($tableName).'ByIds';
	}

	public function getClassListFromString(string $tableName,bool $includeNamespace=false):string{
		$classListPrefix=$this->metaVariableConfiguration->getClassListPrefix();
		$classListSuffix=$this->metaVariableConfiguration->getClassListSuffix();
		$classList =  $classListPrefix.$this->underscoreToCamelCase($tableName,true).$classListSuffix;
//		$classList =  $classListPrefix.$this->underscoreToCamelCase($tableName,strlen($classListPrefix)==0).$classListSuffix;
		if($includeNamespace)
			$classList = $this->getClassWithNamespace($classList);
		return $classList ;
	}

	public  function getClassWithNamespace( $className ):?string {
		$namespace = $this->metaVariableConfiguration->getNamespace();
		return $namespace.'\\'.$className;
	}



	public  function getClassListFromClassName( $className ,bool $includeNamespace=false):?string {
		$classList=$this->metaVariableConfiguration->getClassListPrefix().$className.$this->metaVariableConfiguration->getClassListSuffix();
		$namespace = $this->metaVariableConfiguration->getNamespace();
		if($includeNamespace)
			return $namespace.'\\'.$classList;
		else
			return $classList;
	}

	private function underscoreToCamelCase(string $className,bool $capitalizeFirstCharacter = false,$separator='_'):string {
		if (strpos($className, $separator) === false) {

			if ($capitalizeFirstCharacter)
				$className=ucfirst($className);

			return  $className;
		} else {
			return StringManipulator::underscoreToCamelCase($className,$capitalizeFirstCharacter,$separator);
		}
	}


	public function getNamespace():?string {
		return $this->metaVariableConfiguration->getNamespace();
	}

	/**
	 * @return MetaVariableConfiguration
	 */
	public function getMetaVariableConfiguration(): MetaVariableConfiguration {
		return $this->metaVariableConfiguration;
	}

	public function getMethodReferenceId( string $tableName ):string {
		$classTableName=$this->underscoreToCamelCase($tableName,true);
		return 'get'.$classTableName.'Id()';
	}

}