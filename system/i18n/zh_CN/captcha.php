<?php

$lang = array
(
	'file_not_found' => '指定文件 %s 不存在。请在使用之前使用 file_exists() 方法确认文件是否存在。',
	'requires_GD2'   => '验证码（Captcha）库需要带 FreeType 的 GD2 支持。详情请看 http://php.net/gd_info 。',

	// 为 Captcha_Word_Driver 选择不同长度的字符
	// 注意：仅使用字母字符时
	'words' => array
	(
		'cd', 'tv', 'it', 'to', 'be', 'or',
		'sun', 'car', 'dog', 'bed', 'kid', 'egg',
		'bike', 'tree', 'bath', 'roof', 'road', 'hair',
		'hello', 'world', 'earth', 'beard', 'chess', 'water',
		'barber', 'bakery', 'banana', 'market', 'purple', 'writer',
		'america', 'release', 'playing', 'working', 'foreign', 'general',
		'aircraft', 'computer', 'laughter', 'alphabet', 'kangaroo', 'spelling',
		'architect', 'president', 'cockroach', 'encounter', 'terrorism', 'cylinders',
	),

	// 为 Captcha_Word_Driver 选择不同的谜语
	// 注意：仅使用字母字符时
	'riddles' => array
	(
		array('请问你是否讨厌垃圾留言（SPAM）吗？（是或否）', '是'),
		array('你是机器人吗？（是或否）', '否'),
		array('火是... （热的 还是 冷的）', '热'),
		array('秋季之后是什么季节？', '冬季'),
		array('今天是这周的哪一天?', strftime('%A')),
		array('现在是几月份？', strftime('%B')),
	),
);
