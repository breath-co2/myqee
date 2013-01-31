<?php

/**
 * 分页核心类
 *
 * 使用方法举例
 *
 *   # 配置默认值见 pagination.config.php 配置
 *
 *   $pageconfig = array
 *   (
 *       current_page => array
 *       (
 *           'source' => 'default',                    //可不设置，默认值：default，可选query_string，route和default
 *           'key'    => 0,                            //指定参数位置，通常source为default时，key都为0，若source为query_string或route时，通常为page
 *       ),
 *       'view'           => 'pagination/floating',    //可不设置，默认值pagination/basic
 *       'auto_hide'      => false,                    //可不设置，默认值true
 *       'items_per_page' => 20,                       //可不设置，默认值20
 *   );
 *
 *   # 若不设置，则全为默认值，例如：$pagination = new Pagination();
 *   $pagination = new Pagination($pageconfig);
 *   $offset     = $pagination->get_offset();
 *   $limit      = $pagination->get_items_per_page();
 *
 *   #通过$offset和$limit获取指定分页数据
 *   $data = Database::instance()->limit($limit,$offset)->get()->as_array();
 *
 *
 * @author     jonwang(jonwang@myqee.com)
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Core_Pagination
{

    // Merged configuration settings
    protected $config = array
    (
        'current_page' => array
        (
            'source' => 'query_string',
            'key'    => 'page'
        ),
        'total_items'    => 0,
        'items_per_page' => 10,
        'view'           => 'pagination/basic',
        'auto_hide'      => true,
    );

    /**
     * 当前页码
     * @var int
     */
    protected $current_page;

    // Total item count
    protected $total_items;

    // How many items to show per page
    protected $items_per_page;

    // Total page count
    protected $total_pages;

    // Item offset for the first item displayed on the current page
    protected $current_first_item;

    // Item offset for the last item displayed on the current page
    protected $current_last_item;

    // Previous page number; FALSE if the current page is the first one
    protected $previous_page;

    // Next page number; FALSE if the current page is the last one
    protected $next_page;

    // First page number; FALSE if the current page is the first one
    protected $first_page;

    // Last page number; FALSE if the current page is the last one
    protected $last_page;

    // Query offset
    protected $offset;

    /**
     * Creates a new Pagination object.
     *
     * @param   array  configuration
     * @return  Pagination
     */
    public static function factory(array $config = array())
    {
        return new Pagination($config);
    }

    /**
     * Creates a new Pagination object.
     *
     * @param   array  configuration
     * @return  void
     */
    public function __construct(array $config = array())
    {
        // Overwrite system defaults with application defaults
        $this->config = $this->config_group() + $this->config;

        // Pagination setup
        $this->setup($config);
    }

    /**
     * Retrieves a pagination config group from the config file. One config group can
     * refer to another as its parent, which will be recursively loaded.
     *
     * @param   string  pagination config group; "default" if none given
     * @return  array   config settings
     */
    public function config_group($group = 'default')
    {
        // Load the pagination config file
        $config_file = Core::config('pagination');

        // Initialize the $config array
        $config['group'] = (string)$group;

        // Recursively load requested config groups
        while ( isset($config['group']) and isset($config_file->$config['group']) )
        {
            // Temporarily store config group name
            $group = $config['group'];
            unset($config['group']);

            // Add config group values, not overwriting existing keys
            $config += $config_file[$group];
        }

        // Get rid of possible stray config group names
        unset($config['group']);

        // Return the merged config group settings
        return $config;
    }

    /**
     * Loads configuration settings into the object and (re)calculates pagination if needed.
     * Allows you to update config settings after a Pagination object has been constructed.
     *
     * @param   array   configuration
     * @return  object  Pagination
     */
    public function setup(array $config = array())
    {
        if ( isset($config['group']) )
        {
            // Recursively load requested config groups
            $config += $this->config_group($config['group']);
        }

        // Overwrite the current config settings
        $this->config = $config + $this->config;

        // Only (re)calculate pagination when needed
        if ( $this->current_page === null or isset($config['current_page']) or isset($config['total_items']) or isset($config['items_per_page']) )
        {
            // Retrieve the current page number
            if ( !empty($this->config['current_page']['page']) )
            {
                // The current page number has been set manually
                $this->current_page = (int)$this->config['current_page']['page'];
            }
            else
            {
                switch ( $this->config['current_page']['source'] )
                {
                    case 'query_string' :
                        $this->current_page = isset($_GET[$this->config['current_page']['key']]) ? (int)$_GET[$this->config['current_page']['key']] : 1;
                        break;
                    case 'route' :
                        $this->current_page = (int)HttpIO::param($this->config['current_page']['key'], 1);
                    case 'default' :
                    default :
                        $this->current_page = isset( HttpIO::$params['arguments'][$this->config['current_page']['key']] ) ? (int)HttpIO::$params['arguments'][$this->config['current_page']['key']] : 1;
                        break;
                }
            }

            // Calculate and clean all pagination variables
            $this->total_items = (int)max(0, $this->config['total_items']);
            $this->items_per_page = (int)max(1, $this->config['items_per_page']);
            $this->total_pages = (int)ceil($this->total_items / $this->items_per_page);
            $this->current_page = (int)min(max(1, $this->current_page), max(1, $this->total_pages));
            $this->current_first_item = (int)min((($this->current_page - 1) * $this->items_per_page) + 1, $this->total_items);
            $this->current_last_item = (int)min($this->current_first_item + $this->items_per_page - 1, $this->total_items);
            $this->previous_page = ($this->current_page > 1) ? $this->current_page - 1 : FALSE;
            $this->next_page = ($this->current_page < $this->total_pages) ? $this->current_page + 1 : FALSE;
            $this->first_page = ($this->current_page === 1) ? FALSE : 1;
            $this->last_page = ($this->current_page >= $this->total_pages) ? FALSE : $this->total_pages;
            $this->offset = (int)(($this->current_page - 1) * $this->items_per_page);
        }

        // Chainable method
        return $this;
    }

    /**
     * Generates the full URL for a certain page.
     *
     * @param   integer  page number
     * @return  string   page URL
     */
    public function url($page = 1)
    {
        // Clean the page number
        $page = max(1, (int)$page);

        // No page number in URLs to first page
        if ( $page === 1 )
        {
            $page = null;
        }

        switch ( $this->config['current_page']['source'] )
        {
            case 'query_string' :
                return Core::url(HttpIO::$uri) . HttpIO::query(array($this->config['current_page']['key'] => $page));
            case 'route' :
                return Core::url(Core::route()->uri(array($this->config['current_page']['key'] => $page))) . HttpIO::query();
            default :
                $tmparr = array();
                if ( is_numeric($this->config['current_page']['key']) )
                {
                    for ($i=0;$i<$this->config['current_page']['key'];$i++)
                    {
                        $tmparr[$i] = (string)HttpIO::$params['arguments'][$i];
                    }
                }
                $tmparr[$this->config['current_page']['key']] = $page;
                return Core::url(HttpIO::uri($tmparr)) . HttpIO::query();
        }

        return '#';
    }

    /**
     * Checks whether the given page number exists.
     *
     * @param   integer  page number
     * @return  boolean
     * @since   3.0.7
     */
    public function valid_page($page)
    {
        $page = (int)$page;
        if ( !$page > 0 ) return FALSE;

        return $page > 0 and $page <= $this->total_pages;
    }

    /**
     * Renders the pagination links.
     *
     * @param   mixed   string of the view to use, or a Kohana_View object
     * @return  string  pagination output (HTML)
     */
    public function render($view = null)
    {
        // Automatically hide pagination whenever it is superfluous
        if ( $this->config['auto_hide'] === TRUE && $this->total_pages <= 1 ) return '';

        if ( $view === null )
        {
            // Use the view from config
            $view = $this->config['view'];
        }

        if ( !$view instanceof View )
        {
            // Load the view file
            $view = View::factory($view);
        }

        // Pass on the whole Pagination object
        return $view->set(get_object_vars($this))->set('page', $this)->render(false);
    }

    /**
     * Renders the pagination links.
     *
     * @return  string  pagination output (HTML)
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * Returns a Pagination property.
     *
     * @param   string  URI of the request
     * @return  mixed   Pagination property; null if not found
     */
    public function __get($key)
    {
        return isset($this->$key) ? $this->$key : null;
    }

    /**
     * Updates a single config setting, and recalculates pagination if needed.
     *
     * @param   string  config key
     * @param   mixed   config value
     * @return  void
     */
    public function __set($key, $value)
    {
        $this->setup(array($key => $value));
    }

    /**
     * 获取当前页
     */
    public function get_current_page()
    {
        return $this->current_page;
    }

    /**
     * 获取总数
     */
    public function get_total_items()
    {
        return $this->total_items;
    }

    /**
     * 获取每页项目数
     */
    public function get_items_per_page()
    {
        return $this->items_per_page;
    }

    /**
     * 获取总页数
     */
    public function get_total_pages()
    {
        return $this->total_pages;
    }

    /**
     * 获取当前页第一个项目
     */
    public function get_current_first_item()
    {
        return $this->current_first_item;
    }

    /**
     * 获取当前页最后一个项目
     */
    public function get_current_last_item()
    {
        return $this->current_last_item;
    }

    /**
     * 获取上一页页码
     * 如果当前是第一页，则返回false
     */
    public function get_previous_page()
    {
        return $this->previous_page;
    }

    /**
     * 获取下一页页码
     * 如果当前是最后一页，则返回false
     */
    public function get_next_page()
    {
        return $this->next_page;
    }

    /**
     * 获取第一页页码
     */
    public function get_first_page()
    {
        return $this->first_page;
    }

    /**
     * 获取最后一页页码
     */
    public function get_last_page()
    {
        return $this->last_page;
    }

    /**
     * 获取页码Offset值
     */
    public function get_offset()
    {
        return $this->offset;
    }

} // End Pagination