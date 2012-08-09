<div class="box_1" id="content">
<?php
if (is_file($file))
{
    echo Markdown( file_get_contents($file) );
}
else
{
    echo '指定的文档不存在...<br/>';
}
?>
</div>