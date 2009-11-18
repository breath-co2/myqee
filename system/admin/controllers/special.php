<?php
/**
 * 专题控制器
 * @author Myqee Team
 *
 */
class Special_Controller extends Controller {
	
	function __construct(){
		parent::__construct(NULL);
		$this->session = Passport::chkadmin();
	}
	
	public function index () {
		$this->mylist(1);
	}
	
	public function mylist ($page=1) {
		Passport::checkallow('class.special_list');
		$view = new View('admin/special_list');
		$adminmodel = new Admin_Model;
		$per = 20;

		$num = $adminmodel -> db -> count_records('[special]');
		$this -> pagination = new Pagination( array(
			'uri_segment'    => 'mylist',
			'total_items'    => $num,
			'items_per_page' => $per
		) );

		$view -> set ( 'list' , $adminmodel -> get_speciallist(array(),$per,$this -> pagination -> sql_offset) );

		$view -> set('page', $this -> pagination -> render('digg') );
		$view -> render(TRUE);
	}
	
	public function add () {
		$this->edit(0,false);
	}
	public function copy ($sid) {
		$this->edit($sid,true);
	}
	public function edit ($sid,$iscopy=false) {
		Passport::checkallow('class.special_add');
		$view = new View('admin/special_add');	//load views
		$adminmodel = new Admin_Model;		//load models
		
		if ( $sid > 0 ){
			$info = $adminmodel -> get_specialinfo($sid);
		}
		
		//获取所有栏目（若此栏目是某个站点下的，则只读取同站点下的栏目）
		$classtree = $adminmodel -> get_allclass_array();
		
		if (!empty($info['classides'])) {
			$info['classides'] = explode('|',trim($info['classides'],'|'));
		}
		if ( $sid > 0 ){
			$view -> page_title = '修改专题 '.$info['title'];
			if ($info){
				if ($iscopy){
					unset($info['sid']);
					$info['title'] = $info['title'] .'(1)';
					$thisclass['filepath'] = $thisclass['filepath'] .'_1';
					$view -> page_title = '复制专题';
				}
				$view -> set('info',$info);
			}
		} else {
			$view -> page_title = '新增专题';
			$info = array();
			$info['islist'] = 0;
			$info['iscover'] = 1;
			$info['filepath'] = '/special/';
			$info['isrecursion'] = 1;
			$view -> set('info',$info);
		}
		$db = Database::instance();
		$_tmp = $db->query("select cate from `".$db->table_prefix()."[special]` where !isnull(cate) group by cate")->result_assoc();
		$tplcate = array();
		foreach(array_keys($_tmp) as $val) {
			$tplcate[$val] = $val;
		}
		$dbfield = array('id'=>'id','sid'=>'专辑ID','class_id'=>'栏目ID','createtime'=>'创建时间','posttime'=>'修改时间','isheadlines'=>'是否头条','ontop'=>'置顶','ishot'=>'是否热门','iscommend'=>'是否推荐','myorder'=>'自定义顺序');
		$view -> set ('dbfield' , $dbfield );
		$view -> set ('tplcate' , $tplcate );		
		$view -> set ('classtree' , $classtree );		
		$view -> set ('cover_tplarray' , $adminmodel -> get_alltemplate('cover') );			//cover template
		$view -> set ('list_tplarray' , $adminmodel -> get_alltemplate('list') );			//list template
		$view -> render(TRUE);		
	}
	
	public function save() {
		Passport::checkallow('class.special_add');
		$data = array();
		$post = $_POST['info'];
		$sid = intval($post['sid']);
		$data['title'] = trim($post['title']);
		$_filepath = trim($post['filepath']);
		if (isset($post['isnothtml']) && empty($_filepath)) {
			MyqeeCMS::show_error('请填写专辑路径！',true);
		}
		
		if (!isset($post['iscover']) && !isset($post['islist'])) {
			MyqeeCMS::show_error('请确定使用封面模板或者列表模板',true);
		}
		if (isset($post['isnothtml'])) {
			$_tmp = preg_replace('#[\\/]+#','/',WWWROOT.rtrim($post['filepath'],'/'));
			if ($_tmp == preg_replace('#[\\/]+#','/',WWWROOT.'/special')) {
				MyqeeCMS::show_error('请在special 后面加上目录名称，或者自定义目录',true);
			}
			if (file_exists($_tmp) && $sid < 1) {
				MyqeeCMS::show_error('请重新填写专辑路径！，目录已经存在',true);
			}
			if (is_file($_tmp)) {
				MyqeeCMS::show_error('请重新填写专辑路径！，填写的目录是普通文件',true);
			}
		}
		
		if (empty($post['classides'])) {
			MyqeeCMS::show_error('请选择所属栏目！',true);
		}
		$data['classides'] = '|' . implode('|',$post['classides']) . '|';
		$data['isnothtml'] = isset($post['isnothtml']) ? 0 :1;
		$data['iscover'] = intval($post['iscover']);
		$data['cover_tohtml'] = intval($post['cover_tohtml']);
		$data['cover_cachetime'] = intval($post['cover_cachetime']);
		$data['filepath'] = trim($post['filepath']);
		$data['cover_tplid'] = intval($post['cover_tplid']);
		$data['islist'] = intval($post['islist']);
		$data['list_tohtml'] = intval($post['list_tohtml']);
		$data['list_cachetime'] = intval($post['list_cachetime']);
		$data['list_tplid'] = intval($post['list_tplid']);
		$data['list_pernum'] = intval($post['list_pernum']);
		$data['list_count'] = intval($post['list_count']);
		$data['myorder'] = trim($post['myorder']);
		$data['hostname'] = trim($post['hostname']);
		$data['thumb'] = trim($post['thumb']);
		$data['htmlintro'] = trim($post['htmlintro']);
		$data['manage_pernum'] = intval($post['manage_pernum']);
		$data['keyword'] = trim($post['keyword']);
		$data['description'] = trim($post['description']);
		$data['cate'] = trim($post['cate']);
		$data['list_filename'] = trim($post['list_filename']) ? trim($post['list_filename']) : 'list_{{page}}.html';
		$data['cover_filename'] = trim($post['cover_filename']) ? trim($post['cover_filename']) : 'index.html';
		$data['list_byfield'] = trim($post['list_byfield']);
		$data['list_orderby'] = trim($post['list_orderby']);
		$data['isrecursion'] = isset($post['isrecursion']) ? 1 :0;
		$db = Database::instance();
		if ($sid >0) {
			$query = $db->update ('[special]',$data,array('sid'=>$sid));
		} else {
			$query = $db->insert ('[special]',$data);
		}

		if ($query->count() > 0) {
			if ($sid >0) {
				MyqeeCMS::show_ok('更新成功',true);
			}else{
				MyqeeCMS::show_ok('添加成功',true);
			}
		}else{
			if ($sid >0) {
				MyqeeCMS::show_error('更新失败',true);
			} else {
				MyqeeCMS::show_error('添加失败',true,'refresh');
			}
		}
	}
	
	/**
	 * 删除专题
	 * @param $sid
	 */
	public function del ($sid) {
		Passport::checkallow('class.special_del');
		$sid = intval($sid);
		$db = Database::instance();
		$query = $db->getwhere('[special]',array('sid'=>$sid))->result_array(false);
		if (empty($query)) {
			MyqeeCMS::show_error('专题不存在',true);
		}
		
		$filepath = $query[0]['filepath'];
		Tools::remove_dir(WWWROOT.'/'.$filepath);
		
		$query = $db->delete('[special_info]',array('sid'=>$sid));
		$query = $db->delete('[special]',array('sid'=>$sid));
		if ($query->count() >0) {
			MyqeeCMS::show_ok('删除成功',true,'refresh');
		} else {
			MyqeeCMS::show_error('删除失败',true);
		}
	}
	
	/**
	 * 删除专题信息
	 * @param $param
	 */
	public function delinfo ($param) {
		Passport::checkallow('class.special_delinfo');
		$db = Database::instance();
		$allids = array();
		if (ereg(',',$param)) {
			$allids = explode(',',$param);
		} else {
			$allids[] = $param;
		}
		$delnums = 0;
		foreach($allids as $val) {
			if (empty($val)) {
				continue;
			}
			$val = intval($val);
			$query = $db->delete('[special_info]',array('id'=>$val));
			$delnums += $query->count();
		}
		
		if ($delnums >0) {
			MyqeeCMS::show_ok('成功删除'.$delnums.'条记录',true,'refresh');
		} else {
			MyqeeCMS::show_error('删除失败',true);
		}
	}
	
	public function manageinfo($page,$sid) {
		Passport::checkallow('class.special_manageinfo');
		$sid = intval($sid);
		$db = Database::instance();
		$query = $db->getwhere('[special]',array('sid'=>$sid))->result_array(false);
		if (empty($query)) {
			MyqeeCMS::show_error('找不到改专题',true);
		}
		$special_title = $query[0]['title'];
		$limit = 20;
		$num = $db->where('sid',$sid)->count_records ( '[special_info]' );
		
		$pagination = new Pagination( 
			array(
				'uri_segment'	 => 'manageinfo',
				'total_items'    => $num,
				'items_per_page' => $limit,
			)
		);
		$pageurl = $pagination->render();
		$list = $db->orderby(array('myorder'=>'asc','id'=>'asc'))->getwhere('[special_info]',array('sid'=>$sid),$limit, $pagination -> sql_offset)->result_array(false);
		foreach($list as $key=>$val) {
			$val['stitle'] = Tools::substr($val['title'],0,20);
			$val['isshow'] = $val['isshow'] ? '是' : '否';
			$val['isheadlines'] = $val['isheadlines'] ? '是' : '否';
			$val['ontop'] = $val['ontop'] ? '是' : '否';
			$val['ishot'] = $val['ishot'] ? '是' : '否';
			$val['iscommend'] = $val['iscommend'] ? '是' : '否';
			$val['editurl'] = $val['class_id'] >0 ? "/info/editbyclassid/{$val['class_id']}/{$val['infoid']}": "/info/edit/{$val['dbname']}/{$val['infoid']}/";
			$val['URL'] = !empty($val['linkurl']) ? $val['linkurl'] : $val['url'];
			$list[$key] = $val;
		}
		$view = new View('admin/special_manageinfo');	//load views
		
		
		$view->set('pageurl',$pageurl);
		$view->set('special_title',$special_title);
		$view->set('list',$list);
		$view->render(true);
	}
	
	public function editorder($type='list'){
		if ( !($myorder = $_GET['order']) ){
			MyqeeCMS::show_error('缺少参数',true);
		}
		$adminmodel = new Admin_Model();
		if ($type == 'list') {
			$updatenum = $adminmodel -> editmyorder('[special]',$myorder,'specialid_','sid');
		} elseif($type == 'infolist') {
			$updatenum = $adminmodel -> editmyorder('[special_info]',$myorder,'id_','id');
		}
		
		MyqeeCMS::show_info("成功更改{$updatenum}栏目排序！",true,'refresh');
	}
}