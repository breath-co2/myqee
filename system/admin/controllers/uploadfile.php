<?php
class Uploadfile_Controller_Core extends Controller {
	function __construct() {
		parent::__construct ();
	}
	
	protected function _chkadmin() {
		Passport::chkadmin ();
	}
	
	
	public function index() {
		$this->_chkadmin ();
		Passport::checkallow('info.uploadlist');
		$per=20;
		$view = new View ( 'admin/upload_files_list' );
		
		$this->db = Database::instance ();
		$num = $this -> db -> count_records('[uploadfiles]');

		$this -> pagination = new Pagination( array(
			'uri_segment'    => 'index',
			'total_items'    => $num,
			'items_per_page' => $per
		) );
		
		$view->set ( 'list', $this->db->limit($per,$this->pagination->sql_offset())->orderby ( 'id', 'DESC' )->getwhere ( '[uploadfiles]' )->result_array ( FALSE ) );
		$view -> set('page', $this -> pagination -> render('digg') );
		$view->render ( TRUE );
	}
	
	public function up() {
		$this->_chkadmin ();
		Passport::checkallow('info.uploadfile');
		$view = new View ( 'admin/upload_file' );
		$view->set('checekinfo',$this->_get_checkinfo());
		$view->render ( TRUE );
	}
	
    public function del($id) {
		$this->_chkadmin ();
    	Passport::checkallow('info.uploaddel');
		$id = ( int ) $id;
		$this->db = Database::instance ();
		$file_info = $this->db->getwhere ( '[uploadfiles]', array ('id' => $id ) )->result_array ( FALSE );
		$file_info = $file_info [0];
		if (! $file_info) {
			MyqeeCMS::show_error ( Myqee::lang ( 'admin/upload.error.momodel' ), true );
		}
		
		//MyqeeCMS::delconfig('model/model_'.$id);exit;
		if (count ( $this->db->delete ( '[uploadfiles]', array ('id' => $id ) ) )) {
		
			MyqeeCMS::show_info ( Myqee::lang ( 'admin/upload.info.delsuccess' ), true, 'refresh' );
		} else {
			MyqeeCMS::show_info ( Myqee::lang ( 'admin/upload.info.nodelete' ), true );
		}
	}
	
	public function upfile() {
		if (!$this->_chkislogin ( $_POST )){
			exit;
		}
		$upinfo = Upload::save($_POST['config']);
		$upinfo ['url'] = $upinfo ['path'] . $upinfo ['name'] . '.' . $upinfo ['extension'];
		$this -> db = Database::instance();
       
        $fileinfo['name']=$upinfo['name'];
        $fileinfo['filename'] = $upinfo['oldname'];
        $fileinfo['urlpath']  = $upinfo['url'];
        $fileinfo['size'] = $upinfo['filesize'];
        $fileinfo['filetype'] = $upinfo['extension'];
        $fileinfo['suffix'] = $upinfo['extension'];
        $fileinfo['host']='';
        $fileinfo['uploadtime']=$_SERVER['REQUEST_TIME'];
        $fileinfo['content']=$upinfo['oldname'];
         //print_r($fileinfo);
		$status = $this -> db -> insert('[uploadfiles]',$fileinfo);
		$fileid = $status -> insert_id();
		
		echo Tools::json_encode ( $upinfo );
	}
	
	public function fckeditor($type = null) {
		$this->_chkadmin ();
		Passport::checkallow('info.uploadexplorer');
		if ($type != 'connector')
			$type = 'upload';
		chdir (MYQEEPATH . 'api/ckfinder/' );
		if ($type=='upload'||$type=='connector'){
			require ($type . EXT);
		}
		exit ();
	}
	
	public function inframe($type = null, $limit_file = 0 ,$config = 'default') {
		$this->_chkadmin ();
		Passport::checkallow('info.uploadexplorer','',TRUE);
		$view = new View ( 'admin/upload_file_frame' );
		if ($type == 'upimg') {
			$view->set ( 'allow_type', array ('gif', 'jpg', 'jpeg', 'png' , 'bmp' ) );
		}elseif($type == 'upflash') {
			$view->set ( 'allow_type', array ('swf') );
		}
		if ($config && $config!='default' && preg_match("/[a-zA-Z0-9]+/",$config)){
			$config_upload= Myqee::config('upload.'.$config);
		}
		if (!$config_upload||!is_array($config_upload)){
			$config_upload = Myqee::config('core.upload');
			$config = 'default';
		}
		$view->set('config=',$config);
		$view->set('config_upload',$config_upload);
		$view->set('checekinfo',$this->_get_checkinfo());
		$view->set ( 'limit_file', $limit_file );
		$view->render ( TRUE );
	}
	
	protected function _get_checkinfo(){
		$time = $_SERVER['REQUEST_TIME'];
		$sid = Session::instance()->id();
		$adminid = Session::instance()->get('admin.id');
		$key = Myqee::config('encryption.default.key');
		return array(
			'sid' => $sid,
			'time' => $time,
			'adminid' => $adminid,
			'code' =>md5($key.'___'.$time.'___'.$adminid.'__'.$sid),
		);
	}
	
	protected function _check_code($sid,$adminid,$time,$code){
		$key = Myqee::config('encryption.default.key');
		if (md5($key.'___'.$time.'___'.$adminid.'__'.$sid)==$code){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	

	protected function _chkislogin($postinfo) {
		if ($postinfo['time']==0 || $_SERVER['REQUEST_TIME']-$postinfo['time']>1800){
			return FALSE;
		}
		if (!$postinfo['adminid'])return FALSE;
		if (!Passport::getisallow('info.uploadfile',$postinfo['adminid'])){
			return FALSE;
		}
		return $this->_check_code($postinfo['sid'],$postinfo['adminid'],$postinfo['time'],$postinfo['code']);
	}
}