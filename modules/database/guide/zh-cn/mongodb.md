MongoDB驱动
=====

Join ON 查询
----
由于MongoDB不支持join查询，所以当采用QueryBulider时是无法构造join on的

如何获取到MongoDB对象
----
MongoDB对象文档见 [http://cn2.php.net/manual/zh/class.mongodb.php](http://cn2.php.net/manual/zh/class.mongodb.php)

	$db = new Database();	//假设这个$db是用的mongodb驱动
	
	// 这样就获取到了mongodb实例化对象
	$mongodb = $db->driver()->connection();


Group By 查询
----
首先请阅读PHP官方关于group的文档 [http://cn2.php.net/manual/zh/mongocollection.group.php](http://cn2.php.net/manual/zh/mongocollection.group.php)

	public array MongoCollection::group ( mixed $keys , array $initial , MongoCode $reduce [, array $options = array() ] )

采用group查询有2种选择：
第一种是用 $db->driver()->connection()->selectCollection($table_name)->group() 使用原生的MongoCollection对象的group方法

	// 例1
	$collection = $db->driver()->connection()->selectCollection($table_name);

	$keys = array('test_filed');
	$initial = array("count" => 0);
	$reduce = "function (obj, prev) { prev.count++; }";
	$condition = array("a" => array( '$gt' => 1));
	
	$g = $collection->group($keys, $initial, $reduce, $condition);
	
	var_dump($g);
	
以上例子的输出类似于：

	array(4) {
	  ["retval"]=>
	  array(1) {
	    [0]=>
	    array(1) {
	      ["count"]=>
	      float(1)
	    }
	  }
	  ["count"]=>
	  float(1)
	  ["keys"]=>
	  int(1)
	  ["ok"]=>
	  float(1)
	}


第二种方法是使用框架中Database的group_by方法（也是我们推荐的）：

	$rs = $db->group_by('test')->get()->as_array();

由于 `$db->group_by()` 方法只能接受1个参数，如果我们希望实现 `MongoCollection::group()` 方法后面几个参数的方法怎么做呢？可以这样实现：

	//一下写法等同上面的例1
	$fields = 'test_filed';
	$initial = array("count" => 0);
	$reduce = new MongoCode('function (obj, prev) { prev.count++; }');
	
	$g = $db->where($initial)->select($reduce)->group_by($field)->get()->as_array();
	
	var_dump($g);
	
以上例子的输出类似于：
	
	array(1) {
      [0]=>
      array(2) {
        ["_count"]=>
        float(1)
        ["count"]=>
        float(1)
      }
    }

采用Databse的group_by后get时，默认是会返回关键字为“_count”统计数据

直接运行一个Mongo命令
----==

	$db->query("function() { return 'Hello, world!'; }");

这样执行等同于用MongoDB对象执行execute()方法，详见 [http://cn2.php.net/manual/zh/mongodb.execute.php](http://cn2.php.net/manual/zh/mongodb.execute.php)

	
	$response = $db->query("function(greeting, name) { return greeting+', '+name+'!'; }", array("Good bye", "Joe"));
	echo $response['retval'];
	//将输出 Good bye, Joe!

已实现的方法
----==
除MonogDB中不支持join on外，Database的QueryBulider方法大部分都可以使用，包括：

* where()
* select() 及 select_array() 包括select的as方法
* and_where()
* and_where_open()
* and_where_close()
* or_where()
* or_where_open()
* or_where_close()
* group_by()
* order_by()
* limit()
* offset()
* update()
* insert()
* mearge() 即 replace()
* from()
* in()
* notin()
* like()
* or_like()
* mod()
* or_mod()
* table()
* columns()
* value_increment()
* value_decrement()

MapReduce
----
MapReduce是MongoDB在统计、查询及数据分析中非常有用的一个功能。目前PHP的扩展还不支持直接使用，可以使用下面的方法来执行MapReduce

	// 插入一些数据
	$data = array
	(
		"user_id" => $id, 
	    "type"    => $type, 
	    "time"    => new MongoDate(), 
	    "desc"    => $description
    );
	$db->insert('test_table',$data);
	
	// 设定map和reduce方法
	$map = new MongoCode("function() { emit(this.user_id,1); }");
	
	$reduce = new MongoCode("function(k, vals) { ".
	    "var sum = 0;".
	    "for (var i in vals) {".
	        "sum += vals[i];". 
	    "}".
	    "return sum; }"
	);
	
	// 获取MongoDB对象
	$mongodb = $db->driver()->connection();
	
	// 执行MpaReduce方法
	$sales = $mongodb->command(
		array(
		    "mapreduce" => "test_table", 
		    "map"       => $map,
		    "reduce"    => $reduce,
		    "query"     => array("type" => "sale"),
		    "out"       => array("merge" => "testCounts")
		)
	);
	
	$users = $mongodb->selectCollection($sales['result'])->find();
	
	foreach ($users as $user) 
	{
	    echo "{$user['_id']} had {$user['value']} sale(s).\n";
	}

上例将输出类似于：

	User 47cc67093475061e3d9536d2 had 3 sale(s).
	User 49902cde5162504500b45c2c had 14 sale(s).
	User 4af467e4fd543cce7b0ea8e2 had 1 sale(s).

下面是MongoDB的command参数说明：

``` javascript
db.runCommand(
 { mapreduce : <collection>,
   map : <mapfunction>,
   reduce : <reducefunction>,
   out : <see output options below>
   [, query : <query filter object>]
   [, sort : <sorts the input objects using this key. Useful for optimization, like sorting by the emit key for fewer reduces>]
   [, limit : <number of objects to return from collection, not supported with sharding>]
   [, keeptemp: <true|false>]
   [, finalize : <finalizefunction>]
   [, scope : <object where fields go into javascript global scope >]
   [, jsMode : true]
   [, verbose : true]
 }
);
```

具体请看MongoDB官方关于MapReduce手册 [http://www.mongodb.org/display/DOCS/MapReduce](http://www.mongodb.org/display/DOCS/MapReduce) 或 [官方中文文档](http://cn.docs.mongodb.org/manual/reference/commands/#mapReduce)