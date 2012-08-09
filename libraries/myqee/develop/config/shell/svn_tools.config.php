<?php
$config = array(
	'verbose' => false,        //冗余模式
	'path' => '/',             //svn目录，通常/即可
    'trunk-dir' => false,      //当前trunk目录
	'trunk-url' => false,      //正式版本陆军，例如：http://myqeecms.googlecode.com/svn/trunk
	'working-url' => false,    //工作路径，例如：http://myqeecms.googlecode.com/svn/branches/working

    #TODO 暂不支持
//    'svn_log_top_merged_pattern' => '#^merge\s+working\((\d+)\)\s*->\s*trunk$#',    //已合并到最高版本日志正则

//    'svn_log_merged_pattern' => '#^merge\s+working:(\d+):(\d+)\s*->\s*trunk$#',     //合并版本日志格式
);