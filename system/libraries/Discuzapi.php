<?php defined('MYQEEPATH') or die('No direct script access.');

/**
 * discuz api
 *
 * $Id$
 *
 * @package    Core
 * @author     Myqee Team
 * @copyright  (c) 2008-2009 Myqee Team
 * @license    http://myqee.com/license.html
 */
class Discuzapi_Core {
	protected static $instance;
	protected $config;

	protected static $db;
	/**
	 * Singleton instance of session.
	 */
	public static function instance($dbname='bbs')
	{
		if (self::$instance == NULL)
		{
			// Create a new instance
			self::$instance = new Discuzapi($dbname);
		}
		return self::$instance;
	}
	
	public static function db($dbname='bbs'){
		return self::$db;
	}
	
	public function __construct($dbname='bbs'){
		$this -> db = Database::instance('bbs');
	}

	public function db_data($dbname,$where = null,$limit=10,$offset=0,$orderby=null){
		$data = $this -> db -> from ($dbname);
		if($where){
			$data -> where($where);
		}
		if (is_array($orderby)){
			foreach ($orderby as $key=>$value){
				$data = $data -> orderby($key,$value);
			}
		}
		$data = $data -> limit($limit,$offset) -> get() -> result_array(FALSE);
		return $data;
	}
	
	/**
	 * 获取帖子
	 * @param int $uid
	 * @return array $data
	 */
	public function get_threads($where = NULL,$limit=10,$offset=0,$orderby=NULL){
		$sql = $this -> db -> select('threads.*,posts.message,forums.name')
		-> from('threads');
		if($where){
			$sql = $sql -> where($where);
		}
		if (is_array($orderby)){
			foreach ($orderby as $key=>$value){
				$sql = $sql -> orderby($key,$value);
			}
		}
		$sql = $sql -> join('forums','threads.fid','forums.fid');
		$sql = $sql -> join('posts','threads.tid','posts.tid') -> limit($limit,$offset);
		$data = $sql->get() ->result_array(FALSE);
		$count = count($data);
//		echo $sql->last_query();
		$bbsurl = rtrim(Myqee::config('core.bbs_url'),'/').'/';
		if ($count>0){
			for ($i=0;$i<$count;$i++){
				if (isset($data[$i]['URL']))$data[$i]['url']=$data[$i]['URL'];
				$data[$i]['URL'] = $bbsurl.'viewthread.php?tid='.$data[$i]['tid'];
				$data[$i]['FURL'] = $bbsurl.'forumdisplay.php?fid='.$data[$i]['fid'];
			}
		}
		return $data;
	}
	
	protected function _tname($dbname){
		return '`'.$this -> db -> table_prefix().$dbname.'`';
	}
}