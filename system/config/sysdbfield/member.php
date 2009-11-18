<?php
$config['field'] = array(
	//ID
	'id' => array(
		'name'=>'ID',
		'set' => array(
			'iskey'=>true,
			'isonly'=>true,
			'isnonull'=>true,
			'istofile'=>false,
			'type'=>'int',
			'length'=>11,
			'inputtype'=>'hidden',
		),
		'listset' => array(
			'width' => 50,
			'title' => 'ID',
			'align' => 'center',
			'class' => 'td2',
		),
		'editset' => array(
			'type' => 'hidden',
		),
	),
	//USER NAME
	'username' => array(
		'name'=>'用户名',
		'set' => array(
			'isnonull'=>true,
			'type'=>'varchar',
			'length'=>255,
		),
		'listset' => array(
			'titlelink' => true,
		),
		'editset' => array(
			'title' => '用户名',
			'description' => '用户名只允许字母、数字、下划线的组合',
			'type' => 'input',
			'set' => array(
				'size' => 20,
				'class' => 'input',
			),
			'value' => '',
			'notempty' => true,
			'format' => 'string',
		),
	),
	//USER NICKNAME
	'nickname' => array(
		'name'=>'昵称',
		'set' => array(
			'type'=>'varchar',
			'length'=>255,
		),
		'listset' => array(
			'width' => 200,
			'title' => '昵称',
			'align' => 'center',
			'class' => 'td2',
		),
		'editset' => array(
			'title' => '昵称',
			'description' => '',
			'type' => 'input',
			'set' => array(
				'size' => 20,
				'class' => 'input',
			),
			'value' => '',
			'notempty' => true,
			'format' => 'string',
		),
	),
	//USER PASSWORD
	'password' => array(
		'name'=>'密码',
		'set' => array(
			'type'=>'varchar',
			'length'=>36,
		),
		'listset' => array(
			'width' => 200,
			'title' => '密码',
			'align' => 'center',
			'class' => 'td2',
		),
		'editset' => array(
			'title' => '密码',
			'description' => '',
			'type' => 'input',
			'set' => array(
				'size' => 10,
				'class' => 'input',
			),
			'value' => '',
			'notempty' => true,
			'format' => 'password',
		),
	),
	//USER SEX
	'sex' => array(
		'name'=>'性别',
		'set' => array(
			'type'=>'int',
			'length'=>1,
		),
		'listset' => array(
			'width' => 200,
			'title' => '性别',
			'align' => 'center',
			'class' => 'td2',
		),
		'editset' => array(
			'title' => '性别',
			'description' => '',
			'type' => 'radio',
			'set' => array(
				'size' => 20,
				'class' => '',
			),
			'value' => '',
			'notempty' => true,
			'format' => 'radio',
		),
	),
	//USER Marriage
	'marriage' => array(
		'name'=>'婚姻',
		'set' => array(
			'type'=>'select',
			'length'=>10,
		),
		'listset' => array(
			'width' => 100,
			'title' => '婚姻',
			'align' => 'center',
			'class' => 'td2',
		),
		'editset' => array(
			'title' => '婚姻',
			'description' => '',
			'type' => 'select',
			'set' => array(
				'size' => 10,
				'class' => '',
			),
			'value' => '',
			'format' => 'select',
		),
	),
	//USER AGE
	'age' => array(
		'name'=>'年龄',
		'set' => array(
			'type'=>'int',
			'length'=>10,
		),
		'listset' => array(
			'width' => 100,
			'title' => '年龄',
			'align' => 'center',
			'class' => 'td2',
		),
		'editset' => array(
			'title' => '年龄',
			'description' => '',
			'type' => 'input',
			'set' => array(
				'size' => 10,
				'class' => 'input',
			),
			'value' => '',
			'format' => 'int',
		),
	),
	//USER Birthday
	'birthday' => array(
		'name'=>'生日',
		'set' => array(
			'type'=>'int',
			'length'=>10,
		),
		'listset' => array(
			'width' => 100,
			'title' => '生日',
			'align' => 'center',
			'class' => 'td2',
		),
		'editset' => array(
			'title' => '生日',
			'description' => '',
			'type' => 'input',
			'set' => array(
				'size' => 10,
				'class' => 'input',
			),
			'value' => '',
			'format' => 'string',
		),
	),
	//USER Blood
	'blood' => array(
		'name'=>'血型',
		'set' => array(
			'type'=>'select',
			'length'=>10,
		),
		'listset' => array(
			'width' => 100,
			'title' => '血型',
			'align' => 'center',
			'class' => 'td2',
		),
		'editset' => array(
			'title' => '血型',
			'description' => '',
			'type' => 'select',
			'set' => array(
				'size' => 10,
				'class' => '',
			),
			'value' => '',
			'format' => 'select',
		),
	),
	//USER Livingarea
	'livingarea' => array(
		'name'=>'居住地',
		'set' => array(
			'type'=>'int',
			'length'=>10,
		),
		'listset' => array(
			'width' => 100,
			'title' => '居住地',
			'align' => 'center',
			'class' => 'td2',
		),
		'editset' => array(
			'title' => '居住地',
			'description' => '',
			'type' => 'input',
			'set' => array(
				'size' => 10,
				'class' => 'input',
			),
			'value' => '',
			'format' => 'string',
		),
	),
	//USER HOME
	'home' => array(
		'name'=>'家乡',
		'set' => array(
			'type'=>'int',
			'length'=>10,
		),
		'listset' => array(
			'width' => 100,
			'title' => '家乡',
			'align' => 'center',
			'class' => 'td2',
		),
		'editset' => array(
			'title' => '家乡',
			'description' => '',
			'type' => 'input',
			'set' => array(
				'size' => 10,
				'class' => 'input',
			),
			'value' => '',
			'format' => 'string',
		),
	),
	//USER GRADE
	'grade' => array(
		'name'=>'等级',
		'set' => array(
			'type'=>'select',
			'length'=>10,
		),
		'listset' => array(
			'width' => 100,
			'title' => '等级',
			'align' => 'center',
			'class' => 'td2',
		),
		'editset' => array(
			'title' => '等级',
			'description' => '',
			'type' => 'select',
			'set' => array(
				'size' => 20,
				'class' => 'select',
			),
			'value' => '',
			'format' => 'select',
		),
	),
	//USER PERMISSIONS
	'permissions' => array(
		'name'=>'权限',
		'set' => array(
			'type'=>'select',
			'length'=>10,
		),
		'listset' => array(
			'width' => 100,
			'title' => '权限',
			'align' => 'center',
			'class' => 'td2',
		),
		'editset' => array(
			'title' => '权限',
			'description' => '',
			'type' => 'select',
			'set' => array(
				'size' => 20,
				'class' => 'select',
			),
			'value' => '',
			'format' => 'select',
		),
	),
	//USER GAME_SCORE
	'game_score' => array(
		'name'=>'积分',
		'set' => array(
			'type'=>'int',
			'length'=>10,
		),
		'listset' => array(
			'width' => 100,
			'title' => '积分',
			'align' => 'center',
			'class' => 'td2',
		),
		'editset' => array(
			'title' => '积分',
			'description' => '',
			'type' => 'input',
			'set' => array(
				'size' => 10,
				'class' => 'input',
			),
			'value' => '',
			'format' => 'int',
		),
	),
	//USER EMAIL
	'email' => array(
		'name'=>'EMAIL',
		'set' => array(
			'type'=>'varchar',
			'length'=>200,
		),
		'listset' => array(
			'width' => 200,
			'title' => 'EMAIL',
			'align' => 'center',
			'class' => 'td2',
		),
		'editset' => array(
			'title' => 'EMAIL',
			'description' => '',
			'type' => 'input',
			'set' => array(
				'size' => 20,
				'class' => 'input',
			),
			'value' => '',
			'notempty' => true,
			'format' => 'email',
		),
	),
	//USER QQ
	'qq' => array(
		'name'=>'QQ',
		'set' => array(
			'type'=>'varchar',
			'length'=>10,
		),
		'listset' => array(
			'width' => 100,
			'title' => 'QQ',
			'align' => 'center',
			'class' => 'td2',
		),
		'editset' => array(
			'title' => 'QQ',
			'description' => '',
			'type' => 'input',
			'set' => array(
				'size' => 10,
				'class' => 'input',
			),
			'value' => '',
			'format' => 'int',
		),
	),
	//USER MSN
	'msn' => array(
		'name'=>'MSN',
		'set' => array(
			'type'=>'varchar',
			'length'=>200,
		),
		'listset' => array(
			'width' => 200,
			'title' => 'MSN',
			'align' => 'center',
			'class' => 'td2',
		),
		'editset' => array(
			'title' => 'MSN',
			'description' => '',
			'type' => 'input',
			'set' => array(
				'size' => 20,
				'class' => 'input',
			),
			'value' => '',
			'format' => 'string',
		),
	),
	//USER IP
	'ip' => array(
		'name'=>'IP',
		'set' => array(
			'type'=>'varchar',
			'length'=>100,
		),
		'listset' => array(
			'width' => 100,
			'title' => 'IP',
			'align' => 'center',
			'class' => 'td2',
		),
		'editset' => array(
			'title' => 'IP',
			'description' => '',
			'type' => 'input',
			'set' => array(
				'size' => 10,
				'class' => 'input',
			),
			'value' => '',
			'format' => 'string',
		),
	),
);

