<table cellspacing="1" cellpadding="4" border="0" align="center" class="tableborder">
  <tr>
    <th class="td1">选择</th>
    <th class="td1">用户名</th>
    <th class="td1">内容</th>
    <th class="td1">发表时间</th>
    <th class="td1">管理</th>
  </tr>
  <?php foreach ($list as $val) {?>
  <tr class="td3">
    <td class="td1" align="center" width="40"><input type="checkbox" id="select_id_<?php echo $val['id']?>" onclick="select_tr(this)"/></td>
    <td class="td1" align="center"><?php echo $val['username']?></td>
    <td class="td2"><?php echo $val['comment']?></td>
    <td class="td2" align="center" width="120"><?php echo date('Y-m-d H:i',$val['addtime']);?></td>
    <td class="td2" align="center"><input type="button" class="btnss" value="删除" onclick="ask_del('<?php echo $val['id'];?>','<?php echo ('plugins/run/comment/comment/del');?>','确认删除？');" _is_myqee_set="true"/></td>
  </tr>
  <?php }?>
  <tr class="td3">
    <td class="td1" align="center"><input type="checkbox" onclick="selectbox(this,'select_id')" title="选择上面全部"/></td>
    <td colspan="4" class="td1"><input type="button" onclick="confirm('确认执行删除操作？',null,null,null,function(e){if(e=='ok')submitbox('select_id','plugins/run/comment/comment/del/[id]/','hiddenFrame')})" class="btns" value="删除" _is_myqee_set="true"/></td>
  </tr>
</table>
