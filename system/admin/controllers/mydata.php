<?php
class Mydata_Controller_Core extends Controller {

	function __construct(){
		parent::__construct(NULL);
		Passport::chkadmin();
	}

	public function index(){
		Passport::checkallow('info.mydata_list');
		$view = new View('admin/mydata_list');
		$adminmodel = new Admin_Model();
		$per = 20;
		$alldata = array();
		$where = array();
		$cate = $_GET['cate'];
		$type = $_GET['type'];
		if(!is_null($cate) && $cate != ''){
			$listwhere['cate'] = $cate;
			$where['cate'] = $cate;
		}
		if(!is_null($type) && $type != ''){
			$listwhere['type'] = $type;
			$where['type'] = $type;
		}
		$num = $adminmodel -> db -> count_records('[mydata]',$listwhere);
		$this -> pagination = new Pagination( array(
			'uri_segment'    => 'index',
			'total_items'    => $num,
			'items_per_page' => $per,
		));
		$alldata = $adminmodel -> db -> from ( '[mydata]' ) -> where ( $where ) -> limit ( $per, $this -> pagination -> sql_offset )-> orderby ( 'myorder', 'ASC' ) -> orderby ( 'id', 'DESC' ) ->get () ->result_array ( FALSE );
		$view -> set('list',$alldata);
		$view -> set('page', $this -> pagination -> render('digg'));
		$view -> render(TRUE);
	}

	public function add(){
		Passport::checkallow('info.mydata_add');
		$this -> edit();
	}

	public function copy($id = 0){
		Passport::checkallow('info.mydata_copy');
		if ($id>0){
			$this -> iscopy = true;
		}
		$this -> edit($id);
	}

	public function edit($id=0){
		$adminmodel = new Admin_Model();
		Passport::checkallow('info.mydata_edit');
		if ( $id > 0){
			$info = $adminmodel -> db -> from('[mydata]') -> where(array('id' => $id)) -> limit(1) -> get() -> result_array(FALSE);
			$data = $info[0];
			if ( !is_array($data) ){
				MyqeeCMS::show_error(Myqee::lang('admin/task.error.nofoundmydata'),false,'goback');
			}
		}
		$view = new View('admin/mydata_edit');
		if ($this -> iscopy === true){
			unset($data['id'],$id);
			$data['name'] .= '_copy';
			$data['var_name'] .= '_copy';
			$view -> set('iscopy',true);
		}else{
			if ($id >0 )$view -> set('isedit',true);
		}
		$view -> set('id',$id);
		$view -> set('data',$data);
		$view -> set('class' , $adminmodel -> get_allclass_array());
		$view -> set('model' , $adminmodel -> get_model_for_dropdown('请选择'));
		//list template
		$view -> set ('template_id' , array_merge(array(''=>'不使用模板，直接返回数组'),$adminmodel -> get_alltemplate('block')) );

		$result = $adminmodel -> db -> select('cate') -> from('[mydata]') -> groupby('cate') -> orderby('myorder' , 'asc') -> get() -> result_array(FALSE);
		$tplcate = array();
		foreach ($result as $item){
			$tplcate[$item['cate']] = $item['cate'];	
		}
		$view -> tplcate = $tplcate;
		$dbconfig = Myqee::config('database');
		foreach ($dbconfig as $key => $value){
			$dbselect[$key] = $value['name']?$value['name']:$key;
		}
		$view -> set('dbselect',$dbselect);
		$dbinfo = $adminmodel -> get_dbtable_forselect(true);
		$view -> set('dblist' , $dbinfo['forselect']);
		$view->render(TRUE);
	}

	public function del($id=0){
		Passport::checkallow('info.mydata_del');
		$adminmodel = new Admin_Model();
		if(!($id>0))MyqeeCMS::show_error(Myqee::lang('admin/task.error.parameterserror'),true);
		$delNum = $adminmodel -> db -> delete('[mydata]', array('id' => $id));
		if (count ( $delNum ) > 0){
			MyqeeCMS::show_info(Myqee::lang('admin/task.info.delsuccess', count( $delNum )."条" ),true,'refresh');
		}else{
			MyqeeCMS::show_error(Myqee::lang('admin/task.error.saveerror'),true);
		}
	}

	public function editorder(){
		Passport::checkallow('info.mydata_order');
		if (!($myorder = $_GET ['order'])) {
			MyqeeCMS::show_error(Myqee::lang('admin/task.error.noorderinfo'),true);
		}
		$adminmodel = new Admin_Model ( );
		$updatenum = $adminmodel->editmyorder('[mydata]', $myorder, 'mydataid_', 'id');
		/*
		$ids = explode(',',$myorder);
		foreach ($ids as $id){
			$id = (int)substr($id,9);
			$this -> mydata_save_config($id);
		}*/
		MyqeeCMS::show_info(Myqee::lang ('admin/task.info.editmyorderok', $updatenum),true,'refresh');
	}

	public function save($id=0){
		$adminmodel = new Admin_Model();
		if ($id>0){
			Passport::checkallow('info.mydata_edit');
		}else{
			Passport::checkallow('info.mydata_add');
		}
		$post = $_POST['mydata'];
		if (!($data['name']= htmlspecialchars($post['name']))){
			MyqeeCMS::show_error(Myqee::lang('admin/task.error.noacquname'),true);
		}
		$data['is_use'] = $post['is_use'] == 0?0:1;
		$data['myorder'] = (int)$post['myorder'];
		$data['type'] = (int)$post['type'];
		$data['cache_time'] = (int)$post['cache_time'];
		$data['var_name'] = preg_match("/[a-z][a-z_0-9]+/i",$post['var_name'])?$post['var_name']:'data';
		$data['template_id'] = (int)$post['template_id'];
		$data['cate'] = $post['cate'] == '' || $post['cate'] == NULL ? '默认':$post['cate'];
		if($post['type'] != 0){
			$data['table_config'] = $post['table_config'];
			if (!preg_match("/^SELECT /i",$post['sql']))MyqeeCMS::show_error(Myqee::lang('admin/task.error.errorsql'),true);
			$data['sql'] = $post['sql'];
		}else{
			$data['is_hot'] = $post['is_hot'];
			$data['isheadlines'] = $post['isheadlines'];
			$data['ontop'] = $post['ontop'];
			$data['is_indexshow'] = $post['is_indexshow'];
			$data['commend'] = $post['commend'] == 0?'':$post['commend'];
			$data['classid'] = (int)$post['classid'];
			$data['modelid'] = (int)$post['modelid'];
			$data['modelname'] = '';
			$data['dbname'] = $post['dbname'];
			$data['list_byfield'] = $post['list_byfield'];
			$data['list_orderby'] = $post['list_orderby'];
			$data['limit'] = $post['limit'] > 0 ? $post['limit']:20;
			$data['start_number'] = $post['start_number'] > 0 ? $post['start_number']:0;
			if ($post['classid']>0){
				$data['classid'] = $post['classid'];
				if ($classinfo = $adminmodel -> get_class_array($post['classid'],'classname,modelid,dbname') ){
					$data['classname'] = $classinfo['classname'];
					$data['modelid'] = $classinfo['modelid'];
					$data['dbname'] = $classinfo['dbname'];
					if ($data['modelid']>0 && ($modelinfo = $adminmodel -> get_model_array($data['modelid'],'modelname,dbname'))){
						$data['modelname'] = $modelinfo['modelname'];
					}
				}else{
					MyqeeCMS::show_error(Myqee::lang('admin/task.error.nofoundclass'),true);
				}
			}elseif ($post['modelid']>0){
				$data['classname'] = '';
				$data['modelid'] = $post['modelid'];
				if ($modelinfo = $adminmodel -> get_model_array($post['modelid'],'modelname,dbname')){
					$data['modelname'] = $modelinfo['modelname'];
					$data['dbname'] = $modelinfo['dbname'];
				}else{
					MyqeeCMS::show_error(Myqee::lang('admin/task.error.nofoundmodel'),true);
				}
			}elseif (isset($post['dbname']) && !empty($post['dbname'])){
				$data['classname'] = '';
				$data['dbname'] = $post['dbname'];
			}
		}

		if ($id>0){
			//编辑
			$status = $adminmodel -> db -> update('[mydata]', $data ,array('id' => $id)) -> count();
		}else{
			//添加
			$status = $adminmodel -> db -> insert('[mydata]', $data);
			$id = $status -> insert_id();
			$status = $status -> count();
		}
		$data['id'] = $id;
		if ($status){
			$adminmodel -> mydata_save_config($data);
			MyqeeCMS::show_info(Myqee::lang('admin/task.info.saveok'),true,Myqee::url('mydata/index'));
		}else{
			MyqeeCMS::show_info(Myqee::lang('admin/task.info.savenone'),true);
		}
	}

	public function output($mydataid,$key = ''){
		Passport::checkallow('info.mydata_output');
		$this -> db or $this-> db = Database::instance();
		if ($this -> outgroup){
			$results = $this -> db -> from('[mydata]') -> where('group',$mydataid) -> orderby('id') -> get() -> result_array(false);
		}else{
			$mydataid = Tools::formatids($mydataid , false);
			$results = $this -> db -> from('[mydata]') -> in('id',$mydataid) -> orderby('id') -> get() -> result_array(false);
		}
		if (count($results) == 0){
			MyqeeCMS::show_info(Myqee::lang('admin/task.info.noouttemplate'),true);
		}
		$mydata = Tools::info_encryp($results,$key,true);
		if (!$mydata){
			MyqeeCMS::show_info(Myqee::lang('admin/task.info.nooutdb'),true);
		}
		download::force('./',$mydata,'mydata.txt');
	}

	public function input() {
		$view = new View ( 'admin/mydata_input' );
		$view->render ( TRUE );
	}
	public function input_save() {
		Passport::checkallow('info.mydata_input');
		//上传方式
		$thedata = $this->_getinputdata ();
		$adminmodel = new Admin_Model ( );
		$inputok = 0;
		foreach ( $thedata as $item ) {
			$data = array (
				'modelid'			=> (int)$item['modelid'], 
				'is_use'			=> (int)$item['is_use'], 
				'classid'			=> (int)$item['classid'], 
				'dbname'			=> preg_replace("/[^\w]/", '',$item['dbname'] ), 
				'is_hot'			=> (int)$item['is_hot'],  
				'isheadlines'		=> (int)$item['isheadlines'], 
				'ontop'				=> (int)$item['ontop'], 
				'commend'			=> (int)$item['commend'], 
				'is_indexshow'		=> (int) $item['is_indexshow'], 
				'myorder'			=> $item['myorder'], 
				'name'				=> $item['name'], 
				'limit'				=> (int)$item ['limit'], 
				'sql'				=> $item['sql'], 
				'list_byfield'		=> $item['list_byfield'], 
				'list_orderby'		=> $item['list_orderby'], 
				'classname'			=> $item['classname'], 
				'modelname'			=> $item['modelname'], 
				'cache_time'		=> (int)$item['cache_time'], 
				'table_config'		=> $item['table_config'], 
				'type'				=> (int)$item['type'], 
				'var_name'			=> $item['var_name'], 
				'template_id'		=> $item['template_id'], 
				'cate'				=> $item['cate']
			);
			
			$status = $adminmodel->db->insert( '[mydata]', $data );
			if (count ( $status )) {
				//保存文件
				$adminmodel -> mydata_save_config ($data);
				//输出提示信息
				$inputok += 1;
			}
		}
		MyqeeCMS::show_info ( Myqee::lang ( 'admin/task.info.inputok', $inputok ), true, 'goback' );
	}

	protected function _getinputdata() {
		$key = $_POST ['key'];
		$thedata = $_POST ['data'];
		
		if (empty ( $thedata ) && $_FILES ['upload'] ['size'] == 0) {
			MyqeeCMS::show_error ( Myqee::lang ( 'admin/task.error.inputdataempty' ), true );
		}
		if ($_FILES ['upload'] ['tmp_name']) {
			if ($_FILES ['upload'] ['size'] < 5000000) { //只操作5MB以内的文件
				if (! $thedata = @file_get_contents ( $_FILES ['upload'] ['tmp_name'] )) {
					MyqeeCMS::show_error ( Myqee::lang ( 'admin/task.error.inputreadfileerror' ), true );
				}
				//反解文件
				$thedata = Tools::info_uncryp ( $thedata, $key, true );
			} else {
				MyqeeCMS::show_error ( Myqee::lang ( 'admin/task.error.inputerrorsize' ), true );
			}
		} else {
			if (strlen ( $thedata ) < 5000000) {
				//反解文件
				$thedata = Tools::info_uncryp ( $thedata, $key ,true );
			} else {
				MyqeeCMS::show_error ( Myqee::lang ( 'admin/task.error.inputerrorsize' ), true );
			}
		}
		if ($thedata === - 1) {
			MyqeeCMS::show_error ( Myqee::lang ( 'admin/task.error.inputtplbyedit' ), true );
		}
		
		if (! is_array ( $thedata )) {
			MyqeeCMS::show_error ( Myqee::lang ( 'admin/task.error.decodeerror' ), true );
		}
		return $thedata;
	}
	
	public function renewfiles($theid=0){
		Passport::checkallow('info.mydata_renewfiles');
		$theid = (int)$theid;		
		$adminmodel = new Admin_Model ( );
		$results = $adminmodel -> db -> from('[mydata]');
		if ($theid >0 ){
			$results = $results -> where ('id',$theid);
			if ( $adminmodel -> mydata_save_config ( $results ) ){
				$save_ok += 1;
			}
		}
		$results = $results -> get() -> result_array(false);
		$save_ok = $save_error = 0;
		if (is_array($results)){
			foreach ($results as $item){
				if ( $adminmodel -> mydata_save_config ( $item ) ){
					$save_ok += 1;
				}else{
					$save_error += 1;
				}
			}
		}
		MyqeeCMS::show_info('执行完毕，共重新生成文件'.$save_ok.'个，执行失败：'.$save_error,TRUE);
	}
}