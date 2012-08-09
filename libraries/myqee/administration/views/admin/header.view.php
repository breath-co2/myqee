<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Language" content="zh-cn" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $page_title;?></title>
<script type="text/javascript">
var MyQEE = {};
MyQEE.Url = {
    Site    : '<?php echo rtrim(Core::url(''),'/');?>',
    Statics : '<?php echo rtrim(Core::url('statics'),'/');?>'
};
</script>
<script type="text/javascript" src="<?php echo Core::url('statics/js/global.js');?>"></script>
<script type="text/javascript" src="<?php echo Core::url('statics/jquery/js/jquery-1.6.2.min.js');?>"></script>
<script type="text/javascript" src="<?php echo Core::url('statics/js/ymPrompt/ymPrompt.js');?>"></script>
<link rel="stylesheet" type="text/css" href="<?php echo Core::url('statics/js/ymPrompt/skin/simple/ymPrompt.css');?>" />
<link rel="stylesheet" type="text/css" href="<?php echo Core::url('statics/css/global.css');?>" />
<script type="text/javascript" src="<?php echo Core::url('statics/js/admin_header.js');?>"></script>
<!--[if (gte IE 6)&(lte IE 8)]>
  <script type="text/javascript" src="<?php echo Core::url('statics/js/selectivizr-min.js');?>"></script>
<![endif]-->
</head>
<body>
<iframe width="1" height="1" name="hiddenFrame" id="hiddenFrame" style="display:none;"></iframe>
<!--
header - begin
-->
<div id="div_header">
    <!--[if lt IE 7]><iframe border="0" id="for_ie6_top_frame"></iframe><![endif]-->
    <div id="topdiv">
        <div class="topbg">
            <div class="mainWidth clear">
                <div id="logo">
                    <?php $logurl = Core::url('statics/images/logo.png');?>
                    <img src="<?php echo $logurl;?>" style="_display:none;" />
                    <img src="<?php echo  Core::url('statics/images/spacer.gif');?>" style="display:none;_display:inline;_filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true,sizingMethod=image,src=<?php echo $logurl;?> );" alt="Logo" />
                </div>
                <div id="top_info_div">
                    <?php
                    $username = Session::instance()->member()->username;
                    if ($username)
                    {
                    ?>
                    <font style="color:#666;">
                        欢迎您：<?php
                        echo $username;
                        if ( Session::instance()->member()->is_super_admin )
                        {
                            echo ' (<span style="color:#ff9600">超管</span>)';
                        }
                        elseif ( Session::instance()->member()->perm()->is_own('administrator.is_group_manager') )
                        {
                            echo ' (<span style="color:#ff9600">组长</span>)';
                        }
                        ?>
                    </font>
                    <span id="user_link_div">
                    <?php
                    $menu_arr = Core::config('admin/top_menu');
                    if ($menu_arr)foreach ( $menu_arr as $item)
                    {
                        if (!isset($item['innerHTML']))continue;
                        if ( isset($item['href']) )
                        {
                            echo ' | <a href="'.Core::url($item['href']).'">'.$item['innerHTML'].'</a>';
                        }
                        else
                        {
                            echo ' | <span style="color:#666;">' . $item['innerHTML'] .'</span>';
                        }
                    }
                    ?>
                    </span> | <a href="<?php echo Core::url('login/out');?>">退出</a> |
                    <?php
                    }
                    ?>
                    <a href="<?php echo Core::url('/');?>">管理首页</a>
                </div>
            </div>
        </div>
        <div id="banner">
            <div class="mainWidth clear">
                <ul class="ul" id="menu_ul">
                <?php
                if ($admin_menu)foreach ($admin_menu as $k=>&$v){
                    if (is_array($v)){
                        if (isset($v['innerHTML'])){
                            echo '<li><a onclick="return change_menu(\''.$k.'\',this);"'.($k=='base1'?' class="hover"':'');
                            if ($k==$top_menu)echo ' class="hover"';
                            foreach ($v as $key=>$value){
                                if ($key=='innerHTML' || $key=='perm' || $key=='icon')
                                {
                                    continue;
                                }
                                elseif (is_string($value))
                                {
                                    echo ' '.$key.'="'.$value.'"';
                                }
                                elseif (!isset($v['href']) && is_array($value) && isset($value['href']))
                                {
                                    # 没有链接的话，取子菜单第一个
                                    echo ' href="'.$value['href'].'"';
                                    $v['href'] = $value['href'];
                                }
                            }
                            if (!isset($v['href'])) {
                                echo ' href="#"';
                            }
                            if ( isset($v['icon']) )
                            {
                                $html = '<img src="'.Core::url('statics/images/'.$v['icon']).'" />' .$v['innerHTML'];
                            }
                            else
                            {
                                $html = $v['innerHTML'];
                            }
                            echo '><strong>'.$html.'</strong></a></li>';
                        }
                    }
                }

                ?>
                </ul>
            </div>
        </div>
    </div>
    <div id="menutagdiv">
        <div class="menutagdiv">
            <ul class="menutagul ul" id="menutagdiv_ul">
                <li style="float:right;width:64px;padding-right:5px;">
                    <div id="go_and_back_button"><a id="button_back" href="#" onclick="window.history.back();return false;" title="转到上一页"></a><a id="button_renew" href="#" onclick="window.location.reload();return false;" title="刷新本页"></a><a href="#" id="button_go" onclick="window.history.forward();return false;" title="转到下一页"></a></div>
                </li>
                <?php
                if ($quick_menu){
                ?>
                <li style="float:right;padding-right:5px;">
                    <div id="menutag_more">
                        <style type="text/css">
                            #menutag_more:hover{height:<?php echo 18*count($quick_menu);?>px;}
                        </style>
                        <ul class="menutag_more ul">
                            <?php
                            foreach ($quick_menu as $k=>$item)
                            {
                                echo '<li><a href="'.Core::url($k).'">' .$item. '</a></li>';
                            }
                            ?>
                        </ul>
                    </div>
                </li>
                <?php
                }
                ?>
                <li class="tag_menu"><font><strong><a href="<?php echo Core::url('/');?>">网站管理</a></strong></font></li>
<?php
$i=0;
$tmp_menu = $admin_menu;
foreach ($menu as $key){
    $i++;
    $tmp_menu = $tmp_menu[$key];
    if ($i==$this_key_len || !isset($tmp_menu['href']) ){
        echo '<li class="tag_menu hover"><font><strong style="cursor:default;">'.$tmp_menu['innerHTML'].'</strong></font></li>';
    }else{
        echo '<li class="tag_menu"><font><strong><a href="'.$tmp_menu['href'].'">'.$tmp_menu['innerHTML'].'</a></strong></font></li>';
    }
}

$i=0;
$location_count = count($location);
if ($location)foreach ($location as $value){
    $i++;
    if ( is_array($value) )
    {
        $tag = isset($value['href'])?'a':'span';
    }
    else
    {
        $tag = 'span';
    }
    echo "\r\n".'<li'.($location_count==$i?' class="tag_menu hover"':'tag_menu').'><font><strong'.($tag=='span'?' style="cursor:default;"':'').'>';

    if ( is_array($value) )
    {
        echo '<'.$tag.' ';
        foreach ($value as $k=>$v)
        {
            if ($k=='innerHTML')continue;
            echo ' ' . $k.'="'.$v.'"';
        }
        echo '>'.$value['innerHTML'].'</'.$tag.'>';
    }
    else
    {
        echo (string)$value;
    }

    echo '</strong></font></li>';
}
;?>
            </ul>
        </div>
    </div>
</div>
<script type="text/javascript">
//更新前进后退菜单
setTimeout(function(){
    var obj_go = MyQEE.$('button_go');
    var obj_back = MyQEE.$('button_back');
    if (!document.referrer)
    {
        obj_back.style.opacity=0.3;
        obj_back.style.filter='alpha(opacity=30)';
    }
},10);

</script>
<!--
header - end
-->
<!--
leftmenu - begin
-->
<div id="leftmenudiv">
    <div id="leftmenubar" title="收起、展开左侧菜单" onclick="open_close_left();"></div>
    <div id="leftmenu_srcollbar" style="position:absolute;z-index:1;"></div>
    <div id="leftmenu_top_line"></div>
    <div id="leftmenu">
<!--        <div id="leftmenutitle"><span><font id="leftmenutext"></font></span></div>-->
        <div style="padding-bottom:100px;">
        <ul id="leftmenulink" class="ul"></ul>
        </div>
    </div>
</div>

<script type="text/javascript">
var myqee_top_menu = '<?php echo $top_menu;?>';
var myqee_menu = <?php echo json_encode($menu)?>;
(function()
{
    MyQEE.$import(MyQEE.Url.Site+'/admin_menu?i=<?php echo Session::instance()->member()->id;?>&n=<?php echo Session::instance()->member()->login_num;?>',function(){
        reset_left_scroll_top();
        init_left_scroll();
        scroll_left_menu();
    });
})();
</script>
<!--
leftmenu - end
-->
<div id="maindiv_leftline"></div>
<div id="maindiv_rightline"></div>

<script type="text/javascript">
ini_header();
</script>

<div class="clear" style="height:0;overflow:hidden;"></div>

<div id="maindiv">
<script type="text/javascript">
    window.onresize();
</script>

<table style="width:100%" width="100%" border="0" cellpadding="0" cellspacing="0">
<tr>
<td id="maindiv_td">