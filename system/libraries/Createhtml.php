<?php
defined('MYQEEPATH') or die('No direct script access.');
class Createhtml_Core {

	protected $myclass = array();

	public function __construct(){
		
	}
	
	/**
	 * Enter description here...
	 *
	 * @return Createhtml_Core
	 */
	public static function instance(){
		static $instance;

		// Create the instance if it does not exist
		($instance === NULL) and $instance = new Createhtml;

		return $instance;
	}

	public function getclassurl($class_info,$page=0,$issearch=false){
		if ( !is_array($class_info)){
			$class_info = Myqee::myclass($class_info);
		}
		if (!isset($this -> mysiteurl))$this -> mysiteurl = Myqee::config('core.mysite_url');
		
		
		if($class_info['isnothtml']!=0 
			|| (!$class_info['iscover']&&!$class_info['islist']) 
			|| (!$page && ( ($class_info['iscover']&&$class_info['cover_tohtml']) || (!$class_info['iscover']&&$class_info['list_tohtml']) ) ) 
			|| ($page && $class_info['islist'] && $class_info['list_tohtml'])
			|| $issearch
		){
			if (!isset($this -> url_suffix)) $this -> url_suffix = Myqee::config('core.url_suffix');
			
			if (substr($this -> mysiteurl,0,7)=='http://'){
				$host = $this -> mysiteurl;
			}else{
				$host = 'http://'.Myqee::config('core.mysite_domain').$this -> mysiteurl;
			}
			$myqeepage = Myqee::config('core.myqee_page');
			if ($myqeepage){
				$host .= $myqeepage.'/';
			}
			$theurl = $host.'myclass/'.($issearch?'search/':'').substr(Des::Encrypt('['.$class_info['classid'].']',Myqee::config('encryption.urlcode.key')),2).($page?'/'.$page:'').$this -> url_suffix;
		}else{
			if ($class_info['hostname']){
				$theurl = 'http://'.$class_info['hostname'];
			}else{
				$theurl = $this -> mysiteurl .$class_info['classpath'];
			}
			$theurl = rtrim($theurl,'/').'/';
			
			if ($page && $class_info['islist']){
				//列表
				$theurl .= str_replace('{{page}}',$page,$class_info['list_filename']);
			}else{
				//封面
				if (!$class_info['cover_hiddenfilename']){
					$theurl .= $class_info['cover_filename'];
				}
			}
		}
	
		return $theurl;
	}
	
	/**
	 * 返回文件网页路径
	 *
	 * @param int/string $classid 栏目ID/数据表名称
	 * @param int $theinfo 信息内容
	 */
	public function getinfourl($classid,$theinfo,$path=null){
		$path or $path = $this -> getinfopath($classid,$theinfo,false);
		if ($classid>0){
			$this->myclass[$classid] or $this->myclass[$classid]=Myqee::myclass($classid);
			$dbname = $this->myclass[$classid]['dbname'];
		}else{
			$dbname = $classid;
		}
		if (strpos($dbname,'/')===false){
			$database = 'default';
			$tablename = $dbname;
			$dbname = $database.'/'.$dbname;
		}else{
			list($database,$tablename) = explode('/',$dbname);
		}
		$this->dbconfig[$dbname] or $this->dbconfig[$dbname] = Myqee::config('db/'.$dbname);
		if ($this->dbconfig[$dbname]['sys_field']['linkurl']){
			if ($mylinkUrl = $theinfo[$this->dbconfig[$dbname]['sys_field']['linkurl']])return $mylinkUrl;
		}

		if ( !($classid>0) ){
			$classid = $theinfo[$this->dbconfig[$dbname]['sys_field']['class_id']];
			if ( $classid>0 && !$this->myclass[$classid] ){
				$this->myclass[$classid]=Myqee::myclass($classid);
			}
		}
		
		$info_url = Myqee::config('core.mysite_url');
		if ($path && file_exists(WWWROOT.$path)){
			if ($classid>0 && $this->myclass[$classid]['hostname'] && substr($path,0,strlen($this->myclass[$classid]['classpath']))==$this->myclass[$classid]['classpath']){
				$path = substr($path,strlen($this->myclass[$classid]['classpath']));
				$info_url = 'http://'.$this->myclass[$classid]['hostname'].'/';
			}

			return $info_url.ltrim($path,'/');
		}else{
			//缺少数据表信息将无法获取信息内容，返回空链接
			if (!$dbname)return '#dberror';
			
			$this->dbconfig[$dbname] or $this->dbconfig[$dbname]=Myqee::config('db/'.$dbname);
			//缺少唯一标示将无法定位具体信息，返回空链接
			if (!$this->dbconfig[$dbname]['sys_field']['id'])return '#iderror';
			
			$myqeepage = Myqee::config('core.myqee_page');
			if ($myqeepage){
				$info_url .= $myqeepage.'/';
			}
//			return $info_url .= 'myinfo/'.Des::Encrypt('[51,2382]',Myqee::config('encryption.urlcode.key'));
//			return $info_url .= 'myinfo/'.('['.($classid>0?$classid:$dbname).','.$theinfo[$this->dbconfig[$dbname]['sys_field']['id']].']').Myqee::config('encryption.urlcode.key').Myqee::config('core.url_suffix');
			$info_url .= 'myinfo/'.substr(Des::Encrypt(($classid>0?$classid:$dbname).','.$theinfo[$this->dbconfig[$dbname]['sys_field']['id']],Myqee::config('encryption.urlcode.key')),2).Myqee::config('core.url_suffix');
			return $info_url;
		}
	}

	/**
	 * 返回文件路径
	 *
	 * @param int/string $classid 栏目ID/数据表名称
	 * @param array $theinfo 信息内容
	 * @param boolean $retrun_array 是否返回数组
	 */
	public function getinfopath($classid,$theinfo,$retrun_array = false){
		//栏目信息
		if ($classid>0){
			$this->myclass[$classid] or $this->myclass[$classid]=Myqee::myclass($classid);
			$dbname = $this->myclass[$classid]['dbname'];
		}else{
			$dbname = $classid;
			$classid = NULL;
		}
		if (strpos($dbname,'/')===false){
			$database = 'default';
			$tablename = $dbname;
			$dbname = $database.'/'.$dbname;
		}else{
			list($database,$tablename) = explode('/',$dbname);
		}
		if (!$tablename)return false;
		
		$this->dbconfig[$dbname] or $this->dbconfig[$dbname]=Myqee::config('db/'.$dbname);
		
		//根据内容信息的ID重新获取信息classid（如果存在）
		if ( !($classid>0) ){
			$classid = $theinfo[$this->dbconfig[$dbname]['sys_field']['class_id']];
			if ( $classid>0 && !$this->myclass[$classid] ){
				$this->myclass[$classid]=Myqee::myclass($classid);
			}
		}

		if ( isset($this->dbconfig[$dbname]['sys_field']['filepath']) && !empty($theinfo[$this->dbconfig[$dbname]['sys_field']['filepath']]) ){
			//信息字段中存在filepath字段且不为空
			if ($classid>0 && !$this->myclass[$classid]['content_pathtype']){
				$thepath =rtrim($this->myclass[$classid]['classpath'],'/') .'/';
			}
			$thepath .= ltrim($theinfo[$this->dbconfig[$dbname]['sys_field']['filepath']],'/');
		}else{
			if ($classid>0){
				if ($this->myclass[$classid]['content_pathtype']){
					$thepath =trim($this->myclass[$classid]['content_path'],'/');
				}else{
					$classpath =$this->myclass[$classid]['classpath'];
				}
				$classpath = rtrim($classpath,'/');
				if ($this->myclass[$classid]['content_selfpath'] && $this->dbconfig[$dbname]['sys_field']['createtime']){
					if ($theinfo[$this->dbconfig[$dbname]['sys_field']['createtime']]){
						$createtime = $theinfo[$this->dbconfig[$dbname]['sys_field']['createtime']];
					}else{
						$createtime = $_SERVER['REQUEST_TIME'];
						//更新记录
						$updateinfo = array( $this->dbconfig[$dbname]['sys_field']['createtime']=> $createtime );
					}
					$thepath = ($thepath?$thepath.'/':'').date($this->myclass[$classid]['content_selfpath'],$createtime).'/';
				}
				
				if (isset($this->dbconfig[$dbname]['sys_field']['filepath'])){
					$updateinfo[$this->dbconfig[$dbname]['sys_field']['filepath']] = $thepath;
				}
			}else{
				$thepath = '';
			}
		}
		if ($classpath){
			$thepath = $classpath .'/'.$thepath;
		}

		//前缀
		if( $classid>0 ){
			$thename = $this->myclass[$classid]['content_prefix'];
		}else{
			$thename = '';
		}

		//文件名
		if ($this->dbconfig[$dbname]['sys_field']['filename']){
			if ($theinfo[$this->dbconfig[$dbname]['sys_field']['filename']]){
				$thefullname = $theinfo[$this->dbconfig[$dbname]['sys_field']['filename']];
			}else{
				switch ($this->myclass[$classid]['content_filenametype']){
					case 0:
						$thename .= $theinfo[$this->dbconfig[$dbname]['sys_field']['id']];
						break;
					case 1:
						$thename .= $theinfo[$this->dbconfig[$dbname]['sys_field']['createtime']];
						break;
					case 2:
						$thename .= md5(print_r($theinfo,true).'__'.$_SERVER['REQUEST_TIME']);
						break;
					case 3:
						$thename .= substr(md5(print_r($theinfo,true).'__'.$_SERVER['REQUEST_TIME']),8,16);
						break;
					default:
						$thename .= $theinfo[$this->dbconfig[$dbname]['sys_field']['id']];
						break;
				}
			}
		}else{
			$thename .= $theinfo[$this->dbconfig[$dbname]['sys_field']['id']];
		}
		
		if (!$thefullname){
			//后缀（扩展名）
			if ($this->dbconfig[$dbname]['sys_field']['content_suffix'] && $theinfo[$this->dbconfig[$dbname]['sys_field']['content_suffix']]){
				$thename .= $theinfo[$this->dbconfig[$dbname]['sys_field']['content_suffix']];
			}elseif($classid>0){
				$thename .= $this->myclass[$classid]['content_suffix'];
			}else{
				$thename .= '.html';
			}
			$thefullname = $thename;
			if ($this->dbconfig[$dbname]['sys_field']['filename']){
				$updateinfo[$this->dbconfig[$dbname]['sys_field']['filename']] = $thefullname;
			}
		}

		if ($updateinfo && $this->dbconfig[$dbname]['sys_field']['id']){
			Myqee::db($database)->update(
				$tablename, 
				$updateinfo ,
				array( $this->dbconfig[$dbname]['sys_field']['id']=>$theinfo[$this->dbconfig[$dbname]['sys_field']['id']] )
			);
		}
		return $retrun_array?array('path'=>$thepath,'name'=>$thefullname):rtrim($thepath,'/').'/'.$thefullname;
	}

	/**
	 * 数据表是否存在
	 *
	 * @param string $dbname 数据表名称
	 * @return boolean true/false
	 */
	public function table_exists($dbname){
		if ( isset($this->db_exists[$dbname]) ){
			return (boolean)$this->db_exists[$dbname];
		}
		$mydbname = explode('/',$dbname,2);
		$database = $mydbname[0];
		$tablename = $mydbname[1];
		if ( $this->db_exists[$dbname] = Myqee::db($database)->table_exists($tablename) ){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * 生成文件
	 *
	 * @param string $template_id 模板ID/文件名
	 * @param string $tofilename 保存的文件名，目录是以wwwroot算起
	 * @param string $template_suffix 模板后缀
	 * @param string $template_group 所属模板组
	 * @return boolean true/false
	 */
	public function createhtml($template_id ,$tofilename=NULL, $data=NULL ,$viewtype=NULL ,$filepath=NULL){
		if (!$template_id)return;
		if ($template_id>0){
			if (!$this->gettpl_array($template_id))return false;
			$view = new View(
				$this->template[$template_id]['filename'],
				$data,
				$this->template[$template_id]['filename_suffix'],
				$this->template[$template_id]['group'],
				$viewtype
			);
		}else{
			$template_array = explode('.',$template_id);
			if (count($template_array)<=1){
				$filename_file = $template_id;
				$filename_suffix = NULL;
			}else{
				$filename_suffix = '.'.array_pop($template_array);
				$filename_file = join('.',$template_array);
				if ($filename_suffix==EXT)$filename_suffix = NULL;
			}
			
			$view = new View($filename_file,$data,$filename_suffix,NULL,$viewtype);
		}
//		$view -> set_global('database',DatabasePro::instance());
		if (is_string($tofilename)){
			if (!$filepath)$filepath = WWWROOT;
			$html = $view -> render(FALSE,FALSE,TRUE);
			$dirname = dirname($filepath.$tofilename);
			if (!is_dir($dirname))Tools::create_dir($dirname);
			if (Tools::createfile($filepath.$tofilename,$html)){
				unset($html);
				$istohtmlok = true;
			}else{
				$istohtmlok = '生成静态文件失败，可能目录不存在或无写入权限！';
			}
		}else{
			$istohtmlok = $view -> render($tofilename,FALSE,TRUE);
		}
		return $istohtmlok;
	}

	/**
	 * 获取模板名
	 * 
	 * @param int $template_id 模板ID
	 * @return array $tplinfo
	 */
	public function gettpl_array($template_id){
		if (!isset($this->template[$template_id])){
			$rs = Myqee::db() -> select('id','tplname','group','type','filename','filename_suffix') -> getwhere('[template]',array('isuse'=>1,'id'=>$template_id)) ->result_array ( FALSE );
			$this -> template[$template_id] = $rs[0];
		}
		if ($this -> template[$template_id]['filename_suffix']){
			if (!in_array($this->template[$template_id]['filename_suffix'],array('.txt','.tpl','.html','.htm'))){
				$this->template[$template_id]['filename_suffix'] = null;
			}
		}		
		return $this -> template[$template_id];
	}
	/*
	public function toinfohtml($dbname,$infoid=null){
		if (is_array($dbname)){
			$info_array = $dbname;
			unset($dbname);
		}else{
			$info_array = $this -> _getinfo_array($dbname,$infoid);
		}

	}


	protected function _getinfo_array($dbname,$infoid){
		//读取栏目信息
		$this -> myclass[$classid] or $this -> myclass[$classid] = Myqee::myclass($classid);

		if (!$this -> myclass[$nowclassid]['dbname']) return false;
		if ( !$this->_table_exists($this -> myclass[$nowclassid]['dbname']) ){
			return false;
		}
	}
*/
	
	public function get_location_array($classid,$myclass=null){
		if (!is_array($myclass)){
			if ($this->myclass[$classid]){
				$myclass = $this->myclass[$classid];
			}else{
				$myclass = Myqee::myclass($classid);
			}
		}
		if (!is_array($myclass))return false;
		$myfeather = array();
		if ($myclass['fatherclass']){
			$feather = explode('|',trim($myclass['fatherclass'],'|'));
			foreach ($feather as $cid){
				if ( $tempclass = Myqee::myclass($cid) ){
					$myfeather[] = $tempclass;
				}
			}
		}
		$myfeather[] = $myclass;
		return $myfeather;
	}
}