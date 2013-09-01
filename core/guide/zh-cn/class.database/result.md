数据库Result
==============

SELECT 查询返回对象集
------------
通常，查询都是用 Database 的 `get()` 方法执行的，或可以直接用Database的 `query()` 方法直接执行SQL语句。
返回的结果对象为 `Database_Result`

***as_array() - 将查询结果返回为数组***

    // 返回 SELECT * FROM `member` WHERE `id` = '1' 查询的结果的数组
    $db->from('member')->where('id',1)->get()->as_array();
    // 同上
    $db->query('SELECT * FROM `member` WHERE `id` = 1')->as_array();

***count() - 返回查询结果的总行数***


    // 返回 SELECT * FROM `member` WHERE `id` = '1' 查询的结果的行数
    $db->from('member')->where('id',1)->get()->count();
    // 或
    count( $db->from('member')->where('id',1)->get() );

***current() - 返回当前指针的行数据***

    // 返回 SELECT * FROM `member` WHERE `id` = '1' 查询的结果的第一行（数组）
    $db->from('member')->where('id',1)->get()->current();
    
    // 同上，但返回的结果为一个stdClass对象
    $db->from('member')->where('id',1)->get(true)->current();
    
    // 同上，返回的结果为一个MyClass对象
    $db->from('member')->where('id',1)->get('MyClass')->current();

***fetch_array() - 返回当前行，并且把指针向下移动一次***

    $rs = $db->from('member')->where('id',1)->get();
    while ( $data = $rs->fetch_array() )
    {
        //$data
    }

***get($key) - 获取当前指针下指定key的值***

    // SELECT * FROM `member` WHERE `id` = '1'
    // $id = 1
    $id = $db->from('member')->where('id',1)->get()->get('id');

***key() - 返回当前指针的key值***

    // $key = 0;
    $key = $db->from('member')->where('id',1)->get()->key();

***valid() - 当前指针是否有效***

***rewind() - 指针复位***

***prev() - 指针上移***

***next() - 指针下移***

***seek($offset) - 返回指定指针是否有效***

INSERT / REPLACE 插入,替换数据返回
-----------------
返回结果为一个数组:array(插入ID,作用行数)


UPDATE 更新数据返回
-----------------
返回结果为作用行数,0表示未更新数据