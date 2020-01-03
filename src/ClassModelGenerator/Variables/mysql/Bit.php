<?php


namespace NOrmGenerator\ClassModelGenerator\Variables\MySql;


use NOrmGenerator\ClassModelGenerator\Variables\AbstractVariables;

class Bit extends AbstractVariables {

	const META_SELECT = 'BIN(`%column%` + 0) AS `%column%`';

}