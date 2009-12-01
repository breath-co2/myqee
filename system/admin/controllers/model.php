<?php
class Model_Controller_Core extends Controller {
//	protected $site_id = 0;
	protected $allow_db;
	function __construct() {
		parent::__construct ();
		Passport::chkadmin ();
//		$this -> site_id = (int)$_SESSION['now_site'];
		if ($_SESSION['admin']['dbset']!='-ALL-'){
			$this -> allow_db = explode(',',$_SESSION['admin']['dbset']);
		}else{
			$this -> allow_db = '-ALL-';
		}
	}
	
	public function index() {
		Passport::checkallow('model.list');
		$view = new View ( 'admin/model_list' );
		$this->db = Database::instance ();
		
//		if ($this -> site_id>0){
//			$where = array('siteid' => $this -> site_id);
//		}
		$result = $this -> db -> orderby('myorder','DESC') -> orderby ('id','DESC');
		if ($this -> allow_db!='-ALL-'){
			$result = $result -> in ('dbname',$this -> allow_db);
		}
		$view->set ( 'list',$result -> getwhere ( '[model]',$where ) -> result_array(FALSE) );
		$view->render ( TRUE );
	}
	
	public function del($id) {
		Passport::checkallow('model.del');
		$id = ( int ) $id;
		$this->db = Database::instance ();
		$model_info = $this -> db;
		if ($this -> allow_db!='-ALL-'){
			$model_info = $model_info -> in ('dbname',$this -> allow_db);
		}
		$model_info = $model_info -> getwhere ( '[model]', array ('id' => $id ) ) -> result_array ( FALSE );
		$model_info = $model_info [0];
		
		if (! $model_info) {
			MyqeeCMS::show_error ( Myqee::lang ( 'admin/model.error.momodel' ), true );
		}
		
		//MyqeeCMS::delconfig('model/model_'.$id);exit;
		if (count ( $this->db->delete ( '[model]', array ('id' => $id ) ) )) {
			MyqeeCMS::delconfig ( 'model/model_' . $id );
			MyqeeCMS::show_info ( Myqee::lang ( 'admin/model.info.delsuccess' ), true, 'refresh' );
		} else {
			MyqeeCMS::show_info ( Myqee::lang ( 'admin/model.info.nodelete' ), true );
		}
	}
	public function add() {
		$this->edit_step1 ();
	}
	
	public function edit_step1($id = 0) {
		if ($id>0){
			Passport::checkallow('model.add');
		}else{
			Passport::checkallow('model.add');
		}
		$view = new View ( 'admin/model_add_1' );
		$this->db = Database::instance ();
		$where = array ('isuse' => 1, 'ismemberdb' => 0 );
//		if ($this -> site_id>0){
//			$where['siteid'] = $this -> site_id;
//		}
		$dbinfo = $this->db->orderby ( 'myorder', 'DESC' );
		if ($this -> allow_db!='-ALL-'){
			$dbinfo = $dbinfo -> in ('name',$this -> allow_db);
		}
		
		$dbinfo = $dbinfo -> getwhere ( '[dbtable]', $where )->result_array ( FALSE );
		foreach ( $dbinfo as $value ) {
			$dblist [$value ['name']] = $value ['dbname'] . '(' . $value ['name'] . ')';
		}
		
		$adminmodel = new Admin_Model;

		$dbtable = $adminmodel -> get_dbtable_forselect();
		
		if ($id > 0) {
			$model_info = $this->db->getwhere ( '[model]', array ('id' => $id ) )->result_array ( FALSE );
			$model_info = $model_info [0];
			$view->model = $model_info;
		}
		$view->dblist = $dblist;
		$view->render ( TRUE );
	}
	
	public function copy($id = 0) {
		$this->IS_COPY = true;
		$this->edit ( $id );
	}
	public function edit($id = 0) {
		if ($this->IS_COPY){
			Passport::checkallow('model.add');
		}else{
			Passport::checkallow('model.edit');
		}
		$view = new View ( 'admin/model_add_2' );
		$adminmodel = new Admin_Model ( );
		$this->db = Database::instance ();
		
		if ($id > 0) {
			$getwhere = array ('id' => $id );
			$view->page_title = Myqee::lang ( 'admin/model.title.editmodel' );
		} else {
			$getwhere = NULL;
			$view->page_title = Myqee::lang ( 'admin/model.title.addmodel' );
		}
		if ($getwhere) {
			$model_info = $this->db->getwhere ( '[model]', $getwhere )->result_array ( FALSE );
			$model_info = $model_info [0];
			//模型中字段配置
			$model_config = unserialize ( $model_info ['config'] );
		}
		
		if ($this->IS_COPY) {
			//For Copy
			unset ( $model_info ['id'] );
			$model_info ['modelname'] = $model_info ['modelname'] . '_1';
			$model_info ['isdefault'] = 0;
			$view->set ( 'copyid', $id );
		}
		$view->set ( 'model', $model_info );
		
		$db_info = $adminmodel->get_db_array ( $model_info ['dbname'], $model_info ['dbname'] ? false : true );
		$db_info = $db_info [0];
		
		if (! $db_info ['name']) {
			MyqeeCMS::show_error ( Myqee::lang ( 'admin/model.error.no_db' ), false, Myqee::url ( 'model/edit_step1/' . $id ) );
		}
		list($database,$tablename) = explode('/',$db_info['name']);
		$_db = Database::instance($database);
		if (! $_db->table_exists ( $tablename )) {
			MyqeeCMS::show_error ( Myqee::lang ( 'admin/model.error.nothedbname2', $db_info ['name'] ), false, Myqee::url ( 'model/edit_step1/' . $id ) );
		}
		$db_info ['config'] = ( array ) unserialize ( $db_info ['config'] );
		$view->set ('the_sysfield',Tools::json_encode((array)$db_info ['config']['sys_field']));
		//处理关联表
		$_model_config = unserialize($db_info['modelconfig']);
		$relationfield = $_model_config['relationfield'];

		$field = (array)$model_config['field'];
		$field = $this->_addRelationToField($relationfield,$field);
		$view->set ( 'field', $this->_get_modelconfig_json ( $db_info ['config'], $db_info ['name'], $field ) );
		
		//数据接口
		$doinfomodel = $adminmodel -> get_apiclass_list('Info_Api');
		
		$view -> set('doinfomodel',$doinfomodel);
		$view -> set('adminlist',Tools::json_encode($model_config['adminlist']));
		$view -> set('sysadminlist',
			Tools::json_encode(
				array(
					'sys_commend' => array (
						'name' => '评论',
						'class' => 'btns',
					),
					'sys_view' => array (
						'name' => '查看',
						'class' => 'btns',
					),
					'sys_edit' => array (
						'name' => '修改',
						'class' => 'btns',
					),
					'sys_del' => array (
						'name' => '删除',
						'class' => 'btns',
					),
				)
			)
		);
		
		$view -> set('adminedit',$model_config['adminedit']);
	
		$view->render ( TRUE );
	}
	
	protected function _get_modelconfig_json($db_config, $dbname, $model_field) {
		//数据表设置中字段配置
		if (!is_array($db_config)){
			$db_config = ( array ) unserialize ( $db_config );
		}
		$db_field = $db_config ['field'];
		
		$field = array ();
		list($database,$tablename) = explode('/',$dbname);
		$fieldlist = ( array ) Database::instance($database)->list_fields ( $tablename, true ); //读取数据表字段
		$tmp_field = array_merge ( $model_field, $fieldlist );
		foreach ( $tmp_field as $key => $value ) {
			if ($fieldlist [$key] || substr($key,0,1) == '_' || substr($key,0,1) == '#') {
				$field [$key] = $model_field [$key] ? $model_field [$key] : $fieldlist [$key];
				if (substr($key,0,1) == '#') {
					$field [$key] ['disable'] = 1;
				}
				if (! $field [$key] ['dbname']) {
					$field [$key] ['dbname'] = $db_field [$key] ['dbname'];
				}
				if (! $field [$key] ['comment']) {
					$field [$key] ['comment'] = $db_field [$key] ['comment'];
				}
			}
		}
		
		//添加虚拟字段，目前作排序用
		$vconfig = MyqeeCMS::get_virtual_field();
		
		$vfields = array_keys($vconfig);
		foreach ($vfields as $val) {
			$field[$val] = array_merge((array)$field[$val],array('disable'=>1,'dbname'=>$vconfig[$val]['title']));
		}
		
		foreach ($field as $key=>$val) {
			if (substr($key,0,1) == '#') {
				if (!in_array($key,$vfields)) {
					//剔除不存在的虚拟字段
					unset($field[$key]);
				} else {
					//更新下虚拟字段的配置
					$field[$key]['dbname'] = $vconfig[$key]['title'];
				}
			}
		}
		return Tools::json_encode ( $field );
	}
	
	public function get_dbfield($database,$tablename){
		$adminmodel = new Admin_Model; 
		$dbfield = $adminmodel -> get_table_field($database.'/'.$tablename);
		echo Tools::json_encode($dbfield);
	}
	
	public function save($modelid = 0) {
		if ($modelid>0){
			Passport::checkallow('model.add');
		}else{
			Passport::checkallow('model.edit');
		}
		$modelid = ( int ) $modelid;
		
		if (! $_POST ['model'] ['modelname']) {
			MyqeeCMS::show_error ( Myqee::lang ( 'admin/model.error.nomodelname' ), true );
		}
		
		$adminmodel = new Admin_Model ( );
		$this->db = Database::instance ();
		
		$_POST ['model'] ['modelname'] = Tools::formatstr ( $_POST ['model'] ['modelname'] );
		
		$_POST ['model'] ['isuse'] == 0 or $_POST ['model'] ['isuse'] = 1;
		$_POST ['model'] ['isdefault'] == 1 or $_POST ['model'] ['isdefault'] = 0;
		
		$post = array (
			'modelname' => $_POST ['model'] ['modelname'], 
			'isuse' => $_POST ['model'] ['isuse'], 
			'isdefault' => $_POST ['model'] ['isdefault'], 
			'myorder' => ( int ) $_POST ['model'] ['myorder'],
		 );
		if (( int ) $_POST ['copyid'])
			$modelid = ( int ) $_POST ['copyid']; //for copy
		if ($modelid > 0) {
			//editor model
			$where = array ('id' => $modelid );
//			if ($this -> site_id>0){
//				$where['siteid'] = $this -> site_id;
//			}
			$model_info = $this -> db;
			if ($this -> allow_db!='-ALL-'){
				$model_info = $model_info -> in ('dbname',$this -> allow_db);
			}
			$model_info = $model_info = $this->db->getwhere ( '[model]', $where )->result_array ( FALSE );
			$model_info = $model_info [0];
			
			if (! $model_info ['dbname']) {
				MyqeeCMS::show_error ( Myqee::lang ( 'admin/model.error.nothedbname', '' ), true, 'goback' );
			}
			$model_config = ( array ) unserialize ( $model_info ['config'] );
		}
		if ($modelid > 0 && $_POST ['field']) {
			$db_info = $this->db->getwhere ( '[dbtable]', array ('name' => $model_info ['dbname'] ) )->result_array ( FALSE );
			$db_info = $db_info [0];
			if (! $db_info) {
				MyqeeCMS::show_error ( Myqee::lang ( 'admin/model.error.nothedbname', $model_info ['dbname'] ), true );
			}
			$db_config = (array) unserialize ( $db_info ['config'] );
			$tmpdoinfomodel = (array)get_class_methods('Info_Api');
			
			$adminlistmenu = $_POST['model']['adminlistmenu'];
//			print_r($adminlistmenu);
			if (!is_array($adminlistmenu))$adminlistmenu=array();
			
			$sysmenu = array(
				'sys_view'=>array('name'=>'查看'),
				'sys_commend'=>array('name'=>'评论'),
				'sys_edit'=>array('name'=>'修改','isuse'=>1),
				'sys_del'=>array('name'=>'删除','i	suse'=>1)
			);
			$adminlistmenu = array_merge($adminlistmenu,$sysmenu,$adminlistmenu);
			
			$classarr = array('btn','btns','btnss','btn2','btnl','bbtn');
			$adminlist = array();
			$c = 0;
			foreach ($adminlistmenu as $key=>$value){
				if (!$sysmenu[$key]){
					if (!$value['address'])continue;
					$key = '_'.$c;
					$c++;
					$usermenu = true;
				}else{
					$usermenu = false;
				}
				
				$adminlist[$key] = array(
					'name' => $value['name']?$value['name']:$sysmenu[$key]['name'],
					'isuse' => $value['isuse']?1:0,
					'class' => in_array($value['class'],$classarr)?$value['class']:'',
					'target' => $value['target']=='[other]'?$value['target2']:$value['target'],
				);
				if ($usermenu==true){
					$adminlist[$key]['address'] = $value['address'];
				}
			}
			
			$model_config = array(
				'dbname'=>$model_config['dbname'],
				'adminlist' => $adminlist,
				'adminedit' => array(
					'add' => in_array($_POST['model']['adminedit']['add'],$tmpdoinfomodel)?$_POST['model']['adminedit']['add']:null,
					'edit' => in_array($_POST['model']['adminedit']['edit'],$tmpdoinfomodel)?$_POST['model']['adminedit']['edit']:null,
					'del' => in_array($_POST['model']['adminedit']['del'],$tmpdoinfomodel)?$_POST['model']['adminedit']['del']:null,
				),
				'field' => $this->_get_modelconfig_array ( $db_config, ( array ) $_POST ['field'] ),
				'field_set' => $model_config['field_set'],
				'dbset' =>  $model_config['dbset'],
				'list' =>  $model_config['list'],
				'nolist' =>  $model_config['nolist'],
			);
			
			$post ['config'] = serialize ( $model_config );
			unset($c,$classarr,$adminlist,$adminlistmenu,$sysmenu,$usermenu);
		}
		
		$goEditPage = false;
		if ($_POST ['model'] ['dbname']) {
			//Edit dbanem
			if ($db_info ['name'] != $_POST ['model'] ['dbname']) {
				$db_info = $this->db->getwhere ( '[dbtable]', array ('name' => $_POST ['model'] ['dbname'] ) )->result_array ( FALSE );
				$db_info = $db_info [0];
				$goEditPage = true;
			}
			if ($db_info ['name'] != $_POST ['model'] ['dbname']) {
				MyqeeCMS::show_error ( Myqee::lang ( 'admin/model.error.nothedbname', $_POST ['model'] ['dbname'] ), true, 'goback' );
			}
			$post ['dbname'] = $_POST ['model'] ['dbname'];
		}
		
		if (( int ) $_POST ['copyid']) {
			//for copy
			unset ( $modelid );
			$post ['dbname'] = $model_info ['dbname'];
		}
		//FOR DATABASE
		if ($modelid > 0) {
			//UPDATE
			$status = $this->db->update ( '[model]', $post, array ('id' => $modelid ) );
			if ($post ['dbname'] && $model_info ['dbname'] != $post ['dbname']) {
				//更新栏目中数据表名称
				$this->db->update ( '[class]', array ('dbname' => $post ['dbname'] ), array ('modelid' => $model_info ['id'] ) );
			}
		} else {
			//INSERT
			if (! $post ['dbname']) {
				MyqeeCMS::show_error ( Myqee::lang ( 'admin/model.error.dbname_empty' ), true );
			}
			$status = $this->db->insert ( '[model]', $post );
			$modelid = $status->insert_id ();
		}
		$rows = count ( $status );
		//print infomation
		if ($post ['isdefault'] && $modelid > 0) {
			$status = $this->db->update ( '[model]', array ('isdefault' => 0 ), array ('id!=' => $modelid ) );
		}
		
		if ($rows > 0) {
			//保存模型配置文件
			$adminmodel->save_model_config ( $modelid );
			if ($goEditPage) {
				MyqeeCMS::show_info ( Myqee::lang ( 'admin/model.info.saveok_model_1' ), true, Myqee::url ( 'model/edit/' . $modelid ) );
			} else {
				MyqeeCMS::show_info ( Myqee::lang ( 'admin/model.info.saveok' ), true);
			}
		} else {
			MyqeeCMS::show_info ( Myqee::lang ( 'admin/model.info.noeditor' ), true );
		}
	}
	
	protected function _get_modelconfig_array($db_config, $postfield) {
		$modelconfig = array ();
		foreach ( $postfield as $key => $item ) {
			$modelconfig [$key] = array ();
			($item ['dbname'] && $item ['dbname'] != $db_config ['field'] [$key] ['dbname'] && $item ['dbname'] != $db_config ['field'] [$key] ['name']) and $modelconfig [$key] ['dbname'] = $item ['dbname'];
			($item ['comment'] && $item ['comment'] != $db_config ['field'] [$key] ['comment']) and $modelconfig [$key] ['comment'] = $item ['comment'];
			($item ['tag'] && $item ['tag'] != $thistag) and $thistag = $modelconfig [$key] ['tag'] = $item ['tag'];
			$item ['input'] and $modelconfig [$key] ['input'] = ( boolean ) $item ['input'];
			$item ['editor'] and $modelconfig [$key] ['editor'] = ( boolean ) $item ['editor'];
			$item ['view'] and $modelconfig [$key] ['view'] = ( boolean ) $item ['view'];
			$item ['post'] and $modelconfig [$key] ['post'] = ( boolean ) $item ['post'];
			$item ['notnull'] and $modelconfig [$key] ['notnull'] = ( boolean ) $item ['notnull'];
			$item ['caiji'] and $modelconfig [$key] ['caiji'] = ( boolean ) $item ['caiji'];
			$item ['search'] and $modelconfig [$key] ['search'] = ( boolean ) $item ['search'];
			$item ['jiehe'] and $modelconfig [$key] ['jiehe'] = ( boolean ) $item ['jiehe'];
			$item ['list'] and $modelconfig [$key] ['list'] = ( boolean ) $item ['list'];
			$item ['content'] and $modelconfig [$key] ['content'] = ( boolean ) $item ['content'];
		}
		return $modelconfig ;
	}
	
	/**
	 * @name 模型字段设置页面
	 * @param int 模型ID
	 * @param string 字段名
	 */
	public function editfield($modelid,$fieldname){
		Passport::checkallow('model.editfield');
		if (substr($fieldname,0,1) == '_') {
			MyqeeCMS::show_error(Myqee::lang('admin/model.error.fieldnamenoaccess'));
		}
		//读取模型配置文件
		$modelid = (int)$modelid;
		$this->db = Database::instance ();
		$where = array ('id' => $modelid );
//		if ($this -> site_id>0){
//			$where['siteid'] = $this -> site_id;
//		}
		$model_info = $this->db;
		if ($this -> allow_db!='-ALL-'){
			$model_info = $model_info -> in ('dbname',$this -> allow_db);
		}
		$model_info = $model_info -> getwhere ( '[model]', $where )->result_array ( FALSE );
		$model_info = $model_info [0];
		
		if (! $model_info ['dbname']) {
			MyqeeCMS::show_error ( Myqee::lang ( 'admin/model.error.nothedbname', $model_info ['dbname'] ), true );
		}
		//读取模型配置文件
		$modelconfig = ( array ) unserialize ( $model_info ['config'] );
		if (!is_array($modelconfig)){
			MyqeeCMS::show_error(Myqee::lang('admin/model.error.momodel'),true);
		}
		
		//读取数据表配置文件
		$dbname = $model_info['dbname'];
		$dbconfig = MyqeeCMS::config('db/'.$dbname);
//		if (!is_array($dbconfig) || ($this -> site_id>0 && $this -> site_id!=$dbconfig['siteid']) ){
		if (!is_array($dbconfig)){
			MyqeeCMS::show_error(Myqee::lang('admin/model.error.nothedbname',$dbname),FALSE,'goback');
		}
//		MyqeeCMS::print_r($dbconfig);
		if(is_array($modelconfig['dbset'][$fieldname])){
			$fieldset = $modelconfig['dbset'][$fieldname];
		}else{
			$fieldset = $dbconfig['edit'][$fieldname];
		}
		$tmpcandidate = '';
		if (is_array($fieldset['candidate'])){
			foreach ($fieldset['candidate'] as $key => $value){
				if (!empty($value))$tmpcandidate .= "\n".$key .((string)$value===(string)$key?'':'|'.$value);
			}
		}
		if (!empty($tmpcandidate))$tmpcandidate = substr($tmpcandidate,1);
		
		$field = array(
			'dbname' => $fieldset['title'],
			'name' => $fieldname,
			'isnull' => $fieldset['isnull']?$fieldset['isnull']:'YES',
			'size' => $fieldset['set']['size'],
			'rows' => $fieldset['set']['rows'],
			'format' => $fieldset['format'],
			'default' => $fieldset['default'],
			'getcode' => $fieldset['getcode'],
			'candidate' => $tmpcandidate,
			'inputtype' => $fieldset['type'],
			'islist' => $modelconfig['nolist'][$fieldname]?'0':($modelconfig['list'][$fieldname]?'1':''),
		);
		
		
		$listset = is_array($modelconfig['list'][$fieldname])?$modelconfig['list'][$fieldname]:is_array($dbconfig['list'][$fieldname]);
		if ( is_array($listset) ){
			$field = array_merge($field,$listset);
			if (is_array($field['boolean'])){
				foreach ($field['boolean'] as $key => $value){
					if (!empty($value))$tmpboolean .= "\n".$key .((string)$value===(string)$key?'':'|'.$value);
				}
				$field['boolean'] = $tmpboolean;
			}
		}
		
		if ($modelconfig['field'][$fieldname]['dbname']){
			$field['dbname'] = $modelconfig['field'][$fieldname]['dbname'];
		}
		if ($modelconfig['field'][$fieldname]['comment']){
			$field['comment'] = $modelconfig['field'][$fieldname]['comment'];
		}else{
			$field['comment'] = $dbconfig['edit'][$fieldname]['description'];
		}
		
		
		$view = new View('admin/model_editfield');

		//MyqeeCMS::print_r($dbconfig);
		$view -> set('field',$field);
		$view -> set('dbname',$dbname);
		$view -> set('modelid',$modelid);
		$view -> set('islist_normal',$dbconfig['list'][$fieldname]?'列出':'不列出');
		
		//列表输出转换函数API
		$adminmodel = new Admin_Model();
		$view->set ( 'fielddocode', $adminmodel -> get_apiclass_list('Field_list_Api',array(''=>'默认')) );

		$view->set ( 'fieldgetcode', $adminmodel -> get_apiclass_list('Field_get_Api',array(''=>'默认')) );
		
		$view -> render(TRUE);
	}
	
	public function modelfieldsave($modelid,$fieldname) {
		Passport::checkallow('model.editfield');
		$modelid = (int)$modelid;
		$this->db = Database::instance ();
		$where = array ('id' => $modelid );
//		if ($this -> site_id>0){
//			$where['siteid'] = $this -> site_id;
//		}
		$model_info = $this->db;
		if ($this -> allow_db!='-ALL-'){
			$model_info = $model_info -> in ('dbname',$this -> allow_db);
		}
		$model_info = $model_info -> getwhere ( '[model]', $where )->result_array ( FALSE );
		$model_info = $model_info [0];
		
		if (! $model_info ['dbname']) {
			MyqeeCMS::show_error ( Myqee::lang ( 'admin/model.error.nothedbname', '' ), true );
		}
		//读取模型配置文件
		$modelconfig = ( array ) unserialize ( $model_info ['config'] );
		if (!is_array($modelconfig)){
			MyqeeCMS::show_error(Myqee::lang('admin/model.error.momodel'),true);
		}
		$fieldset = $modelconfig['dbset'][$fieldname];
	
		//读取数据表配置文件
		$dbname = $model_info['dbname'];
		$dbconfig = MyqeeCMS::config('db/'.$dbname);
//		if (!is_array($dbconfig) || ($this -> site_id>0 && $this -> site_id!=$dbconfig['siteid']) ){
		if (!is_array($dbconfig)){
			MyqeeCMS::show_error(Myqee::lang('admin/model.error.nothedbname',$dbname),true);
		}
		if (!is_array($fieldset)){
			if (!($fieldset = $dbconfig['edit'][$fieldname])){
				MyqeeCMS::show_error(Myqee::lang('admin/model.error.nothefieldname'),TRUE);
			}
		}

		
		$post = $_POST ['field'];
		$data = array();
		
		//更新表单名和录入说明
		if ( !empty($post ['dbname'] )){
			$modelconfig['field'][$fieldname]['dbname'] = Tools::formatstr($post['dbname']);
		}
		if ( !empty($post ['comment'] )){
			$modelconfig['field'][$fieldname]['comment'] = Tools::formatstr($post['comment']);
		}

		$data = array(
			'type' => $post['inputtype'],
			'set' => array(
				'class' => $post['class'],
				'size' => $post['size'],
				'rows' => $post['rows'],
				'other' => $post['other'],
			),
			'default' => $post['default'],
			'getcode' => $post['getcode'],
			'candidate' => $post['candidate'],
			'format' => $post['format'],
		);
		//格式化类型
		if ($post['format'] && in_array($post['format'],array('string','time','date','int','html','alt','filepath','filename') ) ){
			$data['format'] = $post['format'];
		}
		
		//处理候选值
		if ($post ['getcode']){
			$tmpdoinfomodel = (array)get_class_methods('Field_get_Api');
			if (in_array($post ['getcode'],$tmpdoinfomodel)){
				$data_list['getcode'] = $post['getcode'];
			}
			$data['candidate'] = $post['candidate'];
		}elseif (!empty($post['candidate'])){
			$tmpvalue = explode("\n",$post['candidate']);
			foreach ($tmpvalue as $value){
				if (empty($value))continue;
				$value = explode('|',$value);
				$thekey = array_shift($value);
				if (count($value)>0){
					$value = join('|',$value);
				}else{
					$value = $thekey;
				}
				$tmpvalue1[$thekey] = $value;
			}
			$tmpvalue = $tmpvalue1;
			unset($tmpvalue1);
			$data['candidate'] = $tmpvalue;
		}
		unset($tmpvalue,$tmpvalue1);
		
		//字段栏目信息列出设置
		if ($post['islist']==1){
			$data_list = array();
			$data_list['title'] = $modelconfig['field'][$fieldname]['dbname'];
			$post['width'] and $data_list['width']=$post['width'];
			$post['align'] and $data_list['align']=$post['align'];
			$post['tdclass'] and $data_list['tdclass']=$post['tdclass'];
			
			if ($post ['docode']){
				$tmpdoinfomodel = (array)get_class_methods('Field_list_Api');
				if (in_array($post ['docode'],$tmpdoinfomodel)){
					$data_list ['docode'] = $post ['docode'];
				}
				$post ['boolean'] and $data_list ['boolean'] = $post ['boolean'];
			}else{
				if ($post['boolean']){
					$tmpvalue = explode("\n",$post['boolean']);
					foreach ($tmpvalue as $value){
						$value = explode('|',$value);
						$thekey = array_shift($value);
						if (count($value)>0){
							$value = join('|',$value);
						}else{
							$value = $thekey;
						}
						$tmpvalue1[$thekey] = $value;
					}
					$tmpvalue = $tmpvalue1;
					unset($tmpvalue1);
					$data_list['boolean'] = $tmpvalue;
					unset($tmpvalue);
				}
			}
			
			$modelconfig['list'][$fieldname] = $data_list;
			unset($modelconfig['nolist'][$fieldname]);
		}elseif($post['islist']==0){
			$modelconfig['nolist'][$fieldname] = $fieldname;
			unset($modelconfig['list'][$fieldname]);
		}else{
			unset($modelconfig['list'][$fieldname]);
			unset($modelconfig['nolist'][$fieldname]);
		}
		
		if (count($data)>0)$modelconfig['dbset'][$fieldname] = $data;
		
		$updata = array('config'=>serialize($modelconfig));

		//UPDATE
		$status = $this->db->update ( '[model]', $updata, array ('id' => $modelid ) );
		$rows = count ( $status );
		
		if ($rows > 0) {
			$adminmodel = new Admin_Model();
			//保存模型配置文件
			$adminmodel->save_model_config ( $modelid );
			
			MyqeeCMS::show_info ( Myqee::lang ( 'admin/model.info.saveok' ), true );
		
		} else {
			MyqeeCMS::show_info ( Myqee::lang ( 'admin/model.info.noeditor' ), true );
		}
	}
	
	public function clearmodel_fieldset($modelid,$fieldname) {
		Passport::checkallow('model.editfield');
		$modelid = (int)$modelid;
		$this->db = Database::instance ();
		$model_info = $this->db->getwhere ( '[model]', array ('id' => $modelid ) )->result_array ( FALSE );
		$model_info = $model_info [0];
		
		if (! $model_info ['dbname']) {
			MyqeeCMS::show_error ( Myqee::lang ( 'admin/model.error.nothedbname', '' ), true );
		}
		//读取模型配置文件
		$modelconfig = ( array ) unserialize ( $model_info ['config'] );
		if (!is_array($modelconfig)){
			MyqeeCMS::show_error(Myqee::lang('admin/model.error.momodel'),true);
		}
		unset($modelconfig['dbset'][$fieldname]);
		unset($modelconfig['list'][$fieldname]);
		unset($modelconfig['nolist'][$fieldname]);
		
		$updata = array('config'=>serialize($modelconfig));

		//UPDATE
		$where = array ('id' => $modelid );
//		if ($this -> site_id>0){
//			$where['siteid'] = $this -> site_id;
//		}
		$status = $this->db->update ( '[model]', $updata, $where );
		$rows = count ( $status );
		
		if ($rows > 0) {
			$adminmodel = new Admin_Model();
			//保存模型配置文件
			$adminmodel->save_model_config ( $modelid );
			
			MyqeeCMS::show_info ( Myqee::lang ( 'admin/model.info.saveok' ), true );
		}else {
			MyqeeCMS::show_info ( Myqee::lang ( 'admin/model.info.noeditor' ), true );
		}
	}
	
	public function output($allid, $key = '') {
		Passport::checkallow('model.output');
		$this->db = Database::instance ();
		
		$allid = Tools::formatids ( $allid, false );
		$results = $this->db->from ( '[model]' );
//		if ($this -> site_id>0){
//			$results = $results -> where ('siteid',$this -> site_id);
//		}
		$results = $results -> in ( 'id', $allid )->orderby ( 'id' )->get ()->result_array ( false );
		
		if (count ( $results ) == 0) {
			MyqeeCMS::show_info ( Myqee::lang ( 'admin/model.info.nooutmodel' ), true );
		}
		$mydata = Tools::info_encryp ( $results, $key ,true );
		if (! $mydata) {
			MyqeeCMS::show_info ( Myqee::lang ( 'admin/model.info.nooutdb' ), true );
		}
		download::force ( './', $mydata, 'model.txt' );
	}
	
	public function inputmodel() {
		$view = new View ( 'admin/model_input' );
		if ($this -> allow_db!='-ALL-'){
			MyqeeCMS::show_info ( '您没有管理所有数据表的权限，不能导入数据表！');
		}
		$view->render ( TRUE );
	}
	
	public function input() {
		Passport::checkallow('model.output');
		if ($this -> allow_db!='-ALL-'){
			MyqeeCMS::show_info ( '您没有管理所有数据表的权限，不能导入数据表！');
		}
		//上传方式
		$thedata = $this->_getinputdata ();
		
		$this->db = Database::instance ();
		$adminmodel = new Admin_Model ( );
		$inputerr = 0;
		$inputok = 0;
		foreach ( $thedata as $item ) {
			$data = array (
				'modelname' => $item ['modelname'], 
				'isuse' => $item ['isuse'], 
				'myorder' => ( int ) $item ['myorder'], 
				'dbname' => preg_replace ( "/[^\w]/", '', $item ['dbname'] ), 
				'config' => $item ['config'], 
				'isdefault' => 0 
			);
//			if ($this -> site_id>0){
//				$data['siteid'] = $this -> site_id;
//			}
			
			$status = $this->db->insert ( '[model]', $data );
			if (count ( $status )) {
				//保存文件
				$adminmodel->save_model_config ( $status->insert_id () );
				
				//输出提示信息
				$inputok += 1;
			}
		}
		
		$showinfo = Myqee::lang ( 'admin/model.info.inputok', $inputok );
		MyqeeCMS::show_info ( $showinfo, true, 'goback' );
	}
	
	protected function _savemodelconfig() {
		$adminmodel = new Admin_Model ( );
		$adminmodel->save_model_config ();
	}
	
	protected function _getinputdata() {
		$key = $_POST ['key'];
		$thedata = $_POST ['data'];
		
		if (empty ( $thedata ) && $_FILES ['upload'] ['size'] == 0) {
			MyqeeCMS::show_error ( Myqee::lang ( 'admin/model.error.inputdataempty' ), true );
		}
		if ($_FILES ['upload'] ['tmp_name']) {
			$tmpfile = $_FILES ['upload'] ['tmp_name'];
			if ($_FILES ['upload'] ['size'] < 5000000) { //只操作5MB以内的文件
				if (! $thedata = @file_get_contents ( $_FILES ['upload'] ['tmp_name'] )) {
					MyqeeCMS::show_error ( Myqee::lang ( 'admin/model.error.inputreadfileerror' ), true );
				}
				//反解文件
				$thedata = Tools::info_uncryp ( $thedata, $key,true );
			} else {
				MyqeeCMS::show_error ( Myqee::lang ( 'admin/model.error.inputerrorsize' ), true );
			}
		} else {
			if (strlen ( $thedata ) < 5000000) {
				//反解文件
				$thedata = Tools::info_uncryp ( $thedata, $key,true );
			} else {
				MyqeeCMS::show_error ( Myqee::lang ( 'admin/model.error.inputerrorsize' ), true );
			}
		}
		if ($thedata === - 1) {
			MyqeeCMS::show_error ( Myqee::lang ( 'admin/model.error.inputtplbyedit' ), true );
		}
		
		if (! is_array ( $thedata )) {
			MyqeeCMS::show_error ( Myqee::lang ( 'admin/model.error.decodeerror' ), true );
		}
		return $thedata;
	}
	
	public function renewfiles($theid=0){
		Passport::checkallow('model.renewfiles');
		$theid = (int)$theid;
		//$nowtplgroup = $this -> session -> get('now_tlpgroup');
		//if (!$nowtplgroup)$nowtplgroup = 'default';
		//MyqeeCMS::show_info($nowtplgroup,TRUE);
		
		$this -> db = Database::instance();
		$results = $this -> db -> from('[model]');
		if ($theid >0 ){
			$results = $results -> where ('id',$theid);
		}
		
		$results = $results -> get() -> result_array(false);
		$save_ok = $save_error = 0;
		if (is_array($results)){
			$adminmodel = new Admin_Model();
			foreach ($results as $item){
				//$item['config'] = unserialize($item['config']);
				if ( $adminmodel -> save_model_config ( $item ) ){
					$save_ok += 1;
				}else{
					$save_error += 1;
				}
			}
		}
		
		$msg = '执行完毕，共重新生成文件'.$save_ok.'个，执行失败：'.$save_error.'！';
		if ($_GET['type']=='auto'){
			echo '<script>parent.showinfo("model","'.$msg.'");document.location.href="'.ADMIN_IMGPATH.'/admin/block.html";</script>';
		}else{
			MyqeeCMS::show_info($msg,true);
		}
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	///////////////////////////////////数据表、、、、、、、
	
	public function dblist($onlymemberdb = false) {
		Passport::checkallow('model.dblist');
		$view = new View ( 'admin/db_list' );
		$this->db = Database::instance ();
		if ($onlymemberdb == 'member') {
			$where = array ('ismemberdb' => 1 );
		} else {
			$where = null;
		}
//		if ($this -> site_id>0){
//			$where['siteid'] = $this -> site_id;
//		}
		$db_info = $this -> db;
		if ($this -> allow_db!='-ALL-'){
			$db_info = $db_info -> in ('name',$this -> allow_db);
		}
		$view->set ( 'list', $db_info -> orderby ( 'myorder', 'DESC' )->orderby ( 'name', 'DESC' )->orderby ( 'id', 'DESC' )->getwhere ( '[dbtable]', $where )->result_array ( FALSE ) );
		$view->render ( TRUE );
	}
	
	public function dbadd() {
		$this->dbedit ();
	}
	public function dbcopy($id = 0) {
		$this->iscopy = true;
		$this->dbedit ( $id );
	}
	public function dbedit($id = 0) {
		if ($id>0 && !$this->iscopy){
			Passport::checkallow('model.dbedit');
		}else{
			Passport::checkallow('model.dbadd');
		}
		$id = ( int ) $id;
		
		$view = new View ( 'admin/db_add' );
		
		$adminmodel = new Admin_Model ();
		
		if ($id) {
			$db_info = $adminmodel->get_db_array ( $id ,FALSE,FALSE);
			$db_info = $db_info [0];
			
			if (! $db_info) {
				MyqeeCMS::show_error(Myqee::lang ('admin/model.error.notheiddb' ), false, 'goback');
			}
			list($database,$tablename) = explode('/',$db_info['name']);
			$db = Database::instance($database);
			if (!$db->table_exists($tablename)) {
				MyqeeCMS::show_error('数据表不存在!',false,'goback');
			}
		}
		$model_config = ( array ) unserialize ( $db_info ['modelconfig'] );
		
		//数据接口
		$doinfomodel = $adminmodel -> get_apiclass_list('Info_Api');
		
		$view -> set('doinfomodel',$doinfomodel);
		$view -> set('adminlist',Tools::json_encode($model_config['adminlist']));
		$view -> set('sysadminlist',
			Tools::json_encode(
				array(
					'sys_commend' => array (
						'name' => '评论',
						'class' => 'btns',
					),
					'sys_view' => array (
						'name' => '查看',
						'class' => 'btns',
					),
					'sys_edit' => array (
						'name' => '修改',
						'class' => 'btns',
					),
					'sys_del' => array (
						'name' => '删除',
						'class' => 'btns',
					),
				)
			)
		);
		$view->set ( 'adminedit',$model_config['adminedit']);
		if ($id > 0) {
			//关联表
			$_relationfield = $model_config['relationfield'];
			if (is_array($_relationfield)) {
				sort($_relationfield);
			}else {
				$_relationfield = array();
			}
			
			$view->set('relationfield',Tools::json_encode($_relationfield));
			$view->set ( 'field', $this->_get_modelconfig_json ( $db_info ['config'], $db_info['name'], (array)$model_config['field'] ) );
		} else {
			$view->set('relationfield','[]');
			$view->set ( 'field', '[]' );
		}
		
		$dblist = $adminmodel ->get_dbtable_forselect();
		if ($id > 0 && !$this->iscopy) {
			unset($dblist['forselect'][$tablename]);
			$view -> set('fieldlist',$dbfield = $adminmodel -> get_table_field($db_info['name']) );
		}
		$view->set ( 'dblist', $dblist['forselect'] );
		
		if ($this->iscopy) {
			$view->set ( 'copyid', true );
			$db_info ['dbname'] .= '_1';
			$db_info ['name'] .= '_1';
			$db_info ['isdefault'] = 0;
		}
		$db_info['name'] = $tablename;
		$view->set ( 'db', $db_info );
		$dbdatabase = '';
		if ($id == 0 || $this->iscopy) {
			$options = array();
			foreach (array_keys(Myqee::config('database')) as $val) {
				$options[$val] = $val;
			}
			$dbdatabase = form::dropdown('db[database]',$options);
		}else {
			$_database = empty($database) ? 'default' : $database;
			$dbdatabase = form::input('db[database]',$_database,'readonly');
		}
		$view->set('dbdatabase',$dbdatabase);
		$view->render ( TRUE );
	}
	
	public function dbsavecopy($id = 0) {
		$this->copyid = ( int ) $id;
		$this->dbsave ();
	}
	
	public function dbsave($id = 0) {
		$id = ( int ) $id;
		if ($id>0 && !$this->copyid){
			Passport::checkallow('model.dbedit');
		}else{
			Passport::checkallow('model.dbadd');
		}
//		print_r($_POST);die();
		$post = $_POST ['db'];
		$post['name'] = trim($post['name']);
		if (preg_match ( "/[^a-zA-Z0-9_]+/", $post ['name'] ) || empty($post ['name'])) {
			MyqeeCMS::show_error ( Myqee::lang ( 'admin/model.error.dbnameerror' ), true );
		}
		
		$this->db = Database::instance ();
		
		$data = array (
			'dbname' => $post ['dbname'], 
			'name' => Tools::formatstr ( $post ['database'] ).'/'.Tools::formatstr ( $post ['name'] ), 
			'myorder' => ( int ) $post ['myorder'], 
			'isuse' => ( int ) $post ['isuse'] == 0 ? 0 : 1, 
			'isdefault' => $post ['isdefault'] ? 1 : 0, 
			'ismemberdb' => $post ['ismemberdb'] ? 1 : 0, 
			'readbydbname' => $post ['readbydbname'] ? 1 : 0, 
			'content' => Tools::formatstr ( $post ['content'] ) ,
		);
		if (! $data ['dbname'])
			MyqeeCMS::show_error ( Myqee::lang ( 'admin/model.error.dbname_empty' ), true );
		if (! $data ['name'])
			MyqeeCMS::show_error ( Myqee::lang ( 'admin/model.error.name_empty' ), true );
		$_database = $post['database'];
		$_table = $post['name'];
		//修改
		if ($id > 0) {
			$where = array ('id' => $id );
//			if ($this -> site_id>0){
//				$where['siteid'] = $this -> site_id;
//			}
			$db_info = $this -> db;
			if ($this -> allow_db!='-ALL-'){
				$db_info = $db_info -> in ('name',$this -> allow_db);
			}
			$dbinfo = $db_info -> getwhere ( '[dbtable]', $where )->result_array ( FALSE );
			$dbinfo = $dbinfo [0];
			if (! $dbinfo) {
				MyqeeCMS::show_error ( Myqee::lang ( 'admin/model.error.notheiddb' ), true );
			}
			//检查是否已存在的数据表
			if ($data ['name'] != $dbinfo ['name']) {
				$olddb = $this->db->getwhere ( '[dbtable]', array ('name' => $data ['name'] ) )->result_array ( FALSE );
				if ($olddb [0]) {
					MyqeeCMS::show_error ( Myqee::lang ( 'admin/model.error.hasdb' ), true );
				}
			}
			//修改表名称
			if ($dbinfo ['name'] != $data['name']) {
				if ($this -> allow_db!='-ALL-'){
					MyqeeCMS::show_error ( '您没有管理所有数据表的权限，不能修改数据表名！',TRUE);
				}
				//多数据需调用其所属的数据库
				$_db = Database::instance($post['database']);
				$_oldable = preg_replace('#.*?/#','',$dbinfo['name']);
				$sql = 'RENAME TABLE `' . $_db->table_prefix () . $_oldable . '` TO `' . $_db->table_prefix () . $post ['name'] . '`';
				$_db->query ( $sql );
				//更新栏目中数据表名称
				$this->db->update ( '[class]', array ('dbname' => $data ['name'] ), array ('dbname' => $dbinfo ['name'] ) );
				//更新模型中数据表名称
				$this->db->update ( '[model]', array ('dbname' => $data ['name'] ), array ('dbname' => $dbinfo ['name'] ) );
				
				$changename = $dbinfo ['name'];
			}
			
			//处理数据表模型
			$data['usedbmodel'] = $post['usedbmodel']?1:0;
			$dbinfo ['modelconfig'] = unserialize ( $dbinfo ['modelconfig'] );
			$tmpdoinfomodel = (array)get_class_methods('Info_Api');
			
			
			$adminlistmenu = $_POST['db']['adminlistmenu'];
			if (!is_array($adminlistmenu))$adminlistmenu=array();
			
			$sysmenu = array(
				'sys_view'=>array('name'=>'查看'),
				'sys_commend'=>array('name'=>'评论'),
				'sys_edit'=>array('name'=>'修改','isuse'=>1),
				'sys_del'=>array('name'=>'删除','isuse'=>1)
			);
			$adminlistmenu = array_merge($adminlistmenu,$sysmenu,$adminlistmenu);
			
			$classarr = array('btn','btns','btnss','btn2','btnl','bbtn');
			$adminlist = array();
			$c = 0;
			foreach ($adminlistmenu as $key=>$value){
				if (!$sysmenu[$key]){
					if (!$value['address'])continue;
					$key = '_'.$c;
					$c++;
					$usermenu = true;
				}else{
					$usermenu = false;
				}
				$adminlist[$key] = array(
					'name' => $value['name']?$value['name']:$sysmenu[$key]['name'],
					'isuse' => $value['isuse']?1:0,
					'class' => in_array($value['class'],$classarr)?$value['class']:'',
					'target' => $value['target']=='[other]'?$value['target2']:$value['target'],
				);
				if ($usermenu==true){
					$adminlist[$key]['address'] = $value['address'];
				}
			}
			$field = $this->_get_modelconfig_array ( (array)$dbinfo['modelconfig']['field'] , (array)$_POST ['field'] );
			//将扩展表添加到 字段中
			$relationfield = $this->_dealRelationField($_POST['db']['relationfield']);
			$field = $this->_addRelationToField($relationfield,$field);
//			print_r($field);
			$dbinfo ['modelconfig'] = array(
				'dbname' => $post ['name'],
				'adminlist' => $adminlist,
				'adminedit' => array(
					'add' => in_array($post['adminedit']['add'],$tmpdoinfomodel)?$post['adminedit']['add']:null,
					'edit' => in_array($post['adminedit']['edit'],$tmpdoinfomodel)?$post['adminedit']['edit']:null,
					'del' => in_array($post['adminedit']['del'],$tmpdoinfomodel)?$post['adminedit']['del']:null,
				),
				'field' => $field,
				//处理扩展表
				'relationfield' => $relationfield,
			);
			
			unset($c,$classarr,$adminlist,$adminlistmenu,$sysmenu,$usermenu);

			$data ['modelconfig'] = serialize($dbinfo ['modelconfig']);
			if ($data ['modelconfig'] != $dbinfo ['modelconfig'] || $data['usedbmodel'] != $dbinfo['usedbmodel'] || $data['readbydbname'] != $dbinfo['readbydbname'] ) {
				//更新CONFIG文件
				$changemodelconfig = true;
			}
			
			//更新数据表设置
			$status = $this -> db -> update ( '[dbtable]', $data, array ('id' => $id ) );
		} else {
			//检查是否已存在的数据表
			$olddb = $this->db->getwhere ( '[dbtable]', array ('name' => $data ['name'] ) )->result_array ( FALSE );
			if ($olddb [0]) {
				MyqeeCMS::show_error ( Myqee::lang ( 'admin/model.error.hasdb' ), true );
			}
			$_db = Database::instance($post['database']);
			if ($this->copyid > 0) {
				//复制数据表
				$where = array ('id' => $this->copyid );
//				if ($this -> site_id>0){
//					$where['siteid'] = $this -> site_id;
//				}
				$dbinfo = $this -> db;
				if ($this -> allow_db!='-ALL-'){
					$db_info = $db_info -> in ('name',$this -> allow_db);
				}
				$dbinfo = $dbinfo -> getwhere ( '[dbtable]', $where )->result_array ( FALSE );
				$dbinfo = $dbinfo [0];
				if (! $dbinfo) {
					MyqeeCMS::show_error ( Myqee::lang ( 'admin/model.error.notheiddb' ), true );
				}
				$data ['config'] = $dbinfo ['config'];
				list($_database,$_tablename) = explode('/',$dbinfo['name'],2);
				$_tdb = Database::instance($_database);
				$sql = 'SHOW CREATE TABLE `' . $_tdb->table_prefix () . $_tablename . '`';
				$rs = $_tdb->query ( $sql )->result_array ( FALSE );
				$createsql = $rs [0] ['Create Table'];
				$olddbname = $rs [0] ['Table'];
				if (! $createsql || ! $_tablename) {
					MyqeeCMS::show_error ( Myqee::lang ( 'admin/model.error.showcreatetableerror' ), true );
				}
				$createsql = str_replace ( 'CREATE TABLE `' . $olddbname . '`', 'CREATE TABLE `' . $_db->table_prefix () . $_table . '`', $createsql );
			} else {
				$data ['config'] = serialize ( array ('sys_field'=> array('id'=>'id'),'field' => array ('id' => array ('name' => 'id', 'dbname' => 'ID', 'autoset' => 'id', 'iskey' => 1, 'isonly' => 1, 'isnonull' => 1, 'type' => 'int', 'length' => 11, 'inputtype' => 'hidden', 'islist' => 1, 'width' => 60, 'align' => 'center', 'tdclass' => 'td1' ) ) ) );
				$createsql = 'CREATE TABLE `' . $_db->table_prefix () . $_table . '` (`id` int(11) NULL auto_increment, PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=' . MyqeeCMS::config ( 'database.'.$post['database'].'.character_set' );
			}
			//插入数据
			$status = $this->db->insert ( '[dbtable]', $data );
			//表不存在时创建
			//支持多库后必须换个db连接
			
			if(!$_db->table_exists($_table)){
				$_db->query ( $createsql );
			}
			$id = $status->insert_id ();
			$changename = false;
		}
		if ($id > 0 && ($this->copyid > 0 || $changename || $changemodelconfig)) {
			//save config
			$adminmodel = new Admin_Model ( );
			$adminmodel->save_db_config ( $id );
			if ($changename) {
				MyqeeCMS::delconfig ( 'db/' . $changename );
			}
		}
		if ($data ['isdefault'] && $id > 0) {
			$this->db->update ( '[dbtable]', array ('isdefault' => 0 ), array ('id!=' => $id ) );
		}
		if (count($status)){
			MyqeeCMS::show_info ( Myqee::lang ( 'admin/model.info.saveok' ), true, 'goback' );
		}else{
			MyqeeCMS::show_info ( '没有修改数据！', true );
		}
	}
	
	public function dbdel($id) {
		Passport::checkallow('model.dbdel');
		$id = ( int ) $id;
		if (! $id)
			MyqeeCMS::show_error ( Myqee::lang ( 'admin/model.error.parametererror' ), true );
		
		$this->db = Database::instance ();
		$where = array ('id' => $id );
//		if ($this -> site_id>0){
//			$where['siteid'] = $this -> site_id;
//		}
		$dbinfo = $this -> db;
		if ($this -> allow_db!='-ALL-'){
			$db_info = $db_info -> in ('name',$this -> allow_db);
		}
		$dbinfo = $dbinfo -> getwhere ( '[dbtable]', $where )->result_array ( FALSE );
		$dbinfo = $dbinfo [0];
		if (! $dbinfo) {
			MyqeeCMS::show_error ( Myqee::lang ( 'admin/model.error.notheiddb' ), true );
		}
		
		list($database,$tablename) = explode('/',$dbinfo['name'],2);
		$_db = Database::instance($database);
		if ($_db->table_exists ( $tablename )) {
			$sql = 'DROP TABLE `' . $_db->table_prefix () . $tablename . '`';
			$_db->query ( $sql );
		}
		if (count ( $this->db->delete ( '[dbtable]', array ('id' => $id ) ) )) {
			MyqeeCMS::delconfig ( 'db/' . $dbinfo ['name'] );
			MyqeeCMS::show_info ( Myqee::lang ( 'admin/model.info.delsuccess' ), true, 'refresh' );
		} else {
			MyqeeCMS::show_info ( Myqee::lang ( 'admin/model.info.nodelete' ), true );
		}
	}
	public function dbfield($id) {
		Passport::checkallow('model.dbfieldlist');
		if (! ($id > 0)) {
			header ( "loacation:" . Myqee::url ( 'model/dblist' ) );
			exit ();
		}
		$adminmodel = new Admin_Model ( );
		$this->db = Database::instance ();
		$db_info = $adminmodel->get_db_array ( $id );
		$db_info = $db_info [0];
		
		if (! $db_info) {
			MyqeeCMS::show_error ( Myqee::lang ( 'admin/model.error.notheiddb' ), false, 'goback' );
		}
		$db_info ['config'] = unserialize ( $db_info ['config'] );
		$db_field = ( array ) $db_info ['config'] ['field'];
		list($database,$tablename) = explode('/',$db_info['name'],2);
		$_db = Database::instance($database);
		if (! $_db->table_exists ( $tablename )) {
			MyqeeCMS::show_error ( Myqee::lang ( 'admin/model.error.nothedbname', $tablename ), false, 'goback' );
		}
		
		$field = array ();
		$fieldlist = $this->_get_fieldlist_array ( $tablename  ,$database);
		
		//print_r($fieldlist);exit;
		$db_field_tmp = array_merge ( $db_field, $fieldlist );
		foreach ( $db_field_tmp as $key => $value ) {
			if ($fieldlist [$key]) {
				$field [$key] = $db_field [$key];
				$field [$key] ['type'] = $fieldlist [$key] ['type'];
				$field [$key] ['comment'] = $db_field [$key] ['comment'] ? $db_field [$key] ['comment'] : $fieldlist [$key] ['comment'];
				/*
				$field[$key] = array(
					'name' => $key,
					'dbname' => $db_field[$key]['dbname'],
					'type' => $fieldlist[$key]['type'],
					'comment' => $db_field[$key]['comment']?$db_field[$key]['comment']:$fieldlist[$key]['comment'],
					'islist' => $db_field[$key]['islist'],
				);*/
			//$db_field[$key]['name'] and $field[$key]['name'] = $db_field[$key]['name'];
			/*	$field[$key]['name'] or $field[$key]['name'] = $key;
				$field[$key]['type'] = $value['type']=='string'?($value['length']?'INT':'TEXT'):strtoupper($value['type']);
				if (!$value['length'] && $value['max'] == 127){
					$field[$key]['length'] = 3;
					$field[$key]['type'] = 'TINYINT';
				}
			*/
			}
		}
		//	echo '<pre>';
		//	print_r($db_field);exit;
		$view = new View ( 'admin/db_fieldlist' );
		
		$view->set ( 'id', $id );
		$view->set ( 'field', Tools::json_encode ( $field ) );
		
		$view->render ( TRUE );
	}
	
	/**
	 * 获取数据表字段
	 *
	 * @param string $mydbname
	 * @return array $fieldlist_array
	 */
	protected function _get_fieldlist_array($mydbname,$database) {
		//		$fieldlist = $adminmodel -> db -> list_fields($mydbname,true);	//读取数据表字段
		$_db = Database::instance($database);
		$fieldlist = $_db->query ( 'show full fields from `' . $_db	->table_prefix () . $mydbname . '`' )->result_array ( FALSE ); //读取数据表字段
		$fieldlist_array = array ();
		foreach ( $fieldlist as $item ) {
			$fieldlist_array [$item ['Field']] = array ('name' => $item ['Field'], 'type' => $item ['Type'], 'iskey' => $item ['Key'] == 'PRI', 'default' => $item ['Default'], 'isnull' => $item ['Null'], 'comment' => $item ['Comment'] );
		}
		return $fieldlist_array;
	}
	
	/**
	 * 修改/添加字段设置
	 *
	 * @param int $dbid 数据表中ID
	 * @param string $field 字段名
	 */
	public function dbfieldadd($dbid, $field = '') {
		Passport::checkallow('model.dbfieldadd');
		if (! ($dbid > 0)) {
			header ( "loacation:" . Myqee::url ( 'model/dblist/' ) );
			exit ();
		}
		$field = strtolower ( $field );
		$adminmodel = new Admin_Model ( );
		$this->db = Database::instance ();
		
		$db_info = $adminmodel->get_db_array ( $dbid );
		$db_info = $db_info [0];
		if (! $db_info) {
			MyqeeCMS::show_error ( Myqee::lang ( 'admin/model.error.notheiddb' ), false, 'goback' );
		}
		
		$db_info ['config'] = unserialize ( $db_info ['config'] );
		$db_field = ( array ) $db_info ['config'] ['field'];
		
		list($database,$tablename) = explode('/',$db_info['name'],2);
		$_db = Database::instance($database);
		if (! $_db->table_exists ( $tablename )) {
			MyqeeCMS::show_error ( Myqee::lang ( 'admin/model.error.nothedbname', $tablename ), false, 'goback' );
		}
		
		$fieldlist_array = $this->_get_fieldlist_array ( $tablename ,$database);
		//		print_r($fieldlist);
		

		//检验是否存在此字段
		if ($field && ! $fieldlist_array [$field]) {
			MyqeeCMS::show_error ( Myqee::lang ( 'admin/model.error.nothefieldname' ), false, 'goback' );
		}
		
		//print_r($fieldlist_array);
		$db_field [$field] = is_array ( $db_field [$field] ) ? $db_field [$field] : array ('dbname' => $field, 'autoset' => $field );
		//合并字段
		$fieldlist_array [$field] = ( array ) $fieldlist_array [$field];
		$db_field [$field] = array_merge ( $fieldlist_array [$field], $db_field [$field] );
		
		preg_match ( "/^([^\(\)]+)(\(([0-9]+(,[0-9]+)?)\))?$/", $fieldlist_array [$field] ['type'], $dbtypeset );
		$db_field [$field] ['type'] = $dbtypeset [1];
		$db_field [$field] ['length'] = $dbtypeset [3];
		
		$view = new View ( 'admin/db_field_add' );
		$view->set ( 'dbid', $dbid );
		$view->set ( 'field', $db_field [$field] );
		
		if ($db_info ['ismemberdb'] == 1) {
			$sysdbfield = ( array ) MyqeeCMS::config ( 'sysdbfield/member.field');
		} else {
			$sysdbfield = ( array ) MyqeeCMS::config ( 'sysdbfield/default.field');
		}
		$sysfield_forselect = array ('' => '自定义' );
		
		$sysdbfield_json [''] = $db_field [$field];
		foreach ( $sysdbfield as $key => $value ) {
			$sysfield_forselect [$key] = $value ['name'] . ' (' . $key . ')';
			$sysdbfield_json [$key] = $sysdbfield [$key] ['set'];
			$sysdbfield_json [$key] ['inputtype'] = $sysdbfield [$key] ['editset'] ['type'];
			$sysdbfield_json [$key] ['rows'] = $sysdbfield [$key] ['editset'] ['set'] ['rows'];
			$sysdbfield_json [$key] ['size'] = $sysdbfield [$key] ['editset'] ['set'] ['cols'] ? $sysdbfield [$key] ['editset'] ['set'] ['cols'] : $sysdbfield [$key] ['editset'] ['set'] ['size'];
			$sysdbfield_json [$key] ['default'] = $sysdbfield [$key] ['editset'] ['default'];
			$sysdbfield_json [$key] ['getcode'] = $sysdbfield [$key] ['editset'] ['getcode'];
			$sysdbfield_json [$key] ['candidate'] = $sysdbfield [$key] ['editset'] ['candidate'];
			$sysdbfield_json [$key] ['format'] = $sysdbfield [$key] ['editset'] ['format'];
		}
		$view->set ( 'sysfieldselect', $sysfield_forselect );
		$view->set ( 'sysfieldjson', Tools::json_encode ( $sysdbfield_json ) );
		
		$view->set ( 'field_adv', Tools::json_encode ( (array)$db_field [$field]['adv'] ) );
		
		//列表输出转换函数API
		$view->set ( 'fielddocode',$adminmodel -> get_apiclass_list('Field_list_Api',array(''=>'默认')) );
		$view->set ( 'fieldgetcode',$adminmodel -> get_apiclass_list('Field_get_Api',array(''=>'默认')) );
		
		
		$view->render ( TRUE );
	}
	
	public function dbfieldsave($dbid, $field = '') {
		if (! ($dbid > 0)) {
			header ( "loacation:" . Myqee::url ( 'model/dblist/' ) );
			exit ();
		}
		$post = $_POST ['field'];
		if (preg_match ( "/[^a-zA-Z0-9\_]/", $post ['name'] )) {
			MyqeeCMS::show_error ( Myqee::lang ( 'admin/model.error.fieldnameerror' ), true );
		}
		if (empty ( $post ['name'] )) {
			MyqeeCMS::show_error ( Myqee::lang ( 'admin/model.error.fieldnamenull' ), true );
		}
		
		$newfield = strtolower ( $post ['name'] );
		$adminmodel = new Admin_Model ( );
		$this->db = Database::instance ();
		
		$db_info = $adminmodel->get_db_array ( $dbid );
		$db_info = $db_info [0];
		
		$db_info ['config'] = unserialize ( $db_info ['config'] );
		$db_field = ( array ) $db_info ['config'] ['field'];
		
		list($database,$tablename) = explode('/',$db_info['name'],2);
		$_db = Database::instance($database);
		if (! $_db->table_exists ( $tablename )) {
			MyqeeCMS::show_error ( Myqee::lang ( 'admin/model.error.nothedbname', $tablename ), true );
		}
		$fieldlist_array = $this->_get_fieldlist_array ( $tablename ,$database);
		
		//检验是否存在此字段
		if ($field) {
			if (! $fieldlist_array [$field]) {
				MyqeeCMS::show_error ( Myqee::lang ( 'admin/model.error.nothefieldname' ), true );
			}
			Passport::checkallow('model.dbfieldedit');
		} else {
			if ($fieldlist_array [$field]) {
				MyqeeCMS::show_error ( Myqee::lang ( 'admin/model.error.hasfieldname' ), true );
			}
			Passport::checkallow('model.dbfieldadd');
		}
		foreach ( ( array ) $db_info ['config'] ['field'] as $key => $value ) {
			if (! $fieldlist_array [$key]) {
				unset ( $db_info ['config'] ['field'] [$key] );
			}
		}
		
		ignore_user_abort();	//设置为即便浏览器关闭，程序照样执行直到结束
		
		if (! in_array ( $post ['type'], array ('varchar', 'text', 'mediumtext', 'longtext', 'tinyint', 'smallint', 'int', 'bigint', 'float', 'double' ) )) {
			$post ['type'] = 'varchar';
		}
		
		//修正字符长度
		if ($post ['type'] == 'text' || $post ['type'] == 'mediumtext' || $post ['type'] == 'longtext') {
			unset ( $post ['length'] );
		} elseif ($post ['type'] == 'varchar') {
			$post ['length'] = $post ['length'] > 0 && $post ['length'] <= 65535 ? $post ['length'] : 255;
		} elseif ($post ['type'] == 'tinyint') {
			$post ['length'] = $post ['length'] > 0 && $post ['length'] <= 3 ? $post ['length'] : 3;
			//if (! ($post ['default'] > 0 && $post ['default'] <= 999))unset ( $post ['default'] );
		} elseif ($post ['type'] == 'smallint') {
			$post ['length'] = $post ['length'] > 0 && $post ['length'] <= 6 ? $post ['length'] : 6;
			//if (! ($post ['default'] > 0 && $post ['default'] <= 999999))unset ( $post ['default'] );
		} elseif ($post ['type'] == 'int') {
			$post ['length'] = $post ['length'] > 0 && $post ['length'] <= 11 ? $post ['length'] : 11;
			//if (! ($post ['default'] > 0 && strlen ( ( string ) $post ['default'] ) <= 11))unset ( $post ['default'] );
		} elseif ($post ['type'] == 'bigint') {
			$post ['length'] = $post ['length'] > 0 && $post ['length'] <= 20 ? $post ['length'] : 20;
			//if (! ($post ['default'] > 0 && strlen ( ( string ) $post ['default'] ) <= 20))unset ( $post ['default'] );
		} elseif ($post ['type'] == 'float') {
			$length = explode(',',str_replace(' ','',$post ['length']));
			$length[0] = (int)$length[0];
			$length[1] = (int)$length[1];
			$post ['length'] = ($length[0] > 5 && $length[0] <= 16 && $length[0]>$length[1] ? $length[0] : 16).','.($length[1] > 0 && $length[1] <= 5 ? $length[1] : 5);
			//if (! ($post ['default'] > 0 && strlen ( ( string ) $post ['default'] ) <= 16))unset ( $post ['default'] );
		} elseif ($post ['type'] == 'double') {
			$length = explode(',',str_replace(' ','',$post ['length']));
			$length[0] = (int)$length[0];
			$length[1] = (int)$length[1];
			$post ['length'] = ($length[0] > 5 && $length[0] <= 24 && $length[0]>$length[1] ? $length[0] : 15).','.($length[1] > 0 && $length[1] <= 5 ? $length[1] : 5);
			//if (! ($post ['default'] > 0 && strlen ( ( string ) $post ['default'] ) <= 24))unset ( $post ['default'] );
		}elseif ($post ['type'] == 'decimal') {
			$length = explode(',',str_replace(' ','',$post ['length']));
			$length[0] = (int)$length[0];
			$length[1] = (int)$length[1];
			$post ['length'] = ($length[0] > 0 && $length[0] <= 50 && $length[0]>=$length[1] ? $length[0] : 20).','.($length[1] > 0 && $length[1] <= 50 ? $length[1] : 20);
		}
		
		if (! in_array ( $post ['inputtype'], array ('pageselect', 'page' ,'input', 'password', 'time', 'date', 'select', 'selectinput', 'radio', 'checkbox', 'textarea', 'basehtmlarea', 'htmlarea', 'imginput', 'flash', 'file', 'color', 'hidden' ) )) {
			$post ['inputtype'] = 'input';
		}
		if ($post ['istofile']) {
			$post ['type'] = 'varchar';
			$post ['length'] = 255;
		}
		
		if ($post ['autoset']) {
			if ($db_info['ismemberdb']){
				$sysfile = 'member';
			}else{
				$sysfile = 'default';
			}
			$sysdbfield = MyqeeCMS::config ( 'sysdbfield/'.$sysfile.'.field');
			if ($sysdbfield [$post ['autoset']]) {
				$post = array_merge ( $post, $sysdbfield [$post ['autoset']] ['set'] );

//				$post ['inputtype'] = $sysdbfield [$post ['autoset']] ['editset'] ['type'];
//				$post ['size'] = $sysdbfield [$post ['autoset']] ['editset'] ['set'] ['size'];
//				$post ['cols'] = $sysdbfield [$post ['autoset']] ['editset'] ['set'] ['cols'];
//				$post ['rows'] = $sysdbfield [$post ['autoset']] ['editset'] ['set'] ['rows'];
				
				$db_info['config']['sys_field'][$post ['autoset']] = $newfield;
			} else {
				$post ['autoset'] = '';
				unset($db_info['config']['sys_field'][$post ['autoset']]);
			}
		}
		$thedbname = Tools::formatstr ( $post ['dbname'] );
		
		
		//处理高级分组录入项
//		print_r($post['adv']);
		if (is_array($post ['adv'])){
			$post['adv'] = $adminmodel -> set_field_adv($post['adv'],true);
		}
		
		$data = array (
			'name' => $newfield, 
			'dbname' => $thedbname, 
			'autoset' => $post ['autoset'], 
			'iskey' => $post ['iskey'] ? true : false, 
			'isonly' => $post ['isonly'] ? true : false, 
			'isnonull' => $post ['isnonull'] ? true : false, 
			'istofile' => $post ['istofile'] ? true : false, 
			'type' => $post ['type'], 
			'usehtml' => $post ['usehtml']?$post ['usehtml']:0, 
			'html' => $post ['html'], 
			'adv' => $post ['adv'], 
			'length' => $post ['length'], 
			'inputtype' => $post ['inputtype'], 
			'default' => $post ['default'], 
			'candidate' => $post ['candidate'], 
			'class' => $post ['class'], 
			'other' => $post ['other'], 
			'format' => $post ['usehtml']==2?($post['format']=='json_encode'?'json_encode':'serialize'):$post['format'], 
			'comment' => Tools::formatstr( $post ['comment'] ) ,
			'editwidth' => $post['editwidth']>0||$post['editwidth']==='0'?$post['editwidth']:NULL,
		);
		if ($data['usehtml'] == 1 && !empty($data['html'])){
			$data['default'] = $post ['default2'];
		}
		( int ) $post ['size'] and $data ['size'] = ( int ) $post ['size'];
		( int ) $post ['rows'] and $data ['rows'] = ( int ) $post ['rows'];
		( int ) $post ['cols'] and $data ['cols'] = ( int ) $post ['cols'];
		
		if ($post ['getcode']){
			$tmpdoinfomodel = (array)get_class_methods('Field_get_Api');
			if (in_array($post ['getcode'],$tmpdoinfomodel)){
				$data ['getcode'] = $post ['getcode'];
			}
		}

		if ($post ['islist']) {
			$data ['islist'] = true;
			( int ) $post ['width'] > 0 and $data ['width'] = ( int ) $post ['width'];
			$data ['align'] = $post ['align'] == 'center' || $post ['align'] == 'right' ? $post ['align'] : '';
			$data ['tdclass'] = $post ['align'] == 'td1' ? 'td1' : 'td2';
			$post ['boolean'] and $data ['boolean'] = $post ['boolean'];
			if ($post ['docode']){
				$tmpdoinfomodel = (array)get_class_methods('Field_list_Api');
				if (in_array($post ['docode'],$tmpdoinfomodel)){
					$data ['docode'] = $post ['docode'];
				}
			}
		}
		
		$db_info ['config'] ['field'] [$data ['name']] = $data;
		
		$this->db->update ( '[dbtable]', array ('config' => serialize ( $db_info ['config'] ) ), array ('id' => $dbid ) );
		
		$this->_editfield ( $tablename, $field, $newfield, $data ,$database);
		
		//保存数据表配置文件
		$adminmodel->save_db_config ( $dbid );
		
		MyqeeCMS::show_info ( Myqee::lang ( 'admin/model.info.saveok' ), true, 'goback' );
	}
	
	protected function _editfield($tablename, $oldname, $newname, $dbconfig,$database) {
		$_db = Database::instance($database);
		$fieldtype = $dbconfig ['type'] . ($dbconfig ['length'] ? '(' . $dbconfig ['length'] . ')' : '');
		$sql = "ALTER TABLE `" . $_db->table_prefix () . $tablename . "` " . ($oldname ? "CHANGE `{$oldname}`" : 'ADD COLUMN') . " `{$newname}` {$fieldtype} " . ($dbconfig ['isnonull'] ? 'NOT NULL' : 'NULL') . ($dbconfig ['isonly'] ? ' auto_increment' : '') . " COMMENT '{$dbconfig['comment']}'";
		$_db->query ( $sql );
		$dbindex = $_db->query ( 'SHOW INDEX FROM `' . $_db->table_prefix () . $tablename )->result_array ( FALSE );
		
		//读取索引
		foreach ( $dbindex as $item ) {
			if ($item ['Column_name'] == $newname) {
				$hasKey = true;
				break;
			}
		}
		//创建、删除索引
		if ($dbconfig ['iskey'] && ! $hasKey) {
			$_db->query ( "ALTER TABLE `" . $_db->table_prefix () . $tablename . "` ADD INDEX (`{$newname}`)" );
		} elseif (! $dbconfig ['iskey'] && $hasKey) {
			$_db->query ( "ALTER TABLE `" . $_db->table_prefix () . $tablename . "` DROP INDEX `{$newname}`" );
		}
	
	}
	
	/**
	 * 删除字段
	 *
	 * @param int $dbid
	 * @param string $field
	 */
	public function dbfielddel($dbid, $field) {
		Passport::checkallow('model.dbfielddel');
		if (! ($dbid > 0)) {
			MyqeeCMS::show_error ( Myqee::lang ( 'admin/model.error.parametererror' ), true );
			exit ();
		}
		$adminmodel = new Admin_Model ( );
		$this->db = Database::instance ();
		
		$db_info = $adminmodel->get_db_array ( $dbid );
		$db_info = $db_info [0];
		
		$db_info ['config'] = unserialize ( $db_info ['config'] );
		$db_field = ( array ) $db_info ['config'] ['field'];
		list($database,$tablename) = explode('/',$db_info ['name'],2);
		$_db = Database::instance($db_info['database']);
		if (! $_db->table_exists ( $tablename )) {
			MyqeeCMS::show_error ( Myqee::lang ( 'admin/model.error.nothedbname', $tablename ), true );
		}
		$fieldlist_array = $this->_get_fieldlist_array ( $tablename  ,$db_info['database']);
		
		//检验是否存在此字段
		if ($fieldlist_array [$field]) {
			//删除字段
			$d = $_db->query ( 'ALTER TABLE `' . $_db->table_prefix () . $tablename . '` DROP COLUMN `' . $field . '`' );
			$d = array(1);
		}
		
		if (count ( $d ) > 0) {
			unset ( $db_info ['config'] [$field] );
			$this->db->update ( '[dbtable]', array ('config' => serialize ( $db_info ['config'] ) ), array ('id' => $dbid ) );
			//save config
			$adminmodel->save_db_config ( $dbid );
			MyqeeCMS::show_info ( Myqee::lang ( 'admin/model.info.delsuccess' ), true );
		} else {
			MyqeeCMS::show_info ( Myqee::lang ( 'admin/model.info.noeditor' ), true );
		}
	}
	
	public function dborder($dbid) {
		Passport::checkallow('model.dborder');
		$adminmodel = new Admin_Model ( );
		$this->db = Database::instance ();
		$db_info = $adminmodel->get_db_array ( $dbid );
		$db_info = $db_info [0];
		
		if (! $db_info)
			MyqeeCMS::show_error ( Myqee::lang ( 'admin/model.error.notheiddb' ) );
		$db_info ['config'] = unserialize ( $db_info ['config'] );
		$db_field = ( array ) $db_info ['config'] ['field'];
		list($database,$tablename) = explode('/',$db_info ['name'],2);
		$_db = Database::instance($database);
		if (! $_db->table_exists ( $tablename )) {
			MyqeeCMS::show_error ( Myqee::lang ( 'admin/model.error.nothedbname', $tablename ), true );
		}
		$fieldlist_array = $this->_get_fieldlist_array ( $tablename  ,$db_info['database']);
		
		if (is_array ( $_POST ['field'] )) {
			$tmpfield = array_merge ( ( array ) $_POST ['field'], $fieldlist_array );
		} else {
			$tmpfield = $fieldlist_array;
		}
		$data = array ();
		foreach ( $tmpfield as $key => $value ) {
			if ($fieldlist_array [$key]) {
				if (isset ( $db_field [$key] )) {
					$data [$key] = $db_field [$key];
				} else {
					preg_match ( "/^([^\(\)]+)(\(([0-9]+(,[0-9]+)?)\))?$/", $fieldlist_array [$key] ['type'], $dbtypeset );
					$data [$key] = $fieldlist_array [$key];
					
					$data [$key] = array ('name' => $fieldlist_array [$key] ['name'], 'dbname' => Tools::formatstr ( $dbname ), 'iskey' => $fieldlist_array [$key] ['iskey'] ? true : false, 'type' => $dbtypeset [1], 'length' => $dbtypeset [3] );
				}
				if ($_POST ['field'] [$key] ['dbname']) {
					$data [$key] ['dbname'] = $_POST ['field'] [$key] ['dbname'];
				} else {
					$data [$key] ['dbname'] = $fieldlist_array [$key] ['dbname'];
				}
			}
		}
		if (count ( $this->db->update ( '[dbtable]', array ('config' => serialize ( array ('field' => $data ) ) ), array ('id' => $dbid ) ) )) {
			//save config
			$adminmodel->save_db_config ( $dbid );
			MyqeeCMS::show_info ( Myqee::lang ( 'admin/model.info.saveok' ), true );
		} else {
			MyqeeCMS::show_info ( Myqee::lang ( 'admin/model.info.noeditor' ), true );
		}
	}
	
	public function changemyorder() {
		Passport::checkallow('model.dbroder');
		if (! ($myorder = $_GET ['order'])) {
			MyqeeCMS::show_error ( Myqee::lang ( 'admin/model.error.noorderinfo' ), true );
		}
		$adminmodel = new Admin_Model ( );
		$updatenum = $adminmodel->editmyorder ( $this->changemyorder ? '[dbtable]' : '[model]', $myorder, 'id_', 'id' );
		MyqeeCMS::show_info ( Myqee::lang ( 'admin/model.info.editmyorderok', $updatenum ), true );
	}
	
	public function changedbmyorder() {
		$this->changemyorder = true;
		$this->changemyorder ();
	}
	
	public function dboutput($allid, $key = '') {
		Passport::checkallow('model.dboutput');
		$this->db = Database::instance ();
		$allid = Tools::formatids ( $allid, false );
//		$where = array ('id' => $id );
		$results = $this->db->from ( '[dbtable]' );
//		if ($this -> site_id>0){
//			$results = $results -> where('siteid',$this->site_id);
//		}
		$results = $results -> in ( 'id', $allid )->orderby ( 'id' )->get ()->result_array ( false );
		
		if (count ( $results ) == 0) {
			MyqeeCMS::show_info ( Myqee::lang ( 'admin/model.info.nooutdb' ), true );
		}
		$newresults = array ();
		foreach ( $results as $value ) {
			list($_database,$_table) = explode('/',$value['name']);
			$_db = Database::instance($_database);
			$_config = MyqeeCMS::config('database.'.$_database);
			$sql = 'SHOW CREATE TABLE `' . $_db->table_prefix () . $_table . '`';
			$rs = $_db->query ( $sql )->result_array ( FALSE );
			$createsql = $rs [0] ['Create Table'];
			if ($createsql) {
				$createsql = str_replace ( 'CREATE TABLE `' . $_db->table_prefix (), 'CREATE TABLE `{{$TABLE_PREFIX$}}', $createsql );
				$value ['creatsql'] = $createsql;
				$newresults [] = $value;
			}
		}
		$mydata = Tools::info_encryp ( $newresults, $key,true );
		if (! $mydata) {
			MyqeeCMS::show_info ( Myqee::lang ( 'admin/model.info.nooutdb' ), true );
		}
		download::force ( './', $mydata, 'dbtable.txt' );
	}
	
	public function inputdbshow () {
		$view = new View ( 'admin/db_input' );
		//tables
		$dbconfig = MyqeeCMS::config('database');
		$tables = array();
		foreach ($dbconfig as $database=>$config) {
			$db = Database::instance ($database);
			$tmp = $db->list_tables();
			foreach ($tmp as $val) {
				if (strpos($val,'[') > -1 || !ereg('^'.$config['table_prefix'],$val)) {
					continue;
				}
				$tables[$database][] = preg_replace ("#^{$config['table_prefix']}#",'',$val);
			}
			$databases[$database] = $database;
		}
		$view->set ('databases',$databases);
		$view->set ('tables',Tools::json_encode($tables));
		$view->render ( TRUE );
	}
	
	/**
	 * 从现有的数据库中导入数据表
	 */
	public function inputdbsave () {
		$tables = $_POST['tables'];
		$database = $_POST['database'];
		if (empty($tables)) {
			MyqeeCMS::show_error ( Myqee::lang ( 'admin/model.error.emptytables' ), true );
		}
		
		$db = Database::instance();
		foreach ($tables as $table) {
			$query = $db->getwhere ('[dbtable]',array('name'=>$database.'/'.$tables))->result_array(false);
			$oldconfig = array();
			$isadd = 1;
			if (!empty($query)) {
				$data = $query[0];
				$oldconfig = unserialize($data['config']);
				$isadd = 0;
			} else {
				$data = array(
					'name' => $database.'/'.$table,
					'dbname' => $table,
					'isuse' => 1,
					'readbydbname' => 1,
					'usedbmodel' => 0,
					'siteid' => 0,
				);
			}
			
			$db = Database::instance ($database);
			$newfieldsinfo = $db->list_fields($table);
			if (empty($oldconfig)) {
				$oldconfig['field'] = array();
			}
			$oldfields = array_keys($oldconfig['field']);
			foreach ($newfieldsinfo as $field=>$con) {
				if (in_array($field,$oldfields)) {
					continue;
				}
				$type = 'varchar';
				$length = 11;
				if ($con['type'] == 'int') {
					$type = 'int';
				} elseif ($con['type'] == 'string') {
					$type = 'varchar';
					$length = $con['length'];
				}
				$oldconfig['field'][$field] = array(
					'name' =>$field,
					'dbname' =>$field,
					'type' =>$type,
					'length' =>$length,
				);
				if ($con['sequenced'] == 1) {
					$oldconfig['field'][$field]['autoset'] = 'id';
					$oldconfig['field'][$field]['iskey'] = '1';
					$oldconfig['field'][$field]['isonly'] = '1';
					$oldconfig['field'][$field]['isnonull'] = '1';
					$oldconfig['field'][$field]['inputtype'] = 'hidden';
					$oldconfig['field'][$field]['islist'] = '1';
					$oldconfig['field'][$field]['width'] = '60';
					$oldconfig['field'][$field]['align'] = 'center';
					$oldconfig['field'][$field]['tdclass'] = 'td1';
					$oldconfig['sys_field']['id'] = $field;
				}
			}
			$newfields = array_keys($newfieldsinfo);
			foreach ($oldfields as $field) {
				if (!in_array($field,$newfields)) {
					unset ($oldconfig['field'][$field]);
				}
			}
			$data['config'] = serialize($oldconfig);
			$db = Database::instance();
			if ($isadd) {
				$status = $db->insert ('[dbtable]',$data);
			} else {
				$status = $db->update ('[dbtable]',$data,array('name'=>$data['name']));
			}
		}
		if ($status->count() >0) {
			MyqeeCMS::show_info ( Myqee::lang ( 'admin/model.info.saveok' ), true, 'goback' );
		}else{
			MyqeeCMS::show_info ( '没有修改数据！', true );
		}
	}
	
	public function inputdb() {
		Passport::checkallow('model.dboutput');
		if ($this -> allow_db!='-ALL-'){
			MyqeeCMS::show_error ( '您没有管理所有数据表的权限，不能导入数据表！');
		}
		$view = new View ( 'admin/db_input' );
		//tables
		$dbconfig = MyqeeCMS::config('database');
		$tables = array();
		foreach ($dbconfig as $database=>$config) {
			$db = Database::instance ($database);
			$tmp = $db->list_tables();
			foreach ($tmp as $val) {
				if (strpos($val,'[') > -1 || !ereg('^'.$config['table_prefix'],$val)) {
					continue;
				}
				$tables[$database][] = preg_replace ("#^{$config['table_prefix']}#",'',$val);
			}
			$databases[$database] = $database;
		}
		$view->set ('databases',$databases);
		$view->set ('tables',Tools::json_encode($tables));
		$view->render ( TRUE );
	}
	public function dbinput() {
		Passport::checkallow('model.dboutput');
		if ($this -> allow_db!='-ALL-'){
			MyqeeCMS::show_error ( '您没有管理所有数据表的权限，不能导入数据表！');
		}
		//上传方式
		$thedata = $this->_getinputdata ();
		
		$this->db = Database::instance ();
		$adminmodel = new Admin_Model ( );
		$inputerr = 0;
		$inputok = 0;
		foreach ( $thedata as $item ) {
			$data = array (
				'dbname' => $item ['dbname'], 
				'name' => preg_replace ( "#[^/\w]#", '', $item ['name'] ), 
				'content' => Tools::formatstr ( $item ['content'] ), 
				'isuse' => $item ['isuse'], 
				'myorder' => ( int ) $item ['myorder'], 
				'config' => $item ['config'], 
				'modelconfig' => $item ['modelconfig'], 
				'isdefault' => 0 
			);
//			if ($this -> site_id>0){
//				$data['siteid'] = $this -> site_id;
//			}
			//检测是否已经存在的文件
			$chkdata = $this->db->getwhere ( '[dbtable]', array ('name' => $data ['name'] ) )->result_array ( FALSE );
			$chkdata = $chkdata [0];
			
			if ($chkdata) {
				$inputerr += 1;
			} else {
				list($_database,$_table) = explode('/',$item['name']);
				$_db = Database::instance($_database);
				$createsql = str_replace ( 'CREATE TABLE `{{$TABLE_PREFIX$}}', 'CREATE TABLE `' . $_db->table_prefix (), $item ['creatsql'] );
				$_db->query ( $createsql );
				
				$status = $this->db->insert ( '[dbtable]', $data );
				if (count ( $status )) {
					//保存文件
					$adminmodel->save_db_config ( $status->insert_id () );
					
					//输出提示信息
					$inputok += 1;
				}
			}
		}
		
		$showinfo = Myqee::lang ( 'admin/model.info.dbinputok', $inputok );
		if ($inputerr > 0)
			$showinfo .= Myqee::lang ( 'admin/model.info.inputerror', $inputerr );
		MyqeeCMS::show_info ( $showinfo, true, 'goback' );
	}
	

	public function dbrenewfiles($theid=0){
		Passport::checkallow('model.dbrenewfiles');
		$theid = (int)$theid;
		//$nowtplgroup = $this -> session -> get('now_tlpgroup');
		//if (!$nowtplgroup)$nowtplgroup = 'default';
		//MyqeeCMS::show_info($nowtplgroup,TRUE);
		
		$this -> db = Database::instance();
		$results = $this -> db -> from('[dbtable]');
		if ($theid >0 ){
			$results = $results -> where ('id',$theid);
		}
//		if($this->site_id>0){
//			$results = $results -> where ('siteid',$this->site_id);
//		}
		
		$results = $results -> get() -> result_array(false);
		
		$save_ok = $save_error = 0;
		if (is_array($results)){
			$adminmodel = new Admin_Model();
			foreach ($results as $item){
				$item['config'] = unserialize($item['config']);
				$item['modelconfig'] = unserialize($item['modelconfig']);
				if ( $adminmodel -> save_db_config ( $item ) ){
					$save_ok += 1;
				}else{
					$save_error += 1;
				}
			}
		}
		
		$msg = '执行完毕，共重新生成文件'.$save_ok.'个，执行失败：'.$save_error.'！';
		if ($_GET['type']=='auto'){
			echo '<script>parent.showinfo("db","'.$msg.'");document.location.href="'.ADMIN_IMGPATH.'/admin/block.html";</script>';
		}else{
			MyqeeCMS::show_info($msg,true);
		}
	}
	
	/**
	 * 处理扩展表，去掉重复的
	 *
	 * @param array $relation
	 * @return array
	 */
	protected function _dealRelationField ($relation=array()) {
		if (!is_array($relation)) {
			return array();
		}
		$_relation = array();
		foreach ($relation as $val) {
			$_relation[$val['dbtable']] = $val;
		}
		return $_relation;
	}
	
	/**
	 * 添加扩展表到原字段中
	 *
	 * @param array $relation 扩展表信息
	 * @param array $field 主表的字段
	 * @return array
	 */
	protected function _addRelationToField ($relation,$field) {
		if (!is_array($relation) || empty($relation)) {
			return $field;
		}
		$keys = array_keys($field);
		foreach ($relation as $val) {
			$tmp = '_'.$val['dbtable'];
			if (in_array($tmp,$keys)) {
				continue;
			} else {
				$field[$tmp] = array('dbname'=>$val['dbtable'].'扩展表');
			}
		}
		foreach ($field as $key=>$val) {
			$needdel = true;
			if (substr($key,0,1) == '_') {
				$tmp = substr($key,1);
				
				if ((is_array($relation[$tmp]) || !empty($relation[$tmp])) && (in_array($relation[$tmp]['relation'],array('','1:1')))) {
					$needdel = FALSE;
				}
				if ($needdel) {
					unset($field[$key]); 
				}
			}
		}
		return $field;
	}
	
/*
	function infoformeditor($dbname = NULL){
		if (!$dbname){
			MyqeeCMS::show_error( Myqee::lang('admin/model.error.parametererror'),false);
		}
		$mydbname = '['.$dbname.']';

		$adminmodel = new Admin_Model;
		$sys_field = MyqeeCMS::config('db/'.$dbname.'.sys_field');

		$view = new View('admin/model_infoform');

		$view -> user_editinfo_formhtml = $adminmodel -> get_user_editinfo_form($dbname,$myinfo);

		$view -> render(TRUE);
	}
	*/

}