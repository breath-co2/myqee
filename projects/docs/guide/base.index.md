关于MyQEE PHP Framework
======================
MyQEE吸收了国外优秀PHP框架[Kohana](http://kohanaframework.org/)的优秀思路，针对PHP团队开发实际情况经过多年精心打造而来，目前已在很多高并发、大访问的网站上得到实践。

系统的最大亮点是适合多项目开发的HMVC模式，以及适合复杂数据库项目开发的ORM组件，并且提供了适合程序员开发的调试工具。HMVC模式可随项目进行自由搭配，甚至加载第三方类库。

快速入门
------------
* [MyQEE的命名规则是怎样的？](base.name.html)
* [如何创建一个新项目？](project.create.html)
* [如何扩展对象？](base.extends.html)
* [了解一些常用的类和方法](base.function.html)
* [伟大的Hello World](base.helloworld.html)
* MVC快速入门
  * [控制器](mvc.controller.html)
  * [模块](mvc.model.html)
  * [视图](mvc.view.html)
* [浏览在线API接口（API Explorer）](api/)

MyQEE有哪些优点？
-------------------
* **优秀的可扩展性**<br>完美的HMVC模式，您可以自由扩展包括Core在内的几乎所有的类文件，同时支持第三方类库的加载；
* **自由灵活**<br>类库和项目是分开来的，他们可以自由组合，灵活度高；
* **开发工具丰富**<br>MyQEE提供了很多开发工具（比如debug，trace，sql分析等），让你的开发如虎添翼；
* **高安全性**<br>系统提供QueryBuilder，XSS clear可杜绝SQL注入，保证你的数据库和页面的安全性；
* **功能强大**<br>数据库支持事务、集群，支持驱动扩展，你可根据自己的情况选择不同的数据库驱动；
* **多驱动支持**<br>与数据库相同，包括Session,HttpClient,Cache等在内的类都支持驱动扩展；
* **辅助功能**<br>系统支持文件合并功能，可以大幅提高程序的性能，所以你不需要为加载了多个项目库而影响系统性能烦恼；
* **适合团队开发**<br>全新开发的ORM非常适合团队开发，让你真正体会到OOP带来的优势和开发的乐趣；


MyQEE适合哪些需求的项目？
------------------
* 独立的中小型网站项目
* 多个项目（产品）在一起开发的需求

