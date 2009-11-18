<?php
$page_index = 'model';
$page_title = '导入数据表';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>

<div class="loaction"> 您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; <a href="<?php echo Myqee::url('model/index');?>">模型管理</a> -&gt; <a href="<?php echo Myqee::url('model/dblist');?>">数据表管理</a> -&gt; 导入数据表 </div>
<div style="width:96%;margin:auto;">
  <ul class="ul tag">
    <li class="now" id="mytag_1" onclick="tag(this.id,'mytag','mytagmain');$('upload_fileDiv').innerHTML=$('upload_fileDiv').innerHTML;$('div_password').style.display='';">输入方式导入</li>
    <li id="mytag_2" onclick="tag(this.id,'mytag','mytagmain');$('div_password').style.display='';">上传方式导入</li>
    <li id="mytag_3" onclick="tag(this.id,'mytag','mytagmain');$('div_password').style.display='none';">从已有的数据表导入</li>
  </ul>
</div>
<div style="clear:both"></div>
  <form method="post" name="myforms" action="<?php echo Myqee::url('model/dbinput/');?>" target="hiddenFrame" enctype="multipart/form-data">
    <div id="mytagmain_1">
      <table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="border-bottom:none;">
        <tr>
          <th class="td1" colspan="2">输入方式导入</th>
        </tr>
        <tr>
          <td class="td1" align="right" width="150">待导入内容：</td>
          <td class="td2"><textarea name="data" style="width:500px;overflow:auto;height:200px;" class="input"></textarea></td>
        </tr>
      </table>
    </div>
    <div id="mytagmain_2" style="display:none;">
      <table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="border-bottom:none;">
        <tr>
          <th class="td1" colspan="2">上传方式导入</th>
        </tr>
        <tr>
          <td class="td1" align="right" width="150">上传数据表文件：</td>
          <td class="td2" id="upload_fileDiv"><input type="file" class="input" name="upload" size="50" /></td>
        </tr>
      </table>
    </div>
    <div id="div_password">
    <table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="border-top:none;">
      <tr>
        <td class="td1" align="right" width="150">数据表导入密码：</td>
        <td class="td2"><input type="text" name="key" size="20" class="input" value="" />
          &nbsp;<font color="#a3a3a3">若数据表无加密密码，则留空即可</font></td>
      </tr>
      <tr>
        <td class="td1" align="right">&nbsp;</td>
        <td class="td1"><input type="submit" class="bbtn" value="导入数据表" style="letter-spacing:0" />
          <input onclick="goback(-1,'<?php echo Myqee::url('model/index'); ?>')" type="button" value="返回" class="btns" /></td>
      </tr>
    </table>
    </div>
  </form>
<div id="mytagmain_3" style="display:none;">
<form method="post" name="myforms" action="<?php echo Myqee::url('model/inputdbsave/');?>" target="hiddenFrame" >
  <table border="0" cellpadding="4" cellspacing="1" align="center" class="tableborder" style="border-bottom:none;">
    <tr>
      <th class="td1" colspan="2">从已有的数据表导入</th>
    </tr>
    <tr>
      <td class="td1" align="right" width="150">数据库</td>
      <td class="td2" id="upload_fileDiv">数据表</td>
    </tr>
    <tr>
      <td class="td1" align="right" width="150" valign="top"><?php echo form::dropdown('database',$databases,'','onclick="change_tables(this.value);"');?></td>
      <td class="td2" id="upload_fileDiv"><div id="div_tables"></div></td>
    </tr>
    <tr>
        <td class="td1" align="right">&nbsp;</td>
        <td class="td1"><input type="submit" class="bbtn" value="导入数据表" style="letter-spacing:0" />
          <input onclick="goback(-1,'<?php echo Myqee::url('model/index'); ?>')" type="button" value="返回" class="btns" /></td>
      </tr>
  </table>
  <script>
var alltables = <?php echo $tables;?>;
function change_tables (database) {
	var tables = alltables[database];
	var selectstr = '<select id="tables" name="tables[]" multiple="multiple" >';
	var optionstr = '';
	for (var key in tables) {
		optionstr += '<option value='+tables[key]+'>'+tables[key]+'</option>';
	}
	selectstr += optionstr;
	selectstr += '</select>';
	$('div_tables').innerHTML = selectstr;
}
change_tables('default');
</script>
</form>
</div>
<?php View::factory('admin/footer') -> render(TRUE);?>
