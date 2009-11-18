<?php
class Comment_Controller extends Controller{
	
	function __construct(){
		parent::__construct();
		Passport::chkadmin();
	}
	/**
	 * 删除评论
	 * @param string $allid
	 */
	public function del($allid='') {
		//判断权限
		$isadmin = 1;
		if (!$isadmin) {
			MyqeeCMS::show_error('您没有权限进行操作！');
		}
		$allid = Tools::formatids($allid);
		$status = 1;
		if (!empty($allid) && $isadmin) {
			$db = Database::instance();
			$query = $db -> in ('id',$allid)->delete('comments');
			$status = $query -> count();
		}
		if ($status > 0) {
			MyqeeCMS::show_info("成功删除 $status 条评论",true,'refresh');
		} else {
			MyqeeCMS::show_error('删除评论失败！');
		}
	}
	
	/**
	 * 管理评论
	 * @param int $page
	 */
	public function managelist ($page=1) {
		//判断权限
		$isadmin = 1;
		$db = Database::instance();
		if (!$isadmin) {
			MyqeeCMS::show_error('您没有权限进行操作！');
		}
		
		$per = 20;
		$page = intval($page) > 0 ? intval($page) : 1;
		$num = $db -> count_records('comments');
		$this -> pagination = new Pagination( array(
			'uri_segment'    => 'managelist',
			'total_items'    => $num,
			'items_per_page' => $per
		) );
		$offset = ($page - 1) * $per;
		$list = $db -> getwhere('comments',array(),$per,$this -> pagination -> sql_offset)->result_array(false);
		$view = new View ('comment_managelist');
		$view -> set ( 'list' , $list );
		$view -> render(true);
	}
}