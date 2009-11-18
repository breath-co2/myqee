<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>comment</title>
</head>
<body>
<form action="http://admin.waitanwatch.com.cn/plugins/run/comment/comment/add/" method="post" target="hiddenFrame">
  <table cellspacing="1" cellpadding="4" border="0" align="center" class="tableborder">
    <tr>
      <th class="td1">我来说两句（<?php echo $comment_count;?>）</th>
      <th class="td1">用户名：
        <input type="text" name="username" />
        密码：
        <input type="password" name="password" /></th>
      <th class="td1"><a href="#">新用户注册</a></th>
    </tr>
    <tr>
      <td class="td1"  colspan="3"><textarea name="comment" cols="50" rows="5">hehe</textarea></td>
    </tr>
    <tr>
      <td class="td1" colspan="3"><input type="submit"  value="提交"/>
        <input type="button" value="重置" /></td>
    </tr>
  </table>
</form>
<div id="commentlist">
  <?php foreach ($list as $val) {?>
  <div class="item">xxxx 说：<?php echo $val['comment'];?></div>
  <?php }?>
</div>
</body>
</html>
