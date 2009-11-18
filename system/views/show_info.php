<?php
	$title or $title = '信息提示';
	View::factory('header',array('title'=>$title)) -> render(TRUE);
?>
<link href="{site_url}images/default/style.css" rel="stylesheet" type="text/css" />

<div class="myqee_clear"></div>
<div style="margin:50px auto 30px auto;width:600px;">
	<div class="myqee_box">
		<div class="myqee_top"><div class="myqee_top_left"></div><div class="myqee_top_right"></div></div>
		<div class="myqee_center">
			<div class="myqee_title" style="padding-top:10px;"><?php echo $title;?></div>
			<div class="myqee_main" style="padding:20px 15px;"><?php echo $message;?></div>
			<div style="padding:10px 15px;text-align:right;"><?php if($forward)echo '<script>setTimeout("window.location.href =\''.$forward.'\';", 1000);</script><a href="'.$forward.'">页面跳转中...</a>';?></div>
			<div class="myqee_clear"></div>
		</div>
		<div class="myqee_bottom"><div class="myqee_bottom_left"></div><div class="myqee_bottom_right"></div></div>
	</div>
</div>
<div class="myqee_clear"></div>

<?php 
	View::factory('footer') -> render(TRUE);
?>