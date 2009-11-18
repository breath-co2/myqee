<?php
$page_title = '编辑碎片';
$page_index = 'info';
View::factory('admin/header',array('page_index'=>$page_index,'page_title'=>$page_title)) -> render(TRUE);
?>
<div class="loaction">
您的位置：<a href="<?php echo Myqee::url('index');?>">管理首页</a> -&gt; 
<a href="<?php echo Myqee::url('info/index');?>">内容管理</a>-&gt; 
<a href="<?php echo Myqee::url('block/index');?>">碎片管理</a>-&gt; 
编辑碎片
</div>
<div style="clear: both"></div>

<script type="text/javascript">
function edit_block(type,no,obj){
	win({
		title:'碎片信息编辑',
		width:750,
		height:420,
		minBtn:true,
		maxBtn:true,
		showMask:false,
		dragOut:true,
		iframe:{
			src:'<?php echo Myqee::url('block/view_edit_frame?'.(in_array($type,array('index','cover','content','site','custompage'))?'autohtml='.$type.'&':'').'type=')?>'+type+'&no='+no,
			width:'100%',
			height:'100%'
		}
		});
}

function set_myqeediv(id){
	var obj1 = mainFrame.document.getElementById(id+'_div');
	var obj2 = mainFrame.document.getElementById(id);
	if (!obj1 || !obj2)return;
	obj1.style.width = obj2.offsetWidth+"px";
	obj1.style.height = obj2.offsetHeight+"px";
}
</script>

<table border="0" cellpadding="2" cellspacing="1" width="96%" align="center" class="tableborder">
<tr>
<td>
<iframe id="mainFrame" name="mainFrame" width="100%" height="400" frameborder="0" src="<?php echo Myqee::url('task/tohtml/'.$mytype.'/'.$id.'?_editblock=yes');?>"></iframe>
</td>
</tr>
</table>
<script type="text/javascript">
window.onresize = function(){
	var obj = $('mainFrame');
	var h = dom.clientHeight-110;
	h = Math.max(300,h);
	obj.style.height = h +'px';
}
window.onresize();
</script>
<script type="text/javascript">myqee();</script>
<script type="text/javascript" src="<?php echo ADMIN_IMGPATH;?>/admin/menu.js"></script>
<script type="text/javascript" src="<?php echo ADMIN_IMGPATH;?>/admin/tooltip.js"></script>
</body>
</html>