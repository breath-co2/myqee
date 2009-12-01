<?php
class Task_Core {
	protected $run_file;
	/**
	 * Returns a singleton instance of databasebase.
	 *
	 * @param   mixed   configuration array or DSN
	 * @return  object
	 */
	public static function instance() {
		static $instance;
		
		// Create the instance if it does not exist
		($instance === NULL) and $instance = new Task();
		
		return $instance;
	}
	public function __construct() {
		$this->run_file = CACHEPATH.'task_running.txt';
	}
	
	public function isrun() {
		if ( file_exists( $this->run_file )) {
			if ($_SERVER['REQUEST_TIME'] - filemtime ( $this->run_file ) > 300) {
				return false;
			}
			return true;
		} elseif (file_exists($this->run_file.'.del') && $_SERVER['REQUEST_TIME']-filemtime($this->run_file.'.del')<300){
			return -1;
		} else {
			return false;
		}
	}
	
	public function start_task() {
		if ( file_exists($this->run_file) && $_SERVER['REQUEST_TIME']-filemtime($this->run_file)<300 ){
			return -1;
		}
		if (file_exists($this->run_file.'.del') && $_SERVER['REQUEST_TIME']-filemtime($this->run_file.'.del')<300){
			return -2;
		}
		$timeline = time();
		$code = md5(Myqee::config('encryption.default.key') . '__task_'.$timeline.'__');
		
		$snoopy = new Snoopy();
		$snoopy -> get_header(Myqee::url('task_run/index?timeline='.$timeline.'&code='.$code,null,true) , $_SERVER["SERVER_ADDR"] );

		return is_file($this->run_file);
	}
	
	public function stop_task() {
		$isstop = @unlink($this->run_file);
		file_put_contents( $this->run_file.'.del' ,'' );
		return $isstop;
	}
	/**
	 * 获取一小时内需要执行的任务类表
	 */
	public static function save_task_onehouse() {
		/*$adminmodel = new Admin_Model ( );
		$taskarray = $adminmodel->get_tasks_array ( $isuse );*/
		$taskarray = Myqee::config('tasks');
		$inttime = time(); //本次调用开始时间 
		$currtime = getdate($inttime); //本次调用开始时间数组
		$endtime = $inttime + 60 * 60; //本次调用结束时间
		//创建新的任务数组
		$new_task_arr = array ();
		foreach ( $taskarray as $tasks ) {
			if (($tasks ['maxtimes'] == 0 || $tasks ['maxtimes'] > $tasks ['fin_times']) && $inttime >= $tasks ['starttime'] - 3600 && ($inttime < $tasks ['endtime'] || $tasks ['endtime'] == 0)) {
				$starttime = getdate ( $tasks ['starttime'] ); //开始时间数组
				if ($tasks ['cycletype'] == 1) { //秒
					$offset = ($inttime - $tasks ['starttime']) % ($tasks ['cycle']);
					$firsttime = $inttime - $offset + ($tasks ['cycle']);
					if ($tasks ['starttime'] > $firsttime)
						$firsttime = $tasks ['starttime'];
					$_count = floor ( ($endtime - $firsttime) / ($tasks ['cycle']) );
					if ($_count > $tasks ['maxtimes'] - $tasks ['fin_times']) {
						$_count = $tasks ['maxtimes'] - $tasks ['fin_times'];
					}
					for($i = 0; $i <= $_count; $i ++) {
						$tasks ['runtime'] = $firsttime + $tasks ['cycle'] * $i;
						array_push ( $new_task_arr, $tasks );
					}
				}
				if ($tasks ['cycletype'] == 2) { //分 
					$offset = ($inttime - $tasks ['starttime']) % ($tasks ['cycle'] * 60);
					$firsttime = $inttime - $offset + ($tasks ['cycle'] * 60);
					if ($tasks ['starttime'] > $firsttime)
						$firsttime = $tasks ['starttime'];
					$_count = floor ( ($endtime - $firsttime) / ($tasks ['cycle'] * 60) );
					if ($_count > $tasks ['maxtimes'] - $tasks ['fin_times']) {
						$_count = $tasks ['maxtimes'] - $tasks ['fin_times'];
					}
					for($i = 0; $i <= $_count; $i ++) {
						$tasks ['runtime'] = $firsttime + $tasks ['cycle'] * 60 * $i;
						array_push ( $new_task_arr, $tasks );
					}
				}
				if ($tasks ['cycletype'] == 3) { //时
					if ($tasks ['starttime'] > $inttime) {
						$tasks ['runtime'] = $tasks ['starttime'];
						array_push ( $new_task_arr, $tasks );
					} else {
						$offset = ($inttime - $tasks ['starttime']) % ($tasks ['cycle'] * 60 * 60);
						$firsttime = $inttime - $offset + ($tasks ['cycle'] * 60 * 60);
						//一小时内的任务
						$_count = floor ( ($endtime - $firsttime) / ($tasks ['cycle'] * 60 * 60) );
						for($i = 0; $i <= $_count; $i ++) {
							$tasks ['runtime'] = $firsttime + ($tasks ['cycle'] * 60 * 60 * i); //最近的一次执行时间;
							array_push ( $new_task_arr, $tasks );
						}
					}
				}
				if ($tasks ['cycletype'] == 4) { //天 
					$offset = ($inttime - $tasks ['starttime']) % ($tasks ['cycle'] * 60 * 60 * 24);
					$firsttime = $inttime - $offset + ($tasks ['cycle'] * 60 * 60 * 24);
					if ($inttime < $tasks ['starttime']) {
						$firsttime = $tasks ['starttime'];
					}
					//一小时内的任务
					$_count = floor ( $endtime - $firsttime ) / ($tasks ['cycle'] * 60 * 60 * 24);
					for($i = 0; $i <= $_count; $i ++) {
						$runtime = $firsttime + ($tasks ['cycle'] * 60 * 60 * 24 * i); //最近的一次执行时间;
						$tasks ['runtime'] = $runtime;
						array_push ( $new_task_arr, $tasks );
					}
				}
				if ($tasks ['cycletype'] == 5) { //周  
					$weekarr = explode ( '|', $tasks ['cycle'] );
					$currweek = $currtime ['wday'] == 0 ? 7 : $currtime ['wday'];
					$hourslen = $starttime ['hours'] - $currtime ['hours'];
					
					if (in_array ( $currweek, $weekarr ) && ($hourslen == 1 || $hourslen == 0)) {
						$runtime = mktime ( $starttime ['hours'], $starttime ['minutes'], $starttime ['seconds'], $currtime ['mon'], $currtime ['mday'], $currtime ['year'] );
						$tasks ['runtime'] = $runtime;
						array_push ( $new_task_arr, $tasks );
					}
				}
				if ($tasks ['cycletype'] == 6) { //月
					$len = $currtime ['mon'] + ($currtime ['year'] - $starttime ['year']) * 12 - $starttime ['mon'];
					$hourslen = $starttime ['hours'] - $currtime ['hours'];
					if ($tasks ['starttime'] > $inttime && ($tasks ['starttime'] - $inttime) <= 3600) {
						$tasks ['runtime'] = $tasks ['starttime'];
						array_push ( $tasks, $new_task_arr );
					} else {
						if ($len > 0 && $len % $tasks ['cycle'] == 0 && $currtime ['day'] == $starttime ['day'] && $tasks ['starttime'] < $inttime) {
							$tasks ['runtime'] = mktime ( $starttime ['hours'], $starttime ['minutes'], $starttime ['seconds'], $currtime ['mon'], $currtime ['mday'], $currtime ['year'] );
							array_push ( $tasks, $new_task_arr );
						}
					}
				}
				
				if ($tasks ['cycletype'] == 7) { //年
					$len = $currtime ['year'] - $starttime ['year'];
					$hourslen = $starttime ['hours'] - $currtime ['hours'];
					
					if ($len >= 0 && $len % $tasks ['cycle'] == 0 && $currtime ['mon'] == $starttime ['mon'] && $currtime ['day'] == $starttime ['day'] && ($hourslen == 1 || $hourslen == 0)) {
						//设置执行时间 
						$tasks ['runtime'] = mktime ( $starttime ['hours'], $starttime ['minutes'], $starttime ['seconds'], $currtime ['mon'], $currtime ['mday'], $currtime ['year'] );
						array_push ( $new_task_arr, $tasks );
					}
				}
			}
		}
		// 保存任务列表到配置文件
		if (! empty ( $new_task_arr )) {
			$new_task_arr = $this->_sort_by ( $new_task_arr, 'runtime', 'asc' );
			MyqeeCMS::saveconfig ( 'task_one_hours', $new_task_arr );
		}
	}
	/**
	 *数组排序
	 *
	 * @param  $array 原数组
	 * @param $keyname 
	 * @param  $sortby 排序方式
	 * @return 新数组
	 */
	protected function _sort_by($array, $keyname = null, $sortby = 'asc') {
		$myarray = $inarray = array ();
		foreach ( $array as $i => $befree ) {
			$myarray [$i] = $array [$i] [$keyname];
		}
		switch ($sortby) {
			case 'asc' :
				asort ( $myarray );
				break;
			case 'arsort' :
				arsort ( $myarray );
				break;
			case 'natcasesor' :
				natcasesor ( $myarray );
				break;
			default :
				asort ( $myarray );
				break;
		}
		foreach ( $myarray as $key => $befree ) {
			$inarray [$key] = $array [$key];
		}
		return $inarray;
	}
}