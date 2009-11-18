<?php
/**
 * 虚拟模型字段请以#开头
 * infohook 方法的参数是 $pid(主键ID),$upfield(post过滤后的数据),$dbconfig(数据库配置),$isadd(是否添加)
 * modelhook 方法的参数是$model_set(模型配置),$info(信息) 都是以引用方式传递
 */
$config['#special'] = array(
	'dbname'=>'虚拟专题',
	'infohook' => '_dealSpecial',
	'modelhook' => '_dealSpecial',
);