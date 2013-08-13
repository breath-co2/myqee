<?php

/**
 * 分页核心类
 *
 * 使用方法举例
 *
 *      # 配置默认值见 pagination.config.php 配置
 *
 *      $pageconfig = array
 *      (
 *          current_page => array
 *          (
 *              'source' => 'default',                    //可不设置，默认值：default，可选query_string，route和default
 *              'key'    => 0,                            //指定参数位置，通常source为default时，key都为0，若source为query_string或route时，通常为page
 *          ),
 *          'view'           => 'pagination/floating',    //可不设置，默认值pagination/basic
 *          'auto_hide'      => false,                    //可不设置，默认值true
 *          'items_per_page' => 20,                       //可不设置，默认值20
 *      );
 *
 * 若不设置，则全为默认值，例如：`$pagination = new Pagination();`
 *
 *       $pagination = new Pagination($pageconfig);
 *       $offset     = $pagination->get_offset();
 *       $limit      = $pagination->get_items_per_page();
 *
 * 通过$offset和$limit获取指定分页数据
 *
 *       $data = Database::instance()->limit($limit,$offset)->get()->as_array();
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
    /**
     * 分页配置
     *
     * @var array
     */
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
     *
     * @var int
     */
    protected $current_page;

    /**
     * 总数
     *
     * @var int
     */
    protected $total_items;

    // How many items to show per page
    /**
     * 每页显示数目
     *
     * @var int
     */
    protected $items_per_page;

    /**
     * 总页数
     *
     * @var int
     */
    protected $total_pages;

    /**
     * 当前第一个项目
     *
     * @var int
     */
    protected $current_first_item;

    /**
     * 当前最后项目
     *
     * @var int
     */
    protected $current_last_item;

    /**
     * 上一页码
     *
     * @var int
     */
    protected $previous_page;

    /**
     * 下一页码
     *
     * @var int
     */
    protected $next_page;

    /**
     * 第一页
     *
     * @var int
     */
    protected $first_page;

    /**
     * 最后一页
     *
     * @var int
     */
    protected $last_page;

    /**
     * 查询的offset条件
     *
     * @var int
     */
    protected $offset;

    /**
     * 返回一个实例化对象的分页类
     *
     * @param   string | array configuration
     * @return  Pagination
     */
    public static function factory($config = 'default')
    {
        return new Pagination($config);
    }

    /**
     * 实例化对象
     *
     * @param   array  configuration
     * @return  void
     */
    public function __construct($config)
    {
        $this->config = $this->config_group() + $this->config;

        if (is_string($config))
        {
            $config = $this->config_group($config);
        }

        $this->setup($config);
    }

    /**
     * 返回预配置中指定key的配置内容
     *
     * 配置内容在 `Core::config('pagination')` 中
     *
     * @param  string 配置组，默认 default
     * @return array  配置设置
     */
    public function config_group($group = 'default')
    {
        // Load the pagination config file
        $config_file = Core::config('pagination');

        // Initialize the $config array
        $config['group'] = (string)$group;

        // Recursively load requested config groups
        while (isset($config['group']) && isset($config_file->$config['group']))
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
        if (isset($config['group']))
        {
            // Recursively load requested config groups
            $config += $this->config_group($config['group']);
        }

        // Overwrite the current config settings
        $this->config = $config + $this->config;

        // Only (re)calculate pagination when needed
        if (null===$this->current_page || isset($config['current_page']) || isset($config['total_items']) || isset($config['items_per_page']))
        {
            // Retrieve the current page number
            if (!empty($this->config['current_page']['page']))
            {
                // The current page number has been set manually
                $this->current_page = (int)$this->config['current_page']['page'];
            }
            else
            {
                switch ($this->config['current_page']['source'])
                {
                    case 'query_string' :
                        $this->current_page = isset($_GET[$this->config['current_page']['key']]) ? (int)$_GET[$this->config['current_page']['key']] : 1;
                        break;
                    case 'route' :
                        $this->current_page = (int)HttpIO::param($this->config['current_page']['key'], 1);
                        break;
                    case 'default' :
                    default :
                        $this->current_page = isset( HttpIO::$params['arguments'][$this->config['current_page']['key']] ) ? (int)HttpIO::$params['arguments'][$this->config['current_page']['key']] : 1;
                        break;
                }
            }

            // Calculate and clean all pagination variables
            $this->total_items        = (int)max(0, $this->config['total_items']);
            $this->items_per_page     = (int)max(1, $this->config['items_per_page']);
            $this->total_pages        = (int)ceil($this->total_items / $this->items_per_page);
            $this->current_page       = (int)min(max(1, $this->current_page), max(1, $this->total_pages));
            $this->current_first_item = (int)min((($this->current_page - 1) * $this->items_per_page) + 1, $this->total_items);
            $this->current_last_item  = (int)min($this->current_first_item + $this->items_per_page - 1, $this->total_items);
            $this->previous_page      = ($this->current_page > 1) ? $this->current_page - 1 : false;
            $this->next_page          = ($this->current_page < $this->total_pages) ? $this->current_page + 1 : false;
            $this->first_page         = ($this->current_page === 1) ? false : 1;
            $this->last_page          = ($this->current_page >= $this->total_pages) ? false : $this->total_pages;
            $this->offset             = (int)(($this->current_page - 1) * $this->items_per_page);
        }

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
        if (1===$page)
        {
            $page = null;
        }

        switch ($this->config['current_page']['source'])
        {
            case 'query_string' :
                return Core::url(HttpIO::$uri) . HttpIO::query(array($this->config['current_page']['key'] => $page));
            case 'route' :
                return Core::url(Core::route()->uri(array($this->config['current_page']['key'] => $page))) . HttpIO::query();
            case 'default' :
            default :
                $tmparr = array();
                if (is_numeric($this->config['current_page']['key']))
                {
                    for ($i=0; $i<$this->config['current_page']['key']; $i++)
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
        if (!$page > 0) return false;

        return $page > 0 and $page <= $this->total_pages;
    }

    /**
     * 返回分页HTML
     *
     * @param   mixed   string of the view to use, or a Kohana_View object
     * @return  string  pagination output (HTML)
     */
    public function render($view = null)
    {
        // Automatically hide pagination whenever it is superfluous
        if ($this->config['auto_hide'] === true && $this->total_pages <= 1) return '';

        if (null===$view)
        {
            $view = $this->config['view'];
        }

        if (!$view instanceof View)
        {
            $view = View::factory($view);
        }

        return $view->set(get_object_vars($this))->set('page', $this)->render(false);
    }

    /**
     * 输出HTML
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
     *
     * @return int
     */
    public function get_current_page()
    {
        return $this->current_page;
    }

    /**
     * 获取总数
     *
     * @return int
     */
    public function get_total_items()
    {
        return $this->total_items;
    }

    /**
     * 获取每页项目数
     *
     * @return int
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
     *
     * @return int
     */
    public function get_current_first_item()
    {
        return $this->current_first_item;
    }

    /**
     * 获取当前页最后一个项目
     *
     * @return int
     */
    public function get_current_last_item()
    {
        return $this->current_last_item;
    }

    /**
     * 获取上一页页码
     *
     * 如果当前是第一页，则返回false
     *
     * @return int
     */
    public function get_previous_page()
    {
        return $this->previous_page;
    }

    /**
     * 获取下一页页码
     *
     * 如果当前是最后一页，则返回false
     *
     * @return int
     */
    public function get_next_page()
    {
        return $this->next_page;
    }

    /**
     * 获取第一页页码
     *
     * @return int
     */
    public function get_first_page()
    {
        return $this->first_page;
    }

    /**
     * 获取最后一页页码
     *
     * @return int
     */
    public function get_last_page()
    {
        return $this->last_page;
    }

    /**
     * 获取页码Offset值
     *
     * @return int
     */
    public function get_offset()
    {
        return $this->offset;
    }

} // End Pagination