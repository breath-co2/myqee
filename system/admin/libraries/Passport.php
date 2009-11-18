<?php
class Passport_Core {
	public static $session;
	protected static $adminset = array();
	
	public static function get_adminid(){
		return self::$session -> get('admin.id');
	}
	public static function chkadmin($errmsg = '',$autogo = false){
		self::$session = Session::instance();
		$adminDate = self::$session -> get('admin');
		$login_time = self::$session -> get('login_time');
		if (!$adminDate || $_SERVER['REQUEST_TIME'] - $login_time >1800){
			$errmsg = $errmsg ? $errmsg : Myqee::lang('admin/login.err.timeout');
			
			self::$session -> set_flash('loginerr',$errmsg);
			$gourl = Myqee::url('login/index');
			if (count($_POST)>0 || $autogo===false ){
				echo '
<script type="text/javascript">
try{
	parent.win(
		{
			message:"'.$gourl.'?h=none&forward='.Myqee::url('login/loginok').'",
			width:450,
			height:400,
			title:"登陆",
			iframe:{"name":"loginFrame"}
		}
	);
}catch(e){
	top.location.href="'.$gourl.'?forward='.urlencode( $_SERVER["REQUEST_URI"]).'";
}
</script>
<input id="reloadbtn" type="button" onclick="document.location.reload()" value="刷新本页" />';
			}else{
				header("location:".$gourl.'?forward='.urlencode( $_SERVER["REQUEST_URI"]));
			}
			exit;
		}elseif ( $_SERVER['REQUEST_TIME'] - $login_time> 180 ){
			self::$session -> set('login_time',$_SERVER['REQUEST_TIME']);
		}
		return self::$session;
	}
	
	
	/**
	 * 检查管理员是否有权限操作，若没有权限，直接输出错误提示页
	 * @param $thecpt 待检查的权限
	 * @param $errorinfo 错误提示信息
	 * @param $isInHiddenFrame 页面是否在框架内
	 * @param $gotoUrl 自动跳转页面
	 * @param $adminid 管理员ID
	 * @return boolean true/false
	 */
	public static function checkallow($thecpt,$errorinfo='',$isInHiddenFrame = false, $gotoUrl = 'goback' , $adminid = null){
		$mythecpt = explode('|',$thecpt);
		foreach ($mythecpt as $thecpt){
			if (self::getisallow($thecpt,$adminid)){
				return TRUE;
			}
		}
		$errorinfo or $errorinfo = '您没有权限操作此功能！';
		MyqeeCMS::show_error($errorinfo,$isInHiddenFrame,$gotoUrl);
		return false;
	}
	
	
	/**
	 * 获取管理员是否具有对应权限
	 * @param $thecpt
	 * @param $adminid
	 * @return boolean true/false
	 */
	public static function getisallow($thecpt,$adminid=null){
		$mythecpt = explode('|',$thecpt);
		foreach ($mythecpt as $thecpt){
			if (Myqee::key_string(self::getadmincpt($adminid),$thecpt)){
				return TRUE;
			}
		}
		return FALSE;
	}
	
	/**
	 * 获取管理员权限数组
	 * @param $adminid 管理员ID，不指定将是当前登录的管理员
	 * @return array 管理员权限
	 */
	public static function getadmincpt($adminid=null){
		if ($adminid>0){
			$admin = self::_get_admin_set($adminid);
			if ($admin===false){
				return false;
			}
		}else {
			$admin = $_SESSION['admin'];
		}
		return unserialize($admin['competence']);
	}
	
	
	/**
	 * 获取管理员是否有指定站点管理权限
	 *
	 * @param int $siteid
	 * @param int $adminid
	 * @return boolean true/false
	 */
	public static function getisallow_site($siteid,$adminid=null){
		$adminsite = self::getadminsite($adminid);
		if ($adminsite=='-ALL-')return 1;
		if (in_array($siteid,explode(',',$adminsite))){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	
	public static function getisallow_db($dbname,$adminid=null){
		$adminsite = self::getadmindb($adminid);
		if ($adminsite=='-ALL-')return 1;
		if (in_array($dbname,explode(',',$adminsite))){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	
	public static function getisallow_class($classid,$adminid=null){
		$adminsite = self::getadminclass($adminid);
		if ($adminsite=='-ALL-')return 1;
		if (in_array($classid,explode(',',$adminsite))){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	

	
	public static function getadminsite($adminid=null){
		return self::_getadmin_type('siteset',$adminid);
	}

	public static function getadmindb($adminid=null){
		return self::_getadmin_type('dbset',$adminid);
	}
	
	public static function getadminclass($adminid=null){
		return self::_getadmin_type('classset',$adminid);
	}
	
	/**
	 * 获取指定管理员站点拥有相应的管理权，用,分开
	 *
	 * @param string $type 包括'siteset','dbset','classset'
	 * @param int $adminid
	 * @return string $admin['siteset']
	 */
	protected static function _getadmin_type($type,$adminid=null){
		if (!in_array($type,array('siteset','dbset','classset'))){
			return false;
		}
		if ($adminid>0){
			$admin = self::_get_admin_set($adminid);
			if ($admin===false){
				return false;
			}
		}else {
			$admin = $_SESSION['admin'];
		}
		
		return $admin[$type];
	}
	
	
	/**
	 * 读取管理员设置
	 * @param $adminid
	 * @return array/false	返回管理员设置
	 */
	protected static function _get_admin_set($adminid){
		if (isset(self::$adminset[$adminid])){
			return self::$adminset[$adminid];
		}
		$db = Database::instance();
		$admin = $db -> getwhere('[admin]',array('id'=>$adminid))->result_array(FALSE);
		$admin = $admin[0];
		if (!$admin){
			self::$adminset[$adminid] = FALSE;
			return FALSE;
		}
	
		if ($admin['groupid']>0){
			$group = $db -> getwhere('[admin_group]',array('id'=>$admin['groupid']))->result_array(FALSE);
			$group = $group[0];
			if (!$group)return FALSE;
			
			$admin['competence'] = $group['competence'];
			
			//按组设置
			if ($admin['auto_siteset']==1)
				$admin['siteset'] = $group['site'];
				
			if ($admin['auto_classset']==1)
				$admin['classset'] = $group['class'];
				
			if ($admin['auto_dbset']==1)
				$admin['dbset'] = $group['db'];
		}
		
		self::$adminset[$adminid] = $admin;
		
		return $admin;
	}
}
