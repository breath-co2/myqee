<?php defined('MYQEEPATH') or die('No direct script access.');
//2009-12-30 20:14:02
//it is saved by myqee system,please don't edit it.

$config['title'] = '首页头条';
$config['isuse'] = 1;
$config['myorder'] = 0;
$config['type'] = 'index';
$config['no'] = 3;
$config['len'] = 0;
$config['varname'] = 'data';
$config['cache_time'] = 0;
$config['tpl_id'] = 0;
$config['tpl_engie'] = '';
$config['mydata_id'] = 0;
$config['template'] = '<h1><a href="{$data.0.link}" target="_blank">{$data.0.title}</a></h1>
<tt>
<!--{loop $data.0.other as $item}-->
[<a href="{$item.link}" target="_blank">{$item.title}</a>]
<!--{/loop}-->
</tt>';
$config['advfield'] = 'a:4:{s:2:"_g";a:8:{s:4:"flag";N;s:4:"name";N;s:4:"type";s:1:"2";s:3:"num";s:1:"1";s:9:"editwidth";s:2:"50";s:5:"isadd";i:0;s:5:"isdel";i:0;s:7:"isorder";i:0;}s:5:"title";a:8:{s:4:"flag";s:5:"title";s:4:"name";s:9:"大标题";s:4:"type";s:5:"input";s:6:"format";s:0:"";s:9:"editwidth";s:2:"80";s:3:"set";a:4:{s:4:"size";s:2:"40";s:4:"rows";N;s:5:"class";s:5:"input";s:5:"other";s:0:"";}s:7:"default";s:0:"";s:9:"candidate";s:0:"";}s:4:"link";a:8:{s:4:"flag";s:4:"link";s:4:"name";s:15:"大标题链接";s:4:"type";s:5:"input";s:6:"format";s:0:"";s:9:"editwidth";N;s:3:"set";a:4:{s:4:"size";s:2:"60";s:4:"rows";N;s:5:"class";s:5:"input";s:5:"other";s:0:"";}s:7:"default";s:0:"";s:9:"candidate";s:0:"";}s:5:"other";a:3:{s:2:"_g";a:8:{s:4:"flag";s:5:"other";s:4:"name";s:9:"副链接";s:4:"type";s:1:"1";s:3:"num";s:1:"0";s:9:"editwidth";N;s:5:"isadd";i:1;s:5:"isdel";i:1;s:7:"isorder";i:1;}s:5:"title";a:8:{s:4:"flag";s:5:"title";s:4:"name";s:12:"链接文字";s:4:"type";s:5:"input";s:6:"format";s:0:"";s:9:"editwidth";N;s:3:"set";a:4:{s:4:"size";s:2:"20";s:4:"rows";N;s:5:"class";s:5:"input";s:5:"other";s:0:"";}s:7:"default";s:0:"";s:9:"candidate";s:0:"";}s:4:"link";a:8:{s:4:"flag";s:4:"link";s:4:"name";s:12:"链接地址";s:4:"type";s:5:"input";s:6:"format";s:0:"";s:9:"editwidth";N;s:3:"set";a:4:{s:4:"size";s:2:"40";s:4:"rows";N;s:5:"class";s:5:"input";s:5:"other";s:0:"";}s:7:"default";s:0:"";s:9:"candidate";s:0:"";}}}';
