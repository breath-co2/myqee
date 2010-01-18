<?php
class Index_Controller extends Index_Controller_Core {
	
	public function __construct ()
	{
		parent::__construct();
		//Passport::chkadmin();
	}
	
	public function updatemember() {
		set_time_limit(0);
		$oldid = (int)Session::instance() -> get('oldid');
		
		$data = Myqee::db('mysql55') ->limit(10000) -> getwhere('members',array('id>'=>$oldid)) -> result_array(false);
		$count = Myqee::db('mysql55') -> count_records('members');
		if (!$data || $count>100000000){
			echo 'ALL_OK';
			return TRUE;
		}
		foreach ($data as $item){
			unset($item['id']);
			Myqee::db('mysql55') -> insert('members',$item);
			$id = $item['id'];
		}
		$oldid = $id;
		Session::instance() -> set('oldid',$id);
		echo '<script>document.location.href=document.location.href</script>';
	}
	
	public function id (){
		Session::instance() -> set('oldid',888606);
		echo Session::instance() -> get('oldid');
		//218751
	}
	
	
	public function runtime(){
		echo time() - 3600 * 24 * 600;
		echo '<br/>';
		$time = _getthistime();
		$data = Myqee::db('72') -> from('members') -> where('name','Kasumi') -> get() -> result_array(false);
		echo Myqee::db('mysql55') -> last_query();
		echo _getthistime()-$time;
		print_r($data);
		
	}
}