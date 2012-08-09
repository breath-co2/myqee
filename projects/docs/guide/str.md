字符串对象
=======
Str::factory($str)
--------
构造一个实例化的字符串对象

	$str = 'test';
	$obj_str = Str::factory($str);

addcslashes(string $charlist)
--------
参照 [http://cn2.php.net/manual/zh/function.addcslashes.php](http://cn2.php.net/manual/zh/function.addcslashes.php)

	echo Str::factory('test')->addcslashes('A..z');
	//将输出 \t\e\s\t

substr($start, $length = null, $encoding = 'UTF-8')
--------
截取字符串，支持设定编码

	echo Str::factory('测试中文123456')->substr(2,4);
	// 将输出：中文12

text2html()
--------
将文本转义输出为HTML可识别的字符串，将 < , > , \r\n , \r , \n 对应替换为 &amp;lt; , &amp;gt; , &lt;br /&gt; , &lt;br /&gt; , &lt;br /&gt;

	$str = <<<EOF
	这个是一个多行本文
	欢迎<测试>，欢迎
	EOF;
	echo Str::factory($str)->text2html();
	
	// 将输出：这个是一个多行本文<br />欢迎&lt;测试&gt;，欢迎

strlen($encoding = 'utf-8')
--------
返回字符长度，支持编码设定，一个中文算1

	echo Str::factory('测试中文123456')->strlen();
	// 将输出：10

count($encoding = 'utf-8')
--------
等同上面的 strlen()


append($str)
--------
将一个字符串拼接到当前对象里

	$str1 = new Str('abc');
	$str1->append('def');
	echo $str1;
	// 将输出 abcdef

is_empty()
--------
判断字符串是否为空

	var_dump(Str::factory('')->is_empty());		//bool(true)
	
	var_dump(Str::factory('0')->is_empty());	//bool(true)	
	var_dump(Str::factory('0')->is_empty());	//bool(true)

escape($encode = 'UTF-8')
--------
等同js脚本里的escape函数，请参阅[Text::escape($str, $encode = 'UTF-8')](text.html) 方法
	
	
unescape($encode = 'UTF-8')
--------
等同js脚本里的unescape函数，请参阅[Text::unescape($str, $encode = 'UTF-8')](text.html) 方法
	
pinyin()
--------
输出中文所对应的拼音

	echo Str::factory('中文abc123')->pinyin();			//zhongwenabc123
	
	//等同于
	echo PinYin::get('中文abc123');

byte($force_unit = null, $format = null, $si = true)
--------
输出字节格式化，请参阅[Text::byte($force_unit = null, $format = null, $si = true)](text.html) 方法

	echo Str::factory('1234567890')->byte();
	//将输出 1.23 GB