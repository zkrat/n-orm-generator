<?php


namespace NOrmGenerator\ClassModelGenerator\Variables;


class MetaVariableConfiguration {



	const CONFIG_DB=[
		'NAMESPACE'=>'dbNamespace',
		'DIR'=>'dbDir',
		'CLASS_PREFIX'=>'dbPrefix',
		'CLASS_SUFFIX'=>'dbSuffix',
		'LIST_PREFIX'=>'dbListPrefix',
		'LIST_SUFFIX'=>'dbListSuffix'
	];




	const CONFIG_DRIVER=[
		'NAMESPACE'=>'driverNamespace',
		'DIR'=>'driverDir',
		'CLASS_PREFIX'=>'driverClassPrefix',
		'CLASS_SUFFIX'=>'driverClassSuffix',
		'LIST_PREFIX'=>'driverClassListPrefix',
		'LIST_SUFFIX'=>'driverClassListSuffix'
	];

	const CONFIG_ENTITY=[
		'NAMESPACE'=>'entityNamespace',
		'DIR'=>'entityDir',
		'CLASS_PREFIX'=>'entityRowPrefix',
		'CLASS_SUFFIX'=>'entityRowSuffix',
		'LIST_PREFIX'=>'entityListPrefix',
		'LIST_SUFFIX'=>'entityListSuffix'
	];


	const CONFIG_OVERWRITE='overwrite';

	const CONFIG_DELETE_GEN_FILE='deleteGenFile';

	/**
	 * @var string|null
	 */
	protected $dir;

	/**
	 * @var string|null
	 */
	protected $namespace;

	/**
	 * @var string|null
	 */
	protected $classPrefix;

	/**
	 * @var string|null
	 */
	protected $classSuffix;

	/**
	 * @var string|null
	 */
	protected $classListPrefix;

	/**
	 * @var string|null
	 */
	protected $classListSuffix;


	/**
	 * @var bool
	 */
	protected $overwrite;

	/**
	 * @var bool
	 */
	private $deleteGenFile;

	/**
	 * MetaVariableConfiguration constructor.
	 *
	 * @param string|null $dir
	 * @param string|null $namespace
	 * @param string|null $classSuffix
	 * @param string|null $classPrefix
	 * @param string|null $classListSuffix
	 * @param string|null $classListPrefix
	 */
	private function __construct( array $values, ?array $config) {
		$this->dir             = isset($values[$config['DIR']]) ? $values[$config['DIR']] : null;
		$this->namespace       = isset($values[$config['NAMESPACE']]) ? $values[$config['NAMESPACE']] : null;
		$this->classSuffix     = isset($values[$config['CLASS_SUFFIX']]) ? $values[$config['CLASS_SUFFIX']] : null;
		$this->classPrefix     = isset($values[$config['CLASS_PREFIX']]) ? $values[$config['CLASS_PREFIX']] : null;
		$this->classListSuffix = isset($values[$config['LIST_SUFFIX']]) ? $values[$config['LIST_SUFFIX']] : null;
		$this->classListPrefix = isset($values[$config['LIST_PREFIX']]) ? $values[$config['LIST_PREFIX']] : null;
		$this->overwrite       = isset($values[MetaVariableConfiguration::CONFIG_OVERWRITE]) ? $values[MetaVariableConfiguration::CONFIG_OVERWRITE] : false;
		$this->deleteGenFile   =isset($values[MetaVariableConfiguration::CONFIG_DELETE_GEN_FILE]) ? $values[MetaVariableConfiguration::CONFIG_DELETE_GEN_FILE] : false;
	}


	public static function createDb(array $values):MetaVariableConfiguration {
		return new static($values,MetaVariableConfiguration::CONFIG_DB );
	}

	public static function createEntity(array $values):MetaVariableConfiguration {
		return new static($values,MetaVariableConfiguration::CONFIG_ENTITY );
	}


	public static function createDriver(array $values):MetaVariableConfiguration {
		return new static($values,MetaVariableConfiguration::CONFIG_DRIVER );
	}

	/**
	 * @return string|null
	 */
	public function getDir(): ?string {
		return $this->dir;
	}

	/**
	 * @return string|null
	 */
	public function getNamespace(): ?string {
		return $this->namespace;
	}

	/**
	 * @return string|null
	 */
	public function getClassSuffix(): ?string {
		return $this->classSuffix;
	}

	/**
	 * @return string|null
	 */
	public function getClassPrefix(): ?string {
		return $this->classPrefix;
	}

	/**
	 * @return string|null
	 */
	public function getClassListSuffix(): ?string {
		return $this->classListSuffix;
	}

	/**
	 * @return string|null
	 */
	public function getClassListPrefix(): ?string {
		return $this->classListPrefix;
	}

	/**
	 * @return bool
	 */
	public function isOverwrite(): bool {
		return $this->overwrite;
	}

	/**
	 * @return bool
	 */
	public function isDeleteGenFile(): bool {
		return $this->deleteGenFile;
	}

	/**
	 * @return bool
	 */
	public function hasClassPrefix():bool {
		return strlen($this->classPrefix)>0;
	}


}