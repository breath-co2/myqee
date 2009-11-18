<?php
/**
 * $Id: acquisition.php,v 1.17 2009/11/09 03:19:59 songwubin Exp $
 *
 * @package    Acquisition
 * @author     Myqee Team
 * @copyright  (c) 2008-2010 Myqee Team
 * @license    http://Myqee.com/license.html
 */
class Acquisition_Controller_Core extends Controller {
	protected $message = array(
		'REQUEST EXPRIED' 				=> '操作超时，请重新操作！',
		'UNAUTHORIZED'					=> '未授权，请重新登录！',
		'ADMIN UNAUTHORIZED'			=> '您没有操作采集入库的权限！',
		'NO ACQUISITON'					=> '不存在指定任务，可能已被删除！',
		'ACQUISITON NO USE'				=> '指定任务未启用！',
		'NO NODE'						=> '不存在指定节点，可能已被删除！',
		'NODE NO USE'					=> '指定节点未启用！',
		'ERROR QUERY STRING'			=> '错误的查询参数！',
		'ERROR CODE'					=> '校验码错误，请刷新页面或重新登录或联系管理员！',
		'ADMIN UNAUTHORIZED CLASS'		=> '您没有对应栏目的操作权限！',
		'ADMIN UNAUTHORIZED DATABASE'	=> '您没有对应数据表的操作权限！'
	);
	
	function __construct(){
		parent::__construct();
		Passport::chkadmin();
	}
	
	public function index($page=1){
		Passport::checkallow('task.acquisition_list');
		$view = new View('admin/acquisition_list');
		$per = 20;
		if ($page>=1){
			$page = (int)$page;
		}
		
		$db = Database::instance();
		
//		$acquisition = MyqeeCMS::config('acquisition');
		$count = $acquisition = $db -> count_records('[acquisition]');

		$pagination = new Pagination( array(
			'uri_segment'    => 'index',
			'total_items'    => $count,
			'items_per_page' => $per
		) );
		
		
		$acquisition = $db ->orderby('id','DESC')->from('[acquisition]')->limit($per,$pagination -> sql_offset())->get()->result_array(FALSE);
		
//		$offset = $pagination -> sql_offset();
//		if ($offset>0){
//			$acquisition = array_splice( $acquisition, $offset, $per);
//		}
		
		$view -> set('list',$acquisition);
		$view -> set ('page' , $pagination -> render('digg') );
		$view -> render(TRUE);
	}

	
	public function add(){
		Passport::checkallow('task.acquisition_add');
		$this -> edit();
	}
	
	public function copy($id){
		Passport::checkallow('task.acquisition_add');
		if (!($id>0)){
			MyqeeCMS::show_error(Myqee::lang('admin/acquisition.error.nofoundacqu'),false,'goback');
		}
		$this -> _iscopy = true;
		$this -> edit($id);
	}
	
	public function edit($id=0){
		if ( $id > 0){
			$db = Database::instance();
			$data = $db ->from('[acquisition]')->where('id',$id)->get()->result_array(FALSE);
			$data = $data[0];
//			$acquisition = MyqeeCMS::config('acquisition');
//			$data = $acquisition['acqu_'.$id];
			if ( !is_array($data) ){
				MyqeeCMS::show_error(Myqee::lang('admin/acquisition.error.nofoundacqu'),false,'goback');
			}
			$data['post'] = unserialize($data['post']);
		}
		$view = new View('admin/acquisition_add');
		
		if ($this -> _iscopy === true){
			$view -> set('copyacquid',$id);
			unset($data['id'],$id);
		}else{
			if ($id >0 )$view -> set('isedit',true);
		}
		if ($id>0){
			Passport::checkallow('task.acquisition_edit');
		}else{
			Passport::checkallow('task.acquisition_add');	
		}
		
		
		$view -> set('id',$id);
		$view -> set('data',$data);
		
		$adminmodel = new Admin_Model();
		
		$view -> set('class' , $adminmodel -> get_allclass_array());
		$view -> set('model' , $adminmodel -> get_model_for_dropdown('请选择'));
		
		$dbinfo = $adminmodel -> get_dbtable_forselect(true);
		$view -> set('dblist' , $dbinfo['forselect']);
		$view->render(TRUE);
	}
	
	public function save($id=0){
		if ($id>0){
			Passport::checkallow('task.acquisition_edit');
		}else{
			Passport::checkallow('task.acquisition_add');
		}
		$post = $_POST['acqu'];
//		$data['id'] = (int)$id;
		if (!($data['name']= htmlspecialchars($post['name']))){
			MyqeeCMS::show_error(Myqee::lang('admin/acquisition.error.noacquname'),true);
		}
		
		$data['method'] = $post['method'] == 'POST'?'POST':'GET';
		$data['isuse'] = $post['isuse'] == 0?0:1;
		$data['charset'] = strtoupper($post['charset']);
		
		$data['classid'] = (int)$post['classid'];
		if ($data['classid']>0){
			$adminmodel = new Admin_Model();
			if ($classinfo = $adminmodel -> get_class_array($data['classid'],'classname,modelid,dbname') ){
				$data['classname'] = $classinfo['classname'];
				$data['modelid'] = $classinfo['modelid'];
				$data['dbname'] = $classinfo['dbname'];
				if ($data['modelid']>0 && ($modelinfo = $adminmodel -> get_model_array($data['modelid'],'modelname,dbname'))){
					$data['modelname'] = $modelinfo['modelname'];
					$data['dbname'] = $modelinfo['dbname'];
				}
			}else{
				MyqeeCMS::show_error(Myqee::lang('admin/acquisition.error.nofoundclass'),true);
			}
		}elseif ($post['modelid']>0){
			$data['modelid'] = $post['modelid'];
			$adminmodel = new Admin_Model();
			if ($modelinfo = $adminmodel -> get_model_array($post['modelid'],'modelname,dbname')){
				$data['modelname'] = $modelinfo['modelname'];
				$data['dbname'] = $modelinfo['dbname'];
				$data['classname'] = '';
			}else{
				MyqeeCMS::show_error(Myqee::lang('admin/acquisition.error.nofoundmodel'),true);
			}
		} else {
			$data['modelid'] = '';
			$data['modelname'] = '';
			$data['dbname'] = $post['dbname'];
		}
		if (!isset($data['dbname']) || empty($data['dbname'])){
			MyqeeCMS::show_error(Myqee::lang('admin/acquisition.error.nofounddbname'),true);
		}
		
		$acqupost = array(
			'referer' => $post['post']['referer'],
			'accept' => $post['post']['accept'],
			'accept_encoding' => $post['post']['accept_encoding'],
			'user_agent' => $post['post']['user_agent'],
			'cookie' => $post['post']['cookie'],
		);
		$data['post'] = serialize($acqupost);
		
		$data['other_post'] = $post['other_post'];
		$data['islogin'] = $post['islogin'] ==1?1:0;
		$data['loginactionurl'] = $post['loginactionurl'];
		$data['loginpost'] = $post['loginpost'];
		$data['loginimageurl'] = $post['loginimageurl'];
		
//		$acquisition = MyqeeCMS::config('acquisition');
		
		$db = $adminmodel?$adminmodel->db:Database::instance();
		
		if ($id>0){
			$status = $db -> update('[acquisition]',$data,array('id' =>$id));
		}else{
			if ($_POST['copyacqu']>0){
				$node = $db -> select('node') -> from('[acquisition]') -> where('id',$_POST['copyacqu']) -> get() -> result_array(FALSE);
				$node = $node[0]['node'];
				if ($node){
					$data['node'] = $node;
					if($node)$node = unserialize($node);
				}
			}
			$status = $db -> insert('[acquisition]',$data);
			$id = $status->insert_id ();
		}
//		if ($id>0){
//			$data['node'] = $acquisition['acqu_'.$id]['node'];
//			$acquisition['acqu_'.$id] = $data;
//		}else{
//			//添加啦
//			end($acquisition);
//			if ($theid = key($acquisition)){
//				$theid = (int)substr($theid,5);
//			}
//			while (true){
//				$theid ++;
//				if (!$acquisition['acqu_'.$theid]){
//					$data['id'] = $theid;
//					$acquisition['acqu_'.$theid] = $data;
//					break;
//				}
//			}
//		}
		
//		if (MyqeeCMS::saveconfig('acquisition',$acquisition)){
		if ($status){
			//保存配置文件
			if ($id){
				if(!$node)$node = MyqeeCMS::config('acquisition/acqu_'.$id.'.node');
				$data['post'] = $acqupost;
				$data['node'] = $node;
				MyqeeCMS::saveconfig('acquisition/acqu_'.$id,$data);
			}
			MyqeeCMS::show_ok(Myqee::lang('admin/acquisition.info.saveok'),true);
		}else{
			MyqeeCMS::show_error(Myqee::lang('admin/acquisition.info.noupdate'),true);
		}
	}
	public function del($id){
		Passport::checkallow('task.acquisition_del');
		if(!($id>0))MyqeeCMS::show_error(Myqee::lang('admin/acquisition.error.parameterserror'),true);
		
		$db = Database::instance();
		if ($db->delete('[acquisition]',array('id'=>$id))){
			if (MyqeeCMS::delconfig('acquisition/acqu_'.$id)){
				MyqeeCMS::show_info(Myqee::lang('admin/acquisition.info.saveok'),true,'refresh');
			}else{
				MyqeeCMS::show_info(Myqee::lang('admin/acquisition.error.delconfigerror'),true,'refresh');
			}
		}else{
			MyqeeCMS::show_error(Myqee::lang('admin/acquisition.info.nodelete'),true);
		}
//		$acquisition = MyqeeCMS::config('acquisition');
//		
//		if (!$acquisition['acqu_'.$id])MyqeeCMS::show_error(Myqee::lang('admin/acquisition.error.nofoundacqu'),true);
//		
//		unset($acquisition['acqu_'.$id]);
//		
//		if (MyqeeCMS::saveconfig('acquisition',$acquisition)){
//			MyqeeCMS::show_info(Myqee::lang('admin/acquisition.info.saveok'),true,'refresh');
//		}else{
//			MyqeeCMS::show_error(Myqee::lang('admin/acquisition.error.saveerror'),true);
//		}
	}
	
	public function node_list($id=0){
		$id>0 or MyqeeCMS::show_error(Myqee::lang('admin/acquisition.error.parameterserror'),false,'goback');
		
		Passport::checkallow('task.acquisition_list');
		
		$acquisition = Database::instance() -> getwhere('[acquisition]',array(id=>$id)) -> result_array(FALSE);
		$acquisition = $acquisition[0];
		
		if (!is_array($acquisition) ){
			MyqeeCMS::show_error(Myqee::lang('admin/acquisition.error.nofoundacqu'),false,'goback');
		}
		
		$view = new View('admin/acquisition_node_list');
		//排序
		$list = unserialize($acquisition['node']);
		if (empty($list)) {
			$list = array();
		}
		$cmpfunction = create_function('$a,$b','if ($a["mysort"] == $b["mysort"]) { if ($a["id"] == $b["id"]) {return 0;}else {return ($a["id"] < $b["id"]) ? -1 : 1;}} else {return ($a["mysort"] < $b["mysort"]) ? -1 : 1;}');	
		uasort($list,$cmpfunction);
		$view -> set('list', $list);
		$view -> set('acquisition_id', $id );
		$view -> set('acquisition_name', $acquisition['name'] );
		$view->render(TRUE);
	}
	
	public function node_add($id){
		$this -> node_edit($id);
	}
	
	public function node_copy($id,$node_id = 0){
		$this -> _iscopy = true;
		$this -> node_edit($id,$node_id);
	}
	
	public function node_edit($id , $node_id = 0){
		if ($node_id>0 && !$this->_iscopy){
			Passport::checkallow('task.acquisition_edit');
		}else{
			Passport::checkallow('task.acquisition_add');
		}
		if (!($id>0)){
			MyqeeCMS::show_error(Myqee::lang('admin/acquisition.error.nofoundacqu'),false,'goback');
		}
		$acquisition = Database::instance() -> getwhere('[acquisition]',array(id=>$id)) -> result_array(FALSE);
		$acquisition = $acquisition[0];
		if (!is_array($acquisition) ){
			MyqeeCMS::show_error(Myqee::lang('admin/acquisition.error.nofoundacqu'),false,'goback');
		}
		$acquisition['node'] = unserialize($acquisition['node']);
		$view = new View('admin/acquisition_node_add');
		$view -> set('acquisition_id', $id );
		$view -> set('acquisition_name', $acquisition['name'] );
		
		if ($node_id>0){
			$acqu_node = $acquisition['node']['node_'.$node_id];
			//处理textarea &nbsp;变空格的问题,str_replace的作用是对保存时把换行符替换正则的恢复，不替换也可以
			//只是方便用户浏览，让用户看到的还是自己原来填写的模式
			foreach ((array)$acqu_node['urls'] as $key => $val) {
				$val['preg'] = str_replace('[\r\n]+',"\n",htmlspecialchars($val['preg']));
				$val['replace'] = htmlspecialchars($val['replace']);
				$acqu_node['urls'][$key] = $val;
			}
			foreach ((array)$acqu_node['acqu'] as $key => $val) {
				$val['preg'] = str_replace('[\r\n]+',"\n",htmlspecialchars($val['preg']));
				$val['replace'] = htmlspecialchars($val['replace']);
				$acqu_node['acqu'][$key] = $val;
			}
			foreach ((array)$acqu_node['file'] as $key => $val) {
				$val['preg'] = str_replace('[\r\n]+',"\n",htmlspecialchars($val['preg']));
				$val['replace'] = htmlspecialchars($val['replace']);
				$acqu_node['file'][$key] = $val;
			}
			$acqu_node['the_id_name']['preg'] = str_replace('[\r\n]+',"\n",htmlspecialchars($acqu_node['the_id_name']['preg']));
			
			if($this->_iscopy){
				unset($acqu_node['key']);
				$acqu_node['name'] = 'copy_'.$acqu_node['name'];
			}else{
				$view -> set('node_id', $node_id );
			}
			$view -> set('data', $acqu_node );
			unset($acquisition['node']['node_'.$node_id]);
		} else {
			//获取最大KEY,防止插入节点是重复提交
			$node_id = $this->_get_next_nodeid($acquisition);
			$view -> set('node_id', $node_id );
		}
		$otheracqu = $otheracqu_forurl = array(''=>'请选择');
		if (is_array($acquisition['node']) && count($acquisition['node'])>0){
			foreach ($acquisition['node'] as $value){
				if (count($value['urls'])){
					if (count($value['urls'])){
						foreach ($value['urls'] as $k=>$v){
							$otheracqu_forurl[$value['name']][$value['id'].'|'.$k] = $v['name'];
						}
					}
				}
				$otheracqu[$value['id']] = $value['name'];
			}
		}
		unset($acquisition['node']);
		$view -> set('acquisition', $acquisition );
		$view -> set('otheracqu_forurl', $otheracqu_forurl );
		$view -> set('otheracqu', $otheracqu );
		
		
		$adminmodel = new Admin_Model();
		if ($acqu_node['dbname']){
			$classid = $acqu_node['classid'];
			$modelid = $acqu_node['modelid'];
			$dbname = $acqu_node['dbname'];
		}else{
			$classid = $acquisition['classid'];
			$modelid = $acquisition['modelid'];
			$dbname = $acquisition['dbname'];
		}
		if ($classid>0){
			//读取栏目配置
			$classinfo = $adminmodel -> get_class_array($classid,'classid,modelid,dbname');
			$acquisition['modelid'] = $classinfo['modelid'];
			$acquisition['dbname'] = $classinfo['dbname'];
			$modelconfig = MyqeeCMS::config('model/model_'.$classinfo['modelid']);
		} elseif ($modelid>0){
			//读取模型配置
			$modelconfig = MyqeeCMS::config('model/model_'.$modelid);
			if (is_array($modelconfig) && $modelconfig['dbname']){
				$acquisition['dbname'] = $modelconfig['dbname'];
			}
		}
		if ($dbname){
			//读取数据表配置
			$db_info = $adminmodel -> get_db_array( $dbname , false);
			$db_info = $db_info[0];
		}
		if (!$modelconfig && $db_info['usedbmodel']){
			$modelconfig = (array)unserialize($db_info['modelconfig']);
		}
		if (empty($modelconfig)) {
			MyqeeCMS::show_error(Myqee::lang('admin/acquisition.error.nofounddbname'));
		}
		
		list($database,$tablename) = explode('/',$dbname);
		$_db = Database::instance($database);
		if (!$_db ->table_exists($tablename)) {
			MyqeeCMS::show_error(Myqee::lang('admin/acquisition.error.nofounddbname'),false,'goback');
		}
		$fieldlist = (array)$_db -> list_fields($tablename,true);	//读取数据表字段
		$field = array();
		if (count($modelconfig['field'])){
			foreach ($modelconfig['field'] as $key => $value){
				if (substr($key,0,1) == '_') {
					//扩展表
					$_db_config = MyqeeCMS::config('db/'.substr($key,1));
					list ($_database,$_tablename) = explode('/',substr($key,1));
					if (is_array($_db_config['model']['field'])) foreach ($_db_config['model']['field'] as $k=>$v) {
						if (substr($k,0,1) == '_' || $k == 'id') {
							continue;
						}
						$field[$_database.'/'.$_tablename.'.'.$k] = $v['dbname'].'('.$k.')';
					}
				}
				elseif ($fieldlist[$key]){
					$field[$key] = $modelconfig['field'][$key]['dbname'].'('.$key.')';
				}
			}
		}
		
		$apilist = $adminmodel -> get_apiclass_list('Acquisition_Api');
		$view->set ( 'apilist', $apilist );
		
		$view -> set('field',Tools::json_encode($field));
		$view->render(TRUE);
	}
	
	public function node_save($id,$node_id=0){
		if ($node_id>0){
			Passport::checkallow('task.acquisition_edit');
		}else{
			Passport::checkallow('task.acquisition_add');
			$node_id = 0;
		}
		
		$db = Database::instance();
		$acqudata = $db ->select('node') ->from('[acquisition]')->where('id',$id)->get()->result_array(FALSE);
		$acqudata = $acqudata[0];
		if (!$acqudata){
			MyqeeCMS::show_error(Myqee::lang('admin/acquisition.error.nofoundacqu'),TRUE);
		}
		
		$post = $_POST['acqu_node'];
		
		$post['name'] = trim($post['name']);
		if (empty($post['name'])){
			MyqeeCMS::show_error('节点名称不能空！',TRUE);
		}
		
		$data = array(
			'myorder' => (int)$post['myorder'],							//排序
			'name' => $post['name'],									//名称
			'id' => (int)$node_id,										//ID
			'key' => $post['key']?$post['key']:MyqeeCMS::getRand(30),	//key
			'isuse' => $post['isuse']?1:0,								//是否启用
			'openurltimeout' => (int)$post['openurltimeout']>=0?
								(int)$post['openurltimeout']:60,		//被打开网站超时时间
			'method' => $post['method']=='POST'?'POST':'GET',			//提交方式
			'limitpage' => (int)$post['limitpage'],						//每组采集页面数
			'limittime' => (int)$post['limittime'],						//每组采集时间间隔
			'donum' => (int)$post['donum'],								//组完成后操作
			'dotype' => $post['dotype'],								//组执行完成后主机箱方式
			'donext_node' => $post['donext_node'],						//当执行方式是1时，组执行完成后执行下一个的标签
			'doalltype' => $post['doalltype'],							//任务全部完成后操作
			'doallnext_node' => $post['doallnext_node'],				//任务全部完成后执行任务的ID
			'postdata' => $post['postdata'],							//提交参数
			'reacquurl' => $post['reacquurl']?1:0,						//是否重复采集同一链接
			'urltype' => $post['urltype'],								//采集地址方式
			'is_autotohtml' => $post['is_autotohtml']?1:0,				//入库后自动发布
			'tohtml_errortype' => $post['tohtml_errortype'],			//入库时缺少信息时操作方式
			'tohtml_autonum' => $post['tohtml_autonum']>=0?
								$post['tohtml_autonum']:50,				//自动入库单次入库数据量
			'tohtml_limitnum' => $post['tohtml_limitnum']>=0?
								$post['tohtml_limitnum']:100,			//自动入库单次入库数据量
		);
		
		//////////过滤
		$filter = $_POST['filter'];
		if (is_array($filter)){
			$myfilter = array();
			$filter_id_array = array();			//记录原始filter ID
			foreach ($filter as $value){
				$value['name'] = trim($value['name']);
				if (empty($value['name'])){
					MyqeeCMS::show_error('过滤设置：“'.$value['key'].'”名称不能空！',TRUE);
				}
			
				if (!preg_match("/^[a-z][a-z0-9_]+$/i",$value['key'])){
					MyqeeCMS::show_error('过滤设置：“'.$value['name'].'”标识不符合要求！',TRUE);
				}
				
				if (empty($value['preg'])){
					MyqeeCMS::show_error('过滤设置：“'.$value['name'].'”匹配规则不能空！',TRUE);
				}
				if ($myfilter[$value['key']]){
					MyqeeCMS::show_error('过滤设置：“'.$value['name'].'”标识重复，请修改！',TRUE);
				}
				$myfilter[$value['key']] = $value;
				
				$filter_id_array[] = $value['key'];
			}
			
			//校验“内容匹配范围”
//			if (count($this ->myfilter)){
				$i=0;
				$this ->myfilter = $myfilter;			//记录所有过滤项
				$this ->tmpdoid = $filter_id_array;			//记录临时doid
				foreach ($this ->myfilter as $key => $value){
					if ($value['doid']>=0){
						$this -> _chkfilterinfoid($i,$value);
						$myfilter[$key]['doid'] = $filter_id_array[$value['doid']];
					}
					$i++;
				}
//			}
			
			$data['filter'] = $myfilter;
		}
	
		//采集地址方式
		if ($data['urltype']==0 || !($data['urltype']>0 && $data['urltype']<=3)){
			//一组固定的地址列表
			$post['theurl0'] = trim($post['theurl0']," \n\r");
			$post['theurl0'] = preg_replace("/(\r|\n)+/Uis","\n",$post['theurl0'],$post['theurl0']);
			$data['theurl0'] = $post['theurl0'];
		}elseif($data['urltype']==1){
			//根据当前采集页面分析下一页面地址
			$post['theurl1']['next'] = trim($post['theurl1']['next']);
			if (empty($post['theurl1']['next'])){
				MyqeeCMS::show_error('采集地址的“下一地址规则”不能空！',TRUE);
			}
			$post['theurl1']['tourl'] = trim($post['theurl1']['tourl']);
			if (empty($post['theurl1']['tourl'])){
				MyqeeCMS::show_error('采集地址的“将下一地址规则结果转换为需要的结果”不能空！',TRUE);
			}
			
			if ($post['theurl1']['filter']!='-1'){
				if (!$filter_id_array[$post['theurl1']['filter']]){
					MyqeeCMS::show_error('采集地址的“匹配范围”不存在！',TRUE);
				}else{
					$post['theurl1']['filter'] = $filter_id_array[$post['theurl1']['filter']];
				}
			}else{
				$post['theurl1']['filter'] = '-1';
			}
			
			$data['theurl1'] = array(
				'url' => trim($post['theurl1']['url']),
				'filter' => $post['theurl1']['filter'],
				'next' => $post['theurl1']['next'],
				'tourl' => $post['theurl1']['tourl']
			);
		}elseif($data['urltype']==2){
			//有规律的页面地址
			if (empty($post['theurl2']['replace'])){
				MyqeeCMS::show_error('采集地址的“替换变量”不能空！',TRUE);
			}
			$data['theurl2'] = array(
				'url' => $post['theurl2']['url'],
				'replace' => $post['theurl2']['replace'],
				'begin' => (int)$post['theurl2']['begin'],
				'end' => (int)$post['theurl2']['end'],
				'limit' => (int)$post['theurl2']['limit'],
				'makeup' => $post['theurl2']['makeup']==1?1:0,
				'makeupnum' => (int)$post['theurl2']['makeupnum'],
				'makeupstr' => $post['theurl2']['makeupstr'],
				'reverse' => $post['theurl2']['reverse']?1:0,
			);
		}elseif($data['urltype']==3){
			//调用其它节点输出的地址
			if (!$post['theurl3']){
				MyqeeCMS::show_error('采集地址的“采集地址设置”不能空！',TRUE);
			}
			$url3 = explode('|',$post['theurl3'],2);
			if (!$url3[0]>0){
				MyqeeCMS::show_error('“采集地址设置”设置错误！',TRUE);
				exit;
			}
			$data['theurl3'] = array(
				'id' => (int)$url3[0],
				'nodeid' => $url3[1],
			);
		}
		
		//唯一标识
		if ($post['the_id_name']['string']>=0){
			if (!isset($filter_id_array[$post['the_id_name']['string']])){
				MyqeeCMS::show_error('入库设置：“唯一标识”所选过滤选择不存在！',TRUE);
			}else{
				$data['the_id_name']['string'] = $filter_id_array[$post['the_id_name']['string']];
			}
		}elseif($post['the_id_name']['string']=='-2'){
			$data['the_id_name']['string'] = '-2';
		}else{
			$data['the_id_name']['string'] = '-1';
		}
		//处理正则表达式中的换行符问题，unix和windows 有别
		$post['the_id_name']['preg'] = preg_replace(array("#^[\r\n]+#","#[\r\n]+$#","#[\r\n]+#"),array('','','[\r\n]+'),$post['the_id_name']['preg']);
		$data['the_id_name']['preg'] = $post['the_id_name']['preg'];
		$data['the_id_name']['replace'] = $post['the_id_name']['replace'];
		
		////////匹配
		$acqu = $_POST['acqu'];
		if (is_array($acqu)){
			$myacqu = array();
			foreach ($acqu as $value){
				if ($myacqu[$value['key']]){
					MyqeeCMS::show_error('采集规则：“'.$value['key'].'”存在重复标识名！',TRUE);
				}
				if ($value['doinfo']>=0){
					if (!isset($filter_id_array[$value['doinfo']])){
						MyqeeCMS::show_error('采集规则：“'.$value['name'].'”设置了不存在的过滤标识！',TRUE);
					}else{
						$value['doinfo'] = $filter_id_array[$value['doinfo']];
					}
				}
				//处理正则表达式中的换行符问题，unix和windows 有别
				$value['preg'] = preg_replace(array("#^[\r\n]+#","#[\r\n]+$#","#[\r\n]+#"),array('','','[\r\n]+'),$value['preg']);
				if (empty($value['preg']) || empty($value['replace'])){
					MyqeeCMS::show_error('采集规则：“'.$value['name'].'”匹配规则和转换内容都不能空！',TRUE);
				}
				
				$myacqu[$value['key']] = array(
					'key' => $value['key'],
					'name' => $value['name'],
					'isnotnull' => $value['isnotnull']?1:0,
					'dbfield' => $value['dbfield'],
					'infotype' => $value['infotype'],
					'doinfo' => $value['doinfo'],
					'preg' => $value['preg'],
					'replace' => $value['replace'],
				);
			}
			$data['acqu'] = $myacqu;
		}
		
		
		////////输出采集地址
		$urls = $_POST['urls'];
		if (is_array($urls)){
			$myurls = array();
			foreach ($urls as $value){
				if ($myurls[$value['key']]){
					MyqeeCMS::show_error('输出采集地址：“'.$value['key'].'”存在重复标识名！',TRUE);
				}
				if ($value['doinfo']>=0){
					if (!isset($filter_id_array[$value['doinfo']])){
						MyqeeCMS::show_error('输出采集地址：“'.$value['name'].'”设置了不存在的过滤标识！',TRUE);
					}else{
						$value['doinfo'] = $filter_id_array[$value['doinfo']];
					}
				}
				//处理正则表达式中的换行符问题，unix和windows 有别
				$value['preg'] = preg_replace(array("#^[\r\n]+#","#[\r\n]+$#","#[\r\n]+#"),array('','','[\r\n]+'),$value['preg']);
				if (empty($value['preg']) || empty($value['replace'])){
					MyqeeCMS::show_error('输出采集地址：“'.$value['name'].'”匹配规则和转换内容都不能空！',TRUE);
				}
				
				$myurls[$value['key']] = array(
					'key' => $value['key'],
					'name' => $value['name'],
					'infotype' => $value['infotype'],
					'doinfo' => $value['doinfo'],
					'preg' => $value['preg'],
					'replace' => $value['replace'],
				);
			}
			$data['urls'] = $myurls;
		}
	
		////////采集附件
		$file = $_POST['file'];
		if (is_array($file)){
			$myfile = array();
			foreach ($file as $value){
				if ($myfile[$value['key']]){
					MyqeeCMS::show_error('采集附件：“'.$value['key'].'”存在重复标识名！',TRUE);
				}
				if ($value['doinfo']>=0){
					if (!isset($filter_id_array[$value['doinfo']])){
						MyqeeCMS::show_error('采集附件：“'.$value['name'].'”设置了不存在的过滤标识！',TRUE);
					}else{
						$value['doinfo'] = $filter_id_array[$value['doinfo']];
					}
				}
				//处理正则表达式中的换行符问题，unix和windows 有别
				$value['preg'] = preg_replace(array("#^[\r\n]+#","#[\r\n]+$#","#[\r\n]+#"),array('','','[\r\n]+'),$value['preg']);
				if (empty($value['preg']) || empty($value['replace'])){
					MyqeeCMS::show_error('采集附件：“'.$value['name'].'”匹配规则和转换内容都不能空！',TRUE);
				}
				
				$myfile[$value['key']] = array(
					'key' => $value['key'],
					'name' => $value['name'],
					'isnotnull' => $value['iswatermark']?1:0,
					'doinfo' => $value['doinfo'],
					'preg' => $value['preg'],
					'replace' => $value['replace'],
				);
			}
			$data['file'] = $myfile;
		}
		
		$acqudata['node'] = unserialize($acqudata['node']);
		if ($node_id>0){
			//修改
			$acqudata['node']['node_'.$node_id] = $data;
		}else{
			//获取最大KEY
			$node_id = $this->_get_next_nodeid($acqudata);
			$data['id'] = $node_id;
			$acqudata['node']['node_'.$node_id] = $data;
		}
		
		//对数组进行重新排序
		arr::asort($acqudata['node']);
		
		$acqudata_node = $acqudata['node'];
		$acqudata['node'] = serialize($acqudata['node']);
		
		$docount = $db -> update('[acquisition]',$acqudata,array('id'=>$id)) -> count();
		
		if ($docount>0){
			$acqu = MyqeeCMS::config('acquisition/acqu_'.$id);
			$acqu['node'] = $acqudata_node;
			MyqeeCMS::saveconfig('acquisition/acqu_'.$id,$acqu);
			
			MyqeeCMS::show_ok('恭喜，保存成功！',TRUE);
		}else{
			MyqeeCMS::show_info('未修改任何数据！',TRUE);
		}
	}
	
	public function node_del($id=0,$node_id=0){
		$id = (int)$id;
		$node_id = (int)$node_id;
		if (!$id>0 || !$node_id>0)MyqeeCMS::show_error('缺少参数',TRUE);
		$this->db = Database::instance ();
		$data = $this->db->getwhere ( '[acquisition]', array ('id' => $id ) )->result_array ( FALSE );
		$data = $data [0];
		
		if (! $data) {
			MyqeeCMS::show_error ( '不存在指定的任务，可能已被删除！', true );
		}
		
		$data['node'] = unserialize($data['node']);
		if (!$data['node']['node_'.$node_id]){
			MyqeeCMS::show_error ( '不存在指定的节点，可能已删除！', true );
		}
		unset($data['node']['node_'.$node_id]);
		$update = array('node'=>serialize($data['node']));
		
		if ( $this->db->update('[acquisition]' , $update, array ('id' => $id )) -> count() ) {
			$acqu = MyqeeCMS::config('acquisition/acqu_'.$id);
			$acqu['node'] = $data['node'];
			$acqu = MyqeeCMS::saveconfig('acquisition/acqu_'.$id,$acqu);
			MyqeeCMS::show_ok ( '恭喜，删除节点成功！', true, 'refresh' );
		} else {
			MyqeeCMS::show_info ( '未删除任何数据！' , true );
		}
	}
	
	
	public function node_order($id=0){
		$id = (int)$id;
		$node_id = (int)$node_id;
		$order = $_GET['order'];
		if (!$id>0||!$order)MyqeeCMS::show_error('缺少参数',TRUE);
		
		$this->db = Database::instance ();
		$data = $this->db->select('node')->getwhere ( '[acquisition]', array ('id' => $id ) )->result_array ( FALSE );
		$data = $data [0];
		
		if (! $data) {
			MyqeeCMS::show_error ( '不存在指定的任务，可能已被删除！', true );
		}
		
		$data['node'] = unserialize($data['node']);
		
		$myorder = explode(',',$order);
		foreach ($myorder as $item){
			list($k,$v) = explode('=',$item,2);
			if (isset($data['node'][$k])){
				$data['node'][$k]['myorder'] = (int)$v;
			}
		}
		
		arr::asort($data['node']);
		
		//更新到数据库
		$count = $this->db -> update('[acquisition]', array('node'=>serialize($data['node']) ), array ('id' => $id )  ) -> count();
		
		if ($count){
			MyqeeCMS::show_ok('恭喜，排序保存成功！',true,'refresh');
		}else{
			MyqeeCMS::show_info('排序没有发生变化！',true);
		}
	}
	
	
	public function info_list($id=0,$node_id=0){
		Passport::checkallow('task.acquisition_list');
		if (!$id>0)MyqeeCMS::show_error('缺少参数',false,'goback');
		$id = (int)$id;
		$node_id = (int)$node_id;
		
		$where = array ('acqu_id' => $id);
		if ($node_id>0){
			$where['node_id']=$node_id;
		}
		$acqu = MyqeeCMS::config('acquisition/acqu_'.$id);
		if (!$acqu){
			MyqeeCMS::show_info ( '不存在指定任务！' , false ,'goback' );
		}
		if ($node_id>0 && !($node = $acqu['node']['node_'.$node_id])){
			MyqeeCMS::show_info ( '不存在指定任务的节点！' , false ,'goback' );
		}
		
		$view = new View('admin/acquisition_info_list');
		
		$this -> db = Database::instance ();
		
		$count = $acquisition = $this -> db -> where($where) -> count_records('[acquisition_data]');
		
		$per =20;
		$pagination = new Pagination(
			array(
				'query_string'    => 'page',
				'total_items'    => $count,
				'items_per_page' => $per
			)
		);
		
		$list = $this-> db
					 -> select('id,title,info_url,is_todb,urlread_time,dbname,class_id,model_id,dotime,node_id')
					 -> limit( $per,$pagination->sql_offset() )
					 -> orderby('id','DESC')
					 -> getwhere('[acquisition_data]',$where)
					 -> result_array(FALSE);
		
		$view -> set('acqu_id',$id);
		$view -> set('acqu_name',$acqu['name']);
		$view -> set('list',$list);
		$view -> set ('page' , $pagination -> render('digg') );
		
		if ($node){
			$view -> set('node_id',$node['id']);
			$view -> set('node_name',$node['name']);
		}
		$view -> render(TRUE);
	}
	
	
	public function info_view($id=0){
		if (!($id>=1)){
			MyqeeCMS::show_info ( '缺少参数！' , false ,'goback' );
		}
		$id = (int)$id;
		
		$this-> db = new Database;
		$data = $this-> db
					 -> getwhere('[acquisition_data]',array('id'=>$id))
					 -> result_array(FALSE);
		$data = $data[0];
		if (!$data){
			MyqeeCMS::show_info ( '不存在指定采集数据！' , false ,'goback' );
		}
		
		$acqu = MyqeeCMS::config('acquisition/acqu_'.$data['acqu_id']);
		
		$view = new View('admin/acquisition_info_view');
		$view -> set('data_id',$data['id']);
		$view -> set('acqu_id',$data['acqu_id']);
		$view -> set('node_id',$data['node_id']);
		$view -> set('dotime',$data['dotime']);
		$view -> set('info',unserialize($data['info_content']));
		$view -> render(TRUE);
	}
	
	public function info_del($id=0,$node_id=0,$info_id=0){
		$id = (int)$id;
		$node_id = (int)$node_id;
		if (strpos($info_id,',')!==false){
			$info_id = Tools::formatids($info_id);
			$type = 'in';
		}else{
			$info_id = (int)$info_id;
			$type = 'where';
		}
		$where = array('acqu_id'=>$id);
		if ($node_id>0){
			$where['node_id']=$node_id;
		}
		
		if (!$id>0){
			MyqeeCMS::show_error('缺少参数！',true);
		}
		$db = new Database();
		
		if ($info_id)$db = $db -> $type('id',$info_id);
		$db = $db -> delete('[acquisition_data]',$where) -> count();
		
		if ($db){
			MyqeeCMS::show_ok('成功删除 '.$db.' 条数据！',true);
		}else{
			MyqeeCMS::show_info('未删除任何数据！',true);
		}
	}
	
	
	public function info_todb($id=0,$node_id=0,$data_ids=0){
		$id = (int)$id;
		$node_id = (int)$node_id;
		if (!$id>0 || !$node_id>0){
			MyqeeCMS::show_info ( '缺少参数！' , true );
		}
		
		$this->adminid = $_SESSION['admin']['id'];
		$acqu = MyqeeCMS::config('acquisition/acqu_'.$id);
		if (!$acqu){
			MyqeeCMS::show_info ( '不存在指定任务！' , false ,'goback' );
		}
		if (!($this->node = $acqu['node']['node_'.$node_id])){
			MyqeeCMS::show_info ( '不存在指定任务的节点！' , true );
		}
		
		$post = array();
		
		if ($data_ids){
			$post['dataids'] = Tools::formatids($data_ids,true);
			$keycode = md5(MyqeeCMS::config('encryption.default.key').'__'.$post['dataids']);
		}else{
			$post['id'] = $id;
			$post['nodeid'] = $node_id;
			
			$keycode = $this -> node['key'];
		}
		
		$post['timeline'] = time();
		$post['adminid'] = $this->adminid;
		$post['code'] = $this->_get_url_code($post['timeline'],$keycode);
		$post['dotime'] = $_GET['dotime'];
		
		
		//创建URL
		$url = Myqee::url('acquisition_run/to_dbdata',null,true);
		
		$snoopy = new Snoopy;
		$snoopy -> ip = $_SERVER["SERVER_ADDR"];
		$snoopy -> submit($url,$post);
		
		
		
		$results = $snoopy -> results;
		if ($results=='OK'){
			MyqeeCMS::show_ok('入库程序已成功启动！',true);
		}else if (isset($this->message[$results])){
			MyqeeCMS::show_error($this->message[$results],true);
		}else{
			MyqeeCMS::show_info($results,true);
		}
	}
	
	/**
	 * 获取页面code
	 *
	 * @param string $url 页面地址
	 * @param int $now 时间
	 * @return string md5后的字符串
	 */
	protected function _get_url_code($now,$keycode=null,$url=''){
		return md5($url.($keycode?$keycode:$this->node['key']).'__'.$now.'__'.$this->adminid);
	}
	
	
	protected function _chkfilterinfoid($id,$value){
		if(!$value['doid']>=0)return -1;	//没有设置匹配范围或符合要求
		$doid = $this -> tmpdoid[$id];
		if (!isset($doid))MyqeeCMS::show_error('过滤项“'.$value['name'].'”设置了一个不存在的内容匹配范围！',TRUE);
		
		if ($id==$value['doid'])MyqeeCMS::show_error('过滤项“'.$value['name'].'”内容匹配范围不允许设置自己或子步骤！',TRUE);

		return $this -> _chkfilterinfoid($value['doid'],$this ->myfilter[$doid]);
	}
	
	protected function _check_acqu_node($id=0,$node_id=0){
		if (!$id>0 ||!$node_id>0){
			return Myqee::lang('admin/acquisition.error.parameterserror');
		}
		$this->acqu = MyqeeCMS::config('acquisition/acqu_'.$id);
		if (!$this->acqu){
			return Myqee::lang('admin/acquisition.error.nofoundacqu');
		}
		if (!$this->acqu['isuse']){
			return Myqee::lang('admin/acquisition.error.acqu_nouse');
		}
		
		$this->node = $this->acqu['node']['node_'.$node_id];
		if (!$this->node){
			return Myqee::lang('admin/acquisition.error.nofoundacqu_node');
		}
		if (!$this->node['isuse']){
			return Myqee::lang('admin/acquisition.error.node_nouse');
		}
		$this -> acqu_id = $id;
		$this -> node_id = $node_id;
		
		return true;
	}
	
	public function doit($id=0,$node_id=0){
		if (($msg = $this -> _check_acqu_node($id,$node_id))!==true){
			MyqeeCMS::show_error( $msg,true,'goback');
		}
		
		$view = new View('admin/acquisition_do');
		$view -> set('acquisition_id', $id );
		$view -> set('acquisition_name', $this->acqu['name'] );
		$view -> set('node_id', $node_id );
		$view -> set('data', $this->node );
		
		unset($this->acqu['node']['node_'.$node_id]);
		$otheracqu_forurl = array(''=>'请选择');
		if (is_array($this->acqu['node']) && count($this->acqu['node'])>0){
			foreach ($this->acqu['node'] as $value){
				if (count($value['urls'])){
					if (count($value['urls'])){
						foreach ($value['urls'] as $k=>$v){
							$otheracqu_forurl[$value['name']][$value['id'].'|'.$k] = $v['name'];
						}
					}
				}
			}
		}
		$view -> set('otheracqu_forurl', $otheracqu_forurl );
		
		//$code = md5($node['key'].'__'.$_SERVER['REQUEST_TIME'].'__'.$_SESSION['admin']['id']);
		//$url = Myqee::url('acquisition_run')."?dopage=1&id={$id}&nodeid={$node_id}&timeline=".$_SERVER['REQUEST_TIME']."&code={$code}&adminid={$_SESSION['admin']['id']}";
		
		$view -> render(true);
	}
	
	/**
	 * 执行采集
	 * @return none
	 */
	public function acqu_run($id=0,$node_id=0){
		if (!Passport::getisallow('task.acquisition_run')){
			echo '您没有操作采集入库的权限！';
			exit;
		}
		
		if (($msg = $this -> _check_acqu_node($id,$node_id))!==true){
			echo $msg;
			exit;
		}
		$this -> adminid = $_SESSION['admin']['id'];
		
		$get = null;
		if (isset($_POST['dotype'])){
			if ($_POST['dotype']=='next'||$_POST['dotype']=='del'){
				$get = array('_dotype'=>$_POST['dotype']);
			}
			unset($_POST['dotype']);
		}
		
		echo $this -> _acqu_run($get,$_POST);
	}
	
	/**
	 * 停止节点的采集
	 * @param int $id
	 * @param int $node_id
	 */
	public function node_stop ($id=0,$node_id=0) {
		$id = intval($id);
		$node_id = intval($node_id);
		if (!Passport::getisallow('task.acquisition_run')){
			echo '您没有操作采集入库的权限！';
			exit;
		}
		
		if (($msg = $this -> _check_acqu_node($id,$node_id))!==true){
			echo $msg;
			exit;
		}
		$this -> adminid = $_SESSION['admin']['id'];
		$runfile = MYAPPPATH."data/acqu_run_{$id}_{$node_id}.run";
		$status = @unlink($runfile);
		if ($status) {
			MyqeeCMS::show_ok('停止成功！',true);
		} else {
			MyqeeCMS::show_error('停止失败！请检查是否在运行或手动删除    '.$runfile,true);
		}
	}
	
	public function info_reread($acqu_id=0){
		if (!Passport::getisallow('task.acquisition_run')){
			echo 'ADMIN UNAUTHORIZED';
			exit;
		}
		
		$acqu_id = (int)$acqu_id;
		if (!$acqu_id>0){
			MyqeeCMS::show_error( Myqee::lang('admin/acquisition.error.parameterserror'),true);
		}
		
		$this -> adminid = $_SESSION['admin']['id'];
		
		$this -> db = new Database;
		$info = $this -> db -> getwhere('[acquisition_data]',array('id'=>$acqu_id)) -> result_array(false);
		$info = $info[0];
		if (!$info){
			MyqeeCMS::show_error( Myqee::lang('admin/acquisition.error.nofounddata'),true);
		}
		
		if ( ($msg = $this -> _check_acqu_node($info['acqu_id'],$info['node_id'])) !==true ){
			MyqeeCMS::show_error( $msg,true );
		}
		
		$get = array(
			'dotime' => $info['dotime'],
			'acqu_data_id' => $acqu_id,
			'info_url' => $info['info_url'],
		);
		
		//启动采集程序
		$result = $this -> _acqu_run($get);
		
		if ($result=='OK'){
			MyqeeCMS::show_ok('采集已启动，正在采集！',true);
		}else{
			MyqeeCMS::show_info($result,true);
		}
	}
	
	
	/**
	 * 创建采集进程
	 * @param $get 提交的get参数
	 * @param $post 提交的POST参数
	 * @return string 返回页面输出
	 */
	protected function _acqu_run($get=null,$post=null){
		$myget = array();
		$myget['timeline'] = time();
		$myget['code'] = $this->_get_url_code($myget['timeline'],$this->node['key'],$get['info_url']?$get['info_url']:'');
		$myget['adminid'] = $this -> adminid;
		$myget['id'] = $this -> acqu_id;
		$myget['nodeid'] = $this -> node_id;
		$myget['dopage'] = 1;
		if (is_array($get)){
			//合并项目
			$myget = array_merge($myget,$get);
		}
		//创建URL
		$url = Myqee::url('acquisition_run/index',null,true).'?'.http_build_query($myget,NULL,'&');
		$snoopy = new Snoopy();
		$snoopy -> getresults = false;
		$snoopy -> ip = $_SERVER["SERVER_ADDR"];
		
		if (is_array($post)){
			//将POST的多维数组转换为提交方式
			if ($post){
				$mypost = array();
				foreach ($post as $key => $value){
					if (is_array($value)){
						foreach ($value as $k2 => $v2){
							if (is_array($v2)){
								foreach ($v2 as $k3 => $v3){
									$mypost[$key.'['.$k2.']['.$k3.']'] = $v3;
								}
							}else{
								$mypost[$key.'['.$k2.']'] = $v2;
							}
						}
					}else{
						$mypost[$key] = $value;
					}
				}
			}
			$snoopy -> submit($url,$mypost);
		}else{
			$snoopy -> fetch($url);
		}
		
		if (is_array($snoopy->headers)){
			foreach($snoopy->headers as $header){
				if (substr($header,0,10)=='MyqeeInfo:'){
					return trim(substr(urldecode($header),10));
				}
			}
		}
		
		return 'OK';
	}
	
	
	public function logs_list($id=0,$node_id=0){
		if (!$id>0 ||!$node_id>0){
			MyqeeCMS::show_error( Myqee::lang('admin/acquisition.error.parameterserror'),false,'goback');
		}
		$acquisition = MyqeeCMS::config('acquisition/acqu_'.$id);
		if (!$acquisition){
			MyqeeCMS::show_error('不存在指定采集设置！',FALSE,'goback');
		}
		if (!$acquisition['node']['node_'.$node_id]){
			MyqeeCMS::show_error('不存在指定节点设置！',FALSE,'goback');
		}
		$node = $acquisition['node']['node_'.$node_id];
		
		$view = new View('admin/acquisition_logs_index');
		$view -> set('acqu_id', $id );
		$view -> set('acqu_name', $acquisition['name'] );
		$view -> set('node_id', $node_id );
		$view -> set('node_name', $node['name'] );
		$view -> set('data', $node );
		
		$filesarr = array();
		$len = strlen(MYAPPPATH.'logs/');
		$list=glob(MYAPPPATH.'logs/acqu_'.$id.'_'.$node_id.'_*.log.txt');
		$list = array_reverse($list);	//倒序排列，把最新的log排到前面
		foreach ($list as $file) {
			$filesarr[] = array(
				'file' => substr($file,$len),
				'size' => ($size = filesize($file))>1024?(($size=number_format($size/1024,2))>1024?number_format($size/1024).' MB':$size.' KB'):$size.' Byte',
				'mtime' => filemtime($file),
			);
		}
		
		$view -> set('list', $filesarr );
		
		$view -> render(true);
	}
	
	
	public function logs_del($id=0,$node_id=0){
		$id = (int)$id;
		$node_id = (int)$node_id;
		if (!$id>0){
			MyqeeCMS::show_error('缺少参数！',FALSE,'goback');
		}
		if ($node_id){
			$fileglob = MYAPPPATH.'logs/acqu_'.$id.'_'.$node_id.'_*.log.txt';
		}else{
			$fileglob = MYAPPPATH.'logs/acqu_'.$id.'_*.log.txt';
		}
		
		$filesarr = array();
		$delok = $delerr = 0;
		foreach (glob($fileglob) as $file) {
			if (@unlink($file)){
				$delok++;
			}else{
				$delerr++;
			}
		}
		
		if ($delerr==0 && $delok>0){
			$type = 'show_ok';
			$info = '恭喜，删除全部 '.$delok.' 个日志文件！';
		}else if ($delerr==0 && $delok==0){
			$type = 'show_info';
			$info = '未删除任何日志文件！';
		}else{
			$type = 'show_info';
			$info = '成功删除 '.$delok.' 个日志，删除失败： '.$delerr.' 个文件！';
		}
		
		MyqeeCMS::$type($info,true);
	}
	
	/**
	 * 得到下一个可用的节点ID
	 * @param array $acquisition
	 * @return int
	 */
	protected function _get_next_nodeid ($acquisition=array()) {
		//获取最大KEY
		$bigkey = 0;
		if (is_array($acquisition['node'])){
			foreach ($acquisition['node'] as $v){
				if ($v['id']>$bigkey){
					$bigkey = $v['id'];
				}
			}
		}
		$bigkey ++;	
		return $bigkey;	
	}
}