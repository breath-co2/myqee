<?php defined('MYQEEPATH') or die('No direct script access.');


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
	//标题
	'title' => array(
		'name'=>'标题',
		'set' => array(
			'isnonull'=>true,
			'type'=>'varchar',
			'length'=>255,
		),
		'listset' => array(
			'titlelink' => true,
		),
		'editset' => array(
			'title' => '标题',
			'description' => '请输入信息标题',
			'type' => 'input',
			'set' => array(
				'size' => 45,
				'class' => 'input',
			),
			'value' => '',
			'notempty' => true,
			'format' => 'string',
		),
	),
	//标题图片
	'imagenews' => array(
		'name'=>'标题图片',
		'set' => array(
			'type'=>'varchar',
			'length'=>255,
		),
		'editset' => array(
			'title' => '标题图片',
			'type' => 'imginput',
			'set' => array(
				'size'=>30,
				'class'=>'input',
			),
		),
	),
	//外部链接
	'linkurl' => array(
		'name'=>'外部链接',
		'set' => array(
			'type'=>'varchar',
			'length'=>500,
		),
		'editset' => array(
			'title' => '外部链接',
			'type' => 'input',
			'set' => array(
				'size'=>45,
				'class'=>'input',
			),
		),
	),
	//栏目ID
	'class_id' => array(
		'name'=>'栏目ID',
		'set' => array(
			'type'=>'int',
			'length'=>11,
			'iskey'=>true,
		),
		'editset' => array(
			'title' => '栏目',
			'description' => '请选择栏目',
			'type' => 'select',
		),
	),
	'class_name' => array(
		'name'=>'栏目分类',
		'set' => array(
			'type'=>'varchar',
			'length'=>255,
		),
		'editset' => array(
			'type' => 'hidden',
		),
	),
	//内容正文
	'content' => array(
		'name'=>'文章正文(存文本)',
		'set' => array(
			'istofile'=>true,
			'type'=>'text',
		),
		'editset' => array(
			'title' => '文章正文',
			'type' => 'pagehtmlarea',
			'set' => array(
				'rows'=>22,
			),
			'format' => 'html',
		),
	),
	'contentdb' => array(
		'name'=>'文章正文(单页，存数据库)',
		'set' => array(
			'type'=>'text',
		),
		'editset' => array(
			'title' => '文章正文',
			'type' => 'htmlarea',
			'set' => array(
				'rows'=>22,
			),
			'format' => 'html',
		),
	),
	'contentdb_page' => array(
		'name'=>'文章正文(多页，存数据库)',
		'set' => array(
			'type'=>'text',
		),
		'editset' => array(
			'title' => '文章正文',
			'type' => 'pagehtmlarea',
			'set' => array(
				'rows'=>22,
			),
			'format' => 'html',
		),
	),
	//摘要
	'abstract' => array(
		'name'=>'摘要',
		'set' => array(
			'type'=>'text',
		),
		'editset' => array(
			'title' => '摘要',
			'type' => 'textarea',
			'set' => array(
				'cols'=>80,
				'rows'=>8,
				'class'=>'input',
			),
			'format' => 'html',
		),
	),
	//标题样式
	'title_style' => array(
		'name'=>'标题样式',
		'set' => array(
			'type'=>'varchar',
			'length'=>255,
		),
		'editset' => array(
			'title' => '标题样式',
			'description' => '可输入标题系统样式',
			'type' => 'input',
			'set' => array(
				'size' => 30,
				'class' => 'input',
			),
			'format' => 'alt',
			'other' => 'test',
		),
	),
	//发布时间
	'createtime' => array(
		'name'=>'创建时间(创建后不修改)',
		'set' => array(
			'type'=>'int',
			'length'=>11,
			'iskey'=>true,
		),
		'editset' => array(
			'title' => '创建时间',
			'type' => 'hidden',
			'set' => array(
				'class' => 'input',
			),
			'format' => 'time',
		),
	),
	//发布时间
	'posttime' => array(
		'name'=>'提交时间(带时分)',
		'set' => array(
			'type'=>'int',
			'length'=>11,
			'iskey'=>true,
		),
		'editset' => array(
			'title' => '提交时间',
			'type' => 'time',
			'set' => array(
				'class' => 'input',
			),
			'format' => 'time',
		),
	),
	//发布时间2
	'posttime2' => array(
		'name'=>'提交日期(无时分)',
		'set' => array(
			'type'=>'int',
			'length'=>11,
		),
		'editset' => array(
			'title' => '提交日期',
			'type' => 'date',
			'set' => array(
				'class' => 'input',
			),
			'format' => 'time',
		),
	),
	//作者
	'writer' => array(
		'name'=>'作者',
		'set' => array(
			'type'=>'varchar',
			'length'=>255,
		),
		'editset' => array(
			'title' => '作者',
			'type' => 'input',
			'set' => array(
				'class' => 'input',
				'size'=>20,
			),
			'format' => 'string',
		),
	),
	//信息审核
	'isshow' => array(
		'name'=>'信息审核',
		'set' => array(
			'type'=>'tinyint',
			'length'=>1,
			'iskey'=>true,
		),
		'editset' => array(
			'title' => '信息审核',
			'type' => 'radio',
			'set' => array(
				'size'=>1,
			),
			'candidate' => "0|未审核\n1|已通过\n-1|不通过",
			'default' => '1',
			'format' => 'int',
		),
	),
	//是否头条
	'isheadlines' => array(
		'name'=>'是否头条',
		'set' => array(
			'type'=>'tinyint',
			'length'=>1,
			'iskey'=>true,
		),
		'editset' => array(
			'title' => '头条',
			'type' => 'radio',
			'set' => array(
				'size'=>1,
			),
			'candidate' => "0|否\n1|是",
			'default' => '0',
			'format' => 'int',
		),
	),
	//是否置顶
	'ontop' => array(
		'name'=>'是否置顶',
		'set' => array(
			'type'=>'tinyint',
			'length'=>1,
			'iskey'=>true,
		),
		'editset' => array(
			'title' => '置顶',
			'type' => 'select',
			'set' => array(
				'size'=>1,
			),
			'candidate' => "0|不置顶\n1|1级置顶\n2|2级置顶\n3|3级置顶\n4|4级置顶\n5|5级置顶\n6|6级置顶\n7|7级置顶\n8|8级置顶\n9|9级置顶",
			'default' => '0',
			'format' => 'int',
		),
	),
	//首页显示
	'is_indexshow' => array(
		'name'=>'首页显示',
		'set' => array(
			'type'=>'tinyint',
			'length'=>1,
			'iskey'=>true,
		),
		'editset' => array(
			'title' => '首页显示',
			'type' => 'radio',
			'set' => array(
				'size'=>1,
			),
			'candidate' => "1|是\n0|否",
			'default' => '0',
			'format' => 'int',
		),
	),
	//首页热门
	'is_hot' => array(
		'name'=>'热门',
		'set' => array(
			'type'=>'tinyint',
			'length'=>1,
			'iskey'=>true,
		),
		'editset' => array(
			'title' => '热门',
			'type' => 'radio',
			'set' => array(
				'size'=>1,
			),
			'candidate' => "0|否\n1|是",
			'default' => '0',
			'format' => 'int',
		),
	),
	//是否推荐
	'iscommend' => array(
		'name'=>'推荐',
		'set' => array(
			'type'=>'tinyint',
			'length'=>1,
			'iskey'=>true,
		),
		'editset' => array(
			'title' => '推荐',
			'type' => 'select',
			'set' => array(
				'size'=>1,
			),
			'candidate' => "0|不推荐\n1|1级推荐\n2|2级推荐\n3|3级推荐\n4|4级推荐\n5|5级推荐\n6|6级推荐\n7|7级推荐\n8|8级推荐\n9|9级推荐",
			'default' => '0',
			'format' => 'int',
		),
	),
	//评论数
	'comments' => array(
		'name'=>'评论数',
		'set' => array(
			'type'=>'int',
			'length'=>10,
			'iskey'=>true,
		),
		'editset' => array(
			'title' => '评论数',
			'type' => 'input',
			'set' => array(
				'size'=>10,
				'class'=>'input',
			),
			'candidate' => "0",
			'default' => '0',
			'format' => 'int',
		),
	),
	//是否关闭评论
	'isclose_comments' => array(
		'name'=>'是否关闭评论',
		'set' => array(
			'type'=>'tinyint',
			'length'=>1,
		),
		'editset' => array(
			'title' => '是否关闭评论',
			'type' => 'radio',
			'set' => array(
				'size'=>1,
			),
			'candidate' => "0|否\n1|是",
			'default' => '0',
			'format' => 'int',
		),
	),
	//文件存放路径
	'filepath' => array(
		'name'=>'文件存放路径',
		'set' => array(
			'type'=>'varchar',
			'length'=>255,
		),
		'editset' => array(
			'title' => '保存路径',
			'type' => 'input',
			'set' => array(
				'size'=>30,
				'class'=>'input',
			),
			'format' => "filepath",
		),
	),
	//文件名
	'filename' => array(
		'name'=>'文件名',
		'set' => array(
			'type'=>'varchar',
			'length'=>255,
		),
		'editset' => array(
			'title' => '文件名',
			'type' => 'input',
			'set' => array(
				'size'=>16,
				'class'=>'input',
			),
			'format' => "filename",
		),
	),
	//模板ID
	'template_id' => array(
		'name'=>'模板ID',
		'set' => array(
			'type'=>'int',
			'length'=>10,
		),
		'editset' => array(
			'title' => '内容模板',
			'type' => 'select',
			'set' => array(
				'size'=>1,
			),
			'format' => 'int',
		),
	),
	//关键词
	'keyword' => array(
		'name'=>'关键词',
		'set' => array(
			'type'=>'varchar',
			'length'=>2000,
			'iskey'=>true,
		),
		'editset' => array(
			'title' => '关键词',
			'type' => 'input',
			'set' => array(
				'size'=>60,
				'class'=>'input',
			),
			'format' => '',
		),
	),
	//TAG标签
	'tag' => array(
		'name'=>'Tag标签',
		'set' => array(
			'type'=>'varchar',
			'length'=>255,
			'iskey'=>true,
		),
		'editset' => array(
			'title' => 'Tag标签',
			'type' => 'input',
			'set' => array(
				'size'=>30,
				'class'=>'input',
			),
			'format' => 'int',
		),
	),
	//来源
	'from' => array(
		'name'=>'来源',
		'set' => array(
			'type'=>'varchar',
			'length'=>255,
		),
		'editset' => array(
			'title' => '来源',
			'type' => 'input',
			'set' => array(
				'size'=>30,
				'class'=>'input',
			),
			'format' => 'string',
		),
	),
	//统计
	'hits' => array(
		'name'=>'访问统计',
		'set' => array(
			'type'=>'int',
			'length'=>10,
			'iskey'=>true,
		),
		'editset' => array(
			'title' => '总访问数',
			'type' => 'input',
			'set' => array(
				'size'=>12,
				'class'=>'input',
			),
			'default' => '0',
			'format' => 'int',
		),
	),
	//今日访问
	'hits_today' => array(
		'name'=>'今日访问',
		'set' => array(
			'type'=>'decimal',
			'length'=>'20,10',
			'iskey'=>true,
		),
		'editset' => array(
			'title' => '今日访问',
			'type' => 'input',
			'set' => array(
				'size'=>12,
				'class'=>'input',
			),
			'default' => '0',
			'format' => 'int',
		),
	),
	//昨日访问
	'hits_yesterday' => array(
		'name'=>'昨日访问',
		'set' => array(
			'type'=>'decimal',
			'length'=>'20,10',
			'iskey'=>true,
		),
		'editset' => array(
			'title' => '昨日访问',
			'type' => 'input',
			'set' => array(
				'size'=>12,
				'class'=>'input',
			),
			'default' => '0',
			'format' => 'int',
		),
	),
	//本周访问
	'hits_thisweek' => array(
		'name'=>'本周访问',
		'set' => array(
			'type'=>'decimal',
			'length'=>'20,10',
			'iskey'=>true,
		),
		'editset' => array(
			'title' => '本周访问',
			'type' => 'input',
			'set' => array(
				'size'=>12,
				'class'=>'input',
			),
			'default' => '0',
			'format' => 'int',
		),
	),
	//上周访问
	'hits_lastweek' => array(
		'name'=>'上周访问',
		'set' => array(
			'type'=>'decimal',
			'length'=>'20,10',
			'iskey'=>true,
		),
		'editset' => array(
			'title' => '上周访问',
			'type' => 'input',
			'set' => array(
				'size'=>12,
				'class'=>'input',
			),
			'default' => '0',
			'format' => 'int',
		),
	),
	//本月访问
	'hits_thismonth' => array(
		'name'=>'本月访问',
		'set' => array(
			'type'=>'decimal',
			'length'=>'20,10',
			'iskey'=>true,
		),
		'editset' => array(
			'title' => '本月访问',
			'type' => 'input',
			'set' => array(
				'size'=>12,
				'class'=>'input',
			),
			'default' => '0',
			'format' => 'int',
		),
	),
	//上月访问
	'hits_lastmonth' => array(
		'name'=>'上月访问',
		'set' => array(
			'type'=>'decimal',
			'length'=>'20,10',
			'iskey'=>true,
		),
		'editset' => array(
			'title' => '上月访问',
			'type' => 'input',
			'set' => array(
				'size'=>12,
				'class'=>'input',
			),
			'default' => '0',
			'format' => 'int',
		),
	),
	
	//下载次数
	'hits_down' => array(
		'name'=>'下载次数',
		'set' => array(
			'type'=>'int',
			'length'=>10,
			'iskey'=>true,
		),
		'editset' => array(
			'title' => '下载次数',
			'type' => 'input',
			'set' => array(
				'size'=>6,
				'class'=>'input',
			),
			'default' => '0',
			'format' => 'int',
		),
	),
);