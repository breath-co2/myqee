# 数据库模块


Database类除了拥有自己独有的方法外，还继承了QueryBuilder类。


## Database::instance($config_name = 'default')

获取一个静态已实例化的对象，Database::instance()和new Database()都会返回一个实例化好的Database对象，不同的是，前者不会重复构造，而后者每次都会实例化，推荐使用Database::instance()

    $db1 = Database::instance();
    $db2 = Database::instance();
    $db3 = Database::instance('test');
    $db4 = Database::instance('test');
    $db5 = new Database();
    
    var_dump($db1===$db2);   //bool(true) 
    var_dump($db3===$db4);   //bool(true) 
    var_dump($db1===$db3);   //bool(false) 
    var_dump($db1===$db5);   //bool(false) 

## get($as_object = false, $use_master = null)

构造SQL并进行查询，它首先执行compile()方法获得SQL语句，然后用此SQL语句执行query()，是数据库对象中比较常用的一个方法

    $db = Database::instance();
    $arr = $db->from('mytable')->where('id', 1)->get()->as_array();
    
    $db->where('id',1);
    $db->from('mytable');
    $rs = $db->get();
    echo $rs->count();  // 行数
    foreach ($rs as $item)
    {
        ...
    }
    

## query($sql, $as_object = false, $use_master = null)

执行SQL，由于自行拼写的SQL语句会存在一定的安全隐患，所以推荐尽量少用此方法直接执行SQL语句
$sql - 需要执行的SQL语句
$as_object - 返回对象，true - 返回一个stdClass ， 或者指定一个其它的对象名，比如Arr
$use_master - 是否使用主数据库查询，只对查询语句设置有效（update,insert等语句会自动切换到主数据库），当然，只有你的数据库配置里配置了主从数据库才会起到实际的作用

    $db = Database::instance();
    $arr = $db->query('SELECT * FROM `mytable` where `id` = 1')->as_array();
    print_r($arr);

    // 支持直接返回一个stdClass对象
    $arr = $db->query('SELECT * FROM `mytable` where `id` = 1',true)->as_array();
    var_dump( $arr[0] instanceof strClass );   //bool(true)
    
    // 支持直接返回一个用户自定义对象
    $arr = $db->query('SELECT * FROM `mytable` where `id` = 1','MyClass')->as_array();
    var_dump( $arr[0] instanceof MyClass );   //bool(true)

## last_query()

返回此数据库对象最后一次执行的SQL语句

    $db->from('mytable')->where('id', 1)->get();
    echo $db->last_query();     //SELECT * FROM `mytable` where `id` = 1

## update($table = null, $value = null, $where = null)

更新数据

    $db = Database::instance();
    $data = array(
        'title' => 'test',
        'count' => 1,
    );
    $where = array(
        'id' => 1,
    );
    $rs = $db->update('test',$data,$where);
    echo $rs;       //影响的行数，若返回的是0，则表示没有更新到数据
    echo $db->last_query(); //UPDATE `test` SET `title` = 'test', `count` = 1 WHERE `id` = 1
    
    
    // 同上
    $db->where($where)->update('test',$data);
    
    // 同上
    $db->where($where)->set($data)->table('test')->update();

## insert($table = null, $value = null)

插入数据，用法基本和update()一样，只是没有where条件

    $db = Database::instance();
    $data = array(
        'title' => 'test',
        'count' => 1,
    );
    // 若操作失败返回false，否则返回一个数组
    $rs = $db->insert('test',$data);
    print_r($rs)    //返回的是一个数组，例如：array(10,1);  其中10表示自增ID号，1表示作用行数

## delete($table = null, $where = null)

删除指定条件下的数据

    $db = Database::instance();
    $rs = $db->delete('test' , array('id'=>1) );
    echo $rs;   // 作用行数，0表示没有删除数据，1表示删除1行，2表示删除了2行，以此类推
    
## count_records($table = null, $where = null)

统计指定条件下数目

    $db = Database::instance();
    
    // 返回 `class_id` = 1 条件下mytable表行数
    echo $db->where('class_id' , 1)->count_records('mytable');      

    //SELECT COUNT(1) AS `total_row_count` FROM `mytable` WHERE `class_id` = 1
    echo $db->last_query();

## replace($table = null, $value = null, $where = null)

替换数据，即MySQL的REPLACE INTO，用法同update()，返回作用行数

## merge()

replace()方法的别名
    

## table_prefix()

返回当前数据库配置表前缀，注意，只有在使用自己定义的$sql时需要注意需要自行加上表前缀，否则使用QueryBuilder构造出的SQL时系统会自动加上表前缀
    
    $db = Database::instance();
    $arr = $db->query('SELECT * FROM `'.$db->table_perfix().'mytable` where `id` = 1')->as_array();

    // 效果同上
    $arr = $db->from('mytable')->where('id', 1)->get()->as_array();

## compile($type = 'select')

构造生成SQL语句并返回

    $sql = $db->from('mytable')->where('id', 1)->compile();
    echo $sql;      //SELECT * FROM `mytable` where `id` = '1'
    
    $sql = $db->value('t',1)->table('mytable')->compile('update');
    echo $sql;      //UPDATE `mytable` SET `t` = 1

## driver()

返回当前驱动对象。目前支持MySQL和MySQLI两种类型

## auto_use_master($autoUseMaster = true)

设置是否一直在主数据库上查询
这样设置后，select会一直停留在主数据库上，直到$this->auto_use_master(false)后才会自动判断

## is_auto_use_master()

返回当前是否一直使用主数据库

## Database::parse_dsn($dsn)

解析一个类似 mysql://root:123456@localhost:3306/mytable/ 为一个数组配置格式


## transaction()

返回一个数据库事务对象