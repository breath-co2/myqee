<?php defined('MYQEEPATH') or die('No direct script access.');?><?php include(dirname(__FILE__).DIRECTORY_SEPARATOR.'c7b8169ee447ae1c4951b1036544bee6'.EXT);?>
<?php echo $title;?>

<?php $i=time();?>

<?php $i++;?>
<?php echo $i;?>


<?php $c=count($data);?>

<br/>变量
<?php echo $_GET['feedid']['test']['te_st'];?>

<br/>循环
<?php if(isset($ddd['ss'])&&is_array($ddd['ss'])){foreach($ddd['ss'] as $key => $values){?>
	<li><?php echo $key;?>.<?php echo $values;?><li>
<?php }}?>

<br/>FOR循环
<?php for($i=0 ;$i<count($ddd); $i++){?>
	<li><?php echo $i;?></li>
<?php }?>

<br/>截取
<?php echo Tools::substr($title,0,4);?>

<br/>时间
<?php echo date("Y年m月d日",$current_time);?>
 
<br/>函数
<!--{substr(abcdefghijklmn,3,5)-->
<!--{myqee::config(core.mysitehost)-->


<br/>碎片
<?php Template::block("index",1);?>


<br/>判断
<?php if($bit['ds']==1 && $ds!=1){?>
YYYYY
<?php }elseif($ds==3){?>
HHHHH
<?php } else { ?>
NNNNN
<?php }?>



<br/>语言包
<?php echo Myqee::lang('test.asv.aaa');?>









----------
<span><?php echo $data['time'];?></span>
<h2><?php echo $data['v'];?></h2>
<div class="address">
<?php $i=0;?>
<?php $c=count($data['down']);?>
<?php if(isset($data['down'])&&is_array($data['down'])){foreach($data['down'] as $item){?>
<?php $i++;?>
<a href="<?php echo $item['address'];?>"><?php echo $item['title'];?></a>
<?php if($i==3){?><br/><?php }?>
<?php if($i!=3&&$i!=$c){?> | <?php }?>
<?php }}?>
</div>