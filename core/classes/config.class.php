<?php

/**
 * 配置程序核心类
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Core_Config
{
    /**
     * 数据库配置
     *
     * @var string
     */
    protected $database = 'default';

    /**
     * 表名称
     *
     * @var string
     */
    protected $tablename = 'config';

    /**
     * 是否采用文件缓存
     *
     * @var boolean
     */
    protected $is_use_cache;

    /**
     * 配置
     * @var array
     */
    protected $config;

    function __construct()
    {
        $config = Core::config('configuration');
        if ($config)
        {
            if (isset($config['database']) && $config['database']  )$this->database  = $config['database'];
            if (isset($config['tablename']) && $config['tablename'])$this->tablename = $config['tablename'];
        }

        $this->set_use_cache();
    }

    /**
     * 保存一个配置，支持批量设置
     *
     * @param string/array $key 关键字
     * @param fixed $value 值
     * @param string $type 类型,长度32以内
     * @param boolean $auto_clear_cache 自动清除缓存
     * @return boolean
     */
    public function set($key, $value, $type = '', $auto_clear_cache = true)
    {
        $db = new Database($this->database);
        $type = (string)$type;
        try
        {
            if (is_array($key))
            {
                # 批量设置
                $tr = $db->transaction();
                $tr->start();
                try
                {
                    # 先尝试删除旧数据
                    $db->where('type', $type)->and_where_open();
                    foreach ($key as $k)
                    {
                        $db->or_where('key_md5', md5($k));
                    }
                    $db->and_where_close()->delete($this->tablename);

                    # 设置数据
                    foreach ($key as $i=>$k)
                    {
                        $data = array
                        (
                            'type'     => $type,
                            'key_md5'  => md5($k),
                            'key_name' => $k,
                            'value'    => $this->data_format($value[$i]),
                        );
                        $db->values($data);
                    }

                    $db->columns(
                        array
                        (
                            'type',
                            'key_md5',
                            'key_name',
                            'value',
                        )
                    );
                    $db->insert($this->tablename);

                    $tr->commit();

                    if ($auto_clear_cache)
                    {
                        $this->clear_cache($type);
                    }

                    return true;
                }
                catch (Exception $e)
                {
                    $tr->rollback();
                    return false;
                }
            }
            else
            {
                $data = array
                (
                    'type'     => $type,
                    'key_md5'  => md5($key),
                    'key_name' => $key,
                    'value'    => $this->data_format($value),
                );
                $status = $db->replace($this->tablename, $data);
                $status = $status[1];
            }


            if ($status)
            {
                if (is_array($this->config))
                {
                    $this->config[$key] = $value;
                }

                if ($auto_clear_cache)
                {
                    $this->clear_cache($type);
                }

                return true;
            }
            else
            {
                return false;
            }
        }
        catch (Exception $e)
        {
            if (IS_DEBUG)throw $e;

            return false;
        }
    }

    /**
     * 获取制定key的配置
     *
     * @param string $key KEY
     * @param string $type 类型
     * @param boolean $no_cache 是否允许使用缓存 设置true将直接从数据库中查询
     * @return fixed
     */
    public function get($key, $type='', $no_cache = false)
    {
        $type = (string)$type;

        if ($no_cache)
        {
            # 没有缓存数据，则直接在数据库里获取
            $db = new Database($this->database);
            $config = $db->from($this->tablename)->select('value')->where('type', $type)->where('key_md5', md5($key))->limit(1)->get(false, true)->get('value');

            if ($config)
            {
                return $this->data_unformat($config);
            }
        }

        if (!isset($this->config[$type]))$this->reload(false, $type);

        if (isset($this->config[$type][$key]))return $this->config[$type][$key];

        return null;
    }

    /**
     * 获取制定type的所有值
     *
     * @param string $type
     * @return array
     */
    public function get_by_type($type = '')
    {
        if (null===$this->config)$this->reload(false, $type);

        $type = (string)$type;

        if (isset($this->config[$type]))
        {
            return (array)$this->config[$type];
        }
        else
        {
            return array();
        }
    }

    /**
     * 删除制定key的配置，支持多个
     *
     * @param string/array $key
     * @param string $type
     * @return boolean
     */
    public function delete($key, $type='')
    {
        $db = new Database($this->database);
        $type = (string)$type;
        try
        {
            if (is_array($key))
            {
                # 多个key
                $db->where('type', $type);
                $db->and_where_open();
                foreach ($key as $k)
                {
                    $db->or_where('key_md5', $k);
                    if ($this->config)unset($this->config[$type][$k]);
                }
                $db->and_where_close();
                $db->delete();
            }
            else
            {
                # 单个key
                $db->delete($this->tablename, array('type'=>$type, 'key_md5'=>md5($key)));
                if ($this->config)unset($this->config[$type][$key]);
            }

            // 删除缓存
            $this->clear_cache();
            return true;
        }
        catch (Exception $e)
        {
            return false;
        }
    }

    /**
     * 删除制定type的所有数据
     *
     * @param string $type
     */
    public function delete_type($type='')
    {
        $type = (string)$type;
        $db = new Database($this->database);
        try
        {
            $db->delete($this->tablename, array('type'=>$type));
            if ($this->config)unset($this->config[$type]);

            // 删除缓存
            $this->clear_cache();
            return true;
        }
        catch (Exception $e)
        {
            return false;
        }
    }

    /**
     * 重新加载配置
     *
     * @param boolean $from_db 是否强制从数据库中读取，默认true
     * @param string $type 类型
     * @return Core_Config
     */
    public function reload($from_db=true, $type = '')
    {
        $tmpfile = $this->get_config_cache_file(Core::$project, $type);

        if ($this->is_use_cache && !$from_db && is_file($tmpfile))
        {
            # 在data目录中直接读取
            $this->config[$type] = @unserialize(file_get_contents($tmpfile));
            if (!is_array($this->config[$type]))
            {
                $this->config[$type] = array();
            }
        }
        else
        {
            # 没有缓存数据，则直接在数据库里获取
            $db = new Database($this->database);
            $config = $db->from($this->tablename)->where('type', $type)->get(false, true)->as_array();

            if ($config)
            {
                $this->config = array();
                foreach ($config as $item)
                {
                    $this->config[$item['type']][$item['key_name']] = $this->data_unformat($item['value']);
                }
            }
            else
            {
                $this->config = array
                (
                    $type => array(),
                );
            }

            if ($this->is_use_cache)
            {
                // 普通模式下写缓存
                $rs = File::create_file($tmpfile, serialize($this->config));

                if (IS_DEBUG)Core::debug()->log('save extends config cache ' . ($rs?'success':'fail').'.');
            }
        }
    }

    /**
     * 清除配置缓存
     *
     * @return boolean
     */
    public function clear_cache($type = '')
    {
        if (!$this->is_use_cache)
        {
            // 非普通文件写入，不缓存，无需清理
            return true;
        }

        $projects = array_keys((array)Core::config('core.projects'));
        if ($projects)foreach ($projects as $project)
        {
            # 所有项目的配置文件
            $tmpfile[] = $this->get_config_cache_file($project, $type);
        }

        $rs = File::unlink($tmpfile);

        if (IS_DEBUG)Core::debug()->log('clear extends config cache '. ($rs?'success':'fail') .'.');

        return $rs;
    }

    /**
     * 设置是否启用缓存模式
     */
    protected function set_use_cache()
    {
        $this->is_use_cache = Core::config('core.file_write_mode') == 'normal' ? true:false;
    }

    /**
     * 获取配置文件路径
     *
     * @param string $project
     * @param string $type
     * @return string
     */
    protected function get_config_cache_file($project, $type = '')
    {
        return DIR_DATA .'extends_config'. $project. ($type?'.'.$type:'') .'.txt';
    }

    /**
     * 格式化数据方式
     *
     * @param fixed $data
     * @return string
     */
    protected function data_format($data)
    {
        return gzcompress(serialize($data), 9);
    }

    /**
     * 反解数据
     *
     * @param string $data
     */
    protected function data_unformat($data)
    {
        return @unserialize(gzuncompress($data));
    }
}
