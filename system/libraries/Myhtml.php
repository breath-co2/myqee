<?php defined('MYQEEPATH') or die('No direct script access.');
/**
 * 为前台数据提供数据调用功能
 *
 * $Id: Myhtml.php,v 1.1 2009/11/11 12:00:04 tom Exp $
 *
 * @package    Core
 * @author     Myqee Team
 * @copyright  (c) 2008-2009 Myqee Team
 * @license    http://myqee.com/license.html
 */
class Myhtml_Core{
	
	protected static $dbconfig;
	
	/**
	 * 获取指定栏目地址
	 *
	 * @param int/array $class_info  可以是栏目ID页可以是栏目数组
	 * @param int $page 获取栏目指定页地址
	 * @param boolean $issearch 是否搜索
	 * @return string 栏目地址
	 */
	public function getclassurl($class_info,$page=0,$issearch=false){
		static $myurl;
		//快速静态缓存，用于提高一个请求中请求同一栏目地址返回效率
		$mykey = md5(print_r($class_info,true).'_'.page.'_'.($issearch?'1':'0'));
		if (isset($myurl[$mykey]))return $myurl[$mykey];
		
		if ( !is_array($class_info)){
			$class_info = Myqee::myclass($class_info);
		}
		static $mysiteurl;
		$mysiteurl or $mysiteurl = Myqee::config('core.mysite_url');
		
		if($class_info['isnothtml']!=0 
			|| (!$class_info['iscover']&&!$class_info['islist']) 
			|| (!$page && ( ($class_info['iscover']&&$class_info['cover_tohtml']) || (!$class_info['iscover']&&$class_info['list_tohtml']) ) ) 
			|| ($page && $class_info['islist'] && $class_info['list_tohtml'])
			|| $issearch
		){
			if (substr($mysiteurl,0,7)=='http://'){
				$host = $mysiteurl;
			}else{
				$host = 'http://'.Myqee::config('core.mysite_domain').$mysiteurl;
			}
			$myqeepage = Myqee::config('core.myqee_page');
			if ($myqeepage){
				$host .= $myqeepage.'/';
			}
			$theurl = $host.'myclass/'.($issearch?'search/':'').substr(Des::Encrypt('['.$class_info['classid'].']',Myqee::config('encryption.urlcode.key')),2).($page?'/'.$page:'').Myqee::config('core.url_suffix');
		}else{
			if ($class_info['hostname']){
				$theurl = 'http://'.$class_info['hostname'];
			}else{
				$theurl = $mysiteurl .$class_info['classpath'];
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
	
		$myurl[$mykey] = $theurl;
		return $theurl;
	}
	
	/**
	 * 返回文件网页路径
	 *
	 * @param int/string $classid 栏目ID/数据表名称
	 * @param int $theinfo 信息内容
	 * @param unknown_type $path 路径
	 * @return string $info_url
	 */
	public static function getinfourl($classid,$theinfo,$path=null){
		
		static $myurl;
		$mykey = md5(print_r($classid,true).'_'.print_r($theinfo,true).'_'.$path);
		if (isset($myurl[$mykey]))return $myurl[$mykey];
		
		
		if ( !is_array($class_info)){
			$class_info = Myqee::myclass($class_info);
		}
		
		$path or $path = self::getinfopath($classid,$theinfo,false);
		if ($classid>0){
			Myqee::$myclass[$classid] or Myqee::myclass($classid);
			$dbname = Myqee::$myclass[$classid]['dbname'];
		}else{
			$dbname = $classid;
		}
		if (strpos($dbname,'/')===false){
			$database = 'default';
			$tablename = $dbname;
			$dbname = $database.'/'.$dbname;
		}else{
			list($database,$tablename) = explode('/',$dbname,2);
		}
		
		//缺少数据表信息将无法获取信息内容，返回空链接
		if (!$tablename){
			$myurl[$mykey] = '#dberror';
			return '#dberror';
		}
		
		self::$dbconfig[$dbname] or self::$dbconfig[$dbname] = Myqee::config('db/'.$dbname);
		if (!self::$dbconfig[$dbname]){
			$myurl[$mykey] = '#dberror2';
			return '#dberror2';
		}
		
		if (self::$dbconfig[$dbname]['sys_field']['linkurl']){
			if ($mylinkUrl = $theinfo[self::$dbconfig[$dbname]['sys_field']['linkurl']])return $mylinkUrl;
		}

		if ( !($classid>0) ){
			$classid = $theinfo[self::$dbconfig[$dbname]['sys_field']['class_id']];
			if ( $classid>0 && !Myqee::$myclass[$classid] ){
				Myqee::myclass($classid);
			}
		}
		
		$info_url = Myqee::config('core.mysite_url');
		if ($path && file_exists(WWWROOT.$path)){
			if ($classid>0 && Myqee::$myclass[$classid]['hostname'] && substr($path,0,strlen(Myqee::$myclass[$classid]['classpath']))==Myqee::$myclass[$classid]['classpath']){
				$path = substr($path,strlen(Myqee::$myclass[$classid]['classpath']));
				$info_url = 'http://'.Myqee::$myclass[$classid]['hostname'].'/';
			}

			$info_url .= ltrim($path,'/');
		}else{
			
			//缺少唯一标示将无法定位具体信息，返回空链接
			if (!self::$dbconfig[$dbname]['sys_field']['id']){
				$myurl[$mykey] = '#iderror';
				return '#iderror';
			}
			
			$myqeepage = Myqee::config('core.myqee_page');
			if ($myqeepage){
				$info_url .= $myqeepage.'/';
			}
//			return $info_url .= 'myinfo/'.Des::Encrypt('[51,2382]',Myqee::config('encryption.urlcode.key'));
//			return $info_url .= 'myinfo/'.('['.($classid>0?$classid:$dbname).','.$theinfo[$this->dbconfig[$dbname]['sys_field']['id']].']').Myqee::config('encryption.urlcode.key').Myqee::config('core.url_suffix');
			$info_url .= 'myinfo/'.substr(Des::Encrypt(($classid>0?$classid:$dbname).','.$theinfo[self::$dbconfig[$dbname]['sys_field']['id']],Myqee::config('encryption.urlcode.key')),2).Myqee::config('core.url_suffix');
		}
		
		$myurl[$mykey] = $info_url;
		return $info_url;
	}

	/**
	 * 返回文件路径
	 *
	 * @param int/string $classid 栏目ID/数据表名称
	 * @param array $theinfo 信息内容
	 * @param boolean $retrun_array 是否返回数组
	 */
	public static function getinfopath($classid,$theinfo,$retrun_array = false){
		static $mypath;
		$mykey = md5(print_r($classid,true).'_'.print_r($theinfo,true).'_'.($retrun_array?'1':'0'));
		if (isset($mypath[$mykey]))return $mypath[$mykey];
		
		//栏目信息
		if ($classid>0){
			Myqee::$myclass[$classid] or Myqee::myclass($classid);
			$dbname = Myqee::$myclass[$classid]['dbname'];
		}else{
			$dbname = $classid;
			$classid = NULL;
		}
		if (strpos($dbname,'/')===false){
			$database = 'default';
			$tablename = $dbname;
			$dbname = $database.'/'.$dbname;
		}else{
			list($database,$tablename) = explode('/',$dbname,2);
		}
		if (!$tablename){
			$mypath[$mykey] = false;
			return false;
		}
		
		self::$dbconfig[$dbname] or self::$dbconfig[$dbname] = Myqee::config('db/'.$dbname);
		
		//根据内容信息的ID重新获取信息classid（如果存在）
		if ( !($classid>0) ){
			$classid = $theinfo[self::$dbconfig[$dbname]['sys_field']['class_id']];
			if ( $classid>0 && !Myqee::$myclass[$classid] ){
				Myqee::myclass($classid);
			}
		}

		if ( isset(self::$dbconfig[$dbname]['sys_field']['filepath']) && !empty($theinfo[self::$dbconfig[$dbname]['sys_field']['filepath']]) ){
			//信息字段中存在filepath字段且不为空
			if ($classid>0 && !Myqee::$myclass[$classid]['content_pathtype']){
				$thepath = rtrim(Myqee::$myclass[$classid]['classpath'],'/') .'/';
			}
			$thepath .= ltrim($theinfo[self::$dbconfig[$dbname]['sys_field']['filepath']],'/');
		}else{
			if ($classid>0){
				if (Myqee::$myclass[$classid]['content_pathtype']){
					$thepath = trim(Myqee::$myclass[$classid]['content_path'],'/');
				}else{
					$classpath = Myqee::$myclass[$classid]['classpath'];
				}
				$classpath = rtrim($classpath,'/');
				if (Myqee::$myclass[$classid]['content_selfpath'] && self::$dbconfig[$dbname]['sys_field']['createtime']){
					if ($theinfo[self::$dbconfig[$dbname]['sys_field']['createtime']]){
						$createtime = $theinfo[self::$dbconfig[$dbname]['sys_field']['createtime']];
					}else{
						$createtime = $_SERVER['REQUEST_TIME'];
						//更新记录
						$updateinfo = array( self::$dbconfig[$dbname]['sys_field']['createtime'] => $createtime );
					}
					$thepath = ($thepath?$thepath.'/':'').date(Myqee::$myclass[$classid]['content_selfpath'],$createtime).'/';
				}
				
				if (isset(self::$dbconfig[$dbname]['sys_field']['filepath'])){
					$updateinfo[self::$dbconfig[$dbname]['sys_field']['filepath']] = $thepath;
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
			$thename = Myqee::$myclass[$classid]['content_prefix'];
		}else{
			$thename = '';
		}

		//文件名
		if (self::$dbconfig[$dbname]['sys_field']['filename']){
			if ($theinfo[self::$dbconfig[$dbname]['sys_field']['filename']]){
				$thefullname = $theinfo[self::$dbconfig[$dbname]['sys_field']['filename']];
			}else{
				switch (Myqee::$myclass[$classid]['content_filenametype']){
					case 0:
						$thename .= $theinfo[self::$dbconfig[$dbname]['sys_field']['id']];
						break;
					case 1:
						$thename .= $theinfo[self::$dbconfig[$dbname]['sys_field']['createtime']];
						break;
					case 2:
						$thename .= md5(print_r($theinfo,true).'__'.$_SERVER['REQUEST_TIME']);
						break;
					case 3:
						$thename .= substr(md5(print_r($theinfo,true).'__'.$_SERVER['REQUEST_TIME']),8,16);
						break;
					default:
						$thename .= $theinfo[self::$dbconfig[$dbname]['sys_field']['id']];
						break;
				}
			}
		}else{
			$thename .= $theinfo[self::$dbconfig[$dbname]['sys_field']['id']];
		}
		
		if (!$thefullname){
			//后缀（扩展名）
			if (self::$dbconfig[$dbname]['sys_field']['content_suffix'] && $theinfo[self::$dbconfig[$dbname]['sys_field']['content_suffix']]){
				$thename .= $theinfo[self::$dbconfig[$dbname]['sys_field']['content_suffix']];
			}elseif($classid>0){
				$thename .= Myqee::$myclass[$classid]['content_suffix'];
			}else{
				$thename .= '.html';
			}
			$thefullname = $thename;
			if (self::$dbconfig[$dbname]['sys_field']['filename']){
				$updateinfo[self::$dbconfig[$dbname]['sys_field']['filename']] = $thefullname;
			}
		}

		if ($updateinfo && self::$dbconfig[$dbname]['sys_field']['id']){
			Myqee::db($database)->update(
				$tablename, 
				$updateinfo ,
				array( self::$dbconfig[$dbname]['sys_field']['id']=>$theinfo[self::$dbconfig[$dbname]['sys_field']['id']] )
			);
		}
		
		$mypath[$mykey] = $retrun_array?array('path'=>$thepath,'name'=>$thefullname):rtrim($thepath,'/').'/'.$thefullname;
		
		return $mypath[$mykey];
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
	public static function createhtml($template_id ,$tofilename=NULL, $data=NULL ,$viewtype=NULL ,$filepath=NULL){
		if (!$template_id)return false;
		
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
	
	
	/**
	 * 获取栏目主导航菜单
	 * @param int/string $tplid 模板文件或模板ID
	 * @param string $parentnav 导航服标签
	 * @param int/string $thisnav 当前栏目标示
	 * @param boolean $print 是否直接输出
	 */
	public static function nav($tplid,$parentnav=null,$thisnav=0,$print = true,$otherdata=null){
		$navdata = array(
			'data' => Myqee::config('navigation'.($parentnav?'.'.$parentnav.'.submenu':'')),
			'thisnav' => $thisnav,
		);
		if (is_array($otherdata))$navdata = array_merge($otherdata,$navdata);
		
		return self::createhtml($tplid,(boolean)$print,$navdata);
	}
	
	/**
	 * 别名，获取栏目主导航菜单
	 */
	public static function navigation($tplid,$parentnav=null,$thisnav=0,$print = true,$otherdata=null){
		return self::nav($tplid,$parentnav,$thisnav,$print,$otherdata);
	}
	
	
	public static function location($class_id,$isfull_loaction = true,$interval = ' -&gt; '){
		$location = self::get_location_array($class_id);
		if ($isfull_loaction){
			if ($isfull_loaction===true){
				echo '<a href="'.SITE_URL.'">首页</a>';
			}else{
				echo $isfull_loaction;
			}
		}
		if (is_array($location)){
			foreach ($location as $item){
				echo $interval . '<a href="',self::getclassurl($item),'">',$item['classname'],'</a>';
			}
		}
	}
	
	/**
	 * 获取当前位置数组结构
	 *
	 * @param int $classid 当前栏目ID
	 * @param unknown_type $myclass 树状栏目数组，可不传
	 * @return array
	 */
	public static function get_location_array($classid,$myclass=null){
		if (!is_array($myclass)){
			if (!Myqee::$myclass[$classid]){
				Myqee::myclass($classid);
			}
		}
		if (!is_array(Myqee::$myclass))return false;
		
		$myfeather = array();
		if (Myqee::$myclass['fatherclass']){
			$feather = explode('|',trim(Myqee::$myclass['fatherclass'],'|'));
			foreach ($feather as $cid){
				if(!Myqee::$myclass[$cid])Myqee::myclass($cid);
				$myfeather[] = Myqee::$myclass[$cid];
			}
		}
		
		$myfeather[] = Myqee::$myclass[$classid];
		return $myfeather;
	}
	
	/**
	 * 输出幻灯图片
	 * @param array $data 待传入的数据
	 * @param int $width 宽度
	 * @param int $height 高度
	 * @param boolean $showtext 是否显示标题文字
	 * @param int $pictime 幻灯播放的间隔时间
	 * @param string $swfurl 幻灯SWF路径
	 */
	public static function flashimage($data,$width=300,$height=200,$showtext = false,$pictime=4,$swfurl = 'images/focus.swf'){
		$pics = $link = $text = array();
		$width>0 or $width = 300;
		$height>0 or $height = 200;

		$count = count($data);
		for ($i=0;$i<$count;$i++){
			$pics[] = Tools::imageurl($data[$i]['imagepic'],$width,$height);
			$link[] = $data[$i]['URL'];
			$text[] = $data[$i]['title'];
		}
		echo '<script type="text/javascript">
(function(){
var pics = ', var_export(join('|',$pics)) ,';
var links = ', var_export(join('|',$link)) ,';
var texts = ', var_export(join('|',$text)) ,';
pics=pics.replace(/&/g,"%26");
links=links.replace(/&/g,"%26");
texts=texts.replace(/&/g,"%26");
if (pics.indexOf("|")==-1){
	pics +="|"+pics;
	links +="|"+links;
	texts +="|"+texts;
}

var interval_time=',$pictime,';
var focus_width=',$width,';
var focus_height=',$height,';
var text_height=',($showtext?20:0),';
var text_align="center";
var swf_height = focus_height+text_height;

var flashVars="pics="+pics+"&links="+links+"&texts="+texts+"&interval_time="+interval_time+"&borderwidth="+focus_width+"&borderheight="+focus_height+"&text_align="+text_align+"&textheight="+text_height;

showFlash(null,',var_export($swfurl),',focus_width,swf_height,false,true,flashVars);
})();
</script>
';
	}
	
	public static function getspacedata($classid,$wherestr=NULL,$limit=20,$offset=0,$type='blog',$orderby=NULL){
		$action = array('blog','album');
		if (!in_array($type,$action))$type='blog';
		$where = array('friend'=>0);
		if(is_array($wherestr)){
			$where = array_merge($where,$wherestr);
		}
		if (is_array($orderby)){
			$orderby = array_merge($orderby,array($type.'id'=>'DESC'));
		}else{
			$orderby = array($type.'id'=>'DESC');
		}
		
		$info = Myqee::db('uchome') -> from($type);
		if (count($where)>0)$info = $info -> where($where);
		foreach ($orderby as $key => $value){
			$info = $info -> orderby($key,$value);
		}
		if($limit){
			$info->limit($limit,$offset);
		}
//		$info = $info -> compile();
		$info = $info -> get() -> result_array ( FALSE );
//		echo $info ;return;
		
		$infocount = count($info);
		$spaceurl = rtrim(Myqee::config('core.home_url'),'/').'/';
		if ($infocount>0){
			for ($i=0;$i<$infocount;$i++){
				if (isset($info[$i]['URL']))$info[$i]['url']=$info[$i]['URL'];
				$info[$i]['URL'] = $spaceurl.'space.php?uid='.$info[$i]['uid'].'&do='.$type.'&id='.$info[$i][$type.'id'];
				$info[$i]['picurl'] = $info[$i]['pic']?$spaceurl.'attachment/'.$info[$i]['pic']:'images/block.gif';
				$info[$i]['spaceurl'] = $spaceurl.'space.php?uid='.$info[$i]['uid'];
			}
		}
		return $info;
	}
	
	public static function getspaceurl($uid){
		$spaceurl = rtrim(Myqee::config('core.home_url'),'/').'/';
		return $spaceurl.'space.php?uid='.$uid;
	}
	
	
	
	public static function getfaceurl($uid,$size='small'){
		return Myqee::config('ucconfig.api') .'/avatar.php?uid='.$uid.'&size='.$size.'&type=virtual';
	}
	
	
	public static function getdata_bysql($sqlstr,$dbconfig=null){
		$info = Myqee::db($dbconfig) -> query($sqlstr)->result_array ( FALSE );
		return $info;
	}
	/**
	 * 获取数据表数据
	 * @param int/string $classid 栏目ID/数据表名称
	 * @param int/array $where 操作类型/查询条件
	 */
	public static function getdata($classid,$wherestr=NULL,$limit=20,$offset=0,$orderby=NULL,$dbconfig=null){
		$where = array();
		if ($classid==='[selfinfo]'){
			global $nowclassid;
			$classid = $nowclassid;
		}
		if ($classid>0){
			//当前为栏目ID
			$class_array = Myqee::myclass($classid);
			$dbname = $class_array['dbname'];
		}else{
			$dbname = $classid;
		}
		if (strpos($dbname,'/')===false){
			$database = 'default';
			$tablename = $dbname;
			$dbname = $database.'/'.$dbname;
		}else{
			list($database,$tablename) = explode('/',$dbname,2);
		}
		if(!$tablename)return array();
		if (!Myqee::db($database)->table_exists($tablename))return array();
		
		self::$dbconfig = Myqee::config('db/'.$dbname);
		
		if (is_array($wherestr)){
			$where=$wherestr;
			if (isset($wherestr['[autoset]'])){
				$wherestr = (string)$wherestr['[autoset]'];
				unset($where['[autoset]']);
			}
			if (isset($wherestr['[in]'])){
				$inwhere = $where['[in]'];
				unset($where['[in]']);
			}
		}
		if (is_string($wherestr)){
			$tmpwhere = explode('|',$wherestr);	//支持多条件
			foreach ($tmpwhere as $item){
				$item_value = explode(',',$item);
				$item = $item_value[0];
				$item_value = $item_value[1];
				switch ($item){
					case 'new':
						//最新
						if (self::$dbconfig['sys_field']['posttime']){
							$orderby[self::$dbconfig['sys_field']['posttime']] = 'DESC';
						}elseif (self::$dbconfig['sys_field']['posttime2']){
							$orderby[self::$dbconfig['sys_field']['posttime2']] = 'DESC';
						}elseif (self::$dbconfig['sys_field']['id']){
							$orderby[self::$dbconfig['sys_field']['id']] = 'DESC';
						}
						break;
					case 'commend':
						//推荐
						if (self::$dbconfig['sys_field']['iscommend']){
							if ($item_value){
								$where[self::$dbconfig['sys_field']['iscommend']] = (int)$item_value;
							}else{
								$where[self::$dbconfig['sys_field']['iscommend'].'>'] = 0;
								$orderby[self::$dbconfig['sys_field']['iscommend']] = 'DESC';
							}
						}else{
							return array();
						}
						break;
					case 'hot':
						//热门
						if (self::$dbconfig['sys_field']['is_hot']){
							$where[self::$dbconfig['sys_field']['is_hot']] = 1;
						}else{
							return array();
						}
						break;
					case 'hits':
						//访问统计
						if (self::$dbconfig['sys_field']['hits']){
							$where[self::$dbconfig['sys_field']['hits'].'>'] = 0;
							$orderby[self::$dbconfig['sys_field']['hits']] = 'DESC';
						}else{
							return array();
						}
						break;
					case 'hits_week':
						//本周访问统计
						if (self::$dbconfig['sys_field']['hits_week']){
							$where[self::$dbconfig['sys_field']['hits_week'].'>'] = 0;
							$orderby[self::$dbconfig['sys_field']['hits_week']] = 'DESC';
						}else{
							return array();
						}
						break;
					case 'indexshow':
						//首页显示
						if (self::$dbconfig['sys_field']['is_indexshow']){
							$where[self::$dbconfig['sys_field']['is_indexshow']] = 1;
						}else{
							return array();
						}
						break;
					case 'headlines':
						//头条
						if (self::$dbconfig['sys_field']['isheadlines']){
							if ($item_value){
								$where[self::$dbconfig['sys_field']['isheadlines']] = (int)$item_value;
							}else{
								$where[self::$dbconfig['sys_field']['isheadlines'].'>'] = 0;
								$orderby[self::$dbconfig['sys_field']['isheadlines']] = 'DESC';
							}
						}else{
							return array();
						}
						break;
					case 'imagenews':
						//标题图片
						if (self::$dbconfig['sys_field']['imagenews']){
							$where[self::$dbconfig['sys_field']['imagenews'].'!='] = '';
						}else{
							return array();
						}
						break;
					default:
						//默认
						if ( self::$dbconfig['sys_field']['id'] && !$orderby[self::$dbconfig['sys_field']['id']] ){
							$orderby[self::$dbconfig['sys_field']['id']] = 'DESC';
						}
						break;
				}
			}
		}
		if ($classid>0 && self::$dbconfig['sys_field']['class_id']){
			$where[self::$dbconfig['sys_field']['class_id']]=$classid;
		}
		if (self::$dbconfig['sys_field']['id'] && !$orderby[self::$dbconfig['sys_field']['id']]){
			$orderby[self::$dbconfig['sys_field']['id']]='DESC';
		}
		
		if (self::$dbconfig['sys_field']['isshow']){
			$where[self::$dbconfig['sys_field']['isshow']] = 1;
		}
		
		$info = Myqee::db($database) -> from($tablename);
		if (is_array($where) and count($where)>0){
			$info = $info -> where ($where);
		}
		if (is_array($inwhere)){
			foreach ($inwhere as $in => $va){
				if ($in && is_array($va)){
					$info = $info -> in ($in,$va);
				}
			}
		}

		if (is_array($orderby) and count($orderby)>0){
			foreach ($orderby as $key => $value){
				$info = $info -> orderby($key,$value);
			}
		}
		//echo $info->limit($limit,$offset)->compile();return;
		$info = $info -> limit($limit,$offset) -> get() -> result_array ( FALSE );

		$infocount = count($info);
		if ($infocount>0){
			for ($i=0;$i<$infocount;$i++){
				if (isset($info[$i]['URL']))$info[$i]['url']=$info[$i]['URL'];
				$info[$i]['URL'] = self::getinfourl($classid,$info[$i]);
			}
		}
		return $info;
	}
	

	public static function mydata($id,$isecho = false){
		if ( !$id )return FALSE;
		$mydata_config = Myqee::config('mydata/mydata_'.$id);
		if (!$mydata_config)return FALSE;
		if (!$mydata_config['is_use'])return FALSE;

		if ($mydata_config['cache_time']>0){
			$cachename = 'mydata_id_'.$id;
			if ($html = Cache::get($cachename,$mydata_config['cache_time'])){
				if ($mydata_config['template_id']){
					$html = unserialize($html);
				}
				if ($isecho){
					echo $html;
					return TRUE;
				}else{
					return $html;
				}
			}
		}
		
		if ($mydata_config['type']==0){
			if (!$mydata_config['dbname'])return FALSE;
			$data = self::getdata(
				$mydata_config['classid']>0?$mydata_config['classid']:$mydata_config['dbname'],
				$mydata_config['data_where'],
				$mydata_config['limit']>0?$mydata_config['limit']:20,
				(int)$mydata_config['start_number'],
				array($mydata_config['list_byfield'] => $mydata_config['list_orderby'])
			);
		}else{
			if (!$mydata_config['sql'])return FALSE;
			$db = Myqee::db($mydata_config['table_config']);
			
			//替换字符串
			$mydata_config['sql'] = str_replace(
				array(
					'{{time}}',
					'{{tablepre}}'
				),
				array(
					$_SERVER['REQUEST_TIME'],
					$db -> table_prefix(),
				),
				$mydata_config['sql']
			);
			$data = $db -> query($mydata_config['sql']) -> result_array(FALSE);
			$infocount = count($data);
			if ($infocount>0){
				for ($i=0;$i<$infocount;$i++){
					if (isset($data[$i]['URL']))$data[$i]['url']=$data[$i]['URL'];
					$data[$i]['URL'] = self::getinfourl($id,$data[$i]);
				}
			}
		}
		
		if ($mydata_config['template_id']){
			$mydata_config['var_name'] or $mydata_config['var_name'] = 'data';
			$data = self::createhtml($mydata_config['template_id'],NULL,array($mydata_config['var_name'] => $data));
		}else {
		}
		if ($mydata_config['cache_time']>0){
			$data_cache = serialize($data);
			Cache::set($cachename,$data_cache);
		}
		
		if ($isecho){
			echo $data;
			return TRUE;
		}else{
			return $data;
		}
	}


	public static function gethtml($tpl_id,$classid,$where=NULL,$limit=20,$offset=0,$orderby=NULL){
		$data['data'] = self::getdata($classid,$where,$limit,$offset,$orderby);
		return self::createhtml($tpl_id,true,$data);
	}
	
	
	/*返回页码*/
	//$pageArray：是一个一维数组，将根据它的key用value替换掉网页地址里 [key] 的内容（左右分别有“[”和“]”）
	public static function page($page,$allpage,$weburl,$pageArray=null,$setfirstnullpage=false){
		/*$tmphtml='<div class="pageDiv"><table border="0" align="center" style="white-space:nowrap;"><tr>';
		if ($page>1){
			$firstpage = $setfirstnullpage?str_replace($setfirstnullpage,'',$weburl):str_replace('{{page}}',1,$weburl);
			$tmphtml.='<td><a href="'.$firstpage.'">首页</a></td><td><a href="'.($page==2?$firstpage:str_replace('{{page}}',$page-1,$weburl)).'">&lt;&lt;上一页</a></td>';
		}else{
			$tmphtml.='<td><a class="nolink">首页</a></td><td><a class="nolink">&lt;&lt;上一页</a></td>';
		}
		if ($page>6 && $allpage-$page>5){
			$forstart=$page-5;
		}else if($allpage-$page<10 && $allpage>10){
			$forstart=$allpage-10;
		}else{
			$forstart=1;
		}
		$minnum=min($forstart+11,$allpage+$forstart);
		//$minnum = $forstart+11;
		for ($i=$forstart;$i<$minnum;$i++){
			if ($i<=$allpage){
				if ($page==$i){
					$tmphtml.='<td><a class="linknow">'.$i.'</a></td>';
				}elseif($i==1){
					$tmphtml.='<td><a href="'.$firstpage.'">1</a></td>';
				}else{
					$tmphtml.='<td><a href="'.str_replace('{{page}}',$i,$weburl).'">'.$i.'</a></td>';
				}
			}else{
				$tmphtml.='<td><a class="nolink">'.$i.'</a></td>';
			}
		}
		if ($page==$allpage || $allpage==0){
			$tmphtml.='<td><a class="nolink">下一页&gt;&gt;</a></td><td><a class="nolink">尾页</a></td>';
		}else{
			$tmphtml.='<td><a href="'.str_replace('{{page}}',$page+1,$weburl).'">下一页&gt;&gt;</a></td><td><a href="'.str_replace('{{page}}',$allpage,$weburl).'">尾页</a></td>';
		}
		$tmphtml.='</tr></table></div>';
	
		if (is_array($pageArray)){
			foreach ($pageArray as $key=>$value){
				$tmphtml=str_replace('{{'.$key.'}}',$value,$tmphtml);
			}
		}
		return $tmphtml;*/
		$tmphtml='<dl>';
		if ($page>1){
			$firstpage = $setfirstnullpage?str_replace($setfirstnullpage,'',$weburl):str_replace('{{page}}',1,$weburl);
			$tmphtml.='<span><a href="'.$firstpage.'">首页</a></span><span><a href="'.($page==2?$firstpage:str_replace('{{page}}',$page-1,$weburl)).'">&lt;&lt;上一页</a></span>';
		}else{
			$tmphtml.='<span><a>首页</a></span><span><a>&lt;&lt;上一页</a></span>';
		}
		if ($page>4 && $allpage-$page>3){
			$forstart=$page-3;
		}else if($allpage-$page<6 && $allpage>6){
			$forstart=$allpage-6;
		}else{
			$forstart=1;
		}
		$minnum=min($forstart+7,$allpage+$forstart);
		//$minnum = $forstart+11;
		for ($i=$forstart;$i<$minnum;$i++){
			if ($i<=$allpage){
				if ($page==$i){
					$tmphtml.=' <b id="l_cno"><a>'.$i.'</a></b>';
				}elseif($i==1){
					$tmphtml.=' <b><a href="'.$firstpage.'">1</a></b>';
				}else{
					$tmphtml.=' <b><a href="'.str_replace('{{page}}',$i,$weburl).'">'.$i.'</a></b>';
				}
			}else{
				$tmphtml.='<a>'.$i.'</a>';
			}
		}
		if ($page==$allpage || $allpage==0){
			$tmphtml.='<span><a>下一页&gt;&gt;</a></span><span><a>尾页</a></span>';
		}else{
			$tmphtml.='<span><a href="'.str_replace('{{page}}',$page+1,$weburl).'">下一页&gt;&gt;</a></span><span><a href="'.str_replace('{{page}}',$allpage,$weburl).'">尾页</a></span>';
		}
		$tmphtml.='</span></dl>';
	
		if (is_array($pageArray)){
			foreach ($pageArray as $key=>$value){
				$tmphtml=str_replace('{{'.$key.'}}',$value,$tmphtml);
			}
		}
		return $tmphtml;
	}
	
	
/*返回页码*/
	//$pageArray：是一个一维数组，将根据它的key用value替换掉网页地址里 [key] 的内容（左右分别有“[”和“]”）
	public static function page_v2($page,$allpage,$weburl,$pageArray=null,$setfirstnullpage=false){
		
	}

	
	
	/**
	 * @name 将文章正文分离出标题和分页
	 * @param $value string 文章内容
	 * @return $out array 一个以标题，内容为分开的二维数组
	 */
	public static function get_title_info_array($value){
		$pagepreg = "<div style=\"page\-break\-after\: always\"><span style\=\"display\: none\">([^<]*)<\/span><\/div>";
		
		//处理第一页的分页符
		if (preg_match("/^{$pagepreg}/Usi",$value,$fristtitle)){
			$value = substr($value,strlen($fristtitle[0]));		//将第一页标题去掉
			$fristtitle = $fristtitle[1];
		}else{
			$fristtitle = '';
		}
		preg_match_all("/{$pagepreg}/Usi",$value,$alltitle);
		$alltitle = $alltitle[1];
		array_unshift($alltitle,$fristtitle);	//将第一页标题插入所有标题
		
		//拆分内页
		$allinfo = preg_split("/{$pagepreg}/Usi",$value);
		
		$out['title'] = $alltitle;
		$out['info'] = $allinfo;
		
		//print_r($out);
		//unset($alltitle,$value,$pagepreg,$fristtitle,$allinfo);
		return $out;
	}
	
	/**
	 * 与上面方法相反
	 */
	public static function get_title_info_string($title,$content){
		$allcount = max(count($content),count($title));
		$tmpcontent='';
	
		for ($i=0;$i<$allcount;$i++){
			if ($title[$i]=='-'.($i+1).'-' || $title[$i]==' ')$title[$i] = '';
			$tmpcontent .= self::_get_title_htmlcode($title[$i]) . $content[$i];
		}
		return $tmpcontent;
	}
	
	public static function _get_title_htmlcode($title){
		return '<div style="page-break-after: always"><span style="display: none">'.$title.'</span></div>';
	}
	
	
	
	protected static $class_all_list;

	public static function get_allclass_count(){
		return Myqee::db() -> count_records('[class]');
	}

	protected static $classArray;
	protected static $classArray_bak;
	protected static $class_tree;
	/**
	 * 获取所有栏目的树状数组
	 *
	 * @param number $bclassid
	 * @param string $select
	 * @param number $treedepth 返回的深度
	 * @param boolean $isincludeself 是否包括本身
	 * @return array 栏目树状结构数组
	 */
	public static function get_allclass_array($bclassid = 0 ,$treedepth = 0 , $isincludeself = false,$where = null) {
		if (is_array($where) || !self::$classArray){
			$tmparray = Myqee::db() -> from ( '[class]' ) -> orderby ( 'myorder', 'asc' ) -> orderby ( 'classid', 'asc' );
			if (is_array($where)){
				$tmparray = $tmparray -> where($where);
			}
			$tmparray = $tmparray->get ()->result_array ( FALSE );
			if (self::$classArray)self::$classArray_bak = self::$classArray;
			self::$classArray = $tmparray;
			unset($tmparray);
		}

		if (count ( self::$classArray ) > 0) {
			foreach ( self::$classArray as $tmpclass ) {
				$tmpTree [$tmpclass ['bclassid']] [] = $tmpclass;
				$tmpList [$tmpclass['classid']] = $tmpclass;
			}
			self::$class_tree = $tmpTree;
			self::$class_all_list = $tmpList;
			$myclass =  self::_listclass ( $bclassid ,$treedepth , 0 );
			if ($isincludeself && $bclassid>0){
				if ($bclass = self::$class_all_list[$bclassid]){
					$bclass['sonclassarray'] = $myclass;
					$tmpmyclass = array($bclassid => $bclass);
					$myclass = $tmpmyclass;
				}
			}
		}else{
			$myclass = array();
		}
		
		if (is_array($where)){
			self::$classArray = self::$classArray_bak;
			self::$classArray_bak = NULL;
		}
		empty($myclass) && $myclass = array();
		return $myclass;
	}

	/**
	 * 列出栏目数装结构
	 *
	 * @param number $bclassid
	 * @param number $treedepth 返回的深度
	 * @param number $now_treedepth 当前的深度
	 * @return array
	 */
	protected static function _listclass($bclassid = 0 , $treedepth =0 , $now_treedepth = 0) {
		$bclassid = ( int ) $bclassid;
		if (!($bclassid > 0)) {
			$bclassid = 0;
		}
		$tempclass = self::$class_tree [$bclassid];
		if (count ( $tempclass ) > 0) {
			if ($treedepth>0)$now_treedepth++;
			foreach ( $tempclass as $r ) {
				$classarray [$r ['classid']] = $r;
				if ($now_treedepth==0 || $now_treedepth < $treedepth){
					$classarray [$r ['classid']] ['sonclassarray'] = self::_listclass ( $r ['classid'] ,$treedepth , $now_treedepth );
				}
			}
			return $classarray;
		}
	}


	/**
	 * 获取指定栏目所有子栏目ID，
	 *
	 * @param int $bclassid
	 * @param int $treedepth
	 * @param boolean $isincludeself 是否包含自己，默认否
	 * @param boolean $returnstring 是否返回字符串，若返回字符串，则所有栏目ID之间用,隔开，默认否
	 * @return array/string
	 */
	public static function get_sonclass_id($bclassid, $treedepth =0 ,$isincludeself = false , $returnstring = false){
		$classtree = self::get_allclass_array($bclassid,$treedepth,$isincludeself,'classid,classname,sonclass');
		return self::_listsonclass($classtree,$returnstring);
	}

	protected static function _listsonclass($classtree,$returnstring = true){
		$classtree = (array)$classtree;
		$classid = '';
		foreach ($classtree as $item){
			$classid .= ','.$item['classid'];
			if ($item['sonclassarray'])$classid .= ','.self::_listsonclass($item['sonclassarray'],true);
		}

		if ($returnstring){
			return trim($classid,',');
		}else{
			return explode(',',trim($classid,','));
		}
	}
}
