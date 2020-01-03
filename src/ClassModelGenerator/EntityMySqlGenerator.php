<?php


namespace NOrmGenerator\ClassModelGenerator;


use NOrmGenerator\ClassModelGenerator\Helpers\StringManipulator;
use NOrmGenerator\ClassModelGenerator\Logger\ILogger;
use NOrmGenerator\ClassModelGenerator\Meta\MySqlPhpProperty;
use NOrmGenerator\ClassModelGenerator\Meta\MySqlPropertyGenerator;
use NOrmGenerator\ClassModelGenerator\Traits\TraitClassFill;
use NOrmGenerator\ClassModelGenerator\Variables\IVariable;
use NOrmGenerator\ClassModelGenerator\Variables\MetaVariable;
use Model\DbDriver\MetaTableColumnsForeinKeysMySqlDriver;
use Model\DbDriver\MetaTableColumnsMySqlDriver;
use Model\DbDriver\MetaTableColumnsMySqlDriverList;
use Nette\Database\Context;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;

class EntityMySqlGenerator extends CoreGenerator {
	/**
	 * @var Context
	 */
	protected $db;

	/**
	 * @var DbAccessMySqlGenerator
	 */
	protected $parent;

	const CONFIG_NAMESPACE='namespaceEntity';
	const CONFIG_DIR='dirEntity';

	const PRIMARY_KEY='PRI';

	public function __construct($config,Context $db ,ILogger $logger=null,$parent=null) {
		parent::__construct($config,$db,$logger,$parent);
		$this->metaVariable = MetaVariable::createFomCofiguration($this->metaEntity);
	}

	public function generateFromMetaTableColoumnsMySqlDriverList( MetaTableColumnsMySqlDriverList $metaTableColoumnsMySqlDriverList ,ForeignKeyList $forienKeyList) {
		$tableName=$getter=$classEntity=$classRow=null;

		foreach ($metaTableColoumnsMySqlDriverList as $metaTableColoumnsMySqlDriver){
			/**
			 * @var MetaTableColumnsMySqlDriver $metaTableColoumnsMySqlDriver
			 */
			if ($metaTableColoumnsMySqlDriver instanceof MetaTableColumnsMySqlDriver){

				if(is_null($classRow)){
					$tableName=$metaTableColoumnsMySqlDriver->getTableName();

					$classRow=$this->metaVariable->getClassRowName($metaTableColoumnsMySqlDriver);

					$classEntity = new MySqlPropertyGenerator($this->metaEntity,$classRow);
					$classEntity->addTrait(TraitClassFill::class);
					$classEntity->generateGetter();

					foreach ($forienKeyList->getTableForeignKeyArray($tableName) as $metaTableColumnsForeinKeysMySqlDriver){
						/**
						 * @var MetaTableColumnsForeinKeysMySqlDriver $metaTableColumnsForeinKeysMySqlDriver
						 */
						$classEntity->addMetaTableColumnsForeinKeysMySqlDriverForeignKey($metaTableColumnsForeinKeysMySqlDriver,$this,$tableName);
					}

					foreach ($forienKeyList->getTableReferenceArray($tableName) as $metaTableColumnsForeinKeysMySqlDriver){
						/**
						 * @var MetaTableColumnsForeinKeysMySqlDriver $metaTableColumnsForeinKeysMySqlDriver
						 */
						$classEntity->addMetaTableColumnsForeinKeysMySqlDriverReference($metaTableColumnsForeinKeysMySqlDriver,$this,$tableName);
					}
				}
				if ($metaTableColoumnsMySqlDriver instanceof MetaTableColumnsMySqlDriver && $classEntity instanceof  MySqlPropertyGenerator)
					$classEntity->addMetaTableColumnsMySqlDriver($metaTableColoumnsMySqlDriver);

				if ($metaTableColoumnsMySqlDriver->getColumnKey() == EntityMySqlGenerator::PRIMARY_KEY){
					$parameter=lcfirst($classRow);
					$getter='$'.$parameter.'->get'.ucfirst($metaTableColoumnsMySqlDriver->getColumnName()).'()';
				}
			}
		}

		if(!is_null($tableName)){

		$phpNamespaceClassList=$this->createClassListFromTableName($tableName,$getter,$forienKeyList);
			if (count($phpNamespaceClassList->getClasses())==1){
				foreach ($phpNamespaceClassList->getClasses() as $classList){
					/**
					 * @var ClassType $classList
					 */

					$typeHint=$phpNamespaceClassList->getName().'\\'.$classList->getName();
					if ($classEntity instanceof  MySqlPropertyGenerator){
						if(!$classEntity->getClass()->hasProperty('parent')){
							$property=$classEntity->getClass()->addProperty('parent')->setVisibility(ClassType::VISIBILITY_PROTECTED);
							$property->addComment('@var '.$classList->getName().' $parent');
						}

						$methodSetParent=$classEntity->getClass()->addMethod('setParent');
						$methodSetParent->addParameter('parent')->setTypeHint($typeHint);
						$methodSetParent->addComment('@var '.$classList->getName());
						$methodSetParent->addBody('$this->parent= $parent;');

					}



				}
			}
		}
		if (is_string($classRow) && strlen($classRow)>0 && $classEntity instanceof  MySqlPropertyGenerator && $classEntity->getPhpNamespace() instanceof PhpNamespace){
			$this->addExtraFeatures($classEntity->getClass());
			$this->saveFile($this->metaEntity,$classEntity->getPhpNamespace(),$classRow);

		}




	}

	/**
	 * @return DbAccessMySqlGenerator
	 */
	public function getParent(): DbAccessMySqlGenerator {
		return $this->parent;
	}


	/**
	 * @return bool
	 */
	public function hasExtraMetaTableColumnsMySqlDriverList(): bool {
		return $this->getParent()->hasExtraMetaTableColumnsMySqlDriverList();
	}

	/**
	 * @return MetaTableColumnsMySqlDriverList|null
	 */
	public function getExtraMetaTableColumnsMySqlDriverList(): ?MetaTableColumnsMySqlDriverList {
		return  $this->getParent()->getExtraMetaTableColumnsMySqlDriverList();
	}

	protected function addExtraFeatures( ClassType $class ) {

		if($this->hasParent() && !is_null($class->getNamespace())){
			$dbAccessMysqlGenerator=$this->getParent();

			/**
			 * @var DbAccessMySqlGenerator $dbAccessMysqlGenerator
			 */
			if ($dbAccessMysqlGenerator->hasGeoProperty()){
				$staticCreateMetod=$class->addMethod('create')->setStatic(true);
				$staticCreateMetod->addParameter('array')->setTypeHint('array');
				$class->getNamespace()->addUse(\geoPHP::class);
				$staticCreateMetod->addBody('$class =new static();');
				if (!is_null($dbAccessMysqlGenerator->getExtraMetaTableColumnsMySqlDriverList())){
					foreach ($dbAccessMysqlGenerator->getExtraMetaTableColumnsMySqlDriverList() as $metaTableColumnsMySqlDriver){
						/**
						 * @var MetaTableColumnsMySqlDriver $metaTableColumnsMySqlDriver
						 */
						$mySqlPhpProperty=MySqlPhpProperty::create($metaTableColumnsMySqlDriver);
						$property=$mySqlPhpProperty->getProperty();
						$mySqlPhpProperty->getColumnSelection();
						$columnName=$metaTableColumnsMySqlDriver->getColumnName();


						if($mySqlPhpProperty->isSubclassOf(\Geometry::class)){
							$staticCreateMetod->addBody('$class->'.$property.' =geoPHP::load($array[\''.$columnName.'\']);');
						}elseif($mySqlPhpProperty->implementsInterface(IVariable::class)){
							$className = $mySqlPhpProperty->getPhpVariableType();
							$staticCreateMetod->addBody('$class->'.$property.' ='.$className.'::create($array[\''.$columnName.'\']);');
						}else{
							$staticCreateMetod->addBody('$class->'.$property.' =$array[\''.$columnName.'\'];');
						}


					}
				}

				$staticCreateMetod->addBody('return $class;');
			}


		}
	}

	/**
	 * @return MetaVariable
	 */
	public function getMetaVariable(): MetaVariable {
		return $this->metaVariable;
	}
}