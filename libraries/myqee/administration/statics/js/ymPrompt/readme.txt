===============================================================================
组件名称：

ymPrompt消息提示组件 4.0

===============================================================================
组件说明：

在web开发中，对于浏览器默认的消息提示框（如alert,confirm等）外观无法控制，同时我们经常希望能实现一些window.open之类的弹出框，但window.open弹出框存在诸多问题，如可能被拦截，界面不美观等。
为了实现更好的界面效果和控制，于是模拟系统的消息提示框及弹出窗口实现了该组件。在外观上可以通过css进行完全的控制。

===============================================================================
组件特点及功能介绍：

1、调用方式简单，直接使用ymPrompt.alert()的方式调用，传入相应的参数即可。
2、完美兼容各类浏览器，在以下浏览器测试通过：IE6.0+、FF1.5+、Opear9+、Safari3.1、Google Chrome 0.3.154，完美兼容HTML4/XHTML1.0页面渲染模式。
3、完全的CSS皮肤定制功能，所有外观包括显示图标/关闭按钮等都可以通过css设置。目前提供了五种皮肤，Vista、QQ、dmm-green、ExtBlue和BlackColl。有兴趣可以修改样式表文件定义新的皮肤。
4、提供五种消息类型。分别为消息提示、成功信息、错误信息、询问信息、弹出窗口。
5、支持对显示的操作按钮的自定义功能，如不显示按钮，或者显示“是、否、取消”等按钮，支持自定义图标。
6、弹出消息框时屏蔽页面其它元素的操作，自动隐藏页面select及iframe下select（无限级select隐藏），并遮罩iframe/object等元素。
7、页面select控件状态还原时保持原状态，即如果原来select就是隐藏的，则消息框弹出并关闭后该select仍保持隐藏状态，而只显示原本显示的select。
8、消息组件弹出时屏蔽除Tab和回车外的几乎所有键盘操作（包括F5），“消息确认框”支持“tab键/左右方向键”切换组件按钮焦点。
9、可以页面加载的任意时间调用，如页面初始化时（onload之前）调用、页面加载完成后调用。
10、支持通过JSON（推荐）和传统的参数两种传入方式。如ymprompt.alert('消息内容',300,200,'消息标题',handler)和ymprompt.alert({message:'消息内容',width:300,height:200,title:'消息标题',handler:handler})，这两种写法是等价的。
11、只需要一个回调函数，可捕获确定、取消、关闭按钮及各种自定义按钮的点击事件。
12、无侵入性，组件对外只暴露一个ymPrompt变量，有效防止与页面程序变量的冲突。
13、程序(js)与样式(css)的完全分离，用户可根据需要加载对应css样式。
14、文件小，4.0版压缩后仅为9kb。
15、可实现语言的本地化，可用于英文等版本的系统中。
16、支持：自定义按钮/图标、是否显示标题栏、是否允许拖出窗口范围、是否随滚动条滚动、弹出框默认显示位置（8方向）、遮罩外观及是否显示等控制。
17、支持定义窗体拖动时窗体的透明度。
18、支持渐变式弹出和隐藏，支持定义渐变速度
19、支持连续多次调用弹出框，组件按照调用顺序显示（关闭当前弹出后自动显示下一个弹出）
20、支持配置最大化、最小化、关闭按钮是否显示。

==============================================================================
4.0 版本更新内容：

1、实现当重复引入ymPrompt.js时，只执行一次组件解析。
2、支持配置是否显示遮罩(showMask)。
3、支持配置弹出窗口显示在哪个位置(winPos)（9个内置位置和自定义坐标）。
4、支持esc键关闭弹出框。
5、解决IE Resize时遮罩层宽高不能正确计算的问题。
6、多语言支持，可以自定义显示的语言内容，如“确定”,“取消”按钮等。
7、解决页面Object对象遮盖弹出框的问题（对Object元素进行动态显示和隐藏）。
8、解决IE中鼠标不能在浏览器之外拖动的问题。
9、解决Iframe模式下拖动不流畅的问题。
10、支持iframe下时自定义iframe属性
11、支持定义窗体拖动时窗体的透明度（winAlpha）。
12、增加closeBtn参数用于设定是否显示关闭按钮
13、新增了simple和simplegray皮肤。
14、文件编码改为utf-8，更好兼容各种网页编码。
15、支持渐变式弹出和隐藏，支持定义渐变速度
16、支持弹出框队列方式显示（多次调用弹出，组件会自动在当前弹出框关闭后弹出下一个弹出框而不会覆盖之前的弹出框）
17、IE下阴影效果。
18、支持通过msgCls参数自定义弹出框内容的样式
19、支持最大化最小化、支持配置最大化最小化按钮是否显示。
20、增加max,min,normal三个方法用于设定窗口状态。
21、解决IE下有时报“IE无法打开INTERNET站点”的错误。
22、窗口支持渐隐渐现效果。

3.0 版本更新内容：

1、完美兼容各大主流浏览器，在IE6.0+、FF1.5+、Opear9+、Safari3.1、Google Chrome 0.3.154等浏览器均测试通过。
2、支持自定义按钮，可配置不显示按钮或者显示多个按钮。
3、支持自定义图标样式，可配置不显示图标。
4、增加setDefaultCfg方法，用于设定组件的默认属性。
5、对组件内部代码结构做了大的改动，性能有所提升。
6、支持普通窗体模式（没有图标和按钮）和iframe窗体模式（传入url即可）。

7、解决之前版本存在当组件宽度设定过宽时标题背景显示不全的问题。
8、解决在页面onload之前调用组件不论用那个方法都显示alert图标的问题。

9、在功能增加和改善的同时，程序比以前更精简，3.0的js文件与2.0相比大小几乎没有变化。

//2008-12-12新增
10、新增fixPosition参数设定是否当页面滚动时使弹出框始终浮动在屏幕的固定位置。
11、新增dragOut参数设定是否允许弹出框拖动到页面范围以外。
12、新增autoClose参数设定是否当用户点击按钮后自动关闭弹出框。

//2008-12-17新增
13、新增titleBar参数来控制是否显示标题栏。

2.0 版本更新内容：

1、2.0中程序不再自动加载样式表，由用户将样式表引入到页面中，相比1.0对样式的控制更加简单，且降低了程序实现的复杂性。
2、2.0除了支持传统的参数传入外，还支持JSON，通过json方式，不用再记忆参数顺序。
3、2.0解决了1.0在IE和FF下显示高度不一致的问题。
4、2.0代码封装程度更高，进对外公开一个变量ymPrompt，更好的防止变量冲突问题。
5、2.0代码更加简洁，文件更小，压缩版的由7k减为5k，未压缩的由16k减为11k。2.0中程序实现方式的改变也使性能相比1.0有所提升。
6、2.0回调函数变为一个，1.0中需要分别对确定和取消/关闭分别写回调函数，2.0中只需要一个回调函数，组件会为回调函数传入点击类型，如'ok','cancel','close'，解决了1.0无法区分取消与关闭的问题。
7、调用方法及参数的一些小改变，如Alert改为ymprompt.alert(),当然你可以在你的程序中通过Alert=ymprompt.alert保留Alert的调用方式。
8、2.0提供了对遮罩的样式控制（颜色及透明度）。

===============================================================================
3.0版调用方法及参数说明:

1、在页面中引入ymPrompt.js。如：<script type="text/javascript" src="ymPrompt.js"></script> 
2、在页面中引入对应的皮肤文件的CSS，如：<link rel="stylesheet" type="text/css" href="skin/qq/ymPrompt.css" /> 
3、自定义组件的默认配置信息（此步骤可选，该方法可以在任意时间调用）
   在页面的js中通过ymPrompt.setDefaultCfg(cfg)方法修改组件部分或全部的默认属性。 
   如：ymPrompt.setDefaultCfg({maskAlpha:0.2,maskAlphaColor:'#00f'})
   
   组件的所有配置项的默认值（对于没有设定的项将采用该配置项的默认值）：
   {
	message: '内容',	//消息框按钮
	width: 300,		//宽
	height: 185,		//高
	title: '标题',		//消息框标题
	handler: function() {}, //回调事件
	maskAlphaColor: '#000',	//遮罩透明色
	maskAlpha: 0.1,		//遮罩透明度

	iframe: false,		//iframe模式
	icoCls: '',		//图标的样式
	btn: null,		//按钮配置
	autoClose: true,	//点击关闭、确定等按钮后自动关闭
	fixPosition: true,	//随滚动条滚动
	dragOut: false,		//不允许拖出窗体范围
	titleBar: true,		//显示标题栏
	showMask: true,		//显示遮罩
	winPos: 'c',		//在页面中间显示
	winAlpha:0.8,		//拖动窗体时窗体的透明度
	closeBtn:true,		//是否显示关闭按钮
	showShadow:false,	//不显示阴影，只对IE有效
	useSlide:false,		//不使用淡入淡出
	slideCfg:{increment:0.3,interval:50},	//淡入淡出配置
	
	//按钮文本，可通过自定义这些属性实现本地化
	closeTxt: '关闭',
	okTxt:' 确 定 ',
	cancelTxt:' 取 消 ',	
	msgCls:'ym-content',	//消息内容的样式

	minBtn:true,		//是否显示最小化按钮	
	minTxt:'最小化',
	maxBtn:true,		//是否显示最大化按钮
	maxTxt:'最大化'
   }
4、根据您的需要调用相应的消息函数： 
	ymPrompt.alert(参数) //消息提示类型
	ymPrompt.succeedInfo(参数) //成功信息类型 
	ymPrompt.errorInfo(参数) //错误信息类型 
	ymPrompt.confirmInfo(参数) //询问消息类型 

	ymPrompt.win(参数) //自定义窗口类型 

	参数传入方式包含两种： 

	第一种即传统的参数传入，按照顺序传入相应的参数值即可（一定要按照顺序），对于不需要设定的值请传入null。如ymPrompt.alert('内容',null,null,'标题')
		参数顺序：message,width,height,title,handler,maskAlphaColor,maskAlpha,iframe,icoCls,btn,autoClose,fixPosition,dragOut,titleBar,showMask,winPos,winAlpha
	
	（推荐）第二种即JSON的传入方式，需要指定字段名,没有顺序，根据需要设定相关属性。如ymPrompt.alert({title:'标题',message:'内容'})


	五个方法的参数个数及意义完全相同(所有参数均为可选，不传入则使用默认参数值)： 

	message：消息组件要显示的内容，默认为“内容”。 
	width：消息框的宽度，默认为300。 
	height：消息框的高度，默认为185。 
	title：消息组件标题，默认为“标题” 
	handler：回调函数。当确定/取消/关闭按钮被点击时会触发该函数并传入点击的按钮标识。如ok代表确定，cancel代表取消，close代表关闭 
	maskAlphaColor：遮罩的颜色，默认为黑色。 
	maskAlpha：遮罩的透明度，默认为0.1。

 	fixPosition：设定是否弹出框随滚动条一起浮动，保持在屏幕的固定位置，默认为true
	dragOut：设定是否允许拖出屏幕范围，默认为false。
	autoClose：设定用户点击窗口中按钮后自动关闭窗口，默认为true（设定为false后程序中可以通过调用close方法关闭）。
	titleBar：是否显示标题栏，默认显示。注意，如果没有标题栏需要自己在程序中控制关闭。
	showMask：是否显示遮罩层，默认为true
	winPos：弹出窗口的位置，支持8种内置位置（c,l,t,r,b,lt,rt,lb,rb）及自定义窗口坐标,默认为c。
		各参数意义：c:页面中间,l:页面左侧,t:页面顶部,r:页面右侧,b:页面顶部,lt:左上角,rt:右上角,lb:左下角,rb:右下角
	winAlpha：弹出窗体拖动时的透明度，默认为0.8

	//以下三个参数主要用于win方法（当然你也可以通过设定这些覆盖前面四个消息类型的默认属性）。
	iframe：是否使用iframe方法加载内容，该属性如果为true或者object，组件则尝试将message内容作为url进行加载
		(如果属性值为一个object，则将object的内容添加为iframe的属性，如iframe:{id:'myId',name:'myName',src:'http://www.baidu.com'}则iframe的id为myId,name为myName,src为http://www.baidu.com)。默认为false。
	icoCls：图标类型。传入的内容为className，具体写法可以参考ymprompt.css中对图标的定义方式。默认为空。
	   btn：按钮定义。传入的是数组形式。每个按钮的格式为['按钮文本','按钮标识']，如[['确定','ok'],['取消','cancel'],['关闭','close']]等。
	        注意单个按钮应该是这样的：[['确定','ok']]
	closeBtn：是否显示关闭按钮，默认为true（显示）。

	showShadow:是否启用弹出框阴影效果（IE Only），默认为false
	useSlide:f是否启用弹出框的渐显渐隐效果，默认为false
	slideCfg:渐变效果的配置信息,参数格式为object，属性包括incerment:透明度每次增加的值，interval:变化的速度。例如：{incerment:0.3,interval:50}。该参数仅在useSlide为true时有效

	//以下参数可用于对组件语言本地化，如用于英文等系统中
	okTxt：确定按钮的文本描述，默认为“确定”
	cancelTxt：取消按钮的文本描述，默认为“取消”
	closeTxt：关闭按钮的文本描述（鼠标放在关闭按钮上时显示），默认为“关闭”
	minTxt：最小化按钮的文本描述，默认为“最小化”
	maxTxt：最大化按钮的文本描述，默认为“最大化”

	minBtn：是否显示最小化按钮，默认为false
	maxBtn：是否显示最大化按钮，默认为false
	
	

5、操作接口：

	属性：
	version：当前版本号 如：alert(ymPrompt.version)
	pubDate：当前版本的发布日期 如：alert(ymPrompt.pubDate);
	    cfg：组件的当前的默认配置

	方法：
	setDefaultCfg(cfg)：设定组件的默认属性，设定后的所有弹出均默认采用cfg中的设置。
		如：ymPrompt.setDefaultCfg({maskAlpha:0.2,maskAlphaColor:'#00f'});	//设定遮罩层颜色为蓝色，透明度0.2

	getPage()：在iframe窗口模式下，获取到iframe的dom对象。
		如：alert(ymPrompt.getPage().contentWindow.document.body.outerHTML);	//获取iframe页面的html内容

	resizeWin(w,h)：通过程序动态修改窗口的大小。参数：w：宽度，h:高度
		如：ymPrompt.resizeWin(400,300);	//修改弹出框宽度为400px，高度为300px
	
	doHandler(sign,autoClose)：模拟触发某个按钮的点击事件。参数sign:传给回调函数的标识，autoClose:是否自动关闭窗口（默认采用全局配置）
		如：ymPrompt.doHandler('ok',false);	//触发确定按钮的点击事件，并且执行完回调函数后不关闭窗口

	getButtons()：获取当前弹出窗口的所有按钮对象，返回结果是一个对象集合(数组)。
		如：var btnID=ymPrompt.getButtons()[0].id;//获取第一个按钮的id

	close()：关闭当前弹出的窗口
		如：ymPrompt.close()

	max()：最大化弹出窗口。

	min()：最小化弹出窗口

	normal()：窗口普通弹出状态

6、其他说明：如果觉得“对象.方法”的调用方式比较麻烦，可以采用如下方式简化调用： 
在调用之前设定var Alert=ymPrompt.alert。之后就可以使用Alert()的方式进行调用。  
     
===============================================================================
演示地址：http://www.ajaxbbs.net/test/ymPrompt4.0/demo.html

详细介绍及下载地址：http://www.ajaxbbs.net/post/product/ymPrompt4.0.html

网站：	http://www.ajaxbbs.net
作者：	闫威(yemoo|网者归来)
MSN：	yanwei8410(at)hotmail.com
E-Mail：netman8410(at)163.com
QQ:278384986

最后更新：2009-03-02

