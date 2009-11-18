<?php
class Ckfinder_Controller extends Controller{
	function index(){
		$view = new View('ckfind_index');
		$view -> render(true);
	}

	public function connector(){
		define('PLUGINS_SHOWSELF',true);
		require_once str_replace("\\",'/',dirname(__FILE__))."/../ckfinder_core/connector.php";
		return true;
	}
}