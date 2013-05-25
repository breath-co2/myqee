<style type="text/css">
.filetree,.filetree ul,.filetree li{list-style:none;padding:0;margin:0;}
.filetree li.dir{background:url(<?php echo Core::$base_url;?>/~docs~img/dir_png.gif) no-repeat 3px 3px;padding-left:22px;line-height:1.6em;}
.filetree ul.dir2 li.phpfile{padding-left:18px;background:url(<?php echo Core::$base_url;?>/~docs~img/php_file.gif) no-repeat 2px 2px;}
.filetree ul.dir2 li.dir2{padding-left:18px;background:url(<?php echo Core::$base_url;?>/~docs~img/dir_png.gif) no-repeat 0 2px;}
#file_tree_show_menu{display:none;width:30px;text-align:center;}
.main_center{float:right;margin:0 5px 0 0;}
#filetree_main_div{
    width:166px;
    overflow:auto;
}
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
    if (!obj_left||!obj_left_main)return true;
    var header_height = <?php echo $header_height;?>;
    var bottom_height = <?php echo $bottom_height;?>;
    var title_height = 40;
    var h = dom.clientHeight;
    var real_h = dom.scrollHeight;
    var s_top = dom.scrollTop||document.body.scrollTop;
    var max_scroll = obj_left_main.clientHeight+bottom_height;
    var max_height = real_h-bottom_height-header_height;
    if (real_h-s_top-title_height-20<max_scroll)
    {
        max_scroll =  obj_left.clientHeight+bottom_height;
        obj_left.style.position = 'absolute';
        obj_left.style.top = (real_h-max_scroll)+'px';
        obj_left.style.bottom = '';
        obj_left_main.style.height = Math.min(h,max_height)-title_height-3+'px';
    }
    else if (s_top>header_height){
        obj_left.style.position = 'fixed';
        obj_left.style.top = 0;
        obj_left.style.bottom = '';
        obj_left_main.style.height = Math.min(h,max_height)-title_height-3+'px';
    }
    else if (s_top<header_height && real_h-bottom_height-s_top>=h)
    {
        obj_left.style.position = 'absolute';
        obj_left.style.top = (header_height-3) + 'px';
        obj_left.style.bottom = '';
        obj_left_main.style.height = Math.min((h-header_height+s_top),max_height)-title_height+'px';
    }
    else
    {
        obj_left.style.position = '';
        obj_left.style.top = '';
        obj_left.style.bottom = '';
    }
}

</script>

<div id="filetree_div">
    <div id="file_tree_show_menu">
        <div class="box_2">查看文件列表</div>
    </div>
    <div class="box_2" id="file_tree_show_div">
        <h1>PHP Explorer</h1>
        <div style="white-space:nowrap;wdith:160px;overflow:auto;" id="filetree_main_div">
            <ul class="filetree">
<?php
$nowfilepath = $path.'/'.$nowfile;
function showlist($files,$nowfilepath,$project,$path)
{
	echo '<ul class="dir2">';
	if ($files)foreach ($files as $k=>$v){
		if(is_array($v) || $v===true){
			$itempath = substr($k,strlen($path.'/'));
			if (!$itempath)continue;
			echo '<li class="dir2">';
			if ($k==$nowfilepath){
				echo '<b>'.$itempath.'</b>';
			}else{
				echo '<a href="'.Docs::url($k,true,true).'">'.substr($k,strlen($path.'/')).'</a>';
			}

			if (is_array($v))showlist($v,$nowfilepath,$project,$path.'/'.$itempath);
			echo '</li>';
		}else{
			$fname = substr($k,strlen($path.'/'));
			if ($k==$nowfilepath){
				echo '<li class="phpfile"><b>'.$fname.'</b></li>';
			}else{
				echo '<li class="phpfile"><a href="'.Docs::url($k,true,true).'">'.$fname.'</a></li>';
			}
		}
	}
	echo '</ul>';
}

foreach (array(
	'controllers'=>'控制器 (Controllers)',
	'classes'=>'类库(Classes)',
	'models'=>'模型 (Models)',
	'orm'=>'ORM',
	'config'=>'配置 (config)',
	'i18n'=>'语言包 (i18n)',
) as $key=>$value){
	echo '<li class="dir">';
	if ($key==$path){
		echo '<b><a href="'.Docs::url($key.'/',true,true).'">'.$value.'</a></b>';
		showlist($files,$nowfilepath,$project,$path);
	}else{
		echo '<a href="'.Docs::url($key.'/',true,true).'">'.$value.'</a>';
	}
	echo '</li>';
}
?>
            </ul>
        </div>

    </div>
</div>
