<?php
class Login_Controller_Core extends Controller {
	protected $ucenterapi;
	public function __construct(){
		parent::__construct();
	}
	
	public function iframe(){
		//$this -> ucenterapi = Ucenter_Api::instance(true);
		//Passport::set_login(1);
		if (Passport::check_islogin()){
			echo '已登陆';
		}else{
			echo '请先登陆';
		}
	}

	public function index(){
		if (!$v = Myqee::config('core.systemplate.login')){
			$v = 'login';
		}
		$view = new View($v);
		$view -> render(true);
	}

	public function frame_login(){
		if (!$v = Myqee::config('core.systemplate.frame_login')){
			$v = 'frame_login';
		}
		$view = new View($v);
		$view -> render(true);
	}
	
	public function login(){
		if (!$_POST['imagecode']){
			Myqee::show_error("请填写验证码！",NULL,true);
		}else{
			if (Captcha::valid($_POST['imagecode']) <= 0 ){
				Myqee::show_error("验证码填写错误！",NULL,true);
			}
		}
	if (!$_POST['username']){
			Myqee::show_error("请填写用户名！",NULL,true);
		}
		if (!$_POST['password']){
			Myqee::show_error("请填写密码！",NULL,true);
		}

		$memberdb = Myqee::config('core.member_db');
		$memberdb or $memberdb = 'members';
		if (Myqee::config('core.use_ucenter')){
			//UCHOME
			$this -> ucenterapi || $this -> ucenterapi = new Ucenter_Api(true);
			list($uid, $username, $password, $email) = uc_user_login($_POST['username'], $_POST['password'] , $_POST['isuid']?1:0 );
			
			if($uid > 0) {
				$membermodel = new Model_Member();
				if (!$membermodel -> get_profile($uid)){
					Myqee::db() -> insert($memberdb,array('id'=>$uid,'username'=>$username,'email'=>$email,'password'=>$password));
				}
				
				echo Passport::set_login($uid,$username,$_POST['autologin']?true:false);
				
				Myqee::show_ok("恭喜，登录成功！",$_REQUEST['forward']?$_REQUEST['forward']:SITE_URL,true);
				
			} elseif($uid == -1) {
				Myqee::show_error("用户不存在,或者被删除！",NULL,true);
			} elseif($uid == -2) {
				Myqee::show_error("用户名或密码错误！",NULL,true);
			} else {
				Myqee::show_error("未定义错误，请联系管理员！",NULL,true);
			}
		}else{
			$membermodel = new Model_Member();
			if ($user = $membermodel -> get_profile($username,'password',true)){

				if ($user['password']==md5($_POST['password'])){

					echo Passport::set_login($uid,$username,$_POST['autologin']?true:false);
					Myqee::show_ok("恭喜，登录成功！",$_REQUEST['forward']?$_REQUEST['forward']:SITE_URL,true);

				}else{
					Myqee::show_error("用户名或密码错误！",NULL,true);
				}
				
			}else{
				Myqee::show_error("用户不存在,或者被删除！",NULL,true);
			}
		}
	}

	public function logout(){
		echo Passport::set_logout();
		Myqee::show_info("退出成功，欢迎再次访问！",$_GET['forware']?$_GET['forware']:SITE_URL,true);
	}
	
}