<?php


namespace NOrmGenerator\ClassModelGenerator\Meta;

use NOrmGenerator\ClassModelGenerator\ConstantDefinition;
use NOrmGenerator\ClassModelGenerator\EntityMySqlGenerator;
use NOrmGenerator\ClassModelGenerator\Helpers\StringManipulator;
use NOrmGenerator\ClassModelGenerator\Meta\Exception\PropertyGeneratorException;
use NOrmGenerator\ClassModelGenerator\Variables\MySql\Bit;
use NOrmGenerator\ClassModelGenerator\Variables\MySql\Blob;
use NOrmGenerator\ClassModelGenerator\Variables\MySql\Hex;
use NOrmGenerator\ClassModelGenerator\Variables\MySql\Set;
use NOrmGenerator\ClassModelGenerator\Variables\MySql\Time;
use Model\DbDriver\MetaTableColumnsMySqlDriver;

use Nette\PhpGenerator\PhpNamespace;
use Nette\Utils\DateTime;
use Model\DbDriver\MetaTableColumnsForeinKeysMySqlDriver;


class MySqlPropertyGenerator extends CorePropertyGenerator {

	const IS_NULL_VALUE='YES';

	const VAR_TYPE_ENUM='enum';

	const VAR_TYPE_SET='set';

	public function addMetaTableColumnsMySqlDriver( MetaTableColumnsMySqlDriver $metaTableColoumnsMySqlDriver= null ) {

		if ($metaTableColoumnsMySqlDriver instanceof MetaTableColumnsMySqlDriver){
			$mySqlPhpProperty=MySqlPhpProperty::create($metaTableColoumnsMySqlDriver);
			$type =$mySqlPhpProperty->getPhpVariableType(TRUE);
			$typeComment =$mySqlPhpProperty->getPhpVariableType();
			if($mySqlPhpProperty->isPhpVariableTypeClass() && $this->getPhpNamespace() instanceof PhpNamespace) {
				$fullClassName =$mySqlPhpProperty->getPhpVariableType(true);
				$this->getPhpNamespace()->addUse($fullClassName);
			}

			$property=StringManipulator::underscoreToCamelCase($metaTableColoumnsMySqlDriver->getColumnName());
			$nullable=  ($metaTableColoumnsMySqlDriver->getIsNullable()==MySqlPropertyGenerator::IS_NULL_VALUE) ? true : false;


			$this->createGetter($this->class,$property,$type,$typeComment,$nullable);
			$this->createSetter($this->class,$property,$type,$typeComment);
		}
	}


	public function addMetaTableColumnsForeinKeysMySqlDriverReference( MetaTableColumnsForeinKeysMySqlDriver $metaTableColumnsForeinKeysMySqlDriver,EntityMySqlGenerator $entityMySqlGenerator,string $tableName ) {
		$tableName=$metaTableColumnsForeinKeysMySqlDriver->getTableName();

		$fullTableNameList=$entityMySqlGenerator->getMetaVariable()->getClassListFromString($tableName,true);
		$tableNameList=$entityMySqlGenerator->getMetaVariable()->getClassListFromString($tableName);
		$this->phpNamespace->addUse($fullTableNameList);
		$propertyList=lcfirst($tableNameList);
		$this->generateSetter();
		$this->createSetter($this->class,$propertyList,$fullTableNameList,$tableNameList,true);
		$this->createGetter($this->class,$propertyList,$fullTableNameList,$tableNameList,false);
		$this->setter = false;

		$classRow=$entityMySqlGenerator->getMetaVariable()->getClassRowNameFromString($tableName);
		$fullClassRow=$entityMySqlGenerator->getMetaVariable()->getClassRowNameFromString($tableName,true);
		$propertyRow=lcfirst($classRow);

		$this->phpNamespace->addUse($fullClassRow);

		$method=$this->class->addMethod($this->getAddClassMethod($classRow));


		$method->addComment('@var '.$classRow.' '.$propertyRow);
		$method->addParameter($propertyRow)->setTypeHint($fullClassRow);
		$method->addBody('$this->'.$propertyList.'->add'.$classRow.'($'.$propertyRow.');');

	 	$construct = $this->class->hasMethod('__construct') ? $this->class->getMethod('__construct') : $this->class->addMethod('__construct');
		$construct->addBody('$this->'.$propertyList.' = '.$tableNameList.'::createEmpty();');





		$metaEntity=$entityMySqlGenerator;


		/**
		 * @var MetaTableColumnsForeinKeysMySqlDriver $metaTableColumnsForeinKeysMySqlDriver
		 */
		$getrefId=$metaEntity->getMetaVariable()->getMethodReferenceId($metaTableColumnsForeinKeysMySqlDriver->getReferencedTableName());
		$classList= $metaEntity->getMetaVariable()->getClassListFromString($metaTableColumnsForeinKeysMySqlDriver->getTableName());
		$classListNameSpace= $metaEntity->getMetaVariable()->getClassListFromString($metaTableColumnsForeinKeysMySqlDriver->getTableName(),true);
		$classListVariable= $metaEntity->getMetaVariable()->getClassListVariableFromString($metaTableColumnsForeinKeysMySqlDriver->getTableName(),false);
		$classListVariableDollar= $metaEntity->getMetaVariable()->getClassListVariableFromString($metaTableColumnsForeinKeysMySqlDriver->getTableName());

		$classRow=$metaEntity->getMetaVariable()->getClassRowNameFromString($metaTableColumnsForeinKeysMySqlDriver->getTableName());
		$classRowNamespace=$metaEntity->getMetaVariable()->getClassRowNameFromString($metaTableColumnsForeinKeysMySqlDriver->getTableName(),true);


		$refClassRow=$metaEntity->getMetaVariable()->getClassRowNameFromString($metaTableColumnsForeinKeysMySqlDriver->getReferencedTableName());
		$refClassRowNamespace=$metaEntity->getMetaVariable()->getClassRowNameFromString($metaTableColumnsForeinKeysMySqlDriver->getReferencedTableName(),true);
		$refClassRowVariable= $metaEntity->getMetaVariable()->getClassRowVariableNameFromString($metaTableColumnsForeinKeysMySqlDriver->getReferencedTableName(),false);
		$refClassRowVariableDollar= $metaEntity->getMetaVariable()->getClassRowVariableNameFromString($metaTableColumnsForeinKeysMySqlDriver->getReferencedTableName());


		$classRowVariable= $metaEntity->getMetaVariable()->getClassRowVariableNameFromString($metaTableColumnsForeinKeysMySqlDriver->getTableName(),false);
		$this->getAssocClassMethod($metaTableColumnsForeinKeysMySqlDriver->getTableName());
		$classRowVariableDollar= $metaEntity->getMetaVariable()->getClassRowVariableNameFromString($metaTableColumnsForeinKeysMySqlDriver->getTableName());

		$dataProperty=$entityMySqlGenerator->getDataPropertyByClass($this->class,$metaTableColumnsForeinKeysMySqlDriver->getReferencedTableName());

		$assocClassList = $this->getAssocClassMethod( $classList );
		$method =$this->class->addMethod( $assocClassList);



		$method->addParameter($classListVariable)->setTypeHint($classListNameSpace);
		$method->addBody('foreach ('.$classListVariableDollar.' as '.$classRowVariableDollar.'){');

		$method->addBody(ConstantDefinition::TAB1.'/**');
		$method->addBody(ConstantDefinition::TAB1.' * '.$classRow.' '.$classRowVariableDollar);
		$method->addBody(ConstantDefinition::TAB1.' */');

		$method->addBody(ConstantDefinition::TAB1.$refClassRowVariableDollar.' = $this->'.$dataProperty.'['.$classRowVariableDollar.'->'.$getrefId.'];');


		$method->addBody(ConstantDefinition::TAB1.'/**');
		$method->addBody(ConstantDefinition::TAB1.' * '.$refClassRow.' '.$refClassRowVariableDollar);
		$method->addBody(ConstantDefinition::TAB1.' */');
		$addClassMethod = $this->getAddClassMethod( $classList );
		$method->addBody(ConstantDefinition::TAB1.'$this->'.$dataProperty.'['.$classRowVariableDollar.'->'.$getrefId.']->'.$addClassMethod.'('.$refClassRowVariableDollar.');');
		$method->addBody('}');

	}


	public function addMetaTableColumnsForeinKeysMySqlDriverForeignKey( MetaTableColumnsForeinKeysMySqlDriver $metaTableColumnsForeinKeysMySqlDriver, EntityMySqlGenerator $entityMySqlGenerator, string $tableName ) {
		$tableName=$metaTableColumnsForeinKeysMySqlDriver->getReferencedTableName();

		$classRow=$entityMySqlGenerator->getMetaVariable()->getClassRowNameFromString($tableName);
		$fullClassRow=$entityMySqlGenerator->getMetaVariable()->getClassRowNameFromString($tableName,true);
		$propertyRow=lcfirst($classRow);
		$this->phpNamespace->addUse($fullClassRow);
		$this->generateSetter();



		$this->createSetter($this->class,$propertyRow,$fullClassRow,$classRow,true);
		$this->createGetter($this->class,$propertyRow,$fullClassRow,$classRow,false);
		$this->setter = false;

	}


	private  static  function typeToArray(string $columnType):array{
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

	public function addTrait( string $trail ) {
		if (trait_exists($trail)){
			$this->phpNamespace->addUse($trail);
			$this->class->addTrait($trail);
		}

	}
}