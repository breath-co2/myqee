<?php
class Comment_Controller_Core extends Controller {
	
	function __construct() {
		parent::__construct ();
		Passport::chkadmin ();
	}
	
	public function index($page = 1, $dbname='',$bnewsid = 0) {
		Passport::checkallow('info.comment');
		$bnewsid = (int)$bnewsid;
		$view = new View ( 'admin/comment_list' );
		$per = 20;
		$this->db = Database::instance ();
		if ($bnewsid != 0) :
			$num = $this->db->where ( array('news_id'=>$bnewsid,'dbname'=>$dbname) )->count_records ( 'comments' );
		 else :
			$num = $this->db->count_records ( 'comments' );
		endif;
		$this->pagination = new Pagination ( array ('uri_segment' => 'index', 'total_items' => $num, 'items_per_page' => $per ) );
		if ($bnewsid != 0) :
			$list = $this->db->where (array('news_id'=>$bnewsid,'dbname'=>$dbname) )->limit ( $per, $this->pagination->sql_offset () )->orderby ( 'id', 'DESC' )->getwhere ( 'comments' )->result_array ( FALSE );
		 else :
			$list = $this->db->limit ( $per, $this->pagination->sql_offset () )->orderby ( 'id', 'DESC' )->getwhere ( 'comments' )->result_array ( FALSE );
		endif;
		$view->set ( 'list', $list );
		$view->set ( 'page', $this->pagination->render ( 'digg' ) );
		$view->render ( TRUE );
	}
	
	public function del($allid = null) {
		Passport::checkallow('info.comment_del');
		if (! $allid) {
			MyqeeCMS::show_error ( Myqee::lang ( 'admin/comment.error.parametererror' ), true );
		}
		$this->db = Database::instance ();
		$idArr = explode ( ',', $allid );
		$myId = array ();
		foreach ( $idArr as $tmpid ) {
			if ($tmpid > 0 && ! in_array ( $tmpid, $myId )) {
				$myId [] = $tmpid;
			}
		}
		if (count ( $myId ) == 0) {
			MyqeeCMS::show_info ( Myqee::lang ( 'admin/comment.info.nodelete' ), true );
		}
		
		$result = $this->db->select ( '*' )->from ( 'comments' )->in ( 'id', $myId )->get ()->result_array ( FALSE );
		if (count ( $result ) == 0) {
			MyqeeCMS::show_info ( Myqee::lang ( 'admin/comment.error.noinfo' ), true );
		}
		$delNum = $this-> db -> in('id',$myId) -> delete('comments');
		if (count ( $delNum ) > 0) {
			MyqeeCMS::show_info ( Myqee::lang ( 'admin/comment.info.delsuccess', count ( $delNum ) ), true, 'refresh' );
		} else {
			MyqeeCMS::show_info ( Myqee::lang ( 'admin/comment.info.nodelete' ), true );
		}
	}
	
	public function checked($allid = null) {
		Passport::checkallow('info.comment_check');
		if (! $allid) {
			MyqeeCMS::show_error ( Myqee::lang ( 'admin/comment.error.parametererror' ), true );
		}
		$this->db = Database::instance ();
		$idArr = explode ( ',', $allid );
		$myId = array ();
		foreach ( $idArr as $tmpid ) {
			if ($tmpid > 0 && ! in_array ( $tmpid, $myId )) {
				$myId [] = $tmpid;
			}
		}
		if (count ( $myId ) == 0) {
			MyqeeCMS::show_info ( Myqee::lang ( 'admin/comment.info.nodelete' ), true );
		}
		
		$result = $this->db->select ( '*' )->from ( 'comments' )->in ( 'id', $myId )->get ()->result_array ( FALSE );
		if (count ( $result ) == 0) {
			MyqeeCMS::show_info ( Myqee::lang ( 'admin/comment.error.noinfo' ), true );
		}
		$doNum = $this->db->in ( 'id', $myId )->set ( 'is_checked', 1 )->update ( 'comments' );
		if (count ( $doNum ) > 0) {
			MyqeeCMS::show_info ( Myqee::lang ( 'admin/comment.info.checkedsuccess', count ( $doNum ) ), true, 'refresh' );
		} else {
			MyqeeCMS::show_info ( Myqee::lang ( 'admin/comment.info.nochecked' ), true );
		}
	}
	
	public function commend($allid = null) {
		Passport::checkallow('info.comment_check');
		if (! $allid) {
			MyqeeCMS::show_error ( Myqee::lang ( 'admin/comment.error.parametererror' ), true );
		}
		$this->db = Database::instance ();
		$idArr = explode ( ',', $allid );
		$myId = array ();
		foreach ( $idArr as $tmpid ) {
			if ($tmpid > 0 && ! in_array ( $tmpid, $myId )) {
				$myId [] = $tmpid;
			}
		}
		if (count ( $myId ) == 0) {
			MyqeeCMS::show_info ( Myqee::lang ( 'admin/comment.info.nodelete' ), true );
		}
		
		$result = $this->db->select ( '*' )->from ( 'comments' )->in ( 'id', $myId )->get ()->result_array ( FALSE );
		
		if (count ( $result ) == 0) {
			MyqeeCMS::show_info ( Myqee::lang ( 'admin/comment.error.noinfo' ), true );
		}
		$doNum = $this->db->in ( 'id', $myId )->set ( 'is_commend', 1 )->update ( 'comments' );
		if (count ( $doNum ) > 0) {
			MyqeeCMS::show_info ( Myqee::lang ( 'admin/comment.info.commendsuccess', count ( $doNum ) ), true, 'refresh' );
		} else {
			MyqeeCMS::show_info ( Myqee::lang ( 'admin/comment.info.nocommend' ), true );
		}
	}
	
	public function uncommend($allid = null) {
		Passport::checkallow('info.comment_check');
		if (! $allid) {
			MyqeeCMS::show_error ( Myqee::lang ( 'admin/comment.error.parametererror' ), true );
		}
		$this->db = Database::instance ();
		$idArr = explode ( ',', $allid );
		$myId = array ();
		foreach ( $idArr as $tmpid ) {
			if ($tmpid > 0 && ! in_array ( $tmpid, $myId )) {
				$myId [] = $tmpid;
			}
		}
		if (count ( $myId ) == 0) {
			MyqeeCMS::show_info ( Myqee::lang ( 'admin/comment.info.nodelete' ), true );
		}
		
		$result = $this->db->select ( '*' )->from ( 'comments' )->in ( 'id', $myId )->get ()->result_array ( FALSE );
		if (count ( $result ) == 0) {
			MyqeeCMS::show_info ( Myqee::lang ( 'admin/comment.error.noinfo' ), true );
		}
		$doNum = $this->db->in ( 'id', $myId )->set ( 'is_commend', 0 )->update ( 'comments' );
		if (count ( $doNum ) > 0) {
			MyqeeCMS::show_info ( Myqee::lang ( 'admin/comment.info.uncommendsuccess', count ( $doNum ) ), true, 'refresh' );
		} else {
			MyqeeCMS::show_info ( Myqee::lang ( 'admin/comment.info.nouncommend' ), true );
		}
	}

}