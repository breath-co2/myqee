<?php defined('MYQEEPATH') OR die('No direct access allowed.');

$lang = array
(
	// 类错误
	'invalid_rule'  => '无效的规则：%s',
	'i18n_array'    => 'i18n 键 %s 必须遵循 in_lang 规则且为数组形式',
	'not_callable'  => '校验（Validation）的回调 %s 不可调用',

	// 通常错误
	'unknown_error' => '验证字段 %s 时，发生未知错误。',
	'required'      => '字段 %s 必填。',
	'min_length'    => '字段 %s 最少 %d 字符。',
	'max_length'    => '字段 %s 最多 %d 字符。',
	'exact_length'  => '字段 %s 必须包含 %d 字符。',
	'in_array'      => '字段 %s 必须选中下拉列表的选项。',
	'matches'       => '字段 %s 必须与 %s 字段一致。',
	'valid_url'     => '字段 %s 必须包含有效的 URL。',
	'valid_email'   => '字段 %s 无效 Email 地址格式。',
	'valid_ip'      => '字段 %s 无效 IP 地址。',
	'valid_type'    => '字段 %s 只可以包含 %s 字符。',
	'range'         => '字段 %s 越界指定范围。',
	'regex'         => '字段 %s 与给定输入模式不匹配。',
	'depends_on'    => '字段 %s 依赖于 %s 栏位。',

	// 上传错误
	'user_aborted'  => '文件 %s 上传过程中被中断。',
	'invalid_type'  => '文件 %s 非法文件格式。',
	'max_size'      => '文件 %s 超出最大允许范围. 最大文件大小 %s。',
	'max_width'     => '文件 %s 的最大允许宽度 %s 是 %spx。',
	'max_height'    => '文件 %s 的最大允许高度 %s 是 %spx。',
	'min_width'     => '文件 %s 太小，最小文件宽度大小 %spx。',
	'min_height'    => '文件 %s 太小，最小文件高度大小 %spx。',

	// 字段类型
	'alpha'         => '字母',
	'alpha_numeric' => '字母和数字',
	'alpha_dash'    => '字母，破折号和下划线',
	'digit'         => '数字',
	'numeric'       => '数字',
);
