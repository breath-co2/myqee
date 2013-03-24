## 类的扩展

### 在项目中扩展类库

MyQEE V3开始提供了一套更为完善的扩展机制，核心类库都是以`Core_`开头命名的，比如: Core_Database, Core_Model, Core_Controller 等等。第三方类库都以`Library_{TeamName}_{LibName}_{ClassName}`格式命名的，比如:Library_MyQEE_Test_Database。

当你需要对这些类库进行扩展修改时，可直接扩展到Ex_开头的同名类库上，而不需要直接扩展到类库的真是名称上

比如需要扩展Core_Database (或 Library_MyQEE_Test_Database)，那么只要这些做：

    class Database extends Ex_Database
    {
        // your code
        public function test()
        {
        }
    }

将此内容存放在项目的`classes/database.class.php`中即可。

你会注意到，Database并不是extends到Core_Database，也不是 Library_MyQEE_Test_Database，而是Ex_Database，这是因为自V3开始使用了一种只能的魔术扩展方式，可以对Core类库进行多次扩展，解决了V2中类库和项目无法同时扩展的矛盾。

!!! MyQEE V2是没有这样的逻辑的，MyQEE V2中应该是扩展到MyQEE_前缀的同名类上