<?php
$page_title = '专题信息管理';
$page_index = 'class';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<script type="text/javascript">
	function delspecialinfo(id){
		ask_del(id,'special/delinfo','<?php echo Myqee::lang('admin/class.list.makesuredelete');?>');
	}
	function iseditorder(e){
		if (document.all)e=window.event;
		if (e.keyCode == 13){
			editorder();
		}
	}
	function editorder(){
		var inputs = document.getElementsByTagName('input');
		var myneworder = '';
		for (var k in inputs){
			if (inputs[k].id){
				if (inputs[k].type=='text' && inputs[k].id.substr(0,6) == 'order_' ){
					var thisid = inputs[k].id.substr(6);
					var newid = inputs[k].value.replace(/[^0-9]/g,'');
					var oldorderobj = $('oldorder_'+thisid);
					if (oldorderobj){
						if ( oldorderobj.value != newid){
							myneworder += 'id_'+thisid + '='+newid+',';
						}
					}
				}
			}
		}
		if (myneworder==''){
			alert('排序没有发生变化！');
		}else{
			goUrl('<?php echo Myqee::url('special/editorder/infolist') ?>?order='+myneworder , 'hiddenFrame');
		}
	}
</script>

<div class="loaction"> 您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; <a href="<?php echo Myqee::url('special/index');?>">专题管理</a> -&gt; <?php echo $special_title?>专题信息管理 </div>
<table border="0" cellpadding="3" cellspacing="1" width="96%" align="center" class="tableborder">
  <tr>
  	<th class="td1" width="30">选择</th>
    <th class="td1" width="40">排序</th>
    <th class="td1" width="50">ID</th>
    <th class="td1">标题</th>
    <th class="td1">分类</th>
    <th class="td1">显示</th>
    <th class="td1">头条</th>
    <th class="td1">置顶</th>
    <th class="td1">热门</th>
    <th class="td1">推荐</th>
    <th class="td1">操作</th>
  </tr>
  <?php foreach ($list as $key=>$item) {?>
  <tr <?php if($key%2==0){echo ' class="td3"';} ?> onmouseover="tr_moveover(this)" onmouseout="tr_moveout(this)">
  	<td class="td1" align="center"><input onclick="select_tr(this)" id="select_id_<?php echo $item['id'];?>" type="checkbox"></td>
    <td class="td1" align="center"><input type="hidden" id="oldorder_<?php echo $item['id']?>" value="<?php echo $item['myorder']?>" />
      <input type="text" size="3" id="order_<?php echo $item['id']?>" value="<?php echo $item['myorder']?>" onkeydown="iseditorder(event)" class="input" /></td>
    <td class="td1" align="center"><?php echo $item['infoid']?></td>
    <td class="td2"><div class="nbsp"></div><?php echo $item['stitle']?> <a href="<?php echo $item['URL']?>" target="_blank"><img src="/images/admin/external.png" alt="新窗口查看"></a></td>
    <td class="td2" align="center"><?php echo $item['class_name']?></td>
    <td class="td2" align="center"><?php echo $item['isshow']?></td>
    <td class="td2" align="center"><?php echo $item['isheadlines']?></td>
    <td class="td2" align="center"><?php echo $item['ontop']?></td>
    <td class="td2" align="center"><?php echo $item['ishot']?></td>
    <td class="td2" align="center"><?php echo $item['iscommend']?></td>
    <td class="td2" align="center">
      <input onclick="goUrl('<?php echo Myqee::url($item['editurl']) ?>')" type="button" value="修改" class="btnss" />
      <input onclick="delspecialinfo('<?php echo $item['id'];?>')" type="button" value="删除" class="btnss" /></td>
  </tr>
  <?php }?>
   <tr>
      <td class="td1" align="center"><input type="checkbox" onclick="selectbox(this,'select_id')" title="选择上面全部"/></td>
      <td colspan="9" align="left" class="td1">
      <input type="button" onclick="confirm('确认执行删除操作？',null,null,null,function(e){if(e=='ok')submitbox('select_id','special/delinfo/[id]/','hiddenFrame')})" class="btns" value="删除" _is_myqee_set="true"/>
      <input onclick="editorder();" type="button" value="修改专题顺序" class="btnl" />
      </td>
      <td class="td1" align="center"></td>
    </tr> 
</table>
<script type="text/javascript">
	set_control_fixed(160);
	window.onscroll = function(){set_control_fixed(160)};
</script>
<center style="height:50px;overflow:hidden;">
  <?php echo $pageurl;?>
</center>
<?php View::factory('admin/footer') -> render(TRUE);?>
