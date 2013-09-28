### 2013-09-24

* 开发版assets实时输出控制器完善 - [Link](https://github.com/breath-co2/myqee/commit/9144c95e20de03e309e172dee22f862a41fcceb9)
* 完善 `Core::find_file()` 获取自定义目录文件夹 - [Link](https://github.com/breath-co2/myqee/commit/04791671b2d56b1bc9cbd4bd2642da5ee9138c50)
* 完善 `Core::url_assets()` 方法的输出，完善 `merge-assets` 脚本输出，对独立后台不处理前端 assets - [Link](https://github.com/breath-co2/myqee/commit/37de26c8e0ab313e6238d2fbe01a319bb038ee72)

### 2013-09-23

* 完善merge-assets脚本，支持后台assets的生成，支持less，scss，sass的编译输出和css，js的压缩 -[Link](https://github.com/breath-co2/myqee/commit/3156facae167c0c52a97bc06cf366362e90b8e30)


### 2013-09-22

* 完善assets输出，支持sass编译输出css - [Link](https://github.com/breath-co2/myqee/commit/93e6905bb286f954d52dd4e42bd9eda6a9b2f429)

### 2013-09-12

* 后台类库完善控制器和Session - [Link](https://github.com/breath-co2/myqee/commit/2eecc5166450b3a0e90dfd2b828775f6735ab063)
* 完善类库和ORM的自动加载 - [Link](https://github.com/breath-co2/myqee/commit/1879b871f7709975a96da1fc7a5e3f3a2cfcad74)
* 数据库各驱动独立为单独的驱动包 - [Link](https://github.com/breath-co2/myqee/commit/d4919343d647f14600280b14f9314faa295fc7b0)
* 增加 driver 的支持 - [Link](https://github.com/breath-co2/myqee/commit/67d29e4d32ef139da082c5cfe788c28de7ee4517)


### 2013-09-11

* 优化Module的目录结构 - [Link](https://github.com/breath-co2/myqee/commit/cd3ca34ddb1ad65ad630b8ab57e51e2f65ab7fe3)
* 支持控制器完善，支持文件控制器中 `action_index()` 调用，比如 `/test/abc/` 可直接调用 `test/abc.controller.php` 中的 `action_index()` 方法，但 `/test/abc/123/` 则不会触发 `action_index()` - [Link](https://github.com/breath-co2/myqee/commit/e762ec2c62f05624c9c33f249851eca9439cf0ec)
* `Storage` 独立为模块 - [Link](https://github.com/breath-co2/myqee/commit/3f1fd75db5226ae604b340b1a7e0695412a9e7fb)
* `HttpClient` 独立为模块 - [Link](https://github.com/breath-co2/myqee/commit/b723d059fc53c0c1696a75437b45c2ea5ade13a2)
* `Session` 独立为模块 - [Link](https://github.com/breath-co2/myqee/commit/955e0e0a68298e4c997b1e6154d45a16b73d0b3a)
* 增加 `Cache` 和 `Database` 的模块配置 - [Link](https://github.com/breath-co2/myqee/commit/180e262f21a041ba44e4b8d88e5c6ba73a1e3dc3)
* `OOP_ORM` 独立为模块 - [Link](https://github.com/breath-co2/myqee/commit/f03b6816cbacf7290bf4083c8c8ca36755264de3)
* 优化module目录接口，移除对于module里的classes目录，直接使用当前module目录为classes目录 - [Link](https://github.com/breath-co2/myqee/commit/4b7a191db528875857f2293c392d2e8c74a1b362)
* `Cache` 移动到模块里 - [Link](https://github.com/breath-co2/myqee/commit/a238f788ca7f4af6c4e66edf2ab8c555457ca230)


### 2013-09-06

* 增加 `IS_OPEN_PROFILER` 常量，此常量标记是否开启页面分析功能 - [Link](https://github.com/breath-co2/myqee/commit/67547c4b75201a1f97711e5c8ab4288749907a6b)

### 2013-09-04

* 数据库移到单独module里 - [Link](https://github.com/breath-co2/myqee/commit/bdcb41634ba6e7b69ba6c30f0c001ed74fedbbe7)
* 增加 module 目录支持 - [Link](https://github.com/breath-co2/myqee/commit/c817ffe7cc2d52ceca293157f5d2ad8f23f705b2)

### 2013-08-29

* 增加 `Composer` 的支持 - [Link](https://github.com/breath-co2/myqee/commit/cc756dd459c326b3c310011dc92f110f1b6b4833)
* MongoDB数据库类完善对group by的处理，支持 MongoClient - [Link](https://github.com/breath-co2/myqee/commit/8d362bcf65d24e1012b1ac4bd6c076f79f2218dd)


### 2013-08-22

* 增加RESTFul的支持 - [Link](https://github.com/breath-co2/myqee/commit/85bc78da5c3235db0fc46b08b2f9a0d5dbda43c0)
* 更新 LICENSE - [Link](https://github.com/breath-co2/myqee/commit/fc11a964ce273edf8684817fdb4bd6c743d89ddc)


### 2013-08-20

* `team_library` 目录更改为 `team-library` 目录 - [Link](https://github.com/breath-co2/myqee/commit/8d7ded4b58bea4e228e4f71f23db16570b74043d) [Link](https://github.com/breath-co2/myqee/commit/620aec3d2e12d4d2cfe034cd581b5f33c1f71e2b)

### 2013-08-16

* 解决index控制器在生成的分页路径中出现错误的index/index/ 的问题 - [Link](https://github.com/breath-co2/myqee/commit/f937a8dbb020461abd299fbb3be5ffcc009fa303)


### 2013-08-15

* 修改 `index.controller.php` 和 `default.controller.php` 的优先级，设置前者为首页文件控制器，后缀为默认控制器(任何不存在的同目录控制器都可由此控制器处理，效果等同于 `action_default` 的作用)，解决 `index.controller.php` 的分页问题 - [Link](https://github.com/breath-co2/myqee/commit/a793ded0ece05c2b3dea1c423541756dc4d5206a)


### 2013-08-13

* 完善控制器的获取逻辑，修复分页类库分页显示错误的bug - [Link](https://github.com/breath-co2/myqee/commit/879bc00196f6203f29c141c070622873f9904732)
* 完善默认控制器传送参数的获取 - [Link](https://github.com/breath-co2/myqee/commit/7938cf2213c9221c494c606d2825f17265514b76)
* 增加对目录内 `index.controller.php` 的全匹配支持，增加 `default.controller.php` 的支持，当前路径下， `default.controller.php` 为首页文件优先级高于 `index.controller.php`
当没有找到任何控制器，会读取 `index.controller.php` 控制器 - [Link](https://github.com/breath-co2/myqee/commit/30d139fa64c59b1b53423d3afd0038cee7d4381f)


### 2013-08-12

* 完善 `Access-Control-Allow-Origin` 的输出 - [Link](https://github.com/breath-co2/myqee/commit/c96a4fa02e167c234cd1d79c9eca72c63c9bbb79)
* config配置增加 `$config['hide_x_powered_by_header']` 和 `$config['ajax_cross_domain']` 参数 - [Link](https://github.com/breath-co2/myqee/commit/60c0c772bcdde710891c50a1348e51309ac8758a)
* Admin, Shell, System 控制器目录下划线改成横线， 由 `controllers_admin`, `controllers_shell`, `controllers_system` 分别改成 `controllers-admin`, `controllers-shell`, `controllers-system` - [Link](https://github.com/breath-co2/myqee/commit/d382c118dbe5ee2e07bbef49c66783c531d18663)
* 解决在部分配置PATH_INFO情况下导致多传wwwroot路径错误的bug - [Link](https://github.com/breath-co2/myqee/commit/5bb493d72c9a4478669883650744939e7eccde2f)


### 2013-08-07 

* 数据库增加 `set_builder($builder)` 和 `recovery_last_builder()` 方法 - [Link](https://github.com/breath-co2/myqee/commit/bcd6b8dfc8595917845bcb0f8363b4bd6cdb66dc)
* 数据库增加 `recovery_last_builder()` 方法，可以恢复上一次被reset()时的builder - [Link](https://github.com/breath-co2/myqee/commit/11dca0bd93c02aaad48faf9451d6dc04945bc57a)


### 2013-07-29

* runtime_config 读取配置完善 - [Link](https://github.com/breath-co2/myqee/commit/dfbe68530b331ccf4e5ddddd704c257736aaa477)
* 增加 `Core::RELEASE` 常量。用来标识程序发布的版本状态，有(但不限于) `stable`, `rc1`, `rc2`, `beta1`, `beta2` 等 - [Link](https://github.com/breath-co2/myqee/commit/4ad609a89b5491b0af910c87e86177c7e7325285)
* 系统配置中增加 $config['runtime_config'] 参数，可对不同环境设置加载runtime配置，取代原来的 `$config['debug_config']` 配置 - [Link](https://github.com/breath-co2/myqee/commit/8296a9b3c14d5da593c301099821a3d291f50fd7)
* 修复带横线的控制器无法获取的Bug - [Link](https://github.com/breath-co2/myqee/commit/1c82ff86c9912466e2b9a9ba25901aca3071a66c)


### 2013-07-24

* 完善ErrException抛出类库代码 - [Link](https://github.com/breath-co2/myqee/commit/2238e7407990c4da64e3539583675b2a453afdfc)
* 增加 `View::get_global_data()` 方法 - [Link](https://github.com/breath-co2/myqee/commit/6dfdffedd6fb91d2c2e8000ce64e5c23a23e3d80)

### 2013-07-21

* 完善ORM Parse解析function类的bug - [Link](https://github.com/breath-co2/myqee/commit/d02f62a370db7bf88132acc4206aa2c3b3f60bd5)


### 2013-06-20

* 完善ORM REST方式, 数据库类中TYPE全部改成大写 - [Link](https://github.com/breath-co2/myqee/commit/a7c00ae60210d63a7a222371ad7a21a36c733b38)
* 增加 `PostgreSQL` 数据库驱动 - [Link](https://github.com/breath-co2/myqee/commit/575d6a78dfd95e5fa559b3ecdd48b3e54387ed29)


### 2013-05-26

* 优化对文件写入的处理 - [Link](https://github.com/breath-co2/myqee/commit/20b4904e6b785d306ac9d6400d7f9aba4d45382f)


### 2013-05-23

* 完善Session，后台Session更新,ORM `get_by_id()` 方法增加 `$use_master` 参数 - [Link](https://github.com/breath-co2/myqee/commit/2bbb30f66217f6485e61e78a9b3c28fd024034eb)


### 2013-04-26

* 更新文档工具对ORM的输出 - [Link](https://github.com/breath-co2/myqee/commit/3378ba479b817a5dc2b32d054e010585bed8e554)

### 2013-04-07

* 修正某些情况下会导致 `Invalid multibyte sequence in argument` 的错误 - [Link](https://github.com/breath-co2/myqee/commit/cf88c46d1b1128746915915d7aaa81f41bea3307)


### 2013-03-28

* 完善文档生成脚本，当2个文件的md5一致时，且没有-a(--all)参数时不做复制文件操作，这样可忽略2个文件修改时间不一致的情况 - [Link](https://github.com/breath-co2/myqee/commit/fecdcc004346845ff2da1fd64b05105a7a69cb9b)
* svn-tools 工具更新，增加将一个文件夹同步到一个带svn版本控制的目录，如果有多余的文件，则会利用svn目录删除多余的文件 - [Link](https://github.com/breath-co2/myqee/commit/0a541bed063d9e85bf37f3a69d4958871be8dea9)
* 将 shell, admin, system 控制器目录改为和 `controllers` 同目录下的 `controllers_shell`,
`controllers_admin`, `controllers_system` 目录，解决部分主机下不支持特殊目录的问题 - [Link](https://github.com/breath-co2/myqee/commit/a3e48329aae85d920a808a9d3b5de2792aa346c0)
* 完善shell文件夹下控制器代码，完善 `Controller_Shell::getopt()` 获取参数命令 - [Link](https://github.com/breath-co2/myqee/commit/318069e083075f0ec503076c325e6a22714493ee)


### 2013-03-26

* 文档生成工具完善，增加todo list功能，优化API列表 [Link](https://github.com/breath-co2/myqee/commit/c5f37029c49ee26916e9748eb0949f1cef78b042)


### 2013-03-12

* `Core::$core_config` 改为 `Core::$config` - [Link](https://github.com/breath-co2/myqee/commit/0d2d23c23335a1fe3171064edf4e74d6490aff32)
* 优化 `$config` 的读取 - [Link](https://github.com/breath-co2/myqee/commit/cd28c7ac8baced78c3b38f9913fb5650f0c5efa8)
* Model 和 ORM 的数据库连接对象使用自身独立构造出的对象，避免和 `Database::instance()` 中的对象在使用QueryBuilder 时产生冲突 - [Link](https://github.com/breath-co2/myqee/commit/cc4a15143fa011c574cca6e52437a9742cccae1d)


### 2013-03-11

* `Bootstrap::$config` 默认继承 `Bootstrap::$core_config` 总配置 - [Link](https://github.com/breath-co2/myqee/commit/3ce885fe6bafbef3c3176eca5d48fd936a75a7b2)
* 完善根据 `$_SERVER["SCRIPT_NAME"]` 获取 `Bootstrap::$base_url` 的方法 - [Link](https://github.com/breath-co2/myqee/commit/ce4888eeb1495066ee81cc88d5aa5cb8de4c259e)
* 修复在子目录下运行MyQEE时获取base_url时右侧缺少/的bug - [Link](https://github.com/breath-co2/myqee/commit/5eaaaafc208805462f7323c39947c90ea735336e)


### 2013-02-06

* 完善 `Bootstrap::import_library` 回调 [Link](https://github.com/breath-co2/myqee/commit/b0b2fe05d128b861fa1e4924fe33ed392c774e7c)


### 2013-02-05

* 增加 `Core::change_project_add_callback()` 和 `Core::import_libraray_add_callback()` 方法 - [Link](https://github.com/breath-co2/myqee/commit/0e187ce4c580585a696e153270f32614fc619d21)
* 寻找控制器支持路由功能 - [Link](https://github.com/breath-co2/myqee/commit/a5f13754bfc2779bc7f7cde239882fc90ca86230)
* 修复 `Bootsgtrap::autoload` 方法 - [Link](https://github.com/breath-co2/myqee/commit/2794357788d60c2cab7fc351b8e84e71a3200805) [Link](https://github.com/breath-co2/myqee/commit/c85d2dc13e4ef133075443652b58b5d90bb70552)
* `Swift` 存储驱动优化 - [Link](https://github.com/breath-co2/myqee/commit/028512473d8198303b9b9d9b87a94d93398df6dc)
* 将 `Bootstrap::execute`, `Bootstrap::find_controller` 等方法移动到 `Core` 中 - [Link](https://github.com/breath-co2/myqee/commit/63caf58cecf2b819eeda25c194873d6ac7d0d8c3)

### 2013-01-31

* 增加 `Storage` 存储类 - [Link](https://github.com/breath-co2/myqee/commit/7f6fcd32a7ffb0a8dd0f9c53ed8a0137549f9f1c)


### 2013-01-25

* 增加 `team-library` 类库目录，并增加 `DIR_TEAM_LIBRARY` 常量 - [Link](https://github.com/breath-co2/myqee/commit/f47f9b8d2507b1498b4f8d74bba5f0646e15430e)
* `bin/recreate-id-helper-file` 修复不子文件夹的文件读取问题 - [Link](https://github.com/breath-co2/myqee/commit/9f8976634e3dac06ace542fee9a74d3afbffd95a)


### 2013-01-23

* 更新 `Core::change_project()` 方法 - [Link](https://github.com/breath-co2/myqee/commit/1572de60a0535425fc582a971a51899ce7548252)

### 2013-01-15

* 完善 `bin/recreate-empty-extend-files` 工具 [Link](https://github.com/breath-co2/myqee/commit/07539f5fc2a4480beacbedafcbd458b5df623abe)


### 2013-01-09

* `Bootstrap` 和 `Core` 升级 - [Link](https://github.com/breath-co2/myqee/commit/cef4fbcfeaf0c4597dc474add89db1436ebfe06d)
* 增加 `recreate-extend-files` 脚本工具 - [Link](https://github.com/breath-co2/myqee/commit/bed1125377e841dee52b235c6bc65b48fa28120c)
* `Core::i18n()` 方法改为 `I18n::get()` - [Link](https://github.com/breath-co2/myqee/commit/b693e23c7be8c75ade1f570a73f871b1daadb04f)

### 2013-01-08

* `view-error500-log` 工具完善 - [Link](https://github.com/breath-co2/myqee/commit/a6d031672a4ca6540a82eb6179a5547c4763e4c8)
* shell模式下错误输出更加人性化 - [Link](https://github.com/breath-co2/myqee/commit/41f2f8458535a372db5e6f839608916b76decafa)
* 将以前shell的svn_tools控制器移到 `bin/svn-tools` - [Link](https://github.com/breath-co2/myqee/commit/7ef837d5ca61daf218200d34cb06b6bf2f4d1497)

### 2013-01-07

* 增加后缀名限制支持 - [Link](https://github.com/breath-co2/myqee/commit/ddb9aedd4aeacdd5c5ef5e312ad7de26023b8429)
* 数据库完善，当连接错误时不会在异常堆叠里把数据库信息给暴露出来 - [Link](https://github.com/breath-co2/myqee/commit/6cb390f1d2b3c434975a02519086ba940a6fa630)
* merge-assets支持全部项目的文件生成，加参数-a - [Link](https://github.com/breath-co2/myqee/commit/61a55e11cc74031370438bfd1225419cd4ac468f)


### 2013-01-06

* 完善merge-assets，无修改的文件不重新写入 - [Link](https://github.com/breath-co2/myqee/commit/1eb093a5b3c1b8f4da19bf525306fce5053c0457)

### 2013-01-05

* 完善core - [Link](https://github.com/breath-co2/myqee/commit/5a6c6913e00eab6c7d3a449fdac6747b70d55d55)
* 完善MongoDB的查询 - [Link](https://github.com/breath-co2/myqee/commit/0c152ccd0d9e79cff0cd4156cb3fecabbd99ed31)
* devassets控制器更新 - [Link](https://github.com/breath-co2/myqee/commit/256cf05b082521c8fca252072ea0f13ed43132b8)
* 后台视图更新，修改 `bottom.view.php` 为 `footer.view.php` - [Link](https://github.com/breath-co2/myqee/commit/5f64fd7e3fb503e427d50bcd0b0e1a265e5850ab)