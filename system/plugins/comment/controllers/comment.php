<?php
/**
 * 评论插件控制器
 * @author binbin
 *
 */
class Comment_Controller extends Controller{

	protected $dbname = 'plugins_comments';
	/**
	 * 发表评论
	 */
	public function add () {
		$classid = intval ($_POST['classid']);
		$api = Plugins::api('comment','comment');
		$canshow = $api->check_ifcomment($classid);
		
		//此栏目不能显示
		if (!$canshow) {
			//显示栏目不能评论
			exit (' ');
		}
		$data['news_id'] = intval ($_POST['news_id']);
		$data['news_title'] = trim ($_POST['news_title']);
		//检查是否登录
		//没有登录是否发送过来用户名和密码
		$data['username'] = trim ($_POST['username']);
		$data['user_id'] = trim ($_POST['user_id']);
		$data['password'] = trim ($_POST['password']);
		//检查用户名和密码是否合法
		//检查评论内容，有非法过滤
		$data['comment'] = trim ($_POST['comment']);
		$data['dbname'] = trim ($_POST['dbname']);
		
		list ($database,$tablename) = explode ($data['dbname']);
		if (empty ($database) || empty ($tablename)) {
			//数据表不对
			exit ();
		}
		$db = Database::instance ();
		$query = $db -> getwhere ('[dbtable]',array('dbname'=>$data['dbname']))->result_array (false);
		if (empty ($query)) {
			//数据表不存在
			exit ();
		}
		$data['addip'] = Tools::getonlineip();
		$data['addtime'] = time();
		$query = $db -> insert ($this->dbname,$data);
		if ($query->count() >0) {
			//评论成功
			exit ('success');
		} else {
			//评论失败
			exit ('fail');
		}
	}
	
	/**
	 * 删除评论
	 * @param string $allid
	 */
	public function del($id='') {
		//判断权限
		$isadmin = 1;
		if (!$isadmin) {
			//您没有权限进行操作！
		}
		$id = intval($id);
		$status = 1;
		if (!empty($id) && $isadmin) {
			$db = Database::instance();
			$query = $db -> where ('id',$id)->delete($this->dbname);
			$status = $query -> count();
		}
		if ($status > 0) {
			//成功删除 $status 条评论
		} else {
			//删除评论失败！
		}
	}
	
	/**
	 * 给前台页面显示一个评论框
	 * @param int $page
	 * @param int $classid
	 * @param int $news_id
	 */
	public function showcomment ($page=1,$classid=0,$news_id=0) {
		$view = new View ('comment_showcomment');
		$classid = intval($classid);
		$news_id = intval($news_id);
		
		if ($news_id < 1) {
			exit (' ');
		}
		$api = Plugins::api('comment','comment');
		$canshow = $api->check_ifcomment($classid);
		
		//此栏目不能显示
		if (!$canshow) {
			exit (' ');
		}
		//找出此信息ID下的评论
		$view = new View ('comment_showcomment');
		$db = Database::instance();
		$per = 10;
		$page = intval($page) > 0 ? intval($page) : 1;
		$count = $db -> count_records($this->dbname);
		$url = Myqee::url('plugins/comment/comment/showcomment/{{page}}/{{classid}}/{{news_id}}/');
		$pageurl = Myhtml::page($page,$count,$url,array('page'=>$page,'classid'=>$classid,'news_id',$news_id));
		
		$offset = ($page - 1) * $per;
		$list = $db -> getwhere($this->dbname,array('classid'=>$classid,'news_id'=>$news_id),$per,$this -> pagination -> sql_offset)->result_array(false);
		$view -> set ('list',$list);
		$view -> set ('pageurl',$pageurl);
		$view -> set ('count',$count);
		$view -> render (true);
	}
}