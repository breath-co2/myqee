<?php
$config['index']['example'] = array(
	'innerHTML' => '查看例子',
	array(
		'innerHTML' => '查看例子',
		'href' => 'demo/',
	),
	array(
		'innerHTML' => '使用帮助说明',
		'href' => 'demo/use',
	),
);

$config['test'] = array(
    'innerHTML' => '测试菜单',
    'perm' => 'testperm.test',	//权限
    array(
        'innerHTML' => '树形菜单一级目录',
        'href' => 'demo/test',
        array(
            'innerHTML' => '树形菜单二级目录',
            'href' => 'demo/test/menu1',
            array(
                 'innerHTML' => '树形菜单三级目录',
                'href' => 'demo/test3/',
            ),
        ),
        array(
            'innerHTML' => '树形菜单二级目录',	//显示内容，支持HTML
            'href' => 'demo/test/menu2',				//超链接
            'style' => 'color:red;',			//样式，当然你还可以加任意的tag
        ),
    ),
);
