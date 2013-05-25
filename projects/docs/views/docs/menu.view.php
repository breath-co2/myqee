<div id="left_menu_div">
    <div class="box_2">
        <h1>手册目录</h1>
        <div id="left_menu_main_div" style="width:166px;height:300px;overflow:auto;">
<?php
echo Markdown( file_get_contents( $menu ) );

$site_view = Core::config('core.site_views');
$header_height = (int)$site_view['header_height'];
$bottom_height = (int)$site_view['bottom_height'];
?>
        </div>
    </div>
</div>
<div>&nbsp;</div>
<script type="text/javascript">
(function()
{
	var href = document.location.href.split('#')[0].split('?')[0];
	var objs = document.getElementById('left_menu_div').getElementsByTagName('a');
	for(var i=0;i<objs.length;i++)
	{
    	var obj = objs[i];
    	if (obj.href==href)
    	{
    	    obj.parentNode.className = 'active';
    	    break;
    	}
	};

	$(document).ready(function()
    {
	    var key = 'doc_left_menu_scroll';
    	window.onresize = window.onscroll = function()
    	{
    	    var dom = (document.compatMode &&document.compatMode=="CSS1Compat")?document.documentElement:document.body;
    	    var obj_left = document.getElementById('left_menu_div');
    	    var obj_left_main = document.getElementById('left_menu_main_div');
    	    if (!obj_left)return true;
    	    var header_height = <?php echo $header_height;?>;
    	    var bottom_height = <?php echo $bottom_height;?>;
    	    var title_height = 40;
    	    var h = dom.clientHeight;
    	    var real_h = dom.scrollHeight;
    	    var s_top = dom.scrollTop||document.body.scrollTop;
    	    var max_scroll = obj_left_main.clientHeight+bottom_height;
    	    var max_height = real_h-bottom_height-header_height;
    	    if (real_h-s_top-title_height<max_scroll)
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
    	        obj_left_main.style.height = (max_height-bottom_height-title_height)+'px';
    	        obj_left.style.position = '';
    	        obj_left.style.top = '';
    	        obj_left.style.bottom = '';
    	    }

    	    if (window.localStorage)
    	    {
    	        if (!obj_left_main.onscroll)obj_left_main.onscroll = function(){
        	        if (this._rt)
        	        {
            	        clearTimeout(this._rt);
            	        this._rt = null;
        	        }
    	    	    this._rt = setTimeout(function(){
        	    	    var s = obj_left_main.scrollTop;
        	    	    if (s>0)
        	    	    {
        	    	        localStorage.setItem(key,s);
        	    	    }
        	    	    else
        	    	    {
        	    	        localStorage.removeItem(key);
        	    	    }
    	    	    },300);
            	};

    	    }
    	}

    	window.onscroll();

        if (window.localStorage)
	    {
            var s = localStorage.getItem(key);
            document.getElementById('left_menu_main_div').scrollTop = s;
	    }
	});
})();
</script>