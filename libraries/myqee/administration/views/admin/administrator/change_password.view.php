<form autocomplete="off" method="post" onsubmit="return MyQEE.form_post(this);">
<table border="0" cellpadding="4" cellspacing="1" align="center" class="mainTable">
    <tr>
        <th colspan="2">修改管理员密码</th>
    </tr>
    <?php
    if ($member_id==Session::instance()->member()->id)
    {
    ?>
    <tr>
        <td class="td1" align="right">
            旧密码：
        </td>
        <td class="td2">
            <input type="password" value="" name="old_password" />
        </td>
    </tr>
    <?php
    }
    ?>
    <tr>
        <td width="30%" class="td1" align="right">
            新密码：
        </td>
        <td class="td2">
            <input type="password" value="" name="new_password" />
        </td>
    </tr>
    <tr>
        <td class="td1" align="right">
            确认密码：
        </td>
        <td class="td2">
            <input type="password" value="" name="new_password_2" />
        </td>
    </tr>
    <tr>
        <td class="td1"> </td>
        <td class="td1">
        <input type="submit" value="修改密码" />
        <input type="button" value="返回" onclick="window.history.go(-1)" />
        <font style="color:red"><?php echo $msg;?></font>
        </td>
    </tr>
</table>
</form>