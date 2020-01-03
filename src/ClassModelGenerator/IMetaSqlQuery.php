<?php


namespace NOrmGenerator\ClassModelGenerator;


interface IMetaSqlQuery {

	const ALL_ITEMS='%ALL%ITEMS%';

	/**
	 * get Sql query to get databsename in use
	 * @return string
	 */
	public function getActualDatabaseName():string;

	/**
	 *
	 *   Get Sql query of meta informations of single database
	 *
	 * @param string $databaseName
	 *
	 * @return string
	 */
	public function getDatabaseQuery(string  $databaseName):string;

	/**
	 *
	 * get Sql query of meta informations of all table meta informations
	 *
	 * @param string $databaseName
	 *
	 * @return string
	 */
	public function getTablesQuery(string $databaseName):string;

	/**
	 *
	 * get Sql query of meta informations of columns of single table
	 *
	 * @param string $databaseName
	 * @param string $table
	 *
	 * @return string
	 */
	public function getTableQuery(string $databaseName,string $table):string;

	/**
	 *
	 * get sql meta query of all foriechn keys
	 *
	 * @param string $databaseName
	 * @param string $table
	 *
	 * @return string
	 */
	public function getTableForenKeyQuery(string $databaseName,string $table):string;

}