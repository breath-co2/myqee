<?php

/**
 * nodejs 执行文件默认路径
 *
 * 此功能在devassets等处理css时用到，通常不用改，除非你的node安装目录不是默认目录
 *
 * 留空则使用默认值：
 *   Window:
 *      程序路径 c:\Program Files\nodejs\node.exe
 *      模块路径 c:\Program Files\nodejs\node_modules\
 *   其它系统:
 *      程序路径 /usr/local/bin/node
 *      模块路径 /usr/local/lib/node_modules/
 *
 * @array
 */
$config['nodejs'] = array
(
    '',    // 执行脚本路径，留空则默认
    '',    // node_modules路径，留空则默认
);




