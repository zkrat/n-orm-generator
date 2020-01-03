<?php
/**
 * Created by PhpStorm.
 * User: zkrat
 * Date: 09/11/2017
 * Time: 15:40
 */

namespace NOrmGenerator\ClassModelGenerator\Helpers;


class StringManipulator {

	public static function camelCaseToUnderscore($input) {
		return ltrim(strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $input)), '_');
	}

	public static function underscoreToCamelCase(string $string,bool $capitalizeFirstCharacter = false,string $separator='_'):string
	{

		$string=strtolower($string);
		$str = lcfirst(implode('', array_map('ucfirst', explode($separator, $string))));;

		if ($capitalizeFirstCharacter) {
			$str = ucfirst($str);
		}
		return $str;
	}

	public static function getLongClasnameWithNamespace(string $className):string {
		return str_replace('\\','', $className);


	}
	public static function getClasnameWithoutNamespace(string $className):string {
		$path = explode('\\', $className);
		return array_pop($path);

	}

	public static function isInString(string $fullString,string $findString  ):bool {
		return is_numeric(strpos($fullString,$findString));
	}

	public static function removeStrings(array $arrayStrings,string $string):string{
		foreach ($arrayStrings as $removeString){
			$string= str_replace($removeString,'',$string);
		}
		return $string;
	}

}