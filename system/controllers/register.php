<?php defined('MYQEEPATH') or die('No direct script access.');
/**
 * 游戏积分API
 *
 * $Id$
 *
 * @package    App
 * @author     Dianming Team
 * @copyright  (c) 2008-2009 Dianming Team
 * @license    http://dianming.com/license.html
 */
class Register_Controller_Core extends Controller{

	public function index(){
		if (!$v = Myqee::config('core.systemplate.register')){
			$v = 'register';
		}
		$view = new View($v);
		$view -> render(true);
	}


	public function reg(){
		if(empty($_POST['step'])){
			$view = new View("register");
			$view->render(TRUE); 
		}elseif ($_POST['step'] == 2){
			$db = new Database();
			$tools = new Tools();
			//检测数据
			$_POST['username'] 	= trim($_POST['username']);
			$_POST['email'] 	= trim($_POST['email']);
			$_POST['qq'] 		= trim($_POST['qq']);
			$_POST['msn'] 		= trim($_POST['msn']);
			if(empty($_POST['username']) || strlen($_POST['username']) > 20 || strlen($_POST['username']) < 6){
				
				Myqee::show_error("用户名长度错误！",NULL,true);
			}
			if(strlen($_POST['password']) <6 || $_POST['password'] != $_POST['ck_password']){
				Myqee::show_error("请重新输入密码！",NULL,true);
			}
			if(preg_match("#[^\w]#",$_POST['username'])){
				Myqee::show_error("用户名中含有非法字符",NULL,true);
			}


			if(!valid::email($_POST['email'])){
				Myqee::show_error("请认真填写邮箱！",NULL,true);
			}
			

			if(!empty($_POST['qq']) && !preg_match("#^[0-9]+$#",$_POST['qq'])){
				Myqee::show_error("请认真填写QQ！");
			}
			if(!empty($_POST['msn']) && !valid::email($_POST['msn'])){
				Myqee::show_error("请认真填写MSN！",NULL,true);
			}
			if (!$_POST['imagecode']){
				Myqee::show_error("请填写验证码！",NULL,true);
			}else{
				if (Captcha::valid($_POST['imagecode'])<=0){
					Myqee::show_error("验证码填写错误！",NULL,true);
				}
			}
			
			
			$username = $_POST['username'];

			$insert_data = array(
				"username"		=> $username,
				"password"		=> md5($_POST['password']),
				"email"			=> $_POST['email'],
				"qq"			=> $_POST['qq'],
				"msn"			=> $_POST['msn'],
				"lastloginip"	=> Tools::getonlineip(),
			);
			
			$memberdb = Myqee::config('core.member_db');
			$memberdb or $memberdb = 'members';

			if ($isuseucenter = Myqee::config('core.use_ucenter')){
				$ucenter = new Ucenter_Api(true);

				$ucresult = uc_user_checkname($username);
				if($ucresult > 0) {
					//echo '用户名可用';
				} elseif($ucresult == -1) {
					Myqee::show_error("用户名不合法！",NULL,true);
				} elseif($ucresult == -2) {
					Myqee::show_error("包含要允许注册的词语！",NULL,true);
				} elseif($ucresult == -3) {
					Myqee::show_error("用户名已经存在！",NULL,true);
				}


				$ucresult = uc_user_checkemail($_POST['email']);
				if($ucresult > 0) {
					//echo 'Email 格式正确';
				} elseif($ucresult == -4) {
					Myqee::show_error("Email 格式有误！",NULL,true);
				} elseif($ucresult == -5) {
					Myqee::show_error("Email 不允许注册！",NULL,true);
				} elseif($ucresult == -6) {
					Myqee::show_error("该 Email 已经被注册！",NULL,true);
				}

				$uid = uc_user_register($username, $_POST['password'], $_POST['email']);
				if($uid <= 0) {
					if($uid == -1) {
						Myqee::show_error("用户名不合法！",NULL,true);
					} elseif($uid == -2) {
						Myqee::show_error("包含要允许注册的词语！",NULL,true);
					} elseif($uid == -3) {
						Myqee::show_error("用户名已经存在！",NULL,true);
					} elseif($uid == -4) {
						Myqee::show_error("Email 格式有误！",NULL,true);
					} elseif($uid == -5) {
						Myqee::show_error("Email 不允许注册！",NULL,true);
					} elseif($uid == -6) {
						Myqee::show_error("该 Email 已经被注册！",NULL,true);
					} else {
						Myqee::show_error("未定义错误！",NULL,true);
					}
				} else {
					$db = Myqee::db();
					//检测用户名
					$insert_data['id'] = $uid;
					
					$query = $db -> merge($memberdb,$insert_data);
				}

			}else{
				$db = Myqee::db();
				//检测用户名
				$query = $db -> count_records($memberdb,array("username",$username) );
				if ($query>0) Myqee::show_error("该用户名已经存在！",NULL,true);
				
				//检测邮箱
				$query = $db -> count_records($memberdb,array("email",$_POST['email']) );
				if ($query>0) Myqee::show_error("该邮箱已经存在！",NULL,true);
			

				$query = $db->insert($memberdb,$insert_data);
				$uid = $query->insert_id();
			}
			
			if (Myqee::config('core.use_uchome')){
				//为UCHOME添加用户
				Uchome_Api::instance() -> new_user($uid,$username,$_POST['password'],$_POST['email']);
				
				//为邀请设置好友
				if ($_POST['uid']>0 && $_POST['code']){
					if (Passport::check_code($_POST['uid'],'tofriend',$_POST['code'])){
						Uchome_Api::instance() -> add_friend($_POST['uid'],$uid);
						
						if ($isuseucenter){
							//发生注册邀请的信息
							uc_pm_send(0,$_POST['uid'],$username.'接受了你的邀请已注册成会员', '<a href="'.Myqee::config('core.home_url').'/space.php?uid='.$uid.'">'.$username.'</a>刚刚接受你的邀请注册成为会员，赶快去看看他的空间吧！' ,1);
						}
					}
				}
			}

			echo Passport::set_login($uid,$username);
			$homeurl = Myqee::config('core.home_url');
			Myqee::show_ok("恭喜注册成功！",$homeurl?$homeurl.'/cp.php?ac=avatar':Myqee::config('core.mysite_url'),true);
		}
	}
}