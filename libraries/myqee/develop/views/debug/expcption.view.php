<?php
// 输出一个隐藏的div，在源代码中便于阅读的字符串
echo "<div style=\"display:none\">\r\n\r\n\r\n\r\n\r\n\r\n\r\n========================== Track String ==========================\r\n",str_replace(array(DIR_CORE,DIR_LIBRARY,DIR_WWWROOT,DIR_SYSTEM),array('./core/','./libraries/','./wwwroot/','./'),$e->__toString()),"\r\n\r\n\r\n\r\n\r\n\r\n\r\n</div>";

$error_id = uniqid('error');
?>
<style type="text/css">
#expction_div { background: #ddd; font-size: 1em; font-family:sans-serif; text-align: left; color: #111; }
#expction_div h1,
#expction_div h2 { margin: 0; padding: 1em; font-size: 1em; font-weight: normal; background: #911; color: #fff; }
#expction_div h1 a,
#expction_div h2 a { color: #fff; }
#expction_div h2 { background: #222; }
#expction_div h3 { margin: 0; padding: 0.4em 0 0; font-size: 1em; font-weight: normal; }
#expction_div p { margin: 0; padding: 0.2em 0; }
#expction_div a { color: #1b323b; }
#expction_div pre { overflow: auto; white-space: pre-wrap; }
#expction_div table { width: 100%; display: block; margin: 0 0 0.4em; padding: 0; border-collapse: collapse; background: #fff; }
#expction_div table td { border: solid 1px #ddd; text-align: left; vertical-align: top; padding: 0.4em; }
#expction_div div.content { padding: 0.4em 1em 1em; overflow: hidden; }
#expction_div pre.source { margin: 0 0 1em; padding: 0.4em; background: #fff; border: dotted 1px #b7c680; line-height: 1.2em; }
#expction_div pre.source span.line { display: block; }
#expction_div pre.source span.highlight { background: #f0eb96; }
#expction_div pre.source span.line span.number { color: #666; }
#expction_div ol.trace { display: block; margin: 0 0 0 2em; padding: 0; list-style: decimal; }
#expction_div ol.trace li { margin: 0; padding: 0; }
</style>
<script type="text/javascript">
document.write('<style type="text/css"> .collapsed { display: none; } </style>');
function koggle(elem)
{
elem = document.getElementById(elem);

if (elem.style && elem.style['display'])
// Only works with the "style" attr
var disp = elem.style['display'];
else if (elem.currentStyle)
// For MSIE, naturally
var disp = elem.currentStyle['display'];
else if (window.getComputedStyle)
// For most other browsers
var disp = document.defaultView.getComputedStyle(elem, null).getPropertyValue('display');

// Toggle the state of the "display" style
elem.style.display = disp == 'block' ? 'none' : 'block';
return false;
}
</script>

<div style="padding:0 10px;">
<div id="expction_div">
<h1><span class="type"><?php echo $type ?> [ <?php echo $code ?> ]:</span> <span class="message"><?php echo str_replace(DIR_SYSTEM,'./',$message); ?></span></h1>
<div id="<?php echo $error_id ?>" class="content">
<p><span class="file"><?php echo Core::debug_path($file,'#a00') ?> [ <?php echo $line ?> ]</span></p>
<?php echo ErrException::debug_source($file, $line) ?>
<ol class="trace">
<?php foreach (ErrException::trace($trace) as $i => $step): ?>
<li>
<p>
	<span class="file">
		<?php if ($step['file']): $source_id = $error_id.'source'.$i; ?>
			<a href="#<?php echo $source_id ?>" onclick="return koggle('<?php echo $source_id ?>')"><?php echo Core::debug_path($step['file'],'#a00') ?> [ <?php echo $step['line'] ?> ]</a>
		<?php else: ?>
			{PHP internal call}
		<?php endif ?>
	</span>
	&raquo;
	<?php echo $step['function'] ?>(<?php if ($step['args']): $args_id = $error_id.'args'.$i; ?><a href="#<?php echo $args_id ?>" onclick="return koggle('<?php echo $args_id ?>')">arguments</a><?php endif ?>)
</p>
<?php if (isset($args_id)): ?>
<div id="<?php echo $args_id ?>" class="collapsed">
	<table cellspacing="0">
	<?php foreach ($step['args'] as $name => $arg): ?>
		<tr>
			<td><code><?php echo $name ?></code></td>
			<td><pre style="padding:0;margin:0;"><?php echo ErrException::dump($arg) ?></pre></td>
		</tr>
	<?php endforeach ?>
	</table>
</div>
<?php endif ?>
<?php if (isset($source_id)): ?>
	<pre id="<?php echo $source_id ?>" class="source collapsed"><code><?php echo $step['source'] ?></code></pre>
<?php endif ?>
</li>
<?php unset($args_id, $source_id); ?>
<?php endforeach ?>
</ol>
</div>
<?php $env_id = $error_id.'environment';?>
<h2 onclick="return koggle('<?php echo $env_id ?>');" style="cursor:pointer;" onmouseover="this.getElementsByTagName('A')[0].style.color='#f0ff00';" onmouseout="this.getElementsByTagName('A')[0].style.color='';"><a href="#<?php echo $env_id; ?>" onclick="return false">Environment</a></h2>
<div id="<?php echo $env_id ?>" class="content collapsed">
	<?php $included = Core::include_path(); ?>
	<h3><a href="#<?php echo $env_id = $error_id.'environment_include_path' ?>" onclick="return koggle('<?php echo $env_id ?>')">Include path</a> (<?php echo count($included) ?>)</h3>
	<div id="<?php echo $env_id ?>" class="collapsed">
		<table cellspacing="0">
			<?php foreach ($included as $file): ?>
			<tr>
				<td><code><?php echo Core::debug_path($file,'#a00') ?></code></td>
			</tr>
				<?php endforeach ?>
		</table>
	</div>
	<?php $included = get_included_files(); ?>
	<h3><a href="#<?php echo $env_id = $error_id.'environment_included' ?>" onclick="return koggle('<?php echo $env_id ?>')">Included files</a> (<?php echo count($included) ?>)</h3>
	<div id="<?php echo $env_id ?>" class="collapsed">
		<table cellspacing="0">
			<?php foreach ($included as $file): ?>
			<tr>
				<td><code><?php echo Core::debug_path($file,'#a00') ?></code></td>
			</tr>
				<?php endforeach ?>
		</table>
	</div>
	<?php $included = get_loaded_extensions() ?>
	<h3><a href="#<?php echo $env_id = $error_id.'environment_loaded' ?>" onclick="return koggle('<?php echo $env_id ?>')">Loaded extensions</a> (<?php echo count($included) ?>)</h3>
	<div id="<?php echo $env_id ?>" class="collapsed">
		<table cellspacing="0">
			<?php foreach ($included as $file): ?>
			<tr>
				<td><code><?php echo Core::debug_path($file,'#a00') ?></code></td>
			</tr>
			<?php endforeach ?>
		</table>
	</div>
	<?php foreach (array('_SESSION', '_GET', '_POST', '_FILES', '_COOKIE', '_SERVER') as $var): ?>
	<?php if (empty($GLOBALS[$var]) OR ! is_array($GLOBALS[$var])) continue ?>
	<h3><a href="#<?php echo $env_id = $error_id.'environment'.strtolower($var) ?>" onclick="return koggle('<?php echo $env_id ?>')">$<?php echo $var ?></a></h3>
	<div id="<?php echo $env_id ?>" class="collapsed">
		<table cellspacing="0">
			<?php foreach ($GLOBALS[$var] as $key => $value): ?>
			<tr>
				<td><code><?php echo $key ?></code></td>
				<td><pre style="padding:0;margin:0;"><?php echo ErrException::dump($value) ?></pre></td>
			</tr>
			<?php endforeach ?>
		</table>
	</div>
	<?php endforeach ?>
</div>
</div>
</div>
