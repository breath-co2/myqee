<style type="text/css">
pre { background: #f8f8f8;padding:4px;overflow: auto; }
table th { color: #444; background: #eee; }
table td { background: #fafafa; }
	table tr.alt td { background: #fff; }
code, pre { color: #c33; }

#content,#content p {font-size:12px;}
#content .method-source h5 {font-size:12px;margin:0;}
#content .method-source pre {margin:4px 0 0 0;}
#content .method .return {margin:0 0 6px 0;}
#content .method h5{margin:5px 0;font-size:12px;}
#content h1 small,
#content h2 small,
#content h3 small,
#content h4 small,
#content h5 small,
#content h6 small { font-size: 14px; font-weight: normal;color:#666;}
#content h1 small{color:#aaa;}
#content pre { font-size: 1em; line-height: 1.4em; direction: ltr }

#content div.class-list div.class { width: 50%; }
#content div.class-list div.class.left { float: left; clear: both; }
#content div.class-list div.class.right { float: right; }
	#content div.class-list div.class h2 { margin: 0.4em; padding: 0.2em 0.4em; font-size: 1em; }

#content div.method,
#content div.properties,
#content div.constants { margin-bottom: 1em; }
	#content div.method h2 {font-size:14px;padding:5px;}
	#content div.method h2 .param { font-weight: normal; cursor: help; border-bottom:1px dashed #666;}
	#content div.method h2 abbr.param { text-transform: none; font-size: 1em; }
#content p.note { display: block; padding: 0.4em 0.6em; padding-left: 3em; background: url(<?php echo Core::$base_url;?>/~docs~img/note.png) 1em 0.6em no-repeat; border: 1px solid #e5e5e5; font-family: Georgia, serif; font-size: 1.05em; font-style: italic; color: #555;margin-bottom:10px; }
#content dl.tags { overflow: auto; background: #eee; border: solid 6px #ddd; }
	#content dl.tags span{display:block;border-bottom:1px dotted #fff;zoom:1; padding:4px;}
	#content dl.tags span:after{content:'\20';display:block;clear:both;}
	#content dl.tags dt { margin: 0 0 2px; clear: both; float: left; width: 25%; }
	#content dl.tags dd { margin: 0; padding: 0; clear: right; float: right; }

#content #methods{margin:10px 0;}
#content .ul{
	margin:0;
	list-style:none;
	padding:0 0 0 18px;
}

#content .constants pre{border:none;background:transparent;}
#content .properties pre{border:none;background:transparent;}
#content .properties p{margin:0;padding:5px 0 0 4px;}
#content .properties dt{font-size:14px;font-weight:bold;}
#content .properties dt code{font-weight:normal;}

.main_center{width:695px;margin:0 0 0 6px;float:left;}
.main_left{float:left;width:30px;height:600px;margin:0;padding:0;}

#file_tree_show_div{display:none;}
#filetree_div{
    z-index:1;
}

#file_tree_show_div
{
    -moz-box-shadow: 10px 10px 10px rgba(0,0,0,0.4);
    -webkit-box-shadow: 10px 10px 10px rgba(0,0,0,0.4);
    box-shadow: 10px 10px 10px rgba(0,0,0,0.4);
}
#filetree_main_div{
    width:200px;
    overflow:auto;
}
#classtoc_div{
    width:183px;
}
#classtoc_main_div{
    width:183px;
    overflow:auto;
}

#classtoc_main_div{white-space:nowrap;}

.toc ul{
	margin:0;
	list-style:none;
	padding:0;
}
.toc ul li{line-height:1.8em;}

.toc .tocfield .toc_public{padding-left:14px;background:url(<?php echo Core::$base_url;?>/~docs~img/toc.gif) no-repeat 0 -58px;}
.toc .tocfield .toc_private{padding-left:14px;background:url(<?php echo Core::$base_url;?>/~docs~img/toc.gif) no-repeat 0 -26px;}
.toc .tocfield .toc_protected{padding-left:14px;background:url(<?php echo Core::$base_url;?>/~docs~img/toc.gif) no-repeat 0 5px;}
.toc .tocfun .toc_public{padding-left:14px;background:url(<?php echo Core::$base_url;?>/~docs~img/toc.gif) no-repeat 0 -160px;}
.toc .tocfun .toc_private{padding-left:14px;background:url(<?php echo Core::$base_url;?>/~docs~img/toc.gif) no-repeat 0 -125px;}
.toc .tocfun .toc_protected{padding-left:14px;background:url(<?php echo Core::$base_url;?>/~docs~img/toc.gif) no-repeat 0 -92px;}
.toc .toc_f{padding-left:10px;background:url(<?php echo Core::$base_url;?>/~docs~img/toc.gif) no-repeat -3px -195px;}
.toc .toc_s{padding-left:10px;background:url(<?php echo Core::$base_url;?>/~docs~img/toc.gif) no-repeat -3px -225px;}
.toc .toc_n{padding-left:10px;}

.toc li em{padding:0 10px;}

</style>

<?php
$site_view = Core::config('core.site_views');
$header_height = (int)$site_view['header_height'];
$bottom_height = (int)$site_view['bottom_height'];
?>
<script type="text/javascript">

var dom = (document.compatMode &&document.compatMode=="CSS1Compat")?document.documentElement:document.body;
window.onresize = window.onscroll = function(){
    var obj_left = document.getElementById('filetree_div');
    var obj_left_main = document.getElementById('filetree_main_div');
    var classtoc = document.getElementById('classtoc_div');
    var classtoc_main = document.getElementById('classtoc_main_div');
    if (!obj_left||!classtoc)return true;
    var header_height = <?php echo $header_height;?>;
    var bottom_height = <?php echo $bottom_height;?>;
    var title_height = 40;
    var h = dom.clientHeight;
    var real_h = dom.scrollHeight;
    var s_top = dom.scrollTop||document.body.scrollTop;
    var max_scroll = classtoc_main.clientHeight+bottom_height;
    var max_height = real_h-bottom_height-header_height;
    if (real_h-s_top-title_height-20<max_scroll)
    {
        max_scroll =  classtoc.clientHeight+bottom_height;
        classtoc.style.position =
        obj_left.style.position = 'absolute';
        classtoc.style.top =
        obj_left.style.top = (real_h-max_scroll)+'px';
        classtoc.style.bottom =
        obj_left.style.bottom = '';
        classtoc_main.style.height =
        obj_left_main.style.height = Math.min(h,max_height)-title_height-3+'px';
    }
    else if (s_top>header_height){
        classtoc.style.position =
        obj_left.style.position = 'fixed';
        classtoc.style.top =
        obj_left.style.top = 0;
        classtoc.style.bottom =
        obj_left.style.bottom = '';
        classtoc_main.style.height =
        obj_left_main.style.height = Math.min(h,max_height)-title_height-3+'px';
    }
    else if (s_top<header_height && real_h-bottom_height-s_top>=h)
    {
        classtoc.style.position =
        obj_left.style.position = 'absolute';
        classtoc.style.top =
        obj_left.style.top = (header_height-3) + 'px';
        classtoc.style.bottom =
        obj_left.style.bottom = '';
        classtoc_main.style.height =
        obj_left_main.style.height = Math.min((h-header_height+s_top),max_height)-title_height+'px';
    }
    else
    {
        classtoc.style.height =
        obj_left.style.height = (max_height-bottom_height-title_height)+'px';
        classtoc.style.position =
        obj_left.style.position = '';
        classtoc.style.top =
        obj_left.style.top = '';
        classtoc.style.bottom =
        obj_left.style.bottom = '';
    }
}

var hidden_filetree;
function show_filetree(isshow)
{
    var obj1 = document.getElementById('file_tree_show_menu');
    var obj2 = document.getElementById('file_tree_show_div');
    if (hidden_filetree){
        clearTimeout(hidden_filetree);
        hidden_filetree = null;
    }
    hidden_filetree = setTimeout(function(){
        if (isshow)
        {
            obj1.style.display='none';
            obj2.style.display='block';
        }
        else
        {
            obj1.style.display='block';
            obj2.style.display='none';
        }
    },isshow?50:300);
}

(function (){
    var obj = document.getElementById('file_tree_show_div');
    obj.onmouseover=function(){show_filetree(true);};
    obj.onmouseout=function(){show_filetree(false);};
    var obj = document.getElementById('file_tree_show_menu');
    obj.onmouseover=function(){show_filetree(true);};
    obj.style.display = 'block';
})();
</script>

<div class="box_1" id="content">
<h1>
	<?php
	if (false)$doc = new Docs_Class();
	echo $doc->modifiers, $doc->class->name ?>
</h1>
<?php $parent = $doc->class; ?>
<?php
$endtag = '';
$i=0;
while ($parent = $parent->getParentClass()):
if ($i==0)echo '<h2 style="margin-top:0;">';
$filename = str_replace(array('\\','/'),'_',strtolower($parent->getFileName()));
if (preg_match('#_([a-z0-9]+)_'.preg_quote(strtolower($parent->name).EXT).'$#',$filename,$m)){
	$pdir = $m[1];
}else{
	$pdir = $dir;
}
?>
<ul class="ul"><li><small>└ <?php echo HTML::anchor(Docs::url($parent->name), $parent->name) ?></small></li>
<?php
$endtag .= '</ul>';
$i++;
endwhile;
echo $endtag;
if ($i>0)echo '</h2>';
?>

<?php echo $doc->description ?>

<?php if ($doc->tags) View::factory('docs/api/tags')->set('tags', $doc->tags)->render(true); ?>

<p class="note">
<?php if ($path = $doc->class->getFilename()): ?>
Class declared in <tt><?php echo Core::debug_path($path) ?></tt> on line <?php echo $doc->class->getStartLine() ?>.
<?php else: ?>
Class is not declared in a file, it is probably an internal <?php echo HTML::anchor('http://php.net/manual/class.'.strtolower($doc->class->name).'.php', 'PHP class') ?>.
<?php endif ?>
</p>



<?php if ($doc->constants): ?>
<div class="constants">
	<h1 id="constants"><?php echo __('Constants'); ?></h1>
	<dl>
	<?php foreach ($doc->constants as $name => $value): ?>
	<h2>
	<dt id="constant:<?php echo $name ?>"><?php echo $name ?></dt>
	</h2>
	<dd><?php echo $value ?></dd>
	<?php endforeach; ?>
	</dl>
</div>
<?php endif ?>

<?php if ($properties = $doc->properties()): ?>
<div class="properties">
	<h1 id="properties"><?php echo __('Properties'); ?></h1>
	<dl>
	<?php foreach ($properties as $prop): ?>
	<h2>
	<dt id="property:<?php echo $prop->property->name ?>"><?php echo $prop->modifiers ?> <code><?php echo $prop->type ?></code> $<?php echo $prop->property->name ?></dt>
	</h2>
	<dd><?php echo $prop->description ?></dd>
	<dd><?php echo $prop->value ?></dd>
	<?php endforeach ?>
	</dl>
</div>
<?php endif ?>

<?php if ($methods = $doc->methods()): ?>
<h1 id="methods"><?php echo __('Methods'); ?></h1>
<div class="methods">
<?php foreach ($methods as $method): ?>
<?php View::factory('docs/api/method')->set('method', $method)->set('dir',$dir)->render(true); ?>
<?php endforeach ?>
</div>
<?php endif ?>

</div>