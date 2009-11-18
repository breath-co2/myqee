<?php
class Index_Controller_Core extends Controller {
	
	public function __construct ()
	{
		parent::__construct(NULL);
		Passport::chkadmin();
	}
	
	public function index ()
	{
		$view = new View('admin/index');
		$view -> render(TRUE);
	}
	
	public function pinyin(){
		if ( !($pinyin = $_REQUEST['pinyin']) ){
			echo '{"error":"请求为空！"}';
		}
		$str = pinyin::render($pinyin,Tools::is_ascii($pinyin)?'gbk':'utf-8');
		echo '{"pinyin":"'.$str.'"}';
	}

	public static function runtime ($decimals = 3, $begintime = null)
	{
		$begintime or $begintime = STARTTIME;
		list ($usec, $sec) = explode(" ", microtime());
		$thistime = ((float) $usec + (float) $sec);
		return number_format($thistime - $begintime, $decimals);
	}

	public function testwatermark ()
	{
		if (is_array($_GET['watermark'])) {
			foreach ($_GET['watermark'] as $key => $value) {
				if (is_array($value)) {
					foreach ($value as $k2 => $v2) {
						$testconfig[$key][$k2] = is_array($v2) ? $v2 : MyqeeCMS::unescape($v2);
					}
				}
				else {
					$testconfig[$key] = MyqeeCMS::unescape($value);
				}
			}
		}
		else {
			$testconfig = NULL;
		}
		Image::factory(ADMINPATH . 'views/testwatermark.jpg') -> watermark($testconfig, false) -> render();
	}

	public function config ()
	{
		Passport::checkallow('index.config');
		$view = new View('admin/config');
		$adminmodel = new Admin_Model();
		$covertemplate = $adminmodel -> get_alltemplate('cover');
		$modulesdir = array_merge((array) MyqeeCMS::dirlist(MYQEEPATH . 'modules/', 'dir'), (array) MyqeeCMS::dirlist(MODULEPATH, 'dir'));
		$modules_config = (array) Myqee::config('core.modules');
		foreach ($modules_config as $key => $value) {
			if (! in_array($key, $modulesdir)) {
				unset($modules_config[$key]);
			}
		}
		//重复$modules_config是保持$modules_config的排序，同时已存在的值不被$modulesdir覆盖
		$modules_config = array_merge($modules_config, $modulesdir, $modules_config);
		$modules = Tools::json_encode($modules_config);
		$view -> set('tplgroup', $covertemplate);
		$view -> set('modules', $modules);
		$view -> set('member', (array) MyqeeCMS::config('member'));
		$view -> set('coreconfig', Myqee::config('core'));
		$view -> render(TRUE);
	}

	public function welcome ()
	{
		$view = new View('admin/index');
		$view -> render(TRUE);
	}

	public function phpinfo ()
	{
		Passport::checkallow('index.phpinfo');
		phpinfo();
	}

	public function runsql ($isrun = false)
	{
		Passport::checkallow('index.runsql');
		$dbconfig = Myqee::config('database');
		$configs = array();
		if (is_array($dbconfig)) {
			foreach ($dbconfig as $key => $item) {
				$configs[$key] = $item['name'] ? $item['name'] : $key;
			}
		}
		$view = new View('admin/runsql');
		$view -> set('configname', $configs);
		$view -> render(TRUE);
	}

	public function runsql_post ()
	{
		Passport::checkallow('index.runsql');
		$thedata = $_POST['data'];
		$charset = 'UTF-8';
		if (empty($thedata) && $_FILES['upload']['size'] == 0) {
			MyqeeCMS::show_error('待执行的SQL语句为空！', true);
		}
		if ($_FILES['upload']['tmp_name']) {
			$tmpfile = $_FILES['upload']['tmp_name'];
			if (! $thedata = @file_get_contents($_FILES['upload']['tmp_name'])) {
				MyqeeCMS::show_error('读取上传文件失败，请联系管理员！', true);
			}
			if ($_POST['upload_charset']) {
				$charset = $_POST['upload_charset'];
			}
		}
		if ($_POST['changecharset'] && $_POST['changecharset'] != $charset) {
			$thedata = iconv($charset, $_POST['changecharset'], $thedata);
			$charset = $_POST['changecharset'];
		}
		$configname = $_POST['configname'];
		$dbconfig = Myqee::config('database');
		if (! $dbconfig[$configname]) {
			MyqeeCMS::show_error('提交的数据库配置不存在，请重新选择！', true);
		}
		$connection = $dbconfig[$configname]['connection'];
		//替换表前缀
		$thedata = trim(str_replace('{{table_prefix}}', $dbconfig[$configname]['table_prefix'], $thedata), " \n\r") . "\n";
		$sqlarray = preg_split("/;([\s,]+)?(\n|\r)/", $thedata);
		$ER = error_reporting(0);
		$link = mysql_connect($connection['host'] . ($connection['port'] ? ':' . $connection['port'] : ''), $connection['user'], $connection['pass']) or MyqeeCMS::show_error('连接数据库失败<br/>' . mysql_error(), true);
		mysql_select_db($connection['database']) or MyqeeCMS::show_error('切换数据表失败！');
		mysql_query('SET NAMES ' . $dbconfig[$configname]['character_set']);
		$run_ok = $run_error = 0;
		$error_sql = '<div style="line-height:1.5em;width:584px;padding:5px;height:358px;overflow:auto;"><ol>';
		foreach ($sqlarray as $sql) {
			$sql = trim($sql, " \n\r");
			if (! empty($sql)) {
				if (mysql_query($sql)) {
					$run_ok ++;
				}
				else {
					$run_error ++;
					if ($charset != 'UTF-8') {
						$error = iconv($charset, 'UTF-8', mysql_error());
						$sql = iconv($charset, 'UTF-8', $sql);
					}
					else {
						$error = mysql_error();
					}
					$error_sql .= '<li>' . $error . '<br/><font color="red"><b>SQL:</b>' . str_replace("\r", '<br/>', str_replace("\n", '<br/>', htmlspecialchars($sql))) . '</font></li>';
				}
			}
		}
		$error_sql .= '</ol></div>';
		// 释放结果集
		mysql_free_result($result);
		// 关闭连接
		mysql_close($link);
		error_reporting($ER);
		//print_r($_POST);
		if ($run_ok > 0 && $run_error == 0) {
			MyqeeCMS::show_ok('恭喜，全部执行成功！', true);
		}
		else {
			$infoarr = array(
				'message' => '执行成功：' . $run_ok . ' &nbsp; 执行失败：' . $run_error . '。'
			);
			if ($run_error > 0) {
				$infoarr['message'] .= ' 点击查看错误信息！';
				$infoarr['handler'] = 'function(er){
						if (er!="showerr")return;
						parent.win({"title":"SQL执行错误记录","maxBtn":true,"message":unescape("' . Tools::escape($error_sql) . '"),"width":600,"height":400});
					}';
				$infoarr['btn'] = array(
					array(
						'查看错误' , 
						'showerr'
					) , 
					array(
						'关闭窗口' , 
						'ok'
					)
				);
			}
			MyqeeCMS::show_info($infoarr, true);
		}
	}

	public function configsave ()
	{
		Passport::checkallow('index.config');
		$post = $_POST;
		$myconfig = Myqee::config('core');
		$myroutes = explode("\n", $post['core']['routes']);
		$post['core']['routes'] = array();
		$ii = 0;
		foreach ($myroutes as $value) {
			$item = explode('=>', $value);
			$item[0] = trim($item[0]);
			$item[1] = trim($item[1]);
			if (! empty($item[0]) && ! empty($item[1])) {
				$post['core']['routes']['key'][$ii] = '/' . $item[0] . '/i';
				$post['core']['routes']['value'][$ii] = $item[1];
				if ($post['core']['routes']['key'][$ii] != $myconfig['routes']['key'][$ii] || $post['core']['routes']['value'][$ii] != $myconfig['routes']['value'][$ii]) {
					$routes_ischange = true;
				}
				$ii ++;
			}
		}
		
		//检测routes
		if ($routes_ischange || count($myconfig['routes']['key']) != count($post['core']['routes']['key'])) {
			$this -> isconfigchange = true;
			$myconfig['routes'] = $post['core']['routes'];
		}
		unset($ii, $myroutes, $item, $routes_ischange);
		$post['core']['upload']['floder'] = empty($post['core']['upload']['floder']) ? 'floder/' : trim($post['core']['upload']['floder'], '/ ') . '/';
		$post['core']['upload']['maxsize'] = (int) $post['core']['upload']['maxsize'];
		
		if ($post['core']['internal_cache']>1){
			$post['core']['internal_cache'] = $_POST['internal_cache']>1?(int)$_POST['internal_cache']:86400;
		}
		$myconfig = $this -> _setnewconfig($myconfig, $post['core'], true);
		
		//检测modules
		if (count($post['core']['modules']) != count($myconfig['modules'])) {
			$myconfig['modules'] = $post['core']['modules'];
			$this -> isconfigchange = true;
		}else {
			//检查是否内容也一样
			if (md5(serialize($post['core']['modules'])) != md5(serialize($myconfig['modules']))) {
				$myconfig['modules'] = $post['core']['modules'];
				$this -> isconfigchange = true;
			}
		}
		//print_r($post);exit;
		if ($this -> isconfigchange) {
			if ($post['core']['index_filename'] != $myconfig['index_filename']) {
				@rename(WWWROOT . $myconfig['index_filename'], WWWROOT . $post['core']['index_filename']);
				$myconfig['index_filename'] = $post['core']['index_filename'];
				$myconfigEdited = true;
			}
			//保存系统配置文件
			$this -> _savecoreconfig($myconfig);
			$myconfigsaved = true;
		}
		//////////////////////////////database
		$this -> isconfigchange = false;
		$myconfig = Myqee::config('database');
		$myconfig = $this -> _setnewconfig($myconfig, $post['database']);
		if ((int) $post['database']['default']['connection']['port'] != (int) $myconfig['default']['connection']['port']) {
			$myconfig['default']['connection']['port'] = (int) $post['database']['default']['connection']['port'] > 0 ? (int) $post['database']['default']['connection']['port'] : NULL;
			$this -> isconfigchange = true;
		}
		if ($this -> isconfigchange) {
			MyqeeCMS::saveconfig('database', $myconfig);
			$myconfigsaved = true;
		}
		//////////////////////////////member	
		$member = $post['member'];
		if (is_array($member) && (serialize($member) != serialize(MyqeeCMS::config('member')))) {
			MyqeeCMS::saveconfig('member', $member);
		}
		//////////////////////////////encryption
		$this -> isconfigchange = false;
		$myconfig = MyqeeCMS::config('encryption');
		$myconfig = $this -> _setnewconfig($myconfig, $post['encryption']);
		if ($this -> isconfigchange) {
			MyqeeCMS::saveconfig('encryption', $myconfig);
			$myconfigsaved = true;
		}
		if ($myconfigsaved) {
			MyqeeCMS::show_info(Myqee::lang('admin/index.info.saveok'), true, 'refresh');
		}
		else {
			MyqeeCMS::show_info(Myqee::lang('admin/index.info.savenone'), true);
		}
	}

	public function configbak ()
	{
		Passport::checkallow('index.config_bak');
		if (! is_file(MYAPPPATH . 'config.php.bak')) {
			MyqeeCMS::show_error(Myqee::lang('admin/index.error.nobakconfig'), true);
		}
		if (! @rename(MYAPPPATH . 'config.php.bak', MYAPPPATH . 'config_1.php.bak')) {
			MyqeeCMS::show_error(Myqee::lang('admin/index.error.dobakconfigerror'), true);
		}
		if (! @rename(MYAPPPATH . 'config.php', MYAPPPATH . 'config.php.bak')) {
			MyqeeCMS::show_error(Myqee::lang('admin/index.error.doconfigerror'), true);
		}
		if (! @rename(MYAPPPATH . 'config_1.php.bak', MYAPPPATH . 'config.php')) {
			//恢复
			rename(MYAPPPATH . 'config.php.bak', MYAPPPATH . 'config.php');
			MyqeeCMS::show_error(Myqee::lang('admin/index.error.bakconfigerror'), true);
		}
		MyqeeCMS::show_info(Myqee::lang('admin/index.info.bakok'), true, 'refresh');
	}

	protected function _savecoreconfig ($core)
	{
		$configcode = new View('admin/config_set', NULL, 'txt');
		$configcode = $configcode -> render(false);
		$configcode = str_replace('NULL/*{{routes}}*/', stripslashes(var_export($core['routes'], true)), $configcode);
		$configcode = str_replace('NULL/*{{modules}}*/', stripslashes(var_export($core['modules'], true)), $configcode);
		$configcode = $this -> _replacestr($core, $configcode);
		//$configcode = preg_replace("/\ = {\{[^\;]*\}\};\r/"," = NULL;\r",$configcode);
		if (is_file(MYAPPPATH . 'config.php.bak')) @unlink(MYAPPPATH . 'config.php.bak');
		@rename(MYAPPPATH . 'config.php', MYAPPPATH . 'config.php.bak');
		Tools::createfile(MYAPPPATH . 'config.php', $configcode);
		header('Content-Type:text/html; charset=utf-8');
	}

	protected function _replacestr ($config, $configcode, $keystr = '')
	{
		foreach ($config as $key => $value) {
			if (is_array($value)) {
				$mykeystr = $keystr . '{' . $key . '}';
				$configcode = $this -> _replacestr($value, $configcode, $mykeystr);
			}
			else {
				$mykeystr = 'NULL/*' . $keystr . '{{' . $key . '}}*/';
				$configcode = str_replace($mykeystr, var_export($value, true), $configcode);
			}
		}
		return $configcode;
	}

	protected function _setnewconfig ($config, $newconfig, $isprokey = false)
	{
		if (! is_array($config)) return $config;
		foreach ($config as $key => $value) {
			//忽略modules和routes
			if (($isprokey == true && ($key == 'modules' || $key == 'routes')) || ! isset($newconfig[$key])) continue;
			if (is_array($value)) {
				$config[$key] = $this -> _setnewconfig($value, $newconfig[$key]);
			}
			else {
				if ($newconfig[$key] != $config[$key]) {
					$this -> isconfigchange = true;
					$config[$key] = $newconfig[$key];
				}
			}
		}
		return $config;
	}

	public function memu_reset ()
	{
		Passport::checkallow('index.adminmenu');
		MyqeeCMS::delconfig('adminmenu');
		$sys_configdata = Myqee::config('adminmenu');
		$status = MyqeeCMS::saveconfig('adminmenu', $sys_configdata);
		if ($status) {
			$cacheid = $_SERVER['HTTP_HOST'] . 'admin.header_admin_' . $_SESSION['admin']['id'];
			Cache::instance() -> delete($cacheid);
			MyqeeCMS::show_ok(Myqee::lang('admin/index.info.configbackok'), true, 'refresh');
		}
		else {
			MyqeeCMS::show_info(Myqee::lang('admin/index.info.configbackerror'), true);
		}
	}

	public function menu ()
	{
		Passport::checkallow('index.adminmenu');
		$view = new View('admin/admin_menu');
		$data = array();
		$sysConfigData = Myqee::config('adminmenu');
		if (! is_array($sysConfigData)) {
			MyqeeCMS::show_error(Myqee::lang('admin/index.error.nofoundadminmenu'), false, 'goback');
		}
		$appConfigData = (array) MyqeeCMS::config('adminmenu');
		$appConfigData = array_merge($sysConfigData, $appConfigData);
		if (is_array($appConfigData)) {
			//复制系统菜单，生产新的数组  $data
			foreach ($appConfigData as $key => $item) {
				$data[$key]['name'] = $item['name'] ? $item['name'] : $sysConfigData[$key]['name'];
				$data[$key]['address'] = $item['address'] ? $item['address'] : $sysConfigData[$key]['address'];
				$data[$key]['level'] = $item['level'] ? $item['level'] : $sysConfigData[$key]['level'];
				$data[$key]['myorder'] = ($item['myorder'] != '' && $item['myorder'] != NULL) ? $item['myorder'] : $sysConfigData[$key]['myorder'];
				$data[$key]['target'] = $item['target'] ? $item['target'] : $sysConfigData[$key]['target'];
				$data[$key]['target2'] = $item['target2'] ? $item['target2'] : $sysConfigData[$key]['target2'];
				$data[$key]['is_use'] = $item['is_use'] === 0 ? 0 : 1;
				if (! $item['sub']) continue;
				$syssubConfigData = $sysConfigData[$key]['sub'];
				foreach ($item['sub'] as $subkey => $item) {
					$data[$key]['sub'][$subkey]['name'] = $item['name'] ? $item['name'] : $syssubConfigData[$subkey]['name'];
					$data[$key]['sub'][$subkey]['address'] = $item['address'] ? $item['address'] : $syssubConfigData[$subkey]['address'];
					$data[$key]['sub'][$subkey]['level'] = $item['level'] ? $item['level'] : $syssubConfigData[$subkey]['level'];
					$data[$key]['sub'][$subkey]['target'] = $item['target'] ? $item['target'] : $syssubConfigData[$subkey]['target'];
					$data[$key]['sub'][$subkey]['target2'] = $item['target2'] ? $item['target2'] : $syssubConfigData[$subkey]['target2'];
					$data[$key]['sub'][$subkey]['is_use'] = $item['is_use'] === 0 ? 0 : 1;
				}
			}
		}
		$view -> set('allkey', $this -> _get_all_syskey());
		$view -> set('data', $this -> _array_sort($data));
		$view -> render(TRUE);
	}

	public function adminmenusave ()
	{
		Passport::checkallow('index.adminmenu_edit');
		$post = $_POST['menu'];
		if (! is_array($post)) {
			MyqeeCMS::show_info(Myqee::lang('admin/index.error.dataerror'), true, '');
		}
		$data = array();
		$sysConfigData = Myqee::config('adminmenu');
		$i = 1;
		$allFatherKeys = array();
		$fn = 1;
		$allFatherNames = array();
		foreach ($post as $key => $item) :
			if (! $item['name']) {
				MyqeeCMS::show_info(Myqee::lang('admin/index.error.noacqufathername'), true, '');
			}
			if (in_array($item['name'], $allFatherNames)) {
				MyqeeCMS::show_error('此父菜单名称已存在', true);
			}
			$allFatherNames[$fn] = $item['name'];
			$fn ++;
			if (! $item['address']) {
				MyqeeCMS::show_info(Myqee::lang('admin/index.error.noacqufatheraddress'), true, '');
			}
			$key = $item['key'];
			if (! $key || empty($key) || ! preg_match("/^[a-zA-Z][a-zA-Z0-9_]*$/", $key)) {
				MyqeeCMS::show_error('抱歉，KEY值“' . $key . '”不符合要求，\n\n KEY值不能空，且不能含有非法字符，且不能为纯数字，且以字母开头\n\n请重新输入！', true, '');
			}
			if (in_array($key, $allFatherKeys)) {
				MyqeeCMS::show_error('此KEY值已存在', true);
			}
			$allFatherKeys[$i] = $key;
			$i ++;
			$data[$key]['name'] = $item['name'];
			$data[$key]['address'] = $item['address'];
			$data[$key]['level'] = $item['level'];
			$data[$key]['key'] = $item['key'];
			$data[$key]['target'] = $item['target'];
			$data[$key]['target2'] = $item['target2'];
			$data[$key]['myorder'] = $item['myorder'] ? $item['myorder'] : '0';
			if (in_array($key, $this -> _get_all_syskey())) {
				$data[$key]['is_use'] = $item['is_use'] == 0 ? 0 : 1;
			}
			$postsubData = $post[$key]['sub'];
			if (! is_array($postsubData)) continue;
			$q = 1;
			$cn = 1;
			$allChildNames = array();
			foreach ($postsubData as $subkey => $item) :
				if (! $item['name']) {
					MyqeeCMS::show_info(Myqee::lang('admin/index.error.noacquchildname'), true, '');
				}
				if (in_array($item['name'], $allChildNames)) {
					MyqeeCMS::show_error('此子菜单名称已存在', true);
				}
				$allChildNames[$cn] = $item['name'];
				$cn ++;
				if (! $item['address']) {
					MyqeeCMS::show_info(Myqee::lang('admin/index.error.noacquchildaddress'), true, '');
				}
				if (substr($subkey, 0, 1) == '_') {
					$data[$key]['sub']['_' . $q] = $item;
					$q ++;
				}
				else {
					$data[$key]['sub'][$subkey]['name'] = $item['name'] ? $item['name'] : $sysConfigData[$key]['sub'][$subkey]['name'];
					$data[$key]['sub'][$subkey]['address'] = $item['address'] ? $item['address'] : $sysConfigData[$key]['sub'][$subkey]['address'];
					$data[$key]['sub'][$subkey]['level'] = $item['level'] ? $item['level'] : $sysConfigData[$key]['sub'][$subkey]['level'];
					$data[$key]['sub'][$subkey]['target'] = ($target1 = $item['target'] ? $item['target'] : $sysConfigData[$key]['sub'][$subkey]['target']) != NULL ? $target1 : '';
					$data[$key]['sub'][$subkey]['target2'] = ($target2 = $item['target2'] ? $item['target2'] : $sysConfigData[$key]['sub'][$subkey]['target2']) != NULL ? $target2 : '';
					$data[$key]['sub'][$subkey]['is_use'] = $item['is_use'] == 0 ? 0 : 1;
				}
			endforeach;
		endforeach;
		$status = MyqeeCMS::saveconfig('adminmenu', $this -> _array_sort($data));
		if ($status) {
			$cacheid = $_SERVER['HTTP_HOST'] . 'admin.header_admin_' . $_SESSION['admin']['id'];
			Cache::instance() -> delete($cacheid);
			MyqeeCMS::show_info(Myqee::lang('admin/index.info.savemenuok'), true, 'refresh');
		}
		else {
			MyqeeCMS::show_info(Myqee::lang('admin/index.info.savemenunone'), true);
		}
	}

	public function addmenusave ()
	{
		$menu = $_POST['menu'];
		if (! $menu['name']) {
			MyqeeCMS::show_info(Myqee::lang('admin/index.error.noacqufathername'), true, '');
		}
		if (in_array($menu['name'], $this -> _get_all_father_name())) {
			MyqeeCMS::show_error('此父菜单名称已存在', true);
		}
		if (! $menu['address']) {
			MyqeeCMS::show_info(Myqee::lang('admin/index.error.noacqufatheraddress'), true, '');
		}
		if (! $menu['key'] || empty($menu['key']) || ! preg_match("/^[a-zA-Z][a-zA-Z0-9_]*$/", $menu['key'])) {
			MyqeeCMS::show_error('抱歉，KEY值“' . $menu['key'] . '”不符合要求，\n\n KEY值不能空，且不能含有非法字符，且不能为纯数字，且以字母开头\n\n请重新输入！', true, '');
		}
		$configData = MyqeeCMS::config('adminmenu');
		$k = 1;
		$allkey = array();
		if ((is_array($configData) && ! empty($configData))) {
			foreach ($configData as $key => $configitem) :
				$allkey[$k] = $key;
				$k ++;
			endforeach;
		}
		if (in_array($menu['key'], $allkey)) {
			MyqeeCMS::show_error('此KEY值已存在', true);
		}
		if ((is_array($menu) && ! empty($menu))) {
			$data = $configData;
			$data[$menu['key']] = $menu;
		}
		else {
			$data = $configData;
		}
		$status = MyqeeCMS::saveconfig('adminmenu', $this -> _array_sort($data));
		if ($status) {
			$cacheid = $_SERVER['HTTP_HOST'] . 'admin.header_admin_' . $_SESSION['admin']['id'];
			Cache::instance() -> delete($cacheid);
			MyqeeCMS::show_info(Myqee::lang('admin/index.info.savemenuok'), true, 'refresh');
		}
		else {
			MyqeeCMS::show_info(Myqee::lang('admin/index.info.savemenunone'), true);
		}
	}
	
	/**
	 * 缓存管理
	 *
	 */
	public function cache(){
		$view = new View('admin/cache_manage');
		$view -> render(TRUE);
	}
	/**
	 * 重建站点文件索引缓存
	 *
	 */
	public function renewfilelist(){
		if (Myqee::config('core.internal_cache')==0){
			$msg = '动态加载信息缓存功能已关闭，不需要生成缓存！';
			if ($_GET['type']=='auto'){
				echo '<script>parent.showinfo("filelist","'.$msg.'");document.location.href="'.ADMIN_IMGPATH.'/admin/block.html";</script>';
				return true;
			}else{
				MyqeeCMS::show_info($msg,true);
			}
		}
		$adminpath = Myqee::include_paths(FALSE);
		
		$pathset = array(
			'controllers'	=> true,
			'config'		=> true,
			'i18n'			=> true,
			'views'			=> true,
			'api'			=> false,
			'helpers'		=> false,
			'libraries'		=> false,
			'models'		=> false,
		);
	
		$this->extension_prefix = Myqee::config('core.extension_prefix');
		$this->extension_prefix_len = strlen($this->extension_prefix);
		$this->foundnum = 0;
		
		$this -> listarr = array();
		foreach ($pathset as $dir => $includesonpath){
			foreach ($adminpath as $fullpath){
				$this -> _find_file($fullpath,$dir,'*'.EXT,$includesonpath);
			}
		}
		
		Event::clear( 'system.shutdown' );
		Event::add ( 'system.shutdown', array ('Myqee', 'shutdown' ) );
		
		
		Myqee::cache_save('find_file_paths',$this -> listarr);
		
		$msg = '路径配置缓存更新成功！共找到<font color=red> '.$this->foundnum.' </font>个文件。';
		if ($_GET['type']=='auto'){
			echo '<script>parent.showinfo("filelist","'.$msg.'");document.location.href="'.ADMIN_IMGPATH.'/admin/block.html";</script>';
		}else{
			MyqeeCMS::show_ok($msg,true);
		}
	}
	
	protected function _find_file($fullpath,$dir,$search,$includesonpath=false){
		$pathlen = strlen($fullpath);
		foreach (glob($fullpath.$dir.'/'.$search) as $filename) {
			$arrkey = substr($filename,$pathlen);
			$this->foundnum++;
			if ($dir=='config'||$dir=='i18n'){
				if ( is_array($this -> listarr[$arrkey]) ){
					//在开头插入一路径
					array_unshift($this -> listarr[$arrkey],$filename);
				}else{
					$this -> listarr[$arrkey][] = $filename;
				}
				
			}elseif( !isset($this -> listarr[$arrkey]) || $this -> listarr[$arrkey]===false ){
				$this -> listarr[$arrkey] = $filename;
				
				if ($this->extension_prefix && $dir!='views'){
					//为扩展文件处理
					$basename = basename($filename);
					if (substr($basename,0,$this->extension_prefix_len)!==$this->extension_prefix){
						$tmparrkey = explode('/',$arrkey);
						$tmparrkey[count($tmparrkey)-1] = $this->extension_prefix . $tmparrkey[count($tmparrkey)-1];
						$arrkey = implode('/',$tmparrkey);
						$this -> listarr[$arrkey] = false;
					}
				}

			}
		}
		
		//搜索子目录
		if ($includesonpath){
			$allsonpath = MyqeeCMS::dirlist($fullpath.$dir.'/', 'dir');
			if (is_array($allsonpath) && count($allsonpath)){
				foreach ($allsonpath as $sonpath){
					$this -> _find_file($fullpath,$dir,$sonpath.'/'.$search);
				}
			}
		}
	}
	

	protected function _get_all_father_name ()
	{
		$k = 1;
		$allName = array();
		$configData = MyqeeCMS::config('adminmenu');
		if (! is_array($configData) || empty($configData)) {
			return array();
		}
		else {
			foreach ($configData as $item) :
				$allName[$k] = $item['name'];
				$k ++;
			endforeach;
		}
		return $allName;
	}

	protected function _get_all_syskey ()
	{
		$syskey = Array(
			'1' => 'index' , 
			'2' => 'info' , 
			'3' => 'task' , 
			'4' => 'class' , 
			'5' => 'member' , 
			'6' => 'model' , 
			'7' => 'template' , 
			'8' => 'plugins', 
		);
		return $syskey;
	}

	protected function _array_sort ($array, $type = 'asc')
	{
		$result = array();
		foreach ($array as $var => $val) {
			$set = false;
			foreach ($result as $var2 => $val2) {
				if ($set == false) {
					if ($val['myorder'] > $val2['myorder'] && $type == 'desc' || $val['myorder'] < $val2['myorder'] && $type == 'asc') {
						$temp = array();
						foreach ($result as $var3 => $val3) {
							if ($var3 == $var2) $set = true;
							if ($set) {
								$temp[$var3] = $val3;
								unset($result[$var3]);
							}
						}
						$result[$var] = $val;
						foreach ($temp as $var3 => $val3) {
							$result[$var3] = $val3;
						}
					}
				}
			}
			if (! $set) {
				$result[$var] = $val;
			}
		}
		return $result;
	}
	
	

	public function logs_view(){
		$logfile = $_GET['log'];
		if (!$logfile){
			MyqeeCMS::show_error('缺少参数！',false,'goback');
		}
		$file = MYAPPPATH . 'logs/'.ltrim($logfile,'./\\');
		
		if (!file_exists($file)){
			MyqeeCMS::show_error('不存在指定的日志文件，可能已删除！',false,'goback');
		}
		
		$list = $this -> _get_logs($file);
		$ftell = (int)$this->ftell;
		$time=time();
		
		$view = new View('admin/logs_show');
		$view -> set('logfile',$logfile);
		$view -> set('list',Tools::json_encode($list));
		$view -> set('readall',$this->isreadall);
		
		$view -> set('ftell',$ftell);		//读取指针停留位置
		$view -> set('time',$time);
		$view -> set('code',$this->_get_logs_code($time,$ftell));
		
		$view -> set('renewtime',$_GET['renewtime']);
		
		$view -> render(true);
	}
	
	
	public function logs_more(){
		$ftell = $_GET['ftell'];
		$code = $_GET['code'];
		$time = $_GET['time'];
		$logfile = $_GET['log'];
		if (!$time||!$logfile || !$code){
			echo '{"error":"缺少参数"}';
			exit;
		}
		
		$file = MYAPPPATH . 'logs/'.ltrim($logfile,'./\\');
		if (!file_exists($file)){
			echo '{"error":"不存在指定的日志文件，可能已删除！"}';
			exit;
		}
		
		$list = $this -> _get_logs($file,$ftell,$time,$code);
		$list = array('log'=>$list,'isall'=>$this->isreadall);
		$list['time'] = time();
		$list['ftell'] = $this->ftell;
		$list['code'] = $this->_get_logs_code($list['time'],$list['ftell']);
	
		echo Tools::json_encode($list);
	}
	
	protected function _get_logs_code($time,$ftell){
		return md5(MyqeeCMS::config('encryption.default.key').'__logread__'.$time.'__'.$ftell);
	}
	
	protected function _get_logs($file,$ftell=0,$time=0,$code=''){
		$list = array();
		$readline = 100;
		if ($ftell>0){
			$now = time();
			if ($now-$time>1800||$this->_get_logs_code($time,$ftell)!=$code || filesize($file)<$ftell){
				return false;
			}
		}
		$handle = @fopen($file,'r');
		
		if ($handle){
			if ($ftell>0){
				//移动到指定位置
				fseek($handle,$ftell);
			}
			$i=0;
			do {
				if (feof($handle)){
					$this -> isreadall = true;
					break;
				}
				$line = fgets($handle,99999);
				preg_match("/^(.*) --- (info|error|success|system|other|debug)\:(.*)$/",$line,$match);
				if ($match){
					$list[$i] = array(
						'info' => $match[3],
						'type' => $match[2],
						'time' => $match[1],
					);
					$i++;
				}else if($i>0){
					$list[$i-1]['info'] .= $line;
				}
			}
			while( $i<$readline && !$this -> isreadall );
			
			$this -> ftell = ftell($handle);
			fclose($handle);
		}
		
		return $list;
	}
	
	
	public function logs_del(){
		$logfile = $_GET['log'];
		if (!$logfile){
			MyqeeCMS::show_error('缺少参数！',true);
		}
		$file = MYAPPPATH . 'logs/'.ltrim($logfile,'./\\');
		
		if (!file_exists($file)){
			MyqeeCMS::show_info('不存在指定的日志文件，可能已删除！',true);
		}
		
		if (@unlink($file)){
			if ($_GET['close']=='yes'){
				MyqeeCMS::show_ok(array('handler'=>'function(e){if(e=="ok")parent.close();}','btn'=>array(array('关闭页面','ok'),array('取消','c')),'message'=>'恭喜，日志删除成功！'),true);
			}else{
				MyqeeCMS::show_ok('恭喜，日志删除成功！',true,'refresh');
			}
		}else{
			MyqeeCMS::show_error('删除失败，可能没有权限操作！',true);
		}
	}
}