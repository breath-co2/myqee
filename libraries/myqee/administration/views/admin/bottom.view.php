<div class="clear" style="height:0;overflow:hidden;"></div>
</td>
</tr>
</table>
</div>

<div id="bottomdiv">
    <div style="padding:0 10px;">
    &copy; MyQEE All Rights Reserved. Design by <?php echo HTML::email('jonwang@myqee.com')?>. Run Time:<?php echo number_format(microtime(true)-START_TIME,4);?>s.
    </div>
</div>
<script type="text/javascript">
if (!MyQEE.isinFrame()){
	//监视当前框架高度
	MyQEE.nowFrameId = 1;
	MyQEE.renewHeight = function(){
		var myframe = window.frames['framemain_'+MyQEE.nowFrameId];
		try{
			if (myframe){
				var dom = (myframe.document.compatMode && myframe.document.compatMode=="CSS1Compat")?myframe.document.documentElement:myframe.document.body;
				if (dom)
				{
					var h = Math.max(300,MyQEE.DOM().clientHeight-110,Math.min(dom.scrollHeight,(dom.offsetHeight+20)));
					MyQEE.$('framemain_'+MyQEE.nowFrameId).style.height = h +'px';
				}
			}
		}catch(e){}
		setTimeout('MyQEE.renewHeight()',100);
	}

	MyQEE.renewHeight();
	window.onresize();
}else{
	MyQEE.$('bottomdiv').style.display='none';
	MyQEE.$('maindiv').style.margin='5px 3px';
}


MyQEE.iniHtml();

</script>
<script type="text/javascript" src="<?php echo Core::url('statics/js/tooltip.js');?>"></script>
</body>
</html>