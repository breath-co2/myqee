(function()
{
    // 防止左侧菜单太长
    $(window).resize(function(){
        $('.bs-docs-sidenav').css('maxHeight', $(window).height()-docConfig.pageTop);
    }).trigger('resize');

    // 优化滚动菜单后切换页面
    if (localStorage)
    {
        var url = document.location.href.split('#')[0].split('?')[0];
        var path = url.substr(0, url.lastIndexOf('/')+1);

        var scroll = localStorage.getItem('left-div-scroll');
        if (scroll)
        {
            scroll = scroll.split('|');
            if (scroll[0] == path)
            {
                $('.bs-docs-sidenav').scrollTop(scroll[1]);
            }
        }

        var tm = null;
        $('.bs-docs-sidenav').bind('scroll', function(){
            if (tm)
            {
                clearTimeout(tm);
                tm = null;
            }

            var top = this.scrollTop;
            tm = setTimeout(function(){
                localStorage.setItem('left-div-scroll', path+'|'+top);
            },50);
        });
    }

    // 处理menu相关代码
    var href = document.location.href.split('#')[0].split('?')[0];
    $('.left-div a').each(function(){
        if (this.href==href)
        {
            $(this.parentNode).addClass('active');
        }
        this.innerHTML = '<div class="left-menu-i-div"><i class="icon-chevron-right"></i></div>'+this.innerHTML;
    });
    $('.left-div .top-menu i').addClass('icon-chevron-up');

    $('.left-div ul').each(function(){
        var i = 0;
        $(this).children('li').each(function(){
        var c = this.childNodes;
        var len = c.length;
        var k = 0, html;
        for(var ii=0; ii<len; ii++)
        {
            if (typeof c[k]=='undefined')
            {
                k++;
                continue;
            }

            if (!c[k].tagName)
            {

                if (i==0)
                {
                    html = '<a href="javascript:" class="text menu_title"></a>';
                }
                else
                {
                    html = '<a href="javascript:" class="text"></a>';
                }

                var tmpObj = $(html).insertAfter(c[k]);
                $(c[k]).appendTo(tmpObj);
                tmpObj = null;
            }
        }
        i++;
    });
    });

    $('.bs-docs-sidenav li.active > a > .badge-info').removeClass('badge-info').addClass('badge-important');

    // 处理缩进
    var pd = function(obj, nn){
        obj.children('li').each(function(){
            var oo = $(this);
            if (nn>0)
            {
                oo.children('a').css('paddingLeft', (nn+1)+'em');
            }
            pd(oo.children('ul'), nn+1);
        });
    }
    pd($('.bs-docs-sidenav > ul'), 0);

    $('.left-div hr').remove();



    (function()
    {
        var t = $(window).width() <= 980 ? 70 : 0;
        $(window).bind('resize',function(){t=$(window).width()<=980?70:0;});

        setTimeout(function (){
            $('.bs-docs-sidenav').affix({offset: {top: function(){return t;}, bottom: docConfig.pageBottom || 0}})
        }, 100);
    })();
})();