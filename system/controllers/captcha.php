<?php
class Captcha_Controller_Core extends Controller {	
	public function render(){
		Captcha::render();
	}
	public function small(){
		
		Captcha::render(array(
		'width'      => 60,
		'height'     => 24,
	));
	}
	public function _default(){
		$this -> render();
	}
}