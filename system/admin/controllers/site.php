<?php
/**
 * $Id: site.php,v 1.6 2009/10/19 01:32:53 jonwang Exp $
 *
 * @package    Acquisition
 * @author     Myqee Team
 * @copyright  (c) 2008-2010 Myqee Team
 * @license    http://Myqee.com/license.html
 */
class Site_Controller_Core extends Controller {
	function __construct(){
		parent::__construct();
		$this -> session = Passport::chkadmin();
	}

	public function index(){
		$view = new View('admin/site_list');
		
		$db = Database::instance();
		$result = $db -> from('[site]');
		if ($_SESSION['admin']['siteset'] && $_SESSION['admin']['siteset']!='-ALL-'){
			$mysite = explode(',',$_SESSION['admin']['siteset']);
			$result = $result -> in('id',$mysite);
		}
		$result = $result -> orderby('id','DESC')->get()->result_array(FALSE);
		
		$view -> set('list',$result);
		$view -> render(TRUE);
	}
	
	public function add(){
		$this -> edit();
	}
	
	public function edit($id=0){
		$db = Database::instance();
		if ($id>0){
			Passport::checkallow('index.site_edit');
			$siteinfo = $db -> from('[site]') -> where('id',$id) -> get() -> result_array(FALSE);
			$siteinfo = $siteinfo[0];
			if ($siteinfo){
				$siteinfo['config'] = unserialize($siteinfo['config']);
			}
		}else{
			Passport::checkallow('index.site_add');
			$siteinfo = NULL;
		}
		
		/*获取有权限的数据表*/
		/*
		$result = $db -> select('name,dbname');
		$admindbset = $_SESSION['admin']['dbset'];
		if ($admindbset && $admindbset!='-ALL-'){
			$mydbtable = $mydbtable -> in('name',explode(',',$admindbset));
		}
		$result = $result -> orderby('myorder','ASC') -> getwhere('[dbtable]',array('isuse'=>1)) -> result_array(FALSE);
		$alldbarr = array();
		$mydb = array('' => '----------------全部有权限的表----------------');
		foreach ($result as $item){
			$mydb[$item['name']] = $item['dbname'] .'('.$item['name'].')';
			$alldbarr[] = $item['name'];
		}
		
		//$mymodel = $this -> _get_mymodel($alldbarr);

		$myclass = $this -> _get_myclass();
		
		$adminmodel = new Admin_Model;
		$adminmodel -> classArray = $db -> select('classid,bclassid,classname,sonclass,fatherclass') -> orderby('myorder','ASC') -> getwhere('[class]') -> result_array(FALSE);
		$myclass = $adminmodel -> get_allclass_array(0,0,true);
		
*/
		$view = new View('admin/site_edit');
		$view -> set('site',$siteinfo);
		
		
		$tplgroup = MyqeeCMS::config('template.group');
		if (!is_array($tplgroup)){
			$tplgroup = array('default' => '默认模板组');
		}else{
			$tmpgroup = array();
			foreach ($tplgroup as $key => $item){
				$tmpgroup[$key] = $item['name'];
			}
			$tplgroup = $tmpgroup;
		}

		
		$view -> set('tplgroup',$tplgroup);
		
		$view -> render(TRUE);
	}
	/*
	public function get_html(){
		if ($_GET['type']=='mymodel'){
			$myarr = $this -> _get_mymodel($_GET['value']?explode(',',$_GET['value']):NULL);
			echo form::dropdown('site[model][]',$myarr,explode(',',$_GET['checked']),'id="modelset" size="8" style="width:300px;" multiple="multiple" onchange="changemodel()"');
		}else{
			$myarr = $this -> _get_myclass( $_GET['value']?explode(',',$_GET['value']):NULL , $_GET['db']?explode(',',$_GET['db']):NULL );
			$v = explode(',',$_GET['checked']);
			echo form::classlist('site[class][]',$myarr,'id="classset" size="16" style="width:300px;" multiple="multiple"',$v,array('---------------全部有权限的栏目----------------'));
		}
	}
	
	protected function _get_mymodel($alldbarr=array()){
		$mymodel = array();
		
		$result = Database::instance() -> select('id,modelname');
		if (is_array($alldbarr)&&count($alldbarr))$result = $result -> in('dbname',$alldbarr);
		$adminmodelset = $_SESSION['admin']['modelset'];
		if ($adminmodelset && $adminmodelset!='-ALL-'){
			$result = $result -> in('name',explode(',',$adminmodelset));
		}
		$result = $result -> orderby('myorder','ASC') -> getwhere('[model]',array('isuse'=>1)) -> result_array(FALSE);
		$mymodel = array('' => '---------------全部有权限的模型---------------');
		$allmodel = array();
		foreach ($result as $item){
			$mymodel[$item['id']] = $item['modelname'];
			$allmodel[] = $item['id'];
		}
		$this -> allmodel = $allmodel;
		
		return $mymodel;
	}
	
	protected function _get_myclass($allmodelarr=array(),$alldb=array()){
		$myclass = array();
		
		$result = Database::instance() -> select('classid,bclassid,classname,sonclass,fatherclass');
		if (is_array($allmodelarr)&&count($allmodelarr))$result = $result -> in('modelid',$allmodelarr);
		if (is_array($alldb)&&count($alldb))$result = $result -> in('dbname',$alldb);
		$adminclassset = $_SESSION['admin']['classset'];
		if ($adminclassset && $adminclassset!='-ALL-'){
			$result = $result -> in('classid',explode(',',$adminclassset));
		}
		$myclass = $result -> orderby('myorder','ASC') -> getwhere('[class]') -> result_array(FALSE);
//		echo Database::instance() -> last_query();exit;
		$adminmodel = new Admin_Model;
		$adminmodel -> classArray = $myclass;
		$myclass = $adminmodel -> get_allclass_array(0,0,true);
		
		return $myclass;
	}
*/
	
	public function get_tlphtml(){
		$adminmodel = new Admin_Model();
		$covertemplate = $adminmodel -> get_alltemplate('cover',$_GET['group']);
		echo 'HTML=',form::dropdown('site[config][indexpage][tpl]',$covertemplate,'','id="index_tpl"');
	}
	
	
	public function save($id=0){
		if ($id>0){
			Passport::checkallow('index.site_edit');
		}else{
			Passport::checkallow('index.site_add');
		}
		$post = $_POST['site'];
		$post['sitename'] = trim($post['sitename']);
		if (!$post['sitename']) MyqeeCMS::show_error(Myqee::lang('admin.site.error.sitename_empty'),TRUE);
		
		if ($post['config']){
			if ($post['config']['indexpage']){
				$indexset = $post['config']['indexpage'];
				$indexset['filename'] = trim($indexset['filename']);
				if (!empty($indexset['filename']) && preg_match("/[^a-z0-9\.~_\-\(\),]/i",$indexset['filename'])){
					MyqeeCMS::show_error(Myqee::lang('admin.site.error.errorfilename'),TRUE);
				}
				
				$indexset['filepath'] = str_replace('\\','/',trim($indexset['filepath']));
				if (!empty($indexset['filepath']) && preg_match("/[^a-z0-9~_\/\-]/i",$indexset['filepath'])){
					MyqeeCMS::show_error(Myqee::lang('admin.site.error.errorfilepath'),TRUE);
				}
				
				$post['config']['indexpage'] = array(
					'isuse' => $indexset['isuse']?1:0,
					'filename' => $indexset['filename'],
					'filepath' => $indexset['filepath'],
					'tpl' => $indexset['tpl']>0?$indexset['tpl']:0,
				);
			}
		}
		$data = array(
			'sitename' => $post['sitename'],
			'siteurl' => $post['siteurl'],
			'sitehost' => $post['sitehost'],
			'myorder' => $post['myorder'],
			'isuse' => $post['isuse'],
			'content' => $post['content'],
			'config' => serialize($post['config']),
		);
		if ($id>0){
			$data['id'] = $id;
		}
		
		$result = Database::instance() -> merge('[site]',$data);
		$count = $result -> count();
		$id = $result -> insert_id();
		
		if ($count>0){
			
			//对于拥有独立管理站点的管理员，给他添加到自己的管理站点里
			if ($_SESSION['admin']['siteset'] && $_SESSION['admin']['siteset']!='-ALL-'){
				$mysite = explode(',',$_SESSION['admin']['siteset']);
				if(!in_array($id,$mysite)){
					$mysite[] = $id;
					$mysite = implode(',',$mysite);
					$_SESSION['admin']['siteset'] = $mysite;
					Database::instance() -> update('[admin]',array('siteset'=>$mysite),array('id'=>$_SESSION['admin']['id']));
				}
			}
			$data['config'] = $post['config'];
			$this -> _save_config($id,$data);
			MyqeeCMS::show_ok(Myqee::lang('admin/site.info.success'),TRUE);
		}else{
			MyqeeCMS::show_info(Myqee::lang('admin/site.info.donothing'),TRUE);
		}
	}
	
	
	protected function _save_config($id,$data){
		MyqeeCMS::saveconfig('site/'.$id,$data);
	}


	/**
	 * 删除网站
	 *
	 * @param Integer $id 网站ID
	 */
	public function del($id=''){
		Passport::checkallow('index.site_del');
		
		if (!($id>0)){
			MyqeeCMS::show_error(Myqee::lang('admin/site.error.parametererror'));
		}
		
		$db = Database::instance();
		$siteinfo = $db -> from('[site]') -> where('id',$id) -> get() -> result_array(FALSE);
		$siteinfo = $siteinfo[0];
		if (!$siteinfo){
			MyqeeCMS::show_error(Myqee::lang('admin/site.error.no_site'));
		}
		$count = $db -> delete('[site]',array('id'=>$id)) -> count();
		
		MyqeeCMS::delconfig('site/'.$id);
		if ($count){
			MyqeeCMS::show_ok('恭喜，删除成功！',true,'refresh');
		}else{
			MyqeeCMS::show_info('未删除任何站点！',true);
		}
	}
	
	public function changesite($id=''){
		$db = Database::instance();
		if ($id)$siteinfo = MyqeeCMS::config('site/'.$id);
		if ($siteinfo && $siteinfo['isuse']){
			$this -> session -> set ('now_site',$id);
			$this -> session -> set ('now_site_name',$siteinfo['sitename']);
			if ($siteinfo['config']['template_group']){
				$this -> session -> set ('now_site_tlpgroup',$siteinfo['config']['template_group']);
			}
		}else{
			$this -> session -> delete ('now_site');
			$this -> session -> delete ('now_site_name');
			$this -> session -> delete ('now_site_tlpgroup');
		}
		$this -> session -> delete ('now_tlpgroup');
		
		$view = new View('admin/header');
		$view -> set('recache',TRUE);
		$view -> render(FALSE);
		
		MyqeeCMS::show_ok('恭喜，切换站点成功！',false,Myqee::url('index'));
	}
	
}