<div class="clear" style="height:10px;"></div>
<table cellspacing="1" cellpadding="2" border="0" align="center" class="tableborder">
<tr class="td1">
<th><?php echo $title?$title:'请选择信息';?></th>
</tr>
<tr>
<td class="td2">
<?php foreach ($list as $val) {?>
<div style="float:left;width:124px;height:30px;margin-left:5px;">
<div style="white-space:nowrap;height:1.2em;position:absolute;background:#fff;cursor:pointer;width:106px;overflow:hidden;border:1px gray dashed;padding:5px;" 
onmouseover="this.style.backgroundColor='#FF3';if(this.scrollWidth>116)this.style.width='auto';this.style.zIndex=1" onmouseout="this.style.width='106px';this.style.zIndex='auto';this.style.backgroundColor='#FFF'" 
onclick="frameFrame.setExtandValue(<?php echo "'$fieldname','{$val[$ffieldsave]}','{$val[$ffieldshow]}','{$expstr}','{$isappend}'";?>)">
<?php echo $val[$ffieldshow];?>
</div>
</div>
<?php }?>
</td>
</tr>
<tr><td class="td1" align="right">
<table cellspacing="0" cellpadding="0" border="0"><tr>
<?php
if ($isappend){
echo '<td><input type="checkbox" id="_select_auto_close_obj" value="1" checked="checked" /></td>
<td>自动关闭本窗口</td>';
}
?>
<td><input type="button" class="btn2" value="清除信息" onclick="(function(){var obj=frameFrame.$('__myqee_input_<?php echo $fieldname;?>');var obj2=frameFrame.$('__myqee_input_<?php echo $fieldname;?>_showinput');if(!obj||!obj2)return;obj.value=obj2.value='';window.closewin();})()" /></td>
</tr></table>
</td></tr>

</table>
<div style="height:40px;overflow:hidden;"><center><?php echo $pageurl;?></center></div>