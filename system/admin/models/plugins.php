<?php
/**
 * 此类用来处理插件中虚拟字段的数据
 *
 */
class Plugins_Model_Core extends Model {
	
	/**
	 * 保存数据时对专题处理
	 *
	 * @param int $infoid
	 * @param array $upfield
	 * @param array $dbconfig
	 * @param boolean $isadd
	 */
	public static function specialinfo ($infoid,$upfield,$dbconfig,$isadd=true) {
		$sids = $_POST['info']['#special'];
		if (empty($sids)) {
			$sids = array('-1');
		}
		//add or modify
		$db = Database::instance();
		$data = array();
		$data['infoid'] = $infoid;
		$data['dbname'] = $dbconfig['dbname'];
		$data['posttime'] = time();
		
		if ($isadd) {
			$data['createtime'] = time();
		}
		if ($dbconfig['sys_field']['title']){
			$data['title'] = $upfield[$dbconfig['sys_field']['title']];
		}
		
		if ($dbconfig['sys_field']['imagenews']){
			$data['imagenews'] = $upfield[$dbconfig['sys_field']['imagenews']];
		}
		
		if ($dbconfig['sys_field']['linkurl']){
			$data['linkurl'] = $upfield[$dbconfig['sys_field']['linkurl']];
		}
		
		if ($dbconfig['sys_field']['class_id']){
			$data['class_id'] = $upfield[$dbconfig['sys_field']['class_id']];
		}
		
		if ($dbconfig['sys_field']['class_name']){
			$data['class_name'] = $upfield[$dbconfig['sys_field']['class_name']];
		}
		
		if ($dbconfig['sys_field']['title']){
			$data['title'] = $upfield[$dbconfig['sys_field']['title']];
		}

		if ($dbconfig['sys_field']['isshow']){
			$data['isshow'] = $upfield[$dbconfig['sys_field']['isshow']];
		}
		
		if ($dbconfig['sys_field']['isheadlines']){
			$data['isheadlines'] = $upfield[$dbconfig['sys_field']['isheadlines']];
		}
		if ($dbconfig['sys_field']['ontop']){
			$data['ontop'] = $upfield[$dbconfig['sys_field']['ontop']];
		}
		if ($dbconfig['sys_field']['is_hot']){
			$data['ishot'] = $upfield[$dbconfig['sys_field']['is_hot']];
		}
		if ($dbconfig['sys_field']['iscommend']){
			$data['iscommend'] = $upfield[$dbconfig['sys_field']['iscommend']];
		}
		//算出url
		$upfield[$dbconfig['sys_field']['id']] = $data['infoid'];
		$classid = $data['class_id'] >0 ? $data['class_id'] : $dbconfig['dbname'];
		$adminmodel = new Admin_Model();
		$data['url'] = $adminmodel->getinfourl($classid,$upfield);
		
		unset ($data[$dbconfig['sys_field']['id']]);
		//end		
		foreach ($sids as $val) {
			$data['sid'] = $val;
			$db->merge('[special_info]',$data);
		}
		//del
		$db->in('sid',$sids,true)->delete('[special_info]',array('infoid'=>$infoid,'dbname'=>$dbconfig['dbname']));
	}
	
	/**
	 * 处理模型是对专题的处理
	 *
	 * @param array $dbset
	 * @param array $model_set
	 * @param array $info
	 */
	public static function specialmodel ($dbset,&$model_set,&$info) {
		$classid = intval($info[$dbset['sys_field']['class_id']]);
		if ($classid < 0 ) {
			return;
		}
		$db = Database::instance();
		$query = $db->select ('sid,title,classides,isrecursion')->orderby(array('myorder'=>'asc','sid'=>'asc'))->get('[special]')->result_array(false);
		if (empty($query)) {
			return ;
		}
		$specials = array();
		
		foreach ($query as $val) {
			//找出 $classid的子栏目
			$_fatherclasses = array();
			$_canaddclasses = array();
			if ($val['classides'] == '|0|') {
				$specials[] = $val;
				continue;
			}
			
			if ($val['isrecursion']) {
				//是递归的话找出所有的classid
				$tmp = explode('|',trim($val['classides'],'|'));
				foreach ($tmp as $v) {
					$_canaddclasses[] = $v;
					$_config = Myqee::config('class/class_'.$v);
					$_sonclasses =explode('|',trim($_config['sonclass'],'|'));
					$_canaddclasses = array_merge($_canaddclasses,$_sonclasses);
				}
				$_config = Myqee::config('class/class_'.$classid);
				$_fatherclasses = explode('|',trim($_config['fatherclass'],'|'));
			}else{
				$_canaddclasses = explode('|',trim($val['classides'],'|'));
			}
			//子栏目或者父栏目中有
			if (in_array($classid,$_canaddclasses) || array_intersect($_fatherclasses,$_canaddclasses)) {
				$specials[] = $val;
			}
		}
		if (empty($specials)) {
			return ;
		}
		$candidate = array();
		foreach ($specials as $val) {
			$candidate[$val['sid']] = $val['title'];
		}
		$model_set['dbset']['#special']['candidate'] = $candidate;
		//查询已经所在的专题
		$query = $db->select('sid')->getwhere ('[special_info]',array('infoid'=>$info['id'],'dbname'=>$model_set['dbname']))->result_assoc();
		if (empty($query)) {
			return ;
		}
		$sids = array_keys($query);
		$info['#special'] = implode('|',$sids);
	}
}