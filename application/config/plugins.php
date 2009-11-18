<?php defined('MYQEEPATH') or die('No direct script access.');
//2009-11-10 16:36:48
//it is saved by myqee system,please don't edit it.

$config['ckfinder'] = array (
  'name' => 'ckfinder',
  'isuse' => true,
  'detailconfig' => 
  array (
    'name' => 'CKFinder',
  ),
);
$config['link'] = array (
  'name' => 'link',
  'isuse' => false,
  'detailconfig' => 
  array (
    'name' => '用户管理',
  ),
);
$config['comment'] = array (
  'name' => 'comment',
  'isuse' => true,
  'detailconfig' => 
  array (
    'name' => 'Comment',
    'model' => 
    array (
      'virtualfield' => 
      array (
        0 => 
        array (
          'title' => '是否评论',
          'type' => 'radio',
          'candidate' => 
          array (
            1 => '是',
            0 => '否',
          ),
        ),
      ),
    ),
  ),
);
