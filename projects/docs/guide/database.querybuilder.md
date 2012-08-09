QueryBuilder
=================

select()
----------
select(c1,c2,c3,......)
查询字段
    
    // SELECT `id`,`username` as `user`
    $db->select('id','username as user');

select_array()
----------
select(array(c1,c2,c3,......))
    
    // SELECT `id`, `username` AS `user`
    $db->select_array(array('id','username as user'));

from()
----------
    
    //FROM `tablename`
    $db->from('tablename');
    
where()
----------

    // WHERE `id` = '1'
    $db->where('id',1);
    
    // WHERE `class_id` = '1' AND `status` = '2'
    $db->where(array('class_id'=>1,'status'=>2));
    
    // WHERE `time` >= '1234567890'
    $db->where('time',1234567890,'>=');

and_where_open() , and_where_close() , and_where() , or_where()
-----------

    // WHERE `id` = 2 OR `id` = 3
    $db->where('id',2)->or_where('id',3);

    // WHERE `id` = 1 AND (`test` = 1 AND `test` = 2)
    $db->where('id',1)->and_where_open()->where('test',1)->where('test',2)->and_where_close();

    // WHERE `id` = 3 AND (`test` = 1 OR `test` = 2) 
    $db->where('id',3)->and_where_open()->where('test',1)->or_where('test',2)->and_where_close();

where_open() , where_close()
-----------
where_open() 等同 and_where_open() ， where_close() 等同 and_where_close()

or_where_open() , or_where_close()
-----------

    // WHERE `id` = 1 OR (`test` = 1 AND `test` = 2) 
    $db->where('id',1)->or_where_open()->where('test',1)->where('test',2)->or_where_close();

    // WHERE `id` = 3 OR (`test` = 1 OR `test` = 2) 
    $db->where('id',3)->or_where_open()->where('test',1)->or_where('test',2)->or_where_close();

    
in()
---------

    // WHERE `id` IN ('1','3','5') 
    $db->in('id',array(1,3,5));
    
notin()
---------

    // WHERE `id` NOT IN ('1','3','5') 
    $db->notin('id',array(1,3,5));

    
join() , on()
----------

    // 注意，join和on的顺序不可颠倒
    
    // FROM `tb1` JOIN `tb2` ON `tb2`.`id` = `tb1`.`id`
    $db->from('tb1')->join('tb2')->on('tb2.id','tb1.id');

    // FROM `tb1` LEFT JOIN `tb2` ON `tb2`.`id` = `tb1`.`id`
    $db->from('tb1')->join('tb2','LEFT')->on('tb2.id','tb1.id');

limit()
----------

    // LIMIT 10
    $db->limit(10);
    
    // LIMIT 20 OFFSET 10
    $db->limit(20,10);

offset()
-----------

    // LIMIT 20 OFFSET 10
    $db->limit(20)->offset(10);

order_by()
-----------

    // ORDER BY `id` DESC
    $db->order_by('id','DESC');

group_by()
-----------

    // GROUP BY `test`
    $db->group_by('test');

value_increment() 递增
-----------

    // `v` = `v` + 3
    $db->value_increment('v',3);

    // `v` = `v` + 1
    $db->value_increment('v');

value_decrement() 递减
-----------

    // `v` = `v` - 1
    $db->value_decrement('v');

having() , and_having() , or_having() , having_open() , having_close()
-----------

    // HAVING `id` = 1
    $db->having('id', 1);
    
    // HAVING `id` = 1 AND `id` = 2
    $db->having('id', 1)->and_having('id', 2);
    
    // HAVING `id` = 3 AND (`id` = 1 AND `id` = 2) 
    $db->having('id',3)->having_open()->having('id',1)->having('id',2)->having_close();
    
    // WHERE `id` = 3 HAVING (`id` = 1 OR `id` = 2) 
    $db->where('id',3)->having_open()->having('id',1)->or_having('id',2)->having_close();
   

or_having_open() , or_having_close()
-----------

    // HAVING `id` = 1 OR (`test` = 1 AND `test` = 2) 
    $db->having('id',1)->or_having_open()->having('test',1)->having('test',2)->or_having_close();

    // HAVING `id` = 3 OR (`test` = 1 OR `test` = 2) 
    $db->having('id',3)->or_having_open()->having('test',1)->or_having('test',2)->or_having_close();


like() , or_like()
------------

    // `test` LIKE '%tes%'
    $db->like('test','%test%');

    // `test` OR LIKE '%tes%'
    $db->or_like('test','%test%');

    // WHERE `id` = 1 AND (`test` = 1 OR `dd` LIKE '%abc%')
    $db->where('id',1)->and_where_open()->where('test',1)->or_like('dd','%abc%')->and_where_close();
    
mod() , or_mod()
------------
v2.0正式版新增

    // `test` mod 10 = 1
    $db->mod('test',10,1);

    //OR `test` mod 10 = 1
    $db->or_like('test',10,1);

	// `test` mod 10 != 1
    $db->mod('test',10,1,'!=');

reset()
------------
重置所有查询

set()
-------------
为update,insert设置数据

    // SET `id` = 1,`title` = 'aaa'
    $db->set( array('id'=>1,'title'=>'aaa') );

value()
-------------
为update,insert设置数据

    // UPDATE `test_table` SET `name`='newname' WHERE `id` = 1
    $db->value('name','newname')->where('id',1)->update('test_table');
    // 等效于
    $db->update('test_table',array('name'=>'newname'),array('id'=>1));

columns() 和 values()
-------------
批量插入多条数据时可用到此方法。columns()是指定字段，values()是设置多条数据的数组

	// 字段名
	$c = array
	(
		'id',
		'name',
		'sex',
	);
	// 测试数据
	$v = array();
	$v[] = array(1,'name1','男');	//一行数据，字段名由$c来指定
	$v[] = array(2,'name2','女');
	$v[] = array(3,'name3','女');
	
	//执行批量插入3条数据
	$db->columns($c)->values($v)->insert('user');
	
	// 上例将执行如下SQL：
	// INSERT INTO `user` (`id`, `name`, `sex`) VALUES (('1', 'name1', '男'), ('2', 'name2', '女'), ('3', 'name3', '女')) 
	

distinct()
--------------

    // SELECT DISTINCT
    $db->distinct()
    
force_index()
--------------
查询时强制使用指定的索引

	// FORCE INDEX('test')
	$db->force_index('test');
	
ignore_index()
--------------
查询时需要或略的索引

	// IGNORE INDEX('test')
	$db->ignore_index('test');
	

Database::expr_value()
-------------
传入一个不被处理的数据，静态方法

    // SELECT SUM(ITEMNUM) as num FROM `test`
    $db->select( QueryBuilder::expr_value('SUM(ITEMNUM) as num') )->from('test');

