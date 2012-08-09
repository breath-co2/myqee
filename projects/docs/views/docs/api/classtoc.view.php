<div class="box_2" id="classtoc_div">
<h1><?php echo __('Class Contents'); ?></h1>
<div class="toc" id="classtoc_main_div">
	<div class="constants">
		<h2><?php echo __('Constants'); ?></h2>
		<ul class="tocfield">
		<?php if ($doc->constants): ?>
		<?php foreach ($doc->constants as $name => $value): ?>
			<li><div class="toc_public"><div class="toc_f"><a href="#constant:<?php echo $name ?>" title="<?php echo $name;?>"><?php echo $name ?></a></div></div></li>
		<?php endforeach ?>
		<?php else: ?>
			<li><em><?php echo __('None'); ?></em></li>
		<?php endif ?>
		</ul>
	</div>
	<div class="properties">
		<h2><?php echo __('Properties'); ?></h2>
		<ul class="tocfield">
		<?php if ($properties = $doc->properties()): ?>
		<?php foreach ($properties as $prop): ?>
			<li><div class="toc_<?php echo $prop->get_modifier();?>"><div class="toc_<?php echo $prop->is_static()?'s':'n';?>"><a href="#property:<?php echo $prop->property->name ?>" title="$<?php echo $prop->property->name ?>">$<?php echo $prop->property->name ?></a></div></div></li>
		<?php endforeach ?>
		<?php else: ?>
			<li><em><?php echo __('None'); ?></em></li>
		<?php endif ?>
		</ul>
	</div>
	<div class="methods span-6 last">
		<h2><?php echo __('Methods'); ?></h2>
		<ul class="tocfun">
		<?php if ($methods = $doc->methods()): ?>
		<?php foreach ($methods as $method): ?>
			<li><div class="toc_<?php echo $method->get_modifier();?>"><div class="toc_<?php echo $method->is_static()?'s':'n';?>"><a href="#<?php echo $method->method->name ?>" title="<?php echo $method->method->name ?>()"><?php echo $method->method->name ?>()</a></div></div></li>
		<?php endforeach ?>
		<?php else: ?>
			<li><em><?php echo __('one'); ?></em></li>
		<?php endif ?>
		</ul>
	</div>
</div>
</div>