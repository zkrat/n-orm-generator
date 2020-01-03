<?php


namespace NOrmGenerator\ClassModelGenerator\Meta;


use NOrmGenerator\ClassModelGenerator\Helpers\StringManipulator;
use NOrmGenerator\ClassModelGenerator\Meta\Exception\PropertyGeneratorException;
use NOrmGenerator\ClassModelGenerator\Meta\Traits\TraitMethodBuilder;
use NOrmGenerator\ClassModelGenerator\TraitGetterSetter;
use NOrmGenerator\ClassModelGenerator\Variables\MetaVariableConfiguration;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;

abstract class CorePropertyGenerator {


	use TraitGetterSetter;
	use TraitMethodBuilder;
	/**
	 * @var ClassType
	 */
	protected $class;
	/**
	 * @var PhpNamespace
	 */
	protected $phpNamespace=null;



	protected $substituteUse=true;

	protected $useArray=[];

	/**
	 * @var MetaVariableConfiguration
	 */
	protected $metaVariableConfiguration;


	public function __construct(MetaVariableConfiguration $metaVariableConfiguration,string $className) {
		$namespace = $metaVariableConfiguration->getNamespace();
		$this->metaVariableConfiguration = $metaVariableConfiguration;
		$phpNamespace=new PhpNamespace($namespace);
		$this->phpNamespace=$phpNamespace;
		$this->class=$phpNamespace->addClass($className);
	}

	/**
	 * @return ClassType
	 */
	public function getClass(): ClassType {
		return $this->class;
	}





	/**
	 * @throws PropertyGeneratorException
	 */
	public function disableSubstituteUse(): void {
		if (count($this->useArray)==0)
			$this->substituteUse=false;
		else{
			throw new PropertyGeneratorException(PropertyGeneratorException::MSG_PROPERTY_IS_ALREADY_ADDED,PropertyGeneratorException::PROPERTY_IS_ALREADY_ADDED);

		}
	}

	/**
	 * @return PhpNamespace|null
	 */
	public function getPhpNamespace(): ?PhpNamespace {
		return $this->phpNamespace;
	}


}