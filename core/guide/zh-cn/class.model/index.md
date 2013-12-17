模型 (Model)
====================
模型通常是给控制调用获取数据。

命名规则
---------------
同class文件一样：模型名和文件名保持一致，并把_替换成/且大小写敏感，以 `.model.php` 为后缀。
例如 `Model_Abc_TEST_Aaa` 存放的文件名应该是 `models/abc/test/aaa.model.php` 文件。

!!! 在V2版本里，文件名是需要和类名称一样大小写的，在V3版本里，全部为小写字母

创建一个模型。
----------------

    //models/post.model.php
    class Model_Post extends Model
    {
        public function do_stuff()
        {
            // This is where you do domain logic...
        }
    }


扩展到另外一个模型：

    //models/post/abc.model.php
    class Model_Post_ABC extends Model_Test
    {
        public function do_stuff()
        {
            // This is where you do domain logic...
        }
    }


$this->db()
-----------------------
返回一个数据库实例化对象。可以设置 `$this->database` 值已调用需要的数据库默认配置，$this->database默认值是default。


$this->cache()
-----------------------
获取Cache实例化对象可以设置 `$this->cache_config` 值已调用需要的缓存默认配置， `$this->cache_config` 默认值是 `default`。
例如 `$this->cache()->get($key);` 可获取指定 $key 的缓存。

$this->get_cache_data()
-----------------------
获取一个缓存数据。
本功能无需传递参数，程序会根据类名称，以及传递给模型方法的参数自动生成一个唯一$key。

    class Model_Post extends Model_Test
    {
        public function get_by_id($id)
        {
            $data = $this->get_cache_data();
            if ( $data===null)
            {
                //获取数据...
                $data = ...;
                
                //将数据设置到缓存中
                $this->set_cache_data( $data );
            }
            return $data;
        }
    }
    
    $model_post = new Model_Post();
    $model_post->get_by_id(1);
    $model_post->get_by_id(2);

以上的例子您或许会疑问当执行 `$model_post->get_by_id(1)` 和 `$model_post->get_by_id(2)` 时，程序是否会错误的调用了同一个缓存，因为 `$this->get_cache_data()` 方法是没有传递任何参数的。
对于这样的疑问您可以打消，因为虽然 `get_cache_data()` 或 `get_cache_data()` 调用时没有传递任何参数，但是系统会根据类名称(即Model_Post)和 get_by_id() 传递进去的参数（即array(0=>1)和array(0=>2)）产生一个唯一的$key，所以能够保证获取和设置缓存都是一一对应的。
此功能并不考虑对象乃至整个程序环境变量的差异，这也就意味着，假如您这个方法中获取数据需要依赖一些特定的变量来处理，这个功能或许不适用。
另外，对于扩展后的对象，产生的$key也是不一样的。例如当上例中，如果一个类扩展时：

    class Model_Post2 extends Model_Post
    {
        ...
    }
    
    $model_post2 = new Model_Post2();
    $model_post2->get_by_id(1);
    $model_post2->get_by_id(2);

这样 `$model_post->get_by_id(1)` 和 `$model_post2->get_by_id(1)` 会被认为不相同的，因为类名分别是 `Model_Post` 和 `Model_Post2` ，他们会分别读取和设置自己的缓存。
对于这种情况，如果您希望避免出现错误，可采用标准的缓存类 $this->cache() 去自己获取和设置缓存。


$this->set_cache_data( $data, $exp = 3600 , $type = Cache::TYPE_MAX_AGE )
-----------------------
设置一个缓存数据，原理同 `$this->get_cache_data()`。

* $data 数据内容
* $exp  过期数据
* $type 过期类型



$this->error($message, $no = 0)
-----------------------
设置一个错误，此方法只可能不调用


$this->get_error()
-----------------------
获取通过 `$this->error()` 设置的错误的 `Exception` 对象


让Model拥有ORM特性
-----------------------
Model可扩展至ORM上以获得更加强劲的功能支持

	//例如一个测试model扩展至一个MyTest的ORM Finder
	Model_Test extends ORM_MyTest_Finder
	{
		/**
		 * 根据name获取一个对象
		 *
		 * @return ORM_MyTest_Data 对象
		 */
		public function get_test($name)
		{
			return $this->where('field_name',$name)->limit(1)->find();
		}
	}
