<?php
class Class_Controller_Core extends Controller {

	function __construct(){
		parent::__construct(NULL);
		$this->session = Passport::chkadmin();
	}

	public function index($bclassid = 0){
		Passport::checkallow('class.list');
		$view = new View('admin/class_list');
		$adminmodel = new Admin_Model;
		$myclass = $adminmodel -> get_allclass_array($bclassid,0,true,null,true);
		$view -> set ( 'list' , $myclass );
		if ($bclassid){
			//获取“你现在的位置”的数组，并传递给视图的$location变量
			$view ->location  = $adminmodel -> get_location_array($bclassid);
		}
		$view -> render(TRUE);
	}

	public function mylist($page=1,$bclassid = 0){
		Passport::checkallow('class.list');
		$view = new View('admin/class_list');
		$adminmodel = new Admin_Model;
		$per = 20;

		$num = $adminmodel -> db -> count_records('[class]');
		$this -> pagination = new Pagination( array(
			'uri_segment'    => 'mylist',
			'total_items'    => $num,
			'items_per_page' => $per
		) );

		$view -> set ( 'list' , $adminmodel -> get_allclass_forlist('*',$per,$this -> pagination -> sql_offset) );

		$view -> set('page', $this -> pagination -> render('digg') );
		$view -> render(TRUE);
	}

	public function add($bclasid=0){
		$this -> edit(0,$bclasid);
	}

	public function copy($clasid=0){
		$this -> edit($clasid,null,true);
	}

	public function edit($id=0,$bclassid=0,$iscopy=false){
		if ( $iscopy == false && $id > 0 ){
			Passport::checkallow('class.edit');
			if (!Passport::getisallow_class($id)){
				MyqeeCMS::show_error('你没有权限操作此栏目！');
			}
		}else{
			Passport::checkallow('class.add');
		}
		$view = new View('admin/class_add');	//load views
		$adminmodel = new Admin_Model;		//load models
		
		
		if ( $id > 0 ){
			$thisclass = $adminmodel -> get_class_array($id);
		}
		
		//获取所有栏目（若此栏目是某个站点下的，则只读取同站点下的栏目）
		$allclass_array = $adminmodel -> get_allclass_array(0,0,FALSE,$thisclass['siteid']>0?array('siteid'=>$thisclass['siteid']):NULL );
		
		if ( $id > 0 ){
			$view -> page_title = '修改栏目';
			if ($iscopy){
				unset($thisclass['classid']);
				$thisclass['classname'] = $thisclass['classname'] .'(1)';
				$thisclass['classpath'] = $thisclass['classpath'] .'_1';
				$view -> page_title = '复制栏目';
			}
			$view -> set('class',$thisclass);
			$dbfield = $adminmodel -> get_table_field($thisclass['dbname']);
			
			//修改站点
			$changesite = $thisclass['bclassid']==0 && Passport::getisallow('class.dbchangesite')?TRUE:FALSE;
			$view -> set('changesite',$changesite);
		
			if ($thisclass['siteid']){
				//有指定站点
				$siteconfig = MyqeeCMS::config('site/'.$thisclass['siteid']);
				if ($siteconfig){
					if ($siteconfig['config']['template_group']){
						$class_tplgroup = $siteconfig['config']['template_group'];
					}
				}
			}
			
			if ($changesite){
				//列出所有站点
				if (Passport::getadminsite()=='-ALL-'){
					$addlist = array('0'=>'[主站点]');
				}else{
					$addlist = array();
				}
				$view -> set('mysite',$adminmodel -> get_site_forselect($addlist) );
			}
		
		}else{
			$view -> page_title = '新增栏目';
			$default_class = array('iscontent'=>1,'islist'=>1);
			if ($bclassid>0){
				$default_class['bclassid'] = $bclassid;
			}
			$dbfield = array('id'=>'ID');
			$view -> set('class',$default_class);
		}
		$view -> set ('dbfield' , $dbfield);
		$view -> set ('classtree' , $allclass_array );		//class array
		$view -> set ('models' , $adminmodel -> get_model_for_dropdown() );					//models array
		$view -> set ('allclass_path' , $adminmodel -> get_allclass_jsonpath() );			//class path
		$view -> set ('cover_tplarray' , $adminmodel -> get_alltemplate('cover' ,$class_tplgroup) );			//cover template
		$view -> set ('list_tplarray' , $adminmodel -> get_alltemplate('list' ,$class_tplgroup) );			//list template
		$view -> set ('content_tplarray' , $adminmodel -> get_alltemplate('content' ,$class_tplgroup) );		//content template
		$view -> set ('search_tplarray' , $adminmodel -> get_alltemplate('search' ,$class_tplgroup) );		//search template
		
		
		$view -> render(TRUE);
	}

	public function save(){
		$post = $_POST['class'];
		if ($post['classid']>0){
			Passport::checkallow('class.edit','',true);
			if (!Passport::getisallow_class($id)){
				MyqeeCMS::show_error('你没有权限操作此栏目！',true);
			}
		}else{
			Passport::checkallow('class.add','',true);
		}
		$adminmodel = new Admin_Model;

		if ($classid = $adminmodel -> save_edit_class($post)){
			MyqeeCMS::show_info(
				array(
					'message' => Myqee::lang('admin/class.info.updataok'),
					'btn' => array( array('返回列表','ok'),array('添加栏目','addclass'),array('重新修改','editclass') ),
					'handler' => 'function (el){
						if (el=="addclass"){parent.document.location.href="'.Myqee::url('class/add').'";}
						else if (el=="editclass"){parent.document.location.href="'.Myqee::url('class/edit/'.$classid).'";}
						else if (el=="ok"){parent.document.location.href="'.Myqee::url('class/index').'";}
					}',
				)
			,true);
		}else{
			MyqeeCMS::show_info(Myqee::lang('admin/class.info.noupdata'),true);
		}
	}
	
	public function renew_config($classid=0){
		if($classid && strpos($classid,',')!==FALSE){
			$classid = Tools::formatids($classid);
		}
		$adminmodel = new Admin_Model;
		list($run_ok,$run_error) = $adminmodel -> renew_classcatch($classid);
		
		$msg = Myqee::lang('admin/class.info.renewconfigok',$run_ok).Myqee::lang('admin/class.info.renewconfigerror',$run_error);
		if ($_GET['type']=='auto'){
			echo '<script>parent.showinfo("class","'.$msg.'");document.location.href="'.ADMIN_IMGPATH.'/admin/block.html";</script>';
		}else{
			MyqeeCMS::show_ok($msg,true);
		}
	}

	public function del($classid){
		Passport::checkallow('class.del','',true);
		$classid = (int)$classid;
		if ($classid==0)MyqeeCMS::show_error(Myqee::lang('admin/class.error.noparameters'),true);

		$adminmodel = new Admin_Model;

		$myclass = $adminmodel -> get_class_array($classid,'classpath,bclassid');
		if (!$myclass)MyqeeCMS::show_error(Myqee::lang('admin/class.error.nothisclass'),true);
		
		$status = $adminmodel -> db -> delete('[class]',array('classid'=>$classid))->count();
		MyqeeCMS::delconfig('class/class_'.$classid);
		
		$fatherclass = $adminmodel -> db -> like('fatherclass','|'.$classid.'|') -> get('[class]') -> result_array(FALSE);
		if (count($fatherclass)){
			$ids = array();
			foreach ($fatherclass as $v){
				$ids[] = $v['classid'];
				MyqeeCMS::delconfig('class/class_'.$v['classid']);
			}
			$status  += $adminmodel -> db -> in('classid',$ids) -> delete('[class]') -> count();
		}
		
		$sonclass = $adminmodel -> db -> like('sonclass','|'.$classid.'|') -> get('[class]') -> result_array(FALSE);
		if (count($sonclass)){
			foreach ($sonclass as $v){
				$data = array('sonclass' => str_replace('|'.$classid.'|','|',$v['sonclass']) );
				if ($data['sonclass']=='|')$data['sonclass'] = '';
				$v['sonclass'] = $data['sonclass'];
				
				$adminmodel -> db -> update('[class]',$data,array('classid'=>$v['classid']));
				MyqeeCMS::saveconfig('class/class_'.$v['classid'],$v);
			}
		}
		echo WWWROOT.$myclass['classpath'];
		Tools::remove_dir(WWWROOT.$myclass['classpath']);
		MyqeeCMS::show_info(Myqee::lang('admin/class.info.deleteok',$status),true,'refresh');
	}

	public function editorder(){
		Passport::checkallow('class.edit_paixu','',true);
		if ( !($myorder = $_GET['order']) ){
			MyqeeCMS::show_error(Myqee::lang('admin/class.error.noorderinfo'),true);
		}
		$adminmodel = new Admin_Model();
		$updatenum = $adminmodel -> editmyorder('[class]',$myorder,'classid_','classid');
		/*
		$myclass = explode(',',$myorder);
		$updatenum = 0;
		foreach ($myclass as $v){
			if ($v){
				$temp = explode('=',$v);
				$theid = (int)substr($temp[0],8);
				$neworder = (int)$temp[1];
				if ($theid > 0){
					if (!$db)$db = new Database;
					$updatenum += count($db -> update ('[class]',array('myorder' => $neworder),array('classid'=>$theid)));
				}
			}
		}
		*/
		//更新全部栏目缓存
		$adminmodel -> renew_classcatch();

		MyqeeCMS::show_info(Myqee::lang('admin/class.info.editmyorderok',$updatenum),true,'refresh');
	}
	
	public function navigation(){
		Passport::checkallow('class.navigation');
		$siteid = $_SESSION['now_site'];
		$arguments = $arguments = explode('/',$_GET['path']);
		$view = new View('admin/class_nav_list');
		$nav_list = MyqeeCMS::config('navigation'.($siteid?'/site_'.$siteid:''));
		if ($arguments){
			foreach ($arguments as $arg){
				if (!empty($arg)){
					if (!$nav_list[$arg]){
						MyqeeCMS::show_error('不存在此父菜单标识',false,'goback');
						break;
					}
					$nav_list = $nav_list[$arg]['submenu'];
				}
			}
		}else{
			
		}
		
		/*
		$adminmodel = new Admin_Model();
		if (!($b_classid>=0))$b_classid = 0;
		$class_list =  $adminmodel -> get_allclass_array($b_classid,2);

		if (is_array($nav_list)){
			foreach ($nav_list as $key => $value){
				if ($value['classid'] && $class_list[$value['classid']]){
					unset($class_list[$value['classid']]);
				}
			}
		}
		//MyqeeCMS::print_r($class_list);
		if (count($class_list)>0){
			$nav_list_tmp = $this -> _get_nav_classurl($class_list,Myqee::config('core.mysite_url'));
			$nav_list = array_merge($nav_list,$nav_list_tmp);
		}
		*/
		
		$view -> set('list',$nav_list);
		$view -> set('nav_path' , join('/',$arguments));
		array_pop($arguments);
		$view -> set('nav_parentpath' , join('/',$arguments));
		$view -> render(TRUE);
	}
	
	protected function _get_nav_classurl($class_list,$myurl){
		foreach ($class_list as $value){
			if ($value['isnavshow']){
				$nav_list['class_'.$value['classid']] = array(
					'myorder' => $value['myorder'],
					'classid' =>$value['classid'],
					'name' => $value['classname'],
					'url' => $value['hostname']?'http://'.$value['hostname'].'/':$myurl.($value['isnothtml']==0 && !empty($value['classpath'])?$value['classpath'].'/':'myclass/'.$value['classid'].'/'),
				);
				if ($value['sonclassarray']){
					$nav_list['class_'.$value['classid']]['submenu'] = $this -> _get_nav_classurl($value['sonclassarray'],$myurl);
				}
			}
		}
		return $nav_list;
	}
	
	public function nav_save(){
		Passport::checkallow('class.navigation','',true);
		$siteid = $_SESSION['now_site'];
		$nav_list = (array)MyqeeCMS::config('navigation'.($siteid?'/site_'.$siteid:''));
		//MyqeeCMS::print_r($_POST);
		//生成完整的菜单结构
		/*
		if (is_array($nav_list)){
			$adminmodel = new Admin_Model();
			$class_list =  $adminmodel -> get_allclass_array(0);
			foreach ($nav_list as $key => $value){
				if ($value['classid'] && $class_list[$value['classid']]){
					unset($class_list[$value['classid']]);
				}
			}
		}else{
			$nav_list = array();
		}
		if ( count($class_list)>0 ){
			$nav_list_tmp = $this -> _get_nav_classurl( $class_list,Myqee::config('core.mysite_url') );
			$nav_list = array_merge($nav_list,$nav_list_tmp);
		}
		*/
		
		$nav_list2 = $nav_list;		//复制一个用来处理
		$thepath = trim($_GET['path'],'/ ');
		if (!empty($thepath)){
			$arguments = explode('/',$thepath);
		}else{
			$arguments = array();
		}
		if ($arg_count = count($arguments)){
			for ($i=0;$i<$arg_count;$i++){
				if (!$nav_list2[$arguments[$i]]){
					MyqeeCMS::show_error('不存在此父菜单标识',true,'');
					break;
				}
				$nav_list2 = $nav_list2[$arguments[$i]]['submenu'];
			}
		}
		
		//处理提交来的数据
		$newlist = (array)$_POST['data'];
		$newlist_count = count($newlist['menukey']);
		$mynew_nav = array();
		foreach ($newlist as $value){
			if ($value['oldkey']!=$value['newkey']){
				//KEY发生变化
				if ( empty($value['newkey']) || $value['newkey']>0 || !preg_match("/^[a-zA-Z][a-zA-Z0-9_]*$/",$value['newkey'])){
					MyqeeCMS::show_error('抱歉，新标识“'.$value['newkey'].'”不符合要求，\n\n标识不能空，且不能含有非法字符，且不能为纯数字，且以字母开头\n\n请重新输入！',true,'');
				}
				if ($mynew_nav[$value['newkey']]){
					MyqeeCMS::show_error('存在相同的菜单标识',true,'');
				}
			}
			//放到一个临时变量里
			$tmp_nav = array(
				'myorder' => (int)$value['myorder'],
				'classid' => $nav_list2[$value['oldkey']]['classid'],
				'name' => trim($value['name']),
				'url' => str_replace('"','',trim($value['url'])),
				'target' => preg_replace("/[^a-zA-Z0-9_]+/",'',$value['target']=='[other]'?$value['target2']:$value['target']),
				'submenu' => $nav_list2[$value['oldkey']]['submenu'],
			);
			if (empty($value['newkey']) || empty($tmp_nav['name']) || empty($tmp_nav['url'])){
				if (!($tmp_nav['classid']>0)){
					if (isset($value['oldkey']) || (!empty($value['newkey']) || !empty($tmp_nav['name']) || !empty($tmp_nav['url']))) {
						MyqeeCMS::show_error('抱歉，自定义栏目的“标识”、“菜单名称”和“链接地址”不能空！',true,'');
					}
					continue;
				}
			}
			if ($tmp_nav['classid']>0 && $tmp_nav['myorder']!=$nav_list2[$value['oldkey']]['myorder']){
				//栏目的排序发生变化，须更新到数据库
				$this -> db or $this -> db = Database::instance();
				$this -> db -> update('[class]',array('myorder'=>$tmp_nav['myorder']),array('classid'=>$tmp_nav['classid']));
			}
			$mynew_nav[$value['newkey']] = $tmp_nav;
			
		}
		$nav_list = $this -> _set_nav_array($nav_list,$mynew_nav,$arguments);
		
		$adminmodel or $adminmodel = new Admin_Model();
		
		//MyqeeCMS::print_r($nav_list);
		if ($adminmodel ->update_nav_array($nav_list)){
			MyqeeCMS::show_info('恭喜，保存成功！','true','refresh');
		}else{
			MyqeeCMS::show_error('抱歉，保存失败，可能是没有写入文件的权限！','true');
		}
	}
	
	protected function _set_nav_array($nav_list,$mynew_nav,$arguments){
		$new_nav = array();
		$newarr = $arguments;
		$update = false;
		if (is_array($newarr)){
			if (count($newarr)===0){
				$update = true;
			}else{
				$thearr = array_shift($newarr);
			}
		}else{
			$newarr = null;
		}
		if ($update==true){
			if (is_array($nav_list)){
				//这里主要是操作删除栏目
				foreach ($nav_list as $key => $value){
					if ( $value['classid'] && !$mynew_nav[$key] ){
						$this -> db or $this -> db = Database::instance();
						$this -> db -> update('[class]',array('isnavshow'=>0),array('classid'=>$value['classid']));
					}
				}
			}
			$new_nav = $mynew_nav;
		}else{
			if (!is_array($nav_list))return ;
			foreach ($nav_list as $key => $value){
				$new_nav[$key] = $value;
				if ( $thearr == $key ){
					$new_nav[$key]['submenu'] = $this -> _set_nav_array($new_nav[$key]['submenu'],$mynew_nav,$newarr);
				}
			}
		}
		
		if ($update==true)asort($new_nav);	//对当更新的数据重新排序
		return $new_nav;
	}
	
	public function set($sclassid = 0){

		$view = new View('admin/class_set');
		$adminmodel = new Admin_Model();
		$view -> set ('classtree' , $allclass_array );		//class array
		$view -> set ('models' , $adminmodel -> get_model_for_dropdown() );					//models array
		$view -> set ('classtree' , $adminmodel -> get_allclass_array('classid,classname,bclassid,classpath,hits,myorder') );
		$view -> set ('cover_tplarray' , $adminmodel -> get_alltemplate('cover') );			//cover template
		$view -> set ('list_tplarray' , $adminmodel -> get_alltemplate('list') );			//list template
		$view -> set ('content_tplarray' , $adminmodel -> get_alltemplate('content') );		//content template
		$view -> set ('search_tplarray' , $adminmodel -> get_alltemplate('search') );		//search template

		$view -> render(TRUE);
	}
	
	public function psave(){
		$adminmodel = new Admin_Model();
		$post = Input::instance()->post('class');
		$classid_array = (array)$_POST['classid'];

		$stutas = 0;
		$allclass = array();
		if (in_array(0,$classid_array)) {
			//获取所有栏目
			$allclass = $adminmodel -> db ->from ( '[class]' )-> orderby ( 'classid', 'asc' ) ->get ()->result_array ( FALSE );
		}else {
			$allclass = $adminmodel -> db ->from ( '[class]' ) ->in('classid',$classid_array) -> orderby ( 'classid', 'asc' ) ->get ()->result_array ( FALSE );
		}
		foreach ($allclass as $item) {
			$item['classpath'] = explode('/',$item['classpath']);
			$item['classpath'] = $item['classpath'][count($item['classpath'])-1];
			$item = array_merge($item,$post);
			$stutas += $adminmodel -> save_edit_class($item);
		}

		if ($stutas){
			MyqeeCMS::show_info(Myqee::lang('admin/class.info.updataset',$stutas),true);
		}else{
			MyqeeCMS::show_info(Myqee::lang('admin/class.info.noupdata'),true);
		}
	}
}



