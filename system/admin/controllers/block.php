<?php

class Block_Controller_Core extends Controller {
	
	public function __construct() {
		parent::__construct();
		
		$this->session = Passport::chkadmin();
	}
	
	public function index($bclassid = 0) {
		Passport::checkallow('info.block_list');
		$limie = 20;
		$view = new View("admin/block_list_class");
		
		$adminmodel = new Admin_Model;
		$myclass = $adminmodel -> get_allclass_array($bclassid,0,true);
		$view -> set ( 'list' , $myclass );
		if ($bclassid){
			//获取“你现在的位置”的数组，并传递给视图的$location变量
			$view -> set('location',$adminmodel -> get_location_array($bclassid) );
		}
		$view->render(TRUE);
	}
	
	public function siteindex(){
		Passport::checkallow('info.block_list');
		
		$view = new View("admin/block_list_site");
		
		$db = new Database;
		$list = $db -> orderby('myorder','ASC') -> orderby('id','DESC') -> getwhere('[site]',array('isuse'=>1)) -> result_array(false);
		$view -> set ( 'list' , $list );
		
		$view->render(TRUE);
	}
	
	public function custompage($page=1){
		$this->customtype=='customlist' or $this->customtype = 'custompage';
		$page = (int)$page;
		$page>0 or $page=1;
		
		Passport::checkallow('info.block_list');
		
		$view = new View("admin/block_list_".$this->customtype);
		
		$limit = 20;
		
		$db = new Database;
		
		
		$where = array('isuse'=>1,'istpl'=>1);
		$num = $db->count_records('['.$this->customtype.']',$where);
		
		$pagination = new Pagination ( 
			array (
				'uri_segment'=>$this->customtype,
				'total_items' => $num,
				'items_per_page' => $limit 
			)
		);
		
		$view->set('list',$db->where($where)->limit($limit,$pagination->sql_offset)->orderby('id','DESC')->getwhere('['.$this->customtype.']')->result_array(FALSE) );
		$view->set('page',$pagination->render ('digg') );
		
		$view->render(TRUE);
	}
	
	public function customlist($page=1){
		$this -> customtype = 'customlist';
		$this -> custompage($page);
	}
	
	
	public function add(){
		$this -> isadd = true;
		$this -> edit();
	}
	
	public function copy($id=0){
		$this -> iscopy = true;
		$this -> edit($id);
	}
	
	public function edit($id=0){
		if ($id>0 && !$this -> iscopy){
			Passport::checkallow('info.block_edit');
		}else{
			Passport::checkallow('info.block_add');
		}
		
		$view = new View('admin/block_edit');
		if ($id>0){
			$db = new Database();
			$block = $db -> getwhere('[block]',array('id'=>$id)) -> result_array(FALSE);
			$block = $block[0];
			if (!$block){
				MyqeeCMS::show_error('没有找到指定碎片！');
			}
			if ($this->iscopy){
				unset($block['id']);
			}
			$view -> set('block',$block);
		}elseif($this->isadd){
			if ($_GET['type'] && $_GET['no']){
				$block['type'] = $_GET['type'];
				$block['no'] = $_GET['no'];
			}
			$view -> set('block',$block);
		}
		
		$type = array(
			'index'				=> '首页',
			'class_cover'		=> '栏目封面页',
			'class_info'		=> '栏目信息页',
			'class_list'		=> '栏目列表页',
			'class_search'		=> '栏目搜索页',
			'custompage'		=> '自定义页',
			'customlist'		=> '自定义列表页',
			'site_index'		=> '子站点首页',
			'special_cover'		=> '专题封面页',
			'special_list'		=> '专题列表页',
			'special_search'	=> '专题搜索页',
		);
		$view -> set('type',$type);
		$view -> set('field_adv', Tools::json_encode( (array)unserialize($block['advfield']) ) );

		$adminmodel = new Admin_Model();
		$template = $adminmodel -> get_alltemplate('block');
		$view -> set('template',$template);
		
		
		$view -> render(true);
	}
	
	
	public function save($id=0){
		$id = (int)$id;
		$post = $_POST['block'];
		$post['title'] = trim(Tools::formatstr($post['title'],200,0,0,0,0,0));
		if(empty($post['title'])){
			MyqeeCMS::show_error('碎片名称不能空！',true);
		}
		
		if(empty($post['type'])){
			MyqeeCMS::show_error('请选择所属区块！',true);
		}
		if(!preg_match("/^[a-z][a-z0-9_]+$/i",$post['type'])){
			MyqeeCMS::show_error('指定区块不符合要求，只允许数字字母下划线，且首位必须是字母！',true);
		}
		if($post['varname'] && !preg_match("/^[a-z][a-z0-9_]+$/i",$post['varname'])){
			$post['varname'] = 'data';
		}
		
		
		$data = array(
			'title' => $post['title'],
			'isuse' => $post['isuse']==0?0:1,
			'myorder' => (int)$post['myorder'],
			'type' => $post['type'],
			'no' => (int)$post['no'],
			'len' => (int)$post['len'],
			'varname' => $post['varname']?$post['varname']:'data',
			'cache_time' => (int)$post['cache_time'],
			'tpl_id' => (int)$post['tpl_id'],
			'tpl_engie' => $post['tpl_engie'],
			'mydata_id' => (int)$post['mydata_id'],
			'template' => $post['template'],
		);
		
		$db = new Database();
		if ($id>0){
			$olddata = $db -> select('id,type,no,show_type') -> getwhere('[block]',array('id'=>$id))->result_array(false);
			$olddata = $olddata[0];
			if (!$olddata){
				MyqeeCMS::show_error('指定的碎片不存在！',true);
			}
			$show_type = $olddata['show_type'];
		}else{
			//只有添加的时候保存
			$show_type = $data['show_type'] = (int)$post['show_type']>0?(int)$post['show_type']:0;
		}
		
		//显示类型为格式化碎片或自定义碎片时记录高级字段
		if ($show_type>0){
			$adminmodel = new Admin_Model();
			$data['advfield'] = serialize($adminmodel -> set_field_adv($_POST['field']['adv'],true));
		}
			
		$where = array('type'=>$data['type'],'no'=>$data['no']);
		
		if ($data['no']>0){
			if ($id>0){
				$where['id!='] = $id;
			}
			if ($db -> count_records('[block]',$where)){
				MyqeeCMS::show_error('指定的调用ID已存在，请更换调用ID！',true);
			}
		}else{
			//自动创建调用ID
			if ($id>0){
				//保留原来的
				unset($data['no']);
			}else{
				$theno = $db -> select('no') -> from('[block]') -> where($where) -> limit(1) -> get() -> result_array(false);
				$theno = $theno[0];
				if ($theno['no']>0){
					$data['no'] = $theno['no']+1;
				}else{
					$data['no'] = 1;
				}
			}
		}
		
		if ($id>0){
			$count = $db -> update('[block]',$data,array('id'=>$id)) ->count();
		}else{
			$result = $db -> insert('[block]',$data);
			$count = $result -> count();
			$data['id'] = $id = $result -> insert_id();
		}
		
		if ( $count ){
			if ( $olddata && ($data['type']!=$olddata['type']||$data['no']!=$olddata['no']) ){
				//删除旧配置、缓存
				MyqeeCMS::delconfig('block/block_'.$olddata['type'].'_'.$olddata['no']);
				Cache::delete('block_'.$olddata['type'].'_'.$olddata['no']);
			}
			
			MyqeeCMS::saveconfig('block/block_'.$data['type'].'_'.$data['no'],$data);
			MyqeeCMS::show_ok('恭喜，碎片保存成功！',true,Myqee::url('block/mylist'));
		}else{
			MyqeeCMS::show_info('未保存任何数据！',true);
		}
	}
	
	public function del($ids=0){
		$ids = Tools::formatids($ids);
		if (!$ids){
			MyqeeCMS::show_error('请指定要删除的碎片！',true);
		}
		
		$db = new Database();
		$block = $db -> select('type','no','id') -> from('[block]') -> in ('id',$ids) -> get() -> result_array(false);
		
		foreach ($block as $item){
			MyqeeCMS::delconfig('block/block_'.$item['id']);
			//清除缓存
			Cache::delete('block_'.$item['type'].'_'.$item['no']);
		}
		
		$count = $db -> in ('id',$ids) -> delete('[block]') -> count();
		
		MyqeeCMS::show_info('已删除'.$count.'个碎片！',true,'refresh');
	}
	
	
	public function mylist($page=1){
		$page = (int)$page;
		if (!$page>0)$page=1;
		$limit = 20;
		
		$db = new Database();
		
		$num = $db -> count_records('[block]');
		$pagination = new Pagination(
			array(
				'uri_segment'    => 'mylist',
				'total_items'    => $num,
				'items_per_page' => $limit
			)
		);
		
		$list = $db -> orderby('id','DESC') -> limit($limit,$pagination->sql_offset) -> get('[block]') -> result_array(FALSE);
		
		$view = new View('admin/block_list');
		$view -> set('list',$list);
		$view->render(TRUE);
	}
	
	
	public function view_edit($type='cover',$id=0){
		Passport::checkallow('info.block_edit');
		$id = (int)$id;
		if (!$type){
			MyqeeCMS::show_error('缺少参数！');
		}
		if ($id==0){
			$type ='index';
		}
		$typearr = array(
			'index'=>'toindex',
			'cover'=>'class_block_cover',
			'list'=>'class_block_list',
			'content'=>'class_block_content',
			'search'=>'class_block_search',
			'site'=>'siteindex',
			'custompage'=>'tocustompage',
			'customlist'=>'tocustomlist'
		);
		$typearr[$type] or $type = 'index';
		
		$view = new View("admin/block_view_edit");
		$view -> set('mytype',$typearr[$type]);
		$view -> set('type',$type);
		$view -> set('id',$id);
		$view->render(TRUE);
	}
	
	public function view_edit_frame() {
		Passport::checkallow('info.block_edit');
		$type = $_GET['type'];
		$no = (int)$_GET['no'];
		$id = (int)$_GET['id'];
		if ( !(($type&&$no>0)||$id>0) ){
			MyqeeCMS::show_error('缺少参数！',true);
		}
		
		$db = new Database();
		if ($id>0){
			$where = array('id'=>$id);
		}else{
			$where = array('type'=>$type,'no'=>$no);
		}
		$block = $db -> getwhere('[block]',$where) -> result_array(FALSE);
		$block = $block[0];
		
		if (!$block){
			MyqeeCMS::show_info(array(
				'message'=>'没有找到指定碎片，是否立即创建？',
				'handler'=>'function(er){
					if (er!="ok")return;
					goUrl("'.Myqee::url('block/add?type='.$type.'&no='.$no).'","_blank");
				}',
				'btn'=>array(
					array('立即创建','ok'),
					array('取消','cancel')
				)
			),true);
		}
		
		if ($block['show_type']>0){
			//规则的数据信息
			$block['advfield'] = array('usehtml'=>2,'title'=>$block['title'],'adv'=>unserialize($block['advfield']) );
		}else{
			//HTML格式
		}
		
		$view = new View('admin/block_view_edit_frame');
		
		$view -> set('autohtml',$_GET['autohtml']);
		$view -> set('block',$block);
		$view -> set('infoid',$id);
		$view -> render(true);
	}
	

	public function view_edit_save() {
		Passport::checkallow('info.block_edit',true);
		$id = (int)$_POST['id'];
		if (!$id>0){
			echo '<script>alert("缺少参数！")</script>';
		}
		$db = new Database();
		$block = $db -> getwhere('[block]',array('id'=>$id)) -> result_array(FALSE);
		$block = $block[0];
		
		if (!$block){
			echo '<script>alert("没有找到指定碎片！")</script>';
			exit;
		}
		
		if ($block['show_type']=='1'){
			//规则的数据信息
			$content = array();
			$i = 0;
			foreach ($_POST['content'] as $item){
				if ( is_array($item) ){
					$item['title'] = trim($item['title']);
					$item['URL'] = trim($item['URL']);
					if ( !empty($item['title']) && !empty($item['URL']) ){
						$content[$i] = $item;
						$i++;
						if ($block['len']>0 && $i>=$block['len']){
							break;
						}
					}
				}
			}
			$content = serialize($content);
		}elseif ($block['show_type']=='2'){
			$adminmodel = new Admin_Model();
			$content = $adminmodel -> check_postvalue($_POST['content']['adv'], array('usehtml'=>2,'format'=>'serialize','adv'=>unserialize($block['advfield'])) );
		}else{
			//HTML格式
			$content = Tools::formatstr($_POST['content']);
		}
		$status = $db -> update ('[block]',array('content'=>$content),array('id'=>$id)) -> count();
		
		if ($status){
			if ($block['cache_time']>0){
				//清除缓存
				Cache::delete('block_'.$block['type'].'_'.$block['no']);
			}
			
			$msg = '如果被静态页调用，请重新生成对应静态页面！';
			$showfun = 'show_ok';
			if ($_POST['autohtml']){
				//自动生成静态页
				$msg = $this->_auto_tohtml($_POST['autohtml'],(int)$_POST['infoid']);
				if ($msg===true){
					$msg = '并且当前页已重新生成了当前静态页！';
				}else{
					$showfun = 'show_info';
				}
			}
			MyqeeCMS::$showfun('恭喜，碎片信息保存成功！<br/><br/>'.$msg,true);
		}else{
			echo '<script>alert("信息未修改，没有保存任何信息！")</script>';
		}
	}
	
	protected function _auto_tohtml($type,$id=0){
		$typearr = array(
			'index'			=> array('dohtml_index','您没有执行生成首页的权限！'),
			'cover'			=> array('dohtml_class','您没有执行生成栏目页的权限！'),
			'content'		=> array('dohtml_info','您没有执行生成内容页的权限！'),
			'site'			=> array('dohtml_siteindex','您没有执行生成子站点首页的权限！'),
			'custompage'	=> array('dohtml_custompage','您没有执行生成子自定义页的权限！'),
		);
		if (!$typearr[$type]){
			return false;
		}
		list ($allow,$err) = $typearr[$type];
		
		if ( Passport::getisallow('task.'.$allow) ){
			switch ($type){
				case 'index':
					$result = Tohtml::toindex();
					break;
				case 'site':
					$result = Tohtml::tositeindex($id);
					break;
				case 'custompage':
					$result = Tohtml::tocustompage($id);
					break;
				case 'cover':
					$result = Tohtml::toclass($id);
					break;
				default:
					return '';
			}
			if ($result['ok']){
				return true;
			}elseif($result['error']){
				return $result['error'];
			}else{
				return '执行生成首页报错，请检查页面模板！';
			}
		}else{
			return $err;
		}
		/*
		switch ($type){
			case 'index':
				//重新生成首页
				if (Passport::getisallow('task.dohtml_index')){
					$result = Tohtml::toindex(false);
					if ($result['ok']){
						return true;
					}elseif($result['error']){
						return $result['error'];
					}else{
						return '执行生成首页报错，请检查页面模板！';
					}
				}else{
					return '没有执行生成首页的权限！';
				}
				break;
				
			case 'site':
				//重新生成站点首页
				if (!$id>0){
					return '页面执行时异常：缺少生成自定义参数！';
				}
				if (Passport::getisallow('task.dohtml_siteindex')){
					$result = Tohtml::tositeindex($id);
					if ($result['ok']){
						return true;
					}elseif($result['error']){
						return $result['error'];
					}else{
						return '执行生成站点首页面错误，请检查页面模板！';
					}
				}else{
					return '没有执行生成站点首页的权限！';
				}
				break;
				
			case 'custompage':
				//重新生成自定义页
				if (!$id>0){
					return '页面执行时异常：缺少生成自定义参数！';
				}
				if (Passport::getisallow('task.dohtml_custompage')){
					$result = Tohtml::tocustompage($id);
					if ($result['ok']){
						return true;
					}elseif($result['error']){
						return $result['error'];
					}else{
						return '执行生成自定义页面错误，请检查页面模板！';
					}
				}else{
					return '没有执行生成自定义页的权限！';
				}
				break;
				
			default:
				return '';
		}*/
	}
}
?>