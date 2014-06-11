OOP ORM Data
============
数据对象，每一条数据对应一个对象，此对象继承 `ArrayIterator`

例如：<br>
`ORM_Test_Finder` 返回的是 `ORM_Test_Data`<br>
`ORM_Test_MyTest_Finder` 返回的是 `ORM_Test_MyTest_Data`


as_array()
------------
返回数组同 `getArrayCopy()`

set_data(array $data)
------------
批量设置数据

    $orm_test = ORM_Test_Finder();
    $test = $orm_test->create();
    $data = array(
        'id' => 1,
        'title' => '标题',
        'create_time' => TIME,
    );
    // 设置数据
    $test->set_data($data);
    
    echo $test->title;      //标题

get_id_field_name()
------------
获取ID字段
    
    ORM_Test_Data extends OOP_ORM_Data
    {
        // ID 字段
        public $id = array(
            'field_name' => 'id',
            'is_id_field' => true,      // 此设置表明id为ID字段
        );
        
        public $create_time;
    }
    
    $data = new ORM_Test_Data();
    echo $data->get_id_field_name();     //id
    
get_group_data()
-------------
返回当前数据所在组的所有Data数据

    $orm_test = ORM_Test_Finder();
    // 返回所有id<10的数据
    $result = $orm_test->where('id',10,'<')->find();
    
    // 获取第一条数据
    $one = $result->current();
    
    // 获取这一组数据
    $group_data = $one->get_group_data();
    
    var_dump($group_data===$result);        // (bool)true

update()
--------------
更新数据，对对象设置数据并不会直接执行SQL，只有执行update()方法才会更新

    $orm_test = ORM_Test_Finder();
    // 获取ID=1的数据
    $test = $orm_test->get_by_id(1);
    
    $test->status = 2;
    $test->title = '新标题';
    //更新数据
    $test->update();
    
    //UPDATE `test` SET `status` = 2,`title` = '新标题' WHERE `id` = 1;
    echo $orm_test->last_query();

insert()
--------------
    $orm_test = ORM_Test_Finder();
    // 返回一个新构造的Data对象，也可以直接$test = new ORM_Test_Data();但推荐用create()获取
    $test = $orm_test->create();
    
    $test->title = 'abc';
    $test->create_time = TIME;
    $test->update();
    
    //看看刚刚执行了什么SQL
    //INSERT INTO `test` SET `title`,`create_time` VALUES ('abc',1234567890);
    echo $orm_test->last_query();
    
    echo $test->id;     //返回新插入的ID

delete()
-------------
删除当前数据

    $orm_test = ORM_Test_Finder();
    // 获取ID=1的数据
    $test = $orm_test->get_by_id(1);
    // 删除
    $test->delete();

get_field_data()
-------------
获取所有字段对应的数据，返回的数据的key是数据库字段名，value是数据值

get_changed_data()
-------------
返回修改过的数据，结构同 `get_field_data()`，也是 key 是数据库字段名，value 是数据值