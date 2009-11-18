<?php
class __tohtml_Controller_Core extends Controller {
	public $myclass = array();

	function __construct ()
	{
		defined('IN_ADMINMODEL') or die('No direct script access.');
		if (isset($_GET['_timeline']) && isset($_GET['_code'])) {
			if (! ($_GET['_timeline'] > 0) || $_SERVER['REQUEST_TIME'] - $_GET['_timeline'] > 3600) {
				Myqee::show_404();
			}
			$encryptionkey = Myqee::config('encryption.default.key');
			if (md5($encryptionkey . '__' . $_GET['_timeline']) != $_GET['_code']) {
				echo $_GET['_timeline'];
				Myqee::show_404();
			}
		}
		else {
			Myqee::show_404();
		}
		header('Cache-Control:no-store');
		header('Pragrma:no-cache');
		header('Expires:0');
		
		if ($_GET['_editblock']=='yes'){
			$this->editblock = true;
			define('ADMIN_EDIT_BLOCK',true);
		}else{
			$this->editblock = false;
			define('ADMIN_EDIT_BLOCK',false);
		}
	}

	public function toindexpage ()
	{
		if ($this->editblock){
			return $this -> _echo_editblock_html(Myqee::config('core.index_template'));
		}
		if (!$tpl=Myqee::config('core.index_template')){
			echo '{"error":"没有设置首页模板！"}';
			return false;
		}
		if (!$file=Myqee::config('core.index_filename')){
			echo '{"error":"没有设置首页文件！"}';
			return false;
		}
		$runmassage = Createhtml::instance() -> createhtml($tpl,$file );
		
		if ($runmassage === true) {
			echo '{"ok":1}';
		}
		else {
			echo $runmassage;
		}
	}

	public function tositeindexpage ()
	{
		$where = array('isuse'=>1);
		if ($siteid = (int)$_GET['_siteid']){
			$where['id'] = $siteid;
		}
		$mydata = Myqee::db() -> from('[site]') -> where($where) -> get() -> result_array(FALSE);
		$dook = $doerr = 0;
		foreach ($mydata as $item) {
			$config = unserialize($item['config']);
			if ($config['indexpage'] && $config['indexpage']['isuse']) {
				$indexset = $config['indexpage'];
				if ($this->editblock){
					if (!$indexset['tpl']){
						echo '{"error":"指定的站点没有设置站点模板！"}';
						return false;
					}
					//编辑碎片模式
					return $this -> _echo_editblock_html($indexset['tpl']);
				}
				if (!$indexset['tpl'])continue;
				if ($indexset['filepath'][0] != '/' && ! preg_match("/^[a-z]:(.+)$/i", $indexset['filepath'])) {
					//WWWROOR目录内
					$indexset['filepath'] = WWWROOT . trim($indexset['filepath'], './') . '/';
					if (! is_dir($indexset['filepath'])) {
						//创建目录
						Tools::create_dir($indexset['filepath']);
					}
					if (empty($indexset['filename'])) {
						$indexset['filename'] = Myqee::config('core.index_filename');
						$indexset['filename'] or $indexset['filename'] = 'index.html';
					}
					$r = Createhtml::instance() -> createhtml($indexset['tpl'], $indexset['filename'], null, null, $indexset['filepath']);
					if ($r === TRUE) {
						$dook ++;
					}
					else {
						$doerr ++;
					}
				}
			}elseif ($this->editblock){
				echo '{"error":"指定的站点没有启用或没有设定站点首页！"}';
				return false;
			}
		}
		if ($dook > 0 && $doerr == 0) {
			echo '{"ok":"'.$dook.'"}';
		}
		else {
			echo '{"ok":"'.$dook.'","err":"'.$doerr.'"}';
		}
	}

	public function tocustompage ()
	{
		$mydata = Myqee::db() -> from('[custompage]') -> where('isuse', 1);
		if ($_GET['_theid']) {
			$ids = Tools::formatids($_GET['_theid']);
			if (count($ids)==1){
				$mydata = $mydata -> where('id', $ids[0]);
			}else{
				$mydata = $mydata -> in('id', $ids);
			}
		}
		$mydata = $mydata -> get() -> result_array(FALSE);
		$tplset = array();
		$donum = count($mydata);
		$dook = $doerr = 0;
		if ($donum==0 && $this->editblock){
			echo '{"error":"没有发现指定自定义页！"}';
			exit;
		}
		foreach ($mydata as $item) {
			$filepath = $item['filepath'];
			$data = array(
				'id' => $item['id'],
			);
			$data[$item['title_flag'] ? $item['title_flag'] : 'title'] = $item['pagetitle'];
			$data[$item['keyword_flag'] ? $item['keyword_flag'] : 'keyword'] = $item['keyword'];
			$data[$item['pagedesc_flag'] ? $item['pagedesc_flag'] : 'description'] = $item['pagedesc'];
			$data[$item['content_flag'] ? $item['content_flag'] : 'content'] = $item['content'];
			$item['param'] = unserialize($item['param']);
			if (is_array($item['param']) && count($item['param'])) {
				foreach ($item['param'] as $item2) {
					if ($item2['flag']) $data[$item2['flag']] = $item2['value'];
				}
			}
			if ($this->editblock){
				//编辑碎片模式
				if (!$item['tplid']){
					echo '{"error":"选定的自定义页没有设置模板！"}';
					exit;
				}
				return $this -> _echo_editblock_html($item['tplid'],$data);
			}
			if (!($item['tplid'] > 0)) {
				continue;
			}
			$file = $filepath . '/' . $item['filename'] . $item['filename_suffix'];
			if (Createhtml::instance() -> createhtml($item['tplid'], $file, $data) === true) {
				$dook++;
			}
			else {
				$doerr++;
			}
		}
		if ($dook == 0 && $doerr == 0) {
			echo '{"error":"未生成任何页面！"}';
			exit;
		}
		elseif ($donum > 0 && $donum == $dook && $doerr == 0) {
			if ($donum == 1) {
				$msg = '指定自定义页生成成功！';
			}
			else {
				$msg = '全部自定义页生成成功！';
			}
		}
		else {
			$msg = '成功生成' . $dook . '页面，生成失败：' . $doerr;
		}
		echo '{"ok":"'.$msg.'"}';
	}
	
	/**
	 * 生成自定义列表
	 * @return unknown_type
	 */
	public function tocustomlist (){
		
	}
	

	public function toinfo_byid ()
	{
		$this -> toinfo_byclassid();
	}

	public function toinfo_byclassid ()
	{
		$offset = $_GET['_offset'];
		$offset > 0 or $offset = 0;
		$limit = $_GET['_limit'];
		($limit > 0 and $limit <= 1000) or $limit = 100;
		$allclassid = Tools::formatids($_GET['_allclassid'], false);
		global $nowclassid;
		$nowclassid = (int) $_GET['_nowclassid'];
		if (! ($nowclassid > 0) || in_array($nowclassid, $allclassid)) {
			$nowclassid = $allclassid[0];
		}
		$this -> myclass[$nowclassid] = Myqee::myclass($nowclassid);
		list($database,$tablename) = explode('/',$this -> myclass[$nowclassid]['dbname'],2);
		$_db = Database::instance($database);
		if (! $this -> myclass[$nowclassid]) {
			//栏目不存在，取消执行
			$this -> _gonextpage_byclassid($nowclassid, $allclassid, '栏目不存在，取消执行');
		}
		if (! $this -> myclass[$nowclassid]['iscontent']) {
			//栏目未提供信息录入功能，取消执行
			$this -> _gonextpage_byclassid($nowclassid, $allclassid, '栏目未提供信息录入功能，取消执行');
		}
		if ($this -> myclass[$nowclassid]['isnothtml']) {
			//栏目为动态栏目，取消执行
			$this -> _gonextpage_byclassid($nowclassid, $allclassid, '栏目为动态栏目，取消执行');
		}
		if ($this -> myclass[$nowclassid]['content_tohtml']) {
			//栏目为使用动态内容输出，取消执行
			$this -> _gonextpage_byclassid($nowclassid, $allclassid, '栏目为使用动态内容输出，取消执行');
		}
		if (! $this -> myclass[$nowclassid]['dbname']) {
			//栏目数据表信息缺失，取消执行
			$this -> _gonextpage_byclassid($nowclassid, $allclassid, '栏目数据表信息缺失，取消执行');
		}
		//读取数据表配置
		$this -> dbconfig = Myqee::config('db/' . $this -> myclass[$nowclassid]['dbname']);
		if (! $this -> dbconfig['sys_field']['class_id']) {
			//数据表不存在栏目ID字段，取消执行
			$this -> _gonextpage_byclassid($nowclassid, $allclassid, '数据表不存在栏目ID字段，取消执行');
		}
		if (! $_db -> table_exists($tablename)) {
			//数据表不存在，取消执行
			$this -> _gonextpage_byclassid($nowclassid, $allclassid, '数据表不存在，取消执行');
		}
		$this -> modelconfig = Myqee::config('model/model_' . $this -> myclass[$nowclassid]['modelid']);
		$where = array(
			$this -> dbconfig['sys_field']['class_id'] => $nowclassid
		);
		if ($_GET['_id'] && $this -> dbconfig['sys_field']['id']) {
			$where[$this -> dbconfig['sys_field']['id']] = (int) $_GET['_id'];
		}
		
		//读取信息数量
		$info_count = $this -> myclass[$nowclassid]['info_count'] = $_db -> count_records($tablename, $where);
		$tohtml_ok = 0;
		$tohtml_error = 0;
		if ($offset < $info_count) {
			$selectwhere = $this -> modelconfig['field_set']['content'];
			//加入必要的查询字段
			$this -> dbconfig['sys_field']['id'] and $selectwhere[$this -> dbconfig['sys_field']['id']] = $this -> dbconfig['sys_field']['id'];
			$this -> dbconfig['sys_field']['class_id'] and $selectwhere[$this -> dbconfig['sys_field']['class_id']] = $this -> dbconfig['sys_field']['class_id'];
			$this -> dbconfig['sys_field']['class_name'] and $selectwhere[$this -> dbconfig['sys_field']['class_name']] = $this -> dbconfig['sys_field']['class_name'];
			$this -> dbconfig['sys_field']['template_id'] and $selectwhere[$this -> dbconfig['sys_field']['template_id']] = $this -> dbconfig['sys_field']['template_id'];
			$this -> dbconfig['sys_field']['filename'] and $selectwhere[$this -> dbconfig['sys_field']['filename']] = $this -> dbconfig['sys_field']['filename'];
			$this -> dbconfig['sys_field']['filepath'] and $selectwhere[$this -> dbconfig['sys_field']['filepath']] = $this -> dbconfig['sys_field']['filepath'];
			//预处理，操作模型、数据表设置
			$dbset = array_merge($this -> dbconfig['edit'], $this -> modelconfig['dbset']); //合并数据表，模型配置
			$pagehtml_field = '';
			if (count($dbset) && is_array($this -> modelconfig['field_set']['content'])) {
				$dbset = array_intersect_key($dbset, $this -> modelconfig['field_set']['content']); //计算KEY的交集，排除不在内容中显示的字段
				foreach ($dbset as $k => $v) {
					if ($v['type'] == 'checkbox') {
						//复选框需要进行数据的转换操作
						$checkbox_field[$k] = $k;
					}
					if ($v['type'] == 'pagehtmlarea' && ! $pagehtml_field) {
						//复选框需要进行数据的转换操作
						$pagehtml_field = $k;
					}
				}
			}
			//处理扩展表
			$sontables = array();//副表
			foreach ($selectwhere as $key=>$val) {
				if (substr($val,0,1) == '_') {
					$sontables[] = substr($val,1);
					unset($selectwhere[$key]);
				}
			}
			
			$allinfo = $_db -> select($selectwhere) -> getwhere($tablename, $where, $limit, $offset) -> result_array(FALSE);
			//处理扩展表的信息
			if (!empty($sontables) || is_array($this->dbconfig['model']['relationfield'])) {
				$this->_addRelationInfo ($allinfo,$selectwhere,$sontables,'content');
			}
			
			//
			foreach ($allinfo as $theinfo) {
				//处理复选框
				if (count($checkbox_field)) {
					foreach ($checkbox_field as $v) {
						$theinfo[$v] = explode('|', trim($theinfo[$v], '|'));
					}
				}
				//获取信息存放路径
				$thefile = Createhtml::instance() -> getinfopath($nowclassid, $theinfo);
				if ($_GET['_noretohtml'] == 1 && $thefile && is_file(WWWROOT . $thefile)) {
					continue;
				}
				if ($this -> dbconfig['sys_field']['template_id'] && $theinfo[$this -> dbconfig['sys_field']['template_id']]) {
					$template_id = $theinfo[$this -> dbconfig['sys_field']['template_id']];
				}
				if (! $template_id) {
					$template_id = $this -> myclass[$nowclassid]['content_tplid'];
				}
				if (! $template_id) continue;
				$thedata = $this -> _get_content_data($theinfo);
				if ($pagehtml_field) {
					//带有分页功能的页面
					$title = $theinfo[$this -> dbconfig['sys_field']['title']];
					$theinfo_pageinfo = Myhtml::get_title_info_array($theinfo[$pagehtml_field]);
					$allpage = count($theinfo_pageinfo['info']);
					$path_parts = pathinfo($thefile);
					$basenameWE = substr($path_parts['basename'], 0, - 1 - strlen($path_parts["extension"]));
					if ($allpage > 1) {
						$theurl = Createhtml::instance() -> getinfourl($nowclassid, $theinfo, $thefile);
						$url_path_parts = pathinfo($theurl);
						$url_basenameWE = substr($url_path_parts['basename'], 0, - 1 - strlen($url_path_parts['extension']));
						$urlstring = $url_path_parts['dirname'] . '/' . $url_basenameWE . '_{{page}}.' . $url_path_parts['extension'];
					}
					for ($key = 0; $key < $allpage; $key ++) {
						$thedata['info'][$pagehtml_field] = $theinfo_pageinfo['info'][$key];
						$thedata['info'][$this -> dbconfig['sys_field']['title']] = $title . ($theinfo_pageinfo['title'][$key] ? ' - ' . $theinfo_pageinfo['title'][$key] : ($key > 0 ? ' - ' . ($key + 1) : ''));
						if ($key == 0) {
							$thepagefile = $thefile;
						}
						else {
							$thepagefile = $path_parts['dirname'] . '/' . $basenameWE . '_' . ($key + 1) . '.' . $path_parts['extension'];
						}
						if ($allpage > 1) $thedata['page'] = Myhtml::page($key + 1, $allpage, $urlstring, null, '_{{page}}');
						
						if (($msg = Createhtml::instance() -> createhtml($template_id, $thepagefile, $thedata, 'info')) === true) {
							$tohtml_ok += 1;
						}
						else {
							$tohtml_error += 1;
						}
					}
				}
				else {
					if (($msg = Createhtml::instance() -> createhtml($template_id, $thefile, $thedata, 'info')) === true) {
						$tohtml_ok += 1;
					}
					else {
						$tohtml_error += 1;
					}
				}
			}
		}
		$this -> _show_json_byclassid(array(
			'allclassid' => $allclassid , 
			'nowclassid' => $nowclassid , 
			'offset' => $offset , 
			'limit' => $limit , 
			'tohtml_ok' => $tohtml_ok , 
			'tohtml_error' => $tohtml_error , 
			'info_count' => $info_count
		));
	}

	/**
	 * 生成专辑页面
	 * @return 
	 */
	public function tospecial_byspecialid (){
		$offset = $_GET['_offset'];
		$offset > 0 or $offset = 0;
		$limit = $_GET['_limit'];
		($limit > 0 and $limit <= 1000) or $limit = 100;
		$page = ceil($offset/$limit);
		$page>0 or $page=1;
		$db = Myqee::db();
		$allspecialid = Tools::formatids($_GET['_allspecialid'], false);
		$nowspecialid = (int) $_GET['_nowspecialid'];
		
		if (! ($nowspecialid > 0) || in_array($nowspecialid, $allspecialid)) {
			$nowspecialid = $allspecialid[0];
		}
		
		$tmp = $db -> getwhere ('[special]',array('sid'=>$nowspecialid))->result_array(false);
		$this->specialinfo = $tmp[0];
		
		if (! $this->specialinfo) {
			//专题不存在，取消执行
			$this -> _gonextpage_byspecialid($nowspecialid, $allspecialid, '专题不存在，取消执行');
		}
		if ($this->specialinfo['isnothtml'] || !($this->specialinfo['islist'] && !$this->specialinfo['list_tohtml'] || $this->specialinfo['iscover'] && !$this->specialinfo['cover_tohtml'])) {
			//专题为动态专题，取消执行
			$this -> _gonextpage_byspecialid($nowspecialid, $allspecialid, '专题为动态专题，取消执行');
		}
		
		
		//读取信息数量
		$info_count = $this->specialinfo['info_count'] = $db-> where (array('sid'=>$nowspecialid))->count_records('[special_info]');
		$tohtml_ok = 0;
		$tohtml_error = 0;
		//专题如果有列表就不能有封面
		if ($this->specialinfo['islist']) {
			$template_id = $this->specialinfo['list_tplid'];
		}else{
			$template_id = $this->specialinfo['cover_tplid'];
		}
		
		if (! $template_id) {
			//数据表不存在，取消执行
			$this -> _gonextpage_byspecialid($nowspecialid, $allspecialid, '列表模板不存在，取消执行', 'speciallist');
		}
		
		if ($this->specialinfo['islist']) {
			//有列表页专题
			$list_pernum = $this->specialinfo['list_pernum'];
			$list_pernum > 0 or $list_pernum = 20; //专题每页显示数
			$limit = $limit * $list_pernum; //数据库查询分页，*$limit_class是用于一次性读取出来
			$offset = ($page - 1) * $limit; //计算offset
			//设置最大页数
			if ($this->specialinfo['list_count'] >0) {
				$info_count = min($info_count,$this->specialinfo['list_count']*$this->specialinfo['list_pernum']);
			}
			if ($offset < $info_count) {
				$allinfo = $db->orderby(array($this->specialinfo['list_byfield']=>$this->specialinfo['list_orderby'])) -> getwhere ('[special_info]',array('sid'=>$nowspecialid),$limit,$offset)->result_array(false);
				$allinfo = array_chunk($allinfo, $list_pernum); //按专题设置将所有数据分割开
				$thefile = $this->specialinfo['filepath'] . '/';
				$this->specialinfo['list_filename'] or $this->specialinfo['list_filename'] = 'list{{page}}.html';
				if (stripos($this->specialinfo['list_filename'], '{{page}}') !== false) {
					$thefile .= $this->specialinfo['list_filename'];
				} else {
					$tmpfilename = explode('.', $this->specialinfo['list_filename']);
					if (count($tmpfilename) > 1) {
						$tmpf = array_pop($tmpfilename);
						$thefile .= join('.', $tmpfilename) . '{{page}}.' . $tmpf;
					} else {
						$thefile .= '{{page}}_' . $this->specialinfo['list_filename'];
					}
				}
				$listpage = ceil(min($limit, $info_count) / $list_pernum);
				for ($i = 0; $i < $listpage; $i ++) {
					//分页地址字符串
					$specialurlstr = $this->specialinfo['filepath'] . '/'.$this->specialinfo['list_filename'];
					//待传入模板的数据
					$thedata = $this->_get_special_data($allinfo[$i],$i+1,$info_count,$list_pernum,$specialurlstr);
					if ($i == 0) {
						$cover_tolistpage = $this->specialinfo['filepath']."/index.html";
						if (($msg = Createhtml::instance() -> createhtml($template_id, $cover_tolistpage, $thedata,'list')) === true) {
							$tohtml_ok += 1;
						} else {
							$tohtml_error += 1;
						}
					}
					//$tmp_classinfo[$i];
					if (($msg = Createhtml::instance() -> createhtml($template_id, str_replace('{{page}}', ($i + 1), $thefile), $thedata,'list')) === true) {
						$tohtml_ok += 1;
					} else {
						$tohtml_error += 1;
					}
				}
			}
		} else {
			$cover_tolistpage = $this->specialinfo['filepath'].'/'.$this->specialinfo['cover_filename'];
			$tmp = $db->orderby(array('myorder'=>'asc','sid'=>'asc')) -> get('[special_info]')->result_array(false);
			$thedata = array('list'=>$tmp);
			$info_count = count($thedata);
			$limit = $info_count;
			$list_pernum = $info_count;
			$offset = $info_count;
			if (($msg = Createhtml::instance() -> createhtml($template_id, $cover_tolistpage, $thedata)) === true) {
				$tohtml_ok += 1;
			} else {
				$tohtml_error += 1;
			}
		}
		$this -> _show_json_byspecialid(
			array(
				'allspecialid' 	=> $allspecialid , 
				'nowspecialid' 	=> $nowspecialid , 
				'offset' 		=> $offset , 
				'limit' 		=> $limit / $list_pernum , 
				'tohtml_ok' 	=> $tohtml_ok , 
				'tohtml_error' 	=> $tohtml_error , 
				'info_count' 	=> ceil($info_count / $list_pernum)
			),
			'speciallist'
		);
	}
	protected function _gonextpage_byclassid ($nowclassid, $allclassid, $msg = '', $type = 'info')
	{
		$newallclassid = array();
		foreach ($allclassid as $theclassid) {
			if ($theclassid != $nowclassid) {
				$newallclassid[] = $theclassid;
			}
		}
		$outinfo = array(
			'ok' => true,
			'docancel' => true , 
			'errorinfo' => $msg , 
			'thisdoingok' => true , 
			'thedoingpage' => 0 , 
			'allcount' => 0 , 
			'classid' => $nowclassid , 
			'runtime' => '<font title="' . $msg . '">已取消</font>'
		);
		if (count($newallclassid) == 0) {
			//所有任务结束，输出结果
			$outinfo['alldook'] = true;
			@header('Connection: close');
		}
		else {
			$limit = $_GET['_limit'];
			($limit > 0 and $limit <= 1000) or $limit = 100;
			$outinfo['nexturl'] = _get_tohtmlurl($type == 'class' ? 'toclass_byclassid' : 'toinfo_byclassid', Myqee::config('encryption.default.key'), 'allclassid=' . join(',', $newallclassid) . '&limit=' . $limit);
		}
		echo Tools::json_encode($outinfo);
		exit();
	}
	
	protected function _gonextpage_byspecialid ($nowspecialid, $allspecialid, $msg = '', $type = 'speciallist')
	{
		
		$newallspecialid = array();
		foreach ($allspecialid as $thespecialid) {
			if ($thespecialid != $nowspecialid) {
				$newallspecialid[] = $thespecialid;
			}
		}
		$outinfo = array(
			'ok' => true,
			'docancel' => true , 
			'errorinfo' => $msg , 
			'thisdoingok' => true , 
			'thedoingpage' => 0 , 
			'allcount' => 0 , 
			'specialid' => $nowspecialid , 
			'runtime' => '<font title="' . $msg . '">已取消</font>'
		);
		if (count($allspecialid) == 0) {
			//所有任务结束，输出结果
			$outinfo['alldook'] = true;
			@header('Connection: close');
		} else {
			$limit = $_GET['_limit'];
			($limit > 0 and $limit <= 1000) or $limit = 100;
			$type = empty($type_map[$type]) ? 'tospeciallistpage' : $type_map[$type];
			$outinfo['nexturl'] = _get_tohtmlurl('tospecial_byspecialid', Myqee::config('encryption.default.key'), 'allspecialid=' . join(',', $newallspecialid) . '&limit=' . $limit);
		}
		echo Tools::json_encode($outinfo);
		exit();
	}
	
	protected function _show_json_byspecialid ($theinfo)
	{
		extract($theinfo);
		//print_r($theinfo);
		$outinfo = array(
			'doinfo' => '生成专题：[' . $this -> specialinfo['title'] . '](id:' . $nowspecialid . ')第' . ($offset + 1) . '-' . ($offset + $limit) . '条信息' , 
			'allcount' => $info_count , 
			'dook' => $tohtml_ok , 
			'doerror' => $tohtml_error , 
			'dotime' => date("Y-m-d H:i:s") , 
			'specialid' => $nowspecialid
		);
		if ($offset + $limit >= $info_count) {
			//当前栏目信息已到结尾，转到下一页
			$newallspecialid = array();
			foreach ($allspecialid as $thespecialid) {
				if ($thespecialid != $nowspecialid) {
					$newallspecialid[] = $thespecialid;
				}
			}
			$allspecialid = $newallspecialid;
			$outinfo['thisdoingok'] = true;
			$outinfo['thedoingpage'] = $info_count;
		}
		else {
			$outinfo['thedoingpage'] = $offset + $limit;
		}
		if (count($allspecialid) == 0) {
			//所有任务结束，输出结果
			$outinfo['alldook'] = true;
			@header('Connection: close');
		}
		else {
			if ($offset + $limit < $info_count) {
				$newoffset = $offset + $limit;
				$nextspecial = $this->specialinfo;
			}
			else {
				$newoffset = 0;
				$tmp = Myqee::db() -> getwhere ('[special]',array('sid'=>$nowspecialid))->result_array(false);
				$nextspecial = $tmp[0];
			}
			$outinfo['nexturl'] = _get_tohtmlurl('tospecial_byspecialid', Myqee::config('encryption.default.key'), '_allspecialid=' . join(',', $allspecialid) . '&_limit=' . $limit . '&_offset=' . $newoffset);
			$outinfo['nextdoing'] = '生成专题：[' . $nextspecial['title'] . '](id:' . $allspecialid[0] . ')第' . ($newoffset + 1) . '-' . ($newoffset + $limit) . '条信息';
		}
		$outinfo['runtime'] = Myqee::runtime();
		echo Tools::json_encode($outinfo);
	}
	
	protected function _show_json_byclassid ($theinfo, $type = 'info')
	{
		extract($theinfo);
		//print_r($theinfo);
		$outinfo = array(
			'doinfo' => '生成栏目：[' . $this -> myclass[$nowclassid]['classname'] . '](id:' . $nowclassid . ')第' . ($offset + 1) . '-' . ($offset + $limit) . ($type == 'class' ? '页列表' : '条信息') , 
			'allcount' => $info_count , 
			'dook' => $tohtml_ok , 
			'doerror' => $tohtml_error , 
			'dotime' => date("Y-m-d H:i:s") , 
			'classid' => $nowclassid
		);
		if ($offset + $limit >= $info_count) {
			//当前栏目信息已到结尾，转到下一页
			$newallclassid = array();
			foreach ($allclassid as $theclassid) {
				if ($theclassid != $nowclassid) {
					$newallclassid[] = $theclassid;
				}
			}
			$allclassid = $newallclassid;
			$outinfo['thisdoingok'] = true;
			$outinfo['thedoingpage'] = $info_count;
		}
		else {
			$outinfo['thedoingpage'] = $offset + $limit;
		}
		if (count($allclassid) == 0) {
			//所有任务结束，输出结果
			$outinfo['alldook'] = true;
			@header('Connection: close');
		}
		else {
			if ($offset + $limit < $info_count) {
				$newoffset = $offset + $limit;
				$nextclass = $this -> myclass[$nowclassid];
			}
			else {
				$newoffset = 0;
				$nextclass = Myqee::myclass($allclassid[0]);
			}
			$outinfo['nexturl'] = _get_tohtmlurl($type == 'class' ? 'toclass_byclassid' : 'toinfo_byclassid', Myqee::config('encryption.default.key'), '_allclassid=' . join(',', $allclassid) . '&_limit=' . $limit . '&_offset=' . $newoffset);
			$outinfo['nextdoing'] = '生成栏目：[' . $nextclass['classname'] . '](id:' . $allclassid[0] . ')第' . ($newoffset + 1) . '-' . ($newoffset + $limit) . '条信息';
		}
		$outinfo['runtime'] = Myqee::runtime();
		echo Tools::json_encode($outinfo);
	}

	public function toclass_byclassid ()
	{
		$offset = (int)$_GET['_offset'];
		$offset >= 0 or $offset = 0;
		$limit = (int)$_GET['_limit'];
		($limit > 0 and $limit <= 1000) or $limit = 100;
		$page = ceil($offset/$limit);
		$page>0 or $page=1;
		$allclassid = Tools::formatids($_GET['_allclassid'], false);
		global $nowclassid;
		$nowclassid = (int) $_GET['_nowclassid'];
		if (! ($nowclassid > 0) || in_array($nowclassid, $allclassid)) {
			$nowclassid = $allclassid[0];
		}
		$this -> myclass[$nowclassid] = Myqee::myclass($nowclassid);
		if (! $this -> myclass[$nowclassid]) {
			//栏目不存在，取消执行
			$this -> _gonextpage_byclassid($nowclassid, $allclassid, '栏目不存在，取消执行', 'class');
		}
		
		if ($this -> myclass[$nowclassid]['isnothtml']) {
			//栏目为动态栏目，取消执行
			$this -> _gonextpage_byclassid($nowclassid, $allclassid, '栏目为动态栏目，取消执行', 'class');
		}
		if ($this -> myclass[$nowclassid]['cover_tohtml'] && $this -> myclass[$nowclassid]['list_tohtml']) {
			//栏目为动态列表栏目，取消执行
			$this -> _gonextpage_byclassid($nowclassid, $allclassid, '栏目为动态列表栏目，取消执行', 'class');
		}
		if (! $this -> myclass[$nowclassid]['dbname']) {
			//栏目数据表信息缺失，取消执行
			$this -> _gonextpage_byclassid($nowclassid, $allclassid, '栏目数据表信息缺失，取消执行', 'class');
		}
		//读取数据表配置
		$this -> dbconfig = Myqee::config('db/' . $this -> myclass[$nowclassid]['dbname']);
		if (! $this -> dbconfig['sys_field']['class_id']) {
			//数据表不存在栏目ID字段，取消执行
			$this -> _gonextpage_byclassid($nowclassid, $allclassid, '数据表不存在栏目ID字段，取消执行', 'class');
		}
		$database = $this -> dbconfig['database'];
		$tablename = $this -> dbconfig['tablename'];
		$_db = Database::instance($database);
		if (! $_db -> table_exists($tablename)) {
			//数据表不存在，取消执行
			$this -> _gonextpage_byclassid($nowclassid, $allclassid, '数据表“'.$tablename.'”不存在，取消执行', 'class');
		}
		$this -> modelconfig = Myqee::config('model/model_' . $this -> myclass[$nowclassid]['modelid']);
		//where
		//list_nosonclass==0本栏目及子栏目
		//list_nosonclass==1本栏目
		//list_nosonclass==2仅子栏目
		if ($this -> myclass[$nowclassid]['list_nosonclass'] == 1) {
			$sqlwhere = array(
				'type' => 'where' , 
				'value' => $nowclassid
			);
		}else {
			$sonclassid = '';
			if ($this -> myclass[$nowclassid]['list_nosonclass'] == 2) {
				$sqlwhere = array(
					'type' => 'in' , 
					'value' => explode('|', trim($this -> myclass[$nowclassid]['sonclass'], '| '))
				);
			}else {
				$sqlwhere = array(
					'type' => 'in' , 
					'value' => explode('|', trim($nowclassid . $this -> myclass[$nowclassid]['sonclass'], '| '))
				);
			}
		}
		if ($this -> dbconfig['sys_field']['isshow']) {
			$where = array(
				$this -> dbconfig['sys_field']['isshow'] => 1
			);
		}
		
		//读取信息数量
		$info_count = $_db;
		if (count($where))$info_count = $info_count -> where($where);
		if ($sqlwhere['value']){
			$info_count = $info_count -> $sqlwhere['type']($this -> dbconfig['sys_field']['class_id'], $sqlwhere['value']);
		}
		$info_count = $info_count -> count_records($tablename);
		$this -> myclass[$nowclassid]['info_count'] = $info_count;
		
		$tohtml_ok = 0;
		$tohtml_error = 0;
		//有列表页栏目
		$limit_class = $this -> myclass[$nowclassid]['list_pernum'];
		$limit_class > 0 or $limit_class = 20; //栏目每页显示数
		$limit = $limit * $limit_class; //数据库查询分页，*$limit_class是用于一次性读取出来
		$offset = ($page - 1) * $limit; //计算offset
		//只有在生成第一页的时候才执行
		if ($offset == 0) {
			//栏目设置使用栏目封面
			$defaultpage = $this -> myclass[$nowclassid]['cover_filename'] ? $this -> myclass[$nowclassid]['cover_filename'] : 'index.html';
			$defaultpage = $this -> myclass[$nowclassid]['classpath'] . '/' . $defaultpage;
			//生成栏目页
			if ($this -> myclass[$nowclassid]['iscover'] && $this -> myclass[$nowclassid]['cover_tohtml'] == 0) {
				//待传入模板的数据
				$thedata = $this->_get_cover_data();
				//生成封面
				if (($msg = Createhtml::instance() -> createhtml($this -> myclass[$nowclassid]['cover_tplid'], $defaultpage, $thedata)) === true) {
					$tohtml_ok += 1;
				}
				else {
					$tohtml_error += 1;
				}
			}
			else {
				//生成列表第一页为封面页
				$cover_tolistpage = $defaultpage;
			}
		}
		if ($this -> myclass[$nowclassid]['islist']) {
			if ($offset < $info_count) {
				if (! ($template_id = $this -> myclass[$nowclassid]['list_tplid'])) {
					//数据表不存在，取消执行
					$this -> _gonextpage_byclassid($nowclassid, $allclassid, '列表模板不存在，取消执行', 'class');
				}
				//orderby
				$allinfo = $_db;
				if (count($where))$allinfo = $allinfo -> where($where);
				if ($sqlwhere['value']){
					$allinfo = $allinfo -> $sqlwhere['type']($this -> dbconfig['sys_field']['class_id'], $sqlwhere['value']);
				}
				if ($this -> dbconfig['sys_field']['ontop']) {
					$allinfo = $allinfo -> orderby($this -> dbconfig['sys_field']['ontop'], 'DESC');
				}
				if ($this -> myclass[$nowclassid]['list_byfield']) {
					$allinfo = $allinfo -> orderby($this -> myclass[$nowclassid]['list_byfield'], $this -> myclass[$nowclassid]['list_orderby'] == 'ASC' ? 'ASC' : 'DESC');
				}
				if ($listfield=$this -> modelconfig['field_set']['list']){
					foreach ($listfield as $key=>$val) {
						if (substr($key,0,1) == '_') {
							unset ($listfield[$key]);
						}
					}
					$this -> _get_info_field($listfield);	//补充信息地址所必须字段
					$allinfo = $allinfo -> select(implode(',',$listfield));
				}
				$allinfo = $allinfo -> limit($limit, $offset) -> get($tablename) -> result_array(FALSE);
				//处理扩展表的信息
				foreach (array_keys($this->modelconfig['field']) as $_field) {
					if (substr($_field,0,1) == '_') {
						$sontables[] = substr($_field,1);
					}else {
						$selectwhere[] = $_field;
					}
				}
				//处理扩展表的信息
				if (!empty($sontables) || is_array($this->dbconfig['model']['relationfield'])) {
					$this->_addRelationInfo ($allinfo,$selectwhere,$sontables,'content');
				}
				$this -> myclass[$nowclassid]['url'] = Createhtml::instance() -> getclassurl($this->myclass[$nowclassid]);
				//附加URL地址
				$infocount = count($allinfo);
				if ($infocount > 0) {
					for ($i = 0; $i < $infocount; $i ++) {
						if (isset($allinfo[$i]['URL'])) $allinfo[$i]['url'] = $allinfo[$i]['URL'];
						$allinfo[$i]['URL'] = Createhtml::instance() -> getinfourl($allinfo[$i][$this -> dbconfig['sys_field']['class_id']], $allinfo[$i]);
						$thisclassid = $allinfo[$i][$this->dbconfig['sys_field']['class_id']];
						if (!isset($this->myclass[ $thisclassid ]['url'])){
							$this -> myclass[$thisclassid]['url'] = Createhtml::instance() -> getclassurl($this->myclass[$thisclassid]);
						}
						$allinfo[$i]['CLASS_URL'] = $this->myclass[ $thisclassid ]['url'];
					}
				}
				//getwhere($this -> myclass[$nowclassid]['dbname'],array($this -> dbconfig['sys_field']['class_id'] => $nowclassid),$limit,$offset) -> result_array ( FALSE );
				$allinfo = array_chunk($allinfo, $limit_class); //按栏目设置将所有数据分割开
				$thefile = $this -> myclass[$nowclassid]['classpath'] . '/';
				$this -> myclass[$nowclassid]['list_filename'] or $this -> myclass[$nowclassid]['list_filename'] = 'list{{page}}.html';
				if (stripos($this -> myclass[$nowclassid]['list_filename'], '{{page}}') !== false) {
					$thefile .= $this -> myclass[$nowclassid]['list_filename'];
				}
				else {
					$tmpfilename = explode('.', $this -> myclass[$nowclassid]['list_filename']);
					if (count($tmpfilename) > 1) {
						$tmpf = array_pop($tmpfilename);
						$thefile .= join('.', $tmpfilename) . '{{page}}.' . $tmpf;
					}
					else {
						$thefile .= '{{page}}_' . $this -> myclass[$nowclassid]['list_filename'];
					}
				}
				$listpage = ceil(min($limit, $info_count) / $limit_class);
				for ($i = 0; $i < $listpage; $i ++) {
					//分页地址字符串
					if (!isset($classurlstr[$nowclassid])){
						$classurlstr[$nowclassid] = Createhtml::instance() -> getclassurl($this->myclass[$nowclassid],'{{page}}');
					}
					//待传入模板的数据
					$thedata = $this->_get_list_data($allinfo[$i],$i+1,$info_count,$limit_class,$classurlstr[$nowclassid]);
					if ($i == 0) {
						//若栏目是列表页，则生成封面（列表第一页）
						if ($cover_tolistpage) {
							if (($msg = Createhtml::instance() -> createhtml($template_id, $cover_tolistpage, $thedata, 'class')) === true) {
								$tohtml_ok += 1;
							}
							else {
								$tohtml_error += 1;
							}
						}
					}
					//$tmp_classinfo[$i];
					if (($msg = Createhtml::instance() -> createhtml($template_id, str_replace('{{page}}', ($i + 1), $thefile), $thedata, 'class')) === true) {
						$tohtml_ok += 1;
					}
					else {
						$tohtml_error += 1;
					}
				}
			}
		}
		$this -> _show_json_byclassid(
			array(
				'allclassid' 	=> $allclassid , 
				'nowclassid' 	=> $nowclassid , 
				'offset' 		=> $offset , 
				'limit' 		=> $limit / $limit_class , 
				'tohtml_ok' 	=> $tohtml_ok , 
				'tohtml_error' 	=> $tohtml_error , 
				'info_count' 	=> ceil($info_count / $limit_class)
			),
			'class'
		);
	}
	
	protected function _get_special_data($list,$page=1,$info_count=0,$limit_class=100,$nowspecial=''){
		return array(
			'specialinfo'	=> $this -> specialinfo , 
			'list' 			=> $list, 
			'count' 		=> $info_count , 
			'limit' 		=> $limit_class , 
			'page'			=> $page , 
			'allpage' 		=> ceil($info_count / $limit_class) , 
			'listpage' 		=> $nowspecial
		);
	}
	
	protected function _get_cover_data(){
		global $nowclassid;
		return array(
			'db_name'		=> $this -> myclass[$nowclassid]['dbname'] , 
			'db_config'		=> $this -> dbconfig , 
			'model_config'	=> $this -> modelconfig , 
			'class_id'		=> $this -> myclass[$nowclassid]['classid'] , 
			'class_name'	=> $this -> myclass[$nowclassid]['classname'] , 
			'myclass'		=> $this -> myclass[$nowclassid] , 
			'class_url'		=> Createhtml::instance()->getclassurl($this->myclass[$nowclassid])
		);
	}
	
	protected function _get_list_data($list,$page=1,$info_count=0,$limit_class=100,$nowclassurl=''){
		global $nowclassid;
		$nowclassurl or $nowclassurl = Createhtml::instance()->getclassurl($this->myclass[$nowclassid]);
		return array(
			'db_name' 		=> $this -> myclass[$nowclassid]['dbname'] , 
			'db_config' 	=> $this -> dbconfig , 
			'model_config' 	=> $this -> modelconfig , 
			'class_id' 		=> $nowclassid , 
			'class_name' 	=> $this -> myclass[$nowclassid]['classname'] , 
			'myclass' 		=> $this -> myclass[$nowclassid] , 
			'list' 			=> $list, 
			'count' 		=> $info_count , 
			'limit' 		=> $limit_class , 
			'page'			=> $page , 
			'allpage' 		=> ceil($info_count / $limit_class) , 
			'listpage' 		=> $nowclassurl , 
			'class_url' 	=> $this -> myclass[$nowclassid]['url']
		);
	}
	
	
	protected function _get_content_data($theinfo){
		global $nowclassid;
		return array(
			'ok' => true,
			'id' => $theinfo[$this -> dbconfig['sys_field']['id']] , 
			'title' => $theinfo[$this -> dbconfig['sys_field']['title']] , 
			'class_id' => $nowclassid , 
			'class_name' => $this -> myclass[$nowclassid]['classname'] , 
			'db_name' => $this -> myclass[$nowclassid]['dbname'] , 
			'db_config' => $this -> dbconfig , 
			'model_id' => $this -> myclass['modelid'] , 
			'model_config' => $this -> modelconfig , 
			'info' => $theinfo , 
			'myclass' => $this -> myclass[$nowclassid] , 
			'class_url' => Createhtml::instance() -> getclassurl($this -> myclass[$nowclassid])
		);
	}
	
	
	public function class_block(){
		$blocktype = $_GET['_blocktype'];
		global $nowclassid;
		
		$nowclassid = (int) $_GET['_nowclassid'];
		if (!$nowclassid>0){
			exit('<script>parent.alert("缺少参数！")</script>');
		}
		
		$this -> myclass[$nowclassid] = Myqee::myclass($nowclassid);
		if (!$this -> myclass[$nowclassid]) {
			exit('<script>parent.alert("指定的栏目不存在！")</script>');
		}
		
		
		if (! $this -> myclass[$nowclassid]['dbname']) {
			//栏目数据表信息缺失，取消执行
			exit('<script>parent.alert("栏目数据表信息缺失！")</script>');
		}
		//读取数据表配置
		$this -> dbconfig = Myqee::config('db/' . $this -> myclass[$nowclassid]['dbname']);
		if (! $this -> dbconfig['sys_field']['class_id']) {
			exit('<script>parent.alert("数据表不存在栏目ID字段！")</script>');
		}
		$database = $this -> dbconfig['database'];
		$tablename = $this -> dbconfig['tablename'];
		$_db = Database::instance($database);
		if (! $_db -> table_exists($tablename)) {
			//数据表不存在，取消执行
			exit('<script>parent.alert("数据表“'.$tablename.'”不存在！")</script>');
		}
		$this -> modelconfig = Myqee::config('model/model_' . $this -> myclass[$nowclassid]['modelid']);
		
		
		if ($blocktype=='cover'){
			//封面
			if ($this -> myclass[$nowclassid]['cover_tplid']){
				//待传入模板的数据
				$thedata = $this->_get_cover_data();
				//生成封面
				return $this -> _echo_editblock_html($this -> myclass[$nowclassid]['cover_tplid'],$thedata);
			}else{
				exit('<script>parent.alert("栏目没有指定封面模板！")</script>');
			}
		}elseif ($blocktype=='list'||$blocktype=='search'){
			//列表
			if ($blocktype=='list' && ! ($template_id = $this -> myclass[$nowclassid]['list_tplid']) ) {
				exit('<script>parent.alert("列表模板不存在！")</script>');
			}elseif($blocktype=='search' && ! ($template_id = $this -> myclass[$nowclassid]['search_tplid']) ) {
				exit('<script>parent.alert("搜索模板不存在！")</script>');
			}
			//list_nosonclass==0本栏目及子栏目
			//list_nosonclass==1本栏目
			//list_nosonclass==2仅子栏目
			if ($this -> myclass[$nowclassid]['list_nosonclass'] == 1) {
				$sqlwhere = array(
					'type' => 'where' , 
					'value' => $nowclassid
				);
			}else {
				$sonclassid = '';
				if ($this -> myclass[$nowclassid]['list_nosonclass'] == 2) {
					$sqlwhere = array(
						'type' => 'in' , 
						'value' => explode('|', trim($this -> myclass[$nowclassid]['sonclass'], '| '))
					);
				}else {
					$sqlwhere = array(
						'type' => 'in' , 
						'value' => explode('|', trim($nowclassid . $this -> myclass[$nowclassid]['sonclass'], '| '))
					);
				}
			}
			
			if ($this -> dbconfig['sys_field']['isshow']) {
				$where = array(
					$this -> dbconfig['sys_field']['isshow'] => 1
				);
			}
			
			//读取信息数量
			$info_count = $this -> myclass[$nowclassid]['info_count'] = $_db;
			if ($sqlwhere['value']){
				$info_count = $info_count -> $sqlwhere['type']($this -> dbconfig['sys_field']['class_id'], $sqlwhere['value']);
			}
			if ($where && count($where)) {
				$info_count = $info_count -> where($where);
			}
			$info_count = $info_count -> count_records($tablename);
			
			$limit_class = $this -> myclass[$nowclassid]['list_pernum'];
			$limit_class > 0 or $limit_class = 20;
			if ($info_count){
				//orderby
				$allinfo = $_db;
				if ($sqlwhere['value']){
					$allinfo = $allinfo -> $sqlwhere['type']($this -> dbconfig['sys_field']['class_id'], $sqlwhere['value']);
				}
				if ($where && count($where)) {
					$allinfo = $allinfo -> where($where);
				}
				if ($this -> dbconfig['sys_field']['ontop']) {
					$allinfo = $allinfo -> orderby($this -> dbconfig['sys_field']['ontop'], 'DESC');
				}
				if ($this -> myclass[$nowclassid]['list_byfield']) {
					$allinfo = $allinfo -> orderby($this -> myclass[$nowclassid]['list_byfield'], $this -> myclass[$nowclassid]['list_orderby'] == 'ASC' ? 'ASC' : 'DESC');
				}
				if ($listfield = $this -> modelconfig['field_set']['list']){
					$this -> _get_info_field($listfield);
					$allinfo = $allinfo -> select(implode(',',$listfield));
				}
				$allinfo = $allinfo -> limit($limit_class, 0) -> get($tablename) -> result_array(FALSE);
				//处理扩展表的信息
				foreach (array_keys($this->modelconfig['field']) as $_field) {
					if (substr($_field,0,1) == '_') {
						$sontables[] = substr($_field,1);
					}else {
						$selectwhere[] = $_field;
					}
				}
				//处理扩展表的信息
				if (!empty($sontables) || is_array($this->dbconfig['model']['relationfield'])) {
					$this->_addRelationInfo ($allinfo,$selectwhere,$sontables,'content');
				}
				//附加URL地址
				for ($i = 0; $i < $info_count; $i ++) {
					if (isset($allinfo[$i]['URL'])) $allinfo[$i]['url'] = $allinfo[$i]['URL'];
					$classid = $allinfo[$i][$this->dbconfig['sys_field']['class_id']];
					$allinfo[$i]['URL'] = Createhtml::instance() -> getinfourl($classid, $allinfo[$i]);
					$allinfo[$i]['CLASS_URL'] = Createhtml::instance() -> getclassurl($classid);
				}
				
			}else{
				$allinfo = array();
			}
			$thedata = $this->_get_list_data($allinfo,1,$info_count,$limit_class);
			return $this -> _echo_editblock_html($template_id,$thedata,'class');
		}elseif($blocktype=='content'){
			if (!($template_id = $this -> myclass[$nowclassid]['content_tplid']) ) {
				exit('<script>parent.alert("内容模板不存在！")</script>');
			}
			return $this -> _echo_editblock_html($template_id,array(),'info');
		}
	}
	
	/**
	 * 以下字段用于信息地址必须，所以在读取时需要用到
	 * @param $listfield
	 * @return null
	 */
	protected function _get_info_field(&$listfield){
		if ($this->dbconfig['sys_field']['id']){
			$listfield[] = $this->dbconfig['sys_field']['id'];
		}
		if ($this->dbconfig['sys_field']['linkurl']){
			$listfield[] = $this->dbconfig['sys_field']['linkurl'];
		}
		if ($this->dbconfig['sys_field']['class_id']){
			$listfield[] = $this->dbconfig['sys_field']['class_id'];
		}
		if ($this->dbconfig['sys_field']['filepath']){
			$listfield[] = $this->dbconfig['sys_field']['filepath'];
		}
		if ($this->dbconfig['sys_field']['createtime']){
			$listfield[] = $this->dbconfig['sys_field']['createtime'];
		}
		if ($this->dbconfig['sys_field']['filename']){
			$listfield[] = $this->dbconfig['sys_field']['filename'];
		}
		if ($this->dbconfig['sys_field']['class_name']){
			$listfield[] = $this->dbconfig['sys_field']['class_name'];
		}
		$listfield = array_unique($listfield);	//移除重复值
	}
	/**
	 * 将扩展表的信息加入到主表
	 *
	 * @param array $allinfo 以引用的方式传递
	 * @param array $selectwhere 查询的条件
	 * @param array $sontables 子表
	 * @param string $op 是生成内容还是生成列表
	 */
	protected function _addRelationInfo(&$allinfo,$selectwhere,$sontables,$op) {
		//1 首先处理副表，就是对应关系为1:1的情况
		$sontables_select = array(); 
		$sontables_pk = array();
		$sontables_info = array();
		$sontables_fkey = array();
		$sontables_database = array();
		foreach ($sontables as $val) {
			$_config = Myqee::config('db/'.$val);
			$_select = array();
			if (!is_array($_config)) {
				continue;
			}
			$_pk = $this->dbconfig['model']['relationfield'][$val]['dbfield'];
			$sontables_pk[$val] = $_pk;
			$sontables_database[$val] = $_config['database'];
			$sontables_fkey[$val]['fkey'] = $this->dbconfig['model']['relationfield'][$val]['field'];
			//先加入主键
			$_select[]= $_pk;
			if (empty($_config['model']['field']) || !is_array($_config['model']['field'])) {
				continue;
			}
			foreach ($_config['model']['field'] as $_field=>$v) {
				if ($op=='content' && $v['content'] || $op=='list' && $v['list']) {
					$_select[] = $_field;
				}
			}
			$sontables_select[$val] = $_select;
		}
		//处理扩展表，例如输入用户ID，显示用户名这种
		$extend_tables = array();
		$extend_fkeys = array();
		$_relationfieldeinfos = $this->dbconfig['model']['relationfield'];
		foreach ($selectwhere as $val) {
			foreach ($_relationfieldeinfos as $v) {
				if ($val == $v['field'] && $v['dbfield'] != $v['dbfieldshow'] && $v['relation'] == 'n:1') {
					$extend_tables[] = $v['dbtable'];
				}
			}
		}
		
		//查询副表们的数据
		foreach ($allinfo as $val) {
			//处理子表的外键
			if (is_array($sontables_fkey)) foreach ($sontables_fkey as $k=>$v) {
				$sontables_fkey[$k]['ids'][] = $val[$sontables_fkey[$k]['fkey']];
			}
			//处理扩展表的外键
			if (is_array($extend_tables)) foreach ($extend_tables as $v) {
				$extend_fkeys[$v]['ids'][] = $val[$_relationfieldeinfos[$v]['field']];
			}
		}
		
		foreach ($sontables as $val) {
			if (empty($sontables_fkey[$val]['ids'])) {
				continue;
			}
			list($_database,$_tablename) = explode('/',$val,2);
			$_db = Database::instance($_database);
			$sontables_info[$val] = $_db->select ($sontables_select[$val])->in ($sontables_fkey[$val]['fkey'],$sontables_fkey[$val]['ids'])->get($_tablename)->result_assoc();
		}
		//处理扩展表的配置
		$extend_tables_info = array();
		
		foreach ($extend_tables as $val) {
			if (empty($extend_fkeys[$val]['ids'])) {
				continue;
			}
//			$_config = Myqee::config('db/'.$val);
			
			list($_database,$_tablename) = explode('/',$_relationfieldeinfos[$val]['dbtable'],2);
			$_db = Database::instance($_database);
			$_select = array($_relationfieldeinfos[$val]['dbfield'],$_relationfieldeinfos[$val]['dbfieldshow']);
			$extend_tables_info[$val] = $_db->select ($_select)->in ($_relationfieldeinfos[$val]['dbfield'],$extend_fkeys[$val]['ids'])->get($_tablename)->result_assoc();
		}
//		print_r($sontables_info);die;
		//将副表扩展表的数据合并到主表
		foreach ($allinfo as $key=>$val) {
			foreach ($sontables_fkey as $k=>$v) {
				$tmp = $sontables_info[$k][$val[$sontables_pk[$k]]];
				if (is_array($tmp)) {
					$val = array_merge($val,$tmp);
				}
			}
			
			foreach ($extend_fkeys as $k=>$v) {
				$val['_'.$_relationfieldeinfos[$k]['field']] = $extend_tables_info[$k][$val[$_relationfieldeinfos[$k]['field']]][$_relationfieldeinfos[$k]['dbfieldshow']];
//				$val = array_merge($val,$extend_tables_info[$k][$val[$_relationfieldeinfos[$k]['field']]]);
			}
			$allinfo[$key] = $val;
		}
	}
	
	
	/**
	 * 
	 * @param $tpl_id 模板ID
	 * @param $data 传入模板数据
	 * @param $type 视图类型
	 * @return boolean true/false
	 */
	protected function _echo_editblock_html($tpl_id,$data=null,$type=null){
			$html = Createhtml::instance() -> createhtml($tpl_id,false,$data,$type);
			if (!preg_match("/<base([^>]+)href([ ]+)?=.*>/is",$html)){
				//添加base href
				$oldlen = strlen($html);
				$html = preg_replace("/<head([^>]+)?>(.*)<\/head>/Usi","<head$1>\r\n<base href=\"http://".Myqee::config('core.mysite_domain')."/\" />$2</head>",$html);
				if (strlen($html)==$oldlen){
					$html = "<base href=\"http://".Myqee::config('core.mysite_domain')."/\" />\r\n".$html;
				}
			}
			echo $html;
			return true;
	}
}	//end myqeetohtml