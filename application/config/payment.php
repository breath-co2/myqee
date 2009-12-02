<?php
/**
 * @name 支付配置
 */

// 网银在线
$config['api']['chinabank'] = array(
	'mem_id'	=> 20948381,
	'code'	=> 'longlongago090419sadklhknsda',
	'log_file'	=> 'chinabank.log'
);

// 财付通中介支付
$configp['api']['tenpaymed'] = array(
	'mem_id'	=> 122323543,
	'code'	=> 'sdasdfwfsdfsd',
	'log_file'	=> 'tenpaymed.log'
);


//接口通知页面
$config['notify_url'] = 'http://www.farcore.org.cn/payment/test/notify_chinabank.php';

//返回页面
$config['return_url'] = 'http://www.farcore.org.cn/payment/test/return_chinabank.php';