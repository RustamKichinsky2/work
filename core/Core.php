<?php

namespace dvijok\core;
class Core {
	
	public $path;
	public static $mainUri;
	public $models = array();
	public $db = false;
	public function __construct($path, $data = array()) {
		
		$this->baseUrl = \dvijok\core\Config::$baseUrl;
		$this->path = $path;
		$this->modulesFolder = \dvijok\core\Config::$modulesFolder;
		$this->baseSystem = \dvijok\core\Config::$baseSystem;
		$this->controllersFolder = \dvijok\core\Config::$controllersFolder;
		$this->modelsFolder = \dvijok\core\Config::$modelsFolder;
		$this->viewsFolder = \dvijok\core\Config::$viewsFolder;
		$this->requestMethod = strtolower(\dvijok\core\Config::$requestMethod);
		$this->ext = \dvijok\core\Config::$ext;
		if(DB::$useDb)
		{
			$this->db = new Database(DB::$dbHost, DB::$dbLogin, DB::$dbPassword, DB::$dbName);
			
			$modelSearchDir = $this->modulesFolder.DS.$this->path['module'].DS.$this->modelsFolder;
			if(is_dir($modelSearchDir))
			{
				$files = Helper::scanDir($this->modulesFolder.DS.$this->path['module'].DS.$this->modelsFolder);
				foreach($files as $f)
				{
					
					$temp = explode('Model', $f);
					
					$path = $this->modulesFolder.DS.$this->path['module'].DS.$this->modelsFolder.DS.$f;
					
					$c = '\\dvijok\\modules\\'.$this->path['module'].'\\'.$temp[0].'Model';
					
					if(!is_numeric($temp[0]) && !class_exists($c, false))
					{
						require($path);
						$n = strtolower($temp[0]);
						$temp = explode('.', $f);
						$this->models[$n] = new $c();
					}
					
					
				}
			}
		}
		else
		{
			$this->db = false;
		}
		
		$this->call = function() {
			
			return self::call($scriptUri);
		};
		
		$this->session = new Session();
		$this->input = new Input();
		$this->validator = new Validator($this->session, $this->db);
		
		
		
	}
	
	public function view($view, $params = array()) {
		
		ob_start();
		//extract($params, EXTR_OVERWRITE, "d_");
		if(file_exists($this->modulesFolder.DS.$this->path['module'].DS.$this->viewsFolder.DS.lcfirst($this->path['controller']).DS.$view.$this->ext))
		{
			require($this->modulesFolder.DS.$this->path['module'].DS.$this->viewsFolder.DS.lcfirst($this->path['controller']).DS.$view.$this->ext);
		}
		else
		{
			require($this->modulesFolder.DS.Config::$errorModule.DS.$this->viewsFolder.DS.lcfirst(Config::$errorController).DS.$view.$this->ext);
		}
		return ob_get_clean();
	}
	
	public function c($scriptUri) {
		
		return self::call($scriptUri);
		
	}
	
	
	public static function call($scriptUri, $data = array()) {
		
		
		if($scriptUri)
		{
			$path = array();
			$baseUrl = \dvijok\core\Config::$baseUrl;
			$path = $path;
			$modulesFolder = \dvijok\core\Config::$modulesFolder;
			$baseSystem = \dvijok\core\Config::$baseSystem;
			$controllersFolder = \dvijok\core\Config::$controllersFolder;
			$modelsFolder = \dvijok\core\Config::$modelsFolder;
			$viewsFolder = \dvijok\core\Config::$viewsFolder;
			$requestMethod = strtolower(\dvijok\core\Config::$requestMethod);
			$ext = \dvijok\core\Config::$ext;
			$scriptUri = trim($scriptUri, '/');
			$temp = Helper::checkPath($scriptUri);
			
			if($temp->redirect)
			{
				return Helper::redirect($baseUrl.$temp->url);
			}
			$scriptUri = trim($temp->scriptUri, '/');
			$segments = array_filter(explode('/', $scriptUri));
			if(!Core::$mainUri)
			{
				Core::$mainUri = $scriptUri;
			}
			$path['module'] = isset($segments[0]) ? $segments[0] : 'index';
			unset($segments[0]);
			$path['controller'] = ucfirst(isset($segments[1]) ? $segments[1] : 'index');
			unset($segments[1]);
			$path['function'] = isset($segments[2]) ? $segments[2] : 'index';
			if(is_numeric($path['function']))
			{
				$path['function'] = 'index';
			}
			else
			{
				unset($segments[2]);
			}
			$path['params'] = array_values($segments);
			$fullPath = $baseSystem.$modulesFolder.DS.$path['module'].DS.$controllersFolder.DS.$path['controller'].$ext;
			$scanDir = $baseSystem.$modulesFolder.DS.$path['module'].DS.$controllersFolder.DS;
			
			if(isset($data['firstRun']) && $data['firstRun'])
			{

					spl_autoload_register(function ($class_name) use ($path) {
						
						$temp = explode('\\', $class_name);
						$cl = end($temp);
						if(file_exists(\dvijok\core\Config::$baseSystem.\dvijok\core\Config::$modulesFolder.DS.$path['module'].DS.\dvijok\core\Config::$controllersFolder.DS.$cl.'.php'))
						{
							$res = require \dvijok\core\Config::$baseSystem.\dvijok\core\Config::$modulesFolder.DS.$path['module'].DS.\dvijok\core\Config::$controllersFolder.DS.$cl.'.php';
						}
						else
						{
							$res = require \dvijok\core\Config::$baseSystem.\dvijok\core\Config::$modulesFolder.DS.Config::$errorModule.DS.\dvijok\core\Config::$controllersFolder.DS.$cl.'.php';
						}
						if($res)
						{
							return true;
						}
						return false;
					});
					
					
					
				
				
			}
			
					$cl = '\\dvijok\\modules\\'.$path['module'].'\\'.$path['controller'];
					$c = new $cl($path, $data);
					if(isset($data['firstRun']) && $data['firstRun'])
					{
						if($c->requestMethod == 'post')
						{
							/*
							if($c->input->post('csrf', false) != $c->session->get('csrf'))
							{
								echo 'unauthorized request';
								exit;
							}
							$c->session->set('csrf', base64_encode(openssl_random_pseudo_bytes(32)));
							*/
						}
						
					}
					$p = $data;
					if($data)
					{
						$p = array_merge($data, $path);
					}

			if(file_exists($fullPath))
			{
					
					if(method_exists($c, $path['function'].'_'.$requestMethod))
					{
						$result = call_user_func_array(array($c, $path['function'].'_'.$requestMethod), array($p));
					}
					else
					{
						
						if(method_exists($c, $path['function']))
						{
						
							$result = call_user_func_array(array($c, $path['function']), array($p));
						}
						else
						{
							return $c->redirect('/error404');
						}
					}
					
					return $result;
				
			}
			else
			{
				$cl = '\\dvijok\\modules\\'.\dvijok\core\Config::$errorModule.'\\'.\dvijok\core\Config::$errorController;
				$c = new $cl($path, $data);
				$result = call_user_func_array(array($c, $path['function']), array($p));
				return $result;
			}
		}
		else
		{
			return false;
		}
	}
	
	public function redirectBack() {
		
		
		if(!isset($_SERVER['HTTP_REFERER']) || !$_SERVER['HTTP_REFERER'])
		{
			header('Location: '.$this->baseUrl);
		}
		else
		{
			header('Location: '.$_SERVER['HTTP_REFERER']);
		}
	}
	
	public function redirect($url = false) {
		
		Helper::redirect($url);
	}
	
	public function model($name) {
		
		if(isset($this->models[$name]))
		{
			return $this->models[$name];
		}
		return false;
		
	}
	
	public function __destruct() {
		
		
		
		
		
	}
	
	
}