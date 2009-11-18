<?php
class Captcha_Controller_Core extends Controller {	
	public function render(){
		Captcha::render(false);
	}
	
	public function _default(){
		$this -> render();
	}
}