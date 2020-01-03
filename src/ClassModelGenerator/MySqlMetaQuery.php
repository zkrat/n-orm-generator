<?php


namespace NOrmGenerator\ClassModelGenerator;


use NOrmGenerator\ClassModelGenerator\Exception\MySqlMetaQueryException;

class MySqlMetaQuery implements IMetaSqlQuery {



	/**
	 *
	 * Get Sql query of meta informations of single database
	 *
	 * @param string $databaseName
	 *
	 * @return string
	 */
	public function getDatabaseQuery(string $databaseName ): string {
		$sql='SELECT * FROM information_schema.SCHEMATA';
		if ($databaseName!==IMetaSqlQuery::ALL_ITEMS)
			$sql.=sprintf(' WHERE SCHEMA_NAME="%s"',$databaseName);
		return $sql;

	}

	/**
	 * get Sql query of meta informations of all table meta informations
	 *
	 * @param string $databaseName
	 *
	 * @return string
	 */
	public function getTablesQuery(string  $databaseName ): string {
		if ($databaseName==IMetaSqlQuery::ALL_ITEMS)
			throw new MySqlMetaQueryException(MySqlMetaQueryException::MSG_QUERY_NOT_IMPLEMENTED,MySqlMetaQueryException::QUERY_NOT_IMPLEMENTED);
		return sprintf('SHOW TABLE STATUS FROM %s',$databaseName);
	}

	/**
	 * get Sql query of meta informations of columns of single table
	 * @param string $databaseName
	 * @param string $table
	 *
	 * @return string
	 * @throws MySqlMetaQueryException
	 */
	public function getTableQuery(string  $databaseName,string  $table ): string {
		if ($databaseName==IMetaSqlQuery::ALL_ITEMS)
			throw new MySqlMetaQueryException(MySqlMetaQueryException::MSG_QUERY_NOT_IMPLEMENTED,MySqlMetaQueryException::QUERY_NOT_IMPLEMENTED);

		$sql =sprintf('SELECT  * FROM information_schema.`COLUMNS` WHERE table_schema = "%s"',$databaseName);
		if ($table!==IMetaSqlQuery::ALL_ITEMS)
			$sql.=sprintf(' AND table_name = "%s"',$table);

		return $sql;
	}

	/**
	 * get Sql query to get databsename in use
	 * @return string
	 */
	public function getActualDatabaseName(): string {
		return 'SELECT DATABASE() AS db';
	}

	/**
	 * @param string $databaseName
	 * @param string $table
	 *
	 * @return string
	 * @throws MySqlMetaQueryException
	 */
	public function getTableForenKeyQuery(string $databaseName,string  $table):string {

		if ($databaseName==IMetaSqlQuery::ALL_ITEMS)
			throw new MySqlMetaQueryException(MySqlMetaQueryException::MSG_QUERY_NOT_IMPLEMENTED,MySqlMetaQueryException::QUERY_NOT_IMPLEMENTED);


		$sql =sprintf('SELECT * FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_SCHEMA =  "%s"',$databaseName);

		if ($table!==IMetaSqlQuery::ALL_ITEMS)
			$sql.=sprintf(' AND TABLE_NAME = "%s"',$table);

		return $sql;
	}

}