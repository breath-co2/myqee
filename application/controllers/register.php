<?php
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
class Register_Controller{
	protected $key = 'aabb#&ccdd123&%';
	
	public function __construct() {
		define('TEMPLATE_GROUP','v2');
	}
	
	protected function _get_hash($step,$timeline){
		return md5($timeline.'_'.$this -> key.'_'.$step);
	}
	
	protected function _check_hash($step,$timeline,$hash){
		if (time() - $timeline >1800)return FALSE;
		return md5($timeline.'_'.$this -> key.'_'.$step)==$hash?TRUE:FALSE;
	}
	
	public function index(){
		$step = $this -> step ==2||$this -> step==3?$this -> step:1;
		$timeline = time();
		$hash = $this->_get_hash(($step+1),$timeline);
		
		$view = new View('register');
		$view -> set('step',$step);
		$view -> set('hash',$hash);
		$view -> set('timeline',$timeline);
		$view -> render(true);
	}
	
	public function step($hash='',$timeline=0,$step=1){
		if ($step==2||$step==3){
			if ( $this->_check_hash($step,$timeline,$hash) ){
				$this -> step = $step;
			}
		}
		$this -> index();
	}


	public function reg(){
		if ( !$this->_check_hash(3,$_POST['timeline'],$_POST['hash']) ){
			Myqee::show_error("您停留的时间过长或页面参数错误，请刷新后重新提交！",NULL,true);
		}
		//检测数据
		$username 	= Tools::formatstr($_POST['username'], 0, 1, 1);
		$email 		= Tools::formatstr($_POST['email'], 80, 1, 1);
		$password	= $_POST['password'];
		
		if(empty($username) || strlen($username) > 20 || strlen($username) < 6){
			Myqee::show_error("用户名长度错误，必须6-20位之间！",NULL,true);
		}
		
		if(strlen($password) <6 || $password != $_POST['ck_password']){
			Myqee::show_error("请重新输入密码！",NULL,true);
		}
		
		if(preg_match("#[^\w]#",$username)){
			Myqee::show_error("用户名中含有非法字符",NULL,true);
		}

		if(!valid::email($email)){
			Myqee::show_error("请认真填写邮箱！",NULL,true);
		}
		
		if (!$_POST['imagecode']){
			Myqee::show_error("请填写验证码！",NULL,true);
		}else{
			if (Captcha::valid($_POST['imagecode'])<=0){
				Myqee::show_error("验证码填写错误！<script>parent.$('imagecode').onclick();</script>",NULL,true);
			}
		}

		$insert_data = array(
			"username"		=> $username,
			"password"		=> md5($password),
			"email"			=> $email,
			"lastloginip"	=> Tools::getonlineip(),
		);
		
		//高级项
		if ($_POST['pro'] && $profile = $this -> _get_proinfo($_POST['pro'])){
			$insert_data += $profile;
		}
		
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

			$ucresult = uc_user_checkemail($email);
			if($ucresult > 0) {
				//echo 'Email 格式正确';
			} elseif($ucresult == -4) {
				Myqee::show_error("Email 格式有误！",NULL,true);
			} elseif($ucresult == -5) {
				Myqee::show_error("Email 不允许注册！",NULL,true);
			} elseif($ucresult == -6) {
				Myqee::show_error("该 Email 已经被注册！",NULL,true);
			}

			$uid = uc_user_register($username, $password, $email);
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
			$query = $db -> count_records($memberdb,array("email",$email) );
			if ($query>0) Myqee::show_error("该邮箱已经存在！",NULL,true);
		

			$query = $db->insert($memberdb,$insert_data);
			$uid = $query->insert_id();
		}
		
		if (Myqee::config('core.use_uchome')){
			$spacefield = array(
				'qq' => $insert_data['qq'],
			);
			if ($profile){
				//高级字段
				if ($profile['name'])$profield['name'] = array($profile['name']);
				$spacefield += array(
					'sex'				=> $profile['sex'],
					'msn'				=> $profile['msn'],
					'birthyear'			=> $profile['birthyear'],
					'birthmonth'		=> $profile['birthmonth'],
					'birthday'			=> $profile['birthday'],
					'blood'				=> $profile['blood'],			//血型
					'marry'				=> $profile['marry'],			//婚姻状态
					'birthprovince'		=> $profile['birthprovince'],	//出生省
					'birthcity'			=> $profile['birthprovince'],	//出生市
					'resideprovince'	=> $profile['resideprovince'],	//居住省
					'residecity'		=> $profile['resideprovince'],	//居住市
					'spacenote'			=> $profile['content'],			//个人说明
				);
			}
			//为UCHOME添加用户
			Uchome_Api::instance() -> new_user($uid,$username,$password,$email,$profield,$spacefield);
			
			//为邀请设置好友
			if ($_POST['uid']>0 && $_POST['code']){
				if (Passport::check_code($_POST['uid'],'tofriend',$_POST['code'])){
					Uchome_Api::instance() -> add_friend($_POST['uid'],$uid);
					
					if ($isuseucenter){
						//发生注册邀请的信息回执
						uc_pm_send(0,$_POST['uid'],$username.'接受了你的邀请已注册成会员', '<a href="'.Myqee::config('core.home_url').'/space.php?uid='.$uid.'">'.$username.'</a>刚刚接受你的邀请注册成为会员，赶快去看看他的空间吧！' ,1);
					}
				}
			}
		}
		

		echo Passport::set_login($uid,$username);
		
		$timeline = time();
		$hash = $this -> _get_hash('3',$timeline);
		
		echo '<script type="text/javascript">parent.location.href="'.Myqee::url('register/step/'.$hash.'/'.$timeline.'/3').'";</script>';
		//$homeurl = Myqee::config('core.home_url');
		//Myqee::show_ok("恭喜注册成功！",$homeurl?$homeurl.'/cp.php?ac=avatar':Myqee::config('core.mysite_url'),true);
		
	}
	
	protected function _get_proinfo($data){
		if (!$data || !is_array($data))return null;
		return array(
			'name'				=> Tools::formatstr($data['name'], 10, 1, 1, 1),
			'sex'				=> $data['sex']>=0?$data['sex']:0,
			'qq'				=> Tools::formatstr($data['qq'], 20, 1, 1),
			'msn'				=> Tools::formatstr($data['msn'], 80, 1, 1),
			'birthyear'			=> $data['birthyear']>1990&&$data['birthyear']<2010?(int)$data['birthyear']:0,
			'birthmonth'		=> $data['birthmonth']>0&&$data['birthmonth']<=12?(int)$data['birthmonth']:0,
			'birthday'			=> $data['birthday']>0&&$data['birthday']<=31?(int)$data['birthday']:0,
			'blood'				=> in_array($data['blood'],array('A','B','AB','O'))?$data['blood']:'',	//血型
			'marry'				=> $data['marry']>=0&&$data['marry']<=10?(int)$data['marry']:0,			//婚姻状态
			'birthprovince'		=> Tools::formatstr($data['birthprovince'], 20, 1, 1),					//出生省
			'birthcity'			=> Tools::formatstr($data['birthprovince'], 20, 1, 1),					//出生市
			'resideprovince'	=> Tools::formatstr($data['resideprovince'], 20, 1, 1),					//居住省
			'residecity'		=> Tools::formatstr($data['resideprovince'], 20, 1, 1),					//居住市
			'spacenote'			=> Tools::formatstr($data['content'],500),								//个人说明
		);
	}
	
	
}