<?php defined('MYQEEPATH') or die('No direct script access.');

/**
 * ucenter api
 *
 * $Id: uchome.php,v 1.3 2009/07/08 03:25:22 songwubin Exp $
 *
 * @package    Core
 * @author     Myqee Team
 * @copyright  (c) 2008-2009 Myqee Team
 * @license    http://myqee.com/license.html
 */
class Uchome_Api_Core {
	protected static $instance;
	protected $config;

	protected static $db;
	/**
	 * Singleton instance of session.
	 */
	public static function instance($dbname='uchome')
	{
		if (self::$instance == NULL)
		{
			// Create a new instance
			self::$instance = new Uchome_Api($dbname);
		}
		return self::$instance;
	}
	
	public static function db($dbname='uchome'){
		return self::$db;
	}
	
	public function __construct($dbname='uchome'){
		$this -> db = Database::instance($dbname);
	}

	/**
	 * 提交积分
	 * 若$credit为负值，则是扣住用户积分
	 *
	 * @param int $uid
	 * @param int $credit
	 * @return int count
	 */
	public function update_credit($uid,$credit){
		$credit = (int)$credit;
		if ($credit==0)return 0;
		return $this -> db -> query ('UPDATE `'.$this -> db -> table_prefix().'space` SET `credit` = credit + ('.$credit.') WHERE `uid` = '.$uid) -> count();
	}

	/**
	 * 获取话题
	 * @param int $uid
	 * @return array $data
	 */
	public function get_thread($where = NULL,$limit=10,$offset=0,$orderby=NULL){
		$sql = $this -> db -> select('thread.*,post.message')
		-> from('thread');
		if($where){
			$sql = $sql -> where($where);
		}
		if (is_array($orderby)){
			foreach ($orderby as $key=>$value){
				$sql = $sql -> orderby($key,$value);
			}
		}
		$sql = $sql -> join('post','thread.tid','post.tid') -> limit($limit,$offset);
		$data = $sql->get() ->result_array(FALSE);
		$count = count($data);
		$spaceurl = rtrim(Myqee::config('core.home_url'),'/').'/';
		if ($count>0){
			for ($i=0;$i<$count;$i++){
				if (isset($data[$i]['URL']))$data[$i]['url']=$data[$i]['URL'];
				$data[$i]['URL'] = $spaceurl.'space.php?uid='.$data[$i]['uid'].'&do=thread&id='.$data[$i]['tid'];
			}
		}
		return $data;
	}

	/**
	 * 获取空间信息
	 *
	 * @param int $uid
	 * @return array $data
	 */
	public function get_spaceinfo($uid){
		$data = $this -> db_data('space',array('uid'=>$uid),1);
		return $data[0];
	}
	
	public function get_credit($uid){
		$data = $this -> get_spaceinfo($uid);
		return (int)$data['credit'];
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
	 * 创建Uchome
	 *
	 * @param int $uid
	 * @param string $username
	 * @param string $password
	 * @param string $email
	 * @param array $profield	高级字段
	 * @param array $spacefield	空间字段
	 * @return boolean true/-1/-2/-3	true成功，-1创建用户表失败，-2创建空间失败，-3创建空间字段失败
	 */
	public function new_user($uid,$username,$password,$email,$profield=null,$spacefield=null){
		$c1= $this -> db ->  merge('member',array(
			'uid' => $uid,
			"username"	=> $username,
			"password"	=> md5($password)
		)) -> count();
		if (!$c1)return -1;
		
		//开通空间
		
		
		$spacefield = array(
			'uid' => $uid,
			'username' => $username,
			'dateline' => $_SERVER['REQUEST_TIME'],
			'groupid' => 0,
		);
		if ($profield && is_array($profield)){
			$myfield = array('name','namestatus','domain','viewnum','notenum','friendnum','updatetime','lastsearch','lastpost','lastlogin','lastsend','attachsize','addsize','flag','newpm','avatar','ip','mood');
			foreach ($myfield as $k => $v){
				if (isset($profield[$v])){
					$spacefield[$v] = $profield[$v];
				}
			}
		}
		$c2 = $this -> db -> merge('space', $spacefield ) -> count();
		if (!$c2)return -2;
		
		
		
		$sfield = array(
			'uid'=>$uid,
			'email'=>$email,
			'css' => '',
			'privacy' => '',
			'friend' => '',
			'feedfriend' => '',
			'sendmail' => '',
		);
		if ($spacefield && is_array($spacefield)){
			$myfield = array('sex','emailcheck','qq','msn','birthyear','birthmonth','birthday','blood','marry','birthprovince','birthcity','resideprovince','residecity','note','spacenote','authstr','theme','nocss','menunum','css','privacy','friend','feedfriend','sendmai');
			foreach ($myfield as $k => $v){
				if (isset($profield[$v])){
					$spacefield[$v] = $profield[$v];
				}
			}
		}
		$c3 = $this -> db -> merge('spacefield', $sfield ) -> count();
		
		return $c3?TRUE:-3;
	}
	
	public function add_friend($uid,$fuid,$status=1,$note=''){
		if(!$uid||!$fuid)return FALSE;
		$time = $_SERVER['REQUEST_TIME'];
		$query = $this -> db -> select('username') -> form('space') -> where('uid',$uid) -> orwhere('uid',$fuid) -> get() -> result_array(FALSE);
		foreach ($query as $value){
			if ($value['uid']==$uid){
				$username = $value['username'];
			}
			if ($value['uid']==$fuid){
				$fusername = $value['username'];
			}
		}
		if (!$username || !$fusername)return FALSE;
		$up1 = $this -> db -> merge('friend', array(
			'uid' => $uid,
			'fuid' => $fuid,
			'fusername' => $fusername,
			'status' => 1,
			'gid' => 0,
			'note' => $note,
			'num' => 1,
			'dateline' => $time,
		)) -> count();
		
		$up1 = $this -> db -> merge('friend', array(
			'uid' => $fuid,
			'fuid' => $uid,
			'fusername' => $username,
			'status' => 1,
			'gid' => 0,
			'note' => $note,
			'num' => 1,
			'dateline' => $time,
		));
		if ($up1 && $up2){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	
	public function del_friend($uid,$fuid){
		$c1 = $this -> db -> delete('friend',array('uid'=>$uid,'fuid'=>$fuid)) -> count();
		$c2 = $this -> db -> delete('friend',array('uid'=>$fuid,'fuid'=>$uid)) -> count();
		if ($c1 || $c2){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	

	//删除空间
	function deletespace($uid, $force=0) {
		return FALSE;	//下面还未测试
		$delspace = array();
		$my_uid = Myqee::get_cookie('uid');
		$my_uname = Myqee::get_cookie('uname');
		//$allowmanage = 1;
		$query = $this -> db -> from('space') -> where ('uid',$uid) -> get -> result_array(FALSE);
		foreach ($query as $value) {
			if($force || $allowmanage && $value['uid'] != $my_uid) {
				$delspace = $value;
				//如果不是强制删除则入删除记录表
				if(!$force) {
					$setarr = array(
						'uid' => $value['uid'],
						'username' => $value['username'],
						'opuid' => $my_uid,
						'opusername' => $my_uname,
						'flag' => '-1',
						'dateline' => $_SERVER['REQUEST_TIME']
					);
					$this -> db -> insert('spacelog', $setarr);
				}
			}
		}
		if(empty($delspace)) return array();
	
		//space
		$this -> db -> delete('space',array('uid'=>$uid));
		//spacefield
		$this -> db -> delete('spacefield',array('uid'=>$uid));
	
		//feed
		$this -> db -> delete('feed',array('uid'=>$uid));
	
		//记录
		$this -> db -> delete('doing',array('uid'=>$uid));
		
		//删除记录回复
		$this -> db -> delete('docomment',array('uid'=>$uid));
		
		//分享
		$this -> db -> delete('share',array('uid'=>$uid));
	
		//数据
		$this -> db -> delete('album',array('uid'=>$uid));
		
		//删除通知
		$this -> db -> where('uid',$uid) -> orwhere('authorid',$uid) -> delete('notification');
		
		//删除打招呼
		$this -> db -> where('uid',$uid) -> orwhere('fromuid',$uid) -> delete('poke');
		
		//pic
		//删除图片附件
		$pics = array();
		$query = $this -> db -> select('filepath') -> from('pic') -> where('uid',$uid) -> get() -> result_array(FALSE);
		foreach ($query as $value) {
			$pics[] = $value;
		}
		//数据
		$this -> db -> delete('pic',array('uid'=>$uid));
	
		//blog
		$blogids = array();
		$query = $this -> db -> select('blogid') -> from ('blog') -> where ('uid',$uid) -> get() -> result_array(FALSE);
		foreach ($query as $value) {
			$blogids[$value['blogid']] = $value['blogid'];
			//tag
			$tags = array();
			$subquery = $this -> db -> select('tagid, blogid') ->  from('tagblog') -> where ('blogid',$value['blogid']) -> get -> result_array(FALSE);
			foreach ($subquery as $tag ) {
				$tags[$tag['tagid']] = $tag['tagid'];
			}
			if($tags) {
				$this -> db -> query('UPDATE `'.$this -> db -> table_prefix()."tag` SET blognum=blognum-1 WHERE tagid IN (".implode(',',$tags).")");
				$this -> db -> delete('tagblog',array('blogid'=>$value['blogid']));
			}
		}
		//数据删除
		$this -> db -> delete('blog',array('uid'=>$uid));
		$this -> db -> delete('blogfield',array('uid'=>$uid));
	
		//评论
		$this -> db -> query("DELETE FROM `".$this -> db -> table_prefix()."comment` WHERE (uid='$uid' OR authorid='$uid' OR (id='$uid' AND idtype='uid'))");
	
		//访客
		$this->db->query("DELETE FROM `".$this -> db -> table_prefix()."visitor` WHERE (uid='$uid' OR vuid='$uid')");
		
		//删除活动记录
		$this -> db -> delete('usertask',array('uid'=>$uid));
	
		//class
		$this -> db -> delete('class',array('uid'=>$uid));
	
		//friend
		//好友
		$this->db->query("DELETE FROM `".$this -> db -> table_prefix()."friend` WHERE (uid='$uid' OR fuid='$uid')");
	
		//member
		$this -> db -> delete('member',array('uid'=>$uid));
		
		//删除脚印
		$this -> db -> delete('trace',array('uid'=>$uid));
		
		//删除黑名单
		$this->db->query("DELETE FROM `".$this -> db -> table_prefix()."blacklist` WHERE (uid='$uid' OR buid='$uid')");
		
		//删除邀请记录
		$this->db->query("DELETE FROM `".$this -> db -> table_prefix()."invite` WHERE (uid='$uid' OR fuid='$uid')");
		
		//删除邮件队列
		$this->db->query("DELETE FROM ".$this -> _tname('mailcron').", ".$this -> _tname('mailqueue')." USING ".$this -> _tname('mailcron').", ".$this -> _tname('mailqueue')." WHERE ".$this -> _tname('mailcron').".touid='$uid' AND ".$this -> _tname('mailcron').".cid=".$this -> _tname('mailqueue').".cid");
	
		//漫游邀请
		$this->db->query("DELETE FROM ".$this -> _tname('myinvite')." WHERE (touid='$uid' OR fromuid='$uid')");
		$this->db->query("DELETE FROM ".$this -> _tname('userapp')." WHERE uid='$uid'");
		
		//mtag
		//thread
		$tids = array();
		$query = $this->db->query("SELECT tid, tagid FROM ".$this -> _tname('thread')." WHERE uid='$uid'")->result_array(FALSE);
		foreach ($query as $value){
			$tids[$value['tagid']][] = $value['tid'];
		}
		foreach ($tids as $tagid => $v_tids) {
			deletethreads($tagid, $v_tids);
		}
	
		//post
		$pids = array();
		$query = $this->db->query("SELECT pid, tagid FROM ".$this -> _tname('post')." WHERE uid='$uid'") ->result_array(FALSE);
		foreach ($query as $value){
			$pids[$value['tagid']][] = $value['pid'];
		}
		foreach ($pids as $tagid => $v_pids) {
			deleteposts($tagid, $v_pids);
		}
		$this->db->query("DELETE FROM ".$this -> _tname('thread')." WHERE uid='$uid'");
		$this->db->query("DELETE FROM ".$this -> _tname('post')." WHERE uid='$uid'");
	
		//session
		$this->db->query("DELETE FROM ".$this -> _tname('session')." WHERE uid='$uid'");
		
		//排行榜
		$this->db->query("DELETE FROM ".$this -> _tname('show')." WHERE uid='$uid'");
	
		//群组
		$mtagids = array();
		$query = $this->db->query("SELECT * FROM ".$this -> _tname('tagspace')." WHERE uid='$uid'") -> result_array(FALSE);
		foreach ($query as $value){
			$mtagids[$value['tagid']] = $value['tagid'];
		}
		if($mtagids) {
			$this->db->query("UPDATE ".$this -> _tname('mtag')." SET membernum=membernum-1 WHERE tagid IN (".simplode($mtagids).")");
			$this->db->query("DELETE FROM ".$this -> _tname('tagspace')." WHERE uid='$uid'");
		}
		
		$this->db->query("DELETE FROM ".$this -> _tname('mtaginvite')." WHERE (uid='$uid' OR fromuid='$uid')");
		
		//删除图片
		deletepicfiles($pics);//删除图片
		//删除举报
		$this->db->query("DELETE FROM ".$this -> _tname('report')." WHERE id='$uid' AND idtype='space'");
		//变更记录
		$this -> db -> merge('userlog', array('uid'=>$uid, 'action'=>'delete', 'dateline'=>$_SGLOBAL['timestamp']));
	
		return $delspace;
	}
	
	protected function _tname($dbname){
		return '`'.$this -> db -> table_prefix().$dbname.'`';
	}
	
		
	//删除话题
	public function deletethreads($tagid, $tids) {
	
		$tnums = $pnums = $delthreads = $newids = $spaces = array();
		//$allowmanage = checkperm('managethread');
		$allowmanage = '1';
	
		//群主
		$wheresql = '';
		if(empty($allowmanage) && $tagid) {
			$mtag = $this -> getmtag($tagid);
			if($mtag['grade'] >=8) {
				$allowmanage = 1;
				$wheresql = " AND t.tagid='$tagid'";
			}
		}
	
		$query = $this->db->query("SELECT t.* FROM ".$this -> _tname('thread')." t WHERE t.tid IN(".simplode($tids).") $wheresql") -> result_array(FALSE);
		foreach ($query as $value){
			if($allowmanage || $value['uid'] == Myqee::get_cookie('uid')) {
				$newids[] = $value['tid'];
				$value['isthread'] = 1;
				$delthreads[] = $value;
				$spaces[$value['uid']]++;
			}
		}
		if(empty($delthreads)) return array();
	
		//删除
		$this->db->query("DELETE FROM ".$this -> _tname('thread')." WHERE tid IN(".simplode($newids).")");
		$this->db->query("DELETE FROM ".$this -> _tname('post')." WHERE tid IN(".simplode($newids).")");
		
		//删除举报
		$this->db->query("DELETE FROM ".$this -> _tname('report')." WHERE id IN (".simplode($newids).") AND idtype='thread'");
	
		//积分
//		updatespaces($spaces, 'thread');
	
		return $delthreads;
	}
	
	
	//群组信息
	function getmtag($id) {
		
		$query = $this -> query("SELECT * FROM ".$this -> _tname('mtag')." WHERE tagid='$id'") -> result_array(FALSE);
		$mtag = $query[0];
		if(!count($mtag)) {
			Myqee::show_error('designated_election_it_does_not_exist');
		}
		//空群组
		if($mtag['membernum']<1 && ($mtag['joinperm'] || $mtag['viewperm'])) {
			$mtag['joinperm'] = $mtag['viewperm'] = 0;
			$this -> db -> update('mtag', array('joinperm'=>0, 'viewperm'=>0), array('tagid'=>$id));
		}
		
		//处理
//		include_once(S_ROOT.'./data/data_profield.php');
//		$mtag['field'] = $_SGLOBAL['profield'][$mtag['fieldid']];
//		$mtag['title'] = $mtag['field']['title'];
//		if(empty($mtag['pic'])) {
//			$mtag['pic'] = 'image/nologo.jpg';
//		}
	
		//成员级别
		$mtag['ismember'] = 0;
		$mtag['grade'] = -9;//-9 非成员 -2 申请 -1 禁言 0 普通 1 明星 8 副群主 9 群主
		$query = $this->db->query("SELECT grade FROM ".$this -> _tname('tagspace')." WHERE tagid='$id' AND uid='$_SGLOBAL[supe_uid]' LIMIT 1");
		if($value = $this->db->fetch_array($query)) {
			$mtag['grade'] = $value['grade'];
			$mtag['ismember'] = 1;
		}
		if($mtag['grade'] < 9 && checkperm('managemtag')) {
			$mtag['grade'] = 9;
		}
		$mtag['allowpost'] = $mtag['grade']>=0?1:0;
		$mtag['allowview'] = ($mtag['viewperm'] && $mtag['grade'] < -1)?0:1;
		
		$mtag['allowinvite'] = $mtag['grade']>=0?1:0;
		if($mtag['joinperm'] && $mtag['grade'] < 8) {
			$mtag['allowinvite'] = 0;
		}
		
		return $mtag;
	}
		
}