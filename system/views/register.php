<?php
$this -> view('header',array('title'=>'用户注册'));
?>
<link href="{site_url}images/default/style.css" rel="stylesheet" type="text/css" />
<style text="text/css">
.rTitle{border-top:1px solid #ccc;padding:2px 8px;line-height:1.8em;font-weight:bold;}
.rMain{padding:15px;}
.rInfo{color:#999;float:left;padding:0 0 6px 0}
.myform input.input{width:300px;padding:4px;font-size:14px;border:1px solid #666;}
td{line-height:28px;}
.xiey{overflow:auto;border:1px solid #5ca4b3;line-height:1.6em;padding:3px 8px;color:#666;width:650px;height:160px;margin:10px auto;}
</style>
<div class="myqee_clear"></div>
<script type="text/javascript" src="{site_url}images/js/valid.js"></script>

<form class="myform" method="post" action="{site_url}register/reg.html" target="hiddenFrame" onSubmit="return Validator.Validate(this,3)">
	<input type="hidden" name="step" value="2" />
	<input type="hidden" name="forward" value="<?php echo str_replace('"','&quot;',$_GET['forward']);?>" />
	<div style="padding:10px 0 30px 0;color:#666;">欢迎您注册成为{site_name}用户，注册过程不会收取您任何费用；我们承诺保护您个人信息的安全，不会提供给任何第三方。
	<a href="{site_url}login/index.html" onclick="login_frame();this.blur();return false;"><u>老用户登陆</u></a>
	</div>


	<div class="title rTitle">帐户信息</div>
	<div class="rMain">
		<table width="100%" border="0">
	      <tr>
	      <td width="200" align="right" valign="top"><font color="red">*</font>帐户：</td>
	      <td><input name="username" type="text" id="username" value="" class="input" maxlength="20" dataType="Username" msg="只允许英文字母和数字、下划线，且6-20位" />
	      <font class="rInfo">6～20位，由字母a～z(不区分大小写)、数字0～9、减号或下划线组成</font>
	      <span id="span_username"></span>
	      </td>
	    </tr>
	    <tr>
	      <td align="right" valign="top"><font color="red">*</font>密码：</td>
	      <td><input name="password" type="password" id="password" value="" maxlength="16" class="input" dataType="Limit" min="3" max="16" msg="密码3～16位，区分大小写" />
	      <font class="rInfo">3～16位，区分大小写</font>
	      </td>
	    </tr>
	    <tr>
	      <td align="right" valign="top"><font color="red">*</font>确认密码：</td>
	      <td><input name="ck_password" type="password" id="ck_password" dataType="Repeat" to="password" msg="两次输入的密码不一致" value="" class="input" />
	      <font class="rInfo">请再次输入您的密码</font></td>
	    </tr>
	      <tr>
	      <td width="200" align="right" valign="top"><font color="red">*</font>EMAIL地址：</td>
	      <td><input name="email" type="text" id="email" maxlength="50" value="" dataType="Email" msg="信箱格式不正确" class="input" />
	       <font class="rInfo">请填写真实的邮箱地址地址，用于忘记密码时取回</font>
	       <span id="span_email"></span>
	      </td>
	    </tr>
	    </table>
	</div>


	<div class="title rTitle">联系方式</div>
	<div class="rMain">
		<table width="100%" border="0">
	      <tr>
	      <td width="200" align="right" valign="top">QQ：</td>
	      <td><input name="qq" type="text" value="" class="input" maxlength="12" require="false" dataType="QQ" msg="QQ号码不存在" />
	       <font class="rInfo">填写真实的QQ，以便我们与您取得联系</font>
	      </td>
	    </tr>
	    <tr>
	      <td align="right" valign="top">MSN：</td>
	      <td><input name="msn" type="text" value="" class="input" maxlength="50" require="false" dataType="Email" msg="MSN格式不正确" />
	       <font class="rInfo">填写真实的MSN，以便我们与您取得联系</font>
	      </td>
	    </tr>
	    <tr>
	      <td align="right" valign="top"><div style="padding-top:12px;"><font color="red">*</font>验证码：</div></td>
	      <td><input name="imagecode" type="text" value="" class="input" style="width:100px;" maxlength="4" dataType="Limit" min="4" max="4" msg="验证码长度为4位" />
	     <img src="{site_url}captcha/image.png" width="150" height="50" style="cursor:pointer" onclick="this.src='{site_url}captcha/image.png?time='+(new Date()).getTime()" align="absmiddle" />
	       <font class="rInfo">请输入上方图片中的字符</font>
	      </td>
	    </tr>
	    <tr>
	      <td></td>
	      <td><input type="submit" value=" 注 册 " class="submit2" /> <input type="button" onclick="history.go(-1,'{site_url}')" value=" 返 回 " class="button2" /> </td>
	    </tr>
	    <tr>
	      <td colspan="2"><div class="xiey"> 您在{site_name}注册即代表您无条件接受以下所有服务条款：<br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;用户必须同意下述所有服务条款并完成注册程序，才能成为{site_name}的正式会员并使用{site_name}提供的各项服务。{site_name}所提供的各项服务的所有权和运作权归成都网盟科技有限公司，服务条款的修改权归成都网盟科技有限公司所有。<br />
		一、服务说明<br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{site_name}运用自主设计的系统，通过互联网络为会员免费提供网站内容服务。{site_name}有权在必要时修改服务条款，服务条款一旦发生变动，将会在重要页面上提示修改内容或通过其他形式告知会员。如果会员不同意所改动的内容，可以主动取消获得的网络服务。如果会员继续享用网络服务，则视为接受服务条款的变动。{site_name}保留随时修改或中断服务而不需知照会员的权利。{site_name}行使修改或中断服务的权利，不需对会员或第三方负责。<br />
		二、隐私声明<br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{site_name}承诺不公开或透露您的密码、姓名、手机号码等在本站的非公开信息。除非因会员本人的需要、法律或其他合法程序的要求、服务条款的改变或修订等。
		同时会员须做到：<br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;a.用户名和昵称的注册与使用应符合网络道德，遵守中华人民共和国的相关法律法规。<br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;b.用户名和昵称中不能含有威胁、淫秽、漫骂、非法、侵害他人权益等有争议性的文字。<br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;c.注册成功后，会员必须保护好自己的帐号和密码，因会员本人泄露而造成的任何损失由会员本人负责。<br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;d.不得盗用他人帐号，由此行为造成的后果自负。<br />
		三、会员资格要求<br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;1、会员以及会员网站不得利用本站危害国家安全、泄露国家秘密，不得侵犯国家社会集体的和公民的合法权益，不得利用本站制作、复制和传播下列信息：<br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;（1）煽动抗拒、破坏宪法和法律、行政法规实施的；<br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;（2）煽动颠覆国家政权，推翻社会主义制度的；<br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;（3）煽动分裂国家、破坏国家统一的；<br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;（4）煽动民族仇恨、民族歧视，破坏民族团结的；<br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;（5）捏造或者歪曲事实，散布谣言，扰乱社会秩序的；<br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;（6）宣扬封建迷信、淫秽、色情、赌博、暴力、凶杀、恐怖、教唆犯罪的；<br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;（7）公然侮辱他人或者捏造事实诽谤他人的，或者进行其他恶意攻击的；<br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;（8）损害国家机关信誉的；<br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;（9）其他违反宪法和法律行政法规的。<br />
		2、会员以及会员网站必须保证如下约定才能使用本联盟的服务：<br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;（1）网站不得传播任何包含病毒、特洛伊木马、蠕虫、时间炸弹或任何其他有害的程序或代码的内容；<br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;（2）不得未征得他人同意, 收集、邮寄或散布可识别他人身份的信息, 包括但不限于,
		地址、电话号码、电子邮件地址和社会保障号码；<br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;（3）不得传播或散布连锁信件、垃圾邮件或任何未经索要的邮件；<br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;（4）不得创设虚假的身份, 或以其他方式企图误导发件人的身份或邮件内容的来源；<br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;（5）不得张贴或传播任何可能侵犯第三人著作权、商业秘密、商业形象或其他知识产权或其他权利的内容；<br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;（6）不得从事任何有关赌博、赛马、博彩、抽奖、竞赛、宝塔式骗局、庞氏骗局或类似形式的违反任何适用法律的行为；<br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;（7）从事的业务或展示的信息必须获得主管政府部门批准或在主管政府部门登记的范围；<br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;（8）不得以任何形式对{site_name}服务进行破坏或者干扰活动。<br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;3、未经本站的授权或许可，任何会员不得借用本站的名义从事任何商业活动，也不得将本站作为从事商业活动的场所、平台或其他任何形式的媒介。禁止将本站用作从事各种非法活动的场所、平台或者其他任何形式的媒介。违反者若触犯法律，一切后果自负，本站不承担任何责任。<br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;4、如果有会员以及会员网站不符合会员资格要求，{site_name}将立刻终止服务，并删除该会员帐号。如果涉及触犯国家法律，{site_name}将报告相关部门处理。{site_name}不对会员以及会员网站下的非本联盟提供的内容担负任何责任。<br />
		四、服务终止或中断说明<br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;任何会员以及会员网站违反会员资格要求约定，{site_name}有权中止与频道定制服务用户的服务且不承担任何违约责任；由于会员违约造成损失，{site_name}有权向违约方追究由此带来的不良影响及经济损失。如果会员需要中止本协议，需提前15天通知本公司。<br />
		基于技术和不可预见的原因而导致的服务中断，或者因会员的非法操作而造成的损失，{site_name}不负责任。会员应当自行承担一切因自身行为而直接或者间接导致的民事或刑事法律责任。<br />
		五、免责声明<br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{site_name}所有提供之服务内容都来自互联网，{site_name}本身不制作或提供任何内容。<br />
		{site_name}对于任何包含、经由或连接、下载或从任何与有关本网站所获得的任何内容、信息或广告，不声明或保证其正确性或可靠性；并且对于用户经本网站上的内容、广告、展示而购买、取得的任何产品、信息或资料，{site_name}不负保证责任。用户自行负担使用本网站的风险。<br />
		{site_name}有权但无义务，改善或更正本网站任何部分之任何疏漏、错误。<br />
		<br />
		{site_name}提供之内容仅代表来源网站或搜索引擎的内容，本站不承担由此引发的法律责任。 </div></td>
	    </tr>
	    </table>
	    
	</div>


</form>
<?php
$this -> view('footer',array('runtime'=>true));
?>