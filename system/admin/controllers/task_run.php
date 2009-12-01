<?php
class task_run_Controller_Core extends Controller {
	protected $run_file;
	function __construct(){
		$code = md5(Myqee::config('encryption.default.key') . '__task_'.$_GET['timeline'].'__');
		if ($_SERVER['REQUEST_TIME'] - $_GET['timeline']>600 || $code != $_GET['code'] ){
			exit;
		}
		
		$this->run_file = CACHEPATH.'task_running.txt';
		
		$time = time();
		
		if ( 
			(file_exists($this->run_file) && $time-filemtime($this->run_file)<300)
			 || 
			(file_exists($this->run_file.'.del') && $time-filemtime($this->run_file.'.del')<300)
		){
			exit;
		}
		
		set_time_limit(0);
		
		Myqee::run_in_system();
	}
	
	public function index(){
		$interval=5;
		
		$renew_file_time = $_SERVER['REQUEST_TIME'];
	
		file_put_contents( $this->run_file,'');
		
		do{
			if ( !file_exists($this->run_file) ){
				if (file_exists($this->run_file.'.del')){
					@unlink($this->run_file.'.del');
				}
				exit;
			}
			
			$time = time();
			if ($time - $renew_file_time >=300){
				//5分钟更新一次
				file_put_contents($this->run_file,'');
				$renew_file_time = $time;
			}
			
			/////////////
			
			//每一小时更新一次任务
			//Task::save_task_onehouse();
			
			sleep($interval);
		}
		while(true);
	}
}