分页输出类
=================

可以智能的输出分页HTML

    $confit = array
    (
        'total_items'     => 1000,    //总数为1000
        'items_per_page', => 30,      //每页显示30条，默认20
    );
    $page = new Pagination($config);
    
    // 将HTML输出
    echo $page->render();

其中config可传的完整的参数以及默认值如下：

    $config = array
    (
    	'current_page' => array
        (
    		'source' => 'query_string',          //页码获取方式，query_string|default
    		'key'    => 'page'                   //页码所在参数，当source=query_string时通常为page,当source=default时通常为0
        ),
        'total_items'    => 0,                   //总数
        'items_per_page' => 10,                  //每页显示数
        'view'           => 'pagination/basic',  //页码视图 pagination/basic 或 pagination/floating 或自定义
        'auto_hide'      => true,                //当总页码只有1页或0页时是否不输出分页HTML，即此时返回空字符串
    );

上面 $config['view']可用的视图包括pagination/basic，pagination/floating 也可自己添加，位于views/pagination/目录中

*$config['current_page'] 使用解释：*
当source为query_string时表示页面变量位于$_GET参数中，key为page表示为$_GET['page'] 的值，页面就类似如下：

    http://localhost/?page=1
    http://localhost/?page=3

而如果key=abc则页面URL类似如下：

    http://localhost/?abc=1
    http://localhost/?abc=3

而当source为default时，将不用$_GET的参数而是用URL内的参数

    // 分页配置如下
    /*
    $config['current_page'] = array
    (
    	'source' => 'default',
		'key'    => 0
    );
    */
    // 页面结构如下
    http://myhost/controller/action/page
    
    // 列子URL
    http://localhost/users/list/1       //表示page=1
    http://localhost/users/list/3       //表示page=2

这样就可以获取到相应的页码。
设置key=0表示URL后的第一个参数就是分页变量，那么有可能在我们的程序中，第一个参数并不是分页，而有可能是第2个，那么此时就需要指定key=1，以此类推。


在new Pagination()时你至少传一个total_items（总数）给它。或许你会认为至少应该把当前分页传给它，但是恰恰相反，通常情况下，你没有必要传当前的$page给它，而是通过它获取当前的$page和$offset等数据。

这些源于Pagination独特的设计，当你告诉它总数total_items并构造对象时，它会自动获取页面的分页数。

    $config = array
    (
        'total_items'     => 1000,    //总数为1000
        'items_per_page', => 30,      //每页显示30条，默认20
    );
    $page = new Pagination($config);
    
    // 将输出当前页码，比如1(不会输出0)
    echo $page->get_current_page();
    
    // 获取到的offset可用于sql查询里的offset偏移
    echo $page->get_offset();


下面是一个例子，帮你理解怎么使用分页类

    //下面的代码在某个列表控制器里
    //以下代码摘自后台类库的管理员列表里并整理简化了
    
    // 管理员模块
    $model_administrator = new Model_Admin_Administrator();
    
    // 首先查询页面总数
    $count = $db->total_count();

    // 如果有数量执行
    if ($count)
    {
        $pageconfig = array();
        $pageconfig['total_items'] = $count;         // 总数
        $pagination = new Pagination($pageconfig);
        
        // 当前页码应当偏移的offset
        $offset = $pagination->get_offset();
        
        // 每页需要显示的数量
        $limit  = $pagination->get_items_per_page();
        
        // 获取列表数据
        $list = $model_administrator->get_aministrator_list($offset,$limit);
        
        $pagehtml = $pagination->render();
    }
    else
    {
        $list = array();
        $pagehtml = '';
    }
    
    // 加载视图输出
    $view = new View('admin/administrator/list');
    $view->pagehtml = $pagehtml;    //视图在获取到$pagehtml后直接echo $pagehtml即可
    $view->list = $list;
    $view->render();

Pagination::factory($config = array())
-------
获取一个实例化好的分页对象


    $page = new Pagination($config);
    $html = $page->render();
    // 以上等价于
    $html = Pagination::factory($config)->render();

url($page = 1)
--------
获取一个指定页码的URL字符串

    $page = new Pagination($config);
    // 输出第4页完整URL地址
    echo $pgae->url(4);
    
    // 输出最后一页URL
    echo $page->url($page->get_last_page());
    
    // 输出下一页URL
    echo $page->url($page->get_next_page());
    

valid_page($page)
--------
验证一个指定的$page是否有效，即是否>0且<=总页数

render($view = null)
--------
输出分页的HTML代码，可以指定其它分页视图，不传则使用分页类库默认的视图，即 pagination/basic 视图

get_current_page()
--------
获取当前页码


get_total_items()
--------
获取总数

get_items_per_page()
--------
获取每页需要显示的项目数值

get_total_pages()
--------
获取总页数


get_current_first_item()
--------
获取当前第一个项目的offset值

get_current_last_item()
--------
获取当前页面最后一个项目的offset值

get_previous_page()
--------
获取上一页值

get_next_page()
--------
获取下一页值

    // 输出下一页URL
    echo $page->url($page->get_next_page());

get_first_page()
--------
获取第一页值

get_last_page()
--------
获取最后一页值

get_offset()
--------
获取offset值


