<?php defined('MYQEEPATH') or die('No direct script access.');

$lang = array
(
	'error' => array(
		'nofoundmydata' => '不存在指定ID的数据调用！',
		'parameterserror' => '参数错误！',
		'saveerror' => '保存失败，可能没有操作权限！',
		'noacquname' => '请输入任务名称！',
		'errorsql' => '错误的SQL语句，请检查！',
		'nofoundclass' => '不存在指定的栏目！',
		'nofoundmodel' => '不存在指定模型！',
		'noorderinfo' => '缺少排序信息！',
		'inputdataempty' => '导入文件为空，请返回重新操作！',
		'inputreadfileerror' => '上传文件读取失败，请联系管理人员！',
		'inputerrorsize' => '上传的文件太大或上传失败，本系统只解析5MB以内大小模板！',
		'inputtplbyedit' => '待导入内容遭受修改或受损，系统已终止导入，请返回！',
		'decodeerror' => '解析模板失败，可能密码错误或导入的文件错误！',
	),
	
	'info' => array(
		'delsuccess' => '成功删除%s信息！',
		'editmyorderok' => '恭喜，成功修改%s条信息的排序！',
		'saveok' => '恭喜，保存成功！',
		'savenone' => '未保存任何数据！',
		'noouttemplate' => '没有符合条件的模板文件！',
		'nooutdb' => '没有符合条件的数据表！',
		'inputok' => '成功导入%s个模型！',
	),
);