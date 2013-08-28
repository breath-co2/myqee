define(function(require, exports, module) {
  module.exports = function()
  {
    $('.right-div > .content-box').css('minHeight', Math.max($('.left-div .bs-docs-sidenav').height(), $(window).height())+docConfig.pageTop+docConfig.pageBottom);
    // // var resize = function()
    // {
    //     $('.bs-docs-sidenav').css('maxHeight', $(window).height()-docConfig.pageTop);
    // }
    // $(window).bind('resize',resize);
    // resize();

    $('.right-div p').each(function(){
        // 移除空行
        if (this.innerHTML=='&nbsp;'||this.innerHTML==' ')$(this).remove();
    });

    $('.right-div > .content > blockquote').each(function() {
      var c = $(this).children('table, h1, h2, h3, h4, h5, h6, blockquote, pre, ul, ol, div');
      if (c.length===0)
      {
        $(this).addClass('normal');
      }
    });

    // 给所有的表格加上css
    $('.right-div table').addClass('table-bordered table-striped');

    $('.right-div > hr').each(function(){
        if ($(this).prev()[0].tagName=='BLOCKQUOTE'||$(this).next()[0].tagName=='BLOCKQUOTE')
        {
            $(this).remove();
        }
    });

    var get_host = function(url) {
        var host = "null";
        if(typeof url == "undefined" || null == url)
        {
            url = window.location.href;
        }
        var regex = /.*\:\/\/([^\/]*).*/;
        var match = url.match(regex);
        if(typeof match != "undefined" && null != match)
        {
            host = match[1];
        }
        return host;
    }
    $('.right-div a').each(function()
    {
        var href = $(this).attr('href');
        if (href.indexOf('://')!=-1)
        {
            var host = get_host(this.href);
            if (host!=window.location.host)
            {
                this.target = '_blank';
            }
        }
    });
  };
});




// 加载高亮代码
seajs.use(['syntaxhighlighter/styles/shCoreEmacs.css', 'syntaxhighlighter/scripts/shCore.js', 'syntaxhighlighter/scripts/shAutoloader.js'], function()
{
    $('.right-div').tooltip({selector: "*[data-toggle=tooltip]"});

    $('.right-div pre:not(.debug) code').each(function()
    {
        if (!this.className || this.className.indexOf('brush:')===-1)
        {
            $(this).addClass('brush: php');
        }
    });

    var path = function ()
    {
      var args = arguments,
       result = []
      ;
      for(var i = 0; i < args.length; i++)
      {
          result.push(args[i].replace('@', seajs.data.base + '/syntaxhighlighter/scripts/'));
      }

      return result
    };
     
    SyntaxHighlighter.config.tagName = 'code';
    SyntaxHighlighter.defaults.toolbar = false;
    SyntaxHighlighter.defaults.gutter = false;
    SyntaxHighlighter.autoloader.apply(null, path(
      'applescript            @shBrushAppleScript.js',
      'actionscript3 as3      @shBrushAS3.js',
      'bash shell             @shBrushBash.js',
      'coldfusion cf          @shBrushColdFusion.js',
      'cpp c                  @shBrushCpp.js',
      'c# c-sharp csharp      @shBrushCSharp.js',
      'css                    @shBrushCss.js',
      'delphi pascal          @shBrushDelphi.js',
      'diff patch pas         @shBrushDiff.js',
      'erl erlang             @shBrushErlang.js',
      'groovy                 @shBrushGroovy.js',
      'java                   @shBrushJava.js',
      'jfx javafx             @shBrushJavaFX.js',
      'js jscript javascript  @shBrushJScript.js',
      'perl pl                @shBrushPerl.js',
      'php                    @shBrushPhp.js',
      'text plain             @shBrushPlain.js',
      'py python              @shBrushPython.js',
      'ruby rails ror rb      @shBrushRuby.js',
      'sass scss              @shBrushSass.js',
      'scala                  @shBrushScala.js',
      'sql mysql              @shBrushSql.js',
      'vb vbnet               @shBrushVb.js',
      'yaml yml               @shBrushYaml.js',
      'xml xhtml xslt html    @shBrushXml.js'
    ));
    SyntaxHighlighter.all();

});