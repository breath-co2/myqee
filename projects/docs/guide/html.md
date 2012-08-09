HTML输出
==========

HTML::chars($value, $double_encode = TRUE)
----------
对字符串进行转义，防止XSS攻击


HTML::entities($value, $double_encode = TRUE)
----------


HTML::anchor($uri, $title = NULL, array $attributes = NULL, $protocol = NULL)
----------
输出一个超链接

    echo HTML::anchor('/user/profile', 'My Profile');


HTML::email($email)
----------
将Email进行随机转义处理后返回，可有效的防止页面内容被收集Email的程序抓取

    echo HTML::email('myname@test.com');


HTML::mailto($email)
----------
输出一个Email的超链接，并且Email会通过HTML::email()处理

    echo HTML::mailto('myname@test.com');


HTML::obfuscate($string)
----------
对字符串进行随机转义处理，内容将被转成&#...或&#x...开头的HTML形式


HTML::style($file, array $attributes = NULL, $index = FALSE)
----------
输出一个样式代码

    echo HTML::style('media/css/screen.css');


HTML::script($file, array $attributes = NULL, $index = FALSE)
-----------
输出一个javascript


HTML::image($file, array $attributes = NULL, $index = FALSE)
----------
输出一个图片