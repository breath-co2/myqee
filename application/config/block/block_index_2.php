<?php defined('MYQEEPATH') or die('No direct script access.');
//2009-11-05 13:21:37
//it is saved by myqee system,please don't edit it.

$config['title'] = '首页今日推荐';
$config['isuse'] = 1;
$config['myorder'] = 0;
$config['type'] = 'index';
$config['no'] = 2;
$config['len'] = 0;
$config['varname'] = 'data';
$config['cache_time'] = 0;
$config['tpl_id'] = 0;
$config['tpl_engie'] = '';
$config['mydata_id'] = 0;
$config['template'] = '<!--{loop $data as $item}-->
<div style="height:105px">
<a href="{$item.URL}" target="{$item.target}">{$item.title}</a>
<div style="padding:10px 0;width:90px;float:left;height:60px;"><a href="{$item.URL}" target="{$item.target}"><img src="{$item.image}" style="width:90px;height:60px;border:1px solid #ccc;" /></a></div>
<div style="padding:10px 0;float:right;width:125px;height:60px;overflow:hidden;line-height:1.7em;">{$item.description} &nbsp;<a href="{$item.URL}" target="{$item.target}" style="color:#bc2d09;font-weight:bold;">详细&raquo;</a></div><br />
</div>
<!--{/loop}-->';
$config['advfield'] = 'a:2:{s:2:"_g";a:8:{s:4:"flag";N;s:4:"name";N;s:4:"type";s:5:"input";s:3:"num";s:1:"0";s:9:"editwidth";N;s:5:"isadd";i:1;s:5:"isdel";i:1;s:7:"isorder";i:1;}s:6:"sdfsdf";a:8:{s:4:"flag";s:6:"sdfsdf";s:4:"name";s:6:"sdfsdf";s:4:"type";s:6:"select";s:6:"format";s:3:"int";s:9:"editwidth";N;s:3:"set";a:4:{s:4:"size";N;s:4:"rows";N;s:5:"class";s:0:"";s:5:"other";s:0:"";}s:7:"default";s:0:"";s:9:"candidate";s:0:"";}}';
