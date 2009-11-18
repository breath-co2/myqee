<?php

class Admin_Controller_Core extends Controller {
	function __construct(){
		parent::__construct();
		$this->session = Passport::chkadmin();
	}

	
	public function index(){
		$view = new View('admin/index');
		$view->render(TRUE);
	}
	
	public function mylist($page=1){
		Passport::checkallow('admin.list');
		$per = 20;
		$view = new View('admin/admin_list');
		$db = new Database;
		//分页显示
		$num = $db -> count_records('[admin]');
		
		$this->pagination = new Pagination( array(
			'uri_segment'    => 'index',
			'total_items'    => $num,
			'items_per_page' => $per
		) );

		$result = $db -> from('[admin]') -> orderby("id","DESC") -> limit( $per , $this -> pagination -> sql_offset() ) -> get() -> result_array(FALSE);

		$view -> set('list' , $result );
		$view -> set ('page' , $this -> pagination -> render('digg') );

		$view -> render(TRUE);
	}
	public function add(){
		$this->edit();
	}
	public function edit($id=0){
		if (!Passport::getisallow('admin.changecompetence') && !Passport::getisallow('admin.changepassword_1') && !Passport::getisallow('admin.changepassword_2')){
			MyqeeCMS::show_error('抱歉，您没有操作此页面的权限！',FALSE,'goback');
		}
		$view = new View('admin/admin_add');
		$view -> set('admininfo',$this->session->get('admin'));
		
		$this->db = new Database;
		$view -> admingroup = $this -> _get_group_forselect();
		
		if ($id>0){
			$view -> page_title = '修改管理员帐号';
			$query = $this->db->from('[admin]')->where(array('id >=' => $id))->limit(1)->get();
			if ($result = $query->result_array(FALSE)){
				$result = $result[0];
				if ($result['groupid']>0){
					$groupset = $this -> _get_grouparray($result['groupid']);
					$result['competence'] = $groupset['competence'];
				}
				$result['competence'] = unserialize($result['competence']);
				
				//处理默认设置
				if ($result['auto_classset']==1){
					$result['classset'] = '';
				}else{
					if (!empty($result['classset']) && $result['classset']!='-ALL-'){
						$result['classset'] = explode(',',$result['classset']);
					}
				}
				
				if ($result['auto_dbset']==1){
					$result['dbset'] = '';
				}else{
					if (!empty($result['dbset']) && $result['dbset']!='-ALL-'){
						$result['dbset'] = explode(',',$result['dbset']);
					}
				}
			
				if ($result['auto_siteset']==1){
					$result['siteset'] = '';
				}else{
					if (!empty($result['siteset']) && $result['siteset']!='-ALL-'){
						$result['siteset'] = explode(',',$result['siteset']);
					}
				}
				
				if ($result['auto_defaultsite']==1){
					$result['defaultsite'] = '';
				}elseif($result['defaultsite']==0){
					$result['defaultsite'] = '-ALL-';
				}
				
				$view -> set('admin',$result);
			}
		}else{
			$view -> page_title = '添加帐号';
		}
		$adminmodel = new Admin_Model();
		$dbtable = $adminmodel -> get_dbtable_forselect(FALSE,array(''=>'------------------按管理组设置------------------','-ALL-'=>'------------------全部数据表-----------------'));
		$view -> set ('classtree' , $adminmodel -> get_allclass_array('classid,classname,bclassid,classpath,hits,myorder') );
		$view -> set('alldb',$dbtable['forselect']);
		$view -> set('allsite',$adminmodel -> get_allsites_forselect(array(''=>'------------------按管理组设置------------------','-ALL-'=>'-------------------全部站点-------------------')));
		
		$view -> competence = Myqee::config('competence');
		$view -> render(TRUE);
	}
	
	protected function _get_group_forselect() {
		if (!$this -> gourparray){
			$this->db or $this->db = new Database;
		$grouplist = $this->db -> from('[admin_group]')->get()->result_array(FALSE);
		}else{
			$grouplist = $this -> gourparray;
		}
		$listarray = array('自定义');
		$gourparray = array();
		foreach ($grouplist as $value){
			$listarray[$value['id']] = $value['groupname'];
			$gourparray[$value['id']] = $value;
		}
		$this -> gourparray = $gourparray;
		return $listarray;
	}
	
	protected function _get_grouparray($groupid){
		if (!$this -> gourparray){
			$this->db or $this->db =  Database::instance();
			$grouparray = $this->db -> from('[admin_group]')->where('id',$groupid)->get()->result_array(FALSE);
			return $grouparray[0];
		}else{
			return $this -> gourparray[$groupid];
		}
	}
	

	public function save(){
		if (!$post=$_POST['admin']){
			MyqeeCMS::show_error('参数错误！',TRUE);
		}
		$post['id']=(int)$post['id'];
		
		$admin=array(
			'username' => htmlspecialchars($post['username'])
		);
		if (empty($admin['username'])){
			MyqeeCMS::show_error('帐号名称不能空！',TRUE);
		}
		
		//添加帐户时必须密码不能空
		if ($post['id']==0){
			if (empty($post['password'])){
				MyqeeCMS::show_error('密码不能空！',TRUE);
			}
		}
		if (!empty($post['password'])){
			if (strlen($post['password'])<6){
				MyqeeCMS::show_error('密码长度不能小于6位！',TRUE);
			}
			if ($post['password'] != $post['rpassword']){
				MyqeeCMS::show_error('两次输入的密码不相同！',TRUE);
				exit();
			}
			$admin['password'] = md5($post['password']);
		}
		
		$this -> db = Database::instance();
		
		if (Passport::getisallow('admin.changecompetence')){
			if ($post['groupid']>0){
				if ($groupinfo = $this -> _get_grouparray($post['groupid']) ){
					//权限组
					$admin['groupid'] = $post['groupid'];
					$admin['groupname'] = $groupinfo['groupname'];
					$admin['competence'] = $groupinfo['competence'];
				}else{
					$admin['groupid'] = 0;
					$admin['groupname'] = '自定义';
				}
			}else{
				//自定义权限组
				$admin['groupid'] = 0;
				$admin['competence'] = serialize($this -> _get_competence($_POST['competence']));
			}
			if (is_array($post['classset'])){
				if (in_array('',$post['classset'])){
					$admin['classset'] = $groupinfo['class'];
					$admin['auto_classset'] = 1;
				}else{
					if (in_array('-ALL-',$post['classset'])){
						$admin['classset'] = '-ALL-';
					}else{
						$admin['classset'] = implode(',',$post['classset']);
					}
					$admin['auto_classset'] = 0;
				}
			}
			if (is_array($post['dbset'])){
				if (in_array('',$post['dbset'])){
					$admin['dbset'] = $groupinfo['db'];
					$admin['auto_dbset'] = 1;
				}else{
					if (in_array('-ALL-',$post['dbset'])){
						$admin['dbset'] = '-ALL-';
					}else{
						$admin['dbset'] = implode(',',$post['dbset']);
					}
					$admin['auto_dbset'] = 0;
				}
			}
			if (is_array($post['siteset'])){
				if (in_array('',$post['siteset'])){
					$admin['siteset'] = $groupinfo['site'];
					$admin['auto_siteset'] = 1;
				}else{
					if (in_array('-ALL-',$post['siteset'])){
						$admin['siteset'] = '-ALL-';
					}else{
						$admin['siteset'] = implode(',',$post['siteset']);
					}
					$admin['auto_siteset'] = 0;
				}
			}
		}
		if ($post['defaultsite']=='-ALL-'){
			$admin['auto_defaultsite'] = 0;
			$admin['defaultsite'] = 0;
		}elseif($post['defaultsite']>0){
			$admin['auto_defaultsite'] = 0;
			$admin['defaultsite'] = $post['defaultsite'];
		}else{
			$admin['auto_defaultsite'] = 1;
			$admin['defaultsite'] = $groupinfo['defaultsite'];
		}
		if($admin['password']){
			if ($post['id'] == $this->session->get('admin.id')){
				Passport::checkallow('admin.changepassword_1','抱歉，您没有修改个人密码的权限！',TRUE);
			}else{
				Passport::checkallow('admin.changepassword_2','抱歉，您没有修改他人密码的权限！',TRUE);
			}
		}
		if ($post['id']>0){
			//更新
			$status = $this -> db->update('[admin]',$admin,array('id' =>$post['id']));
		}else{
			//插入
			$status = $this -> db->insert('[admin]',$admin);
		}
		$rows = count($status);
		if ($rows>0){
			MyqeeCMS::show_info('保存成功！',TRUE);
		}else{
			MyqeeCMS::show_info('未更新任何数据！',TRUE);
		}
	}
	
	protected function _get_competence($newcpt){
		$competence = Myqee::config('competence');
		if (!is_array($competence))return array();
		$tmpcpt = array();
		foreach ($competence as $key => $value){
			if ($newcpt[$key] && is_array($value['sub'])){
				foreach ($value['sub'] as $k => $v){
					if ($newcpt[$key][$k]){
						$tmpcpt[$key][$k] = 1;
					}
				}
			}
		}
		return $tmpcpt;
	}
	
	public function del($id=0){
		Passport::checkallow('admin.del',NULL,TRUE);
		$id = (int)$id;
		if ($id>0){
			if ($id==1){
				MyqeeCMS::show_info('系统帐号禁止删除',TRUE);
			}
		}else{
			MyqeeCMS::show_info('缺少参数！',TRUE);
		}
		$db = new Database;
		$status = $db->delete('[admin]', array('id' => $id));
		if (count($status)>0){
			MyqeeCMS::show_info('删除成功！',TRUE,'refresh');
		}else{
			MyqeeCMS::show_info('未删除任何数据！',TRUE);
		}
	}
	

	public function group_list($page=1){
		Passport::checkallow('admin.group_list');
		$per = 20;
		$view = new View('admin/admin_group_list');
		$db = new Database;
		//分页显示
		$num = $db -> count_records('[admin]');
		
		$this->pagination = new Pagination( array(
			'uri_segment'    => 'index',
			'total_items'    => $num,
			'items_per_page' => $per
		) );

		$result = $db -> from('[admin_group]') -> orderby("id","DESC") -> limit( $per , $this -> pagination -> sql_offset() ) -> get() -> result_array(FALSE);

		$view -> set('list' , $result );
		$view -> set ('page' , $this -> pagination -> render('digg') );

		$view -> render(TRUE);
	}
	
	public function group_add(){
		Passport::checkallow('admin.group_add');
		$this->group_edit();
	}
	public function group_edit($id=0){
		if ($id>0){
			Passport::checkallow('admin.group_edit',NULL,TRUE);
		}else{
			Passport::checkallow('admin.group_add',NULL,TRUE);
		}
		$view = new View('admin/admin_group_add');
		
		$this->db = new Database;
		if ($id>0){
			$view -> page_title = '修改管理员帐号';
			$result = $this->db -> from('[admin_group]')->where(array('id' => $id))->limit(1)->get()->result_array(FALSE);
			$result = $result[0];
			if ($result){
				$result['competence'] = unserialize($result['competence']);
				if ($result['class']!='-ALL-'){
					$result['class'] = explode(',',$result['class']);
				}
				if ($result['db']!='-ALL-'){
					$result['db'] = explode(',',$result['db']);
				}
				if ($result['site']!='-ALL-'){
					$result['site'] = explode(',',$result['site']);
				}
				$view -> set('group',$result);
			}
		}else{
			$view -> page_title = '添加管理组';
		}
		$adminmodel = new Admin_Model();
		$adminmodel -> allow_db = '-ALL-';
		$dbtable = $adminmodel -> get_dbtable_forselect(FALSE,array('-ALL-'=>'------------------全部数据表-----------------'));
		$view -> set ('classtree' , $adminmodel -> get_allclass_array('classid,classname,bclassid,classpath,hits,myorder') );
		$view -> set('alldb',$dbtable['forselect']);
		$view -> set('allsite',$adminmodel -> get_allsites_forselect(array('-ALL-'=>'-------------------全部站点-------------------')));
		$view -> competence = Myqee::config('competence');
		$view -> render(TRUE);
	}
	
	
	public function group_save(){
		if (!$post=$_POST['group']){
			MyqeeCMS::show_error('参数错误！',TRUE);
		}
		$post['id']=(int)$post['id'];
		if ($post['id']>0){
			Passport::checkallow('admin.group_edit');
		}else{
			Passport::checkallow('admin.group_add');
		}
		$group=array(
			'groupname' => htmlspecialchars($post['groupname'])
		);
		if (empty($group['groupname'])){
			MyqeeCMS::show_error('管理组名称不能空！',TRUE);
		}
		
		if (is_array($post['class'])){
			if (in_array('-ALL-',$post['class'])){
				$group['class'] = '-ALL-';
			}else{
				$group['class'] = implode(',',$post['class']);
			}
		}
		if (is_array($post['db'])){
			if (in_array('-ALL-',$post['db'])){
				$group['db'] = '-ALL-';
			}else{
				$group['db'] = implode(',',$post['db']);
			}
		}
		if (is_array($post['site'])){
			if (in_array('-ALL-',$post['site'])){
				$group['site'] = '-ALL-';
			}else{
				$group['site'] = implode(',',$post['site']);
			}
		}
		$group['defaultsite'] = (int)$post['defaultsite'];
		
		$this -> db = Database::instance();
		
		$group['competence'] = serialize($this -> _get_competence($_POST['competence']));
		
		if ($post['id']>0){
			//更新
			$status = $this -> db->update('[admin_group]',$group,array('id' =>$post['id']));
			if (count($status)){
				//更新管理员数据表
				$this -> db -> update('[admin]',array(
					'groupname' => $group['groupname'],
					'competence' => $group['competence'],
				),array('groupid' =>$post['id']));
				
				$this -> db -> update('[admin]',array(
					'classset' => $group['class'],
				),array('groupid' =>$post['id'],'auto_classset'=>1));
				
				$this -> db -> update('[admin]',array(
					'dbset' => $group['db'],
				),array('groupid' =>$post['id'],'auto_dbset'=>1));
				
				$this -> db -> update('[admin]',array(
					'siteset' => $group['site'],
				),array('groupid' =>$post['id'],'auto_siteset'=>1));
				
				$this -> db -> update('[admin]',array(
					'defaultsite' => $group['defaultsite'],
				),array('groupid' =>$post['id'],'auto_defaultsite'=>1));
			}
		}else{
			//插入
			$status = $this -> db->insert('[admin_group]',$group);
		}
		$rows = count($status);
		if ($rows>0){
			MyqeeCMS::show_info('保存成功！',TRUE,Myqee::url('admin/group_list'));
		}else{
			MyqeeCMS::show_info('未更新任何数据！',TRUE);
		}
	}
	
	public function group_del($id=0){
		Passport::checkallow('admin.group_del');
		$id = (int)$id;
		if (!($id>0)){
			MyqeeCMS::show_info('缺少参数！',TRUE);
		}
		$db = new Database;
		$status = $db->delete('[admin_group]', array('id' => $id));
		if (count($status)>0){
			MyqeeCMS::show_info('删除成功！',TRUE,'refresh');
		}else{
			MyqeeCMS::show_info('未删除任何数据！',TRUE);
		}
	}
}