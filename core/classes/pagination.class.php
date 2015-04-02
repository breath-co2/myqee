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
 *          'source'         => 'default',                //可不设置，默认值：default，可选query_string，route和default
 *          'key'            => '0',                      //指定参数位置，通常source为default时，key都为0，若source为query_string或route时，通常为page
 *          'items_per_page' => 20,                       //可不设置，默认值20
 *          'view'           => 'pagination/basic',       //可不设置，默认值pagination/basic
 *          'auto_hide'      => true,                    //可不设置，默认值true
 *      );
 *
 * 若不设置，则全为默认值，例如：`$pagination = new Pagination();`
 *
 *       $pagination = new Pagination($pageconfig);
 *       $offset     = $pagination->offset();
 *       $limit      = $pagination->items_per_page();
 *
 * 通过$offset和$limit获取指定分页数据
 *
 *       $data = Database::instance()->limit($limit, $offset)->get()->as_array();
 *
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2016 myqee.com
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
        'source'         => 'pagination',
        'key'            => '0',
        'total_items'    => 0,
        'items_per_page' => 20,
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
    protected $total_items = 0;

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
     * 记录当前分页是否已更新
     *
     * @var boolean
     */
    protected $_is_renew = false;

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
    public function __construct($config = 'default')
    {
        if (!is_array($config))
        {
            $config = Core::config('pagination.'.$config);
        }

        if ($config && is_array($config))
        {
            // 兼容2.0写法
            if (isset($config['current_page']) && is_array($config['current_page']))
            {
                if (isset($config['current_page']['source'])) $config['source'] = $config['current_page']['source'];
                if (isset($config['current_page']['key']))    $config['key']    = $config['current_page']['key'];
                unset($config['current_page']);
            }

            $this->config = $config + $this->config;
        }
    }

    /**
     * 设置更新
     *
     * @param   array   configuration
     * @return  object  Pagination
     */
    protected function renew()
    {
        $this->_is_renew = true;

        if (null===$this->current_page)
        {
            if (!empty($this->config['page']))
            {
                $this->current_page = (int)$this->config['page'];
            }
            else
            {
                switch ($this->config['source'])
                {
                    case 'query_string' :
                        $this->current_page = isset($_GET[$this->config['key']]) ? (int)$_GET[$this->config['key']] : 1;
                        break;
                    case 'route' :
                        $this->current_page = (int)HttpIO::param($this->config['key'], 1);
                        break;
                    case 'default' :
                    default :
                        $this->current_page = isset(HttpIO::$params['arguments'][$this->config['key']])?(int)HttpIO::$params['arguments'][$this->config['key']] : 1;
                        break;
                }
            }
        }
        else
        {
            $this->current_page = (int)max(1, $this->current_page);
        }

        $this->items_per_page = (int)$this->items_per_page;
        if (!$this->items_per_page>0)
        {
            $this->items_per_page = (int)max(1, $this->config['items_per_page']);
        }

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

    /**
     * 获取指定分页数的URL
     *
     *      $page = new Pagination();
     *      echo $page->total_items(100)->url(10);
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

        switch ($this->config['source'])
        {
            case 'query_string' :
                return Core::url(HttpIO::$uri) . HttpIO::query(array($this->config['key'] => $page));
            case 'route' :
                return Core::url(Core::route()->uri(array($this->config['key'] => $page))) . HttpIO::query();
            case 'default' :
            default :
                $tmparr = array();
                if (is_numeric($this->config['key']))
                {
                    for ($i=0; $i<$this->config['key']; $i++)
                    {
                        $tmparr[$i] = (string)HttpIO::$params['arguments'][$i];
                    }
                }
                $tmparr[$this->config['key']] = $page;

                return Core::url(HttpIO::uri($tmparr)) . HttpIO::query();
        }

        return '#';
    }

    /**
     * 检查当前分页数是否存在
     *
     * @param   integer page number
     * @return  boolean
     */
    public function valid_page($page)
    {
        $page = (int)$page;
        if (!$page > 0) return false;

        return $page > 0 && $page <= $this->total_pages;
    }

    /**
     * 返回分页HTML
     *
     * @param   $view 视图对象或视图文件名
     * @return  string 分页HTML
     */
    public function render($view = null)
    {
        if (!$this->_is_renew)
        {
            $this->renew();
        }

        # 是否自动隐藏只有1个分页或0个分页的HTML
        if (true===$this->config['auto_hide'] && $this->total_pages <= 1) return '';


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
     * 获取，设置当前页
     *
     * @return int
     * @since  3.0
     */
    public function current_page($value = null)
    {
        return $this->get_or_set_data(__FUNCTION__, $value);
    }

    /**
     * 获取，设置总数
     *
     * @return int
     * @since  3.0
     */
    public function total_items($value = null)
    {
        return $this->get_or_set_data(__FUNCTION__, $value);
    }

    /**
     * 获取，设置每页项目数
     *
     * @return int
     * @since  3.0
     */
    public function items_per_page($value = null)
    {
        return $this->get_or_set_data(__FUNCTION__, $value);
    }

    /**
     * 获取总页数
     *
     * @return int
     * @since  3.0
     */
    public function total_pages()
    {
        return $this->get_or_set_data(__FUNCTION__);
    }

    /**
     * 获取当前页第一个项目
     *
     * @return int
     * @since  3.0
     */
    public function current_first_item()
    {
        return $this->get_or_set_data(__FUNCTION__);
    }

    /**
     * 获取当前页最后一个项目
     *
     * @return int
     * @since  3.0
     */
    public function current_last_item()
    {
        return $this->get_or_set_data(__FUNCTION__);
    }

    /**
     * 获取上一页页码
     *
     * 如果当前是第一页，则返回false
     *
     * @return int
     * @since  3.0
     */
    public function previous_page()
    {
        return $this->get_or_set_data(__FUNCTION__);
    }

    /**
     * 获取下一页页码
     *
     * 如果当前是最后一页，则返回false
     *
     * @return int
     * @return false 表示已经没有下一页了
     * @since  3.0
     */
    public function next_page()
    {
        return $this->get_or_set_data(__FUNCTION__);
    }

    /**
     * 获取第一页页码
     *
     * @return int
     * @since  3.0
     */
    public function first_page()
    {
        return $this->get_or_set_data(__FUNCTION__);
    }

    /**
     * 获取最后一页页码
     *
     * @return int
     * @since  3.0
     */
    public function last_page()
    {
        return $this->get_or_set_data(__FUNCTION__);
    }

    /**
     * 获取页码Offset值
     *
     * @return int
     * @since  3.0
     */
    public function offset()
    {
        return $this->get_or_set_data(__FUNCTION__);
    }


    /**
     * 获取或读取数据
     *
     * @param  string $key
     * @param  int $value
     * @return Pagination
     * @since  3.0
     */
    protected function get_or_set_data($key, $value = null)
    {
        if (null===$value)
        {
            return $this->__get($key);
        }
        else
        {
            $this->__set($key, $value);

            return $this;
        }
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
        if (false===$this->_is_renew)
        {
            $this->renew();
        }

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
        $this->$key = $value;
        $this->_is_renew = false;

        return $this;
    }


    public function __call($method, $params)
    {
        // 兼容V2中的get_***() 方法

        if (substr($method, 0, 4) == 'get_')
        {
            $key = substr($method, 4);
            return $this->__get($key);
        }

        throw new Exception('class Pagination not found method : '. $method);
    }
}