<?php

namespace dvijok\core;
class Config {
	
	public static function init() {
		
		error_reporting(E_ALL);
		ini_set("display_errors", 1);
		session_start();
		define('DS', '/');
		require(\dvijok\core\Config::$coreFolder.DS.'Helper.php');
		require(\dvijok\core\Config::$coreFolder.DS.'Database.php');
		require(\dvijok\core\Config::$coreFolder.DS.'Core.php');
		require(\dvijok\core\Config::$coreFolder.DS.'Input.php');
		require(\dvijok\core\Config::$coreFolder.DS.'Validator.php');
		require(\dvijok\core\Config::$coreFolder.DS.'Session.php');
		require(\dvijok\core\Config::$coreFolder.DS.'Model.php');
		require('Routes.php');
		require('DB.php');
		self::$scriptName = $_SERVER['SCRIPT_NAME'];
		self::$scriptUri = $_SERVER['REQUEST_URI'];
		self::$requestMethod = $_SERVER['REQUEST_METHOD'];
		self::$baseUrl = dirname(self::$scriptName).DS;
		if(self::$baseUrl == '//'){self::$baseUrl = DS;}
		$scriptName2 = explode(DS, self::$scriptName);
		$requestUri2 = explode(DS, self::$scriptUri);
		$scriptName2 = array_filter($scriptName2, function($value) { return $value !== ''; });
		$requestUri2 = array_filter($requestUri2, function($value) { return $value !== ''; });
		$c = true;
		foreach($requestUri2 as $s)
		{
			foreach($scriptName2 as $s2)
			{
				if($s == $s2)
				{
					$scriptUri = \dvijok\core\Helper::strReplaceFirst($s2.'/', '', self::$scriptUri);
					$c = false;
					break;
				}
			}
		}

		$temp = explode('?', self::$scriptUri);
		self::$scriptUri = trim($temp[0], '/');
		if(self::$scriptUri == '')
		{
			self::$scriptUri = '/';
		}
		self::$baseSystem = $_SERVER['DOCUMENT_ROOT'].self::$baseUrl;
		$data = array();
		$data['firstRun'] = true;
		self::$data = $data;
		$result = \dvijok\core\Core::call(self::$scriptUri, self::$data);
		return $result;
	}
	public static $errorModule = 'error';
	public static $errorController = 'Error';
	public static $current_url = '';
	public static $relative_url = '';
	public static $coreFolder = 'core';
	public static $modulesFolder = 'modules';
	public static $controllersFolder = 'controllers';
	public static $modelsFolder = 'models';
	public static $viewsFolder = 'views';
	public static $ext = '.php';
	public static $scriptName;
	public static $scriptUri;
	public static $requestMethod;
	public static $baseSystem;
	public static $baseUrl;
	public static $data;
}