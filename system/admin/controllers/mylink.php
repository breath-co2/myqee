<?php
class Mylink_Controller_Core extends Controller {

	/**
	 * 数据库
	 * @var object $db
	 */
	public $db;

	function __construct(){
		parent::__construct(NULL);
		Passport::chkadmin();
	}

	public function index(){
		Passport::checkallow('info.mylink_lists');
		$view = new View('admin/mylink_lists');
		$this -> db = Database::instance();
		$per = 20;
		$alldata = array();
		$num = $this -> db -> count_records('[mylink]');
		$this -> pagination = new Pagination( array(
			'uri_segment'    => 'index',
			'total_items'    => $num,
			'items_per_page' => $per,
		));
		$alldata = $this -> db -> from ( '[mylink]' ) -> limit ( $per, $this -> pagination -> sql_offset )-> orderby ( 'myorder', 'ASC' ) -> orderby ( 'id', 'DESC' ) ->get () ->result_array ( FALSE );
		$view -> set('list',$alldata);
		$view -> set('page', $this -> pagination -> render('digg'));
		$view -> render(TRUE);
	}
	
	public function links($id=0){
		Passport::checkallow('info.mylink_child_links');
		$arguments = explode('/',$_GET['path']);
		$view = new View('admin/mylink_child_lists');
		$link_list = Myqee::config('mylinks/link_'.$id);
		if ($arguments){
			foreach ($arguments as $arg){
				if (!empty($arg)){
					if (!$link_list[$arg]){
						MyqeeCMS::show_error('不存在此父菜单标识',false,'goback');
						break;
					}
					$link_list = $link_list[$arg]['submenu'];
				}
			}
		}
		$view -> set('id' , $id);
		$view -> set('list',$link_list);
		$view -> set('nav_path' , join('/',$arguments));
		array_pop($arguments);
		$view -> set('nav_parentpath' , join('/',$arguments));
		$view -> render(TRUE);
	}
	
	public function mylinkadd(){
		Passport::checkallow('info.mylink_add');
		$this -> mylinkedit();
	}

	public function mylinkedit($id=0){
		$this -> db = Database::instance();
		if ($id>0){
			Passport::checkallow('info.mylink_edit');
		}else{
			Passport::checkallow('info.mylink_add');
		}
		if ( $id > 0){
			$info = $this -> db -> from('[mylink]') -> where(array('id' => $id)) -> limit(1) -> get() -> result_array(FALSE);
			$data = $info[0];
			if ( !is_array($data) ){
				MyqeeCMS::show_error(Myqee::lang('admin/info.error.nofoundmylink'),false,'goback');
			}
		}
		$view = new View('admin/mylink_edit');
		if ($id >0 )$view -> set('isedit',true);
		$view -> set('id',$id);
		$view -> set('data',$data);
		$view->render(TRUE);
	}

	public function mylinkdel($id=0){
		Passport::checkallow('info.mylink_del');
		$this -> db = Database::instance();
		if(!($id>0))MyqeeCMS::show_error(Myqee::lang('admin/info.error.parametererror'),true);
		$delNum = $this -> db -> delete('[mylink]', array('id' => $id));
		if (count ( $delNum ) > 0){
			MyqeeCMS::show_info(Myqee::lang('admin/info.info.delsuccess', count( $delNum )."条" ),true,'refresh');
		}else{
			MyqeeCMS::show_error(Myqee::lang('admin/info.error.saveerror'),true);
		}
	}

	public function editorder(){
		Passport::checkallow('info.mylink_order');
		if (!($myorder = $_GET ['order'])) {
			MyqeeCMS::show_error(Myqee::lang('admin/info.error.noorderinfo'),true);
		}
		$adminmodel = new Admin_Model ( );
		$updatenum = $adminmodel->editmyorder('[mylink]', $myorder, 'mydataid_', 'id');
		MyqeeCMS::show_info(Myqee::lang ('admin/info.info.editmyorderok', $updatenum),true,'refresh');
	}

	public function mylinksave($id=0){
		$this -> db = Database::instance();
		if ($id>0){
			Passport::checkallow('info.mylink_edit');
		}else{
			Passport::checkallow('info.mylink_add');
		}
		$post = $_POST['mylink'];
		if (!($data['name']= htmlspecialchars($post['name']))){
			MyqeeCMS::show_error(Myqee::lang('admin/info.error.noacquname'),true);
		}
		$data['is_use'] = (int)$post['is_use'] == 0?0:1;
		$data['mydata_title'] = $post['mydata_title'];
		$data['myorder'] = (int)$post['myorder'];
		$data['mydata_order'] = $post['mydata_order']?(int)$post['mydata_order']:0;
		$data['mydata_target'] = $post['mydata_target']?(preg_replace("/[^a-zA-Z0-9_]+/",'',$post['mydata_target']=='[other]'?$post['mydata_target2']:$post['mydata_target'])):NULL;
		$data['mydata_limit'] = (int)$post['mydata_limit'];
		$data['mydata_id'] = (int)$post['mydata_id'];
		if ($id>0){
			//编辑
			$status = $this -> db -> update('[mylink]', $data ,array('id' => $id));
		}else{
			//添加
			$status = $this -> db -> insert('[mylink]', $data);
			$id = $status -> insert_id();
		}
		$data['id'] = $id;
		if ($status){
			MyqeeCMS::show_info(Myqee::lang('admin/info.info.saveok'),true,'refresh');
		}else{
			MyqeeCMS::show_info(Myqee::lang('admin/info.info.savenone'),true);
		}
	}
	
	public function save_child_links($id=0){
		Passport::checkallow('info.save_links');
		$link_list = (array)Myqee::config('mylinks/link_'.$id);
		$link_list2 = $link_list;		//复制一个用来处理
		$thepath = trim($_GET['path'],'/ ');
		if (!empty($thepath)){
			$arguments = explode('/',$thepath);
		}else{
			$arguments = array();
		}
		if (($arg_count = count($arguments)) > 0){
			for ($i=0;$i<$arg_count;$i++){
				if (!$link_list2[$arguments[$i]]){
					MyqeeCMS::show_error('不存在此父链接标识',true,'');
					break;
				}
				$link_list2 = $link_list2[$arguments[$i]]['submenu'];
			}
		}
		
		//处理提交来的数据
		$newlist = (array)$_POST['data'];
		$mynew_nav = array();
		foreach ($newlist as $value){
			if ($value['oldkey']!=$value['newkey']){
				//KEY发生变化
				if ( empty($value['newkey']) || $value['newkey']>0 || !preg_match("/^[a-zA-Z][a-zA-Z0-9_]*$/",$value['newkey'])){
					MyqeeCMS::show_error('抱歉，新标识“'.$value['newkey'].'”不符合要求，\n\n标识不能空，且不能含有非法字符，且不能为纯数字，且以字母开头\n\n请重新输入！',true,'');
				}
				if ($mynew_nav[$value['newkey']]){
					MyqeeCMS::show_error('存在相同的链接标识',true,'');
				}
			}
			//放到一个临时变量里
			$tmp_nav = array(
				'myorder' => (int)$value['myorder'],
				'infoid' => $link_list2[$value['oldkey']]['infoid'],
				'name' => trim($value['name']),
				'url' => str_replace('"','',trim($value['url'])),
				'target' => preg_replace("/[^a-zA-Z0-9_]+/",'',$value['target']=='[other]'?$value['target2']:$value['target']),
				'submenu' => $link_list2[$value['oldkey']]['submenu'],
			);
			if (empty($value['newkey']) || empty($tmp_nav['name']) || empty($tmp_nav['url'])){
				if (!($tmp_nav['infoid']>0)){
					if (isset($value['oldkey']) || (!empty($value['newkey']) || !empty($tmp_nav['name']) || !empty($tmp_nav['url']))) {
						MyqeeCMS::show_error('抱歉，自定义栏目的“标识”、“链接名称”和“链接地址”不能空！',true,'');
					}
					continue;
				}
			}
			$mynew_nav[$value['newkey']] = $tmp_nav;
			
		}
		
		$link_list = $this -> _set_link_array($link_list,$mynew_nav,$arguments);
		
		//更新到数据库
		$this -> db or $this -> db = Database::instance();
		$this -> db -> update('[mylink]',array( 'content' => serialize($link_list), 'count' => count($link_list) ),array( 'id' => $id ));
		
		//保存配置文件
		$status = MyqeeCMS::saveconfig('mylinks/link_'.$id,$link_list);
		if ($status){
			MyqeeCMS::show_info('恭喜，保存成功！','true','refresh');
		}else{
			MyqeeCMS::show_error('抱歉，保存失败，可能是没有写入文件的权限！','true');
		}
	}

	protected function _set_link_array($link_list,$mynew_nav,$arguments){
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
			$new_nav = $mynew_nav;
		}else{
			if (!is_array($link_list))return ;
			foreach ($link_list as $key => $value){
				$new_nav[$key] = $value;
				if ( $thearr == $key ){
					$new_nav[$key]['submenu'] = $this -> _set_link_array($new_nav[$key]['submenu'],$mynew_nav,$newarr);
				}
			}
		}
		
		if ($update==true)asort($new_nav);	//对当更新的数据重新排序
		return $new_nav;
	}

	public function renewfiles($id=0){
		Passport::checkallow('info.mylink_renewfiles');
		$id = (int)$id;
		$save_ok = $save_error = 0;
		$this -> db = Database::instance();
		$results = $this -> db -> select('*') -> from('[mylink]');
		if ($id >0 ){
			$results = $results -> where ('id',$id);
		}
		$results = $results -> where ('mydata_id != ','');
		$results = $results -> get() -> result_array(false);
		if (is_array($results)){
			foreach ($results as $item){
				$data = array();
				if(!$mydata = $this -> _renew_mydata($item['mydata_id'],$item,$item['mydata_limit'])){
					$save_error += 1;
					continue;
				}
				$mylink_config = Myqee::config('mylinks/link_'.$item['id']);
				$mylink = array();
				foreach ($mylink_config as $key => $mylink_item){
					if($mylink_item['infoid'] == '' || $mylink_item['infoid'] == NULL){
						$mylink[$key] = $mylink_item;
					}
				}
				$data = array_merge($mylink,$mydata);
				asort($data);
				$status = MyqeeCMS::saveconfig('mylinks/link_'.$item['id'],$data);
				
				// 子链接的个数更新到数据库里面
				$this -> db -> update('[mylink]',array( 'count' => count($data)),array( 'id' => $item['id'] ));
				
				if ( $status ){
					$save_ok += 1;
				}else{
					$save_error += 1;
				}
			}
		}
		MyqeeCMS::show_info('执行完毕，共更新链接'.$save_ok.'个，未更新：'.$save_error,TRUE,'refresh');
	}
	
	protected function _renew_mydata($id=0,$mylinkconfig,$count){
		Passport::checkallow('info.mylink_renewfiles');
		$id = (int)$id;
		if($id <= 0){
			return FALSE;
		}
		$mydata_config = Myqee::config('mydata/mydata_'.$id);
		$dbname = $mydata_config['dbname'];
		if ($dbname == '' || $dbname == NULL){
			return FALSE;
		}
		$sql = $mydata_config['sql'];
		if (!is_array($mydata_config) || is_null($sql) || $sql == ''){
			return FALSE;
		}else{
			$adminmodel = new Admin_Model ( );
			$results = $adminmodel -> db -> query($sql) -> result_array(false);
			$data = array();
			$db_config = Myqee::config('db/'.$dbname);
			if(count($results) < $count){
				$count = count($results);
			}
			if($count <= 0){
				return FALSE;
			}
			for ($i = 0; $i < $count; $i++){
				$idfield = $db_config['sys_field']['id']?$results[$i][$db_config['sys_field']['id']]:'id';
				$data['_info_'.$idfield] = array(
					'myorder' => $mylinkconfig['mydata_order'],
					'infoid' => $idfield,
					'name' => $results[$i][$mylinkconfig['mydata_title']]?$results[$i][$mylinkconfig['mydata_title']]:$results[$i][$db_config['sys_field']['title']],
					'url' => $adminmodel -> getinfourl($mydata_config['classid']?$mydata_config['classid']:$dbname,$results[$i]),
					'target' => $mylinkconfig['mydata_target'],
					'submenu' => NULL,
				);
			}
			return $data;
		}
	}
	
	public function mylink_output($mylinkid,$key = ''){
		Passport::checkallow('info.mylink_output');
		$this -> db or $this-> db = Database::instance();
		if ($this -> outgroup){
			$results = $this -> db -> from('[mylink]') -> where('group',$mylinkid) -> orderby('id') -> get() -> result_array(false);
		}else{
			$mylinkid = Tools::formatids($mylinkid , false);
			$results = $this -> db -> from('[mylink]') -> in('id',$mylinkid) -> orderby('id') -> get() -> result_array(false);
		}
		if (count($results) == 0){
			MyqeeCMS::show_info(Myqee::lang('admin/info.info.noouttemplate'),true);
		}
		$mylink = Tools::info_encryp($results,$key,true);
		if (!$mylink){
			MyqeeCMS::show_info(Myqee::lang('admin/info.info.nooutdb'),true);
		}
		download::force('./',$mylink,'mylink.txt');
	}
	
	public function mylink_inputmodel(){
		Passport::checkallow('info.mylink_input');		
		$view = new View('admin/mylink_input');
		$view -> render(TRUE);
	}
	
	public function mylink_input() {
		Passport::checkallow('info.mylink_input');
		//上传方式
		$thedata = $this->_getinputdata ();
		$this -> db = Database::instance();
		$inputok = 0;
		foreach ( $thedata as $item ) {
			$data = array ('mydata_id' =>  ( int )$item ['mydata_id'], 'limit' =>  ( int )$item ['limit'], 'title' => $item ['title'], 'name' => preg_replace ( "/[^\w]/", '', $item ['name'] ), 'myorder' => ( int )$item ['myorder'],  'is_show' => ( int )$item ['is_show'], 'count' => ( int )$item ['count'], 'is_use' => ( int )$item ['is_use'], 'content' => $item ['content']);
			
			$status = $this -> db -> insert ( '[mylink]', $data );
			$id = $status -> insert_id();
			if (count ( $status )) {
				//保存文件,输出提示信息
				if ( MyqeeCMS::saveconfig('mylinks/link_'.$id,unserialize($item['content'])) ){
					$inputok += 1;
				}
			}
		}
		MyqeeCMS::show_info ( Myqee::lang ( 'admin/info.info.inputok', $inputok ), true, 'goback' );
	}

	protected function _getinputdata() {
		$key = $_POST ['key'];
		$thedata = $_POST ['data'];
		
		if (empty ( $thedata ) && $_FILES ['upload'] ['size'] == 0) {
			MyqeeCMS::show_error ( Myqee::lang ( 'admin/info.error.inputdataempty' ), true );
		}
		if ($_FILES ['upload'] ['tmp_name']) {
			if ($_FILES ['upload'] ['size'] < 5000000) { //只操作5MB以内的文件
				if (! $thedata = @file_get_contents ( $_FILES ['upload'] ['tmp_name'] )) {
					MyqeeCMS::show_error ( Myqee::lang ( 'admin/info.error.inputreadfileerror' ), true );
				}
				//反解文件
				$thedata = Tools::info_uncryp ( $thedata, $key ,true );
			} else {
				MyqeeCMS::show_error ( Myqee::lang ( 'admin/info.error.inputerrorsize' ), true );
			}
		} else {
			if (strlen ( $thedata ) < 5000000) {
				//反解文件
				$thedata = Tools::info_uncryp ( $thedata, $key ,true );
			} else {
				MyqeeCMS::show_error ( Myqee::lang ( 'admin/info.error.inputerrorsize' ), true );
			}
		}
		if ($thedata === - 1) {
			MyqeeCMS::show_error ( Myqee::lang ( 'admin/info.error.inputtplbyedit' ), true );
		}
		
		if (! is_array ( $thedata )) {
			MyqeeCMS::show_error ( Myqee::lang ( 'admin/info.error.decodeerror' ), true );
		}
		return $thedata;
	}
}