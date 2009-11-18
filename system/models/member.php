<?php
/**
 * Member Model.
 *
 * $Id: member.php,v 1.1 2009/06/18 05:21:16 jonwang Exp $
 *
 * @package    Image
 * @author     Myqee Team
 * @copyright  (c) 2007-2008 Myqee Team
 * @license    http://myqee.com/license.html
 */
class Member_Model_Core extends Model {
	protected $uid=0;
	protected $memberdb = 'members';
	protected $dbconfig;
	
	public function __construct() {
		parent::__construct();
		$memberdb = Myqee::config('core.member_db');
		if ($memberdb)$this -> memberdb = $memberdb;
		$this -> dbconfig = Myqee::config('db/'.$this -> memberdb);
		if (!$this -> dbconfig || !$this -> dbconfig['sys_field']['id'] || !$this -> dbconfig['sys_field']['username']){
			Myqee::show_info('用户表配置错误，请联系管理员！');
		}
	}
	
	public function get_profile($uid=0,$select = '*',$isuname=false){
		if (!$uid)return false;
		if ($isuname){
			$where = array($this -> dbconfig['sys_field']['username']=>$uid);
		}else{
			$where = array($this -> dbconfig['sys_field']['id']=>$uid);
		}
		$result = $this -> db -> select($select) -> getwhere($this -> memberdb,$where) -> result_array(false);
		
		return $result[0];
	}
	
	public function set_profile($profile,$uid=0){
		$uid or $uid = Passport::get_loginuid();
		if (!$uid)return false;
		
		
	}
}