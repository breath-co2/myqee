<br />
<?php
if ($config_str)
{
    $config_file = DIR_PROJECT . Core::$project . '/config/database.config'.EXT;
    if ( is_file($config_file) )
    {
        $is_writable = is_writable($config_file);
    }
    else
    {
        $is_writable = true;
    }
?>
<table class="mainTable" style="margin-top:2px;">
<tr>
<th colspan="2">输出配置文件</th>
</tr>
<tr>
    <td align="right" class="td1" width="140">文件路径：</td>
    <td><font color="green"><?php echo DIR_PROJECT;?></font><font color="blue"><?php echo Core::$project.'/config/database.config'.EXT;?></font></td>
</tr>
<tr>
    <td align="right" class="td1">文件内容：</td>
    <td><div style="white-space:pre;font-family:Consolas"><?php echo htmlspecialchars($config_str);?></div></td>
</tr>
<?php
if (!$is_writable)
{
?>
<tbody class="errTable">
<tr>
    <td align="right" class="td1"></td>
    <td>
    文件不可写，请手动保存配置文件，保存完毕后点击下一步。
    </td>
</tr>
</tbody>
<?php
}
?>
<?php
if ($message)
{
?>
<tbody class="errTable">
<tr>
<td width="25%"></td>
<td><?php echo $message;?></td>
</tr>
</tbody>
<?php
}
?>
</table>
<div style="padding:20px 0 10px 0;text-align:center;">
<form method="post">
<input type="button" value="上一步" onclick="document.location='<?php echo Core::url('install/step_2/');?>';" />
<?php
if ( $is_writable )
{
?>
<?php echo Form::hidden('save','1');?>
<?php echo Form::hidden('hostname',$_POST['hostname']?$_POST['hostname']:'localhost');?>
<?php echo Form::hidden('port',$_POST['port']?$_POST['port']:3306);?>
<?php echo Form::hidden('database',$_POST['database'])?>
<?php echo Form::hidden('username',$_POST['username']?$_POST['username']:'root');?>
<?php echo Form::hidden('password',$_POST['password']?$_POST['password']:'root');?>
<?php echo Form::hidden('table_prefix',$_POST['table_prefix']);?>
<input type="button" value="跳过保存，直接下一步" title="你可以手动保存后，然后跳过此步骤" onclick="document.location='<?php echo Core::url('install/step_3/');?>';" />
<input type="submit" value="保存并下一步" />
<?php
}else{
?>
<input type="button" class="submit" value="下一步" onclick="document.location='<?php echo Core::url('install/step_3/');?>';" />
</form>
<?php
}
?>
</div>
<?php
}
else
{
    if ($_POST)
    {
        $db_config = $_POST;
    }
    else
    {
        $database = Core::config('admin/core.database');
        if (!$database)$database = 'default';
        $server_db_config = Core::config('database.'.$database);
        $db_config = array
        (
            'hostname' => $server_db_config['connection']['hostname'],
            'port' => $server_db_config['connection']['port'],
            'database' => $server_db_config['connection']['database'],
            'username' => $server_db_config['connection']['username'],
            'password' => $server_db_config['connection']['password'],
            'charset' => $server_db_config['charset'],
            'table_prefix' => $server_db_config['table_prefix'],
        );
    }
?>
<form method="post">
<table class="mainTable">
<tr>
    <th colspan="2">配置数据库</th>
</tr>
<tr>
    <td align="right" class="td1" width="25%">数据库服务器：</td>
    <?php
    if (is_array($db_config['hostname']))
    {
    ?>
    <td>
    主数据库：<?php echo Form::input('hostname[master]',$db_config['hostname']['master']?$db_config['hostname']['master']:'localhost');?> 例如:localhost
    <br />
    从数据库：<?php echo Form::textarea('hostname[slaver]',$db_config['hostname']['slaver']?implode(CRLF,$db_config['hostname']['slaver']):'localhost',array('style'=>'width:250px;height:5em;'));?> 每行一个
    </td>
    <?php
    }
    else
    {
    ?>
    <td><?php echo Form::input('hostname',$db_config['hostname']?$db_config['hostname']:'localhost');?> 例如:localhost</td>
    <?php
    }
    ?>
</tr>
<tr>
    <td align="right" class="td1">数据库端口：</td>
    <td><?php echo Form::input('port',$db_config['port']?$db_config['port']:3306);?> 默认值:3306</td>
</tr>
<tr>
    <td align="right" class="td1">数据库名：</td>
    <td><?php echo Form::input('database',$db_config['database'])?></td>
</tr>
<tr>
    <td align="right" class="td1">数据库用户名：</td>
    <td><?php echo Form::input('username',$db_config['username']?$db_config['username']:'root');?></td>
</tr>
<tr>
    <td align="right" class="td1">数据库密码：</td>
    <td><?php echo Form::input('password',$db_config['password']?$db_config['password']:'root');?></td>
</tr>
<tr>
    <td align="right" class="td1">数据表前缀：</td>
    <td><?php echo Form::input('table_prefix',$db_config['table_prefix']);?> 同一数据库运行多个系统时，请修改前缀</td>
</tr>
<?php
if ($message)
{
?>
<tbody class="errTable">
<tr>
<td width="25%"></td>
<td><?php echo $message;?></td>
</tr>
</tbody>
<?php
}
?>
</table>
<div style="padding:20px 0 10px 0;text-align:center;">
<input type="button" value="上一步" onclick="document.location='<?php echo Core::url('install/step_1/');?>';" />
<input type="submit" value="下一步" />
</div>
</form>
<?php
}
?>