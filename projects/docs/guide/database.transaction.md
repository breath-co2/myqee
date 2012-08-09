数据库事务
==============
数据库事务对于关系复杂并且要求数据完整性的应用非常有用。本系统的事务支持事务内嵌套事务，但不支持跨库事务。要注意，MySQL中MyISAM表引擎是不支持事务的，推荐数据库使用InnoDB。

事务的用途
----------
当你同时需要更新（插入，替换等，甚至是修改表结构等）多个表，又希望确保所有的SQL都是成功执行的，此时你就可以考虑使用事务了。使用它可以让你程序在执行异常时将前面执行成功的SQL撤销掉，以确保不会造成残留、残缺数据甚至破坏性的数据。

比如，网站需要删除一个会员数据，而和会员相关的数据表有很多，此时，你可以开启事务，然后删除要删除的数据，最后全部成功了提交事务，操作完毕，否则若发现某个SQL有问题，可以回滚数据，这样前面删了一半的数据也不会出现异常。否则，若不用事务，当删除一半数据程序异常，结果数据库里的数据就有问题了。

总结：

* 需要保证数据完整性的多表操作推荐使用事务
* 单表操作不需要使用事务
* 对于不需要确保数据完整性的多表操作可以不用事务

简单的例子
------------
    // 以下代码可实现以下逻辑：
    // 1.当执行插入test1表失败，则回滚，
    // 2.当插入test1成功，test2失败，则回滚，test1的插入的数据也被撤销
    // 3.当插入test1,test2都成功，但是更新test3的count失败时回滚，撤销掉对test1,test2的数据插入
    // 4.只有全部都执行通过，才会提交生效
    
    // 获取一个数据库对象
    $db = Database::instance();
    // 获取事务对象
    $transaction = $db->transaction();
    // 开启事务
    $transaction->start();
    try
    {
        // 执行数据库，若下面的SQL执行失败会直接跳转到catch里
        
        // 插入test1表数据
        $db->insert('test1',array('name'=>'abc','set'=>'......'));
        // 插入test2数据表
        $db->insert('test2',array('name'=>'abc','set'=>'......'));
        // 更新test3数据表，计数+1
        $transactionatus = $db->value_increment('count', 1)->update('test3');
        if (!$transactionatus)
        {
            // 没有更新到数据？抛出
            throw new Exception('更新失败');
        }
        // 提交事务
        $transaction->commit();
        
        echo 'ok';
    }
    catch(Exception $e)
    {
        // 有错误？回滚，回滚后，已执行的SQL都不会生效
        $transaction->rollback();
        
        echo $e->getMessage();
    }

事务嵌套事务举例
--------------

    // 获取一个数据库对象
    $db = Database::instance();
    // 获取事务对象
    $transaction = $db->transaction();
    // 开启事务
    $transaction->start();
    try
    {
        // 执行数据库，若下面的SQL执行失败会直接跳转到catch里
        
        // 插入test1表数据
        $db->insert('test1',array('name'=>'abc','set'=>'......'));
        // 插入test2数据表
        $db->insert('test2',array('name'=>'abc','set'=>'......'));
        // 更新test3数据表，计数+1
        $transactionatus = $db->value_increment('count', 1)->update('test3');
        if (!$transactionatus)
        {
            // 没有更新到数据？抛出
            throw new Exception('更新失败');
        }
        
        // 再开启一个事务，此时相当于开启了一个子事务
        $transaction->start();
        try
        {
            // 插入test4表数据
            $db->insert('test4',array('name'=>'abc','set'=>'......'));
            // 插入test5表数据
            $db->insert('test5',array('name'=>'abc','set'=>'......'));
            $transaction->comment();
        }
        catch(Exception $e)
        {
            $transaction->rollback();
        }
        //////////////////////////////////////////////////////////////////////////////////////// 
        // 若插入test4,test5成功，则会提交子事务，否则回滚子事务。
        // 但你需要明白：因为它是属于前面的一个事务，所以若最后前面的事务也回滚了，那么这些操作都会被回滚
        // 
        // 这样就会有以下情况发生：
        // 1.父事务成功，子事务成功 - 所有的SQL都执行
        // 2.父事务成功，子事务失败 - 父事务的SQL语句被执行，子事务的SQL被撤销
        // 3.父事务失败　　　　　　 - 无论子事务是否成功，所有的SQL都被撤销
        ////////////////////////////////////////////////////////////////////////////////////////
        
        
        // 提交事务
        $transaction->commit();
        
        echo 'ok';
    }
    catch(Exception $e)
    {
        // 有错误？回滚，回滚后，已执行的SQL都不会生效
        $transaction->rollback();
        
        echo $e->getMessage();
    }


start()
---------------
开启事务，若之前已经开启了一个事务，则开启的会是一个子事务，支持多级嵌套
    
    // 获取事务对象
    $transaction = Database::instance()->transaction();
    // 开启事务
    $transaction->start();

commit()
---------
提交事务，开启事务后执行的SQL只有在commit()后才会生效

rollback()
-----------
回滚事务，若发现有异常可以执行回滚函数撤销之前执行的SQL

    // 获取事务对象
    $transaction = Database::instance()->transaction();
    // 开启事务
    $transaction->start();
    Database::instance()->insert('test1',array('title'=>'abc'));
    $st = Database::instance()->insert('test2',array('title'=>'456'));
    if ($st)
    {
        // 提交
        $transaction->commit();
    }
    else
    {
        // 回滚
        $transaction->rollback();
    }

is_root()
-----------
判断当前是否父事务。若返回true则表示它上面没有事务了，否则表明它上面还有一个或多个事务
    