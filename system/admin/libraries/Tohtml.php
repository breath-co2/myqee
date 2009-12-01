<?php
/**
 * File Tohtml class.
 *
 * $Id: Tohtml.php,v 1.1 2009/09/22 01:22:28 jonwang Exp $
 *
 * @package    Core
 * @author     Myqee Team
 * @copyright  (c) 2007-2009 Myqee Team
 * @license    http://myqee.com/license.html
 */
class Tohtml_Core {
	
	protected static $snoopy;
	
	
	public static function toindex($iseditblock=false){
		return self::_run_tohtml('toindexpage',$iseditblock?'_editblock=yes':null);
	}
	
	public static function tositeindex($siteid=0,$iseditblock=false){
		if ($siteid){
			$other = '_siteid='.$siteid.'&';
		}
		if ($iseditblock){
			$other .= '_editblock=yes';
		}
		return self::_run_tohtml('tositeindexpage',$other);
	}
	
	public static function tocustompage($id=0,$iseditblock=false){
		if ($id){
			$other = '_theid='.$id.'&';
		}
		if ($iseditblock){
			$other .= '_editblock=yes';
		}
		return self::_run_tohtml('tocustompage',$other);
	}
	
	public static function tocustomlist($id=0,$page=1,$iseditblock=false){
		$other = '_page='.$page.'&';
		if ($id){
			$other .= '_theid='.$id.'&';
		}
		if ($iseditblock){
			$other .= '_editblock=yes';
		}
		
		return self::_run_tohtml('tositeindexlist',$other);
	}
	
	/**
	 * 生成指定栏目页
	 * @param $classid 栏目ID，留空则全部
	 * @param $page 起始页码，0表示封面
	 * @param $iseditblock 是否管理编辑碎片
	 * @return string/array 返回信息
	 */
	public static function toclass($classid=0,$offset=0,$allclassid=null,$limit=100,$iseditblock=false){
		if (!$allclassid){
			$allclassid=$classid;
		}elseif(is_array($allclassid)){
			$allclassid = implode(',',$allclassid);
		}else{
			$allclassid = Tools::formatids($allclassid,true);
		}
		$other = '_nowclassid='.$classid.'&_limit='.$limit.'&_offset='.$offset.'&_allclassid='.$allclassid;
		if ($iseditblock){
			$other .= '_editblock=yes';
		}
		
		return self::_run_tohtml('toclass_byclassid',$other);
	}
	
	
	protected static function _run_tohtml($type,$other=null){
		$url = _get_tohtmlurl($type,Myqee::config('encryption.default.key'),$other);
		if (substr($url,0,1)=='/'){
			$url = Myqee::protocol() .'://'. $_SERVER['HTTP_HOST'] . $url;
		}
		$snoopy = Snoopy::instance();
		$snoopy -> fetch($url,$_SERVER['SERVER_ADDR']);
		if (substr($snoopy->results,0,1)=='{' && ($tmparr = Tools::json_decode($snoopy->results)) && is_array($tmparr) ){
			return $tmparr;
		}else{
			return $snoopy->results;
		}
	}
	
} // End Plugins