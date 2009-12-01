<?php
/**
 * $Id: plugins.php,v 1.17 2009/10/26 08:53:52 songwubin Exp $
 *
 * @package    Plugins Controller
 * @author     Myqee Team
 * @copyright  (c) 2008-2010 Myqee Team
 * @license    http://myqee.com/license.html
 */
class Plugins_Controller_Core extends Controller {

	protected $config = NULL;

	function __construct() {
		parent::__construct();
		$this -> session = Passport::chkadmin();
	}

	public function index() {
		Passport::checkallow('plugins.list');
		
		$view = new View('admin/plugins_index');

		$view -> set('plugins', $this -> _get_plugins_dir() );
		$view -> render(TRUE);
	}

	public function main(){
		Passport::checkallow('plugins.list',NULL,TRUE);
		$view = new View('admin/plugins_main');
		$view -> set('plugins', Tools::json_encode($this -> _get_plugins_dir(true)));
		//$view -> set('list',$result);
		$view -> render(TRUE);
	}
	
	public function main_save(){
		Passport::checkallow('plugins.edit',NULL,TRUE);
		$post = $_POST['plugins'];
		$plugins_dir = $this -> _get_plugins_dir(true);
		
		$data = array();
		foreach ($post as $k => $item){
			if($plugins_dir[$k]){
				$item['name'] = Tools::formatstr($item['name'],50,0,0,0,0,0);
				if(empty($item['name']))$item['name'] = $k;
				//保存一些系统需要的配置到plugin总的配置文件
				include (MYQEEPATH."admin/plugins/{$k}/config/config.php");
				$data[$k] = array(
					'name' => $item['name'],
					'isuse' => $item['isuse']?true:false,
					'detailconfig' => $config,
				);
			}
		}
		
		MyqeeCMS::saveconfig('plugins',$data);
		
		MyqeeCMS::show_ok('恭喜，保存成功！',true,'refresh');
	}
	
	public function config($plugins=''){
		Passport::checkallow('plugins.edit',NULL,$_GET['fullpage']=='yes'?FALSE:TRUE);
		$this -> plugins = $plugins;
		if (!$path = $this->_get_plugins()) {
			MyqeeCMS::show_error('不存在指定的插件！',true,'goback');
		}
		$p = Plugins::instance($plugins);
		
		$config = Plugins::config();
		//$this -> _config('core.name');
		
		$config_set = $this -> _config('config_set');
		
		if ($config_set && is_array($config_set)){
			
			$html = form::outhtml($config_set,$config,'config');
			
			$view = new View('admin/plugins_config');
			
			$view -> set('plugins',$plugins);
			$view -> set('user_editinfo_formhtml',$html);
			
			$view -> render(true);
		}
	}
	
	
	public function config_save($plugins=''){
		Passport::checkallow('plugins.edit',NULL,TRUE);
		$this -> plugins = $plugins;

		if (!$path = $this->_get_plugins()) {
			MyqeeCMS::show_error('不存在指定的插件！',true,'goback');
		}
		
		$s_config = $this -> _config('core');
		
		$my_config = array_merge_recursive($s_config,$_POST['config']);
		
		if (MyqeeCMS::saveconfig('plugins/'.$plugins,$my_config)){
			MyqeeCMS::show_ok('插件配置保存成功！',true);
		}else{
			MyqeeCMS::show_error('插件配置保存失败，可能您没有文件操作权限！',true);
		}
	}

	public function ajax_getmenu($plugins=''){
		if (!Passport::getisallow('plugins.list')){
			echo '{"error":"没有权限管理插件！"}';
		}
		$this -> plugins = $plugins;

		if (!$path = $this->_get_plugins()) {
			MyqeeCMS::show_error('不存在指定的插件！',true,'goback');
		}
		$config_menu = $this -> _config('menu');
		
		echo Tools::json_encode($config_menu);
	}
	
	public function run($plugins=''){
		if (isset($_GET['fullpage']) && $_GET['fullpage']=='yes'){
			$showheader = true;
		}else{
			$showheader = false;
		}
		Passport::checkallow('plugins.list',NULL,$showheader?FALSE:TRUE);
		$this -> plugins = $plugins;

		if (!$path = $this->_get_plugins()) {
			MyqeeCMS::show_error('不存在指定的插件！',$showheader?FALSE:TRUE,'goback');
		}
		
		$plugins_config = Myqee::config('plugins.'.$plugins);
		if (!$plugins_config || !$plugins_config['isuse']){
			MyqeeCMS::show_error('指定的插件未启用！',$showheader?FALSE:TRUE,'goback');
		}
		
		Plugins::instance($plugins);
		
		//定义插件路径
		define('PLUGINS_PATH',$plugins);
		
		$result = myqee_root::sub_controller(true,$path,false,1);
		
		if (defined('PLUGINS_SHOWSELF') && PLUGINS_SHOWSELF==true){
			while ( ob_get_level() ) {
				ob_end_clean ();
			}
			echo $result;
			return true;
		}
		
		View::factory('admin/'.($showheader?'header':'header_frame'),array('page_title'=>'插件管理：'.$plugins_config['name'],'page_index'=>'plugins')) -> render(true);
		if ( $result ){
			echo $result;
		}else{
			echo '<br/><br/><table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder"><tr><th class="td1" colspan="2">错误提示</th></tr><tr><td align="center" class="td1" width="100"><img src="'.ADMIN_IMGPATH.'/admin/error.gif" /></td><td class="td2"><div style="padding:20px 40px;">您访问的插件页面不存在！</div></td></tr></table><br/><br/>';
		}
		if ($showheader){
			View::factory('admin/footer') -> render(TRUE);
		}else{
			echo "\r\n\r\n\r\n<script type=\"text/javascript\">\r\nmyqee();\r\nchangeHeight();\r\n</script>\r\n</body>\r\n</html>";
		}
		
		return true;		
	}

	protected function _config($myconfig) {
		$c = explode ( '.', $myconfig );
		$cname = array_shift ( $c );
		if ($cname=='core')$cname='config';
		if (isset($this -> config[$cname])) {
			$config = $this -> config[$cname];
		}else{
			$allpath = $this->_get_plugins();
			arsort($allpath);
	
			if(!is_array($allpath)){
				$this -> config[$cname] = false;
				return FALSE;
			}
	
			$config = null;
			foreach ($allpath as $path) {
				if (file_exists($path.'config/'.$cname.EXT)) {
					include $path.'config/'.$cname.EXT;
				}
			}
			$this -> config[$cname] = $config;
		}
		$v = $config;
		foreach ( $c as $i ) {
			$v = $v [$i];
		}
		return $v;
	}

	protected function _get_plugins_dir($getarr=false){
		$pluginsdir = array_merge(
			(array) MyqeeCMS::dirlist(MYQEEPATH . 'admin/plugins/', 'dir'),
			(array) MyqeeCMS::dirlist(ADMINPATH .'plugins/', 'dir')
		);
		$plugins_config = (array) Myqee::config('plugins');
		if($getarr==true){
				$data = $plugins_config;
		}else{
			foreach ($plugins_config as $key => $value) {
				if ($value['isuse']){
					$data[$key] = $value['name'];
				}else{
					unset($pluginsdir[$key]);
				}
			}
		}
		if ($pluginsdir){
			foreach ($pluginsdir as $value){
				if (!isset($data[$value])){
					if($getarr==true){
						$data[$value] = array('name'=>$value,'isuse'=>fales);
					}else{
						$data[$value] = $value;
					}
				}
			}
		}
		return $data;
	}


	protected function _get_plugins($plugins=null) {
		$plugins or $plugins = $this -> plugins;
		if (!$plugins)return false;
		
		static $pluginspath = null;
		if ($pluginspath!==null)return $pluginspath;

		

		$pluginspath = array();
		if(is_dir($path = ADMINPATH.'plugins/'.$plugins.'/')) {
			$pluginspath['q'] = $path;
		}
		if(is_dir($path = MYQEEPATH.'admin/plugins/'.$plugins.'/')) {
			$pluginspath['p'] = $path;
		}
		return $pluginspath;
	}
	
	
	/**
	 * 创建插件安装文件
	 * @param $plugins 插件目录
	 * @return none
	 */
	public function createsetupfile($plugins=''){
		Passport::checkallow('plugins.createsetupfile',NULL,$_GET['fullpage']=='yes'?FALSE:TRUE);
		$this->zip = new Zip();
		
		if (!$plugins){
			MyqeeCMS::show_error('缺少参数！');
		}
		if ($this->_get_plugins($plugins)==false){
			MyqeeCMS::show_error('指定的插件不存在！');
		}
		$pass = $_GET['pass']?$_GET['pass']:'';
		$pass .= 'myqee.com';
		
		$this->zip->add_dir('Powered by www.myqee.com');
		
		//用户自定义参数
		$myconfig = array(
			'vision'	=> MYQEE_VERSION,
			'host'		=> $_SERVER['HTTP_HOST'],
			'time'		=> time(),
			'config'	=> Myqee::config('plugins/'.$plugins),
			'path'		=> $plugins,
		);
		$myconfig = Tools::info_encryp(serialize($myconfig),$pass);
		$this->zip->add_file($myconfig,'myconfig.txt');
		
		//用户后台插件目录
		if(is_dir($path = ADMINPATH.'plugins/'.$plugins.'/')) {
			$this -> _read_dir($path,'admin');
		}
		//用户前台插件目录
		if(is_dir($path = MYAPPPATH.'plugins/'.$plugins.'/')) {
			$this -> _read_dir($path,'myapp_plugins');
		}
		//系统后台插件目录
		if(is_dir($path = MYQEEPATH.'admin/plugins/'.$plugins.'/')) {
			$this -> _read_dir($path,'admin_plugins');
		}
		//系统目录插件目录
		if(is_dir($path = MYQEEPATH.'plugins/'.$plugins.'/')) {
			$this -> _read_dir($path,'myqee_plugins');
		}
		//前台资源文件目录
		if(is_dir($path = WWWROOT.'images/plugins/'.$plugins.'/')) {
			$this -> _read_dir($path,'wwwroot');
		}
		//后台资源文件目录
		if(is_dir($path = dirname($_SERVER['SCRIPT_FILENAME']).'/images/admin/plugins/'.$plugins.'/')) {
			$this -> _read_dir($path,'admin_wwwroot');
		}
		
		$content = $this->zip->get_file();
		//全部内容进行加密效率比较低下，故采用局部加密的方式
		$encode = gzcompress(Tools::info_encryp(substr($content,-10240),$pass,true,0),9);
		$content = $encode.substr($content,0,-10240).Des::Encrypt(str_pad(strlen($encode),10),$pass);
		
		file_put_contents(WWWROOT.'txt.plugins',$content);
		
		MyqeeCMS::show_ok('恭喜，插件打包成功，点击下载！');
	}
	
	
	protected function _read_dir($dirName,$filestr=''){
		$handle = opendir($dirName);
		if (!$handle)return false;
		$i = 0;
		$nolistfile = array('.','..','.svn','CVS');
		if ($filestr)$this->zip->add_dir($filestr.'/');
		while(($file = readdir($handle)) !== false)
		{
			if(!in_array($file,$nolistfile))
			{
				$dir = $dirName . '/' . $file;
				$newfilestr = ($filestr?$filestr.'/':'').$file;
				if (is_dir($dir)){
					$this->_read_dir($dir,$newfilestr);
				}else{
					$this->zip -> add_file(file_get_contents($dir),$newfilestr);
				}
			}
		}
		closedir($handle);
	}

	public function setup() {
		Passport::checkallow('plugins.setup',NULL,$_GET['fullpage']=='yes'?FALSE:TRUE);
		$view = new View('admin/plugins_setup');

		$dir = MYAPPPATH .'temp/';
		$dirlen = strlen($dir);
		$filesarr = array();

		foreach (glob($dir."*.plugins") as $key => $file) {
			$filename = substr($file,$dirlen,-8);
			$filesarr[$filename] = $filename .'.plugins ('.date("Y-m-d H:i:s",filemtime($file)).')';
		}

		$view -> set('files',$filesarr);
		$view -> render(TRUE);
	}

	public function setup_save() {
		Passport::checkallow('plugins.setup',NULL,TRUE);
		$view = new View('admin/plugins_setup');
		$tmpdir = MYAPPPATH .'temp/';
		$post = $_POST;
		$filetype = $post['filetype'];
		$pass = $post['pass']?$post['pass']:'';
		$pass .= 'myqee.com';
		
		$path = preg_match("/[a-z0-9_]/i",$post['path'])?$post['path']:'';
		
		if ($filetype == 'localfile'){
			$file = $_FILES['upfile'];
			if (!$file['name']){
				MyqeeCMS::show_error('请上传插件安装文件！',true);
			}
			$path_info = pathinfo($file['name']);
			$file_extension = strtolower($path_info["extension"]);

			if($file_extension != 'plugins'){
				MyqeeCMS::show_error('文件类型不符，必须是.plugins文件！',true);
			}
			
			$setupfile = $tmpdir.strtolower($path_info["basename"]);
			if(!@move_uploaded_file($file['tmp_name'], $setupfile )){
				MyqeeCMS::show_error('上传文件失败，可能临时文件目录没有写入权限！',true);
			}
			if (!file_exists($setupfile)||filesize($setupfile)==0){
				MyqeeCMS::show_error('抱歉，上传文件失败或文件为空！',true);
			}
		}elseif($filetype == 'remotefile'){
			if(!$post['url'] || $post['url']=='http://'){
				MyqeeCMS::show_error('请输入远程安装文件地址！',true);
			}
			if(!preg_match("#^(http|ftp|https)://[\w.]+#i", $post['url'])){
				MyqeeCMS::show_error('远程文件地址输入不正确！',true);
			}
			$setupfile = $tmpdir.md5($post['url']).'.plugins';
			if (file_exists($setupfile) &&$post['redownload']!='down'){
				if ($post['redownload']=='ok'){
					@unlink($setupfile);
				}else{
					MyqeeCMS::show_info(
					array(
						'message'=>'指定的文件在服务器上已有下载，是否重新下载？',
						'btn'=>array(
							array('重新下载','ok'),
							array('断点下载','down'),
							array('取消','cancel'),
							),
						'handler' => 'function(el){if(el=="ok"||el=="down"){parent.$("redownload").value=el;parent.document.forms["myforms"].submit();}else{parent.$("redownload").value="";}}',
						)
					,true);
				}
			}
			$snoopy = new Snoopy();
			
			$snoopy -> fetch($post['url'],null,$setupfile);
			
			if (!file_exists($setupfile)||filesize($setupfile)==0){
				MyqeeCMS::show_error('抱歉，远程地址获取失败！',true);
			}
		}else{
			$setupfile = $tmpdir.$post['server_file'].'.plugins';
			if (!file_exists($setupfile)||filesize($setupfile)==0){
				MyqeeCMS::show_error('抱歉，指定的文件不存在可能已被删除！',true);
			}
		}
		
		if ($post['md5'] && md5_file($setupfile)!=$post['md5']){
			MyqeeCMS::show_error('抱歉，MD5校验错误，可能文件已被修改！',true);
		}

		set_time_limit(0);
		ignore_user_abort(true);
		
		//解压缩zip文件
		$this->temppath = $this->_extract_file($setupfile,$pass);
		
		$configfile = $this->temppath.'myconfig.txt';
		
		if (!file_exists($configfile)){
			Tools::remove_dir($this->temppath);
			MyqeeCMS::show_error('抱歉，安装文件文件缺失配置文件！',true);
		}
		
		$myconfig = unserialize(Tools::info_uncryp(file_get_contents($configfile),$pass));
		if (!$myconfig || !is_array($myconfig)){
			Tools::remove_dir($this->temppath);
			MyqeeCMS::show_error('抱歉，安装配置文件错误！',true);
		}
		//安装路径
		if (empty($path)){
			$path = preg_replace("/[^a-z0-9_]/i",'',$myconfig['path']);
		}
		if (!$path){
			MyqeeCMS::show_error('抱歉，指定安装路径错误！',true);
		}
		
		if ($this -> _get_plugins($path)){
			//存在相同插件，等待用户确认
			$this -> _setup_chk_continue(array($path,$post['pass'],$post['isdel'],$_SERVER['REQUEST_TIME'],0,$setupfile));
		}
		
		if ($myconfig['vision']!=MYQEE_VERSION){
			//版本不一致
			$this -> _setup_chk_continue(array($path,$post['pass'],$post['isdel'],$_SERVER['REQUEST_TIME'],1,$setupfile),$myconfig['vision']);
		}
		
		$this -> _setup_copyfiles($myconfig,$path,$post['isdel']?$setupfile:false);
	}
	
	protected function _setup_copyfiles($myconfig,$plugins,$delsetupfile){
		//用户后台插件目录
		if(is_dir($path = $this->temppath .'admin/')) {
			Tools::move_dir($path,ADMINPATH.'plugins/'.$plugins.'/');
		}
		//用户前台插件目录
		if(is_dir($path = $this->temppath .'myapp_plugins/')) {
			Tools::move_dir($path,MYAPPPATH.'plugins/'.$plugins.'/');
		}
		//系统后台插件目录
		if(is_dir($path = $this->temppath .'admin_plugins/')) {
			Tools::move_dir($path,MYQEEPATH.'admin/plugins/'.$plugins.'/');
		}
		//系统目录插件目录
		if(is_dir($path = $this->temppath .'myqee_plugins/')) {
			Tools::move_dir($path,MYQEEPATH.'plugins/'.$plugins.'/');
		}
		//前台资源文件目录
		if(is_dir($path = $this->temppath .'wwwroot/')) {
			Tools::move_dir($path,WWWROOT.'images/plugins/'.$plugins.'/');
		}
		//后台资源文件目录
		if(is_dir($path = $this->temppath .'admin_wwwroot/')) {
			Tools::move_dir($path,dirname($_SERVER['SCRIPT_FILENAME']).'/images/admin/plugins/'.$plugins.'/');
		}
		
		if ($myconfig['config']){
			MyqeeCMS::saveconfig('plugins/'.$plugins,$myconfig['config']);
		}
		
		Tools::remove_dir($this->temppath);
		
		if ($delsetupfile){
			@unlink($delsetupfile);
		}
		MyqeeCMS::show_ok('恭喜，插件安装成功！',true);
	}
	
	protected function _setup_chk_continue($params,$m=''){
		//$path,$pass,$time,$isdel,$chkstep
		$this -> session -> set_flash('plugins_setup',$params);
		$msg = array(
			'安装目录已存在插件，继续安装将覆盖原先插件，是否继续？',
			'安装程序版本('.$m.')与当前('.MYQEE_VERSION.')不符，是否继续？',
		);
		$now = time();
		Myqee::run_in_system('<script>parent.window.confirm("'.$msg[$params[4]].'<br/>（1分钟内确认有效）",450,null,"请确认",function(el){if(el=="ok"){document.location.href="'.Myqee::url('plugins/setup_contioue/'.$now.'/1').'";}else{document.location.href="'.Myqee::url('plugins/setup_contioue/'.$now.'/0').'";}});</script>');
		//等待客户端确认
		$donext=false;
		$i=0;
		$tmpf = MYAPPPATH.'temp/plugins_do_continue_'.$now;
		do{
			sleep(1);
			$i++;
			if (file_exists($tmpf)){
				//接受到客户端操作请求
				$donext = true;
				if (file_get_contents($tmpf)=='donext'){
					//继续操作
					@unlink($tmpf);
				}else{
					//客户端未确认或超过60秒没确认，取消
					Tools::remove_dir($this->temppath);
					if($params[2])@unlink($params[5]);
					@unlink($tmpf);
				}
				exit;
			}
		}while($i<65);
		
		if (!$donext){
			Tools::remove_dir($this->temppath);
			if($params[2])@unlink($params[5]);
		}
		exit;
	}
	
	public function setup_contioue($runtime=0,$type=''){
		Passport::checkallow('plugins.setup',NULL,TRUE);
		if (!$runtime>0||$_SERVER['REQEUST_TIME']-$runtime>65){
			MyqeeCMS::show_error('操作已超时，请重新安装！',true);
		}
		$params = $_SESSION['plugins_setup'];
		if (!$params || !is_array($params)){
			MyqeeCMS::show_error('缺少参数！',true);
		}
		list($path,$pass,$time,$isdel,$chkstep,$setupfile) = $params;
		$pass .= 'myqee.com';
		
		$path = preg_match("/[a-z0-9_]/i",$path['path'])?$path['path']:'';
		
		$this->temppath = MYAPPPATH.'temp/'.md5('plugins_'.$time).'/';
		if (!is_dir($this->temppath)){
			MyqeeCMS::show_error('没有找到临时文件，可能已删除。请重新安装!'.$this->temppath,true);
		}
		set_time_limit(0);
		ignore_user_abort(true);
		file_put_contents(MYAPPPATH.'temp/plugins_do_continue_'.$runtime,$type==1?'donext':'');
		
		
		$configfile = $this->temppath.'myconfig.txt';
		$myconfig = unserialize(Tools::info_uncryp(file_get_contents($configfile),$pass));
		if (!$myconfig || !is_array($myconfig)){
			Tools::remove_dir($this->temppath);
			MyqeeCMS::show_error('抱歉，插件配置文件错误！',true);
		}
		
		if ($chkstep==0){
			if ($myconfig['vision']!=MYQEE_VERSION){
				//版本不一致
				$this -> _setup_chk_continue(array($path,$pass,$isdel,$time,1,$setupfile),$myconfig['vision']);
			}
		}
		
		$this -> _setup_copyfiles($myconfig,$path,$isdel?$setupfile:false);		
	}

	
	
	public function uninstall($plugins = '') {
		Passport::checkallow('plugins.uninstall',NULL,TRUE);
		$this -> plugins = $plugins;

		if (!$path = $this->_get_plugins()) {
			MyqeeCMS::show_error('不存在指定的插件！',true);
		}
		
		set_time_limit(0);
		ignore_user_abort(true);
		
		//移除文件
		
		//用户后台插件目录
		if(is_dir($path = ADMINPATH.'plugins/'.$plugins.'/')) {
			$this->_remove_dir($path);
		}
		//用户前台插件目录
		if(is_dir($path = MYAPPPATH.'plugins/'.$plugins.'/')) {
			$this->_remove_dir($path);
		}
		//系统后台插件目录
		if(is_dir($path = MYQEEPATH.'admin/plugins/'.$plugins.'/')) {
			$this->_remove_dir($path);
		}
		//系统目录插件目录
		if(is_dir($path = MYQEEPATH.'plugins/'.$plugins.'/')) {
			$this->_remove_dir($path);
		}
		//前台资源文件目录
		if(is_dir($path = WWWROOT.'images/plugins/'.$plugins.'/')) {
			$this->_remove_dir($path);
		}
		//后台资源文件目录
		if(is_dir($path = dirname($_SERVER['SCRIPT_FILENAME']).'/images/admin/plugins/'.$plugins.'/')) {
			$this->_remove_dir($path);
		}
		
		MyqeeCMS::delconfig('plugins/'.$plugins);
		
		MyqeeCMS::show_ok('卸载成功!',true);
	}

	protected function _remove_dir($dirName)
	{
		if(!is_dir($dirName))
		{
			return false;
		}
		
		$handle = opendir($dirName);
		while(($file = readdir($handle)) !== false)
		{
			if($file != '.' && $file != '..')
			{
				$dir = $dirName . DIRECTORY_SEPARATOR . $file;
				is_dir($dir) ? $this->_remove_dir($dir) : @unlink($dir);
			}
		}
		closedir($handle);
		@rmdir($dirName);
	}

	/**
	 * 解析插件安装文件
	 * @param $zip_file 文件路径
	 * @param $pass 解析密码
	 * @return $temp_path 临时目录路径
	 */
	protected function _extract_file($zip_file,$pass){
		$temp_path = MYAPPPATH.'temp/'.md5('plugins_'.$_SERVER['REQUEST_TIME']).'/';
		$content = file_get_contents($zip_file);
		$encodelen = (int)trim(Des::Decrypt(substr($content,-34),$pass));
		if (!$encodelen>0){
			MyqeeCMS::show_error('安装密码错误或文件不是符合要求！',true);
		}
		if (!$encode = @Tools::info_uncryp(gzuncompress(substr($content,0,$encodelen)),$pass)){
			MyqeeCMS::show_error('分析文件错误或文件不是相应格式(No.02)！',true);
		}
		$content = substr($content,$encodelen,-34).$encode;
		
		$tmpzip = MYAPPPATH.'temp/__'.md5(time().'temp').'.plugins.ext';
		file_put_contents($tmpzip,$content);

		Tools::create_dir($temp_path);
		$zip_obj = new Zip();
		if (@$zip_obj -> Extract($tmpzip,$temp_path)==-1){
			unlink($tmpzip);
			Tools::remove_dir($temp_path);
			MyqeeCMS::show_error('分析文件错误或文件不是相应格式(No.03)！',true);
		}
		
		unlink($tmpzip);
		return $temp_path;
	}
}
