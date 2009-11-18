<?php
class Task_Api_Core{
	//以下变量都会在执行API前传入。
	public $id;				//信息ID
	public $info;			//信息、数组
	public $oldinfo;		//旧信息、数组
	public $dbname;			//数据表名称
	public $dbconfig;		//数据表配置
	public $modelid;		//模型ID
	public $modelconfig;	//模型配置（若是数据表模型，此值将是数据表模型配置）
	public $class_id;		//栏目ID
	public $class_name;		//栏目名称
	public $myclass;		//栏目参数
	public $type;			//操作方式。有add,edit,del三种
//	public $_methodname = array('test'=>'测试');
//		
//	//操作API函数时只会传入一个参数，即信息的ID：$id
//	//下面这函数是一个测试
//	public function test($id=0){
//		
//	}
}