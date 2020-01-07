<?php


namespace NOrmGenerator\ClassModelGenerator\Variables;


class MetaVariableConfiguration {

    const NAMESPACE='NAMESPACE';
	const DIR='DIR';
	const CLASS_PREFIX='CLASS_PREFIX';
	const CLASS_SUFFIX='CLASS_SUFFIX';
    const LIST_PREFIX ='LIST_PREFIX';
	const LIST_SUFFIX='LIST_SUFFIX';


	const CONFIG_DB=[
		self::NAMESPACE=>'dbNamespace',
		self::DIR=>'dbDir',
		self::CLASS_PREFIX=>'dbPrefix',
		self::CLASS_SUFFIX=>'dbSuffix',
		self::LIST_PREFIX=>'dbListPrefix',
		self::LIST_SUFFIX=>'dbListSuffix'
	];




	const CONFIG_DRIVER=[
		self::NAMESPACE=>'driverNamespace',
		self::DIR=>'driverDir',
		self::CLASS_PREFIX=>'driverClassPrefix',
		self::CLASS_SUFFIX=>'driverClassSuffix',
		self::LIST_PREFIX=>'driverClassListPrefix',
		self::LIST_SUFFIX=>'driverClassListSuffix'
	];

	const CONFIG_ENTITY=[
		self::NAMESPACE=>'entityNamespace',
		self::DIR=>'entityDir',
		self::CLASS_PREFIX=>'entityRowPrefix',
		self::CLASS_SUFFIX=>'entityRowSuffix',
		self::LIST_PREFIX=>'entityListPrefix',
		self::LIST_SUFFIX=>'entityListSuffix'
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
	 * @param array $values
	 * @param array|null $config
	 */
	private function __construct( array $values, ?array $config) {
		$this->dir             = isset($values[$config[self::DIR]]) ? $values[$config[self::DIR]] : null;
		$this->namespace       = isset($values[$config[self::NAMESPACE]]) ? $values[$config[self::NAMESPACE]] : null;
		$this->classSuffix     = isset($values[$config[self::CLASS_SUFFIX]]) ? $values[$config[self::CLASS_SUFFIX]] : null;
		$this->classPrefix     = isset($values[$config[self::CLASS_PREFIX]]) ? $values[$config[self::CLASS_PREFIX]] : null;
		$this->classListSuffix = isset($values[$config[self::LIST_SUFFIX]]) ? $values[$config[self::LIST_SUFFIX]] : null;
		$this->classListPrefix = isset($values[$config[self::LIST_PREFIX]]) ? $values[$config[self::LIST_PREFIX]] : null;
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