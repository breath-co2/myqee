ORM 使用入门
================

MyQEE ORM 基础
----------------
ORM对象存放在orm/目录，文件存放规则类似 classes 但后缀为.orm.php，每个ORM都有3个类，分为:

* ORM_\*\*\*_Finder, 查询工具，相当于Model的功能
* ORM_\*\*\*_Result, 返回查询集(可不设置)，相当于SQL查询返回的结果集
* ORM_\*\*\*_Data，   数据，相当于每一条数据

建立一个简单的ORM
----------------

例如，用户表(`members`)，有字段：`id`, `username`, `email`, `sex` 等字段，现需要建立一个 `Member` 的 ORM，
那么，在 `orm/member.orm.php` 文件，内容如下:

    // orm/member.orm.php 文件内容如下:
    
    <?php
    //Finder
    class ORM_Member_Finder extends OOP_ORM_Finder_DB
    {
        /**
         * 数据库配置
         */
        protected $database = 'default';
        
        /**
         * 表名称
         */
        protected $tablename = 'members';
        
        /**
         * 根据email获取用户对象
         * 
         * @return ORM_Member_Data
         */
        public function get_member_by_email($email)
        {
            return $this->where('email',$email)->find()->current();
        }
        
        /**
         * 返回所有男姓会员对象集合
         * @return ORM_Member_Result
         */
        public function get_all_man()
        {
            return $this->where('sex',1)->find();
        }
    }
    
    //Result，此对象不设置的话，系统将直接采用OOP_ORM_Result对象
    class ORM_Member_Result extends OOP_ORM_Result
    {
    
    }
    
    //Data
    class ORM_Member_Data extends OOP_ORM_Data
    {
        /**
         * 用户ID
         * 注意，这里面的值并非这个对象的值，而是此键值的配置，下同，具体可参考“ORM字段属性”章节
         */
        public $id = array(
            'field_name'  => 'id',
            'is_id_field' => true,          //ID字段
        );
        
        /**
         * 用户名
         */
        public $username  = array(
            'field_name' => 'username',
            'is_readonly' => true,          //只读
        );
        
        /**
         * 邮箱
         */
        public $email;
        
        /**
         * 性别
         */
        public $sex;
        
        /**
         * 返回空间地址
         */
        public function space_url()
        {
            return 'http://www.meqee.com/u/'.$this->id.'/';
        }
    }

使用方法：

    // 实例化ORM Finder对象
    $orm_member = new ORM_Member_Finder();
    // 根据email获取用户对象，返回的是ORM_Member_Data这个对象
    $member = $orm_member->get_member_by_email('test@abc.com');
    
    var_dump($member);
    $member->id = 2;                    // 这样做无效，因为id为只读字段
    echo $member->id;                   // 当前用户ID
    echo $member->space_url();          // 返回用户空间地址，执行的是space_url方法
    
    // 修改数据
    $member->email = 'test@abc.com';    // 设置新数据
    $member->update();                  // 执行更新操作，没有SQL语句?真棒!
    
    // 删除数据
    $member->delete();                  // 删了这个数据
    
    // 获取男会员
    $man_members = $orm_member->get_all_man();  // 返回的是 ORM_Member_Result 对象
    foreach ( $man_members as $item)
    {
        echo $item->username . '<br>';
    }
    
    
    // 创建一个新数据
    $orm_member = new ORM_Member_Finder();
    $member = $orm_member->create();
    $member->email = 'test@abc.com';
    $member->sex = 1;
    $member->username = 'test';
    $member->insert();
    echo $member->id;   // 此时，$member已经获取到插入的ID了
    
