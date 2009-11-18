<?php
/**
 * $Id: task.php,v 1.7 2009/09/15 09:01:56 jonwang Exp $
 *
 * @package    Core
 * @author     Myqee Team
 * @copyright  (c) 2008-2009 Myqee Team
 * @license    http://myqee.com/license.html
 */
class Task_Controller_Core extends Controller {
	
	function __construct() {
		parent::__construct ( NULL );
		Passport::chkadmin ();
	}
	
	public function index($page = 1, $catename = false) {
		$islist = Passport::checkallow ( 'task.list' );
		$per = 20;
		$view = new View ( 'admin/task_list' );
		$this->db = Database::instance ();
		$listwhere = array ('id>' => 0 );
		if (is_string ( $catename )) {
			$listwhere ['cate'] = $catename;
			$view->set ( 'cate', htmlspecialchars ( $catename ) );
		}
		$num = $this->db->where ( $listwhere )->count_records ( '[tasks]' );
		
		$this->pagination = new Pagination ( array ('uri_segment' => 'index', 'total_items' => $num, 'items_per_page' => $per ) );
		
		if ($page == 1) {
			$isrun = Task::instance ()->isrun ();
			$view->set ( 'is_task_run', $isrun );
		}
		
		$view->set ( 'list', $this->db->where ( $listwhere )->limit ( $per, $this->pagination->sql_offset () )->orderby ( 'id', 'DESC' )->getwhere ( '[tasks]' )->result_array ( FALSE ) );
		$view->set ( 'page', $this->pagination->render ( 'digg' ) );
		$view->render ( TRUE );
	}
	
	public function add() {
		Passport::checkallow ( 'task.task_add' );
		$this->edit ();
	}
	
	public function copy($id) {
		Passport::checkallow ( 'task.task_add' );
		if (! ($id > 0)) {
			MyqeeCMS::show_error ( '没有指定的计划任务！', false, 'goback' );
		}
		$this->_iscopy = true;
		$this->edit ( $id );
	}
	
	public function edit($id = 0) {
		$this->db = Database::instance ();
		if ($id > 0) {
			
			$data = $this->db->from ( '[tasks]' )->where ( 'id', $id )->get ()->result_array ( FALSE );
			$data = $data [0];
			if (! is_array ( $data )) {
				MyqeeCMS::show_error ( '没有指定的计划任务！', false, 'goback' );
			}
			$data ['post'] = unserialize ( $data ['post'] );
		}
		$view = new View ( 'admin/task_edit' );
		
		if ($this->_iscopy === true) {
			unset ( $data ['id'], $id );
			$view->set ( 'iscopy', true );
		} else {
			if ($id > 0)
				$view->set ( 'isedit', true );
		}
		if ($id > 0) {
			Passport::checkallow ( 'task.task_edit' );
		} else {
			Passport::checkallow ( 'task.task_add' );
		}
		
		$view->set ( 'id', $id );
		$view->set ( 'data', $data );
		$result = $this->db->select ( 'cate' )->from ( '[tasks]' )->groupby ( 'cate' )->get ()->result_array ( FALSE );
		$taskcate = array ();
		foreach ( $result as $item ) {
			$taskcate [$item ['cate']] = $item ['cate'];
		}
		
		$result2 = $this->db->select ( 'taskmode' )->from ( '[tasks]' )->groupby ( 'taskmode' )->get ()->result_array ( FALSE );
		$taskmodearr = array ();
		foreach ( $result2 as $items ) {
			$taskmodearr [$items ['taskmode']] = $items ['taskmode'];
		}
		$view->taskcate = $taskcate;
		$view->taskmodearr = $taskmodearr;
		$view->render ( TRUE );
	}
	
	public function save($id = 0) {
		if ($id > 0) {
			Passport::checkallow ( 'task.task_edit' );
		} else {
			Passport::checkallow ( 'task.task_add' );
		}
		$post = $_POST ['task'];
		//		$data['id'] = (int)$id;
		if (! ($data ['name'] = htmlspecialchars ( $post ['name'] ))) {
			MyqeeCMS::show_error ( '请设置计划任务名称！', true );
		}
		//检测是否已经存在的文件
		$chkwhere = array ('name' => $data ['name'] );
		if ($id > 0)
			$chkwhere ['id!='] = $id;
		$db = $adminmodel ? $adminmodel->db : Database::instance ();
		$chktask = $db->getwhere ( '[tasks]', $chkwhere )->result_array ( FALSE );
		$chktask = $chktask [0];
		if ($chktask) {
			MyqeeCMS::show_error ( '已经存在相同的计划任务!', true );
		}
		$data ['isuse'] = $post ['isuse'] == 0 ? 0 : 1;
		$data ['cate'] = $post ['cate'];
		$data ['cycletype'] = $post ['cycletype'];
		$data ['starttime'] = strtotime ( $post ['starttime'] );
		$data ['endtime'] = strtotime ( $post ['endtime'] );
		$data ['maxtimes'] = $post ['maxtimes'];
		$data ['taskfile'] = $post ['taskfile'];
		$data ['taskmode'] = $post ['taskmode'];
		
		if (empty ( $data ['cate'] )) {
			MyqeeCMS::show_error ( '请设置计划任务类别！', true );
		}
		
		switch ($post ['cycletype']) {
			case 1 :
				$cycles = $post ['domins'];
				break;
			case 2 :
				$cycles = $post ['dosecs'];
				break;
			case 3 :
				$cycles = $post ['dohours'];
				break;
			case 4 :
				$cycles = $post ['dodays'];
				break;
			case 5 :
				$cycles = $post ['doweeks'];
				
				break;
			case 6 :
				$cycles = $post ['domonths'];
				break;
			case 7 :
				$cycles = $post ['doyears'];
				break;
		}
		if (is_array ( $cycles )) {
			foreach ( $cycles as $tmp ) {
				$cycle .= $tmp . '|';
			}
			$data ['cycle'] = $cycle;
		} else {
			$data ['cycle'] = $cycles;
		}
		if (empty ( $data ['cycletype'] )) {
			MyqeeCMS::show_error ( '请设置任务周期！', true );
		}
		if (empty ( $data ['cycle'] )) {
			MyqeeCMS::show_error ( '请设置任务周期！', true );
		}
		if (empty ( $data ['starttime'] )) {
			MyqeeCMS::show_error ( '请设置计划任务开始时间！', true );
		}
		if (empty ( $data ['taskfile'] )) {
			MyqeeCMS::show_error ( '请设置计划任务脚本文件！', true );
		}
		if (! empty ( $data ['maxtimes'] ) && ! preg_match ( "/^[0-9]+$/", $data ['maxtimes'] )) {
			MyqeeCMS::show_error ( '最大执行次数只允许允许“数字”！', true );
		}
		if (empty ( $data ['taskfile'] ) || ! preg_match ( "/^[0-9a-zA-Z_]+$/", $data ['taskfile'] )) {
			MyqeeCMS::show_error ( '任务脚本文件只允许允许“数字、英文、下划线”且不能空！', true );
		}
		if (empty ( $data ['taskmode'] )) {
			MyqeeCMS::show_error ( '请设置计划任务方式！', true );
		}
		
		if ($id > 0) {
			$status = $db->update ( '[tasks]', $data, array ('id' => $id ) );
			//输出提示信息
		

		} else {
			$status = $db->insert ( '[tasks]', $data );
			$id = $status->insert_id ();
		}
		if ($status) {
			
			//保存配置文件
			if ($id) {
				$adminmodel = new Admin_Model ( );
				$taskarray = $adminmodel->get_tasks_array ( 1,0 );
				MyqeeCMS::saveconfig ( 'tasks', $taskarray );
			}
			MyqeeCMS::show_info ( '计划任务保存成功！', true );
		} else {
			MyqeeCMS::show_error ( '没有保存任何信息！', true );
		}
	}
	
	public function renewfiles($theid = 0) {
		Passport::checkallow ( 'task.task_renewfiles' );
		$adminmodel = new Admin_Model ( );
		$taskarray = $adminmodel->get_tasks_array ( 1,$theid );
		MyqeeCMS::saveconfig ( 'tasks', $taskarray );
		MyqeeCMS::show_info ( '执行完毕', TRUE );
	}
	
	public function output($allid, $key = '') {
		Passport::checkallow ( 'task.task_output' );
		$this->db = Database::instance ();
		
		$allid = Tools::formatids ( $allid, false );
		$results = $this->db->from ( '[tasks]' )->in ( 'id', $allid )->orderby ( 'id' )->get ()->result_array ( false );
		
		if (count ( $results ) == 0) {
			MyqeeCMS::show_info ( '没有符合条件的计划任务', true );
		}
		$mydata = Tools::info_encryp ($results, $key,true);
		if (! $mydata) {
			MyqeeCMS::show_info ( '没有符合条件的计划任务', true );
		}
		download::force ( './', $mydata, 'tasks.txt' );
	}
	
	public function inputtask() {
		Passport::checkallow ( 'task.task_input' );
		$view = new View ( 'admin/task_input' );
		$view->render ( TRUE );
	}
	
	public function input() {
		Passport::checkallow ( 'task.task_input' );
		//上传方式
		$thedata = $this->_getinputdata ();
		
		$this->db = Database::instance ();
		$inputerr = 0;
		$inputok = 0;
		foreach ( $thedata as $item ) {
			//保存文件
			$data ['name'] = $item ['name'];
			$data ['cate'] = $item ['cate'];
			$data ['starttime'] = $item ['starttime'];
			$data ['endtime'] = $item ['endtime'];
			$data ['nexttime'] = $item ['nexttime'];
			$data ['cycle'] = $item ['cycle'];
			$data ['cycletype'] = $item ['cycletype'];
			$data ['taskfile'] = $item ['taskfile'];
			$data ['taskmode'] = $item ['taskmode'];
			$data ['isuse'] = $item ['isuse'];
			$data ['userid'] = $item ['userid'];
			$data ['maxtimes'] = $item ['maxtimes'];
			$chkwhere = array ('name' => $data ['name'] );
			unset ( $chktask );
			$chktask = $this->db->getwhere ( '[tasks]', $chkwhere )->result_array ( FALSE );
			$chktask = $chktask [0];
			if (! $chktask) {
				$status = $this->db->insert ( '[tasks]', $data );
				//$id = $status->insert_id ();
				if (count ( $status )) {
					$inputok += 1;
				}
			} else {
				$inputerr += 1;
				$showinfo1 = '已存在相同名称的计划任务  ！';
			}
		}
		
		$adminmodel = new Admin_Model ( );
		$taskarray = $adminmodel->get_tasks_array ( 1,0 );
		MyqeeCMS::saveconfig ( 'tasks', $taskarray );
		$showinfo = '成功导入' . $inputok . '个计划任务,失败 ' . $inputerr . '个 ！';
		$showinfo .= $showinfo1;
		MyqeeCMS::show_info ( $showinfo, true );
	}
	
	protected function _getinputdata() {
		$key = $_POST ['key'];
		$thedata = $_POST ['data'];
		
		if (empty ( $thedata ) && $_FILES ['upload'] ['size'] == 0) {
			MyqeeCMS::show_error ( '导入文件为空，请返回重新操作！', true );
		}
		if ($_FILES ['upload'] ['tmp_name']) {
			$tmpfile = $_FILES ['upload'] ['tmp_name'];
			if ($_FILES ['upload'] ['size'] < 5000000) { //只操作5MB以内的文件
				if (! $thedata = @file_get_contents ( $_FILES ['upload'] ['tmp_name'] )) {
					MyqeeCMS::show_error ( '上传文件读取失败，请联系管理人员！', true );
				}
				//反解文件
				$thedata = Tools::info_uncryp ( $thedata, $key ,true );
			} else {
				MyqeeCMS::show_error ( '上传的文件太大或上传失败，本系统只解析5MB以内大小模板！', true );
			}
		} else {
			if (strlen ( $thedata ) < 5000000) {
				//反解文件
				$thedata = Tools::info_uncryp ( $thedata, $key ,true );
			} else {
				MyqeeCMS::show_error ( '上传的文件太大或上传失败，本系统只解析5MB以内大小模板！', true );
			}
		}
		if ($thedata === - 1) {
			MyqeeCMS::show_error ( '待导入内容遭受修改或受损，系统已终止导入，请返回！', true );
		}
		
		if (! is_array ( $thedata )) {
			MyqeeCMS::show_error ( '解析模板失败，可能密码错误或导入的文件错误！', true );
		}
		return $thedata;
	}
	
	public function del($id) {
		Passport::checkallow ( 'task.task_del' );
		if (! ($id > 0))
			MyqeeCMS::show_error ( '参数错误', true );
		
		$db = Database::instance ();
		
		if ($db->delete ( '[tasks]', array ('id' => $id ) )) {
			if (MyqeeCMS::delconfig ( 'task/task_' . $id )) {
				MyqeeCMS::show_info ( '删除计划任务成功！', true, 'refresh' );
			} else {
				MyqeeCMS::show_info ( '删除计划任务失败！', true );
			}
		} else {
			MyqeeCMS::show_error ( '没有删除任何信息', true );
		}
	}
	
	public function run_task($type = 1) {
		if ($type == 1) {
			$task = Task::instance()->start_task();
			if ($task===true){
				MyqeeCMS::show_ok ( '启动计划任务监控成功！', true, 'refresh' );
			}elseif ($task===-1){
				MyqeeCMS::show_info ( '计划任务正在执行中！', true );
			}elseif($task===-2){
				MyqeeCMS::show_error ( '计划任务正在关闭中，请稍后再试！', true );
			}else {
				MyqeeCMS::show_error ( '启动计划任务监控失败！', true );
			}
		} else {
			if (Task::instance()->stop_task()) {
				MyqeeCMS::show_ok ( '关闭计划任务监控成功！，5秒内将停止！', true, 'refresh' );
			} else {
				MyqeeCMS::show_error ( '关闭计划任务监控失败！', true );
			}
		
		}
	}
	
}