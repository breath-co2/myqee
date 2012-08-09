function open_close_left()
{
    var leftmenu = MyQEE.$('leftmenu');
    var leftmenudiv = MyQEE.$('leftmenudiv');
    var maindiv = MyQEE.$('maindiv');
    var maindiv_leftline = MyQEE.$('maindiv_leftline');
    var leftmenubar = MyQEE.$('leftmenubar');
    var menutagdiv_ul = MyQEE.$('menutagdiv_ul');
    var leftmenubar = MyQEE.$('leftmenubar');
    if (leftmenu.isclose)
    {
        leftmenu.isclose = false;
        leftmenu.style.marginLeft = '0';
        leftmenudiv.style.left = '0';
        maindiv.style.marginLeft = '170px';
        maindiv_leftline.style.left = '170px';
        leftmenubar.style.backgroundPosition = '1px center';
        menutagdiv_ul.style.marginLeft = '170px';
        MyQEE.localStorage.removeItem('left_menu_hidden');
    }
    else
    {
        leftmenu.isclose = true;
        leftmenu.style.marginLeft = '-300px';
        leftmenudiv.style.left = '-160px';
        maindiv.style.marginLeft = '10px';
        maindiv_leftline.style.left = '10px';
        leftmenubar.style.backgroundPosition = '-28px center';
        menutagdiv_ul.style.marginLeft = '10px';
        MyQEE.localStorage.setItem('left_menu_hidden',1);
    }
}


function show_or_hidden_menu(obj_id)
{
    var obj = MyQEE.$('_left_menu'+obj_id);
    if (!obj)return false;
    var setdata = function()
    {
        var data = MyQEE.localStorage.getItem('left_menu_fav') || ',';
        if (obj.style.display=='none')
        {
            data += obj_id + ',';
        }
        else
        {
            var re = new RegExp (','+obj_id+',','g');
            data = data.replace(re,',');
        }
        if (data==',')
        {
            MyQEE.localStorage.removeItem('left_menu_fav');
        }
        else
        {
            MyQEE.localStorage.setItem('left_menu_fav',data);
        }
    }
    MyQEE.resizeDiv(obj,setdata);
}


var _myqee_admin_menu = null;
function change_menu(menu_key,a_obj,show_key)
{
    var menu = _myqee_admin_menu;
    var obj = MyQEE.$('leftmenulink');
    var thismenu = menu[menu_key];
    show_key = show_key || [];
    if (!thismenu)return false;
    if (a_obj){
        var url = document.location.href.replace(/\#.*/,'');
        //存在链接，则直接页面跳转
        if (a_obj.href){
            if (a_obj.href==url+'#'){
                //空链接
            }else if (a_obj.href.replace(/\#.*/,'')==url){
                //相同链接，或略
            }else{
                //返回true，允许页面跳转到href指定url上
                return true;
            }
        }
        //已在当前菜单上，或略点击
        if (a_obj.className=='hover')return false;
    }

    var new_title = '';

    var show_key_len = 0;
    if (show_key)show_key_len = show_key.length;


    var show_html = function(keystr,arr,n,islast,leftstr,isfocus)
    {
        leftstr = leftstr||'';
        n=n||0;
        if(n==0)isfocus=show_key[0]==menu_key?true:false;
        var tmphtml = '';
        var tmpli = '';
        var tmparr = '';
        var len = 0;
        for (var k in arr){
            if (typeof arr[k] =='object'){
                len++;
            }
        }

        var i=0;
        for (var k in arr){
            if (typeof arr[k] =='object')
            {
                i++;
                tmpli += show_html(
                    keystr+'_'+k,
                    arr[k],
                    n+1,
                    i==len?true:false ,
                    n>1?(leftstr+(islast?'0':'3')):'' ,
                    isfocus?(k==show_key[n+1]?true:false):false
                );
            }
            else if (k!='innerHTML')
            {
                tmparr += ' '+k+'="'+arr[k]+'"';
            }
        }
        if (arr['innerHTML'])
        {
            if (typeof arr['title'] == 'undefined')
            {
                tmparr += ' title="'+arr['innerHTML']+'"';
            }
            if (n==0){
                new_title = arr['innerHTML'];
            }else{
                var tagleft = '';
                var tagright = '';
                for(var i=0;i<leftstr.length;i++)
                {
                    tagleft += '<div class="menu_tree_'+leftstr.substr(i,1)+'">';
                    tagright += '</div>';
                }
                if (arr['href']){
                    tmparr = '<a'+tmparr+'>'+arr['innerHTML']+'</a>';
                }else{
                    tmparr = '<font'+tmparr+'>'+arr['innerHTML']+'</font>';
                }
                tmparr = tagleft + (n==1||tmpli?'':'<div class="right_nav"></div>') +'<div class="menu_tree_'+(n==1?'title':(islast?'2':'1'))+'">' + tmparr + '</div>' + tagright;
                tmphtml += '<li'
                    +' class="menu_tree_li'
                    +(tmpli?' menu_tree_btn':'')
                    +(n+1==show_key_len && isfocus?' hover':'')
                    +'">'
                    +(tmpli?'<div class="show_hidden_button" title="展开/收缩子菜单" onclick="show_or_hidden_menu(\''+keystr+'\')"></div>':'')
                    +tmparr
                    +'</li>';
            }
        }
        if (tmpli){
            if (n>0)
            {
                var left_menu_fav = MyQEE.localStorage.getItem('left_menu_fav')||'';
                tmphtml += '<ul'+(left_menu_fav.indexOf(','+keystr+',')!=-1?' style="display:none;"':'')+' class="ul menu_tree_block" id="_left_menu'+keystr+'">'+tmpli+'</ul>';
            }
            else
            {
                tmphtml += tmpli;
            }
        }

        return tmphtml;
    }


    var html = show_html('',thismenu);

    if (html){
        obj.innerHTML = html;
        if (a_obj){
            var all_a = MyQEE.$('menu_ul').getElementsByTagName('a');
            for(var i=0;i<all_a.length;i++){
                all_a[i].className = '';
            }
            a_obj.className = 'hover';
        }
        MyQEE.$('leftmenu').scrollTop = 0;
        
        scroll_left_menu();
        return false;
    }else{
        return true;
    }
}

function ini_header()
{
if ( MyQEE.isinFrame() )
{
    //若页面在框架内，则隐藏多余的显示内容
    MyQEE.$('div_header').style.display='none';
    MyQEE.$('leftmenudiv').style.display='none';
    MyQEE.$('maindiv_leftline').style.display='none';
    MyQEE.$('maindiv_rightline').style.display='none';
    if (parent.MyQEE.$('menutagdiv')) parent.MyQEE.$('menutagdiv').innerHTML = MyQEE.$('menutagdiv').innerHTML;
}
else
{
    window.onscroll = function()
    {
        if (MyQEE.is_ie && MyQEE.ie<7)return;
        var obj_div = MyQEE.$('toolbar_div');
        var obj = MyQEE.$('toolbar_table');
        if (!obj_div || !obj)return;
        var dom = MyQEE.DOM();
        var main_height = MyQEE.$('maindiv').offsetHeight;
        var body_height = dom.clientHeight;
        var hh = dom.scrollHeight - (body_height+(dom.scrollTop||document.body.scrollTop)) - MyQEE.$('bottomdiv').offsetHeight - 9;
        if (body_height-main_height<101 && hh>0)
        {
            obj.style.position ='fixed';
            obj.style.bottom ='0';
            obj.style.width = obj_div.offsetWidth+'px';
            obj_div.style.height = obj.offsetHeight+'px';
        }
        else
        {
            obj.style.width = '';
            obj.style.position ='';
            obj.style.bottom ='';
        }
    }

    window.onresize = function(){
        var h = MyQEE.DOM().clientHeight;
        MyQEE.$('leftmenu').style.height =
        MyQEE.$('maindiv_leftline').style.height =
        MyQEE.$('maindiv_rightline').style.height = Math.max(h-85,300)+'px';
        var mh = (MyQEE.DOM().clientHeight - 119) + 'px';
        MyQEE.$('maindiv').style.minHeight = mh;

        if (MyQEE.is_ie)
        {
            if (MyQEE.ie<7)
            {
                MyQEE.$('maindiv').style.height = mh;
            }
        }

        window.onscroll();
    }
}
}


var leftMenuScrollTop = 0;
/**
 * 重设左侧滚动高度
 */
function reset_left_scroll_top(){
    leftMenuScrollTop = MyQEE.localStorage.getItem('left_menu_stop')||0;
    MyQEE.$('leftmenu').scrollTop = leftMenuScrollTop;
    MyQEE.$('leftmenu').onscroll= function()
    {
        var obj = MyQEE.$('leftmenu');
        if ( obj.scrollTop!=leftMenuScrollTop )
        {
            leftMenuScrollTop = obj.scrollTop;
            MyQEE.localStorage.setItem('left_menu_stop',leftMenuScrollTop);
        }
    };
    if (MyQEE.localStorage.getItem('left_menu_hidden')==1)
    {
        open_close_left();
    }
}



function show_left_srcoll()
{
    var o = MyQEE.$('leftmenu_srcollbar_c');
    if (!o)return;
    var obj = MyQEE.$('leftmenu');
    var div_h = obj.clientHeight;
    var h = obj.scrollHeight;
    var s = h-div_h;
    if (s<=0)
    {
        o.style.display = 'none';
        return;
    }
    o.style.display = '';
    var i=0;
    var hid_bar = function(){
        i++;
        if (i>10)
        {
            o.style.opacity = 0.5;
            o.style.display = 'none';
            clearTimeout(window._menu_scroll_tm);
            window._menu_scroll_tm = null;
        }
        else
        {
            o.style.opacity = 0.05*(10-i);
            o.style.filter = 'alpha(opacity='+(5*(10-i))+')';
            window._menu_scroll_tm = setTimeout(hid_bar,18);
        }
    }
    window._menu_scroll_tm = setTimeout(hid_bar,1000);
}
function scroll_left_menu(e)
{
    var o = MyQEE.$('leftmenu_srcollbar_c');
    if (!o)return;
    
    e = e || window.event;
    if (e)
    {
        if (e.wheelDelta)
        {
            var v = -e.wheelDelta/12;
        }
        else
        {
            var v = e.detail;
            v = v*v*v/2+v/2;
        }
    }
    else
    {
        var v = 0;
    }

    var obj = MyQEE.$('leftmenu');
    var div_h = obj.clientHeight;
    var h = obj.scrollHeight;
    var s = h-div_h;
    if (s<=0)
    {
        o.style.display = 'none';
        return;
    }
    else
    {
        o.style.display = '';
    }

    if (window._menu_scroll_tm)
    {
        clearTimeout(window._menu_scroll_tm);
        window._menu_scroll_tm = null;
        o.style.opacity = 0.5;
        o.style.filter = 'alpha(opacity=50)';
    }

    show_left_srcoll();

    o.style.height = Math.min((div_h*div_h/h) , div_h-6)+'px';
    var o_h = o.clientHeight;
    var o_s = div_h-o_h;

    var t = obj.scrollTop;
    if (v>0)
    {
        if (t+div_h>=h)
        {
            o._top = div_h-o_h-3;
            o.style.marginTop = o._top+'px';
            return;
        }
    }
    else
    {
        if (t==0)
        {
            o._top = 0;
            o.style.marginTop = '0px';
        }
    }

    obj.scrollTop = obj.scrollTop+v;

    t = Math.max(Math.min( o_s*obj.scrollTop/s , div_h-o_h-3 ) , 3);
    o.style.marginTop = t+'px';
    
    if (obj.onscroll)obj.onscroll();
}

function init_left_scroll()
{
    var show_scroll = false;
    if (MyQEE.userAgent.indexOf('mac os x')!=-1)
    {
        if (MyQEE.is_safari || MyQEE.is_chrome)
        {
            show_scroll = true;
        }
    }
    else if (MyQEE.userAgent.indexOf('ios')!=-1||MyQEE.userAgent.indexOf('android')!=-1)
    {
        show_scroll = true;
    }
    if (!show_scroll)
    {
        var obj = MyQEE.$('leftmenu');
        if (!obj)return;
        obj.onmousewheel = function(e){e=e||window.event;scroll_left_menu(e);return false;}
        obj.style.overflow = 'hidden';
        MyQEE.$('leftmenu_srcollbar').innerHTML = '<div id="leftmenu_srcollbar_c" style="display:none;z-index:1;position:absolute;margin-top:0;margin-left:142px;background:#000;opacity:0.5;filter:alpha(opacity=50);width:7px;height:200px;border-radius:4px;-moz-border-radius:4px;-khtml-border-radius:4px;-webkit-border-radius:4px;"></div>';
        document.body.onfocus = show_left_srcoll;
    }
}