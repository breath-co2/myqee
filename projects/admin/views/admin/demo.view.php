<!--[if lt IE 8.9]>
<font style="color:red">使用IE9+或Firefox4+或Chrome10+或Safari5+浏览器能得到最佳使用体验</font>
<![endif]-->
<style>
textarea{font-family:"Consolas","Bitstream Vera Sans Mono","Courier New",Courier,monospace !important}
</style>

<h1>通栏列表</h1>

效果：
<table class="mainTable">
    <tbody align="center">
        <tr>
            <th width="70">ID</th>
            <th>用户名</th>
            <th width="150">操作</th>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td>
                <input type="button" onclick="goto('/test');" value="按钮" />
                <input type="button" onclick="goto('/test');" value="按钮2" />
            </td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td>
                <input type="button" onclick="goto('/test');" value="按钮" />
                <input type="button" onclick="goto('/test');" value="按钮2" />
            </td>
        </tr>
        <tr>
            <td colspan="2" class="td1"></td>
            <td class="td1">
                <input type="button" class="submit" onclick="goto('/test');" value="提交按钮" />
            </td>
        </tr>
    </tbody>
</table>

<br />
代码如下：
<textarea style="width:96%;height:150px;">
<table class="mainTable">
    <tbody align="center">
        <tr>
            <th width="70">ID</th>
            <th>用户名</th>
            <th width="150">操作</th>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td>
                <input type="button" onclick="goto('/test');" value="按钮" />
            </td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td>
                <input type="button" onclick="goto('/test');" value="按钮" />
            </td>
        </tr>
        <tr>
            <td colspan="2" class="td1"></td>
            <td class="td1">
                <input type="button" class="submit" onclick="goto('/test');" value="提交按钮" />
            </td>
        </tr>
    </tbody>
</table>
</textarea>


<h1>两栏编辑模式</h1>

效果：
<table class="mainTable">
    <tr>
        <th colspan="2">编辑</th>
    </tr>
    <tr>
        <td width="150" align="right">标题：</td>
        <td>
            <?php echo Form::input('title');?>
        </td>
    </tr>
    <tr>
        <td align="right">时间：</td>
        <td>
            <?php echo Form::input_time('title',TIME);?>
        </td>
    </tr>
    <tr>
        <td class="td1"></td>
        <td class="td1">
            <input type="submit" onclick="goto('/test');" value="提交按钮" />
        </td>
    </tr>
</table>

<br />
代码如下：
<textarea style="width:96%;height:150px;">
<table class="mainTable">
    <tr>
        <th colspan="2">编辑：</th>
    </tr>
    <tr>
        <td width="150" align="right">标题：</td>
        <td>
            &lt;?php echo Form::input('title');?&gt;
        </td>
    </tr>
    <tr>
        <td align="right">时间</td>
        <td>
            &lt;?php echo Form::input_time('title',TIME);?&gt;
        </td>
    </tr>
    <tr>
        <td class="td1"></td>
        <td class="td1">
            <input type="submit" onclick="goto('/test');" value="提交按钮" />
        </td>
    </tr>
</table>
</textarea>


<h1>标签切换</h1>


<div class="tag">
    <ul class="ul">
        <li id="step_tag_1" onclick="MyQEE.tag(this.id,'step_tag','tag_main',this.parentNode,'hover')" class="hover">基本信息</li>
        <li id="step_tag_2" onclick="MyQEE.tag(this.id,'step_tag','tag_main',this.parentNode,'hover')">权限设置</li>
    </ul>
</div>

<div id="tag_main_1">
    <table class="mainTable">
        <tr>
            <th colspan="2">基本属性</th>
        </tr>
        <tr>
            <td width="150" align="right">标题：</td>
            <td>
                <?php echo Form::input('title');?>
            </td>
        </tr>
        <tr>
            <td align="right">时间：</td>
            <td>
                <?php echo Form::input_time('title',TIME);?>
            </td>
        </tr>
        <tr>
            <td class="td1"></td>
            <td class="td1">
                <input type="submit" onclick="goto('/test');" value="提交按钮" />
            </td>
        </tr>
    </table>
</div>

<div id="tag_main_2" style="display:none;">
    <table class="mainTable">
        <tr>
            <th colspan="2">高级属性</th>
        </tr>
        <tr>
            <td width="150" align="right">高级内容：</td>
            <td>
                <?php echo Form::input('title');?>
            </td>
        </tr>
        <tr>
            <td class="td1"></td>
            <td class="td1">
                <input type="submit" onclick="goto('/test');" value="提交按钮" />
            </td>
        </tr>
    </table>
</div>

<br />
<br />
代码如下：
<textarea style="width:96%;height:200px;">
<!--
要领：
tag的li上加入onclick="MyQEE.tag(this.id,'step_tag','tag_main',this.parentNode,'hover')"
其中step_tag和tag_main表示标题和内容部分的id前缀，即step_tag_a会显示tag_main_a的内容，step_tag_1会显示tag_main_1的内容
this.parentNode表示查找的li范围为父标签，即ul，不传则为document.body
hover表示当前焦点的样式名称，不传则为now
 -->
<div class="tag">
    <ul class="ul">
        <li id="step_tag_a" onclick="MyQEE.tag(this.id,'step_tag','tag_main',this.parentNode,'hover')" class="hover">基本信息</li>
        <li id="step_tag_b" onclick="MyQEE.tag(this.id,'step_tag','tag_main',this.parentNode,'hover')">权限设置</li>
    </ul>
</div>

<div id="tag_main_a">
    <table class="mainTable">
        <tr>
            <th colspan="2">基本属性</th>
        </tr>
        <tr>
            <td width="150" align="right">标题：</td>
            <td>
               &lt;?php echo Form::input('title');?&gt;
            </td>
        </tr>
        <tr>
            <td align="right">时间：</td>
            <td>
                &lt;?php echo Form::input_time('title',TIME);?&gt;
            </td>
        </tr>
        <tr>
            <td class="td1"></td>
            <td class="td1">
                <input type="submit" onclick="goto('/test');" value="提交按钮" />
            </td>
        </tr>
    </table>
</div>

<!-- 第二个主体要注意加style="display:none;" -->
<div id="tag_main_b" style="display:none;">
    <table class="mainTable">
        <tr>
            <th colspan="2">高级属性</th>
        </tr>
        <tr>
            <td width="150" align="right">高级内容：</td>
            <td>
                &lt;?php echo Form::input('title');?&gt;
            </td>
        </tr>
        <tr>
            <td class="td1"></td>
            <td class="td1">
                <input type="submit" onclick="goto('/test');" value="提交按钮" />
            </td>
        </tr>
    </table>
</div>
</textarea>

<h1>分页</h1>
<?php
$page_config = Core::config('admin/pagination');
$page_config['total_items'] = 1300;
$pagination = new Pagination($page_config);
$pagehtml = $pagination->render();
?>

<center>
<?php echo $pagehtml;?>
</center>


<br />
<br />
代码：
<textarea style="width:96%;height:180px;">
$page_config = Core::config('admin/pagination');    // 读取模式后台分页配置
$page_config['total_items'] = 1300;                 // 设置总数，通常是之前SQL查出的结果
$pagination = new Pagination($page_config);
$pagehtml = $pagination->render();  	            //获取分页HTML，可传递给视图

$limit = $pagination->get_items_per_page();         // 获取可用于SQL的limit
$offset = $pagination->get_offset();                // 获取可用于SQL的offset
// SELECT * FROM `mytable` WHERE ... LIMIT $limit OFFSET $offset;
</textarea>
视图中直接输出$pagehtml即可，若希望居中对齐，则在&lt;center&gt;&lt;/center&gt;标签中即可


<h1>Admin UI特有的form方法</h1>
<table class="mainTable">
<tr>
<td>
时间输入框：<?php echo Form::input_time('time');?>
</td>
</tr>
<tr>
<td>
日期输入框：<?php echo Form::input_date('test_data');?>
</td>
</tr>
<tr>
<td>
带下拉文本框：<?php echo Form::input_select('time',2,array('aaaaaaa','bbbbbbbbb','ccc','ddddd'));?>
</td>
</tr>
</table>



<br />
代码如下：
<textarea style="width:96%;height:120px;">
时间输入框：&lt;?php echo Form::input_time('time');?&gt;

日期输入框：&lt;?php echo Form::input_date('test_data');?&gt;

带下拉文本框：&lt;?php echo Form::input_select('time',2,array('aaaaaaa','bbbbbbbbb','ccc','ddddd'));?&gt;
</textarea>


<h1>采用AJAX提交Form表单</h1>
<form name="myform" id="myform" method="post" onsubmit="return MyQEE.form_post(this,document.referrer);">
<input type="text" name="test" />
<input type="submit" value="提交" />
</form>

<br />
代码如下：
<textarea style="width:96%;height:220px;">
<!--
可以看到再form上加了一个onsubmit，这样在提交时页面会采用AJAX方式提交
AJAX需返回一个JSON数据，类似{"status":1,"msg":"操作成功"}
status=1表示成功，status=0信息提示，stauts<0错误提示，msg为提示内容
注意：带文件上传的表单不可用此方式
MyQEE.form_post()方法，第一个参数为form对象，第二个参数为操作成功后跳转到的页面
 -->
<form name="myform" id="myform" method="post" onsubmit="return MyQEE.form_post(this,document.referrer);">
<input type="text" name="test" />
<input type="submit" value="提交" />
</form>
</textarea>

<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
