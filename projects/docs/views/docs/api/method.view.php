<div class="method">
<?php
if (false)$method = new Docs_Method();
$declares = $method->method->getDeclaringClass();
?>
<h2 id="<?php echo $method->method->name ?>">
	<?php echo $method->modifiers, $method->method->name ?>( <?php echo $method->params ? $method->params_short() : '' ?>)
	<br/><ul class="ul"><li><small>└ <?php echo HTML::anchor(Docs::url($declares->name), $declares->name) ?></small></li></ul>
</h2>

<div class="description">
<?php echo $method->description ?>
</div>

<?php if ($method->tags) View::factory('docs/api/tags')->set('tags', $method->tags)->render(true); ?>

<?php // param tables disabled, removed the FALSE AND below to activate ?>
<?php if ( FALSE AND $method->params): ?>
<h5><?php echo __('Parameters'); ?></h5>
<table>
<tr>
	<th><?php echo __('Parameter'); ?></th>
	<th><?php echo __('Type'); ?></th>
	<th><?php echo __('Description'); ?></th>
	<th><?php echo __('Default'); ?></th>
</tr>
<?php foreach ($method->params as $param): ?>
<tr>
<td><strong><code><?php echo '$'.$param->name ?></code></strong></td>
<td><code><?php echo $param->byref?'byref ':''.$param->type?$param->type:'unknown' ?></code></td>
<td><?php echo ucfirst($param->description) ?></td>
<td><?php echo $param->default ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php endif ?>

<?php if ($method->return): ?>
<h5><?php echo __('Return Values'); ?></h5>
<ul class="return">
<?php foreach ($method->return as $set): list($type, $text) = $set; ?>
<li><code><?php echo HTML::chars($type) ?></code> <?php echo HTML::chars($text) ?></li>
<?php endforeach ?>
</ul>
<?php endif ?>

<?php if ($method->source): ?>
<div class="method-source">
<h5><?php echo __('Source Code'); ?></h5>
<pre><code class="first-line: <?php echo $method->getStartLine();?>"><?php echo HTML::chars($method->source) ?></code></pre>
</div>
<?php endif ?>

</div>