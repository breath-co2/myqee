<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Language" content="zh-cn" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>欢迎使用MyQEE PHP框架</title>
<style type="text/css">

html{
    background-color:#fff;
}
body {
    font:12px/1.5 Verdana,Helvetica,Arial,sans-serif;
    padding:0;
    margin:0px auto;
    background-color:#fff;
    color:#333;
    width:800px;
}
a:link,a:visited{
    color:#333;
    border-bottom:1px dotted #333;
    text-decoration:none;
}
a:hover,a:active {
    color:#d20000;
    border-bottom:1px solid #d20000;
    text-decoration:none;
}
h1{
    font-size:22px;
    font-weight:bold;
    text-align:center;
}
table
{
    border:1px solid #bfbfbf;
    empty-cells:show;
    border-collapse:collapse;
    width:100%;
    border-spacing:0;
}
table td,
table th{
    border:1px solid #bfbfbf;
    padding:4px;
}
.th,
table th{
    font-size:13px;
    line-height: 1.6em;
    font-weight: bold;
    text-align: center;
    background: #ddd;
    background: -webkit-gradient(linear, 0 0, 0 bottom, from(#eee), to(#ddd));
    background: -moz-linear-gradient(#eee, #ddd);
    background: linear-gradient(#eee, #ddd);
    white-space:nowrap;
}
table .td1{background:#eee;}

table tr:hover
{
    background:#f5f5f5;
}
table tr:hover .td1{
    background:#e9e9e9;
}
table tr.control{
    background:#f3f3f3;
}
table tr.control:hover{
    background:#f0f0f0;
}
table.infoTable{
    border:1px solid #ffcc00;
}
table.infoTable td,
table.infoTable th
{
    border:1px solid #6ca400;
    background:#c7eb81;
    color:#418400;
    padding:4px 6px;
}
table.errTable{
    border:1px solid #ffcc00;
}
table.errTable td,
table.errTable th
{
    border:1px solid #ffcc00;
    background:#fffb94;
    color:#ff5400;
    padding:4px 6px;
}
table.errTable a:link,table.errTable a:visited{
    color:#ff5400;
    border-bottom:1px dotted #ff5400;
}
table.errTable a:hover,table.errTable a:active {
    color:red;
    border-bottom:1px solid red;
}
</style>
<script type="text/javascript">
var check_config = function()
{
    this.url = '<?php echo Core::url('check_config?time='.TIME);?>';
    this.onCompletion = function()
    {
        if (this.responseJSON)
        {
            if (this.responseJSON['status']==1)
            {
                document.getElementById('check_err').style.display='none';
                document.getElementById('check_ok').style.display='';
                return true;
            }
        }
        this.onError();
    }
    this.onError = function()
    {
        document.getElementById('check_err').style.display='';
        document.getElementById('check_ok').style.display='none';
    }

    this.xmlhttp = null;
    this.responseStatus = new Array(2);
    try {
		this.xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
	} catch (e1) {
		try {
			this.xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
		} catch (e2) {
		}
	}

	if (! this.xmlhttp) {
		if (typeof XMLHttpRequest != "undefined") {
			this.xmlhttp = new XMLHttpRequest();
		}
	}

	this.xmlhttp.open('GET', this.url, true);

    try {
        this.xmlhttp.setRequestHeader("X-Requested-With", "XMLHttpRequest");
    } catch (e) { }

    var self = this;

    this.xmlhttp.onreadystatechange = function() {
		switch (self.xmlhttp.readyState) {
			case 4:
				self.response = self.xmlhttp.responseText;
				self.responseJSON = false;
				if (self.response.substr(0,1)=='{'||self.response.substr(0,1)=='['){
					try{
					    self.responseJSON = eval('('+self.response+')');
					}catch(e){
					    self.responseJSON = false;
					}
				}
				self.responseXML = self.xmlhttp.responseXML;
				self.responseStatus[0] = self.xmlhttp.status;
				self.responseStatus[1] = self.xmlhttp.statusText;

				if (self.execute) {
					self.runResponse();
				}

				if (self.responseStatus[0] == "200") {
					self.onCompletion();
				} else {
					self.onError();
				}

				self.URLString = "";

				//销毁对象防止内存溢出
				self.xmlhttp = null;
				break;
		}
	};

	this.xmlhttp.send();
}

</script>
</head>
<body>
<h1>欢迎使用MyQEE PHP多项目开发框架</h1>

<table class="errTable" id="check_err" style="display:none;">
<tr>
    <td>
    当前的配置有异常：可能未正确配置 Rewrite，请参照 <a href="http://www.myqee.com/docs/base.config.html" target="_blank">http://www.myqee.com/docs/base.config.html</a> 进行配置。
    <br />
    也可简单的将系统目录下的example.htaccess重命名为.htaccess文件即可。
    </td>
</tr>
</table>
<table class="infoTable" id="check_ok" style="display:none;">
<tr>
    <td>
    恭喜你已成功配置了MyQEE。
    </td>
</tr>
</table>
<script type="text/javascript">
new check_config();
</script>
<br />
<table>
<tr>
    <th colspan="2">基本信息</th>
</tr>
<tr>
    <td class="td1" align="right">PHP 版本：</td>
    <td><?php echo PHP_VERSION; ?></td>
</tr>
<tr>
    <td class="td1" align="right">MyQEE 版本：</td>
    <td>
    <?php echo Core::VERSION; ?>
    </td>
</tr>
<tr>
    <td class="td1" align="right">系统目录：</td>
    <td>
    <?php echo DIR_SYSTEM; ?>
    </td>
</tr>
<tr>
    <td class="td1" align="right">项目目录：</td>
    <td>
    <?php echo DIR_PROJECT; ?>
    </td>
</tr>
<tr>
    <td class="td1" align="right">Team类库目录：</td>
    <td>
    <?php echo DIR_TEAM_LIB; ?>
    </td>
</tr>
<tr>
    <td class="td1" align="right">第三方开源类库目录：</td>
    <td>
    <?php echo DIR_LIBRARY; ?>
    </td>
</tr>
<tr>
    <td class="td1" align="right">WWWROOT目录：</td>
    <td>
    <?php echo DIR_WWWROOT; ?>
    </td>
</tr>
<tr>
    <td class="td1" align="right">数据目录：</td>
    <td>
    <?php echo DIR_DATA; ?>
    </td>
</tr>
</table>

<br />

<table>
<tr>
    <th>访问内置项目</th>
</tr>
<tr>
    <td>
    <ul>
        <li><a href="docs/">手册</a></li>
        <li>
        <a href="admin/">通用管理后台</a>
        &nbsp;
        &nbsp;
        (需要先安装才可使用，<a href="admin/install/">点击这里安装</a>)
        </li>
    </ul>
    </td>
</tr>
</table>

</body>
</html>