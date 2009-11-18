var oEditor = window.parent.InnerDialogLoaded() ;

window.onload = function()
{
	oEditor.FCKLanguageManager.TranslatePage(document) ;
	window.parent.SetOkButton( true ) ;
}

function makeRandomNum(){//根据时间生成数字串 月2+日2+时2+分2+秒2
	var nowDate=new Date();
	var theNum;
	theNum=(nowDate.getMonth()+1).toString()+nowDate.getDate().toString()+nowDate.getHours().toString()+nowDate.getMinutes().toString()+nowDate.getSeconds().toString();
	return  theNum;
}
function Ok()
{	
	var sMp3Url = GetE('mp3Url').value;
	var sAutioPlay="";
	
	var sAutoStart = GetE('autoStart').value;
	if (sAutoStart=="Yes"){
		sAutoStart="autostart=yes&amp;"
	}
	else{
		sAutoStart="";
	}
	
	var sLoop = GetE('loop').value;
	if (sLoop=="Yes"){
		sLoop="loop=yes&amp;";	
	}
	else{
		sLoop="";
	}
	
	var alwayOpen=GetE('alwayOpen').value;
	if (alwayOpen=="Yes"){
		alwayOpen="animation=no&amp;";
	}
	else{
		alwayOpen="";
	}
	//处理title和Artist信息
	var musicTitles,musicArtists;
	musicTitles="";
	musicArtists="";

	if (GetE('musicTitle').value!=""){
		musicTitles="titles="+GetE('musicTitle').value+"&amp;";
	}
	if (GetE('musicArtist').value!=""){
		musicArtists="artists="+GetE('musicArtist').value+"&amp;";
	}
	
	var numTemp;
	if( sMp3Url.length> 0) {
		numTemp=makeRandomNum(); 
		sAutioPlay ="<object id=\"audioplayer"+numTemp+"\" data=\"/images/player.swf\" width=\"290\" height=\"24\" type=\"application/x-shockwave-flash\">"+"<param value=\"/images/player.swf\" name=\"movie\" />"+"<param value=\"playerID="+numTemp+"&amp;bg=0xCDDFF3&amp;leftbg=0x357DCE&amp;lefticon=0xF2F2F2&amp;rightbg=0xF06A51&amp;rightbghover=0xAF2910&amp;righticon=0xF2F2F2&amp;righticonhover=0xFFFFFF&amp;text=0x357DCE&amp;slider=0x357DCE&amp;track=0xFFFFFF&amp;border=0xFFFFFF&amp;loader=0xAF2910&amp;"+musicTitles+musicArtists+sAutoStart+sLoop+alwayOpen+"soundFile="+sMp3Url+"\" name=\"FlashVars\" /><param value=\"high\" name=\"quality\" /><param value=\"false\" name=\"menu\" /><param value=\"transparent\" name=\"wmode\" /><embed src=\"/images/player.swf\" name=\"audioplayer"+numTemp+"\" AllowScriptAccess=\"never\" width=\"290\" height=\"24\" wmode=\"transparent\" FlashVars=\"playerID="+numTemp+"&amp;bg=0xCDDFF3&amp;leftbg=0x357DCE&amp;lefticon=0xF2F2F2&amp;rightbg=0xF06A51&amp;rightbghover=0xAF2910&amp;righticon=0xF2F2F2&amp;righticonhover=0xFFFFFF&amp;text=0x357DCE&amp;slider=0x357DCE&amp;track=0xFFFFFF&amp;border=0xFFFFFF&amp;loader=0xAF2910&amp;"+musicTitles+musicArtists+sAutoStart+sLoop+alwayOpen+"soundFile="+sMp3Url+"\" menu=\"\" type=\"application/x-shockwave-flash\"></embed></object>";
		
		oEditor.FCK.InsertHtml(sAutioPlay) ;
    	//window.parent.Cancel() ;
	} else {
		alert("请输入正确的MP3地址。（例：http://www.163.com/popmusic.mp3）");
	}
	return true ;
}