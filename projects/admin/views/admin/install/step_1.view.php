<h3>环境检查</h3>
<table class="mainTable">
<tbody align="center">
<tr>
    <th>项目</th>
    <th>MyQEE 所需配置</th>
    <th>MyQEE 推荐</th>
    <th>当前服务器</th>
</tr>
<tr>
<td class="td1">操作系统</td>
<td>不限制</td>
<td>类Unix</td>
<td><?php
$supUname = function_exists('php_uname');
if ( $supUname )
{
	$os = explode(" ", php_uname());
	echo $os[0];
}else{
	echo '未知，' . (IS_WIN?'window平台':'非window平台');
}
?></td>
</tr>
<tr>
<td class="td1">PHP 版本</td>
<td>5.2.1</td>
<td>5.3.3</td>
<td><?php echo PHP_VERSION;?></td>
</tr>
<tr>
<td class="td1">数据库支持</td>
<td>MySQL或MYSQLI</td>
<td>MySQLI</td>
<td>
<?php
$supMySQL = function_exists('mysql_close');
$supMySQLI = function_exists('mysqli_close');
if( $supMySQL || $supMySQLI ){
	echo '<font color="#5fd300"><strong>√</strong></font>&nbsp;支持:';
	if ( $supMySQL ) {
		echo 'MySQL';
	}
	if ( $supMySQL && $supMySQLI ) {
		echo ',';
	}
	if ( $supMySQLI ) {
		echo 'MySQLI';
	}
}else{
	echo '<font color="red"><strong>×</strong></font>&nbsp;不支持';
}
?>
</td>
</tr>
</tbody>
</table>

<h3>目录权限检查</h3>
<table class="mainTable">
<tbody align="center">
<tr>
    <th>目录文件</th>
    <th>所需状态</th>
    <th>当前状态</th>
</tr>
<?php
$all_dir = array(
    DIR_DATA => 1,
    DIR_DATA . Core::$project . '/install.lock' => 0,
    DIR_PROJECT . Core::$project . '/config/database.config'.EXT => 2,
);

foreach ( $all_dir as $dir=>$type )
{
    echo '<tr>';
    echo '<td>'.Core::debug_path($dir).'</td>';
    echo '<td>';
    if ($type==1)
    {
        echo '可写';
    }
    elseif ($type==0)
    {
        echo '不存在,且可写';
    }
    elseif ($type==2)
    {
        echo '可写或手动修改';
    }

    echo '</td>';
    echo '<td>';
    if (is_dir($dir) || is_file($dir))
    {
        if ( is_writable($dir) )
        {
            echo '<font color="#5fd300"><strong>√</strong></font>可写';
        }
        else
        {
            echo '<font color="red"><strong>×</strong></font>不可写';
        }
    }
    else
    {
        echo '<font color="#5fd300"><strong>√</strong></font>不存在';
        if ($type==0)
        {
            if ( is_writable($dir) )
            {
                echo '&nbsp; <font color="#5fd300"><strong>√</strong></font>可写　';
            }
            else
            {
                echo '&nbsp; <font color="red"><strong>×</strong></font>不可写';
            }
        }
    }
    echo '</td>';
    echo '</tr>';
}
?>
<tr>
<td colspan="3" class="td1">
注：安全起见，推荐DATA目录可写入，其它文件夹推荐不可写。若系统不具备相应文件操作权限，在安装过程中系统会提示你手工操作办法。
</td>
</tr>
</tbody>
</table>
<div style="padding:20px 0 10px 0;text-align:center;">
<input type="button" value="上一步" onclick="document.location='<?php echo Core::url('install/');?>';" />
<input type="button" class="submit" value="下一步" onclick="document.location='<?php echo Core::url('install/step_2/');?>';" />
</div>
