扩展对象
================
首先，您需要简单的了解本系统的[目录结构](base.dir.html)。其中，需要扩展的类绝大部分是存放在classes目录的。
本系统中，除Bootstrap不能被扩展外，其它（包括Core）在内的所有的类都可以扩展。
在libraries/MyQEE/Core/classes/目录中你会发现，有很多类似于如下内容的文件：

    <?php
    //libraries/MyQEE/Core/classes/Core.class.php
    abstract class Core extends MyQEE_Core
    {
    
    }
    
    //libraries/MyQEE/Core/classes/Model.class.php
    abstract class Model extends MyQEE_Model
    {
    
    }
    
而实际的代码都在libraries/MyQEE/Core/classes/MyQEE/中，比如MyQEE_Core,MyQEE_Model对象。
这样也就意味着您可以自己创建一个类文件已取代现有类文件。比如Core类，您可以在项目目录的classes/中建立项目自有的Core.class.php文件已替代原先的Core.class.php文件，内容可以是您希望的，可以扩展到MyQEE_Core上也可以完全重构。


扩展类库
----------------
您可以在/libraries/目录中建立自己的类库文件夹，然后再/config.php中$config['autoload']里加入或在项目的config.php文件中加入$config['library']。
文件夹支持多级目录，例如：
/libraries/MyQEE/CMS/
则可以在项目的config.php文件里加入（注意大小写保持一致）：

    $config['library'] = array('com.MyQEE.CMS');

这样，/libraries/MyQEE/CMS/目录即可拥有完整的classes,views,models,orm,controllers,config等等目录结构。

目录优先原则
---------------
最高优先级的目录为项目目录，其次是类库（依赖于config中设置的顺序先后），最后是MyQEE核心类库，这样的优先级保证了从项目到扩展类库到核心类库的层层扩展。
