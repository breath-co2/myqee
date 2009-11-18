<!-- include "header.tpl" -->
{$title}

<!--{$i=time()}-->

<!--{$i++}-->
{$i}


<!--{$c=count($data)}-->

<br/>变量
{$_GET.feedid.test.te_st}

<br/>循环
<!--{loop $ddd.ss as $key => $values }-->
	<li>{$key}.{$values}<li>
<!--{/loop}-->

<br/>FOR循环
<!--{for $i=0 ;$i<count($ddd); $i++}-->
	<li>{$i}</li>
<!--{/for}-->

<br/>截取
{$title}#4#

<br/>时间
{$current_time}#Y年m月d日#
 
<br/>函数
<!--{substr(abcdefghijklmn,3,5)-->
<!--{myqee::config(core.mysitehost)-->


<br/>碎片
{myqee.block(index,1)}

<br/>判断
<!--{if $bit.ds==1 && $ds!=1 }-->
YYYYY
<!--{elseif $ds==3}-->
HHHHH
<!--{else}-->
NNNNN
<!--{/if}-->



<br/>语言包
<!--{lang test.asv.aaa}-->









----------
<span>{$data.time}</span>
<h2>{$data.v}</h2>
<div class="address">
<!--{$i=0}-->
<!--{$c=count($data.down)}-->
<!--{loop $data.down as $item}-->
<!--{$i++}-->
<a href="{$item.address}">{$item.title}</a>
<!--{if $i==3}--><br/><!--{/if}-->
<!--{if $i!=3&&$i!=$c}--> | <!--{/if}-->
<!--{/loop}-->
</div>