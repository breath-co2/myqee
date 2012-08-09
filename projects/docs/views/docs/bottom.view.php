<div style="clear:both"></div>

<link href='<?php echo Core::$base_url;?>/~<?php echo Core::$project;?>~js/SyntaxHighlighter/styles/shCore.css' rel='stylesheet' type='text/css' />
<link href='<?php echo Core::$base_url;?>/~<?php echo Core::$project;?>~js/SyntaxHighlighter/styles/shThemeDefault.css' rel='stylesheet' type='text/css' />
<script src='<?php echo Core::$base_url;?>/~<?php echo Core::$project;?>~js/SyntaxHighlighter/scripts/shCore.js' type='text/javascript'></script>
<script src='<?php echo Core::$base_url;?>/~<?php echo Core::$project;?>~js/SyntaxHighlighter/scripts/shBrushPhp.js' type='text/javascript'></script>
<script type="text/javascript">
function onload()
{
    $('pre:not(.debug) code').each(function()
    {
        $(this).addClass('brush: php');
    });

    SyntaxHighlighter.config.tagName = 'code';
    SyntaxHighlighter.defaults.toolbar = false;
    SyntaxHighlighter.all();
}

(function(){
    var obj = document.getElementById('content');
    if (!obj)return;
    var h1 = obj.getElementsByTagName('h1')[0];
    if (h1) {
        document.title = h1.innerHTML + ' - 用户手册';
    }
})();

$(document).ready(function()
{
    onload();
});
if (typeof onscroll == 'function')
{
    setTimeout(window.onscroll,200);
}
</script>
<?php

$site_view = Core::config('core.site_views');
if ( $site_view && $site_view['bottom'] )
{
    if ($site_view['by_project'])Core::set_project($site_view['by_project']);
    View::factory($site_view['bottom'])->render(true);
    if ($site_view['by_project'])Core::reset_project();
}
else
{
?>

<div class="mainwidth">
    <div style="line-height:1.6em;text-align:center;padding:20px 0 5px 0;">
        <hr style="border:none;border-top:1px solid #ccc;" size="1" />
        &copy; 2003 - 2011 <a href="http://www.myqee.com/" target="_blank">myqee.com</a> All Rights Reserved. 执行时间:<?php echo 1000*number_format((microtime(true)-START_TIME),6);?>毫秒
    </div>
</div>
</body>
</html>
<?php
}
?>