<?php


namespace NOrmGenerator\ClassModelGenerator\Meta;


use NOrmGenerator\ClassModelGenerator\Helpers\StringManipulator;
use NOrmGenerator\ClassModelGenerator\Meta\Exception\PropertyGeneratorException;
use NOrmGenerator\ClassModelGenerator\Variables\AbstractVariables;
use NOrmGenerator\ClassModelGenerator\Variables\MySql\Bit;
use NOrmGenerator\ClassModelGenerator\Variables\MySql\Blob;
use NOrmGenerator\ClassModelGenerator\Variables\MySql\Hex;
use NOrmGenerator\ClassModelGenerator\Variables\MySql\Set;
use NOrmGenerator\ClassModelGenerator\Variables\MySql\Time;
use Model\DbDriver\MetaTableColumnsMySqlDriver;
use Nette\Utils\DateTime;
use Tracy\Debugger;
use ReflectionClass;

class MySqlPhpProperty {
	/**
	 * @var MetaTableColumnsMySqlDriver
	 */
	private $metaTableColoumnsMySqlDriver;

	/**
	 * @var string
	 */
	private $phpVariableType;

	/**
	 * @var ReflectionClass|null
	 */
	private $reflectionClass;

	public function __construct(MetaTableColumnsMySqlDriver $metaTableColoumnsMySqlDriver) {
		$this->metaTableColoumnsMySqlDriver = $metaTableColoumnsMySqlDriver;
		$this->phpVariableType              = $this->detectPhpVariableType($metaTableColoumnsMySqlDriver);
		$this->reflectionClass              = $this->createReflectionClass($this->phpVariableType);
	}


	public static function create(MetaTableColumnsMySqlDriver $metaTableColoumnsMySqlDriver):MySqlPhpProperty {
		$class= new static($metaTableColoumnsMySqlDriver);
		return $class;
	}

	/**
	 * @param string $class
	 *
	 * @return bool
	 */
	public function isSubclassOf(string $class):bool{
		if( $this->reflectionClass instanceof  ReflectionClass && $this->reflectionClass->isSubclassOf( $class )){
			return  true;
		}
		return false;
	}

	/**
	 * @param string $interface
	 *
	 * @return bool
	 */
	public function implementsInterface(string $interface):bool {
		if( $this->reflectionClass instanceof  ReflectionClass && $this->reflectionClass->implementsInterface( $interface )){
			return  true;
		}
		return false;
	}


	public function overwriteReflectionClass(string $className):?ReflectionClass {
		$this->reflectionClass=$this->createReflectionClass($className);
		return  $this->reflectionClass;
	}

	/**
	 * @param string $className
	 *
	 * @return ReflectionClass|null
	 */
	private function createReflectionClass(string $className):?ReflectionClass {
		if (class_exists($className)){
			try{
				$reflectionClass = new ReflectionClass( $className );
				return  $reflectionClass;

			}catch (\ReflectionException $e){
				Debugger::barDump($e->getMessage(),'ReflectionException');
			}
		}
		return  null;
	}

	private function detectPhpVariableType(MetaTableColumnsMySqlDriver $metaTableColoumnsMySqlDriver ){
		$columnType=$metaTableColoumnsMySqlDriver->getColumnType();
		[$varType,$length,$lengthDetail] = $this->typeToArray($columnType);
		switch ($varType){
			case 'int':
			case 'tinyint':
			case 'smallint':
			case 'mediumint':
			case 'int':
			case 'bigint':
				return 'int';
			case 'bit':
				return Bit::class;
			case 'decimal':
			case 'float':
			case 'double':
				return 'float';
			case 'binary':
			case 'varbinary':
				return Hex::class;
			case 'char':
			case 'varchar':
			case 'tinytext':
			case 'text':
			case 'mediumtext':
			case 'longtext':
				return 'string';
			case 'tinyblob':
			case 'blob':
			case 'mediumblob':
			case 'longblob':
				return Blob::class;

			case 'date':
			case 'datetime':
			case 'timestamp':
				return DateTime::class;
			case 'linestring':
			case 'geometry':
				return \LineString::class;
			case 'point':
				return \Point::class;
			case 'time':
				return Time::class;
			case 'year':
				return 'string';
			case 'polygon':
				return \Polygon::class;
			case 'multipoint':
				return \MultiPoint::class;
			case 'multipolygon':
				return \MultiPolygon::class;
			case 'multilinestring':
				return \MultiLineString::class;
			case 'multilinestring':
				return \MultiLineString::class;
			case 'geometrycollection':
				return \GeometryCollection::class;
			case 'enum':
				return 'string';
			case 'set':
				return Set::class;
			default:
				throw new PropertyGeneratorException(PropertyGeneratorException::MSG_PROPERTY_TYPE_IS_NOT_DEFINED,PropertyGeneratorException::PROPERTY_TYPE_IS_NOT_DEFINED);
		}


	}

	private function typeToArray(string $columnType):array{
		$varType=$length=$lengthDetail=null;
		$strpos =strpos($columnType,'(');
		if($strpos!==FALSE){
			$varType =substr($columnType,0,$strpos);

			$lengthString =substr($columnType,strlen($varType)+1,-1);
			switch ($varType){
				case MySqlPropertyGenerator::VAR_TYPE_ENUM:
				case MySqlPropertyGenerator::VAR_TYPE_SET:
					$lengthString =substr($lengthString,1,-1);
					$length =explode('\',\'',$lengthString);
					break;
				default:
					$strpos=strpos($lengthString,',');
					if($strpos!==FALSE){
						[$length,$lengthDetail]=explode(',',$lengthString);
					}
			}

		}else{
			$varType=$columnType;
		}

		return [$varType,$length,$lengthDetail];
	}

	/**
	 * @return string
	 */
	public function getPhpVariableType($includeNamespace=false): string {
		$phpVariableType=$this->phpVariableType;
		if($includeNamespace==false && $this->isPhpVariableTypeClass() && strrpos($phpVariableType,'\\')>0){
			$phpVariableType=substr($phpVariableType,strrpos($phpVariableType,'\\')+1);
		}
		return $phpVariableType;
	}
	public function isPhpVariableTypeClass():bool{
		return class_exists($this->phpVariableType);
	}

	/**
	 * @return string
	 */
	public function getProperty():string {
		return StringManipulator::underscoreToCamelCase($this->metaTableColoumnsMySqlDriver->getColumnName());
	}


	/**
	 * @return bool
	 */
	private function hasReflectionClass(): bool {
		return $this->reflectionClass instanceof  ReflectionClass;
	}

	public function getColumnSelection() {
		$columnName =$this->metaTableColoumnsMySqlDriver->getColumnName();
		if($this->reflectionClass instanceof  ReflectionClass && $this->reflectionClass->isSubclassOf(AbstractVariables::class)){
			$class =$this->phpVariableType;
			/**
			 * @var AbstractVariables $class
			 */
			$META_SUBSTITUTION=$this->reflectionClass->getConstant('META_SUBSTITUTION');
			$META_SELECT=$this->reflectionClass->getConstant('META_SELECT');
			$columnName=str_replace($META_SUBSTITUTION,$columnName,$META_SELECT);
		}
		return $columnName;

	}

}