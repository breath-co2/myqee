<?php 
$menu_config = Core::config('admin/core.menu');
if ( count($menu_config)>1 )
{
    # 大于1项的才有必要显示
?>
<tr>
    <td class="td1" align="right">
        菜单配置：
    </td>
    <td colspan="2" class="td2">
<ul class="ul">
<?php 
foreach ($menu_config as $key=>$item){
?>
<li style="float:left;height:1.6em;line-height:1.6em;display:inline;padding-right:20px;">
<label for="menu_<?php echo $key;?>">
<?php echo Form::radio('setting[menu_config]',$key,$checked_menu == $key?true:false,array('id'=>'menu_'.$key));?>
<?php echo $item;?>
</label>
</li>
<?php 
}
?>
</ul>
    </td>
</tr>
<?php 
}
?>