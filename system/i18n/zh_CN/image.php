<?php

$lang = array
(
	'getimagesize_missing'    => '图像库需求 getimagesize() PHP 函数且在 PHP 配置文件没有激活。',
	'unsupported_method'      => '配置驱动不支持 %s 图片转变。',
	'file_not_found'          => '指定图片 %s 没有发现。在使用之前请使用 file_exists() 确认文件是否存在。',
	'type_not_allowed'        => '指定图片 %s 为不允许图片类型。',
	'invalid_width'           => '无效的宽度，%s。',
	'invalid_height'          => '无效的高度，%s。',
	'invalid_dimensions'      => '无效的 dimensions，%s。',
	'invalid_master'          => '无效的 master dimension。',
	'invalid_flip'            => '无效的 flip direction。',
	'directory_unwritable'    => '指定的目录（文件夹）不可写，%s。',

	// ImageMagick 信息
	'imagemagick' => array
	(
		'not_found' => '指定的 ImageMagick 目录不包含在程序中，%s。',
	),
	
	// GraphicsMagick 信息
	'graphicsmagick' => array
	(
		'not_found' => '指定的 GraphicsMagick 目录不包含在程序中，%s。',
	),
	
	// GD 信息
	'gd' => array
	(
		'requires_v2' => '图片库需求 GD2。详情请看 http://php.net/gd_info 。',
	),
);
