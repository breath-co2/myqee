<script type="text/javascript">
function select_group_perm(obj,ck)
{
    var objs = obj.getElementsByTagName('INPUT');
    for(var i=0;i<objs.length;i++)
    {
        if ( objs[i].disabled )continue;
        objs[i].checked = ck;
    }
}
</script>
<div id="perm_checkbox_div">
<?php
$perm_config = Core::config('admin/permission');
if (!$perm)
{
    $perm = new Permission(array());
}
$my_perm = Session::instance()->member()->perm();
if ($perm_config){
?>
<?php
$max_len = 0;
foreach ($perm_config as $key=>$value)
{
    if ( isset($value['perm'][0]) )
    {
        $have_group = true;
    }
    else
    {
        $have_group = false;
    }

    # 是否有权限
    $have_perm = false;
    # 是否有权限内组
    $have_group_perm = false;
    $tmpstr = '<ul class="ul perm_ul">';
    $tmpstr2 = '';

    # 前置HTML，默认为
    $outhtml_left = '<tr>'.($have_group?'<td width="120" class="td1" align="right">'.$value['name'].'：</td>':'').'<td class="td2"><ul class="ul perm_ul">';
    if ($value['perm'])foreach ($value['perm'] as $k=>$v)
    {
        if (preg_match('#^\d+$#', $k))
        {
            # 根据配置修改前置HTML
            $outhtml_left = ($have_group_perm?'</td></tr>'.CRLF:'').'<tr><td width="120" class="td1" align="right"><label>'.trim($v,'-').'<input type="checkbox" onclick="select_group_perm(this.parentNode.parentNode.parentNode,this.checked)" title="全选" /></label></td><td class="td2"><ul class="ul perm_ul">';
            # 重置
            $have_group_perm = false;
            continue;
        }

        # 判断用户是否有权限
        $perm_key = $key.'.'.$k;
        if ( !$my_perm->is_own($perm_key) )
        {
            # 操作者没有此权限？跳过
            continue;
        }

        # 连接字符串
        $tmpstr .= $outhtml_left;

        # 清空前缀
        $outhtml_left = '';

        $have_perm = true;
        $have_group_perm = true;

        if (!is_array($v))
        {
            $v = array('name'=>$v);
        }
        $field_key = 'perm_setting['.$key.']['.$k.']';
        $max_len = max(strlen($v['name']),$max_len);
        $input_att = array();
        $checked = false;

        if ( $member )
        {
            $checked = $member->perm()->is_own($perm_key);
        }

        if ( $perm->is_own($perm_key) )
        {
            $checked = true;
            if ( $member )
            {
                $input_att['disabled'] = 'disabled';
                $input_att['auto_disabled'] = 'on';
                $tmpstr .= '<li title="所选择组已拥有此权限，无需再设置"> ';
            }
            else
            {
                $tmpstr .= '<li> ';
            }
        }
        else
        {
            $tmpstr .= '<li> ';
        }
        if ($checked)$input_att['has_checked'] = 'yes';
        $tmpstr .= '<label title="'. $v['title'].'">'.Form::checkbox($field_key,'1',$checked,$input_att).$v['name'].'</label></li>';
    }
    $tmpstr .= '</ul></td></tr>';
    if ($have_perm)
    {
?>
<table class="mainTable" style="margin-bottom:6px;">
    <tr>
        <th<?php if ($have_group)echo ' colspan="2"';?>><label><?php echo $value['name'];?><input type="checkbox" onclick="select_group_perm(this.parentNode.parentNode.parentNode.parentNode,this.checked)" title="全选" /></label></th>
    </tr>
    <tr>
    	<?php echo $tmpstr;?>
    </tr>
</table>
<?php
    }
}
?>
<div style="clear:both"></div>
</div>
<style>
.perm_ul li{width:<?php echo (3+floor($max_len/strlen('中')));?>em;float:left;line-height:26px;height:26px;overflow:hidden;}
.perm_ul li input{vertical-align:middle;}
</style>
<?php
}
?>