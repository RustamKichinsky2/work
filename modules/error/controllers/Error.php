<?php
namespace dvijok\modules\error;
class Error extends \dvijok\core\Core {

	public $user;
	public $data;
	public $path;
	public function __construct($path, $data) {
		
		parent::__construct($path, $data);
	}
	
	public function index() {
		
		$data = array();
		
		return $this->view(__FUNCTION__, $this->data);
	}
	
	public function header() {
		$data = array();
		return $this->view(__FUNCTION__, $this->data);
		
	}
	
	public function footer() {
		
		$data = array();
		return $this->view(__FUNCTION__, $data);
	}
}