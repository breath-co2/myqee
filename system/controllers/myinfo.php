<?php
class Myinfo_Controller_Core extends Controller {
	public function _default(){
		$urlcode = __ERROR_FUNCTION__;
		$infoarr = Des::Decrypt($urlcode,Myqee::config('encryption.urlcode.key'));
		if (preg_match("/^([_a-z0-9\/]+),([0-9]+)$/",$infoarr,$match)){
			$dbname = $match[1];
			$infoid = (int)$match[2];
		}else{
			Myqee::show_error('您指定的页面不存在！',SITE_URL);
		}
		if ($dbname && is_numeric($dbname)>0){
			$this -> _myinfo($infoid,(int)$dbname);
		}elseif (is_string($dbname) && preg_match("/^[a-z0-9_]+$/i",$dbname)){
			if (strpos($dbname,'/')===false){
				$database = 'default';
				$tablename = $dbname;
				$dbname = $database.'/'.$dbname;
			}else{
				list($database,$tablename) = explode('/',$dbname);
			}
			$dbconfig = Myqee::config('db/'.$dbname);
			if ($dbconfig && is_array($dbconfig)){ 
				if (!$dbconfig['readbydbname'] || !$dbconfig['sys_field']['id'] || !$dbconfig['sys_field']['class_id']){
					Myqee::show_error('网页不存在！',SITE_URL);
				}
				$this -> dbconfig = $dbconfig;
				if ($infoid>0){
					$theinfo = Myqee::db($database) -> getwhere($tablename,array($dbconfig['sys_field']['id'] => $infoid)) -> result_array ( FALSE );
					$theinfo = $theinfo[0];				
					if ($theinfo){
						$this -> info = $theinfo;
						$this -> _myinfo($infoid,$theinfo[$dbconfig['sys_field']['class_id']]);
						return;
					}else{
						Myqee::show_error('指定的信息不存在！',SITE_URL);
					}
				}else{
					Myqee::show_404();
				}
			}else{
				Myqee::show_404();
			}
		}
	}
	protected function _myinfo($infoid=0,$nowclassid=null){
		$infoid = (int)$infoid;
		if (!($nowclassid>0 && $infoid>0)){
			myqee::show_404();
		}
		$myclass = Myqee::myclass($nowclassid);
		if (!$myclass){
			//栏目不存在
			myqee::show_error('指定的栏目不存在！',SITE_URL);
		}
		if (!$myclass['iscontent']){
			//栏目未提供信息录入功能
			myqee::show_info('栏目未提供信息录入功能！',SITE_URL);
		}
		if (!$myclass['dbname']){
			//栏目数据表信息缺失
			myqee::show_error('栏目数据表信息缺失，请联系管理员！',SITE_URL);
		}
		
		$cachetime = 0;
		if ($myclass['content_tohtml']==1 && $myclass['content_cachetime']>0){
			//缓存时间
			$cachetime = $myclass['content_cachetime'];
		}
		
		if ($cachetime>0){
			$cachename = 'info_class_'.$nowclassid.'_id_'.$infoid;
			if ($html = Cache::get($cachename)){
				echo $html;
				exit;
			}
		}
		
		//读取数据表配置
		$this -> dbconfig or $this -> dbconfig = Myqee::config('db/'.$myclass['dbname']);
		$dbconfig = $this -> dbconfig;

		if ( !$dbconfig['sys_field']['class_id'] ){
			//数据表不存在栏目ID字段，取消执行
			myqee::show_error('数据表不存在栏目ID字段，请联系管理员！',SITE_URL);
		}
		if ( !$dbconfig['sys_field']['id'] ){
			//数据表不存在栏目ID字段，取消执行
			myqee::show_error('数据表不存在ID字段，请联系管理员！',SITE_URL);
		}
		if ( !Createhtml::instance()->table_exists($myclass['dbname']) ){
			//数据表不存在，取消执行
			myqee::show_error('数据表不存在，请联系管理员！',SITE_URL);
		}
		$modelconfig = Myqee::config('model/model_'.$myclass['modelid']);

		if ($this -> info){
			$theinfo = $this -> info;
		}else{
			$theinfo = Myqee::db($this->dbconfig['database'])->getwhere($this->dbconfig['tablename'],array($dbconfig['sys_field']['class_id'] => $nowclassid,$dbconfig['sys_field']['id'] => $infoid)) -> result_array ( FALSE );
			$theinfo = $theinfo[0];
		}
		
		if (!$theinfo || !is_array($theinfo)){
			myqee::show_error('指定的信息不存在！',SITE_URL);
		}
		if ( $dbconfig['sys_field']['is_show'] &&  $theinfo[$dbconfig['sys_field']['is_show']]!=1){
			myqee::show_info('本信息为发布或已经撤销！',SITE_URL);
		}
		
		//获取信息存放路径
		$thefile = Createhtml::instance()->getinfopath($nowclassid,$theinfo);

		if ($dbconfig['sys_field']['template_id'] && $theinfo[$dbconfig['sys_field']['template_id']]){
			$template_id = $theinfo[$dbconfig['sys_field']['template_id']];
		}else{
			$template_id = $myclass['content_tplid'];
		}
		$thedata = array(
			'id'=>$theinfo[$dbconfig['sys_field']['id']],
			'title'=>$theinfo[$dbconfig['sys_field']['title']],
			'class_id'=>$nowclassid,
			'class_name'=>$myclass['classname'],
			'db_name' => $myclass['dbname'],
			'db_config' => $dbconfig,
			'model_id' => $myclass['modelid'],
			'model_config' => $modelconfig,
			'info'=>$theinfo,
			'myclass'=>$nowclassid,
			'class_url'=>Createhtml::instance()->getclassurl($myclass),
		);
		
		if ($html = Createhtml::instance()->createhtml($template_id ,NULL,$thedata,'info') ){
			echo $html;
		}else{
			myqee::show_error('页面输出失败，请联系管理员！');
		}
		
		if ($cachetime>0)Cache::set($cachename,$html);
		
		if (!$myclass['isnothtml'] || !$myclass['content_tohtml']){
			//栏目为静态栏目，处理URL
			
		}
		
	}
	
	public function total($code=''){
		//if (!$code || !$_SERVER["HTTP_REFERER"])exit('');
		$code = explode('.',$code);
		$code = $code[0];
		$code = Des::Decrypt($code,null,0,0,null,"/(\\0+)$/Uis");
		if ( !preg_match("/^[a-z0-9_=\&]+$/i",$code) )exit('/*0*/');
		parse_str($code,$mycode);
		
		if ( !$mycode['d'] || !$mycode['i'] || $mycode['o']!=1 ){
			exit('/*1*/');
		}
		if ( !preg_match("/^[a-z0-9_\/]+$/i",$mycode['d']) )exit('/*2*/');
		$dbname = $mycode['d'];
		if (strpos($dbname,'/')===false){
			$database = 'default';
			$tablename = $dbname;
			$dbname = $database.'/'.$dbname;
		}else{
			list($database,$tablename) = explode('/',$dbname);
		}
		$db_config = Myqee::config('db/'.$dbname);
		if (!$db_config)exit('/*3*/');
		
		//print_r($db_config);
		$count_filed = array();
		$db_config['sys_field']['hits'] and $count_filed[] = $total = $db_config['sys_field']['hits'];
		$db_config['sys_field']['hits_today'] and $count_filed[] = $total_today = $db_config['sys_field']['hits_today'];
		$db_config['sys_field']['hits_yesterday'] and $count_filed[] = $total_yesterday = $db_config['sys_field']['hits_yesterday'];
		$db_config['sys_field']['hits_thisweek'] and $count_filed[] = $total_thisweek = $db_config['sys_field']['hits_thisweek'];
		$db_config['sys_field']['hits_lastweek'] and $count_filed[] = $total_lastweek = $db_config['sys_field']['hits_lastweek'];
		$db_config['sys_field']['hits_thismonth'] and $count_filed[] = $total_thismonth = $db_config['sys_field']['hits_thismonth'];
		$db_config['sys_field']['hits_lastmonth'] and $count_filed[] = $total_lastmonth = $db_config['sys_field']['hits_lastmonth'];
		//没有统计字段
		if (count($count_filed)==0)exit();
		
		//$outtotal输出到页面的统计结果
		//$oldtotal旧的统计数据
		//$newtotal新的统计数据，会更新到数据库
		$oldtotal = Myqee::db($database) -> select(implode(',',$count_filed)) -> from($tablename) -> where($db_config['sys_field']['id'],$mycode['i']) -> get() -> result_array(false);
		$outtotal = $oldtotal = $oldtotal[0];
		
		$newtotal = array();
		if ($total){
			//统计总数
			$newtotal[$total] = $oldtotal[$total] + 1;
			$outtotal[$total] = $newtotal[$total];
		}
		
		$time = $_SERVER['REQUEST_TIME'];
		//今日访问
		if ($total_today){
			$timeset = array (
				'begin' =>strtotime(date("Y-m-d 00:00:00",$time)),
				'end' => strtotime(date("Y-m-d 23:59:59",$time)),
			);
			$outtotal[$total_today] = $newtotal[$total_today] = $this -> _updatetotal($oldtotal[$total_today],$timeset);
		}
		
		//昨日访问
		if ($total_yesterday){
			$timeset = array (
				'begin' =>strtotime(date("Y-m-d 00:00:00", $time)),
				'end' => strtotime(date("Y-m-d 23:59:59", $time)),
				'o_begin' =>strtotime(date("Y-m-d 00:00:00", $time)),
				'o_end' => strtotime(date("Y-m-d 23:59:59", $time)),
			);
			$outtotal[$total_yesterday] = $this -> _updatetotal($oldtotal[$total_yesterday],$timeset,$oldtotal[$total_today],true);
			if ($outtotal[$total_yesterday]!==FALSE){
				$newtotal[$total_yesterday] = $outtotal[$total_yesterday];
			}
		}
		
		//本周访问
		if ($total_thisweek){
			//一星期当中的星期几
			$date_w = date("w",$time);
			$date_w == 0 and $date_w = 7;	//这样做就等同于date("N")，N格式是5.1版本才支持的
			
			$timeset = array (
				'begin' =>strtotime(date("Y-m-d 00:00:00", $time - ( ($date_w-1)*86400) )),
				'end' => strtotime(date("Y-m-d 23:59:59", $time + ( (7-$date_w)*86400) )),
			);
			$outtotal[$total_thisweek] = $newtotal[$total_thisweek] = $this -> _updatetotal($oldtotal[$total_thisweek],$timeset);
		}
		
		//上周访问
		if ($total_lastweek){
			//一星期当中的星期几
			$date_w = date("w",$time);
			$date_w == 0 and $date_w = 7;
			
			$timeset = array (
				'begin' =>strtotime(date("Y-m-d 00:00:00", $time - ( ($date_w-1)*86400) )),
				'end' => strtotime(date("Y-m-d 23:59:59", $time + ( (7-$date_w)*86400) )),
				'o_begin' =>strtotime(date("Y-m-d 00:00:00", $time - (($date_w+7-1)*86400) )),
				'o_end' => strtotime(date("Y-m-d 23:59:59", $time - ($date_w*86400) )),
			);
			$outtotal[$total_lastweek] = $this -> _updatetotal($oldtotal[$total_lastweek],$timeset,$oldtotal[$total_lastweek],true);
			if ($outtotal[$total_lastweek]!==FALSE){
				$newtotal[$total_lastweek] = $outtotal[$total_lastweek];
			}
		}
	
		//本月访问
		if ($total_thismonth){
			$date_j = date("j",$time);		//一个月的第几天
			$date_t = date("t",$time);		//一个月有多少天
			
			$timeset = array (
				'begin' =>strtotime(date("Y-m-d 00:00:00", $time - ( ($date_j-1)*86400) )),
				'end' => strtotime(date("Y-m-d 23:59:59", $time + ( ($date_t-$date_j)*86400) )),
			);
			$outtotal[$total_thismonth] = $newtotal[$total_thismonth] = $this -> _updatetotal($oldtotal[$total_thismonth],$timeset);
		}
		
		//上月访问
		if ($total_lastmonth){
			$date_j = date("j",$time);
			$date_t = date("t",$time);
			$date_last_t = date("t",$time-(86400*$date_j));		//上个月有多少天
			
			$timeset = array (
				'begin' =>strtotime(date("Y-m-d 00:00:00", $time - ( ($date_j-1)*86400) )),
				'end' => strtotime(date("Y-m-d 23:59:59", $time + ( ($date_t-$date_j)*86400) )),
				'o_begin' =>strtotime(date("Y-m-d 00:00:00", $time - (($date_j+$date_last_t-1)*86400) )),
				'o_end' => strtotime(date("Y-m-d 23:59:59", $time - ($date_j*86400) )),
			);
			$outtotal[$total_lastmonth] = $this -> _updatetotal($oldtotal[$total_lastmonth],$timeset,$oldtotal[$total_lastmonth],true);
			if ($outtotal[$total_lastmonth]!==FALSE){
				$newtotal[$total_lastmonth] = $outtotal[$total_lastmonth];
			}
		}
		
		if (count($newtotal)){
			//更新到数据库
			Myqee::db($database) -> update($tablename,$newtotal,array($db_config['sys_field']['id']=>$mycode['i']));
		}
		
		echo '
if (typeof(total_callback)!="function"){
	total_callback = function(total){}
}
total_callback(' , Tools::json_encode($outtotal).');
';
	}
	
	protected function _updatetotal($total,$timeset,$resettotal=null,$resetnum=false){
		$tmpnum = explode('.',$total);		
		if ($tmpnum[0]>=$timeset['begin'] && $tmpnum[0]<=$timeset['end']){
			//符合时间范围
			if ($resetnum){
				//对于类似“昨日访问”，“上周访问”这样的形式，无需再次更新，直接返回false
				return FALSE;
			}else{
				$tmpnum[1] += 1;
			}
		}else{
			if ($resetnum){
				$tmpnum2 = explode('.',$resettotal);
				if ($resettotal && $tmpnum[0]>=$timeset['o_begin'] && $tmpnum[0]<=$timeset['o_end']){
					$tmpnum[1] = $tmpnum2[2];
				}else{
					$tmpnum[1] = '0000000000';
				}
			}else{
				$tmpnum[1] = '0000000001';
			}
		}
		$tmpnum[0] = $_SERVER['REQUEST_TIME'];
		$total = implode('.',$tmpnum);
		return $total;
	}
}
?>