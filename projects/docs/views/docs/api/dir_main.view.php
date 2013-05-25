<div class="box_1">
<h1><?php echo $dir.'/'.ltrim(str_replace('\\', '/', $classname).'/','/');?></h1>
<style type="text/css">
.dir_ul,.dir_ul ul{
	list-style:none;
	padding:0;
	margin:0;
}
.dir_method_name{
	float:left;
	width:166px;
	height:28px;
	line-height:28px;
	overflow:hidden;
	margin-left:15px;
	display:inline;
}
</style>
<ul class="dir_ul">
<?php
if ($arr_docs)foreach ($arr_docs as $item){
	if (false)$item = new Docs_Class();
	$methods = $item->methods();
	$class_url = Docs::url($item->class->name);
	echo '<li class="dir_class_name"><h2><a href="'.$class_url.'">'.$item->class->name.'</a></h2><ul>';
	if ($methods)foreach ($methods as $m){
		if (false)$m = new Docs_Method();
		echo '<li class="dir_method_name"><a href="'.$class_url.'#'.$m->method->name.'" title="'.$m->method->name.'()">'.$m->method->name.'()</a></li>';
	}
	echo '</ul><div style="clear:both;"></div></li>';
}else{
	echo '<li style="padding:10px 10px 20px 10px;">本目录文件为空</li>';
}
?>
</ul>
</div>