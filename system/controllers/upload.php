<?php

class Upload_Controller_Core extends Controller{

	public function __construct(){
		if ($_GET['_showimage']=='yes' || !Myqee::config('core.aptitudethumb')){
			Myqee::show_404();
		}
	}
	public function _default(){
		//echo $_SERVER['PATH_INFO'];
		
		//$arguments = func_get_args();		//这个函数不区分大小写，郁闷
		//$path_info = METHOD_NAME . '/' .join('/',$arguments);
		$path_info = substr($_SERVER["PATH_INFO"],8);
		
		if (!preg_match("/^([0-9a-z\/]+\/)?([0-9a-z]+)_thumb(_([0-9]+)_([0-9]+))?\.(jpg|jpeg|gif|png|bmp)$/i",$path_info,$preg)){
			Myqee::show_404();
		}

		$myconfig = Myqee::config('core.upload');

		if ($preg[4]==='' && $preg[4]===''){
			$thumb_w = $myconfig['thumbwidth'];
			$thumb_h = $myconfig['thumbheight'];
		}else{
			if (!($preg[4]>=20 && $preg[4]<=300)){
				Myqee::show_404();
			}else{
				$thumb_w = (int)$preg[4];
			}
			if (!($preg[5]>=20 && $preg[5]<=300)){
				Myqee::show_404();
			}else{
				$thumb_h = (int)$preg[5];
			}
		}
		$filename = $preg[2];
		$fileext = $preg[6];
		$path_info = dirname($path_info);
		$uploadpath = WWWROOT.ltrim($myconfig['urlpath'],'/');
		
		$filepath = str_replace('\\','/',realpath( $uploadpath.'/'.$path_info ) ).'/';
		
		//目录有误
		if ($filepath == '/' || substr($filepath,0,strlen($uploadpath))!=$uploadpath){
			Myqee::show_404();
		}
		
		$thefile = $filepath . $filename.'.'.$fileext;
		
		if (!file_exists($thefile)){
			Myqee::show_404();
		}
		//echo $thefile;
		$thumb_file = $filepath.$filename.'_thumb'.($thumb_w==$myconfig['thumbwidth'] && $thumb_h==$myconfig['thumbheight'] ? '':'_'.$thumb_w.'_'.$thumb_h).'.'.$fileext;
		$image = new Image($thefile);
		$image = $image -> resize($thumb_w,$thumb_h,Image::INSIZE);
		$image = $image -> save($thumb_file);
		
		readfile($thumb_file);
		//header('location:'.$_SERVER["SCRIPT_URL"].'?_showimage=yes');
	}
}