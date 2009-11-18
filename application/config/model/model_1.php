<?php defined('MYQEEPATH') or die('No direct script access.');
//2009-11-13 14:34:28
//it is saved by myqee system,please don't edit it.

$config['dbname'] = 'default/news';
$config['database'] = 'news';
$config['tablename'] = 'news';
$config['adminlist'] = array (
  'sys_commend' => 
  array (
    'name' => '评论',
    'isuse' => 0,
    'class' => 'btns',
    'target' => '',
  ),
  'sys_view' => 
  array (
    'name' => '查看',
    'isuse' => 0,
    'class' => 'btns',
    'target' => '',
  ),
  'sys_edit' => 
  array (
    'name' => '修改',
    'isuse' => 1,
    'class' => 'btns',
    'target' => '',
  ),
  'sys_del' => 
  array (
    'name' => '删除',
    'isuse' => 1,
    'class' => 'btns',
    'target' => NULL,
  ),
);
$config['adminedit'] = array (
  'add' => NULL,
  'edit' => NULL,
  'del' => NULL,
);
$config['field'] = array (
  'id' => 
  array (
  ),
  'title' => 
  array (
    'dbname' => '标题123',
    'input' => true,
    'editor' => true,
    'view' => true,
    'notnull' => true,
    'caiji' => true,
    'search' => true,
    'list' => true,
    'content' => true,
  ),
  'isshow' => 
  array (
    'input' => true,
    'editor' => true,
    'caiji' => true,
    'list' => true,
    'content' => true,
  ),
  'image' => 
  array (
    'input' => true,
    'editor' => true,
    'view' => true,
    'caiji' => true,
    'list' => true,
    'content' => true,
  ),
  'abstract' => 
  array (
    'tag' => '高级',
    'input' => true,
    'editor' => true,
    'caiji' => true,
    'search' => true,
    'list' => true,
    'content' => true,
  ),
  'content' => 
  array (
    'input' => true,
    'editor' => true,
    'view' => true,
    'caiji' => true,
    'list' => true,
    'content' => true,
  ),
  'class_id' => 
  array (
  ),
  'class_name' => 
  array (
  ),
  'filepath' => 
  array (
    'view' => true,
    'list' => true,
    'content' => true,
  ),
  'filename' => 
  array (
    'view' => true,
    'list' => true,
    'content' => true,
  ),
  'iscommend' => 
  array (
    'input' => true,
    'editor' => true,
    'view' => true,
    'jiehe' => true,
    'list' => true,
    'content' => true,
  ),
  '#special' => 
  array (
    'dbname' => '所属专题',
  ),
  '#comment' => 
  array (
    'dbname' => '是否评论',
  ),
);
$config['field_set'] = array (
  'input' => 
  array (
    'title' => 'title',
    'isshow' => 'isshow',
    'image' => 'image',
    'abstract' => 'abstract',
    'content' => 'content',
    'iscommend' => 'iscommend',
  ),
  'editor' => 
  array (
    'title' => 'title',
    'isshow' => 'isshow',
    'image' => 'image',
    'abstract' => 'abstract',
    'content' => 'content',
    'iscommend' => 'iscommend',
  ),
  'notnull' => 
  array (
    'title' => 'title',
  ),
  'caiji' => 
  array (
    'title' => 'title',
    'isshow' => 'isshow',
    'image' => 'image',
    'abstract' => 'abstract',
    'content' => 'content',
  ),
  'search' => 
  array (
    'title' => 'title',
    'abstract' => 'abstract',
  ),
  'list' => 
  array (
    'title' => 'title',
    'isshow' => 'isshow',
    'image' => 'image',
    'abstract' => 'abstract',
    'content' => 'content',
    'filepath' => 'filepath',
    'filename' => 'filename',
    'iscommend' => 'iscommend',
  ),
  'content' => 
  array (
    'title' => 'title',
    'isshow' => 'isshow',
    'image' => 'image',
    'abstract' => 'abstract',
    'content' => 'content',
    'filepath' => 'filepath',
    'filename' => 'filename',
    'iscommend' => 'iscommend',
  ),
  'jiehe' => 
  array (
    'iscommend' => 'iscommend',
  ),
);
$config['dbset'] = array (
  'abstract' => 
  array (
    'type' => 'basehtmlarea',
    'set' => 
    array (
      'class' => '',
      'size' => '80',
      'rows' => '8',
      'other' => '',
    ),
    'default' => '',
    'candidate' => '',
    'format' => 'html',
  ),
);
$config['list'] = array (
);
$config['nolist'] = array (
);
