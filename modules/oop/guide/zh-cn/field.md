ORM 字段设置
================
常用字段举例
----------------
最简单的设置：
<pre><code>public $test = 'test';    //表示使用test这个字段的数据</code></pre>
<pre><code>public $test = 'abcd';    //表示使用abcd这个字段的数据</code></pre>

使用本data的其它数组字段的某个key的映射：

    public $sub_filed = 'parent_field[abc]';  //表示使用test这个字段数据的abc键的值的隐射

或

    public $sub_filed = array(
        'field_name' => 'parent_field[abc]',
    );

ORM虚拟对象设置举例：

    $status = array(
        'orm' => array(
            'name' => 'Status',
            'mapping' => array(
                'info_id' => 'id',
            ),
            'return' => 'single',
        ),
    );
    
ORM 完整关键字配置说明
----------------

    public $testfield = array(
    	// 字段名称，不设置则为虚拟字段
    	'field_name' => 'test2',       // 字段名称，若虚拟字段则不用设置
    	// 使用test字段(数组)的abc键值
    	'field_name' => 'test[abc]',
    	'is_virtual_field' => true,    // 是否虚拟字段，默认否
    	'is_readonly' => true,         // 是否只读，默认否
    	'is_id_field' => true,         // 是否ID字段，默认否，一个ORM只允许一个ID字段
    	'data' => array(
    		// 来源数据设置
    	    // 驱动类型，可自行扩展，系统默认包括：function,database,httpget等
    		'driver' => 'database',
    		// 对应关系
            'mapping' => array(
                'id' => 'blog_id',     // 表示后者的id与当前的blog_id是对应的
            ),
            // where条件
            'where' => array(
                'type' => '123',       // 表示后者的数据需要满足type=123
            ),
            // 缓存设置（如果需要）
            'cache' => array(
                'config' => 'default', // 配置名，也可以是一个数组，配置方法同cache的配置
                // 以下4项可只设置一项，也可多项设置
                'max_age' => 250,               // 生存期，单位秒
                'max_hit' => 1000,              // 最大命中数，达到最大命中数后将重新获取
                'renew_age' => '200~250,1/100', // 介于200～250秒之间，命中率1/100，命中后更新
                'renew_hit' => '800~1000,1/10', // 介于800~1000次之间，允许程序随机选择是否更新
            ),
            
            // <b>以下根据上面的driver不同而不同</b>
    
            // function。会把mapping和where处理后的条件作为一个数组传给第一个参数
            'function' => array('className','test');
            // 如下，也可以是对象的方法
            'function' => array($this,'test');
            // 需要传递给此方法的参数，注意：是从第二个参数开始
            'arguments' => array(
                $this,
                'test',
                ...
            ),
            
    		// 数据库驱动，可设置database和tablename
    		'database' => 'default',
    		'tablename' => 'index_blog_comment',
    		'result_single_row' => true,	 //是否返回单条数据
            'limit' => 10,           		 // 指定需要控制返回集合的数量
            'offset' => 0,           		 // 开始指针
            'order_by' => array(
            	'id' => 'DESC',
            	'test' => 'ASC',
            ),
    		
    		// httpget方式
    		'url' => 'http://...',           //接口地址
    		'method' => 'post',              //方式，post|get
    		'data_format' => 'json',         //数据格式，默认为json，支持json|string
    		// get或post数据中另外附带的一些固定数据
            'arguments' => array(
                'test' => '123',
                'type' => 'abc',
                ...
            ),
    	),
    	
        // 设置返回一个object对象，不设置则返回默认数据格式，当设置驱动为orm时，此项无效
        'object' => 'Arr',          // 表示返回一个Arr对象，data数据在构造对象时会传给Arr
        // 或更详细的设置
        'object' => array(
            'name' => 'Arr',
            // 对象提供的一些回调方法
            'callback' => array(
                // 设置数据，如果此项不存在，则在构造函数时将数据传给对象第一个值
                'set_data' => 'setData',
                'get_data' => 'getArrayCopy',   //获取数据
                'to_string' => '__toString',    //输出为字符串
                'is_change' => 'isChange',      //数据是否修改
            ),
        ),
        
    	// 设置返回一个ORM，注意：当此项设置，上面的data，object设置将无效
    	'orm' => array(
    	    'name' => 'Blog',      // ORM名称
            'return' => 'single',  // single:返回但条数据，group:返回一个集合的数据，其它（包括不设置），则为ORM对象本身
    	    // 对应关系
            'mapping' => array(
                'id' => 'blog_id', // 表示后者的id与当前的blog_id是对应的
            ),
            // where条件
            'where' => array(
                'type' => '123',   // 表示后者的数据需要满足type=123
            ),
            // 下面几项在return='result'时设置有效
            'limit' => 10,           // 指定需要控制返回集合的数量
            'offset' => 0,           // 开始指针
    	),
    	
        // 获取数据后对数据进行格式化转换（如果需要），注意顺序，格式化时从上到下，反解时从下到上
        'format' => array(
            'serialize',        // 序列化字段
            'json',             // json化字段，注意，不能与serialize同时用
            'gzcompress',       // gzcompress压缩
            ...                 // 可自定
        ),
        
        // 字段缓存设置（如果需要）
        'cache' => array(
            'config' => 'default', // 配置名，也可以是一个数组，配置方法同cache的配置
            // 以下4项可只设置一项，也可多项设置
            'max_age' => 250,               // 生存期，单位秒
            'max_hit' => 1000,              // 最大命中数，达到最大命中数后将重新获取
            'renew_age' => '200~250,1/100', // 介于200～250秒之间，命中率1/100，命中后更新
            'renew_hit' => '800~1000,1/10', // 介于800~1000次之间，允许程序随机选择是否更新
        ),
        
        // 字段内容设置有效性验证
        'valid' => array(
            'callback' => 'myfunction',     // 设置一个回调函数
            'rules' => '#^[a-z0-9]$#i',     // 设置一个规则
        ),
    );

以上列出的是单个字段可设置的参数，而并非必须每项都设置。
比如：id字段

    $id = array(
        'field_name' => 'id',
        'is_id_field' => true,
    );

即可。