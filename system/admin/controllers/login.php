<?php
class Login_Controller_Core extends Controller {
	public function __construct(){
		parent::__construct();
	}

	public function index(){
		$this->session = Session::instance();
		$view = new View('admin/login');
		$view-> set ('errinfo' ,$this->session->get('loginerr') );
		if ($this->session->get('loginerr_num')>=3){
			$view -> set ('tooerror',true);
		}
		$view->render(TRUE);
	}
	
	public function test(){
		echo 'Hello World';
	}

	public function login(){
		$input=$_POST;
		$this->session = Session::instance();
		
		if ($this->session->get('loginerr_num')>=3){
			if (!Captcha::valid($input['captcha'])){
				$this -> _goerrorpage(Myqee::lang('admin/login.err.errorcaptcha'),$input['forward']);
			}
		}
		
		if (empty($input['username'])){
			$this -> _goerrorpage(Myqee::lang('admin/login.err.nullusername'),$input['forward']);
		}
		if (empty($input['password'])){
			$this -> _goerrorpage(Myqee::lang('admin/login.err.nullpassword'),$input['forward']);
		}
		
		$password=md5($input['password']);
		$db = Database::instance();
		$query = $db -> from('[admin]') -> where(array('username' =>$input['username'], 'password' => $password)) -> limit(1) -> get();
		if ( count( $query ) == 0 ){
			$this -> _goerrorpage(Myqee::lang('admin/login.err.user_pass'),$input['forward']);
		}
		
		//login OK
		$row=$query->result();
		$row=$row[0];
		$ip = Tools::ip_address();
		$db->update('[admin]', array('lastloginip' => $ip,'lastlogintime'=>$_SERVER['REQUEST_TIME'],'countlogin'=>$row -> countlogin + 1), array('id' => $row->id));
		
		//读取组权限
		$userDate=array(
			'id' => $row->id,
			'username' => $row->username,
			'lastloginip' => $row->lastloginip,
			'thisloginip' => $ip,
			'lastlogintime' => $row->lastlogintime,
			'thislogintime' => $_SERVER['REQUEST_TIME'],
			'countlogin' => $row->countlogin+1,
			'groupid' => $row->groupid,
			'groupname' => $row->groupname,
			'competence' => $row->competence,
			'dbset' => $row->dbset,
			'classset' => $row->classset,
			'siteset' => $row->siteset,
			'defaultsite' => (int)$row->defaultsite,
		);
		
		$this->session->set('admin',$userDate);
		$this->session->set('login_time',$_SERVER['REQUEST_TIME']);
		$this->session->delete('loginerr_num');
		if ($userDate['defaultsite']>0){
			$siteconfig = MyqeeCMS::config('site/'.$userDate['defaultsite']);
			if ($siteconfig['isuse']){
				$this->session->set('now_site',$userDate['defaultsite']);
				$this->session->set('now_site_name',$siteconfig['sitename']);
				$this->session->set('now_site_tlpgroup',$siteconfig['config']['template_group']);
			}
		}
		
		Cache::instance()->delete($_SERVER['HTTP_HOST'].'admin.header_admin_'.$row->id);
		
		if ($input['forward']){
			header("location:".$input['forward']);
		}else{
			header("location:".Myqee::url('index'));
		}
	}
	
	public function logout(){
		$this->session = new Session;
		Cache::instance()->delete($_SERVER['HTTP_HOST'].'admin.header_admin_'.$this->session->get('admin.id'));
		$this->session->destroy();
		header("location:".Myqee::url('login'));
	}
	
	protected function _goerrorpage($msg,$forward = FALSE){
		$this->session->set_flash('loginerr',$msg);
		$this->session->set('loginerr_num',$this->session->get('loginerr_num')+1);
		header("location:".Myqee::url('login/index') . ($forward?'?'.($_GET['h']?'h=none&':'').'forward='.urlencode($forward):'') );
		exit;
	}
	
	public function loginok(){
		echo '<script type="text/javascript">parent.alert("登陆成功，请<font style=\'color:red\'>重新操作</font>！",400);</script>';
	}
}