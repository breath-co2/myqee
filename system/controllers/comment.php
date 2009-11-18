<?php

class Comment_Controller_Core extends Controller {
	
	public function __construct() {
		parent::__construct ();
	}
	public function index($classid = 1, $bnewsid) {
		$bnewsid = ( int ) $bnewsid;
		$classid =  $classid;
		$page = ( int ) $_REQUEST ['page'] == 0 ? 1 : ( int ) $_REQUEST ['page'];
		
		if (! $bnewsid) {
			Myqee::show_error ( '参数错误', true, 'goback' );
		}
		if ($classid>0){
			//当前为栏目ID
			$class_array = Myqee::myclass($classid);
			$dbname = $class_array['dbname'];
		}else{
			$dbname = $classid;
		}	
		$view = new View ( 'comment_list' );
		$this->db = Database::instance ();
		$per = 10;
		$num = $this->db->where ( array ('news_id' => $bnewsid, 'dbname' => $dbname ) )->count_records ( 'comments' );
		$list = $this->db->where ( array ('news_id' => $bnewsid, 'dbname' => $dbname ) )->limit ( $per, ($page - 1) * $per )->orderby ( 'id', 'DESC' )->getwhere ( 'comments' )->result_array ( FALSE );
		

		$news_info = $this->db->where ( 'id', $bnewsid )->getwhere ( $dbname )->result_array ( FALSE );
		//print_r($news_info);
		$news_info = $news_info [0];
		
		$view->set ( 'news_info', $news_info );
		$view->set ( 'list', $list );
		$view->set ( 'news_id', $bnewsid );
		$view->set ( 'classid', $classid );
		$view->set ( 'allpage', ( int ) ceil ( $num / $per ) );
		$view->set ( 'page', $page );
		$view->set ( 'per', $per );
		$view->render ( TRUE );
	}
	
	public function save() {
		
		if (! $post = $_POST ['comment']) {
			Myqee::show_error ( '参数错误', true, 'goback' );
		}
		
		$news_id = ( int ) $post ['news_id'];
		$classid =  $post ['classid'];
		
		if ($classid>0){
			//当前为栏目ID
			$class_array = Myqee::myclass($classid);
			$dbname = $class_array['dbname'];
		}else{
			$dbname = $classid;
		}	
		$this->db = Database::instance ();
		$news_info = $this->db->where ( 'id', $news_id )->getwhere ( $dbname )->result_array ( FALSE );
		$news_info = $news_info [0];
		
		$news_title = $news_info ['title'];
		
		$news_id = $post ['news_id'];
		if (empty ( $post ['comment'] )) {
			Myqee::show_error ( "内容不能为空", true, false );
		}
		if (empty ( $post ['logincode'] )) {
			Myqee::show_error ( "验证码不能为空", true, false );
		}
		if (! Captcha::valid ( $post ['logincode'] )) {
			Myqee::show_error ( '验证码不匹配', true, false );
		}
		
		if (isset ( $_COOKIE ['nid_' . $news_id] )) {
			Myqee::show_error ( "稍后再发", true, 'goback' );
		
		} else {
			setcookie ( 'nid_' . $news_id, $news_id, $_SERVER['REQUEST_TIME'] + 60 * 10 );
		}
		
		$_username = $post ['username'];
		$_password = $post ['password'];
		$loginuser = $_COOKIE ['waitan_LOGIN_USER'];
		if (empty ( $loginuser )) {
			
			if (empty ( $_username )) {
				Myqee::show_error ( "用户名 不能为空", true, false );
			}
			if (empty ( $_password )) {
				Myqee::show_error ( "密码不能为空", true, false );
			}
			Ucenter_Api::instance ();
			list ( $uid, $username, $password, $email ) = uc_user_login ( $_username, $_password );
			if ($uid > 0) {
				//用户登陆成功，设置 Cookie，加密直接用 uc_authcode 函数，用户使用自己的函数
				setcookie ( 'waitan_LOGIN_USER', $username );
				//生成同步登录的代码
				$ucsynlogin = uc_user_synlogin ( $uid );
			} else {
				Myqee::show_error ( "用户或密码错误", true, false );
			}
		}
		
		$comment = array ('comment' => htmlspecialchars ( $post ['comment'] ), 'username' => $post ['username'], 'user_id' => $post ['user_id'], 'news_id' => $news_id, 'news_title' => $news_title, 'dbname' => $dbname, 'classid' => $classid, 'addip' => $_SERVER ["REMOTE_ADDR"], 'addtime' => $_SERVER['REQUEST_TIME'] );
		
		$status = Myqee::db ()->insert ( 'comments', $comment );
		
		if (count ( $status ) > 0) {
			Myqee::show_info ( "发布评论成功", true, 'refresh' );
		} else {
			Myqee::show_info ( "发布评论失败", true, false );
		}
	}
	public function agree($commentid = 0) {
		$commentid = ( int ) $commentid;
		if (! ($commentid > 0)) {
			Myqee::show_error ( '参数错误', true );
		}
		
		if (isset ( $_COOKIE ['aid_' . $commentid] )) {
			Myqee::show_error ( "稍后再发", true );
		} else {
			setcookie ( 'aid_' . $commentid, $commentid, $_SERVER['REQUEST_TIME'] + 60 * 10 );
		}
		
		$sql = 'UPDATE ' . Myqee::db ()->table_prefix () . 'comments SET `agree_num` = agree_num+1 WHERE `id` = ' . $commentid;
		$doNum = Myqee::db ()->query ( $sql );
		
		if (count ( $doNum ) > 0) {
			Myqee::show_info ( '保存成功', true, 'refresh' );
		} else {
			Myqee::show_info ( '保存失败', false );
		}
	}
	public function disagree($commentid = 0) {
		$commentid = ( int ) $commentid;
		if (! ($commentid > 0)) {
			Myqee::show_error ( '参数错误', true );
		}
		
		if (isset ( $_COOKIE ['aid_' . $commentid] )) {
			Myqee::show_error ( "稍后再发", true );
		} else {
			setcookie ( 'aid_' . $commentid, $commentid, $_SERVER['REQUEST_TIME'] + 60 * 10 );
		}
		
		$sql = 'UPDATE ' . Myqee::db ()->table_prefix () . 'comments SET `disagree_num` = disagree_num+1 WHERE `id` = ' . $commentid;
		$doNum = Myqee::db ()->query ( $sql );
		
		if (count ( $doNum ) > 0) {
			Myqee::show_info ( '保存成功', true, 'refresh' );
		} else {
			Myqee::show_info ( '保存失败', false );
		}
	}
	protected function setvalue($type = '', $commentid = null) {
		$canset = array ('agree', 'disagree' );
		
		if (! $type) {
			Myqee::show_error ( '参数错误', true );
		}
		if (! $allid) {
			Myqee::show_error ( '参数错误', true );
		}
		
		$type = explode ( '=', $type );
		if (! in_array ( $type [0], $canset )) {
			Myqee::show_error ( '参数错误', true );
		}
		
		$setkey = $type [0];
		$setvalue = ( int ) $type [1];
		
		$sqlset = '`' . $setkey . '`=' . $setvalue;
		$db = Myqee::$db;
		//$doNum = Myqee::$db-> query('UPDATE `'.$adminmodel -> db -> table_prefix().$dbname .'` SET '.$sqlset.' WHERE '.$sys_field['id'].' IN ('.join(',',$myId).')');
		$doNum = $db->from ( 'comments' . ' a' )->set ( $sqlset )->where ( 'id', $commentid )->update ();
		
		if (count ( $doNum ) > 0) {
			Myqee::show_info ( '保存成功', true, 'refresh' );
		} else {
			Myqee::show_info ( '保存失败', true );
		}
	}
}