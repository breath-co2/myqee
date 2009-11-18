<?php
class Comment_Api_Core {
	public function iframe ($page=1,$classid=0,$news_id=0) {
		$canshow = $this->check_ifcomment($classid);
		$str = '';
		if ($canshow) {
			$str = '<iframe width=750 height=500 style="border:0px"  src="'.Myqee::url("plugins/run/comment/comment/showcomment/{$page}/{$classid}/{$news_id}").'" ></iframe>';
		}
		return $str;
	}

	/**
	 * 检查栏目是否可以评论
	 * @param int $classid
	 * @return boolean
	 */
	public function check_ifcomment ($classid=0) {
		//判断此栏目是否允许评论
		$canshow = false;
		$config = ( array ) Myqee::config('plugins/comment');
		if (empty($config['classides'])) {
			return $canshow;
		}
		
		if (in_array($classid,$config['classides'])) {
			$canshow = true;
		} elseif (!empty($config['isrecursion'])) {
			//包含子栏目
			foreach ($config['classides'] as $val) {
				$_classconfig = Myqee::config('class/'.$val);
				$_sonclasses = explode('|',trim($_classconfig['sonclass'],'|'));
				if (in_array($classid,$_sonclasses,true)) {
					$canshow = true;
				}
			}
		}
		return $canshow;		
	}
}