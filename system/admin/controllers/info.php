<?php
class Info_Controller_Core extends Controller {

	function __construct(){
		parent::__construct(NULL);
		Passport::chkadmin();
	}

	public function index( $bclassid = 0 ){
		$islist = Passport::checkallow('info.list');
		if (Passport::getisallow('special.sms')){
			header('location:'.Myqee::url('info/main/1/sms_info').'?fullpage=yes');
			exit;
		}
		$view = new View('admin/info_index');
		$adminmodel = new Admin_Model;
		$view -> set ( 'list' , $adminmodel -> get_allclass_array() );
		$view -> set ( 'list_db' , $adminmodel -> get_db_array(NULL,FALSE,TRUE,array('ismemberdb'=>0),'id,dbname,name,isdefault,usedbmodel') );
		$view -> render(TRUE);
	}

	/**
	 * 按照数据表列出当前数据表信息
	 *
	 * @param number $p 页码参数位
	 * @param string $mytablename 数据表名称
	 */
	public function main($p=1,$database='default',$tablename=''){
		Passport::checkallow('info.list');
		$dbname = $database.'/'.$tablename;
		$per = 20 ;
		$view = new View('admin/info_main');
		$adminmodel = new Admin_Model;

		$dbtable = $adminmodel -> get_dbtable_forselect();

		$view -> set ('classtree' , $adminmodel -> get_allclass_array('classid,classname,bclassid,classpath,hits,myorder') );
		$view -> set ('dbtable' ,  $dbtable['forselect']);

		if ($tablename){
			$dbtable['forselect'][$dbname] or $dbname = $dbtable['default'];
		}else{
			$dbname = $dbtable['default']?$dbtable['default']:key($dbtable['forselect']);
		}
		list($database,$tablename) = explode('/',$dbname,2);
		$view -> set ( 'select_dbtable' , $dbname);
		$dbconfig = (array)Myqee::config('db/'.$dbname);
		$_db = Database::instance($database);
		if ( $_db -> table_exists($tablename)){
			
			$sys_field = (array)$dbconfig['sys_field'];

			//数据表字段列表
			$dbfield = $adminmodel -> get_table_field($dbname);
			
			//处理搜索
			$search = $_GET['search'];
			//搜索关键词
			if (!empty($search['keyword'])){
				if (!$search['field'] || !$dbfield[$search['field']]){
					$search['field'] = $sys_field['title'] ? $sys_field['title'] : key($dbfield);
				}
				if ($search['type']==1){
					$where[$search['field']] = $search['keyword'];
				}else{
					$otherBuilder[] = array(
						'like',
						$search['field'],
						'%'.$search['keyword'].'%'
					);
				}
			}
			//排序
			if (isset($search['orderby'])){
				if (!$search['myorder'] || !$dbfield[$search['myorder']]){
					$search['myorder'] = $sys_field['id']?$sys_field['id']:key($dbfield);
				}
				$orderby[$search['myorder']] = $search['orderby']?'ASC':'DESC';
			}elseif($sys_field['id']){
				$orderby[$sys_field['id']] = 'DESC';
			}

			//每页显示条数
			if ($search['limit']>0 && $search['limit']<=200){
				$per = $search['limit'];
			}
			
			$total = $adminmodel -> get_userdb_count($tablename ,$where , $sys_field['class_id'] ,$otherBuilder,$database);

			$this -> pagination = new Pagination( array(
				'uri_segment'    => 'main',
				'total_items'    => $total,
				'items_per_page' => $per,
			) );
			$view -> set ('total',$total);
		
			if ($_GET['fullpage']=='yes'){
				$adminmodel -> fullpage = 'fullpage';
			}else{
				$adminmodel -> fullpage = '';
			}
			$view -> set('db_info_html', $adminmodel -> get_db_info_html($dbconfig ,null, false, $per , $this->pagination->sql_offset,$where , $orderby ,$otherBuilder ,$database) );
			$view -> set ('pagehtml', $this -> pagination -> render('digg') );
			$view -> set( 'dbfield' , $dbfield);
		}
		
		$view -> set( 'mydbname' , $dbname);
		$view -> set ('search' , $_GET['search']);
		$view -> showheader = $_GET['fullpage']=='yes'?true:false;
		$view -> render(TRUE);
	}

	/**
	 * 按照分类列出当前栏目信息
	 *
	 * @param number $p 页码
	 * @param number $classid 分类ID
	 */
	public function myclass($p=1,$classid=0,$showheader = ''){
		Passport::checkallow('info.list');
		$classid = (int)$classid;
		
		$view = new View('admin/info_main_class');
		$adminmodel = new Admin_Model;
		if($_GET['fullpage']=='yes')$showheader='fullpage';
		$showheader == 'fullpage' or $showheader = '';
		$admin_in = $adminmodel -> get_admin_in('class');
		if ($admin_in!==0 && !in_array($classid,(array)$admin_in)){
			MyqeeCMS::show_error('您没有权限访问此栏目！',FALSE);
		}
		$dbtable = $adminmodel -> get_dbtable_forselect();
		$view -> set ('classtree' , $adminmodel -> get_allclass_array('classid,classname,bclassid,classpath,hits,myorder') );
		$view -> set ('dbtable' ,  $dbtable['forselect']);

		$classArray = $adminmodel -> get_class_array($classid);
		if ($classArray){
			$per = $classArray['manage_limit'] ? $classArray['manage_limit'] :20;
			
			$dbname = $classArray['dbname'];
			list($database,$tablename) = explode('/',$dbname,2);
			$_db = Database::instance($database);
			//数据表字段列表
			$dbfield = $adminmodel -> get_table_field($dbname,array(''=>'默认'));
			
			$dbconfig = (array)Myqee::config('db/'.$dbname);
			
			
			if ( $_db -> table_exists($tablename)){
				
				$sys_field = (array)$dbconfig['sys_field'];
				
				//处理搜索
				$search = $_GET['search'];
				//搜索关键词
				if (!empty($search['keyword'])){
					if (!$search['field'] || !$dbfield[$search['field']]){
						$search['field'] = $sys_field['title'] ? $sys_field['title'] : key($dbfield);
					}
					if ($search['type']==1){
						$where[$search['field']] = $search['keyword'];
					}else{
						$otherBuilder[] = array(
							'like',
							$search['field'],
							'%'.$search['keyword'].'%'
						);
					}
				}
				//排序
				if (isset($search['orderby'])){
					if (!$search['myorder'] || !$dbfield[$search['myorder']]){
						$search['myorder'] = $sys_field['id']?$sys_field['id']:key($dbfield);
					}
					$orderby[$search['myorder']] = $search['orderby']?'ASC':'DESC';	
				}
				$orderby[$classArray['manage_orderbyfield']] = $classArray['manage_orderby']=='ASC'?'ASC':'DESC';
				if($sys_field['id']){
					$orderby[$sys_field['id']] = 'DESC';
				}

				//每页显示条数
				if ($search['limit']>0 && $search['limit']<=200){
					$per = $search['limit'];
				}
				if ($search['showspecial']){
					$showspecial = explode('=',$search['showspecial'],2);
					if ($sys_field[$showspecial[0]]){
						if (!isset($showspecial[1])){
							$where[$sys_field[$showspecial[0]].'>'] = 0;
						}else{
							$where[$sys_field[$showspecial[0]]] = $showspecial[1];
						}
					}else{
						unset($search['showspecial']);
					}
				}
				
				if ( $classid>0 && $sys_field['class_id'] ){
					$allsonclass = $adminmodel -> get_sonclass_id($classid,true);
					if ($allsonclass && count($allsonclass)){
						$otherBuilder[] = array(
							'in',
							$sys_field['class_id'] ,
							$allsonclass 
						);
					}else{
						$where[$sys_field['class_id']] = $classid;
					}
				}
			
				$total = $adminmodel -> get_userdb_count($dbname ,$where ,$sys_field['class_id'] ,$otherBuilder);

				$this -> pagination = new Pagination( array(
					'uri_segment'    => 'myclass',
					'total_items'    => $total,
					'items_per_page' => $per,
				) );
				$view -> set ('total',$total);
				if ($classArray['manage_orderbyfield']){
					$classArray['manage_orderbyfield'] = strtolower($classArray['manage_orderbyfield']);

					$fieldlist = $_db -> list_fields($tablename,true);	//读取数据表字段
					if ($fieldlist[$classArray['manage_orderbyfield']] && !$orderby[$classArray['manage_orderbyfield']]){
						$orderby[$classArray['manage_orderbyfield']] or $orderby[$classArray['manage_orderbyfield']] = $classArray['manage_orderby'];
					}
				}
				$adminmodel -> fullpage = $showheader=='fullpage'?'?fullpage=yes':'';
				$view -> set('db_info_html', $adminmodel -> get_db_info_html($dbconfig ,$classArray, false, $per , $this->pagination->sql_offset,$where , $orderby , $otherBuilder) );
				$view -> set ('pagehtml', $this -> pagination -> render('digg') );
			}

			$view -> location = $adminmodel -> get_location_array( $classid,$classArray );

			$view -> set ('dbfield',$dbfield);
		}
//		echo $adminmodel -> db -> last_query();
		$query = Database::instance ()->get('[special]')->result_array(false);
		$specials[0] = '复制到下列专题';
		foreach ($query as $val) {
			$specials[$val['sid']] = $val['title'];
		}
		$view -> set ('specials' , $specials);
		$view -> set ('class' , $classArray);
		$view -> set ('page' , $p);
		$view -> set ('showheader' , $showheader);
		$view -> set ('search' , $search);
		$view -> render(TRUE);
	}

	public function add($classid,$showheader = false){
		Passport::checkallow('info.add');
		$classid = (int)$classid;
		if (!($classid>0)){
			MyqeeCMS::show_error( Myqee::lang('admin/info.error.parametererror'),false );
		}
		$this -> adminmodel = $adminmodel = new Admin_Model;
		$classinfo = $adminmodel -> get_class_array($classid,'classid,classname,fatherclass,iscontent,modelid,dbname');
		if (!($dbname = $classinfo['dbname']));
		list($database,$tablename) = explode('/',$dbname,2);
		$this -> classid = $classid;
		$this -> classname = $classinfo['classname'];
		$this -> classinfo = $classinfo;
		$this -> isadd = true;
		$this -> edit($database,$tablename,0,$showheader);
	}
	
	public function addbydbname($database,$tablename=null,$showheader = false){
		$this -> edit($database,$tablename,0,$showheader);
	}
	
	
	public function editbyclassid($classid,$infoid=0,$showheader = false){
		Passport::checkallow('info.edit');
		$classid = (int)$classid;
		if (!($classid>0)){
			MyqeeCMS::show_error( Myqee::lang('admin/info.error.parametererror'),false );
		}
		$this -> adminmodel = $adminmodel = new Admin_Model;
		$admin_in = $this -> adminmodel -> get_admin_in('class');
		if ($admin_in!==0 && !in_array($classid,(array)$admin_in)){
			MyqeeCMS::show_error('您没有权限访问此栏目！',FALSE);
		}
		$classinfo = $adminmodel -> get_class_array($classid,'classid,classname,fatherclass,iscontent,modelid,dbname');
		if (!($dbname = $classinfo['dbname']));
		list($database,$tablename) = explode('/',$dbname,2);
		$this -> classid = $classid;
		$this -> classname = $classinfo['classname'];
		$this -> classinfo = $classinfo;
		$this -> edit($database,$tablename,$infoid,$showheader);
	}
	
	public function for_acquisition($acqu_id=0){
		$showheader = $_GET['fullpage'] =='yes'?true:false;
		$acqu_id = (int)$acqu_id;
		if (!$acqu_id>0){
			MyqeeCMS::show_error( Myqee::lang('admin/info.error.parametererror'),$showheader?false:true);
		}
		
		$this -> db = new Database;
		$info = $this -> db -> getwhere('[acquisition_data]',array('id'=>$acqu_id)) -> result_array(false);
		$info = $info[0];
		if (!$info){
			MyqeeCMS::show_error( '不存在指定的采集信息！',$showheader?false:true);
		}
		
		$this -> for_acquisition = unserialize($info['info_content']);
		$this -> acquisition_data_id = $info['id'];
		$this -> classid = $info['class_id'];
		$this->_modelid = $info['model_id'];
		list($database,$tablename) = explode('/',$info['dbname']);
		$this -> edit($database,$tablename,$info['mydb_id'],$showheader);
	}

	public function edit($database,$tablename,$infoid=0,$showheader = false){
		if ($_GET['fullpage']=='yes'){
			$showheader = 'fullpage';
		}
		$infoid = (int)$infoid;
		if ($infoid>0){
			if ($this -> VIEW_TYPE){
				Passport::checkallow('info.view',$showheader?false:true);
			}else{
				Passport::checkallow('info.edit',$showheader?false:true);
			}
		}else{
			Passport::checkallow('info.add',$showheader?false:true);
		}
		
		$classid = (int)$this -> classid;
		if (!$database && !$tablename && $infoid == 0 && $classid==0){
			MyqeeCMS::show_error( Myqee::lang('admin/info.error.parametererror'),$showheader?false:true);
		}
		$dbname = $database .'/'.$tablename;
		
		$db_config = Myqee::config('db/'.$database.'/'.$tablename);
		($adminmodel = $this -> adminmodel) or $adminmodel = new Admin_Model;

		$_db = Database::instance($database);
		if ( !$_db -> table_exists($tablename)){
			MyqeeCMS::show_error( Myqee::lang('admin/info.error.nodatabase'),$showheader?false:true,'goback');
		}
		$view = new View('admin/info_edit');
		
		
		//查询扩展表的内容
		$myinfo = $this->_dealRealationTableInfo ($dbname,$infoid,$db_config,$adminmodel);

		if (!is_array($db_config)){
			MyqeeCMS::show_error('不存在指定的数据表！',$showheader?false:true);
		}
		$sys_field = (array)$db_config['sys_field'];
		
		if ($sys_field['class_id'] && $classid>0){
			$classid >0 or $classid=$myinfo[$sys_field['class_id']];
			if ($classid>0)$myclass = $this -> classinfo ? $this -> classinfo : $adminmodel -> get_class_array($classid,'classid,classname,fatherclass,iscontent,modelid,dbname');
			if ($dbname!=$myclass['dbname']){
				$view -> set ('diffdbname' , $myclass['dbname']);
			}
			$sys_field['class_id'] and $myinfo[$sys_field['class_id']] = $myclass['classid'];
			$sys_field['class_name'] and $myinfo[$sys_field['class_name']] = $myclass['classname'];
		} elseif($this->_modelid >0){
			//用模型来插入数据,采集中有用到
			$myclass['modelid']=intval($this->_modelid);
			$myclass['iscontent']=1;
		}else{
			//数据表模型
			$myclass['modelid']=0;
			$myclass['iscontent']=1;
			
			if (!$db_config['usedbmodel']){
				MyqeeCMS::show_info('未启用数据表模型',$showheader?false:true,'goback');
			}
		}

		if (isset($this -> for_acquisition) && is_array($this -> for_acquisition) && count($this -> for_acquisition)){
			$myinfo = array_merge($myinfo,$this -> for_acquisition);
		}
		
		$view -> myinfo = $myinfo;
		$view -> user_editinfo_formhtml = $adminmodel -> get_user_editinfo_form($dbname,$myinfo,$myclass['modelid'],$infoid>0?false:true,$this -> VIEW_TYPE?true:false);
		if ($myclass['iscontent']==0){
			MyqeeCMS::show_error( Myqee::lang('admin/info.error.nocontent'),$showheader?false:true,'goback');
		}
		
		if ($classid>0){
			$view -> location = $adminmodel -> get_location_array( $classid ,$myclass );
		}
		$view -> viewtype = $this -> VIEW_TYPE;
		$view -> sys_field = $sys_field;
		$view -> classid = $classid ;
		$view -> dbname = $dbname;
		$view -> time = $time = time();
		$view -> showheader = $showheader;
		$view -> isadd = $this -> isadd;
		$view -> forward = $_GET['forward']?$_GET['forward']:'';
		
		$keyset = array( 'id:'.$myinfo[$sys_field['id']] ,'class:'.$classid ,'db:'. $dbname , 'time:'.$time );
		if (isset($this -> acquisition_data_id) && $this -> acquisition_data_id>0){
			$view -> set('acqu_dataid',$this -> acquisition_data_id);
			$keyset[] = 'acqu_id:'.$this -> acquisition_data_id;
		}
		$view -> savekey = $adminmodel -> get_edit_key( $keyset );
		
		$view -> render(TRUE);
	}
	
	public function view($database,$tablename,$infoid=0,$showheader = false){
		if (!($infoid)>0){
			MyqeeCMS::show_error(Myqee::lang('admin/info.error.noid'),false,'goback');
		}
		$this -> VIEW_TYPE = TRUE;
		$this -> edit($database,$tablename,$infoid,$showheader);
	}
	
	public function viewbyclassid($classid,$infoid=0,$showheader = false){
		if (!($infoid)>0){
			MyqeeCMS::show_error(Myqee::lang('admin/info.error.noid'),false,'goback');
		}
		$this -> VIEW_TYPE = TRUE;
		$this -> editbyclassid($classid,$infoid,$showheader);
	}

	/**
	 * 保存信息
	 *
	 */
	public function save($showheader = false){
		$post = $_POST;
		if ($post['sys']['id']>0){
			Passport::checkallow('info.edit','',true);
		}else{
			Passport::checkallow('info.add','',true);
		}
		$dbname = $post['sys']['mydbname'];
		$classid = (int)$post['sys']['classid'];

		if ( !$dbname ){
			MyqeeCMS::show_error( Myqee::lang('admin/info.error.errordbname'),true );
		}
		/*
		if ( !($classid > 0) ){
			MyqeeCMS::show_error( Myqee::lang('admin/info.error.errorclassid'),true );
		}
		*/

		$adminmodel = new Admin_Model;
		
		$keyset = array( 'id:'.$post['sys']['id'] , 'class:'.$classid , 'db:'.$dbname , 'time:'.$post['sys']['time']);
		if (isset($post['sys']['acquid'])&&$post['sys']['acquid']>0){
			$keyset[] = 'acqu_id:'.(int)$post['sys']['acquid'];
			$acquid = (int)$post['sys']['acquid'];
		}
		
		$savekey = $adminmodel -> get_edit_key( $keyset );

		if ( $savekey != $post['sys']['savekey']){
			MyqeeCMS::show_error( Myqee::lang('admin/info.error.errorkey'),true );
		}
		$dbconfig = Myqee::config('db/'.$dbname);
		list($database,$tablename) = explode('/',$dbname);
		$_db = Database::instance($database);
		if ( !($_db -> table_exists($tablename))){
			MyqeeCMS::show_error( Myqee::lang('admin/info.error.nodatabase'),true );
		}
		
		
		
		
		//读取栏目配置
		$mydbconfig = array();
		if ( $classid > 0 ){
			$classinfo = $adminmodel -> get_class_array($classid);
			$modelid = (int)$classinfo['modelid'];
			if ($modelid>0){
				$modelconfig = Myqee::config('model/model_'.$modelid);
			}elseif ($dbconfig['usedbmodel']){
				$modelconfig = $dbconfig['model'];
			}
		}elseif ($dbconfig['usedbmodel']){
			//启用数据表模型
			$modelconfig = $dbconfig['model'];
		}
		//处理扩展表，两个变量是以引用的方式传递的
		$this->_dealRelationTableDb($dbconfig,$modelconfig);
		
		//
		foreach ((array)$modelconfig['field'] as $key => $value){
			if ($value['input'] && ($value['editor'] || !$post['sys']['id'])){
				$mydbconfig[$key] = $dbconfig['edit'][$key];
				$value['notnull'] and $mydbconfig[$key]['notempty'] = true;
			}
		}
		if (!is_array($mydbconfig))$mydbconfig = (array)$dbconfig['edit'];

		$upfield = array();
		//暂时不能用数组
		foreach ($mydbconfig as $k => $v){
			$upfield[$k] = $adminmodel -> check_postvalue($post['info'][$k],$v);
		}

		if ($dbconfig['sys_field']['class_id'] && $classid){
			$upfield[$dbconfig['sys_field']['class_id']] = $classid;
		}
		if ($dbconfig['sys_field']['class_name'] && $classid>0){
			$myclass = $adminmodel -> get_class_array($classid);
			$upfield[$dbconfig['sys_field']['class_name']] = $myclass['classname'];
		}

		//自动创建摘要
		if ($dbconfig['sys_field']['abstract'] && isset($upfield[$dbconfig['sys_field']['abstract']]) && empty($upfield[$dbconfig['sys_field']['abstract']])){
			foreach(array('contentdb_page','contentdb','content') as $item){
				if ($dbconfig['sys_field'][$item]){
					$upfield[$dbconfig['sys_field']['abstract']] = Tools::substr(strip_tags($upfield[$dbconfig['sys_field'][$item]],''),0,200);
					break;
				}
			}
		}
		if (count($upfield)==0){
			MyqeeCMS::show_info( Myqee::lang('admin/info.info.savenone') ,true );
		}
					
		//备份post数据，因为下来要按照表名进行分组
		$_upfield = $upfield;
		foreach ($upfield as $key=>$val) {
			if (substr($key,0,1) == '#') {
				unset ($upfield[$key]);
			}
		}
		if ( $post['sys']['id'] > 0 ){
			//EDIT
			$doinfo = $modelconfig['adminedit']['edit'];
			if ($doinfo){
				//获取旧数据
				$oldinfo = $_db -> getwhere($tablename,array($dbconfig['sys_field']['id'] => $post['sys']['id'])) -> result_array(FALSE);
				$oldinfo = $oldinfo[0];
				$posttype = 'edit';
			}
			//更新表
			$this->_updateTables ($dbname,$upfield,$dbconfig,$adminmodel);
			$status = array(1);
//			print_r($upfield);exit;
//			$status = $adminmodel -> db -> update($mydbname, $upfield ,array($dbconfig['sys_field']['id'] => $post['sys']['id']));
//			if ($oldinfo && is_array($oldinfo)){
//				$upfield = array_merge($oldinfo,$upfield);
//			}
//			echo $
		}else{
			//创建时间
			if ($dbconfig['sys_field']['createtime'] && (int)$upfield[$dbconfig['sys_field']['createtime']]==0){
				$upfield[$dbconfig['sys_field']['createtime']] = time();
			}
			if ($dbconfig['sys_field']['posttime'] || $upfield[$dbconfig['sys_field']['posttime']]){
				$upfield[$dbconfig['sys_field']['posttime']] = time();
			}
			if ($dbconfig['sys_field']['posttime2'] || $upfield[$dbconfig['sys_field']['posttime2']]){
				$upfield[$dbconfig['sys_field']['posttime2']] = time();
			}

			//文件路径
			if ($dbconfig['sys_field']['filepath'] && $myclass['content_selfpath']){
				$upfield[$dbconfig['sys_field']['filepath']] = date($myclass['content_selfpath']);
			}
			
			//ADD
			if (is_array($dbconfig['default'])){
				$upfield = array_merge($dbconfig['default'],$upfield);
			}
			$upfield = $this->_getPostGrout($upfield,$dbconfig);

			//插入主表
			$status = $_db -> insert($tablename, $upfield[$dbname]);
			$post['sys']['id'] = $status -> insert_id();
			$upfield[$dbname][$dbconfig['sys_field']['id']] = $post['sys']['id'];

			//更新文件名
			if ($dbconfig['sys_field']['filename'] && $upfield[$dbname][$dbconfig['sys_field']['id']]){
				if ($myclass){
					$thename = $myclass['content_prefix'];
					switch ($myclass['content_filenametype']){
						case 0:
							$thename .= $upfield[$dbname][$dbconfig['sys_field']['id']];
							break;
						case 1:
							$thename .= $upfield[$dbname][$dbconfig['sys_field']['createtime']];
							break;
						case 2:
							$thename .= md5(Tools::get_rand(50).'__'.$_SERVER['REQUEST_TIME']);
							break;
						case 3:
							$thename .= substr(md5(Tools::get_rand(50).'__'.$_SERVER['REQUEST_TIME']),8,16);
							break;
						default:
							$thename .= $upfield[$dbname][$dbconfig['sys_field']['id']];
							break;
					}
					//后缀（扩展名）
					$thename .= $myclass['content_suffix'];
				}else{
					$thename .= $upfield[$dbname][$dbconfig['sys_field']['id']].'.html';
				}
				$upfield[$dbname][$dbconfig['sys_field']['filename']] = $thename;

				$_db -> update($tablename, array($dbconfig['sys_field']['filename']=>$thename), array($dbconfig['sys_field']['id']=>$upfield[$dbname][$dbconfig['sys_field']['id']]) );
			}

			//插入扩展表
			$this->_insertExtendTable ($upfield,$dbconfig);
			$doinfo = $modelconfig['adminedit']['add'];
			$posttype = 'add';
		}
		//处理钩子
		$isadd = $post['sys']['id']>0 ? false :true;
		$this->_hook($post['sys']['id'],$_upfield,$dbconfig,$isadd);
	
		if (count($status)>0){
			//执行APP接口
			if ($doinfo){
				$tmpdoinfomodel = get_class_methods('Info_Api');
				if (in_array($doinfo,$tmpdoinfomodel)){
					$domodel = new Info_Api;
					//传入参数
					$domodel -> id = $post['sys']['id'];
					$domodel -> info =& $upfield;
					$domodel -> oldinfo =& $oldinfo;
					$domodel -> dbname = $tablename;
					$domodel -> dbconfig = $dbconfig;
					$domodel -> modelid = $modelid;
					$domodel -> modelconfig = $modelconfig;
					$domodel -> class_id = $classid;
					$domodel -> class_name = $classinfo['classname'];
					$domodel -> myclass = $classinfo;
					$domodel -> type = $posttype;
					
					$domodel -> $doinfo();
				}
			}
			$forward = $post['sys']['forward'];
			$forward or $forward = Myqee::url('info/myclass/1/'.$post['sys']['classid'].'/'.($showheader =='fullpage'?'fullpage':''));
			if ($classinfo && $classinfo['content_tohtml']==0){
				
				//生成静态页
				$snoopy = new Snoopy();
				$url = _get_tohtmlurl('toinfo_byid',Myqee::config('encryption.default.key'),'_id='.(int)$post['sys']['id'].'&_allclassid='.$classid.'&_nowclassid='.$classid.'&_limit=1');
				if (substr($url,0,1)=='/'){
					$url = Myqee::protocol() .'://'. $_SERVER['HTTP_HOST'] . $url;
				}
				$snoopy -> fetch($url,$_SERVER['SERVER_ADDR']);
				$response = $snoopy -> results;
				
				if ($response[0]=='{'||$response[0]=='['){
					$info = Tools::json_decode($response);
					if (is_array($info)){
						if ($info['dook']>=1){
							$message = '恭喜，保存成功并已生成静态页！';
							$fun = 'show_ok';
						}else{
							$message = '数据保存成功，但未生成任何静态页！';
							$fun = 'show_info';
						}
					}else{
						$message = '数据已保存，但生成静态页时发生异常，请联系管理员！';
						$fun = 'show_error';
					}
				}else{
					$message = '数据已保存，但生成静态页时发生异常，请联系管理员！';
					$fun = 'show_error';
				}
				
			}else{
				//输出成功提示
				$message = Myqee::lang('admin/info.info.saveok');
				$fun = 'show_ok';
			}
			$btn_arr =array( 
				array('返回上页','ok'),
				array('继续修改','cancel'),
				array('生成栏目页','class'),
				array('生成首页','index')
			);
			if (!$classid){
				unset($btn_arr[2]);
				$btn_arr = array_values($btn_arr);
			}
			
			//更新采集数据信息状态
			if ($acquid>0){
				$adminmodel -> db -> update ('[acquisition_data]',array('is_todb'=>1,'mydb_id'=>$post['sys']['id']),array('id'=>$acquid));
			}
			
			MyqeeCMS::$fun(
				array(
					'message' => $message,
					'width' => 400,
					'height' =>160,
					'btn' => $btn_arr,
					'handler' => 'function (el){
						if (el=="addclass"){parent.document.location.href="'.Myqee::url('class/add').'";}
						'.($classid?'else if (el=="class"){parent.goUrl("'.Myqee::url('task/tohtml/frame/?type=class&classid%5B%5D='.$classid).'","_blank");}':'').'
						else if (el=="index"){goUrl("'.Myqee::url('task/tohtml/toindex').'","hiddenFrame");}
						else if (el=="ok"){parent.document.location.href="'.$forward.'";}
					}',
				)
			,true);
		}else{
			MyqeeCMS::show_info( Myqee::lang('admin/info.info.savenone') ,true );
		}
	}

	public function del($database,$tablename=null,$allid=null){
		Passport::checkallow('info.del','',true);
		$dbname = $database.'/'.$tablename;
		if ( !$dbname ){
			MyqeeCMS::show_error( Myqee::lang('admin/info.error.errordbname'),true );
		}
		if (!$allid){
			MyqeeCMS::show_error( Myqee::lang('admin/info.error.parametererror'),true );
		}
		$idArr = explode(',',$allid);
		$myId = array();
		foreach ($idArr as $tmpid){
			if ($tmpid>0 && !in_array($tmpid,$myId)){
				$myId[] = $tmpid;
			}
		}
		if (count($myId)==0){
			MyqeeCMS::show_info( Myqee::lang('admin/info.info.nodelete') , true );
		}

		$adminmodel = new Admin_Model;
		$dbconfig = Myqee::config('db/'.$dbname);
		$sys_field = $dbconfig['sys_field'];
		$_db = Database::instance($dbconfig['database']);
		$result = $_db -> select('*') -> from($tablename) -> in($sys_field['id'],$myId) -> get() -> result_array ( FALSE );
		if (count($result)==0){
			MyqeeCMS::show_info( Myqee::lang('admin/info.error.noinfo'),true);
		}
		
		foreach ($result as $item){
			//栏目
			$classid = $item[$sys_field['class_id']];
			if ( $classid > 0 ){
				$classinfo[$classid] or $classinfo[$classid] = $adminmodel -> get_class_array($classid);
				$modelid = (int)$classinfo[$classid]['modelid'];
				if ($modelid>0){
					$nowmodelkey = $modelid;
					$modelconfig[$modelid] or $modelconfig[$modelid] = Myqee::config('model/model_'.$modelid);
				}elseif($dbconfig['usedbmodel']){
					$nowmodelkey = $dbname;
					$modelconfig[$dbname] or $modelconfig[$dbname] = $dbconfig['model'];
				}
				$classpath = ($classinfo[$classid]['content_pathtype']==1)?$classinfo[$classid]['content_path']:(($classinfo[$classid]['isnothtml']==0 && $classinfo[$classid]['classpath'])?$classinfo[$classid]['classpath']:'');
				$classpath and $classpath = trim($classpath,'/').'/';
			}elseif($dbconfig['usedbmodel']){
				$classpath = '';
				$nowmodelkey = $dbname;
				$modelconfig[$dbname] = $dbconfig['model'];
			}
			
			//删除文件
			if ( (!$item[$sys_field['filepath']] || !$item[$sys_field['filename']]) && $sys_field['class_id'] ){
				$thefileinfo = $adminmodel -> getinfopath($item[$sys_field['class_id']],$item,TRUE);
				$sys_field['filepath'] and $item[$sys_field['filepath']] = rtrim($thefileinfo['path'],'/');
				$sys_field['filename'] and $item[$sys_field['filename']] = $thefileinfo['name'];
			}
			$infofile = str_replace('//','/',$classpath.$item[$sys_field['filepath']] .'/'. $item[$sys_field['filename']]);

			if ( $infofile && $infofile!='/' ){
				if (is_file(WWWROOT.$infofile))
				@unlink( WWWROOT . $infofile);
			}

			//删除数据API接口
			$doinfo = $modelconfig[$nowmodelkey]['adminedit']['del'];
			
			if ($doinfo){
				$tmpdoinfomodel or $tmpdoinfomodel = get_class_methods('Info_Api');
				if (in_array($doinfo,$tmpdoinfomodel)){
					$domodel or $domodel = new Info_Api;
					//传入参数
					$domodel -> id = $item[$sys_field['id']]; 
					$domodel -> oldinfo = $item;
					$domodel -> dbname = $dbname;
					$domodel -> dbconfig = $dbconfig;
					$domodel -> modelid = $modelid;
					$domodel -> modelconfig = $modelconfig[$nowmodelkey];
					$domodel -> class_id = $classid;
					$domodel -> class_name = $classinfo[$classid]['classname'];
					$domodel -> myclass = $classinfo[$classid];
					$domodel -> type = 'del';
					
					$domodel -> $doinfo($item[$sys_field['id']]);
				}
			}
		}
		$delNum = $this->_deleteTables ($dbname,$dbconfig,$myId);
//		$delNum = $adminmodel -> db -> in($sys_field['id'],$myId) -> delete($dbname);

		if (count($delNum)>0){
			MyqeeCMS::show_info( Myqee::lang('admin/info.info.delsuccess',count($delNum)) , true , 'refresh');
		}else{
			MyqeeCMS::show_info( Myqee::lang('admin/info.info.nodelete') , true );
		}
	}


	public function setvalue($database=null,$talename=null,$type='',$allid=null){
		Passport::checkallow('info.setvalue','',true);
		$canset = array('isshow','is_indexshow','iscommend','is_hot','isheadlines','ontop','class_id');
		if ( !$database || !$talename ){
			MyqeeCMS::show_error( Myqee::lang('admin/info.error.errordbname'),true );
		}
		$dbname = $database . '/' .$talename;
		if (!$type){
			MyqeeCMS::show_error( Myqee::lang('admin/info.error.setvaluetypenull'),true );
		}
		if (!$allid){
			MyqeeCMS::show_error( Myqee::lang('admin/info.error.parametererror'),true );
		}
		$idArr = explode(',',$allid);
		$myId = array();
		foreach ($idArr as $tmpid){
			if ($tmpid>0 && !in_array($tmpid,$myId)){
				$myId[] = $tmpid;
			}
		}
		if (count($myId)==0){
			MyqeeCMS::show_info( Myqee::lang('admin/info.info.nodoset') , true );
		}
		$type = explode('=',$type);
		if ( !in_array($type[0],$canset)){
			MyqeeCMS::show_error( Myqee::lang('admin/info.error.setvaluetypeerror'),true );
		}
		$sys_field = Myqee::config('db/'.$dbname.'.sys_field');
		if ( !$sys_field[$type[0]] ){
			//MyqeeCMS::show_error( str_replace("\n",'',print_r($sys_field,true)),true );
			MyqeeCMS::show_error( Myqee::lang('admin/info.error.setvaluetypeerror2'),true );
		}

		$adminmodel = new Admin_Model;

		$setkey = $sys_field[$type[0]];
		$setvalue = (int)$type[1];
		if (defined('IS_SETCLASS')){
			$myclass = $adminmodel -> get_class_array($setvalue,'classname,iscontent,dbname');
			if ($myclass['iscontent']==0){
				MyqeeCMS::show_error( Myqee::lang('admin/info.error.nocontent'),true);
			}
			if ($myclass['dbname']!=$dbname){
				MyqeeCMS::show_error( Myqee::lang('admin/info.error.diffdatabase'),true);
			}
			$sqlset = '`'.$setkey.'`='.$setvalue.',`'.$sys_field['class_name'].'`='.var_export($myclass['classname'],true);
		}else{
			$sqlset = '`'.$setkey.'`='.$setvalue;
		}
		$db = Myqee::db($database);

		$sql = 'UPDATE `'.$db -> table_prefix().$talename .'` SET '.$sqlset.' WHERE '.$sys_field['id'].' IN ('.join(',',$myId).')';
		$doNum = $db -> query($sql) -> count();
//		$doNum = $adminmodel -> db -> from($dbname.' a') -> set($newSet) -> in($sys_field['id'],$myId) -> update();
		if ($doNum>0){
			MyqeeCMS::show_info( Myqee::lang('admin/info.info.dosetsuccess',$doNum) , true , 'refresh');
		}else{
			MyqeeCMS::show_info( Myqee::lang('admin/info.info.nodoset') , true );
		}
	}

	public function move($database='',$tablename='',$newclassid=null,$allid=null){
		Passport::checkallow('info.move','',true);
		$dbname = $database.'/'.$tablename;
		$newclassid = (int)$newclassid;
		if (!($newclassid>0))MyqeeCMS::show_error( Myqee::lang('admin/info.error.parametererror'),true );
		define('IS_SETCLASS','yes');
		$this -> setvalue($database,$tablename,'class_id='.$newclassid,$allid);
	}

	public function copy($database='',$tablename='',$newclassid=null,$allid=null){
		Passport::checkallow('info.add','',true);
		$dbname = $database.'/'.$tablename;
		if ( !$dbname ){
			MyqeeCMS::show_error( Myqee::lang('admin/info.error.errordbname'),true );
		}
		$newclassid = (int)$newclassid;
		if (!$newclassid){
			MyqeeCMS::show_error( Myqee::lang('admin/info.error.setvaluetypenull'),true );
		}
		if (!$allid){
			MyqeeCMS::show_error( Myqee::lang('admin/info.error.parametererror'),true );
		}
		$idArr = explode(',',$allid);
		$myId = array();
		foreach ($idArr as $tmpid){
			if ($tmpid>0 && !in_array($tmpid,$myId)){
				$myId[] = $tmpid;
			}
		}
		if (count($myId)==0){
			MyqeeCMS::show_info( Myqee::lang('admin/info.info.nodoset') , true );
		}
		$sys_field = Myqee::config('db/'.$dbname.'.sys_field');

		$adminmodel = new Admin_Model;

		$myclass = $adminmodel -> get_class_array($newclassid,'classname,iscontent,dbname');

		if ($myclass['iscontent']==0){
			MyqeeCMS::show_error( Myqee::lang('admin/info.error.nocontent'),true);
		}
		if ($myclass['dbname']!=$dbname){
			MyqeeCMS::show_error( Myqee::lang('admin/info.error.diffdatabase'),true);
		}
		$dbconfig = Myqee::config('db/'.$dbname);
		$myInfos = $adminmodel -> get_userdb_info($dbname,$myId,'*');
		$_db = Database::instance($database);
		$copynum = 0;
		$nocopynum = 0;
		foreach ($myInfos as $myInfo){
			$infoclassid = $myInfo[$sys_field['class_id']];
			if ($infoclassid!=$newclassid){
				unset($myInfo[$sys_field['id']]);
				$myInfo[$sys_field['class_id']] = $newclassid;
				$myInfo[$sys_field['class_name']] = $myclass['classname'];
				$rs = $_db -> insert($tablename,$myInfo);
				$copynum += count($rs);
			}else{
				$nocopynum++;
			}
		}

		if ($copynum>0){
			MyqeeCMS::show_info( Myqee::lang('admin/info.info.copysuccess',$copynum) , true);
		}else{
			MyqeeCMS::show_info( Myqee::lang('admin/info.info.nodoset') , true );
		}
	}
	
	/**
	 * 信息复制到专题
	 * @param string $database
	 * @param string $tablename
	 * @param int $newclassid
	 * @param string $allid id1,id2,....
	 */
	public function copy2special($database='',$tablename='',$specialid=null,$allid=null) {
		Passport::checkallow('info.add','',true);
		$dbname = $database.'/'.$tablename;
		if ( !$dbname ){
			MyqeeCMS::show_error( Myqee::lang('admin/info.error.errordbname'),true );
		}
		$specialid = (int)$specialid;
		if (!$specialid){
			MyqeeCMS::show_error( Myqee::lang('admin/info.error.setvaluetypenull'),true );
		}
		$db = Database::instance();
		$query = $db->getwhere('[special]',array('sid'=>$specialid))->result_array(false);
		if (empty($query)) {
			MyqeeCMS::show_error( Myqee::lang('admin/info.error.setvaluetypenull'),true );
		}
		$specialinfo = $query[0];
		if (!$allid){
			MyqeeCMS::show_error( Myqee::lang('admin/info.error.parametererror'),true );
		}
		$idArr = explode(',',$allid);
		$myId = array();
		foreach ($idArr as $tmpid){
			if ($tmpid>0 && !in_array($tmpid,$myId)){
				$myId[] = $tmpid;
			}
		}
		if (count($myId)==0){
			MyqeeCMS::show_info( Myqee::lang('admin/info.info.nodoset') , true );
		}
		$dbconfig = Myqee::config('db/'.$dbname);
		$this->adminmodel = new Admin_Model;
		$myInfos = $this->adminmodel -> get_userdb_info($dbname,$myId,'*');
		$_db = Database::instance($database);
		$copynum = 0;
		$nocopynum = 0;
		//找到可以允许的classid
		$_canaddclasses = array();
		if ($specialinfo['isrecursion']) {
			$tmp = explode('|',trim($specialinfo['classides'],'|'));
			foreach ($tmp as $v) {
				$_canaddclasses[] = $v;
				$_config = Myqee::config('class/class_'.$v);
				$_sonclasses =explode('|',trim($_config['sonclass'],'|'));
				$_canaddclasses = array_merge($_canaddclasses,$_sonclasses);
			}
		}else{
			$_canaddclasses = explode('|',trim($specialinfo['classides'],'|'));
		}
		foreach ($myInfos as $myInfo){
			if ($specialinfo['classides'] != '|0|' && !in_array($myInfo['class_id'],$_canaddclasses)) {
				$_config = Myqee::config('class/class_'.$myInfo['class_id']);
				$_fatherclasses = explode('|',trim($_config['fatherclass'],'|'));
				if (!array_intersect($_fatherclasses,$_canaddclasses)) {
					continue;
				}
			}
			$data = $this->_get_specialdata($dbconfig,$myInfo);
			$data['sid'] = $specialid;
			$query = $db->merge('[special_info]',$data);
			$tmp = count($query);
			if ($tmp >0) {
				$copynum += 1;
			}
		}

		if ($copynum>0){
			MyqeeCMS::show_info( Myqee::lang('admin/info.info.copysuccess',$copynum) , true);
		}else{
			MyqeeCMS::show_info( Myqee::lang('admin/info.info.nodoset') , true );
		}
		
	}
	
	/**
	 * 根据数据表信息得到专辑的信息
	 * @param array $dbconfig
	 * @param array $info
	 * @return array
	 */
	protected function _get_specialdata ($dbconfig,$info) {
		$data = array();
		$data['infoid'] = $info[$dbconfig['sys_field']['id']];
		$data['dbname'] = $dbconfig['dbname'];
		$data['posttime'] = time();
		
		$data['createtime'] = time();
		if ($dbconfig['sys_field']['title']){
			$data['title'] = $info[$dbconfig['sys_field']['title']];
		}
		
		if ($dbconfig['sys_field']['imagenews']){
			$data['imagenews'] = $info[$dbconfig['sys_field']['imagenews']];
		}
		
		if ($dbconfig['sys_field']['linkurl']){
			$data['linkurl'] = $info[$dbconfig['sys_field']['linkurl']];
		}
		
		if ($dbconfig['sys_field']['class_id']){
			$data['class_id'] = $info[$dbconfig['sys_field']['class_id']];
		}
		
		if ($dbconfig['sys_field']['class_name']){
			$data['class_name'] = $info[$dbconfig['sys_field']['class_name']];
		}
		
		if ($dbconfig['sys_field']['title']){
			$data['title'] = $info[$dbconfig['sys_field']['title']];
		}

		if ($dbconfig['sys_field']['isshow']){
			$data['isshow'] = $info[$dbconfig['sys_field']['isshow']];
		}
		
		if ($dbconfig['sys_field']['isheadlines']){
			$data['isheadlines'] = $info[$dbconfig['sys_field']['isheadlines']];
		}
		if ($dbconfig['sys_field']['ontop']){
			$data['ontop'] = $info[$dbconfig['sys_field']['ontop']];
		}
		if ($dbconfig['sys_field']['is_hot']){
			$data['ishot'] = $info[$dbconfig['sys_field']['is_hot']];
		}
		if ($dbconfig['sys_field']['iscommend']){
			$data['iscommend'] = $info[$dbconfig['sys_field']['iscommend']];
		}
		//算出url
		$info[$dbconfig['sys_field']['id']] = $data['infoid'];
		$classid = $data['class_id'] >0 ? $data['class_id'] : $dbconfig['dbname'];
		$adminmodel = new Admin_Model();
		$data['url'] = $adminmodel->getinfourl($classid,$info);
		
		unset ($data[$dbconfig['sys_field']['id']]);
		return $data;
	}
	/** 
	 * 从扩展表中读取信息
	 *
	 * @param string $tablename 主表的名称
	 * @param string $fieldname 主表的字段名称
	 */
	public function get_extend_fieldvalue() {
		$codestr = $_POST['code'];
		if (!$codestr){
			exit('ERROR:参数错误，请联系管理员或刷新页面重试！');
		}
		$code = Des::Decrypt($codestr);
		if (!$match = Tools::json_decode($code)){
			exit('ERROR:参数解析错误，请联系管理员或刷新页面重试！');
		}
		list($fieldname,$dbname,$ffieldsave,$ffieldshow,$expstr,$isappend) = $match;
		if (strpos($dbname,'/')===false){
			$fdatabase = 'default';
			$ftablename = $dbname;
			$dbname = $fdatabase.'/'.$dbname;
		}else{
			list($fdatabase,$ftablename) = explode('/',$dbname,2);
		}
		$page = (int)$_GET['page'];
		$page > 0 or $page = 1;
		
		$dbconfig = Myqee::config('db/'.$dbname);
		$db = Database::instance($fdatabase);
		
		$limit = 20;
		$num = $db->count_records ( $ftablename );
		
		$pagination = new Pagination( 
			array(
				'query_string'	 => 'page',
				'total_items'    => $num,
				'items_per_page' => $limit,
				'pagestr'		 => '#" onclick="frameFrame.showSelectValueFrame(\''.$codestr.'\',\'{{page}}\');return false;',
			)
		);
		$pageurl = $pagination->render();
		
		$list = $db->select($ffieldsave.','.$ffieldshow)->getwhere($ftablename,null,$limit,$pagination->sql_offset)->result_array(FALSE);
		$view = new View('admin/extend_fieldvalue');
		$view->set('list',$list);
		$view->set('pageurl',$pageurl);
		$view->set('ffieldsave',$ffieldsave);
		$view->set('ffieldshow',$ffieldshow);
		$view->set('fieldname',$fieldname);
		$view->set('title',$dbconfig['edit'][$fieldname]['title']);
		$view->set('fieldname',$fieldname);
		//是否追加，用于录入多个值的时候
		$view->set('expstr',$expstr);
		$view->set('isappend',$isappend);
		$view->render(TRUE);
	}
	/**
	 * 将扩展表的配置扩展到主表
	 *
	 * @param array $dbconfig
	 * @return array
	 */
	protected function _dealRelationTableDb (&$dbconfig,&$modelconfig) {
		//从post中找出扩展表 post过来的字段中带有.的，前面是扩展表的名称
		$info = $_POST['info'];
		$fields = array_keys($info);
		$ktables = array();
		foreach ($fields as $val) {
			$pos = strrpos($val,'.');
			if ($pos !== FALSE) {
				$table = substr($val,0,$pos);
				$ktables[] = $table;
			}
		}
		$ktables = array_unique($ktables);
		//加载这些表的配置
		foreach ($ktables as $val) {
			$config = Myqee::config('db/'.$val);
			if (!empty($config)) {
				foreach ($config['model']['field'] as $k=>$v) {
					if ($v['input'] || $v['edit']) {
						$modelconfig['field'][$val.'.'.$k] = $v;
						$dbconfig['edit'][$val.'.'.$k] = $config['edit'][$k];
					}
				}
			}
		}
	}
	
	/**
	 * 由于添加了扩展表的功能，所以需要对post的数据进行分组
	 * @param array $fields 
	 * @return array
	 */
	protected function _getPostGrout ($fields,$dbconfig) {
		$post = array();
		$mydbname = $_POST['sys']['mydbname'];
		foreach ($fields as $key=>$val) {
			if (substr($key,0,1) == '_') {
				unset($fields[$key]);
				continue;
			}
			$pos = strrpos($key,'.');
			if ($pos !== FALSE) {
				//扩展表
				$table = substr($key,0,$pos);
				$field = substr($key,$pos+1);
				$post[$table][$field] = $val;
			} else {
				//主表
				$post[$mydbname][$key] = $val;
			}
		}
		
		//在多对多的情况下，需要更新扩展表的数据
		$extandData = array();
		if (!is_array($dbconfig['model']['relationfield'])) {
			return $post;
		}
		foreach ($post[$mydbname] as $key=>$val) {
			if (strpos($key,'.') !== FALSE) {
				continue;
			}
			$rinfo = array();
			foreach ($dbconfig['model']['relationfield'] as $v) {
				if ($key == $v['field']) {
					$rinfo = $v;
					break;
				}
			}
			//多对多的情况
			if ($rinfo['relation'] == 'n:n') {
				//首先对数据进行格式化
				$val = trim($val,'|');
				$val = preg_replace('#\|+#','|',$val);
				$post[$mydbname][$key] = $val;
				//暂时保存起来，要更新到扩展表的，例如添加tag
				$extandData[] = array('dbtable'=>$rinfo['dbtable'],'dbfield'=>$rinfo['dbfield'],'data'=>$val);
			}
		}
		//更新数据到扩展表
		
		foreach ($extandData as $val) {
			list($_database,$_tablename) = explode('/',$val['dbtable'],2);
			$_db = Database::instance($_database);
			$keys = explode('|',$val['data']);
			$keys = array_unique($keys);
			$query = $_db->select($val['dbfield'])->in ($val['dbfield'],$keys)->get($_tablename)->result_assoc ();
			//存在的key
			$_keys = array_keys($query);
			//不存在的key
			$noExistsKeys = array_diff($keys,$_keys);
			//将这些不存在的数据插入到扩展表中
			foreach ($noExistsKeys as $v) {
				$_db->insert($_tablename,array($val['dbfield']=>$v));
			}
		}
		return $post;
	}
	
	/**
	 * 给扩展表插入数据
	 *
	 * @param array $fields 所有post过来的字段的数据 ，其中key是表名
	 * @param unknown_type $dbconfig
	 */
	protected function _insertExtendTable ($fields,$dbconfig) {
		$mainTable = $_POST['sys']['mydbname'];
//		$db = Database::instance($dbconfig['database']);
		foreach ($fields as $key=>$val) {
			if ($key == $mainTable) {
				continue;
			}
			
			//取得关联表的信息
			$relation = $dbconfig['model']['relationfield'];
			//给外键赋值
			$val[$relation[$key]['dbfield']] = $fields[$mainTable][$relation[$key]['field']];
			list($database,$tablename) =  explode('/',$relation[$key]['dbtable'],2);
			$_db = Database::instance($database);
			$_db->merge($tablename,$val);
		}
	}
	
	/**
	 * 查询扩展表的信息
	 *
	 * @param string $dbname 主表的名称
	 * @param int $infoid 主表的主键ID
	 * @param array $dbconfig 主表的配置文件
	 * @param Admin_Model_Core $adminmodel 模型
	 * @return array
	 */
	protected function _dealRealationTableInfo ($dbname,$infoid,$dbconfig,$adminmodel) {
		if (intval($infoid) <1) {
			return array();
		}
		//主表信息
		$myinfo = $adminmodel -> get_userdb_info($dbname,$infoid,'*');
		//扩展表信息
		$relationfield = $dbconfig['model']['relationfield'];
		if (!is_array($relationfield)) {
			return $myinfo;
		}
		//数据表重名有问题
		$relationTable = array_keys($relationfield);
		if (is_array($relationTable)) {
			foreach ($relationTable as $_dbname) {
				$info = $adminmodel -> get_userdb_info($_dbname,$infoid,'*');
				if (is_array($info)) {
					foreach ($info as $k=>$v) {
						$myinfo[$_dbname.'.'.$k] = $v;
					}
				}
			}
		}
		return $myinfo;
	}
	
	/**
	 * 更新主从表的信息
	 *
	 * @param string $dbname 主表的名称
	 * @param array $upfield 要更新的字段信息
	 * @param array $dbconfig 主表的配置
	 */
	protected function _updateTables ($dbname,$upfield,$dbconfig) {
		$post = $this->_getPostGrout($upfield,$dbconfig);
		$post[$dbname][$dbconfig['sys_field']['id']] = $_POST['sys']['id'];
		//更新主表
		list($database,$tablename) = explode('/',$dbname);
		$db = Database::instance($database);
		$db -> update($tablename,$post[$dbname] ,array($dbconfig['sys_field']['id'] => $_POST['sys']['id']));
		foreach ($post as $key=>$val) {
			if ($key == $dbname) {
				continue;
			}
			
			//取得关联表的信息
			$relation = $dbconfig['model']['relationfield'];
			//给外键赋值
			$relationField = $relation[$key]['field'];
			$relationFieldValue = $post[$dbname][$relationField];
			list($database,$tablename) = explode('/',$key,2);
			$_db = Database::instance($database);
			//更新关联表
			$_db->update($tablename,$val,array($relationField=>$relationFieldValue));
		}
	}
	
	/**
	 * 删除出具包括相关表的
	 *
	 * @param string $dbname 主表的名称
	 * @param array $dbconfig 主表的配置
	 * @param array $myId 要删除的Id
	 * @return int
	 */
	protected function _deleteTables ($dbname,$dbconfig,$myId) {
		if (!is_array($myId) || empty($myId)) {
			return ;
		}
		list($database,$tablename) = explode('/',$dbname);
		$db = Database::instance($database);
		$relation = $dbconfig['model']['relationfield'];
		if (is_array($relation)) {
			foreach ($relation as $key=>$val) {
				list($_database,$_tablename) = explode('/',$key,2);
				$_db = Database::instance($_database);
				$_db->in($val['dbfield'],$myId)->delete($_tablename);
			}
		}
		$status = $db->in($dbconfig['sys_field']['id'],$myId)->delete($tablename);
		return $status;
	}
	
	/**
	 * 处理数据的钩子
	 * @param $pid 主键
	 * @param $upfield 过滤后的原始数据
	 * @param $dbconfig 数据库配置
	 * @param $isadd 是否添加
	 */
	protected function _hook ($pid,$upfield,$dbconfig,$isadd) {
		$vfields = (array)MyqeeCMS::get_virtual_field();
		if (!is_array($vfields) || empty($vfields)) {
			return ;
		}
		$hookfields = array();
		foreach ($upfield as $key=>$val) {
			if (substr($key,0,1) == '#') {
				$hookfields[] = $key;
			}
		}
		if (empty($hookfields)) {
			return ;
		}

		$vfieldkeys = array_keys($vfields);
		foreach ($hookfields as $val) {
			if (!in_array($val,$vfieldkeys)) {
				continue;
			}
			
			$method = $vfields[$val]['infohook'];
			if (empty($method) || !is_callable($method)) {
				continue;
			}
			call_user_func_array($method,array($pid,$upfield,$dbconfig,$isadd));
		}
	}
}