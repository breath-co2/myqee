<?php
/**
 * $Id$
 *
 * @package    Core
 * @author     Myqee Team
 * @copyright  (c) 2008-2009 Myqee Team
 * @license    http://myqee.com/license.html
 */
class Tohtml_Controller_Core extends Controller {
	function __construct() {
		parent::__construct ( NULL );
		Passport::chkadmin ();
	}

	public function index() {
		Passport::checkallow('task.dohtml_class');
		$view = new View ( 'admin/tohtml' );
		$adminmodel = new Admin_Model();
		$query = $adminmodel->get_speciallist();
		$speciallist[0] = '全部';
		foreach ($query as $val) {
			$speciallist[$val['sid']] = $val['title'];
		}
		$view->set ('speciallist',$speciallist);
		$view->set ( 'classtree', $adminmodel->get_allclass_array( 'classid,classname,bclassid,classpath,hits,myorder' ) );
		$view->render ( TRUE );
	}
	
	/**
	 * 生成首页
	 */
	public function toindex(){
		Passport::checkallow('task.dohtml_index');
		
		$result = Tohtml::toindex($_GET['_editblock']=='yes'?true:false);
		if ($_GET['_editblock']=='yes'){
			echo $result;
			return true;
		}
		if ($result['ok']){
			echo '恭喜，首页生成成功！<script>parent.alert("恭喜，首页生成成功！");window.close();</script>';
		}elseif($result['error']){
			echo '生成失败！<br/>错误信息：<br/>', $result['error'], '<script>alert("生成失败：'.$result['error'].'！");</script>';
		}else{
			echo '生成失败！<br/>错误信息：<br/>', $result, '<script>alert("抱歉，首页生成失败！");</script>';
		}
	}
	
	/**
	 * 生成子站点首页
	 *
	 */
	public function siteindex($siteid=0){
		Passport::checkallow ( 'task.dohtml_siteindex' );
		
		$result = Tohtml::tositeindex($siteid,$_GET['_editblock']=='yes'?true:false);
		if ($_GET['_editblock']=='yes'){
			echo $result;
			return true;
		}
		if ($result['ok']){
			if ($result['err']){
				$msg = '成功生成' . $result['ok'] . '，生成失败：' . $result['err'];
				echo $msg,'<script>parent.alert("',$msg,'");window.close();</script>';
			}else{
				echo '恭喜，站点首页面成功！<script>parent.alert("恭喜，站点首页面成功！");window.close();</script>';
			}
		}elseif($result['error']){
			echo '生成失败！<br/>错误信息：<br/>', $result['error'], '<script>alert("生成失败：'.$result['error'].'！");</script>';
		}else{
			echo '生成失败！<br/>错误信息：<br/>', $result, '<script>alert("抱歉，站点首页面生成失败！");</script>';
		}
	}

	/**
	 * 生成自定义页
	 */
	public function tocustompage($theid=0) {
		Passport::getisallow ( 'task.dohtml_custompage');
		
		$result = Tohtml::tocustompage($theid,$_GET['_editblock']=='yes'?true:false);
		if ($_GET['_editblock']=='yes'){
			echo $result;
			return true;
		}
		if ($result['ok']){
			echo '恭喜，'.$result['ok'].'！<script>parent.alert("恭喜，'.$result['ok'].'");window.close();</script>';
		}elseif($result['error']){
			echo '抱歉，'.$result['error'].'！<script>parent.alert("抱歉，'.$result['error'].'");window.close();</script>';
		}else{
			echo '生成失败！<br/>错误信息：<br/>', $result, '<script>alert("抱歉，站点首页面生成失败！");</script>';
		}
	}
	
	
	public function tocustomlist($theid=0) {
		Passport::getisallow ( 'task.dohtml_customlist');
		echo Tohtml::tocustomlist($theid,1,$_GET['_editblock']=='yes'?true:false);
	}
	
	/**
	 * 生成专题
	 */
	public function sframe() {
		$type = $_GET ['type'];
		$view = new View ( 'admin/tohtml_sframe' );
		$adminmodel = new Admin_Model ();

		$specialid_array = ( array ) $_GET ['specialid'];
		if (empty($specialid_array)) {
			echo '请选择专题！<script>parent.alert("请选择专题");window.close();</script>';
		}
		if (in_array ( 0, $specialid_array )) {
			//获取所有栏目ID
			$allspecial = $adminmodel->get_speciallist();
			$special_info = $allspecial [0];
		} else {
			$special_info = $adminmodel->get_specialinfo($specialid_array[0]);
			$allspecial = $adminmodel->db->from ( '[special]' )->in ( 'sid', $specialid_array )->orderby ( 'sid', 'asc' )->get ()->result_array ( FALSE );
		}
		
		$dohtml_limit = Myqee::config ( 'core.tohtml_limit' );
		
		$specialid_array = array ();
		foreach ( $allspecial as $item ) {
			//过滤一些不必生成静态页的栏目
			if ($this->specialinfo['isnothtml'] || $this->specialinfo['list_tohtml'] || $this->specialinfo['cover_tohtml']) {
				continue;
			}
			
			$specialid_array [] = $item ['sid'];
			$does [] = array ('sid' => $item ['sid'], 'info' => '生成专题<font color="#12C450">[' . $item ['title'] . ']</font> (id:' . $item ['sid'] . ')' );
		}
		
		$view->set ( 'tohtmlurl', _get_tohtmlurl ( 'tospecial_byspecialid', Myqee::config( 'encryption.default.key' ), '_allspecialid=' . join ( ',', $specialid_array ) . '&_nowspecialid=' . $specialid_array [0] . '&_limit=' . $dohtml_limit  ) );
		$view->set ( 'dohtml_limit', $dohtml_limit );
		$view->set ( 'does', $does );
		$view->set ( 'special_info', $special_info );
		$view->render ( TRUE );
	}
	
	public function frame() {
		$type = $_GET ['type'];
		$view = new View ( 'admin/tohtml_frame' );
		$adminmodel = new Admin_Model ();

		$classid_array = ( array ) $_GET ['classid'];
		if (in_array ( 0, $classid_array )) {
			//获取所有栏目ID
			$allclass = $adminmodel->db->from ( '[class]' )->where(array('iscontent'=>1,'content_tohtml'=>0))->orderby ( 'classid', 'asc' )->get ()->result_array ( FALSE );
			$class_info = $allclass [0];
		} else {
			$class_info = $adminmodel->get_class_array ( $classid_array [0] );
			$allclass = $adminmodel->db->from ( '[class]' )->in ( 'classid', $classid_array )->orderby ( 'classid', 'asc' )->get ()->result_array ( FALSE );
		}
		
		$dohtml_limit = Myqee::config ( 'core.tohtml_limit' );
		
		if ($type == 'info' || $type == 'class') {
			$classid_array = array ();
			foreach ( $allclass as $item ) {
				
				//过滤一些不必生成静态页的栏目
				if ($type=='class'){
					if ( !($item['iscover']&&$item['cover_tohtml']==0) && !($item['islist']&&$item['list_tohtml']==0) ){
						continue;
					}
				}else{
					if ( !($item['iscontent']&&$item['content_tohtml']==0) ){
						continue;
					}
				}
				
				$classid_array [] = $item ['classid'];
				$does [] = array ('classid' => $item ['classid'], 'info' => '生成栏目<font color="#12C450">[' . $item ['classname'] . ']</font> (id:' . $item ['classid'] . ')所有' . ($type == 'info' ? '信息页' : '栏目页') );
			}
			if ($type == 'info' && $_GET ['noreto_contenthtml']) {
				$other = '&_noretohtml=1';
			} else {
				$other = '';
			}
			$view->set ( 'tohtmlurl', _get_tohtmlurl ( ($type == 'info' ? 'toinfo_byclassid' : 'toclass_byclassid'), Myqee::config( 'encryption.default.key' ), '_allclassid=' . join ( ',', $classid_array ) . '&_nowclassid=' . $classid_array [0] . '&_limit=' . $dohtml_limit . $other ) );
		}
		
		$view->set ( 'dohtml_limit', $dohtml_limit );
		$view->set ( 'does', $does );
		$view->set ( 'classinfo', $class_info );
		$view->set ( 'type', $type );
		
		$view->render ( TRUE );
	}
	
	public function class_block_cover($class_id=0){
		$this -> _class_block($class_id,'cover');
	}
	public function class_block_list($class_id=0){
		$this -> _class_block($class_id,'list');
	}
	public function class_block_content($class_id=0){
		$this -> _class_block($class_id,'content');
	}
	public function class_block_search($class_id=0){
		$this -> _class_block($class_id,'search');
	}
	
	protected  function _class_block($class_id,$blocktype){
		$class_id = (int)$class_id;
		if (!$class_id>0){
			MyqeeCMS::show_error('缺少参数',true);
		}
		$adminmodel = new Admin_Model();
		$classarray = $adminmodel -> get_class_array($class_id,'iscover,islist,iscontent,issearch');
		if ($blocktype=='cover'){
			if (!$classarray['iscover']){
				MyqeeCMS::show_error('本栏目无栏目封面！',true);
			}
		}elseif($blocktype=='list'){
			if (!$classarray['islist']){
				MyqeeCMS::show_error('本栏目无栏目列表！',true);
			}
		}elseif($blocktype=='content'){
			if (!$classarray['iscontent']){
				MyqeeCMS::show_error('本栏目不可录入信息！',true);
			}
		}elseif($blocktype=='search'){
			if (!$classarray['issearch']){
				MyqeeCMS::show_error('本栏目没有搜索功能！',true);
			}
		}
		
		$url = _get_tohtmlurl ( 'class_block', 
		Myqee::config('encryption.default.key' ), 
		'_nowclassid='.$class_id.'&_editblock=yes&_blocktype='.$blocktype);
		header('location: '.$url);
		exit('');
	}
}