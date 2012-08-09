<dl class="tags">
<?php foreach ($tags as $name => $set): ?>
<span>
<dt><?php echo $name ?></dt>
<?php foreach ($set as $tag): ?>
<dd><?php echo $tag ?></dd>
<?php endforeach ?>
</span>
<?php endforeach ?>
</dl>