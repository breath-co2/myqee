<?php

class Sp_Controller_Core extends Controller {
	
	public function __construct() {
		parent::__construct ();
	}
	public function index() {
		$view = new View ( 'sp_index' );
		$view->render ( TRUE );
	}

}