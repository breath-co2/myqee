<?php defined ( 'MYQEEPATH' ) or die ( 'No direct script access.' );
class MyqeeCMS_Core {
	protected static $coreconfig = FALSE;
	protected static $config = FALSE;
	
	public function print_r($arr,$isexit=false){
		echo '<pre>';
		print_r($arr);
		echo '</pre>';
		if ($isexit)exit;
	}
	
	/**
	 * 
	 * @param $msg string
	 * @param $isInHiddenFrame true/false
	 * @param $gotoUrl refresh、goback、URL
	 * @return null
	 */
	public static function show_ok($msg = '', $isInHiddenFrame = false, $gotoUrl = false) {
		self::show_info ( $msg, $isInHiddenFrame, $gotoUrl , 'succeed');
	}
	
	/**
	 * 
	 * @param $msg string
	 * @param $isInHiddenFrame true/false
	 * @param $gotoUrl refresh、goback、URL
	 * @return null
	 */
	public static function show_error($msg = '', $isInHiddenFrame = false, $gotoUrl = false) {
		self::show_info ( $msg, $isInHiddenFrame, $gotoUrl , 'error');
	}
	
	/**
	 * 
	 * @param $msg string
	 * @param $isInHiddenFrame true/false
	 * @param $gotoUrl refresh、goback、URL
	 * @param $type alert/succeed/error
	 * @return null
	 */
	public static function show_info($msg = '', $isInHiddenFrame = false, $gotoUrl = false, $type ='alert') {
		if ($isInHiddenFrame) {
			self::_show_info_hiddenframe ( $msg, $gotoUrl , $type );
		} else {
			self::_show_info_selfframe ( $msg, $gotoUrl , $type );
		}
	}
	
	protected static function _show_info_selfframe($msg = '', $gotoUrl = false , $showtype = 'alert') {
		$view = new View('admin/show_message');
		
		if ($gotoUrl == 'refresh'){
			$gotoUrl = $_SERVER["SCRIPT_URI"];
		}elseif ($gotoUrl == 'goback'){
			$gotoUrl = $_SERVER['HTTP_REFERER'];;
		}
		if (!is_array($infoarr)){
			$infoarr = array('message'=>$infoarr);
		}
		
		$view -> set('message', $msg);
		$view -> set('forward', $gotoUrl);
		$view -> set('showtype', $showtype);
		
		$html = $view -> render(FALSE);
		
		echo $html;
		exit ();
	}

	protected static function _show_info_hiddenframe($infoarr = '', $gotoUrl = false, $type ='alert') {
		if (!is_array($infoarr)){
			$infoarr = array('message'=>$infoarr);
		}
		
		if (!$infoarr['handler']){
			if ($gotoUrl){
				if ($gotoUrl == 'refresh'){
					$gotoUrl = 'parent.location.href=parent.location.href';
				}elseif ($gotoUrl == 'goback'){
					$gotoUrl = 'parent.history.go(-1);';
				}else{
					$gotoUrl = 'parent.document.location="'.str_replace ( '"', '\"', $gotoUrl ).'";';
				}
				$infoarr['handler'] = 'function(){'.$gotoUrl.'}';
			}else{
				$infoarr['handler'] = 'function(){if(window.name=="hiddenFrame")document.location.href="'.ADMIN_IMGPATH.'/admin/block.html";}';
			}
		}
		echo '<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>信息提示</title>
</head>
<body>
<div style="font-size:12px;padding:10px;">
',$infoarr['message'],'
<br/><br/>
<br/><br/><a href="',Myqee::url('index'),'" target="_top" style="font-size:12px;color:#000;">点击返回管理首页</a>
</div>
<script type="text/javascript">
	//解决FF里只能提交一次的BUG
	try{
		var allforms = parent.document.forms;
		for(var ii=0;ii<allforms.length;ii++){
			allforms[ii].target=allforms[ii].target||"";
		}
	}catch(e){}

	window._alert = window.alert;
	window.alert= function(runset) {
		if (runWindow!=window.self){
			return runWindow.alert(runset);
		}
		_alert(runset.message);
		if (runset.handler){
			try{runset.handler("ok")}catch(e){}
		}
	}
	window.error = window.alert;
	window.succeed = window.alert;
	
	var runWindow = window.self;
	try{
		if (typeof(parent.',$type,')=="function"){
			runWindow = window.parent;
		}
	}catch(e){}
	var runset=',Tools::json_encode($infoarr),';
	runset["message"] = runset["message"] || "";
	runset["width"] = runset["width"] || 400;
	if(runset["handler"]){
		try{
			runWindow.eval(\'runset["handler"] = \' + runset["handler"]);
		}catch(e){
			runset["handler"] = null;
		}
	}
	runWindow.',$type,'(runset);
</script>
</body>
</html>
';
		exit;
	}
	
	/*
	public static function config($myconfig){
		$c = explode('.',$myconfig);
		if ($c[0] == 'core'){
			return Myqee::config($myconfig);
		}
		$filename = array_shift($c);
		if (!isset(self::$config[$filename])){
			if ($filename == 'config'){
				include (MYAPPPATH.'config'.EXT);
			}else{
				$thefile = MYAPPPATH.'config/'.$filename.EXT;
				if (is_file($thefile)){
					include ($thefile);
				}else{
					$thefile = MYQEEPATH.'config/'.$filename.EXT;
					if (is_file($thefile)){
						include ($thefile);
					}
				}
			}
			self::$config[$filename] = $config;
		}

		$v = self::$config[$filename];
		foreach ($c as $i){
			$v = $v[$i];
		}
		return $v;
	}
	*/

	public static function move_classpath($oldclaspath,$newclasspath){
		$oldclaspath = str_replace('\\', '/', WWWROOT) . $oldclaspath;
		$newclasspath = str_replace('\\', '/', WWWROOT) . $newclasspath;
		$newpathArr = explode('/',$newclasspath);
		array_pop($newpathArr);
		$basepath = join('/',$newpathArr).'/';
		Tools::create_dir($basepath);
		@rename($oldclaspath,$newclasspath);
	}

	public static function saveconfig($filename , $config ,$isadminconfig = false ){
		if ($isadminconfig==true && defined('ADMINPATH')){
			$filepath = ADMINPATH .'config/';
		}else{
			$filepath = MYAPPPATH .'config/';
		}
		Tools::create_dir(dirname($filepath.$filename).'/');
		$filename = strtolower($filename);
		if (!$filename)return false;

		$config_str = '';
		if (is_array($config)){
			foreach($config as $key=>$value){
				$config_str .= '$config['.var_export($key,true).'] = ' . var_export($value,true).";\r\n";
			}
		}
		$config_str = '<?php defined(\'MYQEEPATH\') or die(\'No direct script access.\');'."\r\n//".date("Y-m-d H:i:s",$_SERVER['REQUEST_TIME'])."\r\n".'//it is saved by myqee system,please don\'t edit it.'."\r\n\r\n" . $config_str;

		$isseaved = Tools::createfile($filepath.$filename.EXT , $config_str);

		//Config::clear(str_replace('/','.',$filename));
		//$config_cache_file = Myqee::config('cache.default.params').'/myqee_configuration';
		//if (is_file($config_cache_file))@unlink($config_cache_file);
		
		return $isseaved;
	}

	public static function delconfig($filename){
		$filename = strtolower($filename);
		if (!$filename)return false;
		$thefile = realpath(MYAPPPATH .'config/'.$filename.EXT);
		if (!$thefile)return false;
		if ($thefile == realpath(MYAPPPATH .'config/config'.EXT))return false;	//config.php can not be deleted.
		return @unlink($thefile);
	}
	
	
	/**
	 * 返回目录信息
	 *
	 * @param string $dir 目录
	 * @param array $returndir 获取方式，dir表示只获取目录，file表示只获取文件，其它则表示获取全部
	 * @return array $dirinfo 返回数组或false
	 */
	public static function dirlist($dir,$returndir = 'all',$nolist = array('.','..','CVS','.svn','.settings','.cache','.project')){
		if (is_dir($dir)) {
			$dirinfo = array();
			if ($dh = opendir($dir)) {
				//排除特定文件及文件夹
				while (($file = readdir($dh)) !== false  ) {
					if ( !in_array($file,$nolist) ){
						if ($returndir == 'dir'){
							 if (is_dir($dir . $file)){
							 	$dirinfo[$file] = $file;
							 }
						}elseif ($returndir == 'file'){
							 if (is_file($dir . $file)){
							 	$dirinfo[$file] = $file;
							 }
						}else{
							$dirinfo[$file] = $file;
						}
					}
				}
				closedir($dh);
				return $dirinfo;
			}
		}else {
			return null;
		}
	}
	
	/**
	 * @name 将文章正文分离出标题和分页
	 * @param $value string 文章内容
	 * @return $out array 一个以标题，内容为分开的二维数组
	 */
	public static function get_title_info_array($value){
		$pagepreg = "<div style=\"page\-break\-after\: always\"><span style\=\"display\: none\">([^<]*)<\/span><\/div>";
		
		//处理第一页的分页符
		if (preg_match("/^{$pagepreg}/Usi",$value,$fristtitle)){
			$value = substr($value,strlen($fristtitle[0]));		//将第一页标题去掉
			$fristtitle = $fristtitle[1];
		}else{
			$fristtitle = '-1-';
		}
		preg_match_all("/{$pagepreg}/Usi",$value,$alltitle);
		$alltitle = $alltitle[1];
		array_unshift($alltitle,$fristtitle);	//将第一页标题插入所有标题
		
		//拆分内页
		$allinfo = preg_split("/{$pagepreg}/Usi",$value);
		
		$out['title'] = $alltitle;
		$out['info'] = $allinfo;
		
		//print_r($out);
		//unset($alltitle,$value,$pagepreg,$fristtitle,$allinfo);
		return $out;
	}
	
	/**
	 * 与上面方法相反
	 */
	public static function get_title_info_string($title,$content){
		$allcount = max(count($content),count($title));
		$tmpcontent='';
	
		for ($i=0;$i<$allcount;$i++){
			if ($title[$i]=='-'.($i+1).'-' || $title[$i]==' ')$title[$i] = '';
			$tmpcontent .= self::_get_title_htmlcode($title[$i]) . $content[$i];
		}
		return $tmpcontent;
	}
	
	public static function get_title_info_string_bypost($title,$content){
		$allcount = count($title);
		$tmpcontent='';
		
		$page = 0;
		foreach($title as $key=>$t){
			$page++;
			$i = (int)substr($key,3);
			if (!$i)continue;
			$t = trim($t);
			if ($t=='-'.$page.'-' || $t=='')$t = '';
			if ($page==1){
				$tmpcontent = ($t?self::_get_title_htmlcode($t):'').$content['info_'.$i];
			}else{
				if (!empty($content['info_'.$i]))$tmpcontent .= self::_get_title_htmlcode($t) . $content['info_'.$i];
			}
		}
		return $tmpcontent;
	}
	
	/**
	 * 得到系统内所有的虚拟字段
	 *
	 * @return array
	 */
	public static function get_virtual_field () {
		$vfields = array();
		$specialvfield = self::_get_special_virtial_field();
		$pluginsvfield = self::_get_plugins_virtual_field();
		$vfields = array_merge($vfields,$specialvfield,$pluginsvfield);
		return $vfields;
	}
	
	/**
	 * 得到插件的虚拟字段
	 *
	 * @return array
	 */
	protected static function _get_plugins_virtual_field () {
		$config = (array)Myqee::config('plugins');
		$vfields = array();
		foreach ($config as $key => $val) {
			$_vfield = $val['detailconfig']['model']['virtualfield'];
			if (count($_vfield) > 1) {
				foreach ($_vfield as $k => $v) {
					$vfields['#'.$key.'_'.$k] = $v;
				}
			} elseif (count($_vfield) > 0) {
				$vfields['#'.$key] = $_vfield[0];
			}
		}
		return $vfields;
	}
	
	/**
	 * 得到专题的虚拟字段
	 *
	 */
	protected static function _get_special_virtial_field () {
		$vfields['#special'] = array(
			'type' => 'checkbox',
			'title' => '所属专题',
			'infohook' => array('plugins_Model','specialinfo'),
			'modelhook' => array('plugins_Model','specialmodel'),
		);
		return $vfields;
	}
	
	protected static function _get_title_htmlcode($title){
		return '<div style="page-break-after: always"><span style="display: none">'.$title.'</span></div>';
	}
	
	/**
	 * 对多维数组进行重新排定索引号
	 *
	 * @param array $arr
	 * @return array $newarr
	 */
	/*
	 * 貌似暂时还没用到
	public static function reset_arrayvalue($arr){
		if (!is_array($arr))return $arr;
		$newarr = array();
		foreach ($arr as $key=>$value){
			if (is_int($key)&&$key>=0){
				$newarr[] = self::reset_arrayvalue($value);
			}else{
				$newarr[$key] = self::reset_arrayvalue($value);
			}
		}
		return $newarr;
	}
	*/
}
