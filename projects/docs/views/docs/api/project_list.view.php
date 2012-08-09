<div class="box_2">
<h1>按项目查看API</h1>
<ol style="padding-left:20px;margin-top:0px;">
<?php
$config = Core::config('core.projects');
foreach ($config as $key=>$item){
	if(isset($item['isuse']) && !$item['isuse'])continue;
?>
<li style="padding:5px 0;">
	<h2><?php echo $item['name']?></h2>
	<ul class="ul" style="padding:5px 5px 5px 20px;">
		<li><a href="<?php echo Core::url('api/'.$key.'/controllers/');?>">控制器 (Controllers)</a></li>
		<li><a href="<?php echo Core::url('api/'.$key.'/classes/');?>">类库 (Classes)</a></li>
		<li><a href="<?php echo Core::url('api/'.$key.'/models/');?>">模型 (Models)</a></li>
		<li><a href="<?php echo Core::url('api/'.$key.'/orm/');?>">ORM</a></li>
		<li><a href="<?php echo Core::url('api/'.$key.'/config/');?>">配置 (config)</a></li>
		<li><a href="<?php echo Core::url('api/'.$key.'/i18n/');?>">语言包 (i18n)</a></li>
	</ul>
</li>
<?php
}
?>
</ol>
</div>