OOP ORM Result
============
采用 ORM 的 find() 将返回Result对象。
例如：
`ORM_Test_Finder` 返回的是 `ORM_Test_Result`
`ORM_Test_MyTest_Finder` 返回的是 `ORM_Test_MyTest_Result`

每一个ORM不是必须要有对应的 Result 的，若不存在对应的对象，则默认返回 `OOP_ORM_Result`

get_uniqid()
-------------
返回Result的唯一ID


as_array()
-------------
返回数据对象，同 `getArrayCopy()`

OOP_ORM_Result::get_group_data($id)
-------------
根据唯一ID返回对象组。静态方法