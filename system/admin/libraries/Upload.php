<?php
class Upload_Core {
	protected static $upload_name = 'Filedata';
	protected static $configs = array();
	
	public static function setconfig($myconfig=null){
		if (is_array($myconfig)){
			$config = array_merge(Myqee::config('core.upload'),$myconfig);
		}elseif($myconfig){
			$config = Myqee::config('upload.'.$myconfig);
		}
		if (!$config){
			$config = Myqee::config('core.upload');
		}
		self::$configs['gifwatermark'] = Myqee::config('core.watermark.gifwatermark');
		
		self::$configs['floder'] = !empty($config['floder'])?$config['floder']:'upload/';
		self::$configs['maxsize'] = $config['maxsize']>0?$config['maxsize']*1024:1048576;
		if (isset($config['extension']))self::$configs['extension'] = $config['extension'];
		if (isset($config['autothumb']) && $config['autothumb'] ==1){
			self::$configs['autothumb'] = true;
			self::$configs['thumbwidth'] = $config['thumbwidth']?$config['thumbwidth']:120;
			self::$configs['thumbheight'] = $config['thumbheight']?$config['thumbheight']:90;
		}
		if (isset($config['autowatermark']) && $config['autowatermark'] ==1){
			self::$configs['autowatermark'] = true;
		}

		if($config['filepath']){
			$config['filepath'] = str_replace('\\','/',$config['filepath']);
			if (substr($config['filepath'],0,1)=='/'){
				self::$configs['filepath'] = rtrim($config['filepath'],'/').'/';
			}elseif (true===MYQEE_IS_WIN && preg_match("/^[a-zA-Z]\:\/.*/",$config['filepath'])){
				self::$configs['filepath'] = rtrim($config['filepath'],'/').'/';
			}else{
				self::$configs['filepath'] = WWWROOT . $config['filepath'].'/';
			}
		}else{
			self::$configs['filepath'] = UPLOADPATH;
		}
		self::$configs['selfpath'] = isset($config['selfpath']) ? $config['selfpath']:'Y/m/d';
		self::$configs['chmod'] = isset($config['chmod']) ? $config['chmod']:0755;
		self::$configs['setname'] = $config['setname'];
		self::$configs['urlpath'] = $config['urlpath'];

		return self;
	}
	public static function save( $config = NULL){
		self::setconfig();

		$POST_MAX_SIZE = ini_get('post_max_size');
		$unit = strtoupper(substr($POST_MAX_SIZE, -1));
		$multiplier = ($unit == 'M' ? 1048576 : ($unit == 'K' ? 1024 : ($unit == 'G' ? 1073741824 : 1)));

		if ((int)$_SERVER['CONTENT_LENGTH'] > $multiplier*(int)$POST_MAX_SIZE && $POST_MAX_SIZE) {
			self::HandleError('上传文件超出系统允许上传的大小限制！');
		}

	// Other variables	
		$file_name = "";
		$file_extension = "";
		$upload_name = self::$upload_name;
		
		$uploadErrors = array(
			0=>'There is no error, the file uploaded with success',
			1=>'"The uploaded file exceeds the upload_max_filesize directive in php.ini',
			2=>'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
			3=>'The uploaded file was only partially uploaded',
			4=>'No file was uploaded',
			6=>'Missing a temporary folder'
		);

	// Validate the upload
		if (!isset($_FILES[$upload_name])) {
			self::HandleError('没有上传任何文件！');
		} else if (isset($_FILES[$upload_name]["error"]) && $_FILES[$upload_name]["error"] != 0) {
			self::HandleError($uploadErrors[$_FILES[$upload_name]["error"]]);
		} else if (!isset($_FILES[$upload_name]["tmp_name"]) || !@is_uploaded_file($_FILES[$upload_name]["tmp_name"])) {
			self::HandleError('上传失败');
		} else if (!isset($_FILES[$upload_name]['name'])) {
			self::HandleError('文件名缺失');
		}
		
	// Validate the file size (Warning: the largest files supported by this code is 2GB)
		$file_size = @filesize($_FILES[$upload_name]["tmp_name"]);
		if (!$file_size || $file_size > self::$configs['maxsize']) {
			self::HandleError('文件超出大小限制！');
		}
		
		if ($file_size <= 0) {
			self::HandleError('获取上传文件失败，请重新上传或联系管理员！');
		}
		
	// Validate file extension
		$path_info = pathinfo($_FILES[$upload_name]['name']);
		$file_extension = strtolower($path_info["extension"]);
		
		if (!empty(self::$configs['extension']) && !in_array($file_extension,explode(',',self::$configs['extension']))) {
			self::HandleError('不允许上传“'.htmlspecialchars($file_extension).'”类文件！');
		}
		
		self::$configs['selfpath'] = trim(self::$configs['selfpath'] ,' /.');
		$savefloder = self::$configs['selfpath'] ? date(self::$configs['selfpath']) .'/' : '';
		
		$filepath = str_replace('//','/',self::$configs['filepath'].$savefloder);
		if (!is_dir($filepath))Tools::create_dir($filepath);			//创建文件夹
		
		switch (self::$configs['setname']){
			case 'time':
				$new_file_name = date("YmdHis").mt_rand(1000,9999);
			break;
			case 'abc123':
				$new_file_name = Tools::get_rand(20);
			break;
			case 'md5':
				$new_file_name = md5($_SERVER['REQUEST_TIME'].'___'.print_r($_FILES[$upload_name],true));
			break;
			case 'sha1':
				$new_file_name = sha1($_SERVER['REQUEST_TIME'].'___'.print_r($_FILES[$upload_name],true));
			break;
			default:
				$new_file_name_old = preg_replace( '/\\\\|\\/|\\||\\:|\\?|\\*|"|<|>|[[:cntrl:]]/', '_',substr($_FILES[$upload_name]['name'],0,-1-strlen($file_extension)) );
				$new_file_name = iconv('UTF-8','CP936//IGNORE',$new_file_name_old);
			break;
		}
		$iCounter = 0 ;
		$sFileName = '';
		while ( true ){
			$fullpath = $filepath.urlencode($new_file_name).$sFileName.'.'.$file_extension;
			if ( is_file( $fullpath ) ){
				$iCounter++ ;
				$sFileName = '(' . $iCounter . ')';
			}else{
				if (!@move_uploaded_file($_FILES[$upload_name]["tmp_name"], $fullpath)) {
					self::HandleError('保存失败，可能目录缺少写入权限！');
				}
				$new_file_name = $new_file_name.$sFileName;
				break;
			}
		}
		
		
		$imgext = array('jpg','jpeg','png');
		if (self::$configs['gifwatermark'])$imgext[] = 'gif';
		//创建水印
		if (self::$configs['autowatermark']==true && in_array($file_extension , $imgext)){
			Image::factory($fullpath) -> watermark();
		}

		if (isset(self::$configs['chmod']) != FALSE){
			// Set permissions on filename
			chmod($fullpath, 0755);
		}
		//创建缩略图
		$imgext[] = 'gif';
		if (self::$configs['autothumb']==true && in_array($file_extension , $imgext)){
			Image::factory($fullpath) -> resize(self::$configs['thumbwidth'],self::$configs['thumbheight']) -> save($filepath.$new_file_name.'_thumb.'.$file_extension);
		}
		//恢复为UTF-8编码的
		$new_file_name_old and $new_file_name = $new_file_name_old.$sFileName;
		return array(
			'name' => urlencode($new_file_name),
			'oldname' => $_FILES[$upload_name]['name'],
			'path' => self::$configs['urlpath'].$savefloder,
			'extension' => $file_extension,
			'filesize' => $file_size,
		);
	}

	public static function HandleError($message) {
		header("HTTP/1.1 500 Internal Server Error");
		echo $message;
	
		exit(0);
	}
	
}