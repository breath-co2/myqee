<br />
<?php
if ($error)
{
?>
<table class="errTable mainTable">
<tr>
<td><?php echo $error;?></td>
</tr>
</table>

<div style="padding:5px 0;color:red;">
您必须解决以上问题，安装才可以继续
</div>

<div style="padding:20px 0 10px 0;text-align:center;">
    <input type="button" value="返回上一步" onclick="history.go(-2)" />
</div>
<?php
}
elseif ($ok)
{
?>
<table class="infoTable mainTable">
<tr>
<td align="center">
<br />
<br /><?php echo $ok;?>
<br />
<br />
<br />
</td>
</tr>
</table>

<div style="padding:20px 0 10px 0;text-align:center;">
    <input type="button" class="submit" value="下一步" onclick="document.location='<?php echo Core::url('install/step_4/');?>'" />
</div>
<?php
}
elseif ($info)
{
?>
<table class="mainTable">
<tr>
<td align="center">
<br />
<br />
<?php echo $info;?>
<br />
<br />
<br />
</td>
</tr>
</table>

<div style="padding:20px 0 10px 0;text-align:center;">
    <input type="button" value="返回上一步" onclick="history.go(-2)" />
    <input type="button" class="submit" value="继续安装" onclick="document.location='<?php echo Core::url('install/step_4/');?>'" />
</div>
<?php
}
?>