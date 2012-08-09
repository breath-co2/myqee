<div class="notification <?php if ($code===1){echo 'msgsuccess';}elseif ($code<0){echo 'msgerror';}else{echo 'msginfo';}?>">
    <a class="close" onclick="$(this).parent().fadeOut();"></a>
    <p><?php echo $msg;?></p>
</div><div class="clear"></div>