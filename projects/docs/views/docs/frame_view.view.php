<?php
View::factory('docs/header')->render(true);
?>
<div class="mainwidth">
	<div class="main_left">
        <?php echo $lefthtml;?>
	</div>
	<div class="main_center">
		<?php echo $centerhtml;?>
	</div>
	<div class="main_right">
	    <?php echo $righthtml;?>
	</div>
</div>
<?php View::factory('docs/bottom')->render(true);?>