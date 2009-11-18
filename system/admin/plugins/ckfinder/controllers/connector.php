<?php
class Connector_Controller extends Controller{
	public function connector(){
		define('PLUGINS_SHOWSELF',true);
		require_once str_replace("\\",'/',dirname(__FILE__))."/../ckfinder_core/connector.php";
		return true;
	}
}