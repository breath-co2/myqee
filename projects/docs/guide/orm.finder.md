OOP ORM Finder
================
支持DB和REST两种驱动类型，目前REST方式还处于测试阶段，数据库方式功能已经较完善。
> 在2.0RC1-RC3版本中，REST为HttpGet

    // 继承到数据库类型的Finder上
    ORM_Test_Finder extends OOP_ORM_Finder_DB
    {
        /**
         * 数据库配置，不配置则默认为default
         */
        protected $database = 'mydb';
        
        /**
         * 表名称，数据库类型必须指定表名称
         */
        protected $tablename = 'test';
    }


    // 继承到REST类型的Finder上
    ORM_Test_Finder extends OOP_ORM_Finder_REST
    {
        /**
         * 获取数据的API接口地址
         */
        protected $api_url = 'http://.../api';
    }
    
    // 注意，这样是错误的，因为OOP_ORM不具备独立的功能
    ORM_Test_Finder extends OOP_ORM
    {
    
    }

Finder和Model的关系和区别
---------
Finder 可以理解为一个为数据专门设计的Model。Model是以业务逻辑为基础建立的，而Finder则是以数据为基础建立的。二者功能上非常相似，但Finder具有更强的针对性。

在这里，Model可以扩展到某个Finder上，例如：

    Model_Test extends ORM_MyTest_Finder
    {
        ...
    }

Finder 常用方法
=============

create($data = null, $is_field_key = false)
---------
创建一个新的数据对象

* $data类型为数组，可以为创建的对象设置数据
* $is_field_key告诉程序设置的$data的数组的key是数据库字段还是对象的键值，下面的例子你会看到区别。

文件：orm/test.orm.php,表test,字段：id,title,nickname,create_time

    <?php
    ORM_Test_Finder extends OOP_ORM_Finder_DB
    {
        /**
         * 数据库配置，不配置则默认为default
         */
        protected $database = 'mydb';
        
        /**
         * 表名称，数据库类型必须指定表名称
         */
        protected $tablename = 'test';
    }
    
    ORM_Test_Data extends OOP_ORM_Data
    {
        public $id = array(
            'field_name' => 'id',
            'is_id_field' => true,
        );
        
        public $title = array(
            'field_name' => 'title',
            'object' => 'Str',
        ),
        
        public $name = array(
            'field_name' => 'nickname',
        ),
        
        public $create_time;
    }

简单的例子：

    $orm_test = new ORM_Test_Finder();
    //创建一个空数据
    $new_data = $orm_test->create();
    $new_data->id = 123;                    //赋值

设置数据：

    $orm_test = new ORM_Test_Finder();
    $data = array(
        'id'    => 1,
        'title' => '标题',
        'name'  => '小王',
    );
    $new_data = $orm_test->create($data);
    echo $new_data->id;                     //1
    echo $new_data->name;                   //小王

设置数据（请注意和上例的区别）：

    $orm_test = new ORM_Test_Finder();
    $data = array(
        'id'       => 1,
        'title'    => '标题',
        'nickname' => '小王', // 上例中为name
    );
    $new_data = $orm_test->create($data,true);  // 若第2个参数设置为false，则的值nickname是设不上去的
    echo $new_data->id;                     //1
    echo $new_data->name;                   //小王
    var_dump($new_data->nickname);          //null

driver()
------------
返回当前驱动实例化对象，会根据Finder中设置的$database去读取数据库配置

    $orm_test = new ORM_Test_Finder();
    $db = $orm_test->driver();          // Database实例化好的对象

当驱动为DB时，Finder里还有一个方法是db()也可以返回Database对像，这是为了和Model保持一致

    $orm_test = new ORM_Test_Finder();
    $db1 = $orm_test->driver();
    $db2 = $orm_test->db();
    var_dump($db1===$db2);      //(bool)true

find()
------------
获取指定条件的数据，返回Result集合，关于Result的具体用法可[查看Result](orm.result.html)章节。

***find()是ORM用的比较频繁的一个方法，几乎所有的查询都会经过find()执行***

    $orm_test = new ORM_Test_Finder();
    $result = $orm_test->where('id',1)->find();
    // 输出最后查询的SQL语句
    echo $orm_test->last_query();   // SELECT * FROM `test` WHERE `id` = '1';
    echo $result->count();          // 符合条件的数量
    
    foreach($result as $item)
    {
        echo $item->name;
    }


    // 另一例子
    
    $orm_test = new ORM_Test_Finder();
    // 获取数据库对象
    $db = orm_test->driver();
    // 设置条件
    $db->limit(10)->order_by('id','DESC')->where('id',100,'<');
    // 获取数据
    $result = $orm_test->find();
    // 输出最后查询的SQL语句
    echo $orm_test->last_query(); // SELECT * FROM `test` WHERE `id`<100 ORDER BY `id` DESC LIMIT 10;
    
    foreach($result as $item)
    {
        echo $item->name;
    }
    
where()
------------
同Database的where，但区别的是，Finder的where的key为对象的键名，而并非数据库字段的key

    $orm_test = new ORM_Test_Finder();
    $result = $orm_test->where('name','小王')->find();
    echo $orm_test->last_query();     // SELECT * FROM `test` WHERE `nickname` = '小王';
    
    $orm_test->db()->where('nickname','小王');
    $result = $orm_test->find();
    echo $orm_test->last_query();     // SELECT * FROM `test` WHERE `nickname` = '小王';

get_by_id($id)
------------
根据ID获取数据，返回Data

    $orm_test = new ORM_Test_Finder();
    $test = $orm_test->get_by_id(1);
    if ( $test && $test instanceof ORM_Test_Data )
    {
        echo $test->id;                     //1
        echo $test->name;                   //小王
    }

get_by_ids($ids)
------------
根据ID获取数据，返回Result

    $orm_test = new ORM_Test_Finder();
    $result = $orm_test->get_by_ids( array(1,3,5,6,7) );
    if ( $result->count() ) foreach( $result as $item )
    {
        if ( $item instanceof ORM_Test_Data )
        {
            echo $test->id;
        }
    }

last_query()
--------------
返回最后执行的查询条件

    $orm_test = new ORM_Test_Finder();
    $result = $orm_test->where('id',1)->find();
    // 输出最后查询的SQL语句
    echo $orm_test->last_query();   // SELECT * FROM `test` WHERE `id` = '1';

Finder的高级方法
=========

create_group_data($array, $is_field_key = false)
--------------
将一个数据集构造成一个ORM数据集

    $orm_test = new ORM_Test_Finder();
    $result = $orm_test->where('id','10','<')->find();

    // 和上例同样效果
    
    $db = Database::instance();
    $data = $db->from('test')->where('id','10','<')->get()->as_array(); //获取id<10的所有数据,返回数组
    $orm_test = new ORM_Test_Finder();
    // 用获取的$data数据直接构造对象
    $result = $orm_test->create_group_data( $data , true );

set_auto_where(array $auto_where)
--------------
设置一个自动添加where的数据，这样每次在find()的时候都会复用这个条件
    
    // 没有auto where
    $orm_test = new ORM_Test_Finder();
    $result = $orm_test->where('id','10','<')->find();
    echo $orm_test->last_query();   // SELECT * FROM 'test' WHERE `id`<10;
    
    $result = $orm_test->where('id','1')->find();
    echo $orm_test->last_query();   // SELECT * FROM 'test' WHERE `id`=1;
    
    
    // 增加auto where，在每次执行find()时都会增加set_auto_where的数据
    $orm_test = new ORM_Test_Finder();
    $orm_test->set_auto_where(array('sex'=>1));
    
    $result = $orm_test->where('id','10','<')->find();
    echo $orm_test->last_query();   // SELECT * FROM 'test' WHERE `sex`=1 AND `id`<10;
    
    $result = $orm_test->where('id','1')->find();
    echo $orm_test->last_query();   // SELECT * FROM 'test' WHERE `sex`=1 AND `id`=1;
    
    // 对于直接find中设置SQL的查询不起效果
    $result = $orm_test->find("SELECT * FROM 'test' WHERE `id`=1");
    echo $orm_test->last_query();   // SELECT * FROM 'test' WHERE `id`=1;
    
    

clear_auto_where()
----------------
清除set_auto_where()设置的自动构造数据


get_orm_name($type)
----------------
获取指定类型的ORM对象名称(字符串)

$type = Finder 或 Data 或 Result