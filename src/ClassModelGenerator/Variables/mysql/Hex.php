<?php


namespace NOrmGenerator\ClassModelGenerator\Variables\MySql;


use NOrmGenerator\ClassModelGenerator\Variables\AbstractVariables;


class Hex extends AbstractVariables  {

	const META_SELECT = 'HEX(`%column%` + 0) AS `%column%`';



}