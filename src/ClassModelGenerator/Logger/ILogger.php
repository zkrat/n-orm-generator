<?php


namespace NOrmGenerator\ClassModelGenerator\Logger;


interface ILogger {

	const PRIORITY_LOW=100;
	const PRIORITY_NORMAL=25;
	const PRIORITY_URGENT=1;


	public function message(string $msg,$priority=null);


}