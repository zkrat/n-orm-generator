<?php
/**
 * Created by PhpStorm.
 * User: zkrat
 * Date: 06/11/2017
 * Time: 18:20
 */

namespace NOrmGenerator\ClassModelGenerator;

use NOrmGenerator\ClassModelGenerator\Helpers\StringManipulator;
use Nette\Database\Context;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\Security\User;
use Nette\Database\Table\IRow;

abstract class BaseModel {

	const TABLE='@abstract@';

	/**
	 * @var Context
	 */
	protected $db;
	/**
	 * @var User
	 */
	protected $user;

	const LIST_COLUMNS=['setup columns names!!!'];

	public function __construct(Context $db) {
		$this->db = $db;
	}

	/**
	 * @return Selection
	 */
	public function getAll():Selection{
		return $this->db->table($this::TABLE);
	}

	/**
	 * @param int $id
	 *
	 * @return ActiveRow|null
	 */
	public function get(int $id):?ActiveRow{
		return $this->db->table($this::TABLE)->get($id);
	}

	public function save(array $data,int $id=null):?int{
		if(is_null($id)){
			$row= $this->db->table($this::TABLE)->insert($data);
			if ($row instanceof ActiveRow)
				return $row->id;
			elseif(is_int($row))
				return $row;
		}else{
			$row=$this->db->table($this::TABLE)->get($id);
			if ($row instanceof ActiveRow){
				$row->update($data);
				return $id;
			}

		}
		return null;
	}

	/**
	 * @param Selection|null $selection
	 * @param string|null $prefix
	 *
	 * @return Selection
	 */

	public function getAllColumsPrefix(Selection $selection=null,string $prefix=null){
		if ( is_null($selection))
			$selection= $this->getAll();



		foreach ($this::LIST_COLUMNS as $column){
			if(is_null($prefix))
				$selection->select($this->getColumNameForSelect( $column ));
			else
				$selection->select( $prefix . $this->getColumNameForSelect( $column ));
		}
		return $selection;

	}

	public function getCamelCaseListColumsNames(){
		$array=[];
		foreach ($this::LIST_COLUMNS as $column){
			$columnName = $this->getColumName($column);
			$columnNameRow = str_replace( $this::TABLE . '_','',$columnName);
			$columnNameRow=StringManipulator::underscoreToCamelCase($columnNameRow);
			$array[$columnNameRow]=$columnName;
		}
		return $array;
	}


	private function getColumName( $column ) {
		return $this::TABLE.'_' . $column;
	}

	private function getColumNameForSelect( $column ) {
		return $this::TABLE.'.' . $column . ' AS ' . $this::getColumName($column);
	}

	/**
	 * @param User $user
	 */
	public function setUser( User $user ): void {
		$this->user = $user;
	}
}