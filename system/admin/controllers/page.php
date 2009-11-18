<?php

class Page_Controller_Core extends Controller {
	function __construct(){
		parent::__construct();
		Passport::chkadmin();
	}

	public function index($page=1){
		$per = 20;
		$page>0?'':$page=1;
		$db = Database::instance();

		$query = $db->from('[page]')->orderby("id","DESC")->limit($per,($page-1)*$per)->get();

		$view = new View('admin/page_list');
		$view -> set('list',$query->result_array(FALSE));
		$view -> set('title','页面列表');
		
		
		//分页显示
		$num = $db -> count_records('[page]');
		$this->pagination = new Pagination(array(
			'uri_segment'    => 'index',
			'total_items'    => $num,
			'items_per_page' => $per
		));
		
		$view -> set ('page',$this->pagination->render('digg'));
		$view -> render(TRUE);
	}

	public function add(){
		$this -> edit ();
	}
	/**
	 * 显示编辑页面
	 *
	 * @param int $id
	 */
	public function edit($id=0){
		$view = new View('admin/page_add');
		if ($id>0){
			$view -> title = '修改页面';
			$db = new Database;
			$query = $db->from('page')->where(array('id >=' => $id))->limit(1)->get();
			if (($result = $query->result_array(FALSE))){
				$view -> set('page',$result[0]);
			}
		}else{
			$view -> title = '添加页面';
		}
		$view -> page_index = $this->pageIndex;;
		$view -> render(TRUE);
	}

	public function save(){
		if (!$post=$_REQUEST['page']){
			echo '<script>alert("参数错误！");document.location="about:blank";</script>';
			exit();
		}

		$post['id']=(int)$post['id'];
		$updateinfo=array(
			'title' => htmlspecialchars($post['title']),
			'content' => $post['content'],
			'filename' => $post['filename'],
			'filepath' => $post['filepath'],
			'urlpath' => $post['urlpath'],
			'isshow' => (int)$post['isshow'],
			'classname' => $post['classname']
		);
		if (empty($updateinfo['title'])){
			echo '<script>alert("页面名称不能空！");document.location="about:blank";</script>';
			exit();
		}
		if (empty($updateinfo['content'])){
			echo '<script>alert("页面内容不能空！");document.location="about:blank";</script>';
			exit();
		}
		if (empty($updateinfo['filename'])){
			echo '<script>alert("文件名不能空！");document.location="about:blank";</script>';
			exit();
		}
		if (empty($updateinfo['filepath'])){
			echo '<script>alert("生成文件路径不能空！");document.location="about:blank";</script>';
			exit();
		}
		if (!preg_match("/[a-zA-Z0-9_\.\-]/",$updateinfo['filename'])){
			echo '<script>alert("文件名只能由字母数字及“_.-”组成！");document.location="about:blank";</script>';
			exit();
		}
		if (!preg_match("/[a-zA-Z0-9_\.\-\/]/",$updateinfo['filepath'])){
			echo '<script>alert("文件路径只能由字母数字及“_.-/”组成！");document.location="about:blank";</script>';
			exit();
		}

		$updateinfo['isshow']=$updateinfo['isshow']==0?0:1;		//是否显示
		$updateinfo['lastcreatedate'] = $_SERVER['REQUEST_TIME'];					//最后生成时间
		empty($updateinfo['urlpath'])?$updateinfo['urlpath']=$updateinfo['filepath']:'';

		$db = new Database;
		
		$newfullfile = $updateinfo['filepath'].$updateinfo['filename'];						//新的完整文件名
		$tplpath = 'page_create_'.md5($newfullfile).'.htm';									//模板路径

		if ($post['id']>0){
			//更新数据---------
			//读取旧信息
			$olddata = $db->select("filepath,filename")->from('[page]')->where(array('id'=>$post['id']))->get()->result_array(FALSE);
			$olddata = $olddata[0];
			if (!$olddata){
				echo '<script>alert("不存在此数据，可能已删除！");document.location="about:blank";</script>';
				exit();
			}
			$oldfullfile = $olddata['filepath'].$olddata['filename'];							//旧的完整文件名
			
			if ($newfullfile!=$oldfullfile){
				define('DEL_HTML','yes');
				$this -> _deleteoldfile($oldfullfile);
			}

			$status = $db->update('page',$updateinfo,array('id' =>$post['id']));
		}else{
			//插入数据----------
			//查询旧数据
			$ishavedate = $db->select("count(1) as total")->from('[page]')->where(array('filepath'=>$updateinfo['filepath'],'filename'=>$updateinfo['filename'])) -> limit(1) -> get() ->result_array(FALSE);
			$ishavedate = $ishavedate[0]['total'];
			if ($ishavedate==1){
				echo '<script>alert("已经存在“',$olddata['filepath'].$olddata['filename'],'”文件，添加失败！");document.location="about:blank";</script>';
				exit();
			}
			//执行插入语句
			$status = $db->insert('page',$updateinfo);
		}
		$rows = count($status);
		if ($rows>0){
			
			if ($updateinfo['isshow']==0){
				//删除旧文件
				define('DEL_HTML','yes');
				$this -> _deleteoldfile($newfullfile);
			}else{
				//生成静态模板页面
				define('CREATE_HTML','yes');
				//echo APPPATH . 'template/' .$newfullfile;exit;
				file_put_contents(APPPATH . 'template/' .$tplpath,$updateinfo['content']);			//保存模板
				
				$this -> _caratehtml($newfullfile,$tplpath);
			}
			echo '<script>alert("保存成功！");parent.document.location="'.Myqee::url('admin/page/index').'";</script>';
			exit();
		}else{
			echo '<script>alert("未更新任何数据！");document.location="about:blank";</script>';
		}
	}
	/**
	 * 重新生成html
	 *
	 * @param int $id
	 */
	public function rehtml($id,$reshow = false){
		if (!$id){
			echo '<script>alert("缺少参数！");document.location="about:blank";</script>';
			exit();
		}
		//简单的校验
		if ($reshow){
			if ($reshow != md5($id))
				defined('DEL_HTML') or die('No direct script access.');	//防止页面非法加载
			else
				$updateinfo["isshow"] = 1;	//将更新到数据
		}
		$db = new Database;
		$olddata = $db->select("filepath,filename,content")->from('[page]')->where(array('id'=>$id))->get()->result_array(FALSE);
		$olddata = $olddata[0];
		if (!$olddata){
			echo '<script>alert("不存在此数据，可能已删除！");document.location="about:blank";</script>';
			exit();
		}
		$oldfullfile = $olddata['filepath'].$olddata['filename'];	//完整文件名
		
		//生成静态页面
		define('CREATE_HTML','yes');
		$tplpath = 'page_create_'.md5($oldfullfile).'.htm';		//模板路径
		
		//检查模板文件是否存在
		$mytpl = APPPATH.'template/'.$tplpath;
		if (!is_file($mytpl)){
			if ($reshow){
				//重新生成静态模板文件
				define('CREATE_HTML','yes');
				file_put_contents($mytpl , $olddata['content']);		//保存模板
			}else{
				echo '<script>alert("重新生成失败！\n\n可能模板缓存已删除或此文件已屏蔽，请进入修改页面重新保存！");document.location="about:blank";</script>';
				exit();
			}
		}

		$this -> _caratehtml($oldfullfile,$tplpath);
		$updateinfo["lastcreatedate"] = $_SERVER['REQUEST_TIME'];
		//更新最后生成时间
		$db->update('page',$updateinfo,array('id' =>$id));
		
		$benchmark = Benchmark::get(SYSTEM_BENCHMARK.'_total_execution');
		if ($reshow){
			echo '<script>alert("开启成功，耗时：'.$benchmark['time'].'秒！");parent.document.location=parent.document.location;</script>';
		}else{
			echo '<script>alert("重新生成成功，耗时：'.$benchmark['time'].'秒！");document.location="about:blank";</script>';
		}
	}
	
	public function open($id){
		$this->rehtml($id,md5($id));
	}
	/**
	 * 屏蔽文件
	 *
	 * @param unknown_type $allid
	 */
	public function close($allid){
		$this->del($allid,true);
	}
	
	/**
	 * 删除文件
	 *
	 * @param string/int $allid 待删除文件id，例如：1或1,2,3
	 * @param boolean $isclose 是否屏蔽，true情况下，只屏蔽数据，不删除
	 */
	public function del($allid='',$isclose=false){
		$idArr=explode(',',$allid);
		$myId=array();
		foreach ($idArr as $value){
			if ($value>0){
				$myId[]=$value;
			}
		}
		$allid=preg_replace("/[^0-9,]/i","",$allid);
		if (count($myId)>0){
			$db = new Database;
			$configData=Myqee::config("database.default");
			$dbPrefix = $configData['table_prefix'];
			
			//读取旧信息
			$olddata = $db->select("filepath,filename")->from('page')->in('id' , $myId)->get()->result_array(FALSE);
			//query('SELECT filepath,filename FROM '.$dbPrefix.'page WHERE id IN ("'.join(',',$myId).'")');
			define('DEL_HTML','yes');
			foreach ($olddata as $row){
				$this -> _deleteoldfile($row->filepath.$row->filename);
			}

			if (!$olddata){
				echo '<script>alert("不存在此数据，可能已删除！");document.location="about:blank";</script>';
				exit();
			}
//			$oldfullfile = $olddata['filepath'].$olddata['filename'];		//旧的完整文件名

			
			if ($isclose){
				$thetypetext='屏蔽';
				$status = $db->query('UPDATE '.$dbPrefix.'page SET isshow=0 WHERE id IN ("'.join(',',$myId).'")');
			}else{
				$thetypetext='删除';
				$status = $db->query('DELETE FROM '.$dbPrefix.'page WHERE id IN ("'.join(',',$myId).'")');
			}
			if (count($status)>0){
				echo '<script>alert("恭喜，成功'.$thetypetext.count($status).'个页面！");parent.location=parent.location;</script>';
				exit();
			}else{
				echo '<script>alert("未'.$thetypetext.'任何数据！");document.location="about:blank";</script>';
				exit();
			}
		}else{
			echo '<script>alert("缺少参数！");document.location="about:blank";</script>';
			exit();
		}
	}


	/**
	 * 生成静态页面
	 *
	 * @param array $data 生成文件的信息
	 */
	public function _caratehtml($file,$tplpath){
		defined('CREATE_HTML') or die('No direct script access.');	//防止页面非法加载
		if (!$file || !$tplpath){
			echo '<script>alert("程序异常，请联系管理员！");document.location="about:blank";</script>';
			exit();
		}
		if (substr($file,0,1)!='/'){
			$file = UNCON_PATH . '/' .$file;
		}else{
			$file = UNCON_PATH .$file;
		}
		//加载模板引擎
		$template = new Template($tplpath);
		$template->parse();
		$template->save($file,'gzip');
	}
	
	/**
	 * 删除旧文件
	 *
	 * @param string $oldfullfile 完整文件名，由“文件路径”和“文件名”组成
	 */
	public function _deleteoldfile($oldfullfile){
		defined('DEL_HTML') or die('No direct script access.');	//防止页面非法加载
		if (substr($oldfullfile,0,1)!='/'){
			$thefile = WWWROOT . '/' .$oldfullfile;
		}else{
			$thefile = WWWROOT .$oldfullfile;
		}
		if (is_file($thefile)){
			@unlink($thefile);		//删除旧文件
		}
		$oldtplfile = 'page_create_'.md5($oldfullfile).'.htm';
		if (is_file(APPPATH . 'template/' . $oldtplfile)){
			@unlink(APPPATH . 'template/' . $oldtplfile);			//删除旧模板
		}
	}
}