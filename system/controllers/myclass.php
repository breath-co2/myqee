<?php
class Myclass_Controller_Core extends Controller {
	protected $issearch = false;
	public function _default($page=1){
		$this -> _myclass($page);
	}
	
	/**
	 * @name 栏目搜索
	**/
	public function search($nowclassid){
		$this -> issearch = true;
		$this -> nowclassid = $nowclassid;
		
		$this -> _myclass(1);
	}
	
	protected function _myclass($thepage=0){
		if ($this -> nowclassid){
			$nowclassid = $this -> nowclassid;
		}else{
			$urlcode = __ERROR_FUNCTION__;
			$urlarr = Des::Decrypt($urlcode,Myqee::config('encryption.urlcode.key'));
			if (preg_match("/^\[([a-z0-9_]+)\]$/",$urlarr,$match)){
				$nowclassid = $match[1];
				if (is_numeric($nowclassid)){
					$nowclassid = (int)$nowclassid;
				}
			}else{
				Myqee::show_error('指定的栏目不存在！',SITE_URL);
			}
		}
		if (empty($nowclassid) || !$nowclassid || !preg_match("/[a-z0-9_]/",$nowclassid)){
			myqee::show_error('缺少参数，请联系管理员！');
		}
		if ( !($thepage>0) ){
			$page = $_GET['page'];
			$page > 0 or $page = 1;
			unset($_GET['page']);
		}else{
			$page = $thepage;
		}
		$myclass = Myqee::myclass($nowclassid);
		if (!$myclass){
			//栏目不存在，请联系系统管理员！
			myqee::show_error('指定的栏目不存在！');
		}
		
		$cachetime = 0;
		if ($thepage==0 && $myclass['iscover']){
			//封面
			if ($myclass['cover_tohtml']==1){
				if ($myclass['cover_cachetime']>0){
					//封面缓存时间
					$cachetime = $myclass['cover_cachetime'];
				}
			}else{
				$url = Createhtml::instance() -> getclassurl($myclass);
				header('location:'.$url);
				exit;
			}
		}elseif ($thepage > 0){
			if ($this -> issearch){
				if ($myclass['search_cachetime']>0){
					$cachetime = $myclass['search_cachetime'];
				}
			}else{
				if ($myclass['list_tohtml']==1){
					if ($myclass['list_cachetime']>0){
						$cachetime = $myclass['list_cachetime'];
					}
				}else{
					$url = Createhtml::instance() -> getclassurl($myclass,$thepage);
					//header('location:'.$url);
					//exit;
				}
			}
		}
		if ($cachetime>0){
			//读取缓存
			$cachename = 'class_'.$nowclassid.'_id_'.md5(print_r($_GET,TRUE)).'_'.$thepage;
			if (Cache::get($cachename,$cachetime,true)){
				exit;
			}
		}
		
		if ($this -> issearch == true && !$myclass['issearch']){
			//栏目为动态栏目封面
			myqee::show_error('当前栏目没有开设搜索功能！');
		}
		
		if (!$myclass['dbname']){
			//栏目数据表信息缺失，请联系系统管理员！
			myqee::show_error('栏目数据表信息缺失，请联系系统管理员！');
		}
		//读取数据表配置
		$dbname = $myclass['dbname'];
		list($database,$tablename) = explode('/',$dbname);
		$dbconfig = Myqee::config('db/'.$dbname);

		if ( !$dbconfig['sys_field']['class_id'] ){
			//数据表不存在栏目ID字段，请联系系统管理员！
			myqee::show_error('数据表不存在栏目ID字段，请联系系统管理员！');
		}
		if ( !Createhtml::instance()->table_exists($myclass['dbname']) ){
			//数据表不存在，请联系系统管理员！
			myqee::show_error('数据表不存在，请联系系统管理员！');
		}
		$modelconfig = Myqee::config('model/model_'.$myclass['modelid']);

		//where
		//list_nosonclass==0本栏目及子栏目
		//list_nosonclass==1本栏目
		//list_nosonclass==2仅子栏目
		if ($myclass['list_nosonclass'] == 1){
			$sqlwhere = array(
				'type' => 'where',
				'value' => $nowclassid,
			);
		}else{
			$sonclass = Myhtml::get_sonclass_id($myclass['classid'],0,$myclass['list_nosonclass'] == 2?false:true,false);
			if (count($sonclass)>1){
				$sqlwhere = array(
					'type' => 'in',
					'value' => $sonclass,
				);
			}else{
				$sqlwhere = array(
					'type' => 'where',
					'value' => $sonclass[0],
				);
			}
		}

		if ($dbconfig['sys_field']['isshow']){
			$where=array($dbconfig['sys_field']['isshow']=>1);
		}
	
		//搜索模式
		if ($this -> issearch == true){
			
			$searchfield = array();
			$jiehefield = array();
			foreach ($_GET as $thekey=>$value){
				//支持多字段搜索，用|分开，例如：title|content=abc123，这样可以同时搜索title和content两个字段
				$thekey_arr = explode('|',$thekey);
				foreach ($thekey_arr as $key){
					//匹配搜索模式
					preg_match("/(\%)?([0-9a-z_]+)(\%)?/i",$key,$m_like);
					$key = $m_like[2];
					if($modelconfig['field'][$key]){
						if ($modelconfig['field'][$key]['search']==1){
							$searchfield[$m_like[1].$key.$m_like[3]] = $value;
						}elseif ($modelconfig['field'][$key]['jiehe']==1 && !empty($_GET[$key])){
							if (preg_match("/([0-9]+),([0-9]+)/",$value,$matches)){
								$jiehefield[$key.'>='] = $matches[1];
								$jiehefield[$key.'<='] = $matches[2];
							}elseif(preg_match("/\!=([0-9a-z]+)/i",$value,$matches)){
								$jiehefield[$key.'!='] = $matches[1];
							}elseif(preg_match("/([>|<])(=)?([0-9]+)/",$value,$matches)){
								$jiehefield[$key.$matches[1].$matches[2]] = $matches[3];
							}else{
								$jiehefield[$key] = $value;
							}
						}
					}
				}
			}
			
			if (count($jiehefield)){
				$where += $jiehefield;
//				foreach ($jiehefield as $key => $value){
//					$where[$key] = $value;
//				}
			}
		}
		if (count($where)==0)$where=array(0);
		//读取信息数量
		$info_count = $myclass['info_count'] = Myqee::db($database)->where($where)->$sqlwhere['type']( $dbconfig['sys_field']['class_id'],$sqlwhere['value'] );
		if (is_array($searchfield) &&  count($searchfield)){
			$i=0;
			foreach ($searchfield as $key => $value){
				if ($i==0){
					$info_count = $info_count -> like($key,$value);
				}else{
					$info_count = $info_count -> orlike($key,$value);
				}
				$i++;
			}
		}
		$info_count = $info_count ->count_records($tablename);
		$tohtml_ok = 0;
		$tohtml_error = 0;
//		echo Myqee::db()->last_query();
	
		//有列表页栏目
		$limit = $myclass['list_pernum'];
		$limit > 0 or $limit = 20;			//栏目每页显示数
		$offset = ($page - 1)*$limit;		//计算offset
		
		//只有在生成第一页的时候才执行
		if ($this -> issearch == false && $offset == 0){
			//生成栏目页
			if ($myclass['iscover'] && $myclass['cover_tohtml']==1){
				//待传入模板的数据
				$thedata = array(
					'class_id'=>$myclass['classid'],
					'class_name'=>$myclass['classname'],
					'myclass'=>$myclass,
				);
				if ( $html =Createhtml::instance()->createhtml($myclass['cover_tplid'] ,NULL ,$thedata ,'class') ){
					echo $html;
				}else{
					myqee::show_error('页面执行失败，请稍候再试！');
				}
			}
		}
		
		
		if ($myclass['islist'] || $this -> issearch == true){
			//列表模式
			if ($offset <= $info_count){
				
				if ($this -> issearch == true){
					if (!($template_id = $myclass['search_tplid'])){
						//数据表不存在，请联系系统管理员！
						myqee::show_error('搜索模板不存在，请联系系统管理员！');
					}
				}else{
					if (!($template_id = $myclass['list_tplid'])){
						//数据表不存在，请联系系统管理员！
						myqee::show_error('列表模板不存在，请联系系统管理员！');
					}
				}
				//orderby
				$allinfo = Myqee::db($database) -> where($where)->$sqlwhere['type']( $dbconfig['sys_field']['class_id'],$sqlwhere['value'] );
				if ($dbconfig['sys_field']['ontop']){
					$allinfo = $allinfo->orderby($dbconfig['sys_field']['ontop'],'DESC');
				}
				if ( $this -> issearch ){
					if($myclass['search_byfield']){
						$allinfo = $allinfo->orderby( $myclass['search_byfield'] , $myclass['search_orderby']=='ASC'?'ASC':'DESC' );
					}
					//搜索
					if (is_array($searchfield) &&  count($searchfield)){
						$i=0;
						foreach ($searchfield as $key => $value){
							if ($i==0){
								$allinfo = $allinfo -> like($key,$value);
							}else{
								$allinfo = $allinfo -> orlike($key,$value);
							}
							$i++;
						}
					}
				}elseif($myclass['list_byfield']){
					$allinfo = $allinfo->orderby( $myclass['list_byfield'] , $myclass['list_orderby']=='ASC'?'ASC':'DESC' );	
				}
				$allinfo = $allinfo->limit($limit,$offset) -> get($tablename) -> result_array ( FALSE );
				
				$myclass['url'] = Createhtml::instance() -> getclassurl($myclass);
				
				//附加URL地址
//				echo Myqee::db()->last_query();
				$infocount = count($allinfo);
				if ($infocount>0){
					for ($i=0;$i<$infocount;$i++){
						$allinfo[$i]['URL'] = Createhtml::instance()->getinfourl($allinfo[$i][$dbconfig['sys_field']['class_id']],$allinfo[$i]);
						$allinfo[$i]['CLASS_URL'] = $myclass['url'];
					}
				}
				//待传入模板的数据
				$thedata = array(
					'db_name'		=> $myclass['dbname'],
					'db_config'		=> $dbconfig,
					'model_config'	=> $modelconfig,
					'class_id'		=> $nowclassid,
					'class_name'	=> $myclass['classname'],
					'myclass'		=> $myclass,
					'list'			=> $allinfo,
					'count'			=> $info_count,
					'limit'			=> $limit,
					'page'			=> $page,
					'allpage'		=> ceil($info_count/$limit),
					'listpage'		=> Createhtml::instance()->getclassurl($myclass,'{{page}}',$this -> issearch?true:false),//$this -> issearch?'myclass/search/'.$nowclassid .'/?page={{page}}':
									   //($this->myclass[$nowclassid]['isnothtml'] || $this->myclass[$nowclassid]['list_tohtml']?Myqee::url('myclass/'.$nowclassid.'/{{page}}'):$classurl[$nowclassid].$this->myclass[$nowclassid]['list_filename']),
					'class_url'		=> $myclass['url'],
				);
					
//				$thedata = array(
//					'class_id'=>$nowclassid,
//					'class_name'=>$myclass['classname'],
//					'myclass'=>$myclass,
//					'list'=>$allinfo,
//					'count'=>$info_count,
//					'limit'=>$limit_class,
//					'page'=>$page,
//					'allpage'=>ceil($info_count/$limit),
//					'listpage'=>'myclass/search/'.$nowclassid .'/?page={{page}}',
//				);
				//$tmp_classinfo[$i];
				if ( $html = Createhtml::instance()->createhtml($template_id , NULL , $thedata ,'class') ){
					echo $html;
				}else{
					echo 'page render error';
				}
			}
		}
		
		
		if ($cachetime>0)cache::set($cachename,$html);
	}
}