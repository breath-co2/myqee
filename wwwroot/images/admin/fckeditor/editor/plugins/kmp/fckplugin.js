// JavaScript Document
//插入音乐
FCKCommands.RegisterCommand('KMP', new FCKDialogCommand('KMP', FCKLang.KMP, FCKPlugins.Items['KMP'].Path +'wpAudioPlay.html', 450,390)) ;
var KMPItem = new FCKToolbarButton('KMP', FCKLang.KMP) ;
KMPItem.IconPath = FCKPlugins.Items['KMP'].Path +'kmp.gif';
FCKToolbarItems.RegisterItem('KMP', KMPItem);
