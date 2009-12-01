<?php

class Template_Controller_Core extends Controller {
	function __construct(){
		parent::__construct();
		$this -> session = Passport::chkadmin();
		$this -> mytpl = array('all','cover','list','content','search','page','block','frame');
	}

	public function index($p=1,$tplname='',$catename=false){
		Passport::checkallow('template.list');
		$per = 20;
		if (!in_array($tplname,$this -> mytpl)){
			$tplname = 'all';
		}
		//获取模板组
		$tplconfig = Myqee::config('template');
		$tplgroup = $tplconfig['group'];
		if (!is_array($tplgroup)){
			$tplgroup = array('default' => '默认模板组');
		}else{
			$tmpgroup = array();
			foreach ($tplgroup as $k=>$item){
				$tmpgroup[$k] = $item['name'];
			}
			$tplgroup = $tmpgroup;
		}
		$nowtplgroup = $this -> session -> get('now_tlpgroup');
		
		
		if (!$nowtplgroup){
			$nowtplgroup = $this -> session -> get('now_site_tlpgroup');
			if (!$nowtplgroup){
				if ( !$nowtplgroup = $tplconfig['default'] ){
					$nowtplgroup = key($tplgroup);
				}
			}
			$this -> session -> set ('now_tlpgroup',$nowtplgroup);
		}
		
		$view = new View('admin/template_list');

		$listwhere['group'] = $nowtplgroup;
		if ($tplname != 'all' ){
			$listwhere['type'] = $tplname;
		}
		if ($catename){
			$listwhere['cate'] = $catename;
			$view -> set('cate' , htmlspecialchars($catename) );
		}


		$adminmodel = new Admin_Model;
		$num = $adminmodel -> db -> count_records('[template]',$listwhere);

		$this -> pagination = new Pagination( array(
			//'base_url'		 => 'template/index/{page}/',
			'uri_segment'    => 'index',
			'total_items'    => $num,
			'items_per_page' => $per,
		) );

		$view -> tplname = $tplname;
		$view -> tplgroup = $tplgroup;
		$view -> nowtplgroup = $nowtplgroup;
		$view -> set('list' , $adminmodel -> get_alltemplate_array($listwhere,array('filename'=>'ASC'),'*',array($per , $this -> pagination -> sql_offset)) );
		$view -> set('page', $this -> pagination -> render('digg') );
		$view -> render(TRUE);
	}

	public function changegroup($groupkey=''){
		$tplgroup = Myqee::config('template.group');
		if (!$groupkey || !$tplgroup[$groupkey])$groupkey = $groupkey = key($tplgroup);
		$this -> session -> set ('now_tlpgroup',$groupkey);
		header('location:'.Myqee::url('template/index'));
	}
	public function add($type = ''){
		$this -> edit(0,$type);
	}
	public function copy($id = 0){
		if ($id>0){
			$this -> iscopy = true;
		}
		$this -> edit($id);
	}

	public function edit($id = 0,$type = 'cover'){
		if ($id>0 && !$this->iscopy){
			Passport::checkallow('template.edit');
		}else{
			Passport::checkallow('template.add');
		}
		$view = new View('admin/template_add');
		$this -> db = Database::instance();
		
		$tplconfig = Myqee::config('template');
		
		if ($id>0){
			$result = $this -> db -> from('[template]') -> where(array('id' => $id)) -> limit(1) -> get() -> result_array(FALSE);
			if ( count($result)>0 ){
				if ($this -> iscopy){
					unset($result[0]['id']);
					$result[0]['tplname'] .= '_copy';
					$result[0]['filename'] .= '_copy';
					$view -> set ( 'page_title' , Myqee::lang('admin/template.copytemplate') );
				}else{
					$view -> set ( 'page_title' , Myqee::lang('admin/template.edittemplate') );
				}
				$nowtplgroup = $result[0]['group'];
				$template = $result[0];
			}
		}else{
			$view -> set ('page_title' , Myqee::lang('admin/template.newtemplate') );
			$template = array('type'=>$type);
		}
		
		$nowtplgroup or $nowtplgroup = $this -> session -> get('now_tlpgroup');
		$allsuffix = $tplconfig['group'][$nowtplgroup]['allsuffix'];
		if ($allsuffix){
			$tmpsuffix = explode('|',$allsuffix);
			$allsuffix = array();
			foreach ($tmpsuffix as $item){
				if (preg_match("#.[a-z0-9]+#",$item)){
					$allsuffix[$item] = $item;
				}
			}
		}
		if (!$allsuffix){
			$allsuffix = array('.tpl'=>'.tpl','.html'=>'.html','.txt'=>'.txt');
		}
		$view -> set('allsuffix',$allsuffix);
		if (!$template['filename_suffix'])$template['filename_suffix'] = $tplconfig['group'][$nowtplgroup]['suffix'];
		$view -> set('template',$template);

		$result = $this -> db -> select('cate') -> from('[template]') -> groupby('cate') -> orderby('myorder' , 'asc') -> get() -> result_array(FALSE);
		$tplcate = array();
		foreach ($result as $item){
			$tplcate[$item['cate']] = $item['cate'];
		}

		$view -> tplcate = $tplcate;
		$view -> render(TRUE);
	}


	public function save($id=0){
		if (EDIT_TEMPLATE !== 1){
			MyqeeCMS::show_error('当前设置不允许修改模板！',TRUE);
		}
		$id = (int)$id;
		if ($id>0){
			Passport::checkallow('template.edit');
		}else{
			Passport::checkallow('template.add');
		}
		$post = $_POST['template'];

		if (empty($post['tplname'])){
			MyqeeCMS::show_error(Myqee::lang('admin/template.error.emptytplname'),true);
		}
		$post['tplname'] = Tools::formatstr($post['tplname']);

		$post['filename'] = trim(str_replace('\\','/',$post['filename']),'/ .');
		if ( empty($post['filename']) || !preg_match("#^[0-9a-zA-Z_/]+$#",$post['filename'])){
			MyqeeCMS::show_error(Myqee::lang('admin/template.error.errfilename'),true);
		}
		
		if (empty($post['content'])){
			MyqeeCMS::show_error(Myqee::lang('admin/template.error.emptycontent'),true);
		}
		

		/*
		if ( !Myqee::config('template.allowphp') ){
			if(strpos($post['content'],'<?') || strpos($post['content'],'?>'))
			MyqeeCMS::show_error(Myqee::lang('admin/template.error.contenthavephp'),true);
		}
		*/

		$post['isuse'] == 0 or $post['isuse'] = 1;
		
		$this -> db = Database::instance();
		
		if ($id>0){
			$olddata = $this -> db -> select('group,filename,filename_suffix,filemtime') -> getwhere('[template]', array('id'=>$id)) ->  result_array(FALSE);
			$olddata = $olddata[0];
			if (!$olddata){
				MyqeeCMS::show_error(Myqee::lang('admin/template.error.notemplateid'),true);
			}
			$grouppath = $olddata['group'];
		}else{
			//添加模板组
			$data['group'] = $this -> session -> get('now_tlpgroup');
			if(!$data['group'])$data['group'] = $this -> session -> get('now_site_tlpgroup');
			if(!$data['group'])$data['group'] = 'default';
			$grouppath = $data['group'];
		}
		$tplconfig = Myqee::config('template.group.'.$grouppath);
		if (!$tplconfig){
			MyqeeCMS::show_error(Myqee::lang('admin/template.error.nothisgroup'),true);
		}
		if (!$tplconfig['allsuffix']){
			$allsuffix = array('.txt');
		}else{
			$allsuffix = explode('|',$tplconfig['allsuffix']);
		}
		$post['filename_suffix'] = in_array($post['filename_suffix'],$allsuffix)? $post['filename_suffix'] : '.txt';
		

		in_array($post['type'], $this -> mytpl) or $post['type'] = 'cover';
		$data = array(
			'tplname' => $post['tplname'],
			'type' => $post['type'],
			'isuse' => $post['isuse'],
			'myorder' => (int)$post['myorder'],
			'cate' => Tools::formatstr($post['cate']),
			'content' => $post['content'],
			'filename' => $post['filename'],
			'filename_suffix' => $post['filename_suffix'],
			'filemtime' => time(),
		);
		
		empty($data['cate']) and $data['cate'] = Myqee::lang('admin/template.defaulttemplate');

		//检测是否已经存在的文件
		$chkwhere = array('group'=>$grouppath,'filename'=>$post['filename'],'filename_suffix'=>$post['filename_suffix']);
		if ($id>0)$chkwhere['id!='] = $id;
		$chktpl = $this -> db -> getwhere('[template]', $chkwhere) ->  result_array(FALSE);
		$chktpl = $chktpl[0];

		if ($chktpl)MyqeeCMS::show_error(Myqee::lang('admin/template.error.contusertlpname'),true);

		
		//模板组文件夹
		$filepath = MYAPPPATH .'views/'. $grouppath.'/';
		//完整路径
		$fullfile = $filepath . $data['filename'].$data['filename_suffix'];
		
		if (file_exists($fullfile)){
			//文件已经存在
			$filemtime = filemtime($fullfile);
		}else{
			$filemtime = 0;
		}

		if ($id>0){
			if ($_POST['overfile']!='yes' && $olddata['filemtime']>0 && $olddata['filemtime']<$filemtime && md5(file_get_contents($fullfile))!=md5($data['content']) ){
				$this -> _show_file_error(
					Myqee::lang('admin/template.error.filemtimeerror'),
					$data,
					$grouppath
				);
			}
			$fulloldfile = $this -> _get_fulltplname($olddata);

			//文件路径发生变化时删除旧文件
			if ($fullfile != $fulloldfile){
				if (file_exists($fulloldfile))@unlink($fulloldfile);		//移除文件
			}

			$status = $this -> db -> update('[template]',$data,array('id'=>$id)) -> count();
		}else{
			if ($_POST['overfile']!='yes' && $filemtime && md5(file_get_contents($fullfile))!=md5($data['content']) ){
				$this -> _show_file_error(
					Myqee::lang('admin/template.error.file_exists'),
					$data,
					$grouppath
				);
			}
			
			$data['group'] = $grouppath;
			$status = $this -> db -> insert('[template]',$data) -> count();
		}

		if ($status){
			//保存文件
			Tools::create_dir(dirname($fullfile));
			if (@file_put_contents($fullfile , $data['isuse']?$data['content']:'')){
				//输出提示信息
				@touch($fullfile,$data['filemtime']);
				MyqeeCMS::show_ok(Myqee::lang('admin/template.info.saveok'),true);
			}else{
				MyqeeCMS::show_error(Myqee::lang('admin/template.error.saveerror'),true);
			}
		}else{
			MyqeeCMS::show_info(Myqee::lang('admin/template.info.noeditor'),true);
		}
	}
	
	protected function _show_file_error($msg,$data,$grouppath){
		$url = Myqee::url('template/read_file?group='.$grouppath.'&file='.$data['filename'].'&ext='.$data['filename_suffix'].'&readfile=yes');
		MyqeeCMS::show_error(array(
			'message' => $msg,
			'btn' => array(array('知道了','ok'),array('查看文件','view')),
			'handler' => 'function(e){
				if (e=="view"){
					parent.win({"title":"查看文件代码","message":"'.$url.'","width":700,"height":400,"iframe":true,"allowSelect":true,"allowRightMenu":true});
				}
			}',
		),true);
	}

	protected function _get_fulltplname($tplarray){
		return MYAPPPATH .'views/' .$tplarray['group'].'/'.$tplarray['filename'].$tplarray['filename_suffix'];
	}
	

	
	public function read_file(){
		$arr['group'] = strtolower(preg_replace("/[^a-zA-Z0-9_]+/",'',$_GET['group']));
		if ( $_GET['file'] && preg_match("/^[0-9a-zA-Z_]+$/",$_GET['file'])){
			$arr['filename'] = $_GET['file'];
		}
		$arr['filename_suffix'] = in_array($_GET['ext'],array('.php','.tpl','.htm','.html','.txt'))? $_GET['ext'] : '.php';
		if (!$arr['group']||!$arr['filename']||!$arr['filename_suffix']){
			MyqeeCMS::show_error(Myqee::lang('admin/template.error.parametererror'),true);
		}
		$arr['filename'] = $this -> _get_fulltplname($arr);
		
		if (!file_exists($arr['filename'])){
			MyqeeCMS::show_error(Myqee::lang('admin/template.error.notemplateid'),true);
		}
		$v = file_get_contents($arr['filename']);
		
		echo '<html><body style="margin:0;padding:4px 4px 0 4px">',
		form::textarea(array('style'=>'border:1px #999 solid;font-size:12px;width:100%;height:358px','readonly'=>'readonly','value'=>$v)),
		'</body></html>'
		;
	}

	public function output($allid,$key = ''){
		Passport::checkallow('template.output');
		$this -> db = Database::instance();

		if ($this -> outgroup){
			$results = $this -> db -> from('[template]') -> where('group',$allid) -> orderby('id') -> get() -> result_array(false);
		}else{
			$allid = Tools::formatids($allid , false);
			$results = $this -> db -> from('[template]') -> in('id',$allid) -> orderby('id') -> get() -> result_array(false);
		}
		if (count($results) == 0){
			MyqeeCMS::show_info(Myqee::lang('admin/template.info.noouttemplate'),true);
		}
		$mydata = Tools::info_encryp($results,$key,true);
		if (!$mydata){
			MyqeeCMS::show_info(Myqee::lang('admin/model.info.nooutdb'),true);
		}
		download::force('./',$mydata,'template.txt');
	}

	public function outputgroup($thegroup,$key = ''){
		$this -> outgroup = true;
		$this -> output($thegroup,$key);
	}

	public function dels ($tplids) {
		$tplids = Tools::formatids ( $tplids, false );
		$status = 0;
		foreach ($tplids as $tplid) {
			$status += $this->del($tplid,1);
		}
		MyqeeCMS::show_info( Myqee::lang('admin/template.info.delsuccess',count($status)) , true , 'refresh');
	}
	public function del($tplid,$frommore=0){
		if (EDIT_TEMPLATE !== 1){
			MyqeeCMS::show_error('当前设置不允许修改模板！',TRUE);
		}
		Passport::checkallow('template.del');
		$tplid = (int)$tplid;
		if (!($tplid>0))MyqeeCMS::show_error(Myqee::lang('admin/template.error.parametererror') , true);

		$this -> db = Database::instance();

		$tpl = $this -> db -> getwhere('[template]' , array('id'=>$tplid)) -> result_array(false);
		$tpl = $tpl[0];
		if (!$tpl)MyqeeCMS::show_error(Myqee::lang('admin/template.error.notemplateid') , true);

		//模板组文件夹
		$filepath = MYAPPPATH .'views/'. $tpl['group'].'/';
		//完整路径
		$fullfile = $filepath . $tpl['filename'].$tpl['filename_suffix'];

		if (is_file($fullfile))@unlink($fullfile);		//移除文件

		$status = $this -> db -> delete('[template]',array('id'=>$tplid));
		if (count($status)>0){
			if (!$frommore) {
				MyqeeCMS::show_info( Myqee::lang('admin/template.info.delsuccess',count($status)) , true , 'refresh');
			} else {
				return count($status);
			}
			
		}else{
			MyqeeCMS::show_info( Myqee::lang('admin/template.info.nodelete') , true );
		}
	}
	/*
	protected function is_available_tplname($fullfilename){
		$thefile = APPPATH . 'template/' . $fullfilename;
		if (is_file($thefile)){
			return false;
		}else{
			return true;
		}
	}


	*/

	public function inputtpl(){
		if (EDIT_TEMPLATE !== 1){
			MyqeeCMS::show_error('当前设置不允许修改模板！');
		}
		Passport::checkallow('template.input');
		$view = new View('admin/template_input');

		//获取模板组
		$tplgroup = Myqee::config('template.group');
		if (!is_array($tplgroup)){
			$tplgroup = array('default' => '默认模板组');
		}else{
			$tmpgroup = array();
			foreach ($tplgroup as $key => $item){
				$tmpgroup[$key] = $item['name'];
			}
			$tplgroup = $tmpgroup;
		}
		$nowtplgroup = $this -> session -> get('now_tlpgroup');
		if (!$nowtplgroup){
			$nowtplgroup = key($tplgroup);
		}
		$view -> tplgroup = $tplgroup;
		$view -> nowtplgroup = $nowtplgroup;
		$view -> render(TRUE);
	}

	public function input(){
		if (EDIT_TEMPLATE !== 1){
			MyqeeCMS::show_error('当前设置不允许修改模板！',TRUE);
		}
		Passport::checkallow('template.input');
		//上传方式
		$key = $_POST['key'];
		$thetpl = $_POST['template'];

		if (empty($thetpl) && $_FILES['upload']['size']==0){
			MyqeeCMS::show_error(Myqee::lang('admin/template.error.inputtplempty'),true);
		}
		if ($_FILES['upload']['tmp_name']){
			$tmpfile = $_FILES['upload']['tmp_name'];
			if ($_FILES['upload']['size']<5000000){	//只操作5MB以内的文件
				if (!$thetpl = @file_get_contents($_FILES['upload']['tmp_name'])){
					MyqeeCMS::show_error(Myqee::lang('admin/template.error.inputreadfileerror'),true);
				}

				//反解文件
				$thetpl = Tools::info_uncryp($thetpl,$key,true);
			}else{
				MyqeeCMS::show_error(Myqee::lang('admin/template.error.inputerrorsize'),true);
			}
		}else{
			if (strlen($thetpl)<5000000){
				//反解文件
				$thetpl = Tools::info_uncryp($thetpl,$key,true);
			}else{
				MyqeeCMS::show_error(Myqee::lang('admin/template.error.inputerrorsize'),true);
			}
		}
		if ($thetpl === -1){
			MyqeeCMS::show_error(Myqee::lang('admin/template.error.inputtplbyedit'),true);
		}

		if (!is_array($thetpl)){
			MyqeeCMS::show_error(Myqee::lang('admin/template.error.decodeerror'),true);
		}
		if ($_POST['isnewgroup'] !=1 || !($group = preg_replace("/[^a-zA-Z0-9_]+/",'',$_POST['newgroup']))){
			$group = $_POST['group'];
		}
		if (!$group){
			$group = 'default';
		}
		//获取模板组
		$tplgroup = Myqee::config('template.group');
		if (!is_array($tplgroup))$tplgroup = array('default' => array('name'=>'默认模板组'));

		//创建新模板组
		if (!$tplgroup[$group]){
			$this -> _setgroup($group);
		}

		$this -> db = Database::instance();
		$inputerr = 0;
		$inputok = 0;
		foreach ( $thetpl as $item ){
			$data = array(
				'tplname' => $item['tplname'],
				'group' => $group,
				'type' => $item['type'],
				'isuse' => $item['isuse'],
				'myorder' => (int)$item['myorder'],
				'cate' => Tools::formatstr($item['cate']),
				'content' => $item['content'],
				'filename' => $item['filename'],
				'filename_suffix' => $item['filename_suffix'],
				'filemtime' => $item['filemtime'],
			);

			//检测是否已经存在的文件
			$chkwhere = array('group'=>$data['group'],'filename'=>$data['filename'],'filename_suffix'=>$data['filename_suffix']);

			$chktpl = $this -> db -> getwhere('[template]', $chkwhere) ->  result_array(FALSE);
			$chktpl = $chktpl[0];

			if ($chktpl){
				$inputerr += 1;
			}else{
				//模板组文件夹
				$filepath = MYAPPPATH .'views/'. $data['group'].'/';
				//完整路径
				$fullfile = $filepath . $data['filename'].$data['filename_suffix'];

				$status = $this -> db -> insert('[template]',$data);
				if (count($status)){
					//保存文件
					@Tools::create_dir($filepath);
					@file_put_contents($fullfile , $data['isuse']?$data['content']:'');
					@touch($fullfile,$item['filemtime']);
					//输出提示信息
					$inputok += 1;
				}
			}
		}

		$showinfo = Myqee::lang('admin/template.info.inputok',$inputok);
		if ($inputerr>0)$showinfo .= Myqee::lang('admin/template.info.inputerror',$inputerr);
		MyqeeCMS::show_info($showinfo,true,'goback');
		//sleep(20);
	}

	public function grouplist(){
		Passport::checkallow('template.grouplist');
		$view = new View('admin/template_grouplist');

		$view -> set('list' , Myqee::config('template.group'));
		$view -> set('defaultgroup' , Myqee::config('template.default'));
		$view -> render(TRUE);
	}

	/**
	 * 创建新的模板组
	 *
	 * @param string $group
	 * @param string $name
	 */
	protected function _setgroup($group,$groupset = '' , $isdefault = 0 , $delgroup = NULL){
		if (!$groupset['name'])$groupset['name'] = $group;

		//获取模板组
		$template = Myqee::config('template');
		if (!is_array($template['group']))$template['group'] = array('default' => array('name'=>'默认模板组'));

		//创建新模板组
		$template['group'][$group] = array(
			'name'=>$groupset['name'],
			'engine'=>$groupset['engine'],
			'suffix'=>$groupset['suffix'],
			'allsuffix'=>$groupset['allsuffix'],
		);
		if ($isdefault)	$template['default'] = $group;
		if ($delgroup) unset($template['group'][$delgroup]);
		
		//按键名排个序
		ksort($template['group']);
		
		MyqeeCMS::saveconfig('template',$template);
	}

	public function newgroup(){
		$this -> editgroup();
	}

	public function editgroup($thegroup = NULL){
		if (EDIT_TEMPLATE !== 1){
			MyqeeCMS::show_error('当前设置不允许修改模板！');
		}
		if ($thegroup){
			Passport::checkallow('template.groupedit');
		}else{
			Passport::checkallow('template.groupadd');
		}
		$view = new View('admin/template_groupadd');

		$tplconfig = Myqee::config('template');
		if ($thegroup){
			$view -> set('group',$tplconfig['group'][$thegroup]);
			$view -> set('thisgroup',$thegroup);
		}
		
		$engine = Myqee::config('template.engine');
		if (is_array($engine)){
			$engine_set = array();
			foreach ($engine as $key=>$item){
				$engine_set[$key] = $item['name'];
			}
		}else{
			$engine_set = array(''=>'无(直接使用视图)');
		}
		$view -> set('engine_set',$engine_set);
		
		$view -> set('defaultgroup',$tplconfig['default']);
		$view -> render(TRUE);
	}

	public function gropusave($old_id = ''){
		if (EDIT_TEMPLATE !== 1){
			MyqeeCMS::show_error('当前设置不允许修改模板！');
		}
		if (!$old_id){
			Passport::checkallow('template.groupedit');
		}else{
			Passport::checkallow('template.groupadd');
		}
		$old_id = strtolower(preg_replace("/[^a-zA-Z0-9_]+/",'',$old_id));

		$group_id = strtolower(preg_replace("/[^a-zA-Z0-9_]+/",'',$_POST['id']));
		$groupset = array(
			'name' => htmlspecialchars(trim($_POST['name'])),
			'engine' => $_POST['engine'],
			'suffix' => $_POST['suffix'],
			'allsuffix' => $_POST['allsuffix'],
		);
		$isdefault = $_POST['isdefault']?1:0;

		if (!$group_id)MyqeeCMS::show_error(Myqee::lang('admin/template.error.emptygroupname'),true);
		if (!$groupset['name'])$groupset['name'] = $group_id;
		
		$engine = Myqee::config('template.engine');
		if (!$engine[$groupset['engine']]){
			$groupset['engine'] = '';
		}
		if (!$groupset['suffix']){
			$groupset['suffix'] = $engine[$groupset['engine']]['suffix'];
		}
		if (!$groupset['allsuffix']){
			$groupset['allsuffix'] = $engine[$groupset['engine']]['allsuffix'];
		}

		$tplconfig = Myqee::config('template');
		if ($old_id){
			if ($tplconfig['group'][$old_id]){

				if ( $group_id != $old_id ){
					if ( $tplconfig['group'][$group_id] ){
						MyqeeCMS::show_error(Myqee::lang('admin/template.error.contusergroup'),true);
					}

					$this -> db =  Database::instance();
					$this -> db -> update ('[template]',array('group' => $group_id),array('group' => $old_id));
					@rename(MYAPPPATH .'views/'.$old_id.'/' , APPPATH.'template/'.$group_id.'/');

					$deloldid = $old_id;
				}

			}else{
				unset($old_id);
			}
		}
		if (!$old_id){
			if ( $tplconfig['group'][$group_id] ){
				MyqeeCMS::show_error(Myqee::lang('admin/template.error.contusergroup'),true);
			}
		}

		$this -> _setgroup($group_id , $groupset , $isdefault , $deloldid);

		MyqeeCMS::show_info(Myqee::lang('admin/template.info.savegroupok'),true,Myqee::url('template/grouplist'));
	}

	public function delgroup($thegroup){
		if (EDIT_TEMPLATE !== 1){
			MyqeeCMS::show_error('当前设置不允许修改模板！',TRUE);
		}
		Passport::checkallow('template.groupdel',null,true);
		$tplconfig = Myqee::config('template.group');
		if (!$tplconfig[$thegroup]){
			MyqeeCMS::show_error(Myqee::lang('admin/template.error.nothisgroup'),true);
		}

		$this -> db =  Database::instance();
		$this -> db -> delete ('[template]',array('group' => $thegroup));
		Tools::remove_dir(MYAPPPATH .'views/'.$thegroup.'/');

		$this -> _setgroup($thegroup , NULL , 0 , $thegroup);

		MyqeeCMS::show_info(Myqee::lang('admin/template.info.delgroupok'),true,Myqee::url('template/grouplist'));
	}
	
	
	public function renewfiles($tlpid=0){
		Passport::checkallow('template.renewfiles');
		$tlpid = (int)$tlpid;
		//$nowtplgroup = $this -> session -> get('now_tlpgroup');
		//if (!$nowtplgroup)$nowtplgroup = 'default';
		//MyqeeCMS::show_info($nowtplgroup,TRUE);
		
		$this -> db = Database::instance();
		$results = $this -> db -> from('[template]');
		if ($tlpid>0){
			$results = $results -> where ('id',$tlpid);
		}
		
		$results = $results -> get() -> result_array(false);
		
		$save_ok = $save_error = $file_error = 0;
		if (is_array($results)){
			foreach ($results as $item){
				//模板组文件夹
				$filepath = MYAPPPATH .'views/'. $item['group'].'/';
				//完整路径
				$fullfile = $filepath . $item['filename'].$item['filename_suffix'];
				if ($item['filemtime']>0 && file_exists($fullfile) && filemtime($fullfile)>$item['filemtime'] && md5(file_get_contents($fullfile))!=md5($item['content'])){
					$file_error++;
					Myqee::log('error','更新模板文件(ID:'.$item['id'].'):'.$fullfile.'修改时间冲突');
					continue;
				}
				if (@file_put_contents($fullfile , $item['isuse']?$item['content']:'')){
					if($item['filemtime']>0)@touch($fullfile,$item['filemtime']);
					$save_ok ++;
				}else{
					Myqee::log('error','更新模板文件(ID:'.$item['id'].'):'.$fullfile.'失败，可能没有操作权限');
					$save_error ++;
				}
			}
		}
		
		$msg = '执行完毕，共更新文件'.$save_ok.'，执行失败：'.$save_error.'，文件冲突：'.$file_error.'！';
		if ($_GET['type']=='auto'){
			echo '<script>parent.showinfo("template","'.$msg.'");document.location.href="'.ADMIN_IMGPATH.'/admin/block.html";</script>';
		}else{
			MyqeeCMS::show_info($msg,true);
		}
	}
}