<?php
class Member_Controller_Core extends Controller {
	function __construct(){
		parent::__construct();
		Passport::chkadmin();
	}

	public function index($p=1){
		$per = 20;

		$view = new View('admin/member_list');
		
		$adminmodel = new Admin_Model;
		
		$memberdb = MyqeeCMS::config('member.dbname');
		if ( !$adminmodel -> db -> table_exists($memberdb)){
			MyqeeCMS::show_error('指定的用户数据表不存在！');
		}

		$dbconfig = (array)MyqeeCMS::config('db/'.$memberdb);
		$sys_field = (array)$dbconfig['sys_field'];
		
		
		
		//数据表字段列表
		$dbfield = $adminmodel -> get_table_field($mydbname);
		
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
		//每页显示条数
		if ($search['limit']>0 && $search['limit']<=200){
			$per = $search['limit'];
		}
		
		$total = $adminmodel -> get_userdb_count($memberdb ,$where ,$sys_field['class_id'] ,$otherBuilder);

		$this -> pagination = new Pagination( array(
			'uri_segment'    => 'member/index',
			'total_items'    => $total,
			'items_per_page' => $per
			)
		);
		

		$view -> set('db_info_html', $adminmodel -> get_db_info_html($dbconfig ,null, false, $per , $this->pagination->sql_offset,$where , $orderby ,$otherBuilder ) );
		
		$view -> set('page', $this -> pagination -> render('digg') );
		$view -> render(TRUE);
	}

	public function dbset(){
		$adminmodel = new Admin_Model();
		$this -> db = Database::instance();
		$db_info = MyqeeCMS::config('member');
		if (!$db_info){
			//MyqeeCMS::show_error( Myqee::lang('admin/model.error.notheiddb'),false,'goback');
		}
		$db_info['config'] = unserialize($db_info['config']);
		$db_field = (array)$db_info['config']['field'];

		$mydbname = $db_info['name'];
		if (!$this -> db -> table_exists($mydbname)){
			MyqeeCMS::show_error( Myqee::lang('admin/model.error.nothedbname',$mydbname),false,'goback');
		}

		$field = array();
		$fieldlist = $this -> _get_fieldlist_array($mydbname);

		//print_r($fieldlist);exit;
		$db_field_tmp = array_merge( $db_field,$fieldlist );
		foreach ($db_field_tmp as $key => $value){
			if ($fieldlist[$key]){
				$field[$key] = $db_field[$key];
				$field[$key]['type'] = $fieldlist[$key]['type'];
				$field[$key]['comment'] = $db_field[$key]['comment']?$db_field[$key]['comment']:$fieldlist[$key]['comment'];
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
		$view = new View('admin/member_fieldlist');

		$view -> set ('id' , $id);
		$view -> set ('field' , Tools::json_encode($field));

		$view -> render(TRUE);
	}
	
	public function field_config(){
		$view = new View('admin/member_field_config');
		$memberConfig = (array)MyqeeCMS::config('member');
		if(!is_array($memberConfig)){
			MyqeeCMS::show_error('字段关联数据不存在！',true);
		}

		$field = '';
		$data = array();
		foreach ($memberConfig['member'] as $key => $member){
			if(!is_array($member))continue;
			$data[$key]['is_use'] = $member['is_use'];
			$data[$key]['comment'] = $member['comment'];
			$fieldarray = explode(',',$member['field']);
			for ($i = 0; $i < (count($fieldarray) - 1); $i++){
				$field .= $fieldarray[$i].'&nbsp;<font color="red"><=></font>&nbsp;';
			}
			if($i == (count($fieldarray) - 1)){
				$field .= $fieldarray[count($fieldarray) - 1];
			}
			$data[$key]['field'] = $field;
			$field = '';
		}

		$view->set('memberConfig', $data);
		$view -> render(TRUE);
	}

	public function addfield($id = ''){
		$member = array();
		if((int)$id >= 0){
			$memberConfig = (array)MyqeeCMS::config('member');
			$member = $memberConfig['member'][$id];
			$fieldarray = explode(',',$member['field']);
			foreach ($fieldarray as $dbfield){
				$sindbfield = explode('.',$dbfield);
				$member[$sindbfield[0]] = $sindbfield[1];
			}
		}

		$view = new View('admin/member_field_add');
		$adminmodel = new Admin_Model();
		
		$this->db = Database::instance ();
		$dblists = $this->db->select('name')->orderby ( 'myorder', 'ASC' )->orderby ( 'id', 'ASC' )->getwhere ( '[dbtable]', array ('ismemberdb' => 1 ) )->result_array ( FALSE );
		
		foreach ($dblists as $key => $value){
			$dblists[$key]['select'] = $adminmodel->get_table_field($value['name'],array('请选择'));
		}

		$view->set ( 'id',  $id);
		$view->set ( 'member',  $member);
		$view->set ( 'dblists',  $dblists);
		$view -> render(TRUE);
	}
	
	public function delfield($id = ''){
		if((int)$id < 0){
			MyqeeCMS::show_error('此字段关联不能删除！',true);
		}
		$memberConfig = (array)MyqeeCMS::config('member');
		if(!is_array($memberConfig)){
			MyqeeCMS::show_error('字段关联数据不存在！',true);
		}
		unset($memberConfig['member'][$id]);
		sort($memberConfig['member']);

		$status = MyqeeCMS::saveconfig('member',$memberConfig);
		if ($status){
			MyqeeCMS::show_info(Myqee::lang('admin/member.info.savemenuok'),true,'refresh');
		}else{
			MyqeeCMS::show_info(Myqee::lang('admin/member.info.savemenunone'),true);
		}
	}

	public function savefield($id = ''){
		$post = $_POST;
		if(!is_array($post)){
			MyqeeCMS::show_error('保存数据错误！',true);
		}

		$field = '';
		$data = array();
		$data['is_use'] = $post['is_use'];
		for ($k = 0; $k < $field_num = $post['field_num']; $k++){
			if(isset($post['dbname_'.$k]) && $post['dbname_'.$k] != '' && $post['dbname_'.$k] != NULL && isset($post['field_'.$k]) && $post['field_'.$k] != '' && $post['field_'.$k] != NULL){
				$field .= $post['dbname_'.$k].'.'.$post['field_'.$k];
			}
			if($k != ($field_num - 1)){
				$field .= ',';
			}
		}
		$data['comment'] = $post['comment'];
		$data['field'] = $field;
		$memberConfig = (array)MyqeeCMS::config('member');
		if(((int)$id >= 0) && $id != ''&& $id != NULL){
			$memberConfig['member'][$id] = $data;
		}else{
			$id = count($memberConfig['member']);
			$memberConfig['member'][$id] = $data;
		}
		$status = MyqeeCMS::saveconfig('member',$memberConfig);
		if ($status){
			MyqeeCMS::show_info(Myqee::lang('admin/member.info.savemenuok'),true,'refresh');
		}else{
			MyqeeCMS::show_info(Myqee::lang('admin/member.info.savemenunone'),true);
		}
	}
	
	public function data_update(){
		$view = new View('admin/data_update');

		$this->db = Database::instance ();
		$dblists = $this->db->select('name')->orderby ( 'myorder', 'ASC' )->orderby ( 'id', 'ASC' )->getwhere ( '[dbtable]', array ('ismemberdb' => 1 ) )->result_array ( FALSE );

		$fromdbselect = '<select name="fromtable[]" id="fromtable" size="36" style="width:160px;" multiple="multiple"><option value="0" selected="selected">选择全部数据表</option>';
		$todbselect = '<select name="totable[]" id="totable" size="36" style="width:160px;" multiple="multiple"><option value="0" selected="selected">选择全部数据表</option>';
		for ($i = 0; $i < (count($dblists) - 1); $i++){
			$fromdbselect .= '<option value="'.$dblists[$i]['name'].'" style="background:#f4f4f4;color:#ccc;" >├'.$dblists[$i]['name'].'</option>';
			$todbselect .= '<option value="'.$dblists[$i]['name'].'" style="background:#f4f4f4;color:#ccc;" >├'.$dblists[$i]['name'].'</option>';
		}
		if($i == (count($dblists) - 1)){
			$fromdbselect .= '<option value="'.$dblists[$i]['name'].'" style="background:#f4f4f4;color:#ccc;" >└'.$dblists[$i]['name'].'</option>';
			$todbselect .= '<option value="'.$dblists[$i]['name'].'" style="background:#f4f4f4;color:#ccc;" >└'.$dblists[$i]['name'].'</option>';
		}
		$fromdbselect .= '</select>';
		$todbselect .= '</select>';

		$view -> set ('fromtable' , $fromdbselect);
		$view -> set ('totable' , $todbselect);
		$view -> render(TRUE);
	}
	
	public function data_update_save(){

		$post = $_POST;
		if(!is_array($post)){
			MyqeeCMS::show_error('保存数据错误！',true);
		}

		$fromtable = $post['fromtable'];
		$totable = $post['totable'];
		if(count($fromtable) != 1 || count($totable) != 1 || in_array('0',$fromtable) || in_array('0',$totable)){
			MyqeeCMS::show_error('只允许单表同步！',true);
		}
		$memberConfig = (array)MyqeeCMS::config('member');
		if(!is_array($memberConfig)){
			MyqeeCMS::show_error('字段关联数据不存在！',true);
		}
		$memberlist = $memberConfig['member'];
		if(!is_array($memberlist)){
			MyqeeCMS::show_error('字段关联数据不存在！',true);
		}
		$this->db = Database::instance();
		//数据提取语句
		$select_sql = 'select ';
		//数据同步语句
		$replace_sql = 'replace into ';
		for ($k = 0; $k < count($memberlist); $k++){
			$fieldarray = explode(',',$memberlist[$k]['field']);
			for ($i = 0; $i < count($fieldarray); $i++){
				$sindbfield = explode('.',$fieldarray[$i]);
				if($i == 0){
					if(in_array($sindbfield[0],$fromtable)){
						if ($k == (count($memberlist) - 1)){
							$select_sql .= $sindbfield[1].' from '.$this->db->table_prefix().$sindbfield[0];
						}else{
							$select_sql .= $sindbfield[1].',';
						}
					}
				}else{
					if(in_array($sindbfield[0],$totable)){
						if($k == 0){
							$replace_sql .= 'mycms_'.$sindbfield[0].' ( '.$sindbfield[1].',';
						}else if ($k == (count($memberlist) - 1)){
							$replace_sql .= $sindbfield[1].') values ';
						}else{
							$replace_sql .= $sindbfield[1].',';
						}
					}
				}
			}
		}
		$fromdbdata = $this->db->query($select_sql)->result_array( FALSE );
		$v = 0;
		foreach ($fromdbdata as $item){
			$j = 0;
			$replace_sql .= ' ( ';
			foreach ($item as $data){
				if($j == (count($item) - 1)){
					$replace_sql .= '\''.$data.'\''?'\''.$data.'\'':'';
				}else{
					$replace_sql .= '\''.$data.'\','?'\''.$data.'\',':''.',';
				}
				$j++;
			}
			if($v == (count($fromdbdata) - 1)){
				$replace_sql .= ' ) ';
			}else{
				$replace_sql .= ' ), ';
			}
			$v++;
		}
		$updata_ok = $this->db->query($replace_sql);
	}
}