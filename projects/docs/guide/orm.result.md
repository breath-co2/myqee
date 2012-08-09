OOP ORM Result
============
采用ORM的find()将返回Result对象。
例如：
ORM_Test_Finder返回的是ORM_Test_Result
ORM_Test_MyTest_Finder返回的是ORM_Test_MyTest_Result

每一个ORM不是必须要有对应的Result的，若不存在对应的对象，则默认返回OOP_ORM_Result

get_uniqid()
-------------
返回Result的唯一ID


as_array()
-------------
返回数据对象，同getArrayCopy()

OOP_ORM_Result::get_group_data($id)
-------------
根据唯一ID返回对象组。静态方法