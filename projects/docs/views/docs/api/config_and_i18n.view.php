<div class="box_1" id="content">
<h1>
	<?php echo $file;?>
</h1>
<div style="height:10px;overflow:hidden"></div>
<?php
echo '<pre><code>';
echo htmlspecialchars(print_r($config,true));
echo '</code></pre>';
?>
</div>