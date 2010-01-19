<?php
class Admin_Model_Core extends Model {

	/**
	 * 数据库
	 *
	 * @var object $db
	 */
	public $db;
	/**
	 * 所有栏目树形数组结构
	 *
	 * @var array
	 */
	public $class_tree;
	/**
	 * 所有栏目列表，以classid排列
	 *
	 * @var array
	 */
	public $class_all_list;
	
	
	public $site_id;
	public $allow_db;
	public $allow_class;

	public function __construct(){
		parent::__construct();
		$this -> site_id = (int)$_SESSION['now_site'];
		
		if ($_SESSION['admin']['dbset']!='-ALL-'){
			$this -> allow_db = explode(',',$_SESSION['admin']['dbset']);
		}else{
			$this -> allow_db = '-ALL-';
		}
		
		if ($_SESSION['admin']['classset']!='-ALL-'){
			$this -> allow_class = explode(',',$_SESSION['admin']['classset']);
		}else{
			$this -> allow_class = '-ALL-';
		}
	}

	/**
	 * 获取适用于下拉框的所有模型
	 * 将参数一个二维数组
	 *
	 * @param string $addoption 增加的option
	 * @return array 模型二维数组
	 */
	public function get_model_for_dropdown($addoption = null){
		$modelarray = $this -> get_model_array();
		if ($addoption!==null){
			$models[0] = $addoption;
		}
		if (count($modelarray)){
			foreach ($modelarray as $model){
				$models[$model['id']] = $model['modelname'];
			}
		}
		return $models;
	}

	/**
	 * 获取所有模型数据
	 *
	 * @param boolean $isall 是否返回全部
	 * @return array 模型数据
	 */
	public function get_model_array($isall = false , $select = '*'){
		$this -> db ->select($select) -> from ( '[model]' );
		if ($isall===false){
			$this -> db -> where('isuse',1);
		}elseif ($isall>0){
			$this -> db -> where('id',$isall);
		}
//		if ($this -> site_id>0){
//			$modelarray = $modelarray -> where('siteid',$this -> site_id);
//		}
		if ($_SESSION['admin']['dbset']!='-ALL-'){
			$this -> db -> in('dbname',explode(',',$_SESSION['admin']['dbset']));
		}
		$modelarray = $this -> db -> get () -> result_array ( FALSE );
		if ($isall>0){
			$modelarray = $modelarray[0];
		}
		return $modelarray;
	}
	
	/**
	 * 获取数据表数据
	 *
	 * @return array 模型数据
	 */
	public function get_db_array($tablename = NULL,$default = FALSE ,$isuse = true ,$mywhere=null,$select = '*'){
		if ($isuse)$where = array('isuse'=>1);
		if ($tablename){
			if (is_numeric($tablename)){
				$where['id'] = (int)$tablename;
			}else{
				$where['name'] = $tablename;
			}
		}
		if ($default){
			$where['isdefault'] = 1;
		}
		if (is_array($mywhere)){
			$where += $mywhere;
		}
//		if ($this -> site_id>0){
//			$where['siteid'] = $this -> site_id;
//		}
		$this -> db ->select($select) ->from ( '[dbtable]' ) -> where($where);
		if ($_SESSION['admin']['dbset']!='-ALL-'){
			$this -> db -> in('name',explode(',',$_SESSION['admin']['dbset']));
		}
		$dbarray = $this -> db ->get ()->result_array ( FALSE );
		return $dbarray;
	}

	/**
	 * 获取指定栏目数组
	 *
	 * @param number $classid
	 * @param string $selectit
	 * @return array/false
	 */
	public function get_class_array($classid = 0 , $select = '*') {
		if ($classid==0)return false;
		if ($this -> allow_class!='-ALL-' && !in_array($classid,$this -> allow_class)){
			return false;
		}
		if (isset($this->class_all_list[$classid])){
			if ($select!='*'){
				$myfiled = explode(',',str_replace(array(' ','`'),'',$select));
				$tmpclass = array();
				foreach ($myfiled as $field){
					$tmpclass[$field] = $this -> class_all_list[$classid][$field];
				}
				return $tmpclass;
			}else{
				return $this -> class_all_list[$classid];
			}
		}else{
			$where = array('classid' => $classid);
			if ($this -> site_id>0){
				$where['siteid'] = $this -> site_id;
			}
			$result = $this -> db -> select($select) -> from('[class]')-> where($where) -> limit(1) -> get() -> result_array(FALSE);
			$result = $result[0];
			if ($this -> allow_db != '-ALL-'){
				if ($result['dbname'] && !in_array($result['dbname'],$this -> allow_db)){
					//没有表管理权限的将被剔除
					$result = NULL;
				}
			}
			$this -> class_all_list[$classid] = $result;
			return $result;
		}
	}

	public function get_allclass_count(){
		$c = $this -> db;
		if ($this -> site_id>0){
			$c = $c -> where(array('siteid'=>$this -> site_id));
		}
		if ($this -> allow_db!='-ALL-'){
			$c = $c -> in ('dbname',$this -> allow_db);
		}
		if ($this -> allow_class!='-ALL-'){
			$c = $c -> in ('classid',$this -> allow_class);
		}
		
		return $c -> count_records('[class]');
	}

	public function get_allclass_forlist($select = '*',$per = 15 , $offset = 0 ){
		$this -> db -> select ( $select );
		if ($this -> site_id>0){
			$this -> db -> where(array('siteid'=>$this -> site_id));
		}
		if ($this -> allow_db!='-ALL-'){
			$this -> db -> in ('dbname',$this -> allow_db);
		}
		if ($this -> allow_class!='-ALL-'){
			$this -> db -> in ('classid',$this -> allow_class);
		}
		$myclass = $this -> db -> from ( '[class]' )-> orderby ( 'classid', 'ASC' ) -> limit($per,$offset) ->get ()->result_array ( FALSE );
		return $myclass;
	}

	/**
	 * 获取所有栏目的树状数组
	 *
	 * @param number $bclassid
	 * @param string $select
	 * @param number $treedepth 返回的深度
	 * @param boolean $isincludeself 是否包括本身
	 * @param array $where 查询条件
	 * @param boolean $isaddurl 是否附加栏目链接地址
	 * @return array 栏目树状结构数组
	 */
	public function get_allclass_array($bclassid = 0 ,$treedepth = 0 , $isincludeself = false,$where = null , $isaddurl = false) {
		if (is_array($where) || !isset($this -> classArray)){
			$this -> db -> from ( '[class]' ) -> orderby ( 'myorder', 'asc' ) -> orderby ( 'classid', 'asc' );
			if (is_array($where)){
				$this -> db -> where($where);
			}
			if ($this -> site_id>0){
				$this -> db -> where(array('siteid'=>$this -> site_id));
			}
			if ($this -> allow_db!='-ALL-'){
				$this -> db -> in ('dbname',$this -> allow_db);
			}
			if ($this -> allow_class!='-ALL-'){
				$this -> db -> in ('classid',$this -> allow_class);
			}
			$tmparray = $this -> db -> get ()-> result_array(FALSE);
			if ($this -> classArray)$this -> classArray_bak = $this -> classArray;
			$this -> classArray = $tmparray;
			unset($tmparray);
		}
		if (is_array($this -> classArray) && count ( $this -> classArray ) > 0) {
			$tmpTree = array();
			foreach ( $this -> classArray as $k=>$tmpclass ) {
				if (!$this -> classArray[$k]['URL']){
					$tmpclass['URL'] = $this -> classArray[$k]['URL'] = $this -> get_class_url($tmpclass);
				}
				$tmpTree [$tmpclass ['bclassid']] [] = $tmpclass;
				$tmpList [$tmpclass['classid']] = $tmpclass;
			}
			
			$this -> class_tree = $tmpTree;
			$this -> class_all_list = $tmpList;
			$myclass =  $this ->_listclass ( $bclassid ,$treedepth , 0 );
			if ($isincludeself && $bclassid>0){
				if ($bclass = $this -> class_all_list[$bclassid]){
					$bclass['sonclassarray'] = $myclass;
					$tmpmyclass = array($bclassid => $bclass);
					$myclass = $tmpmyclass;
				}
			}
		}else{
			$myclass = array();
		}
		
		if (is_array($where)){
			$this -> classArray = $this -> classArray_bak;
			unset($this -> classArray_bak);
		}
		return $myclass;
	}

	/**
	 * 列出栏目数装结构
	 *
	 * @param number $bclassid
	 * @param number $treedepth 返回的深度
	 * @param number $now_treedepth 当前的深度
	 * @return array
	 */
	protected function _listclass($bclassid = 0 , $treedepth =0 , $now_treedepth = 0) {
		$bclassid = ( int ) $bclassid;
		if (!($bclassid > 0)) {
			$bclassid = 0;
		}
		$tempclass = $this -> class_tree [$bclassid];
		if (count ( $tempclass ) > 0) {
			if ($treedepth>0)$now_treedepth++;
			foreach ( $tempclass as $r ) {
				$classarray [$r ['classid']] = $r;
				if ($now_treedepth==0 || $now_treedepth < $treedepth){
					$classarray [$r ['classid']] ['sonclassarray'] = $this ->_listclass ( $r ['classid'] ,$treedepth , $now_treedepth );
				}
			}
			return $classarray;
		}
	}
	
	/**
	 * 获取所有子栏目的ID
	 *
	 * @param int $classid
	 * @param boolean $resultids 返回所有ID的数组还是以栏目ID为key的数组
	 * 若$resultids=true 则返回类似array(1,3,4,5);其中1,3,4,5为栏目ID
	 * 若$resultids=false(默认) 则返回类似array(1=>'栏目一',3=>'栏目三',4=>'栏目四',5=>'栏目五');
	 * @param $addarr 在开头添加的数组
	 * @return array $arr
	 */
	public function get_sonclass_id($classid,$resultids=false,$addarr=null){
		if(is_array($addarr)){
			$arr = $addarr;
		}else{
			$arr = array();
		}
		if (!$classid)return $arr;
		$this -> db -> select('classid'.($resultids?'':',classname')) -> like('fatherclass','|'.$classid.'|');
		if ($this -> allow_db!='-ALL-'){
			$this -> db -> in ('dbname',$this -> allow_db);
		}
		if ($this -> allow_class!='-ALL-'){
			$this -> db -> in ('classid',$this -> allow_class);
		}
		$result = $this -> db -> get('[class]') -> result_array(FALSE);
		$c = count($result);
		
		if ($resultids){
			for ($i=0;$i<$c;$i++){
				$arr[] = $result[$i]['classid'];
			}
		}else{
			for ($i=0;$i<$c;$i++){
				$arr[$result[$i]['classid']] = $result[$i]['classname'];
			}
		}
		return $arr;
	}
	
	/**
	 * 更新所有指定栏目的数组缓存
	 *
	 * @param int $classid
	 */
	public function renew_classcatch($classid=0){
		$this -> db -> from('[class]');
		if(is_array($classid) && count($classid)){
			$this -> db -> in ('classid',$classid);
		}elseif ($classid>0){
			$this -> db -> where ('classid',$classid);
		}
		$db = $this -> db -> get() ->result_array(FALSE);
		
		$run_ok = $run_error = 0;
		foreach ($db as $item){
			if (MyqeeCMS::saveconfig('class/class_'.$item['classid'],$item)){
				$run_ok++;
			}else{
				$run_error++;
			}
		}
		return array($run_ok,$run_error);
	}

	/**
	 * 获取转换为JSON字符串的所有栏目的路径
	 *
	 * @param array $thisarray
	 * @return string 经过json转换的所有栏目路径
	 */
	public function get_allclass_jsonpath($thisarray=''){
		if (!is_array($thisarray)){
			if (!$this -> classArray){
				$this -> get_allclass_array();
			}
			$thisarray = $this -> classArray;
		}
		foreach ($thisarray as $class){
			$tmpstr[$class['classid']] = $class['classpath'];
		}
		return Tools::json_encode($tmpstr);
	}

	public function get_template_array($tplid,$select = '*'){
		$tplid = (int)$tplid;
		if (!($tplid>0))return false;

		$tpl = $this -> get_alltemplate_array($tplid,null,$select);
		return $tpl[0];
	}

	public function get_alltemplate_array($tplwhere = null,$orderby = array('myorder'=>'DESC'),$select = '*',$limit=null){
		if (!is_array($tplwhere)){
			if ($tplwhere){
				$tplwhere = array('id' => $tplwhere);
			}else{
				$tplwhere = array();
			}
		}
		$tplwhere['group'] = $_SESSION['now_tlpgroup']?$_SESSION['now_tlpgroup']:Myqee::config('template.default');
		
		$this -> db ->select ( $select )->from ( '[template]' ) -> where($tplwhere);
		if (is_array($orderby)){
			foreach ($orderby as $k=>$v){
				$this -> db -> orderby($k,$v);
			}
		}
		if ($limit && count($limit)==2){
			list($l1,$l2) = $limit;
			$this -> db -> limit($l1,$l2);
		}
		$mytpl = $this -> db -> get () -> result_array ( FALSE );
		return $mytpl;
	}
	/**
	 * 获取所有模板数据
	 *
	 * @param string $type news|list|cover|search
	 * @return array 模板数组
	 */
	public function get_alltemplate($type = '',$group=NULL){
		if ( !$this->templates ){
			$where = array('isuse'=>1);
			if ($group){
				$where['group'] = $group;
			}else{
				$where['group'] = $_SESSION['now_site_tlpgroup']?$_SESSION['now_site_tlpgroup']:Myqee::config('template.default');
			}
			$where['group'] or $where['group']='default';
			$this -> templates = $this -> db -> from ( '[template]' ) -> where($where) -> orderby('cate','asc') -> orderby('myorder','DESC') ->get ()->result_array ( FALSE );
		}
		if (count ( $this->templates ) > 0) {
			$template =array();
			foreach ($this->templates as $item){
				if ($item['type'] == $type || empty($type)){
					$template[$item['cate']][$item['id']] = $item['tplname'];
				}
			}
		}
		return $template;
	}

	/**
	 * 校验子栏目
	 *
	 * @param number $newbclassid 新的父栏目ID
	 * @param unknown_type $classid 当前栏目ID
	 * @return boolean true/false
	 */
	public function check_fatherclass($newbclassid,$classid){
		if ($newbclassid == $classid){
			return false;
		}
		$this -> db ->select ( "classid" )->from ( '[class]' );
		if ($this -> site_id>0){
			$this -> db -> where(array('siteid'=>$this -> site_id));
		}
		$query = $this -> db -> like('fatherclass','|'.$classid.'|') -> get() -> result_array ( FALSE );
		foreach ($query as $v){
			if ($v['classid'] == $newbclassid){
				return false;
			}
		}
		return true;
	}

	public function get_dbname($modelid){
		if (!($modelid>0))return false;
		$where = array('id'=>$modelid);
//		if ($this -> site_id>0){
//			$where['siteid'] = $this -> site_id;
//		}
		$query = $this -> db ->select ( "dbname" )->from ( '[model]' ) -> where($where) ->get()->result_array ( FALSE );

		return $query[0]['dbname'];
	}

	/**
	 * 保存栏目
	 *
	 * @param array $post
	 * @return update num
	 */
	public function save_edit_class($post){
		$post['classid'] = (int)$post['classid'];

		$classdata['isnothtml'] = isset($post['isnothtml']) && $post['isnothtml']==0?0:1;

		if ($post['classid']>0){
			//eidt...
			$old_class = $this -> get_class_array( (int)$post['classid'] );
			if ( !is_array($old_class) ){
				MyqeeCMS::show_info(Myqee::lang('admin/class.info.nothisclassid'),TRUE);
			}

			//check fatherclass
			$oldbclassid = $old_class['bclassid'];
			if ( $oldbclassid != (int)$post['bclassid'] ){
				$classdata['bclassid'] = (int)$post['bclassid'];
				if (self::check_fatherclass($post['bclassid'],$post['classid']) == false){
					MyqeeCMS::show_error(Myqee::lang('admin/class.error.canotinsonclass'),true);
				}
				$ischangebclassid = true;
			}
			//old class path
			$oldclaspath = $old_class['classpath'];
		}else{
			//add...
			$classdata['bclassid'] = (int)$post['bclassid'];
			if ($classdata['bclassid']>0){
				$oldbclassid = 0;
				$ischangebclassid = true;
			}
		}

		$classdata['classname'] = Tools::formatstr($post['classname']);
		if (empty($classdata['classname'])){
			MyqeeCMS::show_error(Myqee::lang('admin/class.error.needclassname'),true);
		}

		$classdata['modelid'] = (int)$post['modelid'];
		if ($post['modelid']==0){
			MyqeeCMS::show_error(Myqee::lang('admin/class.error.needmodelid'),true);
		}

		if ( !($post['classid']) || !$classdata['dbname'] || $old_class['modelid'] != $classdata['modelid']  ){
			$classdata['dbname'] = $this -> get_dbname( $classdata['modelid'] );
		}

		if ($classdata['isnothtml']==0){
			$classdata['classpath'] = preg_replace("/[^a-zA-Z0-9_\-]+/",'',$post['classpath']);
			if (empty($classdata['classpath'])){
				MyqeeCMS::show_error(Myqee::lang('admin/class.error.needclasspath'),true);
			}
			$this -> bclass_new = $this -> get_class_array( (int)$post['bclassid'] );
			if ($this -> bclass_new['isnothtml'] == 0){
				$classdata['classpath'] = ($this -> bclass_new['classpath']?$this -> bclass_new['classpath'].'/':'') . $classdata['classpath'];
				if ( ($classdata['classpath'] != $oldclaspath && $oldclaspath) || !($post['classid']>0) ){
					if (is_dir(WWWROOT.$classdata['classpath'])){
						MyqeeCMS::show_error(Myqee::lang('admin/class.error.folderexist',str_replace('\\','/',WWWROOT.$classdata['classpath'])),true);
					}
				}
			}else{
				unset($classdata['classpath']);
				$classdata['isnothtml'] = 1;
			}
		}

		//-------------------- for cover config
		$classdata['iscover'] = $post['iscover']==1?1:0;
		if ( $classdata['iscover'] == 1 ){
			$classdata['cover_tohtml'] = $post['cover_tohtml']==1||$classdata['isnothtml']==1?1:0;
			$classdata['cover_cachetime'] = (int)$post['cover_cachetime'];
			$classdata['cover_tplid'] = (int)$post['cover_tplid'];
			if ($classdata['cover_tplid']==0){
				MyqeeCMS::show_error(Myqee::lang('admin/class.error.needcovertemplate'),true);
			}
			$classdata['cover_filename'] = preg_replace("/[^a-zA-Z0-9._~,\-\{\}]+/",'',$post['cover_filename']);
			$classdata['cover_hiddenfilename'] = $post['cover_hiddenfilename']==1?1:0;
		}

		//-------------------- for list config
		$classdata['islist'] = $post['islist']==1?1:0;
		if ( $classdata['islist'] == 1 ){
			$classdata['list_tohtml'] = $post['list_tohtml']==1||$classdata['isnothtml']==1?1:0;
			$classdata['list_cachetime'] = (int)$post['list_cachetime'];
			$classdata['list_tplid'] = (int)$post['list_tplid'];
			if ($classdata['list_tplid']==0){
				MyqeeCMS::show_error(Myqee::lang('admin/class.error.needlisttemplate'),true);
			}
			$classdata['list_nosonclass'] = $post['list_nosonclass']==0 || $post['list_nosonclass']==1 || $post['list_nosonclass']==2?(int)$post['list_nosonclass']:0;
			$classdata['list_pernum'] = (int)$post['list_pernum']>0?(int)$post['list_pernum']:20;
			$classdata['list_allpage'] = (int)$post['list_allpage'];
			$classdata['list_byfield'] = preg_replace("/[^a-zA-Z0-9]+/",'',$post['list_byfield']);
			$classdata['list_byfield'] = empty($classdata['list_byfield'])?'id':$classdata['list_byfield'];
			$classdata['list_orderby'] = strtoupper($post['list_orderby'])=='ASC'?'ASC':'DESC';
			$classdata['list_filename'] = preg_replace("/[^a-zA-Z0-9._~,\-\{\}]+/",'',$post['list_filename']);
		}

		//-------------------- for content config
		$classdata['iscontent'] = $post['iscontent']==1?1:0;
		if ( $classdata['iscontent'] == 1 ){
			$classdata['content_tohtml'] = $post['content_tohtml']==1||$classdata['isnothtml']==1?1:0;
			$classdata['content_cachetime'] = (int)$post['content_cachetime'];
			$classdata['content_tplid'] = (int)$post['content_tplid'];
			if ($classdata['content_tplid']==0){
				MyqeeCMS::show_error(Myqee::lang('admin/class.error.needcontenttemplate'),TRUE);
			}

			$classdata['content_pathtype'] = $post['content_pathtype']==1?1:0;
			if ($classdata['content_pathtype'] == 1){
				$classdata['content_path'] = preg_replace("#[^a-zA-Z0-9_\-\\\/]+#",'',$post['content_path']);
				if (empty($classdata['content_path'])){
					MyqeeCMS::show_error(Myqee::lang('admin/class.error.needcontentpath'),TRUE);
				}
			}
			$classdata['content_selfpath'] = preg_replace("#[^a-zA-Z0-9_~\-\\\/]+#",'',$post['content_selfpath']);
			$classdata['content_filenametype'] = (int)$post['content_filenametype'];
			$classdata['content_prefix'] = preg_replace("/[^a-zA-Z0-9_]+/",'',$post['content_prefix']);
			$classdata['content_suffix'] = preg_match("/^\.[a-zA-Z0-9]+$/",$post['content_suffix'])?$post['content_suffix']:'.html';
		}

		//-------------------- for search config
		$classdata['issearch'] = $post['issearch']==1?1:0;
		if ( $classdata['issearch'] == 1 ){
			$classdata['search_tplid'] = (int)$post['search_tplid'];
			if ($classdata['search_tplid']==0){
				MyqeeCMS::show_error(Myqee::lang('admin/class.error.needsearchtemplate'),TRUE);
			}
			$classdata['search_byfield'] = preg_replace("/[^a-zA-Z0-9]+/",'',$post['search_byfield']);
			$classdata['search_byfield'] = empty($classdata['search_byfield'])?'id':$classdata['search_byfield'];
			$classdata['search_orderby'] = strtoupper($post['search_orderby'])=='ASC'?'ASC':'DESC';
		}


		//--------------------- other config
		$classdata['isnavshow'] = $post['isnavshow']==1?1:0;
		$classdata['myorder'] = (int)$post['myorder'];
		$classdata['hostname'] = preg_replace("/[^a-zA-Z0-9_\-\/\.]+/",'',$post['hostname']);
		$classdata['classimg'] = htmlspecialchars($post['classimg']);
		$classdata['htmlintro'] = $post['htmlintro'];

		$classdata['keyword'] = htmlspecialchars($post['keyword']);
		$classdata['description'] = htmlspecialchars($post['description']);

		$classdata['manage_limit'] = $post['manage_limit']>0 && $post['manage_limit']<=200?$post['manage_limit']:20;
		$classdata['manage_orderbyfield'] = empty($post['manage_orderbyfield'])?'id':$post['manage_orderbyfield'];
		$classdata['manage_orderby'] = strtoupper($post['manage_orderby'])=='ASC'?'ASC':'DESC';


		if ($post['classid']>0){
			//更改站点、只允许第一级的站点被修改站点，子栏目将跟随改变
			if (isset($post['siteid']) && $post['bclassid']==0 && $old_class['siteid']!=$post['siteid'] ){
				$post['siteid'] = $post['siteid']>0?$post['siteid']:0;
				//获取管理员是否有对应站点权限
				if (Passport::getisallow('class.dbchangesite')){
					if ( Passport::getisallow_site($post['siteid']) ){
						$classdata['siteid'] = $post['siteid'];
					}
				}
				
				if (isset($classdata['siteid'])){
					//更改子栏目
					$allsonclass = $this -> get_sonclass_id($post['classid'],TRUE);
					if (count($allsonclass)){
						$c = $this -> db -> in('classid',$allsonclass) -> update('[class]', array('siteid'=>$classdata['siteid']) ) -> count();
						//更新缓存
						if ($c)$this -> renew_classcatch($allsonclass);
					}
				}
			}
			
			$status = $this -> db -> update('[class]', $classdata ,array('classid' => $post['classid']));
			$editclassid = $post['classid'];
		}else{
			if ($this -> site_id>0){
				$classdata['siteid'] = $this -> site_id;
			}elseif($classdata['bclassid']>0){
				//集成上级栏目所属站点
				$bclass = $this -> get_class_array($classdata['bclassid'],'siteid');
				$classdata['siteid'] = (int)$bclass['siteid'];
			}else{
				$classdata['siteid'] = 0;
			}
			$classdata['hits'] = 0;
			$status = $this -> db -> insert('[class]', $classdata);
			$editclassid = $status -> insert_id();
		}
		$classdata['classid'] = $editclassid;
		
		$status = count($status);
	
		//更新子栏目域名
		if ($classdata['hostname'] && $post['hostset_tosmallclass']){
			$uphostname = $classdata['hostname'];
		}else{
			$uphostname = false;
		}
		
		if ($status==1||$uphostname){
			if ($status==1){
				//for association
				if ( $ischangebclassid ){
					if ($editclassid>0)$this -> update_class_association($editclassid,$oldbclassid,(int)$post['bclassid']);
				}
				//for path
				if ($classdata['isnothtml']==0 && $classdata['classpath']){
					if ($post['classid']>0 && $oldclaspath && $oldclaspath != $classdata['classpath'] ){
						//rename the path
						MyqeeCMS::move_classpath($oldclaspath,$classdata['classpath']);
						//for fatherclass path
						$upsonclass = true;
					}
					if (!is_dir(WWWROOT.$classdata['classpath'])){
						Tools::create_dir(WWWROOT.$classdata['classpath']);
					}
				}
				//保存栏目配置文件
				$classdata = $this -> db -> getwhere ('[class]',array('classid' => $editclassid)) -> result_array(FALSE);
				$classdata = $classdata[0];
				MyqeeCMS::saveconfig('class/class_'.$editclassid,$classdata);
			}
			
			if ($uphostname||$upsonclass){
				$fatherclass = $this -> get_allclass_array($post['classid'] ,0 , false);
				if (is_array($fatherclass)){
					foreach ($fatherclass as $tmpclass){
						$this -> _update_sonclass_path($tmpclass,$classdata['classpath'],$uphostname,'');
					}
				}
			}
			
			//更新导航显示
			if ( $classdata['isnavshow'] != $old_class['isnavshow'] || ($classdata['isnavshow']==1 && ($classdata['myorder'] != $old_class['myorder'] || $post['uptonav']) )){
				$this -> update_nav_array();
			}
		}
		if ($status==0){
			return false;
		}
		return $editclassid;
	}

	/**
	 * 更新子栏目目录
	 *
	 * @param array $classinfo 栏目信息
	 * @param string $parent_newpath 新的子栏目目录
	 */
	protected function _update_sonclass_path($classinfo,$parent_newpath,$uphostname=false,$mynewpath=''){
		if ($classinfo['isnothtml']==0){
			$patharray = explode('/',$classinfo['classpath']);
			$selfpath = $patharray[(count($patharray)-1)];
			$classinfo['classpath'] = $newpath = $parent_newpath . '/' . $selfpath;
			$mynewpath .= '/'.$selfpath;
			$set = array('classpath'=>$newpath);
			if ($uphostname){
				$classinfo['hostname'] = $set['hostname'] = rtrim($uphostname,'/') . '/' . trim($mynewpath,'/');
			}
			$this -> db -> update('[class]', $set ,array('classid' => $classinfo['classid']) );
			
			MyqeeCMS::saveconfig('class/class_'.$classinfo['classid'],$classinfo);
			
			if (is_array($classinfo['sonclassarray'])){
				foreach ($classinfo['sonclassarray'] as $tmpclass){
					$this -> _update_sonclass_path($tmpclass,$newpath,$uphostname,$mynewpath);
				}
			}
		}
	}

	/**
	 * 更新栏目关系
	 *
	 * @param int $classid
	 * @param int $oldbclassid
	 * @param int $newbclassid
	 * @return null
	 */
	public function update_class_association($classid,$oldbclassid,$newbclassid){
		if ($oldbclassid == $newbclassid){
			return false;
		}

		//delete old bclass sonclass ------------------------------
		//		$query = $this -> db ->select ( "sonclass" )->from ( '[class]' ) -> where('classid',$oldbclassid) -> limit(1) ->get ()->result_array ( FALSE );

		if ($oldbclassid>0){
			$bclass_old = $this -> get_class_array($oldbclassid , 'sonclass');
			if ( $bclass_old ){
				$bclass_sonclass = str_replace('|'.$classid.'|','|',$bclass_old['sonclass']);
				if ( $bclass_sonclass == '|' ){
					$bclass_sonclass = '';
				}
				$this -> db -> update('[class]', array('sonclass' => $bclass_sonclass) ,array('classid' => $oldbclassid));
				
				$classdata = $this -> db -> getwhere ('[class]',array('classid' => $oldbclassid)) -> result_array(FALSE);
				$classdata = $classdata[0];
				MyqeeCMS::saveconfig('class/class_'.$oldbclassid,$classdata);
			}
		}

		//add new bclass sonclass -----------------------------------
		//		$query = $this -> db ->select ( "sonclass,fatherclass" )->from ( '[class]' ) -> where('classid',$newbclassid) -> limit(1) ->get ()->result_array ( FALSE );
		$bclass_new = $this -> bclass_new ? $this -> bclass_new : $this -> get_class_array($newbclassid , 'sonclass,fatherclass');
		if ( $bclass_new ){
			$bclass_sonclass = $bclass_new['sonclass'] . $classid .'|';
			if ( substr($bclass_sonclass,0,1) != '|' ){
				$bclass_sonclass = '|' . $bclass_sonclass;
			}
			$this -> db ->update('[class]', array('sonclass' => $bclass_sonclass) ,array('classid' => $newbclassid));
			
			$classdata = $this -> db -> getwhere ('[class]',array('classid' => $newbclassid)) -> result_array(FALSE);
			$classdata = $classdata[0];
			MyqeeCMS::saveconfig('class/class_'.$newbclassid,$classdata);
		}

		//update fatherclass ----------------------------------------
		if ($newbclassid == 0){
			$bclass_fatherclass = '';
		}else{
			$bclass_fatherclass = (empty($bclass_new['fatherclass'])?'|':$bclass_new['fatherclass']).$newbclassid.'|';
		}
		$this -> db -> update('[class]', array('fatherclass'=>$bclass_fatherclass) ,array('classid' => $classid));

		$query = $this -> db -> from ( '[class]' ) -> like('fatherclass','|'.$classid.'|') -> get() -> result_array ( FALSE );
		if (count($query)>0){
			$bclass_fatherclass = (empty($bclass_fatherclass)?'|':$bclass_fatherclass) . $classid ;
			foreach ($query as $v){
				$fatherclass_array = explode('|'.$classid.'|',$v['fatherclass']);
				$v['fatherclass'] = $bclass_fatherclass . '|' . $fatherclass_array[1];
				if ($v['fatherclass']=='|')$v['fatherclass']='';
				$this -> db -> update('[class]', array('fatherclass' => $v['fatherclass']) ,array('classid' => $v['classid']));
				MyqeeCMS::saveconfig('class/class_'.$v['classid'],$v);
			}
		}


	}
	
	/**
	 * 更新导航菜单
	 *
	 */
	public function update_nav_array($navarray = null){
		$navclass = $this -> get_allclass_array(0,0,false,array('isnavshow'=>1));
		//$navclass = $this -> db -> select('classid,classname,myorder')->where(array('isnavshow'=>1))->orderby('myorder','ASC')->orderby('classid','ASC') -> get('[class]') -> result_array ( FALSE );
		
		if (!$navarray) $navarray = Myqee::config('navigation'.($this -> site_id?'/site_'.$this -> site_id:''));
		$mynav = $this -> _set_nav_array($navarray,$navclass);
		
		return MyqeeCMS::saveconfig('navigation'.($this -> site_id?'/site_'.$this -> site_id:''),$mynav);
	}
	
	protected function _set_nav_array($thelist,$classlist,$myurl=null){
		$new_list = array();
		if (is_array($thelist)){
			foreach ($thelist as $key => $item){
				if ($item['classid']>0){
					//当前项为系统栏目
					if ($classlist[$item['classid']]){
						if ( empty($item['name']) || $_POST['class']['uptonav']['name'] ) $item['name'] = $classlist[$item['classid']]['classname'];
						if ( empty($item['url']) || $_POST['class']['uptonav']['url'] )$item['url'] = $this -> get_class_url( $classlist[$item['classid']] );
						$new_list[$key] = $item;
						$new_list[$key]['myorder'] = $classlist[$item['classid']]['myorder'];
						if ( $item['submenu'] || $classlist[$item['classid']]['sonclassarray']){
							$new_list[$key]['submenu'] = $this -> _set_nav_array( $item['submenu'] , $classlist[$item['classid']]['sonclassarray'] ,$myurl);
						}
						unset($classlist[$item['classid']]);
					}
				}else{
					$new_list[$key] = $item;
				}
			}
		}
		//还存在栏目内容说明当前导航菜单中不存在对应的项，须增加上去
		if (is_array($classlist) && count($classlist)>0){
			
			foreach ($classlist as $item){
				$new_list['_class_'.$item['classid']] = array(
					'myorder' => $item['myorder'],
					'classid' => $item['classid'],
					'name' => $item['classname'],
					'url' => $this -> get_class_url($item),
					'target' => NULL,
					'submenu' => NULL,
				);
				if ($item['sonclassarray']){
					$new_list['_class_'.$item['classid']]['submenu'] = $this -> _set_nav_array( null , $item['sonclassarray'] ,$myurl);
				}
			}
		}
		
		asort($new_list);	//重新排序
		return $new_list;
	}

	public function get_class_url($class_info,$page=0){
		if ( !is_array($class_info)){
			$class_info = $this -> get_class_array($class_info);
		}
		$this -> mysiteurl or $this -> mysiteurl = Myqee::config('core.mysite_url');
		if ($class_info['hostname']){
			$theurl = 'http://'.$class_info['hostname'];
		}else{
			$theurl = $this -> mysiteurl .$class_info['classpath'] .'/';
		}
		
		if($class_info['isnothtml']!=0 || ($page==0 && $class_info['cover_tohtml']) || ($page>0 && $class_info['list_tohtml']) ){
			$this -> url_suffix or $this -> url_suffix = Myqee::config('core.url_suffix');
			$theurl = $this -> mysiteurl.'myclass/'.substr(Des::Encrypt('['.$class_info['classid'].']',Myqee::config('encryption.urlcode.key')),2).($page>0?'/'.$page:'').$this -> url_suffix;
		}else{
			if ($page>0){
				//列表
				$theurl .= str_replace('{{page}}',$page,$class_info['list_filename']);
			}else{
				//封面
				if (!$class_info['cover_hiddenfilename']){
					$theurl .= $class_info['cover_filename'];
				}
			}
		}
		if (substr($theurl,0,1)=='/'){
			$theurl = 'http://'.Myqee::config('core.mysite_domain').$theurl;
		}
		return $theurl;
	}

	/**
	 * 获取下拉菜单需要的分类数组
	 *
	 * @return Array datable for select
	 */
	public function get_dbtable_forselect($includememberdb = true , $additem = null){
		$query = $this -> get_dbtable_array($includememberdb);
		if (is_array($additem)){
			$dbtable = $additem;
		}else{
			$dbtable = array();
		}
		foreach ($query as $item){
			$dbtable[$item['name']] = $item['dbname'];
			if ($item['isdefault']==1){
				$dbdefault = $item['name'];
			}
		}

		$query = array(
			'forselect' => $dbtable,
			'default' => $dbdefault,
		);
		return $query;
	}

	/**
	 * 获取用户信息表数据
	 *
	 * @return array datable
	 */
	public function get_dbtable_array($includememberdb = true){
		$where = array('isuse'=>1);
//		if ($this -> site_id>0){
//			$where['siteid']=$this -> site_id;
//		}
		$includememberdb or $where['ismemberdb'] = 0;
		$this -> db -> from ( '[dbtable]' ) -> where($where);
		if ($this -> allow_db!='-ALL-'){
			$this -> db -> in ('name',$this -> allow_db);
		}
		$result = $this -> db -> orderby('isdefault','DESC') -> get ()->result_array ( FALSE );
		return $result;
	}
	
	/**
	 * @name 获取管理员数据表、栏目的权限
	 * @param $type string 操作方式，db,class
	 * @param $adminid int 指定管理员ID，不指定的话，获取当前管理员信息
	 */
	public function get_admin_in($type='db',$adminid=nul){
		if (!($type=='db')){
			$type = 'class';
		}
		if ($adminid>0){
			$admininfo = $this -> db -> getwhere('[admin]',array('id',$adminid)) -> result_array(FALSE);
			$theset = $admininfo[0][$type.'set'];
		}else{
			$theset = Session::instance()->get('admin.'.$type.'set');
		}
		if ($theset=='-ALL-')return 0;
		if ($theset){
			$in = explode(',',$theset);
		}else{
			$in = NULL;
		}
		return $in;
	}

	/**
	 * 获取用户信息表内容信息
	 *
	 * @param string $dbname 表名称
	 * @param number $per 每页显示数
	 * @param number $offset 查询起始
	 * @param array $orderby 排序
	 * @param string $select 列出的字段
	 * @param string $classid_field 栏目字段名
	 * @param array $otherBuilder 其它查询参数，目前允许'like','orlike','nolike','in','notin','regex','orregex','notregex','ornotregex','groupby','having','orhaving','join','orwhere'
	 * 							     传递形式类似array( array('like','title','abc'),array('orlike','title','def') );
	 * @return array tableinfo 信息数组
	 */
	public function get_userdb_list($dbname , $per = 20 , $offset = 0 , $where = null , $orderby = null ,$select='*',$classid_field = null,$otherBuilder = null){
		if (strpos($dbname,'/')===false){
			$database = 'default';
			$tablename = $dbname;
			$dbname = $database .'/'.$tablename;
		}else{
			list($database,$tablename) = explode('/',$dbname);
		}
		$_db = Database::instance($database);
		if ($classid_field && $this -> site_id>0){
			$allclass = $this -> get_site_allclass_id($dbname);
			if (!$allclass)return 0;
			$_db -> in( $classid_field , $allclass );
		}
		//		if ( !$this -> db ->table_exists($dbname))return false;
		$_db -> select($select) -> from ( $tablename ) -> limit($per,$offset);
		if (is_array($where)){
			$_db -> where($where);
		}
		if (is_array($orderby)){
			foreach ($orderby as $key => $order){
				$_db -> orderby($key, strtoupper($order)=='ASC'?'ASC':'DESC' );
			}
		}
		if (is_array($otherBuilder)){
			foreach ($otherBuilder as $v){
				if (in_array($v[0],array('like','orlike','nolike','in','notin','regex','orregex','notregex','ornotregex','groupby','having','orhaving','join','orwhere')))
					$_db -> $v[0]($v[1],$v[2],$v[3]);
			}
		}
		return $_db -> get() -> result_array ( FALSE );
	}
	
	public function get_userdb_count($dbname , $where = null ,$classid_field = null, $otherBuilder = NULL){
		if (strpos($dbname,'/')===false){
			$database = 'default';
			$tablename = $dbname;
			$dbname = $database .'/'.$tablename;
		}else{
			list($database,$tablename) = explode('/',$dbname);
		}
		$_db = Database::instance($database);
		if ($classid_field && $this -> site_id>0){
			$allclass = $this -> get_site_allclass_id($dbname);
			if (!$allclass)return 0;
			
			//找出其所属的数据库
			
			$_db -> in( $classid_field , $allclass );
		}
		if (is_array($where)){
			$_db -> where($where);
		}
		if (is_array($otherBuilder)){
			foreach ($otherBuilder as $v){
				if (in_array($v[0],array('like','orlike','nolike','in','notin','regex','orregex','notregex','ornotregex','groupby','having','orhaving','join','orwhere')))
					$_db -> $v[0]($v[1],$v[2],$v[3]);
			}
		}
		return $_db -> count_records($tablename);
	}

	/**
	 * 获取信息内容HTML输出内容
	 *
	 * @param array $dbconfig
	 * @param number $classid
	 * @param boolean $isshowclass
	 * @param number $per
	 * @param number $offset
	 * @param string $where
	 * @param array $orderby
	 * @param array $otherBuilder
	 * @return string 返回用于信息列表页视图输出的HTML
	 */
	public function get_db_info_html($dbconfig,$classinfo=null,$isshowclass = true , $per = 20 , $offset = 0 , $where = null , $orderby = null ,$otherBuilder = null){
		$sys_field = $dbconfig['sys_field'];
	
		//读取数据
		if (!$classinfo){
			$modelconfig = $dbconfig['model'];
			$modelconfig['usedbmodel'] = 1;
		}else{
			$modelid = (int)$classinfo['modelid'];
			if ($modelid>0){
				//获取模型
				$modelconfig = Myqee::config('model/model_'.$modelid);
				$modelconfig['usedbmodel'] = 1;
				if (is_array($modelconfig)){
					if (count($modelconfig['nolist'])>0){
						foreach ($modelconfig['nolist'] as $key => $value){
							unset($dbconfig['list'][$key]);
						}
					}
					if (count($modelconfig['list'])>0){
						$dbconfig['list']= array_merge($dbconfig['list'],$modelconfig['list']);
					}
				}
				
			}elseif($dbconfig['usedbmodel']){
				$modelconfig = $dbconfig['model'];
			}
		}
		$list_field = array();
		
		if ($sys_field['id']){
			$list_field[]=$sys_field['id'];
			$idname = $sys_field['id'];
		}else{
			$idname = 'id';
		}
		if ($sys_field['class_id']){
			$list_field[]=$sys_field['class_id'];
			$classidname = $sys_field['class_id'];
		}
		if ($sys_field['class_name']){
			$list_field[]=$sys_field['class_name'];
			$classnamename = $sys_field['filename'];
		}
		if ($sys_field['title']){
			$list_field[]=$sys_field['title'];
		}
		if ($sys_field['filename']){
			$list_field[]=$sys_field['filename'];
		}
		if ($sys_field['filepath']){
			$list_field[]=$sys_field['filepath'];
		}
		
		if(is_array($dbconfig['list'])){
			foreach ($dbconfig['list'] as $k=>$v){
				$list_field[] = $k;
			}
		}
	
		if (count($list_field)){
			$list_field = array_unique($list_field);
			$list_field = implode(',',$list_field);
		}else{
			$list_field = '*';
		}
		list($database,$tablename) = explode('/',$dbconfig['dbname'],2);
		$myinfo = $this -> get_userdb_list($dbconfig['dbname'] , $per, $offset , $where,$orderby ,$list_field ,$sys_field['class_id'] ,$otherBuilder);
		
		$this->_addRelationInfo($myinfo,$dbconfig);
		//echo $this -> db -> last_query();
		/*
		//追溯classid 和 modelid 
		if ($sys_field['class_id']){
			//获取第一个数据的栏目ID
			$class_id = (int)$myinfo['list'][0][$sys_field['class_id']];
			if ($class_id>0){
				$classinfo = $this -> get_class_array($class_id);
				$modelid = (int)$classinfo['modelid'];
				if ($modelid>0){
					//获取模型
					$modelconfig = Myqee::config('model/model_'.$modelid);
					$modelconfig['usedbmodel'] = 1;
				}elseif($dbconfig['usedbmodel']){
					$modelconfig = $dbconfig['model'];
				}
			}
			$classidname = $sys_field['class_id'];
		
			if ($sys_field['class_name']){
				$classnamename = $sys_field['class_name'];
			}else{
				$classnamename = 'class_name';
			}
		}elseif($dbconfig['usedbmodel']){
			$modelconfig = $dbconfig['model'];
		}
*/
		
		
		$outtd = '';
		$i=0;
		$col = count($dbconfig['list']);
		$forward = urlencode($_SERVER['REQUEST_URI']);
		$docode = $boolean = array();
		
		$tmphtml = '<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder"><tbody><tr style="white-space:nowrap"><th class="td1" width="20">&nbsp;</th>';
		
		foreach ($dbconfig['list'] as $key => $table){
			$tmphtml .= '<th'.
			($table['class']?' class="td1 '.$table['class'].'"':' class="td1"').
			' width="'.($table['width']?$table['width']:'100%').'"'.
			($table['height']?' height="'.$table['height'].'"':'').
			($table['style']?' style="'.$table['style'].'"':'').
			'>'.$table['title'].'<br/><img src="'.ADMIN_IMGPATH.'/admin/spacer.gif" width="'.($table['width']?$table['width']:50).'" style="height:0px" /></th>';

			if ($i == 0){
				$outtd .= '<tr{{_tRcolor}} onmouseover="tr_moveover(this)" onmouseout="tr_moveout(this)" ondblclick="change_select(\'select_id_{{'.$sys_field['id'].'}}\');return false;"><td align="center" class="td1"><input onclick="select_tr(this)" type="checkbox" id="select_id_{{'.$sys_field['id'].'}}" /></td>';
			}
			if ($table['docode']){
				$docode_class or $docode_class = new Field_list_Api;
				$docode[$key] = array($table['docode'],$table['boolean']);
			}else{
				if ( is_array($table['boolean']) ){
					$boolean[$key] = $table['boolean'];
				}
			}
			$outtd .= '<td'.
			($table['class']?' class="td'.( $i==0?'1':'2' ).' '.$table['class'].'"':' class="td'.( $i==0?'1':'2' ).'"').
			($table['height']?' height="'.$table['height'].'"':'').
			($table['align']?' align="'.$table['align'].'"':'').
			($table['style']?' style="'.$table['style'].'"':'').
			'><div class="nbsp"></div>'.
			($table['titlelink']?($isshowclass && $classidname?'<a href="'.Myqee::url('info/myclass/1/{{'.$classidname.'}}').'" class="classlink">[{{'.$classnamename.'}}]</a> ':'').
			'{{'.$key.'}}'.($modelconfig['usedbmodel']?' <a href="{{URL}}" target="_blank"><img src="'.ADMIN_IMGPATH.'/admin/external.png" alt="新窗口查看" /></a>':''):($table['link']?'<a href="'.$table['link'].'"'.($table['target']?' target="'.$table['target'].'"':'').'>{{'.$key.'}}</a>':'{{'.$key.'}}')).
			'</td>';
			if($i==$col-1){
				$outtd .= '<td class="td2" align="center" width="50"><pre>';
				if (!is_array($modelconfig['adminlist']))$modelconfig['adminlist'] = array('sys_edit'=>array('name'=>'编辑'),'sys_del'=>array('name'=>'删除'));
				foreach ($modelconfig['adminlist'] as $kk=>$vv){
					if ($kk=='sys_del'){
						if (!$vv['isuse']&&!is_array($vv))continue;
						$outtd .= '<input onclick="ask_del(\'{{'.$idname.'}}\',\'info/del/'.$dbconfig['dbname'].'\',\'确认删除？\');" type="button" value="'.($vv['name']?$vv['name']:'删除').'" class="'.($vv['class']?$vv['class']:'btnss').'" />';
					}else{
						if ($kk=='sys_view'){
							if (!$vv['isuse'])continue;
							$vv['class'] or $vv['class']= 'btnss';
							$vv['address'] = Myqee::url(
								'info/'.($sys_field['class_id']?'viewbyclassid/{{'.$classidname.'}}':'view/'.$dbconfig['dbname']).'/{{'.$idname.'}}/'.($this -> fullpage?'fullpage':'').(!empty($this -> fullpage) && strpos('?',$this -> fullpage)!==false?'&':'?').'forward='.$forward
							);
						}elseif($kk=='sys_edit'){
							if (!$vv['isuse']&&!is_array($vv))continue;
							$vv['class'] or $vv['class']= 'btnss';
							$vv['address'] = Myqee::url(
								'info/'.($sys_field['class_id']?'editbyclassid/{{'.$classidname.'}}':'edit/'.$dbconfig['dbname']).'/{{'.$idname.'}}/'.($this -> fullpage?'fullpage':'').(!empty($this -> fullpage) && strpos('?',$this -> fullpage)!==false?'&':'?').'forward='.$forward
							);
						}elseif($kk=='sys_commend'){
							if (!$vv['isuse'])continue;
							$vv['class'] or $vv['class']= 'btnss';
							$vv['address'] = Myqee::url(
								'comment/index/1/'.$dbconfig['dbname'].'/{{'.$idname.'}}/'.($this -> fullpage?'fullpage':'').(!empty($this -> fullpage) && strpos('?',$this -> fullpage)!==false?'&':'?').'forward='.$forward
							);
						}else{
							if (!$vv['address'])continue;
							if(!preg_match("/^(http(s)?|ftp|file):\/\//i",$vv['address'])){
								$vv['address'] = Myqee::url($vv['address']);
							}
						}
						$outtd .= '<a href="'.$vv['address'].'"'.($vv['target']?' target="'.$vv['target'].'"':'').' class="'.($vv['class']?$vv['class']:'btns').'">'.$vv['name'].'</a>';
					}
				}
			}
			$i++;
		}
		$modelconfig['adminlist']['width'] or $modelconfig['adminlist']['width'] = 50;
		$tmphtml .= '<th class="td1">操作<br/><img src="'.ADMIN_IMGPATH.'/admin/spacer.gif" width="'.$modelconfig['adminlist']['width'].'" style="height:0px" /></th></tr>';

		if($myinfo){
			$i=0;
			$readurl = false;
			foreach ($myinfo as $item){
				if ($i%2==0)$item['_tRcolor'] =' class="td3"';
				if (count($boolean)){
					foreach ($boolean as $key => $v){
						if ( ($tmp = $v[$item[$key]]) )$item[$key] = $tmp;
					}
				}
				if (count($docode)){
					foreach ($docode as $key => $v){
						$dofun = $v[0];
						$item[$key] = $docode_class -> $dofun($item[$key],$v[1]);
					}
				}
				if ($readurl || strrpos($outtd,'{{URL}}')!==false){
					$item['URL'] = $this -> getinfourl($classidname?$item[$classidname]:$dbconfig['dbname'],$item);
					$readurl = TRUE;
				}

				$outhtml .= preg_replace("/\{\{([\w|_]+)\}\}/e","\$item[\\1]" , $outtd );
				$i++;
			}
		}
		$tmphtml .= $outhtml . '</tbody></table>';
		return $tmphtml;
	}

	public function get_location_array($classid,$myclass=null){
		if (!is_array($myclass))$myclass = $this -> get_class_array($classid,'classid,classname,fatherclass');
		if (!is_array($myclass))return false;
		$myfeather = array();
		if ($myclass['fatherclass']){
			$feather = explode('|',trim($myclass['fatherclass'],'|'));
			foreach ($feather as $cid){
				if ( $tempclass = $this -> get_class_array($cid) ){
					$myfeather[] = $tempclass;
				}
			}
		}
		$myfeather[] = $myclass;
		return $myfeather;
	}


	public function get_userdb_info($dbname , $infoid = 0 , $selectit = '*') {
		if ((!is_array($infoid) && !($infoid>0)) || !$dbname)return false;
		$db_id = Myqee::config('db/'.$dbname.'.sys_field.id');

		if (!$db_id)return false;
		list($database,$tablename) = explode('/',$dbname);
		$_db = Database::instance($database);
		$_db -> select ( $selectit ) -> from ( $tablename );
		if (is_array($infoid)){
			$result = $_db -> in( $db_id , $infoid) -> get() -> result_array ( FALSE );
			return $result;
		}else{
			$result = $_db -> where( $db_id , $infoid) -> get() -> result_array ( FALSE );;
			return $result[0];
		}
	}

	public function get_user_editinfo_form($dbname, $info ,$modelid = 0 ,$isadd = true ,$isviewtype = false){
		if (!$dbname)return false;
		$modelid = (int)$modelid;
		$this -> dbset = (array)Myqee::config('db/'.$dbname);
		if ($modelid>0){
			$model_set = Myqee::config('model/model_'.$modelid);
			//处理钩子
			$this->_hook($this -> dbset,$model_set,$info);
			$model_field = $model_set['field'];
			$model_dbset = $model_set['dbset'];
			if (is_array($model_dbset)){
				foreach ($model_dbset as $key => $value){
					if ($value['type'])$this -> dbset['edit'][$key]['type'] = $value['type'];
					if ($value['set'])$this -> dbset['edit'][$key]['set'] = $value['set'];
					if ($value['default'])$this -> dbset['edit'][$key]['default'] = $value['default'];
					if ($value['candidate'])$this -> dbset['edit'][$key]['candidate'] = $value['candidate'];
					if ($value['format'])$this -> dbset['edit'][$key]['format'] = $value['format'];
					if ($value['notempty'])$this -> dbset['edit'][$key]['notempty'] = $value['notempty'];
				}
			}
			//MyqeeCMS::print_r($this -> dbset);
		}else{
			$model_field = $this -> dbset['model']['field'];
		}

		//处理扩展表
		$model_field = $this->_dealRelationTable($model_field);


		if (is_array($model_field)){
			$tmpedit = array();
			$thistag = '常规选项';
			foreach ($model_field as $key => $value){
				if ((!$isviewtype && $value['input']) || ($isviewtype && $value['view']) ){
					$value['tag'] and $thistag = $value['tag'];
					$tmpedit[$thistag][$key] = $this -> dbset['edit'][$key];
					$value['notnull'] and $tmpedit[$thistag][$key]['notempty'] = true;
				}
			}
			$this -> dbset['editlist'] = $tmpedit;
		}

		if (!$tmpedit){
			$this -> dbset['editlist'] = array('全部选项'=>$this -> dbset['edit']);
		}

		$db_edit_set = $this -> dbset['editlist'];
		if (!$db_edit_set)return '<table border="0" cellpadding="4" cellspacing="1" class="tableborder"><tr><td align="center" colspan="2" height="100"><h4>数据库配置文件加载错误！请修复数据表设置！</h4></td></tr></table>';
		
		
		//找出扩展表的信息
		$_relationinfo = array();
		if (is_array($this->dbset['model']['relationfield'])){
			foreach ($this->dbset['model']['relationfield'] as $val) {
				$_relationinfo[$val['field']] = $val;
			}
		}

		$temphtml = '';
		$i = 0;
		$ththml = '<div class="mainTable"><ul class="ul tag">';
		foreach ($db_edit_set as $tdname => $mysets){
			$i++;
			$ththml .= '<li'.($i==1?' class="now"':'').' id="mytag_'.$i.'" onclick="tag(this.id,\'mytag\',\'mytagmain\');changeHeight()">'.$tdname.'</li>';
			$temphtml .= '<div id="mytagmain_'.$i.'"'.($i==1?'':' style="display:none;"').'><table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder"><tr><th class="td1" colspan="2">'.$tdname.'</th></tr>';
			$j = 0;
			if (is_array($mysets)){
				//$autowidth = 100;
				$isfristtr = TRUE;
				foreach ($mysets as $fieldname => $myset){
					$myset['type']=='hidden' or $j++;
					$model_field[$fieldname]['dbname'] and $myset['title'] = $model_field[$fieldname]['dbname'];
					$model_field[$fieldname]['comment'] and $myset['description'] = $model_field[$fieldname]['comment'];
					$trcolor = $j % 2 == 0?' class="td3"':'';
	
					if (!isset($info[$fieldname]) && $myset['ishtml']==0){
						$info[$fieldname] = $myset['default'];
					}else{
						//format string
						$info[$fieldname] = $this -> _reset_format_value($myset['format'],$info[$fieldname],$myset['type']);
					}
					//不可修改字段，添加disabled
					if ($isadd==false && $model_field[$fieldname]['editor']!=1){
						$myset['set']['disabled'] = 'disabled';
					}
					if ($isviewtype){
						$dohtmlfun = 'viewhtml';
					}else{
						$dohtmlfun = 'edithtml';
					}
					/*
					if (!isset($myset['editwidth'])||$myset['editwidth']===NULL){
						$myset['editwidth']=$autowidth;
					}elseif ($myset['editwidth']>0){
						$autowidth = $myset['editwidth'];
					}*/
					//处理扩展表的数据，对于像checkboxes,select等需要预读数据的字段进行初始化，也就是查询 candidate
					$rinfo = $_relationinfo[$fieldname];
					if (is_array($rinfo)) {
						list($_database,$_tablename) = explode('/',$rinfo['dbtable'],2);
						$_db = Database::instance($_database);
						//如果此字段有扩展信息
						//修订type
						if ($rinfo['relation'] == 'n:1') {
							//单值插入，例如文章要选作者。
							if (!in_array($myset['type'],array('select','radio','pageselect'))) {
								$myset['type'] = 'select';
							}
						} elseif ($rinfo['relation'] == 'n:n') {
							//多值插入，例如文章添加tag。
							if (!in_array($myset['type'],array('checkbox','pageselect'))) {
								$myset['type'] = 'checkbox';
							}
						}
//						$myset['relationship'] = $rinfo['relation'];
//						$myset['dbname'] = $database.'/'.$tablename ;
						$myset['fdatabase'] = $_database;
						$myset['ftablename'] = $_tablename;
						$myset['ffieldsave'] = $rinfo['dbfield'];
						$myset['ffieldshow'] = $rinfo['dbfieldshow'];
						$myset['fieldname'] = $fieldname;
						$myset['savevalue'] = $info[$fieldname];
						if ($rinfo['relation'] == 'n:n') {
							$myset['isappend'] = 1;
						}
						//查询值
						if ($myset['type'] == 'pageselect') {
							//弹出式选择，只需要查询一个值
							if ($rinfo['relation'] == 'n:n' && strpos($info[$fieldname],'|') !== FALSE) {
								$_tmp = explode('|',$info[$fieldname]);
								$query = $_db->in ($rinfo['dbfield'],$_tmp)->select ("{$rinfo['dbfield']},{$rinfo['dbfieldshow']}")->get($_tablename)->result_array(FALSE);
								foreach ($query as $val) {
									$myset['showvalue'] .= $val[$rinfo['dbfieldshow']].'|';
								}
							} else {
								$query = $_db->where ($rinfo['dbfield'],$info[$fieldname])->select ("{$rinfo['dbfield']},{$rinfo['dbfieldshow']}")->get($_tablename)->result_array(FALSE);
								$myset['showvalue'] = $query[0][$rinfo['dbfieldshow']];
							}
							
						}else {
							//查询所有值
							$_tmp = $_db->select ("{$rinfo['dbfield']},{$rinfo['dbfieldshow']}")->get($_tablename)->result_array(FALSE);
							$_candidate = array();
							foreach ($_tmp as $val) {
								$_candidate[$val[$rinfo['dbfield']]] = $val[$rinfo['dbfieldshow']]; 
							}
							$myset['candidate'] = $_candidate;
						}
					}
					$myset['fieldname'] = "info[{$fieldname}]";
					$myset['description'] = trim($myset['description']);
					$_inputstr = form::$dohtmlfun($myset,$info[$fieldname],$fieldname);
					$temphtml .= '<tr'.$trcolor.($myset['type']=='hidden'?' style="display:none;"':'').'>';
					$temphtml .= '<td class="td1" align="right"><span style="white-space:nowrap">' . $myset['title']. ($myset['notempty']?'(<font color="red">*</font>)':'') .'：</span>'.($isfristtr?'<br/><img src="'.ADMIN_IMGPATH.'/admin/spacer.gif" style="height:0px;" width="100" />':'').'</td>'.
					'<td class="td2"'.($isfristtr?' width="96%"':'').'>'. 
					
					 $_inputstr.
					
					(!$isviewtype && $myset['description'] && $myset['title']!=$myset['description']? ' <span class="helpicon" title="'. str_replace(array("\r","\n","\"",'&',),array('<br/>','<br/>','&quot;','&amp;'),$myset['description']) .'">&nbsp;</span>':'').
					'</td></tr>'."\r\n";
					
					$isfristtr = FALSE;
				}
			}
			$temphtml .= '</table></div>';
		}
		return $ththml.'</ul></div><div style="clear:both"></div>'.$temphtml;
	}
	
	/**
	 * 格式化数据
	 * 用于给编辑表单输出函数处理
	 *
	 * @param string $formattype
	 * @param all $value
	 * @return all $value
	 */
	protected function _reset_format_value($formattype,$value,$type){
		if ($type=='checkbox'){
			return $value;
		}
		if ($formattype == 'int' || $formattype == 'time'){
			$value = (int)$value;
		}elseif ($formattype == 'serialize'){
			$value = unserialize($value);
		}elseif ($formattype == 'json_encode'){
			$value = Tools::json_decode($value);
		}else{
			$value = htmlspecialchars_decode($value);
		}
		return $value;
	}
	
	public function get_edit_key($info){
		if (is_array($info)){
			$info = join('__',$info);
		}
		return md5($info . '_' .Myqee::config('encryption.default.key'));
	}

	/*public function get_advfield_array($arr,$fieldname='',$idstr=''){
		if (!is_array($arr))return array();
		if (!isset($arr['_g'])||!is_array($arr['_g'])){
			//缺少设置属性
			return array();
		}
		
		$myarray=array(
			'_set'=>$arr['_g'],
		);
		foreach ($arr as $k => $myset){
			//设置字段
			if ($k=='_g'){
				//或略
				continue;
			}
			
			$newidstr = $idstr.'.'.$k;
			$newfieldname = $fieldname.'][{{.'.$idstr.'}}]['.$k;

			if (isset($myset['_g']) && is_array($myset['_g'])){
				$myarray[$k] = $this -> get_advfield_array($myset,$newfieldname,$newidstr);
				$myarray[$k]['_set'] = $myset['_g'];
			}else{
				$myarray[$k] = array(
					'_set' => array(
						'flag'=>$myset['flag'],
						'name'=>$myset['name'],
						'editwidth'=>$myset['editwidth'],
						'type'=>$myset['type'],
						'default'=>$myset['default'],
						'isfield'=>TRUE,
					),
					'_html'=>$this -> edithtml($myset,NULL,$newfieldname),
				);
			}
		}
		
		
		return $myarray;
	}*/

	/*public function edithtml($myset,$value='',$fieldname=''){
		$myset['set']['name'] = 'info['.$fieldname.']';
		$myset['set']['id'] or $myset['set']['id'] = '_myqee_input_'.$myset['set']['name'];
		$type = $myset['type'];
		$data = $myset['set'];
		$extra = $data['ohter'];
		
		unset($data['other']);
		
		if ($myset['usehtml']=='1' && !empty($myset['html'])){
			//自定义html
			$html = str_ireplace(array('{{value}}','{{name}}','{{id}}'),array($value,$fieldname,$data['id']),$myset['html']);
			return $html;
		}elseif($myset['usehtml']=='2'){
			//多维数组编辑
			$myset['adv']['_g']['flag'] = $fieldname;
			$myset['adv']['_g']['name'] = $myset['title'];
			if (isset($value) && !empty($value) && is_array($value)){
				$value = Tools::json_encode($value);
			}else{
//				$value = '[{"title":"标题1","answer":[{"q":"题目1","score":"3"},{"q":"题目2","score":""}]}]';
				$value = '[]';
			}
			
			$html =
			'<script type="text/javascript">
	_advValue["'.$fieldname.'"] = '.$value.';
	_advArr["'.$fieldname.'"] = '.Tools::json_encode($this -> get_advfield_array($myset['adv'],$fieldname,$fieldname)).';
	document.write(add_adv_field(".'.$fieldname.'"));
	ini_adv_field(".'.$fieldname.'");
</script>';
			return $html;
		}
		if ($type == 'select' || $type =='checkbox' || $type == 'radio'){
			if (in_array($fieldname, (array)$this -> dbset['sys_field'])){
				$trans = array_flip((array)$this -> dbset['sys_field']);
				if ($trans[$fieldname]=='class_id'){
					$classtree = $this -> get_allclass_array('classid,classname,bclassid,classpath,hits,myorder');
					return form::classlist($data,$classtree,'',$value,array('请选择栏目'),true);
				}elseif ($trans[$fieldname]=='template_id'){
					return form::dropdown($data,array_merge(array($value=>'使用栏目设置模板'),(array)$this -> get_alltemplate('content')),$value,$extra);
				}
			}
		}
		//$options = (array)$this -> dbset['edit'][$fieldname]['candidate'];
		if (is_array($myset['candidate'])){
			$options = $myset['candidate'];
		}else{
			$options = array();
		}
		
		$html = '';
		switch ($type){
			case 'textarea':
				$data['class'] or $data['class'] = 'input';
				$html = form::textarea($data,$value,$extra);
				break;
			case 'select':
				$value = (string)$value;
				$html = form::dropdown($data,(array)$options,$value,$extra);
				break;
			case 'selectinput':
				$value = (string)$value;
				$html = form::changeinput($data,$value,$extra,(array)$options);
				break;
			case 'pageselect':
				//分页式下拉框,是弹出式的，需要扩展表支持，因为数据时从扩展表中查询出来的
				$value = (string)$value;
				$extra = $myset['extra_input'];
				$html = form::pageselect($data,$value,$extra,$myset['relationvalue'],$myset['relationship'],$myset['dbname']);
				break;	
			case 'checkbox':
				$tmphtml='';
				if (substr($data['name'],-2,2)!='[]'){
					$data['name'] = $data['name'].'[]';
				}
				
				$value = explode('|',trim($value,'|'));
//				print_r($value);
				foreach ((array)$options as $k1=>$v1){
					if (is_array($value)){
						if (in_array($k1,$value)){
							$chked = TRUE;
						}else{
							$chked = FALSE;
						}
					}else{
						if ($k1==$value){
							$chked = TRUE;
						}else{
							$chked = FALSE;
						}
					}
					$tmphtml .= form::checkbox($data,$k1,$chked,$extra).$v1.' ';
				}
				$html = $tmphtml;
				break;
			case 'radio':
				$tmphtml='';
				foreach ((array)$options as $k1=>$v1){
					$tmphtml .= form::radio($data,$k1,$k1==$value?true:false,$extra).$v1.' ';
				}
				$html = $tmphtml;
				break;
			case 'htmlarea':
				$html = form::htmlarea($data,$value,$extra);
				break;
			case 'basehtmlarea':
				$html = form::basehtmlarea($data,$value,$extra);
				break;
			case 'pagehtmlarea':
				//不能出现2个分页输入框
				if ($this -> IS_HAVE_PAGE_HTML){
					$html = form::htmlarea($data,$value,$extra);
				}else{
					$this -> IS_HAVE_PAGE_HTML = true;
					$html = form::pagehtmlarea($data,$value,$extra);
				}
				break;
			case 'time':
				$data['time'] = true;
				$html = form::timeinput($data,$value,$extra);
				break;
			case 'date':
				$data['time'] = null;
				$html = form::timeinput($data,$value,$extra);
				break;
			case 'hidden':
				$data['type'] = 'hidden';
				$html = form::input($data,$value,$extra);
				break;
			case 'imginput':
				$html = form::imginput($data,$value,$extra);
				break;
			default:
				$data['class'] or $data['class'] = 'input';
				$html = form::input($data,$value,$extra);
		}
		return $html;
	}*/


	/*
	public function get_advfield_html($arr,$fieldname=''){
		if (!is_array($arr))return '';
		if (!isset($arr['_g'])||!is_array($arr['_g'])){
			//缺少设置属性
			return '';
		}
		
		if ($arr['_g']['type']=='1'){
			//横铺
		}elseif ($arr['_g']['type']=='2'){
			//展开
		}else{
			//切换
			$tmphtml = '<table border="0" cellpadding="2" cellspacing="1" class="tableborder" style="width:100%"><tr><th colspan="10" style="text-align:right"><div style="float:left;padding:1px 0 0 5px;">'.$arr['_g']['name'].'</div><input type="button" class="btns" value="添加" onclick="show_AdvField(\''.$arr['_g']['idstr'].'\')" /><input type="button" class="btns" value="删除" /></th></tr></table>';
		}
		
		$autowidth = 100;
		$j = 0;
		foreach ($arr as $k => $myset){
			//设置字段
			if ($k=='_g'){
				//或略
				continue;
			}
			
			
			if (isset($myset['_g']) && is_array($myset['_g'])){
				$grouptype = TRUE;
				$thename = $myset['_g']['name'];
				$mywidth = $myset['_g']['editwidth'];
				if (!isset($myset['_g']['editwidth'])||$myset['_g']['editwidth']===NULL){
					$mywidth=$autowidth;
				}else{
					$mywidth = $myset['_g']['editwidth'];
				}
			}else{
				$grouptype = FALSE;
				$thename = $myset['name'];
				$myset['type']=='hidden' or $j++;
				$trcolor = $j % 2 == 0?' class="td3"':'';
				if (!isset($myset['editwidth'])||$myset['editwidth']===NULL){
					$mywidth=$autowidth;
				}else{
					$mywidth = $myset['editwidth'];
				}
			}
			if ($mywidth>0){
				$autowidth = $mywidth;
			}
			
			$tmphtml .= '<table border="0" cellpadding="2" cellspacing="1" align="center" class="tableborder" style="width:100%;border-top:none;border-bottom:1px solid #c3d3dc;"><tr'.$trcolor.($myset['type']=='hidden'?' style="display:none;':'').'>';
			if ($mywidth>0){
				$tmphtml .= '<td class="td1" align="right" width="'.$mywidth.'" style="border-top:none">' . $thename .'：<br/><img src="'.ADMIN_IMGPATH.'/admin/spacer.gif" style="height:0px;" width="'.$mywidth.'" /></td>';
			}
			
			$newfieldname = $fieldname.'][{{'.$arr['_g']['idstr'].'}}]['.$k;
				
			$tmphtml .= '<td class="td2" style="border-top:none">';
			if ($grouptype){
				//当前是组
				$myset['_g']['idstr'] = $arr['_g']['idstr'].'_'.$k;
				$tmphtml .= $this -> get_advfield_html($myset,$newfieldname);
			}else{
				$tmphtml .= $this -> edithtml($myset,NULL,$newfieldname);
			}
			
			$tmphtml .= (trim($myset['description'])? ' <span class="helpicon" title="'. str_replace(array("\r","\n","\"",'&',),array('<br/>','<br/>','&quot;','&amp;'),$myset['description']) .'">&nbsp;</span>':'').
			'</td></tr></table>';
		}
		
		
		$tmphtml = '<div id="f_Div_'.$arr['_g']['idstr'].'"></div>'.
		'<script type="text/javascript" defer="defer">var AdvField_'.$arr['_g']['idstr'].' = '.
		str_replace(
			array('</script>'),
			array('<\'+\'/script>'),
			var_export($tmphtml,TRUE)
		).
		';if (typeof(AdvFieldNum_'.$arr['_g']['idstr'].')=="undefined"){var AdvFieldNum_'.$arr['_g']['idstr'].' = 0;}'.
		'$("f_Div_'.$arr['_g']['idstr'].'").id="f_Div_'.$arr['_g']['idstr'].'_"+AdvFieldNum_'.$arr['_g']['idstr'].';show_AdvField("'.$arr['_g']['idstr'].'");</script>';
		
		//echo htmlspecialchars($tmphtml);
		return $tmphtml;
	}
*/
	

	/**
	 * 对POST过来的数据进行重新组织
	 *
	 * @param array $advvalue 用户提交的值
	 * @param array $advset 字段设置
	 * @return array $newvalue
	 */
	protected function _format_adv_value($advvalue,$advset=null){
		$newvalue = array();
		if (!is_array($advvalue))return ;
		foreach ($advvalue as $key=>$value){
			if (is_int($key)&&$key>=0){
				//序号
				//print_r($advset);
				if (is_array($value)){
					$newvalue[] = $this -> _format_adv_value($value,$advset);
				}else{
					$newvalue[] = $value;
				}
			}else{
				//KYE
				if (!($newset = $advset[$key]))continue;
				if (is_array($value)){
					$newvalue[$key] = $this -> _format_adv_value($value,$newset);
				}else{
					if ($newset['type']=='time'||$newset['type']=='date'){
						$newvalue[$key] = $value;
					}else{
						$newvalue[$key] = $this -> check_postvalue($value,$newset);
					}
				}
			}
		}
		return $newvalue;
	}
	/**
	 * 验证、转换POST数据
	 *
	 * @param string $value
	 * @param array $fieldset
	 * @return string value
	 */
	public function check_postvalue($value,$fieldset){
		//print_r($fieldset);
		if (empty($value) && $fieldset['notempty'] ==true){
			MyqeeCMS::show_error( Myqee::lang('admin/info.error.postempty',$fieldset['title']) ,TRUE );
		}

		if (is_array($value)){
			if ($fieldset['usehtml']==2){
				//多维数组
				$value = $this->_format_adv_value($value,$fieldset['adv'],true);
			}elseif ($fieldset['usehtml']==0){
				if ($fieldset['type']=='pagehtmlarea'){
					//带分页内容
					$value = MyqeeCMS::get_title_info_string_bypost($_POST['page_title'],$value);
				}else{
					$tmpvalue = '';
					foreach ($value as $v){
						$tmpvalue .= '|'.str_replace('|','&#124;',$v);
					}
					$value = '|'.ltrim($tmpvalue,'|').'|';
					unset($tmpvalue);
					
					if ($value=='||' && $fieldset['notempty'] ==true){
						MyqeeCMS::show_error( Myqee::lang('admin/info.error.postempty',$fieldset['title']) ,TRUE );
					}
					return $value;
				}
			}
		}
		
		switch ($fieldset['format']){
			case 'br':
				$value = Tools::formatstr(strip_tags($value,'<br>'));
				break;
			case 'string':
				$value = Tools::xss_clean(strip_tags($value,'<br><p><b><i><stong><u><font>'));
				break;
			case 'time':
				$value = strtotime($value);
				break;
			case 'int':
				$value = (int)$value;
				if ( isset($fieldset['max']) )$value = min($fieldset['max'],$value);
				if ( isset($fieldset['min']) )$value = max($fieldset['min'],$value);
				break;
			case 'safehtml':
				$value = Tools::xss_clean(strip_tags($value,'<br><strong><b><i><u><a><ul><ol><li><span><font><map><area><img><p><div><table><tbody><tr><td><th><h1><h2><h3><h4><h5><h5><pre><code>'));
				break;
			case 'html':
				$value = Tools::xss_clean($value);
				break;
			case 'email':
				if(valid::email($value) == true){
					//return $value;
				}elseif($fieldset['notempty'] == true){
					MyqeeCMS::show_error( Myqee::lang('admin/info.error.erroremail',$fieldset['title']) , TRUE );
				}else{
					$value = '';
				}
				break;
			case 'alt':
				$value = str_replace(array('\'','\"',"\n","\r",' '),array('&#39','&#34','&#10','&#10','&#32'),$value);
				break;
			case 'filename':
				$value = preg_replace("/[^0-9a-zA-Z_\-,\.]/",'',$value);
				break;
			case 'filepath':
				$value = preg_replace("/[^0-9a-zA-Z_\-,\.\/]/",'',str_replace('\\','/',$value));
				break;
			case 'serialize':
				$value = serialize($value);
				break;
			case 'json_encode':
				$value = Tools::json_encode($value);
				break;
			case 'htmlspec':
				$value = htmlspecialchars($value);
				break;
			case 'nodo':
				//不做任何处理
				break;
			default:
				$value = Tools::formatstr($value);
		}
		
		
		if (empty($value) && $fieldset['notempty'] ==true){
			MyqeeCMS::show_error( Myqee::lang('admin/info.error.postempty',$fieldset['title']) ,TRUE );
		}
		
		return $value;
	}

	public function save_db_config($dbid){
		if (!$dbid)return false;
		if (is_array($dbid)){
			$db_info = $dbid;
			$dbid = $db_info['id'];
		}else{
			$db_info = $this -> db -> getwhere ('[dbtable]',array('id'=>$dbid)) -> result_array(FALSE);
			$db_info = $db_info[0];
			$db_info['config'] = unserialize($db_info['config']);
			$db_info['modelconfig'] = unserialize($db_info['modelconfig']);
		}
		if (!$db_info['name'])return false;

		$db_field = is_array($db_info['config']['field'])?$db_info['config']['field']:array();

		$sys_field = array();
		$list_field = array();
		$edit_field = array();
		if ($db_info['ismemberdb']){
			$sysfile = 'member';
		}else{
			$sysfile = 'default';
		}
		$sysdbfield = Myqee::config('sysdbfield/'.$sysfile.'.field');
		foreach ((array)$db_field as $key => $itemfield){
			//auto config set
			if ($itemfield['autoset']){
				$sys_field[$itemfield['autoset']] = $key;
				if ($itemfield['autoset'] == 'id')$itemfield['islist'] = true;
				if ($itemfield['islist']){
					$list_field[$key] = $sysdbfield[$key]['listset'];
					$itemfield['dbname'] and $list_field[$key]['title'] = $itemfield['dbname'];
				}
				$edit_field[$key] = $sysdbfield[$itemfield['autoset']]['editset'];
				$edit_field[$key]['title'] = $db_info['config']['field'][$key]['dbname'];
				$edit_field[$key]['description'] = $db_info['config']['field'][$key]['comment'];
				if (!empty($db_info['config']['field'][$key]['default']))$edit_field[$key]['default']=$db_info['config']['field'][$key]['default'];
				if (!empty($db_info['config']['field'][$key]['candidate']))$edit_field[$key]['candidate']=$db_info['config']['field'][$key]['candidate'];
				if (!empty($db_info['config']['field'][$key]['getcode']))$edit_field[$key]['getcode']=$db_info['config']['field'][$key]['getcode'];
				$inputtype = $edit_field[$key]['type'];
				if ($db_info['config']['field'][$key]['class']){
					$edit_field[$key]['set']['class'] = $db_info['config']['field'][$key]['class'];
				}elseif ($inputtype != 'radio' && $inputtype != 'checkbox'){
					$edit_field[$key]['set']['class'] = 'input';
				}
				$db_info['config']['field'][$key]['size'] and $edit_field[$key]['set']['size'] = $db_info['config']['field'][$key]['size'];
				$db_info['config']['field'][$key]['rows'] and $edit_field[$key]['set']['rows'] = $db_info['config']['field'][$key]['rows'];
				$db_info['config']['field'][$key]['other'] and $edit_field[$key]['set']['other'] = $db_info['config']['field'][$key]['other'];
				
				$db_info['config']['field'][$key]['usehtml'] and $edit_field[$key]['usehtml'] = $db_info['config']['field'][$key]['usehtml'];
				$db_info['config']['field'][$key]['html'] and $edit_field[$key]['html'] = $db_info['config']['field'][$key]['html'];
				$db_info['config']['field'][$key]['adv'] and $edit_field[$key]['adv'] = $db_info['config']['field'][$key]['adv'];
				$db_info['config']['field'][$key]['editwidth'] and $edit_field[$key]['editwidth'] = $db_info['config']['field'][$key]['editwidth'];
				
			}else{
				//edit config
				$edit_field[$key] = array(
					'title' => $db_info['config']['field'][$key]['dbname'],
					'description' => $db_info['config']['field'][$key]['comment'],
					'type' => $db_info['config']['field'][$key]['inputtype'],
					'set' => array(
						'class' => $db_info['config']['field'][$key]['class'],
						'size' =>  $db_info['config']['field'][$key]['size'],
						'rows' =>  $db_info['config']['field'][$key]['rows'],
						'other' =>  $db_info['config']['field'][$key]['other'],
					),
					'usehtml' => (int)$db_info['config']['field'][$key]['usehtml'],
					'default' => $db_info['config']['field'][$key]['default'],
					'getcode' => $db_info['config']['field'][$key]['getcode'],
					'candidate' => $db_info['config']['field'][$key]['candidate'],
					'format' => $db_info['config']['field'][$key]['format'],
					'notempty' => $db_info['config']['field'][$key]['isnonull'],
					'html' => $db_info['config']['field'][$key]['html'],
					'adv' => $db_info['config']['field'][$key]['adv'],
					'editwidth' => $db_info['config']['field'][$key]['editwidth'],
				);
				
				$tmpvalue = $db_info['config']['field'][$key]['default'];
				$inputtype = $db_info['config']['field'][$key]['inputtype'];
			}
			//处理表单类型
			if ($inputtype == 'textarea' || $inputtype == 'htmlarea' || $inputtype == 'basearea'|| $inputtype == 'basehtmlarea'){
				//MyqeeCMS::show_error(str_replace("\"",'\'',str_replace("\n",'\n',print_r($db_info['config']['field'][$key],true))));
				$edit_field[$key]['set']['cols'] = $db_info['config']['field'][$key]['cols']?$db_info['config']['field'][$key]['cols']:($db_info['config']['field'][$key]['size']?$db_info['config']['field'][$key]['size']:80);
				$edit_field[$key]['set']['rows'] = $db_info['config']['field'][$key]['rows']?$db_info['config']['field'][$key]['rows']:12;
			}
			//echo $key.'--'.$inputtype."\n";
			if (!$edit_field[$key]['getcode']){
				$tmpvalue = $db_info['config']['field'][$key]['candidate'];
				if (!empty( $tmpvalue )){
					$tmpvalue = explode("\n",$tmpvalue);
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
					$edit_field[$key]['candidate'] = $tmpvalue;
				}
				unset($tmpvalue,$tmpvalue1);
			}

			//list config
			if ($itemfield['islist']){
				$list_field[$key] = array();
				$list_field[$key]['title'] = $itemfield['dbname'];
				if ($itemfield['width']) $list_field[$key]['width'] = $itemfield['width'];
				if ($itemfield['align']) $list_field[$key]['align'] = $itemfield['align'];
				if ($itemfield['class']) $list_field[$key]['class'] = $itemfield['tdclass'];
				if ($itemfield['autoset'] == 'title'){
					$list_field[$key]['titlelink'] = true;
				}elseif ($itemfield['autoset'] == 'writer'){
					$list_field[$key]['writerlink'] = true;
				}
				if ($itemfield['link']){
					$list_field[$key]['link'] = $itemfield['link'];
					$list_field[$key]['target'] = $itemfield['target'];
				}
				if ($itemfield['docode']){
					$list_field[$key]['docode'] = $itemfield['docode'];
					$list_field[$key]['boolean'] = $itemfield['boolean'];
				}else{
					if ($itemfield['boolean']) {
						$tmpboolean = explode("\n",$itemfield['boolean']);
						$booleanArray = array();
						foreach ($tmpboolean as $tempitem){
							if ($tempitem){
								$tempitemArr = explode('|',$tempitem);
								if (count($tempitemArr)==1){
									$tmpk = $tmpv = $tempitemArr[0];
								}else{
									//$tmpk = $tempitemArr[0];
									$tmpk = array_shift($tempitemArr);
									$tmpv = join('|',$tempitemArr);
								}
								$booleanArray[$tmpk] = $tmpv;
							}
						}
						$list_field[$key]['boolean'] = $booleanArray;
					}
				}
			}
		}
		list($database,$tablename) = explode('/',$db_info['name'],2);
		MyqeeCMS::saveconfig('db/'.$db_info['name'],array(
				'dbname'=>$db_info['name'],
				'database'=>$database,
				'tablename'=>$tablename,
				'sys_field'=>$sys_field,
				'is_member_db' => $db_info['ismemberdb'],
				'list'=>$list_field,
				'edit'=>$edit_field,
				'readbydbname'=>$db_info['readbydbname'],
				'usedbmodel'=>$db_info['usedbmodel'],
				'model'=>$db_info['modelconfig'],
			)
		);
		
		return true;
	}

	public function save_model_config($modelid){
		if (is_array($modelid)){
			$model_info = $modelid;
			$modelid = $model_info['id'];
		}elseif ($modelid>0){
			$model_info = $this -> db -> getwhere('[model]',array('id'=>$modelid)) -> result_array(FALSE);
			$model_info = $model_info[0];
		}
		if (!is_array($model_info))return false;
		//模型中字段配置
		$model_config = unserialize($model_info['config']);
		$model_field = is_array($model_config['field'])?$model_config['field']:array();
		$model_dbset = is_array($model_config['dbset'])?$model_config['dbset']:array();
		$model_list = is_array($model_config['list'])?$model_config['list']:array();
		$model_nolist = is_array($model_config['nolist'])?$model_config['nolist']:array();
		
		list($database,$tablename) = explode('/',$model_info['dbname'],2);
		$_db = Database::instance($database);
		if (!$_db -> table_exists($tablename)){
			return false;
		}
		$f_set = array();
		foreach ($model_field as $key => $value){
			if ($value['input'])$f_set['input'][$key] = $key;
			if ($value['editor'])$f_set['editor'][$key] = $key;
			if ($value['post'])$f_set['post'][$key] = $key;
			if ($value['notnull'])$f_set['notnull'][$key] = $key;
			if ($value['caiji'])$f_set['caiji'][$key] = $key;
			if ($value['search'])$f_set['search'][$key] = $key;
			if ($value['jiehe'])$f_set['jiehe'][$key] = $key;
			if ($value['list'])$f_set['list'][$key] = $key;
			if ($value['content'])$f_set['content'][$key] = $key;
		}
		return MyqeeCMS::saveconfig('model/model_'.$modelid,array(
			'dbname'=>$model_info['dbname'],
			'database'=>$tablename,
			'tablename'=>$tablename,
			'adminlist' => $model_config['adminlist'],
			'adminedit' => $model_config['adminedit'],
			'field'=>$model_field,
			'field_set' => $f_set,
			'dbset'=>$model_dbset,
			'list'=>$model_list,
			'nolist'=>$model_nolist,
		));
		/*
		$str = '<?php defined(\'MYQEEPATH\') or die(\'No direct script access.\');' ."\r\n";
		//$str .= '$config[\'sys_field\'] = ' . var_export($sys_field , true);

		*/
	}

	/**
	 * 更新数据表排序
	 *
	 * @param string $thetable 待更新的数据表
	 * @param string $myorder 排序，类似于id_1=2,id_3=1,id_4=3
	 * @param string $formleftstr 字段中前缀部分，id_
	 * @param int $thefield 查询的字段，通常是id
	 * @param string $myorderfield 排序字段，通常是myorder
	 * @return number $updatenum 更新数量
	 */
	public function editmyorder($thetable,$myorder,$formleftstr,$thefield='id',$myorderfield = 'myorder'){
		$mytable = explode(',',$myorder);
		$updatenum = 0;
		$thestrlen = strlen($formleftstr);
		foreach ($mytable as $v){
			if ($v){
				$temp = explode('=',$v);
				$theid = (int)substr($temp[0],$thestrlen);
				if ($theid > 0){
					$neworder = (int)$temp[1];
					$updatenum += count($this -> db -> update ($thetable,array($myorderfield => $neworder),array($thefield=>$theid)));
				}
			}
		}
		return (int)$updatenum;
	}
	
	
	

	/**
	 * 返回文件网页路径
	 *
	 * @param int/string $classid 栏目ID/数据表名称
	 * @param int $theinfo 信息内容
	 */
	public function getinfourl($classid,$theinfo){
		if ($classid>0){
			$myclass = $this -> get_class_array($classid);
			$dbname = $myclass['dbname'];
			if (!$dbname)return '#';
		}else{
			$dbname = $classid;
		}

		$this->dbconfig[$dbname] or $this->dbconfig[$dbname] = Myqee::config('db/'.$dbname);
		if ($this->dbconfig[$dbname]['sys_field']['linkurl']){
			if ($mylinkUrl = $theinfo[$this->dbconfig[$dbname]['sys_field']['linkurl']])return $mylinkUrl;
		}

		if ( !($classid>0) ){
			$classid = $theinfo[$this->dbconfig[$dbname]['sys_field']['class_id']];
			if ( $classid>0 && !$myclass ){
				$myclass = $this -> get_class_array($classid);
			}
		}
		$siteurl = Myqee::config('core.mysite_url');
		if (substr($siteurl,0,7)=='http://'){
			$info_url = $siteurl;
		}else{
			$info_url = 'http://'.Myqee::config('core.mysite_domain').$siteurl;
		}

		$path = $this -> getinfopath($classid>0?$classid:$dbname,$theinfo,false);
		if (file_exists(WWWROOT.$path)){
			if ($classid>0 && $myclass['hostname'] && substr($path,0,strlen($myclass['classpath']))==$myclass['classpath']){
				$path = substr($path,strlen($myclass['classpath']));
				$info_url = 'http://'.$myclass['hostname'].'/';
			}
			return $info_url.ltrim($path,'/');
		}else{
			//缺少数据表信息将无法获取信息内容，返回空链接
			if (!$dbname)return './#';
			
			$this->dbconfig[$dbname] or $this->dbconfig[$dbname] = Myqee::config('db/'.$dbname);
			//缺少唯一标示将无法定位具体信息，返回空链接
			if (!$this->dbconfig[$dbname]['sys_field']['id'])return './#';
			$myqeepage = Myqee::config('core.myqee_page');
			if ($myqeepage){
				$info_url .= $myqeepage.'/';
			}
			$info_url .= 'myinfo/'.substr(Des::Encrypt(($classid>0?$classid:$dbname).','.$theinfo[$this->dbconfig[$dbname]['sys_field']['id']],Myqee::config('encryption.urlcode.key')),2).Myqee::config('core.url_suffix');
			return $info_url;
		}
	}

	

	/**
	 * 返回文件路径
	 *
	 * @param int/string $classid 栏目ID/数据表名称
	 * @param array $theinfo 信息内容
	 * @param boolean $retrun_array 是否返回数组
	 */
	public function getinfopath($classid,$theinfo,$retrun_array = false){
		//栏目信息
		if ($classid>0){
			$myclass = $this -> get_class_array($classid);
			$dbname = $myclass['dbname'];
		}else{
			$dbname = $classid;
			$classid = NULL;
		}
		if (!$dbname)return false;
		
		$this->dbconfig[$dbname] or $this->dbconfig[$dbname]=Myqee::config('db/'.$dbname);
		
		//根据内容信息的ID重新获取信息classid（如果存在）
		if ( !($classid>0) ){
			$classid = $theinfo[$this->dbconfig[$dbname]['sys_field']['class_id']];
			if ( $classid>0 && !$myclass[$classid] ){
				$myclass = $this -> get_class_array($classid);
			}
		}

		if ( isset($this->dbconfig[$dbname]['sys_field']['filepath']) && !empty($theinfo[$this->dbconfig[$dbname]['sys_field']['filepath']]) ){
			//信息字段中存在filepath字段且不为空
			if ($classid>0 && !$myclass['content_pathtype']){
				$thepath = trim($myclass['classpath'],'/').'/';
			}
			$thepath .= ltrim($theinfo[$this->dbconfig[$dbname]['sys_field']['filepath']],'/');
		}else{
			if ($classid>0){
				if ($myclass['content_pathtype']){
					$thepath = trim($myclass['content_path'],'/');
				}else{
					$classpath =$myclass['classpath'];
				}
				$classpath = rtrim($classpath,'/');
				if ($myclass['content_selfpath'] && $this->dbconfig[$dbname]['sys_field']['createtime']){
					if ($theinfo[$this->dbconfig[$dbname]['sys_field']['createtime']]){
						$createtime = $theinfo[$this->dbconfig[$dbname]['sys_field']['createtime']];
					}else{
						$createtime = $_SERVER['REQUEST_TIME'];
						//更新记录
						$updateinfo = array( $this->dbconfig[$dbname]['sys_field']['createtime']=> $createtime );
						
					}
					
					$thepath = ($thepath?$thepath.'/':'').date($myclass['content_selfpath'],$createtime).'/';
				}
				
				if (isset($this->dbconfig[$dbname]['sys_field']['filepath'])){
					$updateinfo[$this->dbconfig[$dbname]['sys_field']['filepath']] = $thepath;
				}
			}else{
				$thepath = '';
			}
		}
		if ($classpath){
			$thepath = $classpath .'/'.$thepath;
		}
		

		//前缀
		if( $classid>0 ){
			$thename = $myclass['content_prefix'];
		}else{
			$thename = '';
		}

		//文件名
		if ($this->dbconfig[$dbname]['sys_field']['filename']){
			if ($theinfo[$this->dbconfig[$dbname]['sys_field']['filename']]){
				$thefullname = $theinfo[$this->dbconfig[$dbname]['sys_field']['filename']];
			}else{
				switch ($myclass['content_filenametype']){
					case 0:
						$thename .= $theinfo[$this->dbconfig[$dbname]['sys_field']['id']];
						break;
					case 1:
						$thename .= $theinfo[$this->dbconfig[$dbname]['sys_field']['createtime']];
						break;
					case 2:
						$thename .= md5(print_r($theinfo,true).'__'.$_SERVER['REQUEST_TIME']);
						break;
					case 3:
						$thename .= substr(md5(print_r($theinfo,true).'__'.$_SERVER['REQUEST_TIME']),8,16);
						break;
					default:
						$thename .= $theinfo[$this->dbconfig[$dbname]['sys_field']['id']];
						break;
				}
			}
		}else{
			$thename .= $theinfo[$this->dbconfig[$dbname]['sys_field']['id']];
		}
		
		if (!$thefullname){
			//后缀（扩展名）
			if ($this->dbconfig[$dbname]['sys_field']['content_suffix'] && $theinfo[$this->dbconfig[$dbname]['sys_field']['content_suffix']]){
				$thename .= $theinfo[$this->dbconfig[$dbname]['sys_field']['content_suffix']];
			}elseif($classid>0){
				$thename .= $myclass['content_suffix'];
			}else{
				$thename .= '.html';
			}
			$thefullname = $thename;
			if ($this->dbconfig[$dbname]['sys_field']['filename']){
				$updateinfo[$this->dbconfig[$dbname]['sys_field']['filename']] = $thefullname;
			}
		}
	
		if ($updateinfo && $this->dbconfig[$dbname]['sys_field']['id']){
			if (strpos($dbname,'/')===false){
				$database = 'default';
				$tablename = $dbname;
				$dbname = $database .'/'.$tablename;
			}else{
				list($database,$tablename) = explode('/',$dbname);
			}
			Database::instance($database)->update(
				$tablename, 
				$updateinfo ,
				array( $this->dbconfig[$dbname]['sys_field']['id']=>$theinfo[$this->dbconfig[$dbname]['sys_field']['id']] )
			);
		}
		return $retrun_array?array('path'=>$thepath,'name'=>$thefullname):rtrim($thepath,'/').'/'.$thefullname;
	}


	/**
	 * 返回数据表里面的字段，以下拉框的形式
	 * @param string $tablename 数据表名称
	 * @param array $option 返回数组
	 */
	public function get_table_field($dbname, $option = array()){
		$field = Myqee::config('db/'.$dbname);
		if ($field == NULL || $field == ''){
			return array();
		}
		$editfield = array();
		$editfield = $field['edit'];
		for ($i = 0; $i < count($editfield); $i++){
			$option[key($editfield)] = $editfield[key($editfield)]['title'].'('.key($editfield).')';
			next($editfield);
		}
		return $option;
	}

	public function mydata_save_config($id = 0){
		if (is_array($id)){
			$data = $id;
			$id = $data['id'];
		}elseif($id>0){
			$data = $this -> db -> getwhere('[mydata]',array('id'=>$id)) -> result_array(FALSE);
			$data = $data[0];
			if (!$data)return FALSE;
		}
		
		if (!$id>0){
			return FALSE;
		}
		
		if($data['type'] == 0){
			$where = '';
			if( $data['isheadlines'] > 0 ){
				$where .= 'headlines|';
			}
			if( $data['is_hot'] > 0 ){
				$where .= 'hot|';
			}
			if( $data['is_indexshow'] > 0 ){
				$where .= 'indexshow|';
			}
			if( $data['new'] > 0 ){
				$where .= 'new|';
			}
			if( $data['ontop'] > 1 ){
				$where .= 'ontop,'.(int)$data['ontop'].'|';
			}else if( $data['ontop'] == 1 ){
				$where .= 'ontop|';
			}
			if( $data['commend'] > 0 ){
				$where .= 'commend,'.(int)$data['commend'].'|';
			}else if( $data['commend'] == 0 ){
				$where .= 'commend|';
			}
			$data['data_where'] = $where;
		}
		
		return MyqeeCMS::saveconfig('mydata/mydata_'.$id,$data);
	}
	
	/**
	 * 获取任务信息表数据
	 *
	 * @return array datable
	 */
	public function get_tasks_array($isuse = 1,$id = 0){
		$where = array('isuse'=>$isuse);
		if(id > 0){
				$where['id'] = (int)$id;
		}
		$result = $this -> db -> from ( '[tasks]' ) -> where($where);
		$result = $result -> orderby('cycletype','DESC') -> get ()->result_array ( FALSE );
		return $result;
	}
	
	
	/**
	 * 获取用户接口函数列表
	 *
	 * @param string $classname
	 * @param array $fristarray
	 * @return array apimethodes 
	 */
	public function get_apiclass_list($classname,$fristarray=array(''=>'无'),$noname=FALSE){
		$tmpmodelapi = (array)get_class_methods($classname);
		sort($tmpmodelapi);
		if (!$noname && count($tmpmodelapi)){
			$tmpclass = new $classname;
			$methodname = (array)$tmpclass -> _methodname;
			$methodname += (array)$tmpclass -> _my_methodname;
		}
				
		$apimethodes = (array)$fristarray;
		foreach ($tmpmodelapi as $value){
			if (substr($value,0,1)!='_'){
				$apimethodes[$value] = $value . ($methodname[$value]?'('.$methodname[$value].')':'');
			}
		}
		unset($tmpclass);
		
		return $apimethodes;
	}
	
	/**
	 * 处理字段的高级分组录入项
	 *
	 * @param array $adv
	 * @param boolean $isfrist 是否首节点
	 * @return array $adv
	 */
	public function set_field_adv($adv,$isfrist=false){
		if (!is_array($adv))return NULL;
		$type = array(
			'input' => '1',
			'password' => '1',
			'time' => '1',
			'date' => '1',
			'select' => '1',
			'selectinput' => '1',
			'radio' => '1',
			'checkbox' => '1',
			'textarea' => '1',
			'basehtmlarea' => '1',
			'htmlarea' => '1',
			'pagehtmlarea' => '1',
			'imginput' => '1',
			'flash' => '1',
			'file' => '1',
			'color' => '1',
			'hidden' => '1',
		);
		$format = array(
			''=>'1',
			'br'=>'1',
			'string'=>'1',
			'time'=>'1',
			'int'=>'1',
			'safehtml'=>'1',
			'html'=>'1',
			'htmlspec'=>'1',
			'alt'=>'1',
			'filepath'=>'1',
			'filename'=>'1',
			'serialize'=>'1',
			'json_encode'=>'1',
			'nodo'=>'1',
		);
		foreach ($adv as $k=>$v){
			
			if ($k == '_g'){
				if (!$isfrist && !preg_match("/^[a-z][0-9a-z_]*$/i",$v['flag'])){
					continue;
				}
				//当前为分组
				$tmparr['_g'] = array(
					'flag' => $v['flag'],
					'name' => $v['name'],
					'type' => $v['type']>=0&&$v['type']<=2?$v['type']:0,
					'num' => $v['num']>=0?$v['num']:0,
					'editwidth' => $v['editwidth']>0||$v['editwidth']==='0'?$v['editwidth']:NULL,
					'isadd' => $v['isadd']?1:0,
					'isdel' => $v['isdel']?1:0,
					'isorder' => $v['isorder']?1:0,
				);
				if (is_array($v['group_auto'])){
					if (isset($v['group_auto']['_g'])){
						$tmparr['_g']['group_auto'] = $this->set_field_adv($v['group_auto'],true);
					}else{
						$tmparr['_g']['group_auto'] = $this->_get_field_var($v['group_auto']);
					}
				}
			}else{
				if (isset($v['_g']) && is_array($v['_g'])){
					$tmparr[$k] = $this -> set_field_adv($v);
				}else{
					if (!preg_match("/^[a-z][0-9a-z_]*$/i",$v['flag'])){
						continue;
					}
					$tmparr[$k] = $this->_get_field_var($v);
				}
			}
			
		}
		return $tmparr;
	}
	
	protected function _get_field_var($v){
		if ($v['candidate']){
			$tmpvalue = $v['candidate'];
			if (!empty( $tmpvalue )){
				$tmpvalue = explode("\n",$tmpvalue);
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
				$v['candidate'] = $tmpvalue;
			}
			unset($tmpvalue,$tmpvalue1);
		}
		return array(
			'flag' => $v['flag'],
			'name' => $v['name'],
			'type' => $type[$v['type']]?$v['type']:'input',
			'format' => $format[$v['format']]?$v['format']:'',
			'editwidth' => $v['editwidth']>0||$v['editwidth']==='0'?$v['editwidth']:NULL,
			'set' => array(
				'size' => $v['set']['size']>0?$v['set']['size']:NULL,
				'rows' => $v['set']['name']>0?$v['set']['name']:NULL,
				'class' => $v['set']['class'],
				'other' => $v['set']['other'],
			),
			'default' => $v['default'],
			'candidate' => $v['candidate'],
		);
	}
	
	
	public function get_allsites_forselect($myarr=array()){
		$arr = $this -> get_allsites('id,sitename');
		$count = count($arr);
		if (!is_array($myarr))$myarr = array();
		for($i=0;$i<$count;$i++){
			$myarr[$arr[$i]['id']] = $arr[$i]['sitename'];
		}
		return $myarr;
	}
	
	public function get_allsites($select = '*'){
		return 
		$this 	-> db 
				-> select($select) 
				-> orderby('myorder','ASC') 
				-> orderby('id','DESC') 
				-> getwhere('[site]',array('isuse'=>1)) 
				-> result_array(FALSE);
	}
	
	public function get_site_byid($siteid,$select){
		$arr = $this 	-> db 
						-> select($select) 
						-> getwhere('[site]',array('id'=>$siteid,'isuse'=>1)) 
						-> result_array(FALSE);
		return $arr[0];
	}
	
	
	public function get_site_forselect($a=array()) {
		$all_site = $this -> get_allsite_array(null,array('myorder'=>'DESC'),'id,sitename');
		if (!is_array($a))$a=array();
		foreach ($all_site as $item){
			$a[$item['id']] = $item['sitename'];
		}
		return $a;
	}
	
	public function get_allsite_array($where = null,$orderby = array('myorder'=>'DESC'),$select = '*',$limit=null) {
		$this -> db -> select($select) -> where('isuse',1);
		$mysite = Passport::getadminsite();
		if ($mysite){
			if ($mysite!='-ALL-'){
				$this -> db -> in('id',explode(',',$mysite));
			}
		}
		if ($where){
			$this -> db -> where($where);
		}
		if ($orderby && is_array($orderby)){
			foreach ($orderby as $k => $v){
				$this -> db -> orderby($k,$v=='ASC'?'ASC':'DESC');
			}
		}
		if ($limit){
			$this -> db -> limit($limit);
		}
		
		return $this -> db -> get('[site]') -> result_array(FALSE);
	}
	
	public function get_site_allclass_id($dbname) {
		if (!$this->site_id>0)return FALSE;
		if (isset($this -> mysite_allclassid[$dbname])){
			return $this -> mysite_allclassid[$dbname];
		}
		$result = $this -> db -> select('classid') -> from('[class]') -> where(array('siteid'=>$this->site_id,'dbname'=>$dbname)) -> get() -> result_array(FALSE);
		if (!count($result)){
			$this -> mysite_allclassid[$dbname] = 0;
			return FALSE;
		}
		$class = array();
		foreach ($result as $item){
			$class[] = $item['classid'];
		}
		$this -> mysite_allclassid[$dbname] = $class;
		return $class;
	}
	
	/**
	 * 得到某个专题的详细信息
	 * @param int $sid
	 * @return array
	 */
	public function get_specialinfo ($sid) {
		$sid = intval($sid);
		$tmp = $this ->db -> getwhere ('[special]',array('sid'=>$sid))->result_array(false);
		$info = $tmp[0];
		return $info;
	}
	
	/**
	 * 根据条件得到专题的列表
	 * @param array $where
	 * @param int $offset
	 * @param int $limit
	 * @return array
	 */
	public function get_speciallist ($where=array(),$limit=20,$offset=0) {
		$offset = intval($offset);
		$limit = intval($limit);
		if ($limit >0) {
			$this->db->limit ($limit,$offset);
		}
		$query = $this ->db->orderby(array('myorder'=>'asc','sid'=>'asc')) -> getwhere ('[special]',$where)->result_array(false);
		return $query;
	}
	
	/**
	 * 根据条件得到专题的数量
	 * @param array $where
	 * @return int
	 */
	public function get_speciallistcount ($where=array()) {
		$query = $this ->db-> where ($where)->count_records();
		return $query;
	}
	
	/**
	 * 处理扩展表
	 *
	 * @param array $field
	 * @return array
	 */
	protected function _dealRelationTable ($field) {
		$newField = array(); 
		foreach ($field as $key=>$val) {
		 	if (substr($key,0,1) != '_') {
		 		//正常字段
		 		$newField[$key] = $val;
		 	} elseif ($val['input'] || $val['edit']) {
		 		//扩展表
		 		$tablename = substr($key,1);
		 		$newField = array_merge($newField,$this->_getFieldFromTable($tablename));
		 	}
		}
		return $newField;
	}
	
	/**
	 * 从表中得到所需要的字段
	 *
	 * @param string $table
	 * @return array
	 */
	protected function _getFieldFromTable ($table) {
		$config = Myqee::config('db/'.$table);
		$field = array();
		if (is_array($config['model']['field'])) {
			foreach ($config['model']['field'] as $key=>$val) {
				$field["{$table}.{$key}"] = $val;
			}
		}
		if (is_array($config['edit'])) {
			foreach ($config['edit'] as $key=>$val) {
				$this -> dbset['edit']["{$table}.{$key}"] = $val;
			}
		}
		return $field;
	}
	
	protected function _addRelationInfo(&$myinfo,$dbconfig) {
		//处理扩展表，例如输入用户ID，显示用户名这种
		$extend_tables = array();
		$extend_fkeys = array();
		$extend_tables_info = array();
		$_relationfieldeinfos = $dbconfig['model']['relationfield'];
		if (empty($_relationfieldeinfos)) {
			return;
		}
		$fieldshow = array_keys($dbconfig['list']);
		foreach ($fieldshow as $val) {
			foreach ($_relationfieldeinfos as $v) {
				if ($val == $v['field'] && $v['dbfield'] != $v['dbfieldshow'] && $v['relation'] == 'n:1') {
					$extend_tables[] = $v['dbtable'];
				}
			}
		}
		foreach ($myinfo as $val) {
			//处理扩展表的外键
			if (is_array($extend_tables)) foreach ($extend_tables as $v) {
				$extend_fkeys[$v]['ids'][] = $val[$_relationfieldeinfos[$v]['field']];
			}
		}
		foreach ($extend_tables as $val) {
			if (empty($extend_fkeys[$val]['ids'])) {
				continue;
			}
			list($_database,$_tablename) = explode('/',$_relationfieldeinfos[$val]['dbtable'],2);
			$_db = Database::instance($_database);
			$_select = array($_relationfieldeinfos[$val]['dbfield'],$_relationfieldeinfos[$val]['dbfieldshow']);
			$extend_tables_info[$val] = $_db->select ($_select)->in ($_relationfieldeinfos[$val]['dbfield'],$extend_fkeys[$val]['ids'])->get($_tablename)->result_assoc();
		}
		
		//将副表扩展表的数据合并到主表
		foreach ($myinfo as $key=>$val) {
			foreach ($extend_fkeys as $k=>$v) {
				$val[$_relationfieldeinfos[$k]['field']] = $extend_tables_info[$k][$val[$_relationfieldeinfos[$k]['field']]][$_relationfieldeinfos[$k]['dbfieldshow']];
			}
			$myinfo[$key] = $val;
		}
	}
	
	/**
	 * 处理模型的钩子
	 * @param array $dbset 数据库配置
	 * @param array $model_set 模型数组
	 * @param array $info 信息
	 */
	protected function _hook ($dbset,&$model_set,&$info) {
		$vfields = (array)MyqeeCMS::get_virtual_field();
		if (!is_array($vfields) || empty($vfields)) {
			return ;
		}
		foreach ($vfields as $key=>$val) {
			if (empty($val)) {
				continue;
			}
			$model_set['dbset'][$key] = $val;
			$method = $val['modelhook'];
			if (empty($method)) {
				continue;
			}
			if (!is_callable($method)) {
				continue;
			}
			call_user_func_array($method,array($dbset,&$model_set,&$info));
		}
	}
}