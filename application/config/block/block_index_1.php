<?php defined('MYQEEPATH') or die('No direct script access.');
//2009-11-13 14:39:14
//it is saved by myqee system,please don't edit it.

$config['title'] = '首页下载';
$config['isuse'] = 1;
$config['myorder'] = 0;
$config['type'] = 'index';
$config['no'] = 1;
$config['len'] = 0;
$config['varname'] = 'data';
$config['cache_time'] = 0;
$config['tpl_id'] = 0;
$config['tpl_engie'] = '';
$config['mydata_id'] = 0;
$config['template'] = '<span>{$data.0.time}</span>
<h2>{$data.0.v}</h2>
<div class="address">
<!--{$i=0}-->
<!--{$c=count($data.0.down)}-->
<!--{loop $data.0.down as $item}-->
<!--{$i++}-->
<a href="{$item.address}">{$item.title}</a>
<!--{if $i==3}--><br/><!--{/if}-->
<!--{if $i!=3&&$i!=$c}--> | <!--{/if}-->
<!--{/loop}-->
</div>';
$config['advfield'] = 'a:5:{s:2:"_g";a:8:{s:4:"flag";N;s:4:"name";N;s:4:"type";s:1:"2";s:3:"num";s:1:"1";s:9:"editwidth";N;s:5:"isadd";i:0;s:5:"isdel";i:0;s:7:"isorder";i:0;}s:1:"v";a:8:{s:4:"flag";s:1:"v";s:4:"name";s:12:"版本名称";s:4:"type";s:5:"input";s:6:"format";s:0:"";s:9:"editwidth";N;s:3:"set";a:4:{s:4:"size";s:2:"30";s:4:"rows";N;s:5:"class";s:0:"";s:5:"other";s:0:"";}s:7:"default";s:0:"";s:9:"candidate";s:0:"";}s:4:"time";a:8:{s:4:"flag";s:4:"time";s:4:"name";s:12:"更新时间";s:4:"type";s:4:"date";s:6:"format";s:0:"";s:9:"editwidth";N;s:3:"set";a:4:{s:4:"size";N;s:4:"rows";N;s:5:"class";s:0:"";s:5:"other";s:0:"";}s:7:"default";s:0:"";s:9:"candidate";s:0:"";}s:4:"test";a:8:{s:4:"flag";s:4:"test";s:4:"name";s:6:"测试";s:4:"type";s:8:"htmlarea";s:6:"format";s:0:"";s:9:"editwidth";N;s:3:"set";a:4:{s:4:"size";N;s:4:"rows";N;s:5:"class";s:0:"";s:5:"other";s:0:"";}s:7:"default";s:0:"";s:9:"candidate";s:0:"";}s:4:"down";a:4:{s:2:"_g";a:8:{s:4:"flag";s:4:"down";s:4:"name";s:12:"下载地址";s:4:"type";s:1:"0";s:3:"num";s:1:"6";s:9:"editwidth";N;s:5:"isadd";i:1;s:5:"isdel";i:1;s:7:"isorder";i:1;}s:7:"address";a:8:{s:4:"flag";s:7:"address";s:4:"name";s:6:"地址";s:4:"type";s:5:"input";s:6:"format";s:0:"";s:9:"editwidth";N;s:3:"set";a:4:{s:4:"size";s:2:"25";s:4:"rows";N;s:5:"class";s:0:"";s:5:"other";s:0:"";}s:7:"default";s:0:"";s:9:"candidate";s:0:"";}s:5:"title";a:8:{s:4:"flag";s:5:"title";s:4:"name";s:6:"显示";s:4:"type";s:5:"input";s:6:"format";s:0:"";s:9:"editwidth";N;s:3:"set";a:4:{s:4:"size";s:2:"16";s:4:"rows";N;s:5:"class";s:0:"";s:5:"other";s:0:"";}s:7:"default";s:0:"";s:9:"candidate";s:0:"";}s:3:"sss";a:8:{s:4:"flag";s:3:"sss";s:4:"name";s:9:"撒旦法";s:4:"type";s:5:"input";s:6:"format";s:0:"";s:9:"editwidth";N;s:3:"set";a:4:{s:4:"size";N;s:4:"rows";N;s:5:"class";s:0:"";s:5:"other";s:0:"";}s:7:"default";s:0:"";s:9:"candidate";s:0:"";}}}';
