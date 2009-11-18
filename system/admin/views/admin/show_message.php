<?php
$page_index = 'index';
$page_title = Myqee::lang('admin/index.page_title');
$view = new View('admin/header');
$view -> set(array('page_index'=>$page_index,'page_title'=>$page_title));

$view -> render(TRUE);
$showtypeArr = array('alert' => '信息提示','error'=>'错误提示' ,'succeed'=>'操作成功');
if (!$showtypeArr[$showtype]){
	$showtype = 'alert';
	$showtypeArr[$showtype] = $showtypeArr['alert'];
}
?>
<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt;
<?php echo $showtypeArr[$showtype];?>
</div>
<table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder">
	<tr>
		<th class="td1" colspan="2"><?php echo $showtypeArr[$showtype];?></th>
	</tr>
	<tr>
		<td align="center" class="td1" width="100"><img src="<?php echo ADMIN_IMGPATH?>/admin/<?php echo $showtype;?>.gif" /></td>
		<td class="td2">
		<div style="padding:20px 40px;">
		<?php echo $message;?>
		<?php if($forward)echo '<div style="padding-top:80px;text-align:right;"><script>setTimeout("parent.location.href =\''.$forward.'\';", 1000);</script><a href="'.$forward.'" target="parent">页面跳转中...</a></div>';?>
		</div>
		</td>
	</tr>
</table>
<br/>

<?php 
$view = new View('admin/footer');
$view -> render(TRUE);
?>