<?php
class acquisition_run_Controller_Core extends Controller {
	protected $id;				//采集id
	protected $config;			//采集配置
	protected $nodeid;			//采集节点
	protected $node;			//节点配置
	protected $dopage;			//采集的页码
	protected $urllists_file;	//URL列表文件路径
	protected $urlset;			//采集的URL设置
	protected $mylists;			//采集列表
	
	protected $adminid;			//操作的管理员ID
	
	protected $db;				//数据库
	
	protected $dotime;			//操作时间，用于纪录日志时用
	
	protected $rereadinfo = 0;	//重新采集信息ID
	
	public function __construct(){
		parent::__construct();
		
		if (isset($_POST['dotime']))$dotime = preg_replace("/[^0-9]/",'',$_POST['dotime']);
		if (!$dotime && isset($_GET['dotime'])){
			$dotime = preg_replace("/[^0-9]/",'',$_GET['dotime']);
		}
		if (!$dotime){
			$dotime = time()*1000;
		}
		$this->dotime = $dotime;
		
		set_time_limit(0);
		error_reporting(0);
	}
	
	/**
	 * 运行采集
	 *
	 * @return none
	 */
	public function index(){
		$this -> _check_acqu_post();
		//设置运行标志,如果没有此文件则退出采集
		$this->runfile = MYAPPPATH."data/acqu_run_{$this->id}_{$this->nodeid}.run";
		if (@touch ($this->runfile)) {
			$this->_log('info',"成功设置运行标志文件 {$this->runfile}");
		} else {
			$this->_log('info',"设置运行标志文件 {$this->runfile} 失败，请检查目录是否可写");
			return true;
		}
		
		//在后台运行
		Myqee::run_in_system();
		
		if ($_GET['_reload']){
			$loginfo = '第'.$_GET['_reload'].'次尝试采集:	';
		}else{
			$loginfo = '';
		}
		if($this -> rereadinfo){
			$loginfo .= '重新采集临时数据ID:'.$this -> rereadinfo.'.任务：';
		}else{
			$loginfo = '开始采集:	';
		}
		$this->_log('info',$loginfo.'id:'.$this->id.'('.$this->config['name'].'),节点:'.$this->nodeid.'('.$this->node['name'].')任务，分组：'.$this->dopage);
		
		
		$this->snoopy = new Snoopy;
		$this->snoopy -> getresults = FALSE;
		$this->snoopy -> ip = $_SERVER["SERVER_ADDR"];	//指定访问本服务器
		
		$this->node['limitpage']>0 or $this->node['limitpage'] = 1;
		
		if (($this->mylists && is_array($this->mylists)) || file_exists($this->urllists_file)){
			//打开文件
			if (!is_array($this->mylists)){
				$this->mylists = file_get_contents($this->urllists_file);
				$this->mylists = explode("\n",$this->mylists);
			}
		
			//第一行为配置信息，预留功能
			$this->fileconfig = array_shift($this->mylists);
			$this->fileconfig = unserialize(base64_decode($this->fileconfig));
			
			//远程地址列表
			if (($count_i=count($this->mylists))>0){
				if(!$this -> rereadinfo){
					//按组操作
					$count_i = min($this->node['limitpage'],$count_i);
					
					//锁定文件
					$this->fp = fopen($this->urllists_file,'w');
					if (flock($this->fp, LOCK_EX)) {
						$this->islock = TRUE;
					}else{
						$this->islock = FALSE;
					}
				}
				
				$myurl = array();
				
				//打开一组列表
				for($i=0;$i<$count_i;$i++){
					$url = array_shift ($this->mylists);
					$url = str_replace("\n",'',str_replace("\r",'',$url));		//移除换行符
					if (!$url)continue;
					
					if (!$this -> rereadinfo && !$this->node['reacquurl']){
						$this -> db or $this -> db = new Database();
						$isacqu = $this->db -> count_records('[acquisition_data]',array('info_url_md5'=>md5($url)));
						if ($isacqu){
							$this->_log('info','跳过采集信息（已采集过）：'.$url);
							continue;
						}
					}
					
					$now = time();
					$code = $this->_get_url_code($now,null,$url);
					//新加载进程，采集页面
					$dourl = Myqee::url("acquisition_run/readurl?id={$this->id}&nodeid={$this->nodeid}&timeline={$now}&adminid={$this->adminid}&dotime={$this->dotime}&code={$code}&url=".urlencode($url),null,true);
								
					$myurl[] = array('url'=>$url,'path'=>MYAPPPATH.'temp/acqu_'.md5($url));
					
					
					$this->_log('info','创建远程读取地址:		'.$url);
					
					//启动远程读取
					$this->snoopy -> fetch($dourl);
					
//					file_put_contents(MYAPPPATH.'temp/debug.txt',$this->snoopy->results);
					
					$this->_log('info','已打开远程地址:			'.$url);
				}
				
				if ($this -> rereadinfo){
					//执行检测
					$this->_run_acqu_check($myurl);
					
					return true;
				}
				
				if (!$this->mylists || count($this->mylists)==0){
					//列表结束了
					
					//释放文件锁
					if ($this->islock){
						flock($this->fp, LOCK_UN); // 释放锁定
					}
					
					fclose($this->fp);
					
					//采集删除列表
					unlink($this->urllists_file);
					
					//执行检测
					$data_ids = $this->_run_acqu_check($myurl);
					
					//执行最后一组
					if ($this->node['donum']>0){
						$this -> _for_next_group($data_ids);
					}
				
					//任务全部完成后执行
					$this->_do_all_over();
					
					//采集完成
					return TRUE;
				}else{
					//写入下一组待执行的文件
					$this -> _write_url_list($this -> mylists);
				}
				
				//等待采集
				$data_ids = $this->_run_acqu_check($myurl);
				
				
				//为下一组的执行做准备
				if ($this->node['donum']>0){
					$this -> _for_next_group($data_ids);
				}
				
				
				if ($this->node['limittime']>0){
					//如果设置了暂停，则暂停一下
					$this->_log('info','本组采集完成，等待'.$this->node['limittime'].'毫秒后执行下一任务');
					usleep($this->node['limittime']*1000);
				}
				
				//执行下一组
				$this -> _run_next_acqu();
			}else{
				if ($this->dopage==1){
					//若首页面出现采集列表为空的现象，说明此采集列表为空！
					$this->_log('error','待采集文件列表为空！');
				}else{
					
					@unlink($this->urllists_file);	//删除列表文件
					
					//任务全部完成后执行
					$this->_do_all_over();
				}
			}
		}else{
			$this->_log('error','未获取采集列表文件！');
		}
		return TRUE;
	}
	

	/**
	 * 读取远程URL接口
	 * 此进程无超时时间
	 * 此进程将从新打开readurl_run
	 *
	 */
	public function readurl(){
		$this->_chk_readurl();
		
		Myqee::run_in_system();
		
		set_time_limit(0);
		
		$snoopy = new Snoopy;
		$snoopy -> ip = $_SERVER["SERVER_ADDR"];
		
		//尝试5次
		for($i=1;$i<=5;$i++){
			//创建URL
			$url = Myqee::url('acquisition_run/readurl_run').'?'.http_build_query($_GET,NULL,'&');
			if(substr($url,0,1)=='/'){
				$url = 'http://'.$_SERVER['HTTP_HOST'] . $url;
			}
			
			$snoopy -> fetch($url);
			$snoopy -> maxlength = 2;
			if ($snoopy -> results =='OK' ){
				//远程读取正常完成
				
				exit;
			}else{
				$this->_log('error','远程打开信息超时，尝试第'.$i.'次重新采集！');
			}
		}
		
	}
	
	/**
	 * 真正的打开远程地址
	 * 由readurl加载，根据设定的最常时间打开，防止超时。
	 *
	 */
	public function readurl_run(){
		$this->_chk_readurl();
		
		//设置超时，默认60
		$this->node['openurltimeout']>=0 or $this->node['openurltimeout'] = 60;
		
		//设定脚本超时时间~
		set_time_limit($this->node['openurltimeout']);
		
		$snoopy = new Snoopy;
		//模拟客户端数据
		//cookies
		$cookies = preg_replace ('#^[\s]+|[\s]+$#','',$this->config['post']['cookie']);
		if (!empty($cookies)) {
			$tmp = preg_split('#; #',$cookies);
			foreach ($tmp as $val) {
				list ($_key,$_val) = explode ('=',$val);
				$snoopy->cookies[$_key] = $_val;
			}
		}
		//agent
		$user_agent = preg_replace ('#^[\s]+|[\s]+$#','',$this->config['post']['user_agent']);
		if (!empty($user_agent)) {
			$snoopy->agent = $user_agent;
		}
		//referer
		$referer = trim ($this->config['post']['referer']);
		if (!empty($referer)) {
			$snoopy->referer = $referer;
		}
		
		$this->_log('info','打开远程地址:			'.$this->url);
		
		//开始读取
		$snoopy->fetch($this->url);
		
		$this->_log('info','关闭远程地址:			'.$this->url);
		
		//写入临时文件
		$tmpfile = MYAPPPATH.'temp/acqu_'.md5($this->url);
		
		if (empty($snoopy->results)){
			$this->_log('info','获取文件为空:			'.$tmpfile);
		}else{
			file_put_contents($tmpfile,$snoopy->results);
			$this->_log('info','写入临时文件:			'.$tmpfile);
		}
		
		echo 'OK';
	}
	
	
	/**
	 * 写入URL列表
	 *
	 * @param array $urls 列表
	 * @param object $fp 文件指针
	 * @return boolean 是否成功
	 */
	protected function _write_url_list( $urls,&$fp = null ){
		
		array_unshift( $urls , base64_encode(serialize($this->fileconfig)) );	//将配置文件插入第一行
		
		$urls = implode("\n",$urls);
		
		if ($this->fp && $this->islock){
			flock($this->fp, LOCK_UN); // 释放锁定
		}
		$fp or $fp =& $this -> fp;
		
		if(!$fp)return FALSE;
		
		fwrite($fp, $urls);
		fclose($fp);
		
		$fp = null;
		
		return TRUE;
	}
	
	
	/**
	 * 采集下一组
	 *
	 * @param false/$urllist $is_reload 是否重新采集，若重新采集，则将待重新采集的列表传入
	 */
	protected function _run_next_acqu($is_reload=false) {
		//检测是否已经停止
		clearstatcache();
		if (!file_exists($this->runfile)) {
			//如果运行标志文件不存在，说明管理员已经发送了停止标志
			$this->_log('info','用户发送了停止采集的命令，采集已经停止。');
			return true;
		}
		//更新时间线
		$_GET['timeline'] = time();
		$_GET['code'] = $this->_get_url_code($_GET['timeline']);
		$_GET['dopage'] = $this->dopage;
		
		if ($is_reload && is_array($is_reload)){
			if (!$this -> rereadinfo){
				if (preg_match("/_([a-f0-9]{32})_reload\.txt$/",$this->urllists_file,$match) && $match[1]){
					$file = $this->urllists_file;
					$reload_list_name = $match[1];
				}else{
					$reload_list_name = md5(time().serialize($is_reload));
					$file = substr($this->urllists_file,0,-4).'_'.$reload_list_name.'_reload.txt';
				}
				
				$fp = @fopen($file,'w');
				$this -> _write_url_list($is_reload,$fp);
				
				$this->_log('info','写入重新执行列表			'.$file);
				
				//失败的列表存入临时文件1
				$_GET['_reload_list'] = $reload_list_name;
			}
			
			$_GET['_reload'] = (int)$_GET['_reload']+1;
		}else{
			$_GET['dopage'] = $this->dopage + 1;
			
			if (isset($_GET['_reload']))unset($_GET['_reload']);
		}
		
		//次级页面不传递这个参数
		if (isset($_GET['_dotype']))unset($_GET['_dotype']);
		
		//创建URL
		$url = Myqee::url('acquisition_run',null,true).'?'.http_build_query($_GET,NULL,'&');
		
		//将POST的多维数组转换为提交方式
		if ($_POST){
			$post = array();
			foreach ($_POST as $key => $value){
				if (is_array($value)){
					foreach ($value as $k2 => $v2){
						if (is_array($v2)){
							foreach ($v2 as $k3 => $v3){
								$post[$key.'['.$k2.']['.$k3.']'] = $v3;
							}
						}else{
							$post[$key.'['.$k2.']'] = $v2;
						}
					}
				}else{
					$post[$key] = $value;
				}
			}
		}
		
		//启动新进程
		$this->snoopy -> submit($url,$post);
	}
	
	/**
	 * 用于检测传入URL是否有效等
	 *
	 */
	protected function _chk_readurl(){
		$code = $_GET['code'];
		$this->url = $_GET['url'];
		$this->id = $_GET['id'];
		$this->nodeid = $_GET['nodeid'];
		$this->adminid = $_GET['adminid'];
		if (!$this->url || !$code || !($this->id>0) || !($this->nodeid>0) || !($this->adminid>0)){
			$this -> _log('error','ERROR QUERY STRING');
			$this -> _header('ERROR QUERY STRING');
		}
		if ($_SERVER['REQUEST_TIEM'] - $_GET['timeline'] > 1200){
			//>20分钟
			$this -> _log('error','REQUEST EXPRIED');
			$this -> _header('REQUEST EXPRIED');
		}
		
		//读取配置
		$this->config = Myqee::config('acquisition/acqu_'.$this->id);
		if (!is_array($this->config)){
			$this -> _log('error','NO ACQUISITON');
			$this -> _header('NO ACQUISITON');
		}
		
		if (!$this->config['isuse']){
			$this -> _log('error','ACQUISITON NO USE');
			$this -> _header('ACQUISITON NO USE');
		}
		
		$this->node = $this->config['node']['node_'.$this->nodeid];
		if (!is_array($this->node)){
			$this -> _log('error','NO NODE');
			$this -> _header('NO NODE');
		}
		
		if (!$this->node['isuse']){
			$this -> _log('error','NODE NO USE');
			$this -> _header('NODE NO USE');
		}
		
		//校验密钥
		if ($this->_get_url_code($_GET['timeline'],null,$this->url)!=$code){
			$this -> _log('error','ERROR CODE');
			$this -> _header('ERROR CODE');
		}
		
		//检查管理员是否有操作权限
		
	}
	
	/**
	 * 获取页面code
	 *
	 * @param string $url 页面地址
	 * @param int $now 时间
	 * @return string md5后的字符串
	 */
	protected function _get_url_code($now,$keycode=null,$url=''){
		return md5($url.($keycode?$keycode:$this->node['key']).'__'.$now.'__'.$this->adminid);
	}
	
	/**
	 * 必要的检验
	 *
	 */
	protected function _check_acqu_post(){
		
		if ($_SERVER['REQUEST_TIME'] - $_GET['timeline'] > 1200){
			//>20分钟
			$this -> _log('error','REQUEST EXPRIED');
			$this -> _header('REQUEST EXPRIED');
		}
		
		$this->id = $_GET['id'];
		$this->nodeid = $_GET['nodeid'];
		$this->adminid = $_GET['adminid'];
		
		if (!($this->id>0) || !($this->nodeid>0) || !($this->adminid>0)){
			$this -> _log('error','ERROR QUERY STRING');
			$this -> _header('ERROR QUERY STRING');
		}
		
		$this->dopage = (int)$_GET['dopage'];
		$this->dopage>1 or $this->dopage = 1;
		
		
		//读取配置
		$this->config = Myqee::config('acquisition/acqu_'.$this->id);
		if (!is_array($this->config)){
			$this -> _log('error','NO ACQUISITON');
			$this -> _header('NO ACQUISITON');
		}
		
		if (!$this->config['isuse']){
			$this -> _log('error','ACQUISITON NO USE');
			$this -> _header('ACQUISITON NO USE');
		}
		
		$this->node = $this->config['node']['node_'.$this->nodeid];
		if (!is_array($this->node)){
			$this -> _log('error','NO NODE');
			$this -> _header('NO NODE');
		}
		if (!$this->node['isuse']){
			$this -> _log('error','NODE NO USE');
			$this -> _header('NODE NO USE');
		}
	
		//校验密钥
		if ($this->_get_url_code($_GET['timeline'],null,$_GET['info_url']?$_GET['info_url']:'')!=$_GET['code']){
			$this -> _log('error','ERROR CODE');
			$this -> _header('ERROR CODE');
		}
		
		//检查当前管理员
		if (!($this -> adminid>0)){
			$this -> _log('error','UNAUTHORIZED');
			$this -> _header('UNAUTHORIZED');
		}
		
		//检查管理员是否具有采集权限
		if (!Passport::getisallow('task.acquisition_run',$this->adminid)){
			$this -> _log('error','ADMIN UNAUTHORIZED');
			$this -> _header('ADMIN UNAUTHORIZED');
		}
		
		
		//赋予默认值
		$this->node['classid'] or $this->node['classid'] = $this->config['classid'];
		$this->node['classname'] or $this->node['classname'] = $this->config['classname'];
		
		$this->node['modelid'] or $this->node['modelid'] = $this->config['modelid'];
		$this->node['modelname'] or $this->node['modelname'] = $this->config['modelname'];
		
		$this->node['dbname'] or $this->node['dbname'] = $this->config['dbname'];
		
		
		if ($_GET['acqu_data_id']>0 && $_GET['info_url']){
			//重新采集
			$this -> rereadinfo = $_GET['acqu_data_id'];
			
			$this -> mylists = array('',$_GET['info_url']);
			return;
		}
		
		
		if ($_GET['_reload'] && preg_match("[a-f0-9]+/",$_GET['_reload_list'])){
			$this->urllists_file = MYAPPPATH . 'temp/acqu_url_list_'.$this->id.'_'.$this->nodeid.'_'.$_GET['_reload_list'].'_reload.txt';
		}else{
			$this->urllists_file = MYAPPPATH . 'temp/acqu_url_list_'.$this->id.'_'.$this->nodeid.'.txt';
		}
		
		//获取URL设置
		$this->urlset = $this -> _checkpost();
		
		if ($this->dopage==1){
			//存在首页面参数
			if (file_exists($this ->urllists_file)){
				if ($_GET['_dotype']=='del'){
					//重头开始，重新生成列表
					$this->mylists = $this->_todolists();
				}elseif ($_GET['_dotype']=='next'){
					//继续
					unset($_GET['_isfrist'],$_GET['_dotype']);
				}else{
					//为页面提示已经存在列表
					$this -> _header('LISTS EXIST');
				}
			}else{
				$this->mylists = $this->_todolists();
			}
		}
		
	}
	
	
	protected function _run_acqu_check($myurl){
		$do_time = time();
		if ($this->node['openurltimeout']>0){
			$do_big_time = $this->node['openurltimeout']+120;
		}else{
			$do_big_time = 120;		//10天864000
		}
		$interval=2;				//间隔时间
		$ids = array();
		do{
			if (!$myurl)break;
			
			if (time() - $do_time > $do_big_time){
				//已达到此检测脚本设定的最长执行时间
				$this->_log('error','采集超时:				'.$this->id.'('.$this->config['name'].'),节点:'.$this->nodeid.'('.$this->node['name'].')任务');
				
				//尝试重新采集
				$this -> _run_next_acqu($myurl);
				
				exit;
			}
			
			foreach ($myurl as $k=>$v){
				if(file_exists($v['path'])){
					//采集程序已采集到内容
					$this->_log('info','获取到采集内容:			'.$v['path']);
					$tmpid = $this->_do_acqu_set($v);
					if ($tmpid>0){
						$ids[] = $tmpid;
					}
					unset($myurl[$k]);
				}
			}
			
			sleep($interval);
		}
		while(true);
		
		
		return $ids;
	}
	
	
	protected function _do_acqu_set($theurl){

		$url = $theurl['url'];		//网页地址
		$this->my_html = @file_get_contents($theurl['path']);	//获取HTML
		if ($this->my_html===FALSE || empty($this->my_html)){
			return FALSE;
		}
		//解压缩gzip
		if (strcmp(substr($this->my_html,0,2),"\x1f\x8b") === 0) {
			$this->my_html = Tools::gzdecode($this->my_html);
		}
		//过滤规则
		$this->my_filiter = array();
		if (is_array($this->node['filter']) && count($this->node['filter'])){
			foreach ($this->node['filter'] as $key => $value) {
				
				if (!isset($this->my_filiter[$key])){
					$this->_do_filiter($key ,$value,$url );
				}
			}
		}
		
		//匹配规则、采集规则
		$my_acqu = array();
		if (is_array($this->node['acqu']) && count($this->node['acqu'])){
			foreach ($this->node['acqu'] as $key => $value) {
				if ($value['doinfo']=='-1'){
					$str = $this->my_html;
				}else{
					$str = $this->my_filiter[$value['doinfo']];
				}
				$my_acqu[$value['dbfield']] = $this->_match_str($value['preg'],$value['replace'],$str );
			}
		}
		
		//输出采集地址
		$my_urls = array();
		if (is_array($this->node['urls']) && count($this->node['urls'])){
			
			$listfile = MYAPPPATH.'data/acqu_list/node_'.$this->node['id'].EXT;
			
			$list = array();
			//读取旧列表
			if (is_file($listfile)){
				include $listfile;
			}
			$my_urls = $list;
			unset($list);
			
			foreach ($this->node['urls'] as $key => $value) {
				if ($value['doinfo']=='-1'){
					$str = $this->my_html;
				}else{
					$str = $this->my_filiter[$value['doinfo']];
				}
				
				//获取列表
				$tmplist = $this->_match_all_str($value['preg'],$value['replace'],$str );
				if ($value['infotype']==1){
					//追加列表
					if(is_array($tmplist) && count($tmplist)){
						if (is_array($my_urls[$key])){
							//合并数组
							$my_urls[$key] = array_merge($my_urls[$key],$tmplist);
						}else{
							$my_urls[$key] = $tmplist;
						}
					}
				}else{
					$my_urls[$key] = $tmplist;
				}
				
				//移除重复地址
				$my_urls[$key] = array_unique($my_urls[$key]);
			}
			
			$this->_log('info','生成列表：				'.$listfile);
			
			//生成列表
			file_put_contents($listfile,'<?php defined(\'MYQEEPATH\') or die(\'No direct script access.\');'.PHP_EOL.'$list='.var_export($my_urls,TRUE).';');
		
		}
	
		//采集附件
		$my_file = array();
		if (is_array($this->node['file']) && count($this->node['file'])){
			foreach ($this->node['file'] as $key => $value) {
				if ($value['doinfo']=='-1'){
					$str = $this->my_html;
				}else{
					$str = $this->my_filiter[$value['doinfo']];
				}
				$my_file[$key] = $this->_match_all_str($value['preg'],$value['replace'],$str );
				
				//下载远程文件
			}
		}
		
		//唯一标识
		if ($this->node['the_id_name']['string']=='-1'){
			$str = $this->my_html;
		}elseif ($this->node['the_id_name']['string']=='-2'){
			$str = $theurl;
		}else{
			$str = $this->my_filiter[$value['doinfo']];
		}
		$info_id = $this->_match_str( $this->node['the_id_name']['preg'],$this->node['the_id_name']['replace'],$str );
	
		
		if ($this->node['dbname']){
			$this->dbconfig or $this->dbconfig = Myqee::config('db/'.$this->node['dbname']);
		}
		//信息标题
		if ($this->dbconfig && ($title_field=$this->dbconfig['sys_field']['title']) ){
			$title = $my_acqu[$title_field];
		}
		
		//将数据存入采集数据临时表
		$data = array(
			'title' => $title?$title:'采集:'.$this->node['name'],
			'info_id' => $info_id,
			'info_content' => serialize($my_acqu),
			'info_url' => $theurl['url'],
			'info_url_md5' => md5($theurl['url']),
			'is_todb' => 0,
			'urlread_time' => time(),
			'acqu_id' => $_GET['id'],
			'node_id' => $_GET['nodeid'],
			'dbname' => $this->node['dbname'],
			'class_id' => $this->node['classid'],
			'model_id' => $this->node['modelid'],
			'dotime' => $this->dotime,
		);
		
	//	file_put_contents(MYAPPPATH.'temp/ttt.txt',print_r($data,true));
		
		if (!$this -> db)$this -> db = new Database();
		
		//查询旧数据ID
		$dataid = $this -> db -> select('id') -> from('[acquisition_data]') -> where('info_url_md5',$data['info_url_md5']) -> get() -> result_array(false);
		$dataid = $dataid[0]['id'];
		if ($dataid>0){
			//更新数据
			$result = $this -> db -> update('[acquisition_data]',$data,array('id'=>$dataid));
		}else{
			//插入数据
			$result = $this -> db -> insert('[acquisition_data]',$data);
			$dataid = $result -> insert_id();
		}
		
		$this->_log('info','采集数据存入临时数据:		'.$theurl['url']);
		
		//删除临时文件
		if (@unlink($theurl['path'])){
			$this->_log('info','成功采集网页:	'.$theurl['url']);
		}else{
			$this->_log('info','删除临时文件失败:		'.$theurl['path']);
		}
		return $dataid;
	}
	
	
	protected function _checkpost(){
		$post = $_POST['acqu_node'];
		if (!$post){
			$post = $this->node;
			$autorun = TRUE;
		}
		$data = array();
		$data['urltype'] = $post['urltype'];
		if ($data['urltype']==0 || !($data['urltype']>0 && $data['urltype']<=3)){
			$data['urltype'] = 0;
			//一组固定的地址列表
			$post['theurl0'] = trim(str_replace(array("\r","\n\n"),"\n",$post['theurl0'])," \n");
			if (empty($post['theurl0'])){
				$info = 'info:“采集地址”不能空！';
				if ($autorun)$this->_log('info',$info);
				$this -> _header($info);
			}
			$data['theurl0'] = $post['theurl0'];
		}elseif($data['urltype']==1){
			//根据当前采集页面分析下一页面地址
			$post['theurl1']['url'] = trim($post['theurl1']['url']," \n");
			if (empty($post['theurl1']['url'])){
				$info = 'err:“原始采集页面”不能空！';
				if ($autorun)$this->_log('error',$info);
				$this -> _header($info);
			}
			$post['theurl1']['next'] = trim($post['theurl1']['next']);
			if (empty($post['theurl1']['next'])){
				$info = 'info:“下一地址规则”不能空！';
				if ($autorun)$this->_log('info',$info);
				$this -> _header($info);
			}
			$post['theurl1']['tourl'] = trim($post['theurl1']['tourl']," \n");
			if (empty($post['theurl1']['tourl'])){
				$info = 'info:“将下一地址规则结果转换为需要的结果”不能空！';
				if ($autorun)$this->_log('info',$info);
				$this -> _header($info);
			}
			$data['theurl1'] = array(
				'url'   => $post['theurl1']['url'],
				'acqu'  => $post['theurl1']['acqu'],
				'next'  => $post['theurl1']['next'],
				'tourl' => $post['theurl1']['tourl']
			);
		}elseif($data['urltype']==2){
			//有规律的页面地址
			if (empty($post['theurl2']['url'])){
				$info = 'info:“规则地址”不能空！';
				if ($autorun)$this->_log('info',$info);
				$this -> _header($info);
			}
			if (empty($post['theurl2']['replace'])){
				$info = 'info:“替换变量”不能空！';
				if ($autorun)$this->_log('info',$info);
				$this -> _header($info);
			}
			$data['theurl2'] = array(
				'url' => $post['theurl2']['url'],
				'replace' => $post['theurl2']['replace'],
				'begin' => (int)$post['theurl2']['begin'],
				'end' => (int)$post['theurl2']['end'],
				'limit' => (int)$post['theurl2']['limit'],
				'makeup' => $post['theurl2']['makeup']==1?1:0,
				'makeupnum' => (int)$post['theurl2']['makeupnum'],
				'makeupstr' => $post['theurl2']['makeupstr'],
				'reverse' => $post['theurl2']['reverse']?1:0,
			);
		}elseif($data['urltype']==3){
			//调用其它节点输出的地址
			if (!$post['theurl3']['id']>0||!$post['theurl3']['nodeid']){
				$info = 'info:请选择“采集地址设置”！';
				if ($autorun)$this->_log('info',$info);
				$this -> _header($info);
			}
			if (is_string($post['theurl3'])){
				$url3 = explode('|',$post['theurl3'],2);
				$data['theurl3'] = array(
					'id' => (int)$url3[0],
					'nodeid' => $url3[1],
				);
			}else{
				$data['theurl3'] = array(
					'id' => (int)$post['theurl3']['id'],
					'nodeid' => $post['theurl3']['nodeid'],
				);
			}
		}
		return $data;
	}
	
	
	protected function _for_next_group($data_ids){
		if ($this->node['dotype']==0 ){
			//更新数据ID
			if (isset($_POST['dataids']) && $_POST['dataids'] = Tools::formatids($_POST['dataids'],true)){
				$_POST['dataids'] .= ','.implode(',',$data_ids);
			}else{
				$_POST['dataids'] = implode(',',$data_ids);
			}
		}
		if ($this->dopage % $this->node['donum'] == 0){
			//N个分组完成后执行的项目
			if ($this->node['dotype']==0){
				//执行入库
				$this -> _info_to_database($_POST['dataids']);
				unset($_POST['dataids']);	//启动自动入库口，将所记录的临时数据ID清除。
			}elseif ($this->node['dotype']==1 && $this->node['donext_node']){
				//执行另一节点
				if (!$nextnode = $this->config['node']['node_'.$this->node['donext_node']]){
					$this->_log('error','第'.$this->dopage.'组执行结束，自动执行节点:'.$this->node['donext_node'].',但此节点不存在！');
				}else{
					//N组完成后，启动另一采集
					$this -> _run_other_acqu($nextnode);
				}
			}
		}
	}
	
	/**
	 * 创建列表
	 *
	 * @return none
	 */
	protected function _todolists(){
		$config = array ('timeline' => time() , 'urlset' => $this->urlset );
		if ($this->urlset['urltype']==0){
			//固定地址
			$urls = explode("\n",$this->urlset['theurl0']);
			$config['count'] = count($urls);
			
		}elseif ($this->urlset['urltype']==1){
			//根据前一地址得到当前地址
			$post = $_POST['acqu_node'];
			$urltext = $this->_match_str($this->urlset['theurl1']['next'] , $this->urlset['theurl1']['tourl'] , $post['theurl1']['url']);
	
			$urltext = str_replace(array("\n","\r"),'',$urltext);
			
			$urls = array($urltext);
			$config['count'] = 1;
			
			//将提交的url更换为当前匹配出来的URL
			$_POST['theurl1']['url'] = $urltext;
			
		}elseif ($this->urlset['urltype']==2){
			//有规律的地址
			$this->urlset['theurl2']['url'];
			$this->urlset['theurl2']['limit']>=1 or $this->urlset['theurl2']['limit'] = 1;
			
			$config['count'] = ceil(($this->urlset['theurl2']['end'] - $this->urlset['theurl2']['begin'])/$this->urlset['theurl2']['limit']);
			$maxlen = min(1000, $config['count']);
			
			//起始ID
			if ($this->urlset['theurl2']['reverse']){
				//倒序
				$now_id = $this->urlset['theurl2']['end'];
				$limit = -$this->urlset['theurl2']['limit'];
			}else{
				$now_id = $this->urlset['theurl2']['begin'];
				$limit = $this->urlset['theurl2']['limit'];
			}
			
			$urls = array();
			for($i=0;$i<=$maxlen;$i++){
				//补足
				if ($this->urlset['theurl2']['makeupnum']>0){
					$idstr = str_pad($now_id, $this->urlset['theurl2']['makeupnum'], $this->urlset['theurl2']['makeupstr'], $this->urlset['theurl2']['makeup']?STR_PAD_RIGHT:STR_PAD_LEFT);
				}else{
					$idstr = $now_id;
				}
				$urls[] = str_replace($this->urlset['theurl2']['replace'],$idstr,$this->urlset['theurl2']['url']);
				//间隔
				$now_id += $limit;
			}
			
		}elseif ($this->urlset['urltype']==3){
			//其它节点输出的地址
			$filepath = MYAPPPATH . 'data/acqu_list/node_' . $this->urlset['theurl3']['id'] . EXT;
			if (file_exists($filepath)){
				$list = array();
				include $filepath;
				$urls = $list[$this->urlset['theurl3']['nodeid']];
				unset($list);
				$config['count'] = count($urls);
			}else{
				$this -> _header('NO LIST EXISTS');
			}
			
		}else{
			$this -> _log('error','提交参数异常！');
			$this -> _header('ERROR');
		}
		
		array_unique($urls);	//移除重复值
		array_unshift($urls,base64_encode(serialize($config)));	//将配置文件插入第一行
		
		file_put_contents($this->urllists_file,join("\n",$urls));
		
		return $urls;
	}
	
	/**
	 * 匹配字符串
	 *
	 * @param string $s1 匹配规则
	 * @param string $s2 匹配方法
	 * @param string $str 待处理字符串
	 * @return string $mystr
	 */
	protected function _match_str($s1,$s2,$str){
		if ( !@preg_match($s1 , $str , $matches) ){
			return FALSE;
		}
		$mystr = preg_replace( $s1 , $s2 , $matches[0] );
		
		return $mystr;
	}
	
	/**
	 * 全部匹配
	 *
	 * @param string $s1
	 * @param string $s2
	 * @param string $str
	 * @return string $matches[0]
	 */
	protected function _match_all_str($s1,$s2,$str){
		if (is_array($str)){
			$str = $this -> _arr_to_onearr($str);
		}else{
			$str = array($str);
		}
		
		foreach($str as $mystr){
			if (!@preg_match_all($s1 , $mystr , $matches)){
				continue;
			}
			if (is_array($matches[0])){
				foreach ($matches[0] as $item){
					$mymatches[] = preg_replace($s1 , $s2 , $item);
				}
			}
		}
		return $mymatches;
	}
	
	/**
	 * 将一个多维数组转换成有固定格式的一维的数组
	 * @param $str 传入的数组
	 * @param $addstr 处理多维数组用的前置添加字符串
	 * @param $newarr 待返回的数组，采用引用方式
	 * @return array $newarr 一维数组 
	 */
	protected function _arr_to_onearr($str,$addstr='', &$newarr=array()){
		foreach ($str as $key => $value){
			$newstr = $addstr . $key.' => ';
			if (is_array($value)){
				$this -> _arr_to_onearr($value,$newstr,$newarr);
			}else{
				$newarr[] = $newstr.$value;
			}
		}
		return $newarr;
	}
	
	
	protected function _do_filiter($key,$filiter_set,$url){
		if (!is_array($filiter_set))return;
		
		if ($filiter_set['doid']=='-1'){
			$str = $this->my_html;
		}elseif ($filiter_set['doid']=='-2'){
			$str = $url;
		}else{
			if (isset($this->my_filiter[$filiter_set['doid']])){
				//已存在
				$str = $this->my_filiter[$filiter_set['doid']];
				
			}elseif ($this->node['filter'][$filiter_set['doid']]){
				//父过滤项先执行
				$this->_do_filiter( $filiter_set['doid'] ,$this -> node['filter'][$filiter_set['doid']] ,$url );
				$str = $this -> my_filiter[$filiter_set['doid']];
				
			}else{
				return;
			}
		}
		
		
		//将匹配到的内容过滤掉
		$this -> my_filiter[$key] = preg_replace( $filiter_set['preg'], $filiter_set['replace'], $str );
		
		
		//处理接口函数
		if ($filiter_set['api'])
			$this -> _do_api( $this -> my_filiter[$key] , $filiter_set['api'] );
	}
	
	/**
	 * 处理接口函数
	 * @param $api 接口函数名称
	 * @return none
	 */
	protected function _do_api( &$str, $apifun ){
		if ($apifun){
			$this->apilist or $this->apilist = (array)get_class_methods('Acquisition_Api');
			if (in_array($apifun,$this->apilist)){
				$this ->apifun or $this ->apifun = new Acquisition_Api();
				$this ->apifun -> $apifun( $str );
			}
		}
		return $str;
	}
	
	
	/**
	 * 执行另一节点
	 *
	 */
	protected function _run_other_acqu($nextnode){
		if (!$nextnode['id']||!$nextnode['key'])return false;
		$get = array();
		$get['timeline'] = time();
		$get['code'] = $this->_get_url_code($get['timeline'],$nextnode['key']);
		$get['_dotype'] = 'del';
		$get['id'] = $this -> id;
		$get['nodeid'] = $nextnode['id'];
		$get['adminid'] = $this -> adminid;
		$get['dotime'] = $this->dotime;
		
		//创建URL
		
		$url = Myqee::url('acquisition_run/index',null,true).'?'.http_build_query($get,NULL,'&');
		
		$this -> snoopy -> fetch($url,$_SERVER["SERVER_ADDR"]);
		
		return true;
	}
	
	/**
	 * 所有组执行完后执行
	 *
	 */
	protected function _do_all_over(){
		
		if (!$this->node){
			return false;
		}
		//删除运行标志文件
		if (@unlink ($this->runfile)) {
			$this->_log('info',"删除本节点运行标志文件，本节点采集结束。");
		}
		if ($this->node['doalltype']==1){
			//执行入库，完成后结束任务
			return $this -> _info_to_database();
		}
		
		if ($this->node['doalltype']==2){
			//执行入库,完成后执行另一节点
			return $this -> _info_to_database(null,$this->node['doallnext_node']);
		}
	
		if ($this->node['doalltype']==3){
			//执行入库,同时执行另一节点
			$this -> _info_to_database(null,$this->node['doallnext_node']);
		}

		if($this->node['doalltype']==3 || $this->node['doalltype']==4){
			//执行另一节点
			

			if ($this->node['doallnext_node']){
				if (!$nextnode = $this->config['node']['node_'.$this->node['doallnext_node']]){
					$this->_log('error','任务全部执行完成后执行:'.$this->node['doallnext_node'].',但此节点不存在！');
				}else{
					//N组完成后，启动另一采集
					$this -> _log('info','本次采集完成，自动启动另一节点('.$nextnode['id'].')：'.$nextnode['name'].'，<a href="'.Myqee::url('index/logs_view?log=acqu_'.$this->id.'_'.$nextnode['id'].'_'.date("Y-m-d").'-'.$this->dotime.'.log.txt&renewtime=2').'">点击查看状态</a>');
					$this -> _run_other_acqu($nextnode);
				}
			}
		}
		
		$this->_log('success','本次采集完成！');
		
		return TRUE;
	}
	
	/**
	 * 执行入库操作
	 *
	 * @param array $data_ids 采集临时数据表的ID集
	 * @param int $run_nextnodeid 需要执行的下一个节点ID
	 */
	protected function _info_to_database($data_ids=NULL,$run_nextnodeid=0){
		
		$post = array();
		
		if ($data_ids){
			$post['dataids'] = $data_ids;
			$keycode = md5(Myqee::config('encryption.default.key').'__'.$post['dataids']);
		}else{
			$post['id'] = $this -> id;
			$post['nodeid'] = $this -> nodeid;
			
			$keycode = $this -> node['key'];
		}
		
		$post['timeline'] = time();
		$post['adminid'] = $this->adminid;
		$post['code'] = $this->_get_url_code($post['timeline'],$keycode);
		$post['dotime'] = $this->dotime;
		
		
		//创建URL
		$url = Myqee::url('acquisition_run/to_dbdata',null,true);
		$snoopy = new Snoopy;
		$snoopy -> ip = $_SERVER["SERVER_ADDR"];
		$snoopy -> getresults = false;
		
		$this -> _log('info','准备开始自动入库数据，任务：'.$this->config['name'].'，节点'.$this->node['name'].'！');
		
		$snoopy -> submit($url,$post);
	}
	
	/**
	 * 入库控制器
	 * 
	 * @return unknown_type
	 */
	public function to_dbdata(){
		if ($_SERVER['REQUEST_TIEM'] - $_POST['timeline'] > 1200){
			//>20分钟
			$this -> _log('error','入库操作超时！入库程序退出！');
			$this -> _header('REQUEST EXPRIED');
		}
		
		//检测当前管理员是否有对应数据表管理的权限
		$this -> adminid = $_POST['adminid'];
		if (!($this -> adminid>0)){
			$this -> _log('error','没有被授权的请求！入库程序退出！');
			$this -> _header('UNAUTHORIZED');
		}
		
		//检查管理员是否具有数据库入库权限
		if (!Passport::getisallow('task.acquisition_datatodb',$this->adminid)){
			$this -> _log('error','管理员(ID:'.$this->adminid.')没有拥有采集入库的权限！入库程序退出！');
			$this -> _header('ADMIN UNAUTHORIZED');
		}
		
		if ($_POST['id']>0&&$_POST['nodeid']>0){
			//指定节点的全部
			$type=1;
			
			$this->id = (int)$_POST['id'];
			$this->nodeid = (int)$_POST['nodeid'];
			
			//读取配置
			$this->config = Myqee::config('acquisition/acqu_'.$this->id);
			if (!is_array($this->config)){
				$this -> _log('error','没有找到任务！入库程序退出！');
				$this -> _header('NO ACQUISITON');
			}
			
			if (!$this->config['isuse']){
				$this -> _log('error','任务没有启用！入库程序退出！');
				$this -> _header('ACQUISITON NO USE');
			}
			
			$this->node = $this->config['node']['node_'.$this->nodeid];
			if (!is_array($this->node)){
				$this -> _log('error','没有找到指定的节点！入库程序退出！');
				$this -> _header('NO NODE');
			}
			
			if (!$this->node['isuse']){
				$this -> _log('error','指定的节点没有启用！入库程序退出！');
				$this -> _header('NODE NO USE');
			}
			
			$keycode = $this->node['key'];
			
		}else{
			$type=2;
			if ($_POST['dataids']){
				$dataids = Tools::formatids($_POST['dataids'],true);
				$keycode = md5(Myqee::config('encryption.default.key').'__'.$_POST['dataids']);
			}else{
				//phpinfo();
				$this -> _log('error','请求的参数错误！入库程序退出！');
				$this -> _header('ERROR QUERY STRING');
			}
		}
		
		//校验密钥
		if ($this->_get_url_code($_POST['timeline'],$keycode)!=$_POST['code']){
			$this -> _log('error','请求的参数异常！入库程序退出！');
			$this -> _header('ERROR CODE');
		}
		
		$this -> db = new Database();
		
		if ($type==1){
			//入库指定节点数据
			
			//检查是否具有栏目管理权限
			if ($this->config['classid'] && !Passport::getisallow_class($this->config['classid'],$this->adminid)){
				$this -> _log('error','管理员(ID:'.$this->adminid.')没有拥有栏目(ID:'.$this->config['classid'].')管理权限！入库程序退出！');
				$this -> _header('ADMIN UNAUTHORIZED CLASS');
			}
			
			//检查是否具有数据表管理权限
			if (!Passport::getisallow_db($this->config['dbname'],$this->adminid)){
				$this -> _log('error','管理员(ID:'.$this->adminid.')没有拥有数据表'.$this->config['dbname'].'管理权限！入库程序退出！');
				$this -> _header('ADMIN UNAUTHORIZED DATABASE');
			}
			
			$where = array('acqu_id'=>$this->id,'node_id'=>$this->nodeid,'is_todb'=>0);
			
			if ($_POST['info_id']>0){
				$where['id'] = (int)$_POST['info_id'];
			}
			$total = $this -> db ->count_records('[acquisition_data]',$where);
			$data = $this -> db -> from('[acquisition_data]');
			if ($limit = $this->node['tohtml_autonum']){
				$data = $data -> limit($limit,0);
			}
			$data = $data -> where($where) -> get() -> result_array(false);
		}else{
			$data = $this -> db -> from('[acquisition_data]')->in('id',$dataids) -> get() -> result_array(false);
		}
		foreach ($data as $item){
			if ($type!=1){
				$this->id = (int)$item['acqu_id'];
				$this->nodeid = (int)$item['node_id'];
			}
			$info_data = unserialize($item['info_content']);
			$dbname = $item['dbname'];
			$classid = $item['class_id'];
			$this -> dotime = $item['dotime'];
			if (is_array($info_data) && $dbname){
				if (!count($info_data)){
					$this -> _log('info','读取采集信息ID:'.$item['id'].'内容为空，放弃入库。');
					$set = array('is_todb'=>-1,'mydb_id'=>0);
				}else{
					$id = $this -> _to_user_data($dbname,$info_data,$classid,$item['mydb_id']);
					if ($id>0){
						$set = array('is_todb'=>1,'mydb_id'=>$id);
						$this -> _log('success','成功入库一信息('.$item['id'].')，信息ID:'.$id.'数据表:'.$dbname.'，栏目ID:'.$classid.'。');
					}else{
						$set = array('is_todb'=>-2,'mydb_id'=>0);
						$this -> _log('info','入库信息异常('.$item['id'].')，未得到信息ID:。数据表:'.$dbname.'，栏目ID:'.$classid.'。');
					}
				}
				$this -> db -> update('[acquisition_data]',$set,array('id'=>$item['id']));
			}
		}
		
		echo 'OK';
		
		if ($type==1 && $total>$limit){
			if ($this->node['tohtml_limitnum']>0){
				//暂停
				usleep($this->node['tohtml_limitnum']*1000);
			}
			//启动下一页
			$this -> _info_to_database();
		}
	}
	
	
	protected function _to_user_data($dbname,$data,$classid=0,$db_id=0){
		
		$this -> dbconfig or $this -> dbconfig = Myqee::config('db/'.$dbname);
		
		//处理栏目ID和栏目名称
		if ($classid>0){
			if ($classid_field = $this -> dbconfig['sys_field']['class_id']){
				$data[$classid_field] = $classid;
			}
			$classname_field = $this -> dbconfig['sys_field']['class_name'];
			if ($classname_field && !$data[$classname_field]){
				if (!isset($this -> classarr[$classid])){
					$this -> classarr[$classid] = Myqee::config('class/class_'.$classid);
				}
				if ($this -> classarr[$classid]){
					$data[$classname_field] = $this -> classarr[$classid]['classname'];
				}
			}
		}
		
		//添加信息ID
		if ($db_id>0){
			if ($id_field = $this -> dbconfig['sys_field']['id']){
				$data[$id_field] = $db_id;
			}
		}
		$_db = Database::instance($this->dbconfig['database']);
		$extenddata = array();
		//key 是字段名称
		foreach ($data as $key=>$val) {
			if (ereg('/',$key) && ereg('.',$key)) {
				list($_database,$_table,$_field) = preg_split('#[/.]#',$key,3);
				$extenddata[$_database][$_table][$_field] = $val;
				unset($data[$key]);
			}
		}
		//主表
		$id = $_db -> merge($this->dbconfig['tablename'],$data) -> insert_id();
		//副表
		foreach ($extenddata as $_database=>$_tables) {
			$_db = Database::instance($_database);
			foreach ($_tables as $_table=>$_fields) {
				$_config = 	Myqee::config('db/'.$_database.'/'.$_table);
				if (empty($_config) || empty($_config['sys_field']['id'])) {
					continue ;
				}
				$_fields[$_config['sys_field']['id']] = $id;
				$_db->merge($_table,$_fields);	
			}
		}
		
		if ($id>0 && $classid>0 && $this->node['is_autotohtml']){
			//入库完自动发布
			$this -> _auto_to_html($classid,$id);
		}
		
		return $id;
	}
	
	protected function _auto_to_html($classid,$id){
		$url = _get_tohtmlurl('toinfo_byid',Myqee::config('encryption.default.key'),'id='.(int)$id.'&allclassid='.$classid.'&nowclassid='.$classid.'&limit=1');
		if (substr($url,0,1)=='/'){
			$url = Myqee::protocol() .'://'. $_SERVER['HTTP_HOST'] . $url;
		}
		$this -> snoopy or $this -> snoopy = new Snoopy;
		$this -> snoopy -> fetch($url,$_SERVER['SERVER_ADDR']);
		$response = $snoopy -> results;
		
		if ($response[0]=='{'||$response[0]=='['){
			$info = Tools::json_decode($response);
			if (is_array($info)){
				if ($info['dook']>=1){
					$message = '恭喜，保存成功并已生成静态页！';
					$fun = 'show_ok';
				}else{
					$message = '数据保存成功，但未生成任何静态页！';
					$fun = 'show_info';
				}
			}else{
				$message = '数据已保存，但生成静态页时发生异常，请联系管理员！';
				$fun = 'show_error';
			}
		}else{
			$message = '数据已保存，但生成静态页时发生异常，请联系管理员！';
			$fun = 'show_error';
		}
	}
	
	
	/**
	 * 记录LOG
	 *
	 * @param string $info
	 * @param string $type
	 * @return boolean true/false
	 */
	protected function _log($type,$info){
		$file = MYAPPPATH . 'logs/acqu_'.$this->id.'_'.$this->nodeid.'_'.date("Y-m-d").'-'.$this->dotime.'.log.txt';

		if ( $fp = @fopen($file,'a') ){
			$fw = @fwrite($fp,date('Y-m-d H:i:s P').' --- '.$type.':'.$info.PHP_EOL);
			@fclose($fp);
			return $fw;
		}
		
		return FALSE;
	}
	
	
	protected function _header($info){
		header('MyqeeInfo:'.urlencode($info));
		echo $info;
		exit;
	}
	
}

