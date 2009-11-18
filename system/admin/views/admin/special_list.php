<?php
$page_title = '专题管理';
$page_index = 'class';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<script type="text/javascript">
function delspecial(id){
	ask_del(id,'special/del','<?php echo Myqee::lang('admin/class.list.makesuredelete');?>');
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
						myneworder += 'specialid_'+thisid + '='+newid+',';
					}
				}
			}
		}
	}
	if (myneworder==''){
		alert('排序没有发生变化！');
	}else{
		goUrl('<?php echo Myqee::url('special/editorder') ?>?order='+myneworder , 'hiddenFrame');
	}
}
</script>

<div class="loaction"> 您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; <a href="<?php echo Myqee::url('special/index');?>">专题管理</a></div>
<table border="0" cellpadding="3" cellspacing="1" width="96%" align="center" class="tableborder">
  <tr>
    <th class="td1" width="40">排序</th>
    <th class="td1" width="50">ID</th>
    <th class="td1">专题名称</th>
    <th class="td1" width="120">管理信息</th>
    <th class="td1" width="370">操作</th>
  </tr>
  <?php foreach ($list as $key=>$item) {?>
  <tr <?php if($key%2==0){echo ' class="td3"';} ?> onmouseover="tr_moveover(this)" onmouseout="tr_moveout(this)">
    <td class="td1" align="center"><input type="hidden" id="oldorder_<?php echo $item['sid']?>" value="<?php echo $item['myorder']?>" />
      <input type="text" size="3" id="order_<?php echo $item['sid']?>" value="<?php echo $item['myorder']?>" onkeydown="iseditorder(event)" class="input" /></td>
    <td class="td1" align="center"><?php echo $item['sid']?></td>
    <td class="td2"><?php echo $item['title']?></td>
    <td class="td2" align="center"><a class="btn" href="<?php echo Myqee::url('special/manageinfo/1/'.$item['sid']) ?>">管理信息</a></td>
    <td class="td2" align="center"><input onclick="goUrl('<?php echo Myqee::url('task/tohtml/sframe/?specialid[]='.$item['sid'] );?>','_blank')" type="button" value="更新列表" class="btn2" />
      <input onclick="goUrl('<?php echo Myqee::url('special/copy/'.$item['sid']) ?>')" type="button" value="复制" class="btnss" />
      <input onclick="goUrl('<?php echo Myqee::url('special/edit/'.$item['sid']) ?>')" type="button" value="修改" class="btnss" />
      <input onclick="delspecial(<?php echo $item['sid'];?>)" type="button" value="删除" class="btnss" /></td>
  </tr>
  <?php }?>
</table>
<div style="height:40px">
  <div id="control_div" style="width:100%;min-width:800px" class="control"> <img src="<?php echo ADMIN_IMGPATH;?>/admin/spacer.gif" style="width:800px;height:0px" /><br/>
    <table border="0" cellpadding="3" cellspacing="1" width="96%" align="center" class="tableborder" style="border-top:none;">
      <tr>
        <td class="td1"><div style="float:right">
            <input onclick="goUrl('<?php echo Myqee::url('special/add') ?>')" type="button" value="添加专题" class="bbtn" />
          </div>
          <input onclick="editorder();" type="button" value="修改专题顺序" class="btnl" />
          <input onclick="goUrl('<?php echo Myqee::url('task/tohtml/sframe/?specialid[]=0');?>','_blank')" type="button" value="刷新专题页面" class="btnl" /></td>
      </tr>
    </table>
  </div>
</div>
<script type="text/javascript">
	set_control_fixed(160);
	window.onscroll = function(){set_control_fixed(160)};
</script>
<center style="height:50px;overflow:hidden;">
  <?php echo $page;?>
</center>
<?php View::factory('admin/footer') -> render(TRUE);?>
