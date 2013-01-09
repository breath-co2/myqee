<?php

/**
 * 配置程序核心类
 *
 * @author     jonwang(jonwang@myqee.com)
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2012 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class MyQEE_Core_Config
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
     * 配置
     * @var array
     */
    protected $config;

    function __construct()
    {
        $config = Core::config('configuration');
        if ($config)
        {
            if ( isset($config['database']) && $config['database'] )$this->database = $config['database'];
            if ( isset($config['tablename']) && $config['tablename'] )$this->tablename = $config['tablename'];
        }
    }

    /**
     * 保存一个配置，支持批量设置
     *
     * @param string/array $key 关键字
     * @param fixed $value 值
     * @param string $type 类型,长度32以内
     * @return boolean
     */
    public function set($key, $value , $type = '')
    {
        $db = new Database($this->database);
        $type = (string)$type;
        try
        {
            if ( is_array($key) )
            {
                # 批量设置
                $tr = $db->transaction();
                $tr->start();
                try
                {
                    # 先尝试删除旧数据
                    $db->where('type',$type)
                    ->and_where_open();
                    foreach ($key as $k)
                    {
                        $db->or_where('key_md5',md5($k));
                    }
                    $db->and_where_close()
                    ->delete($this->tablename);

                    # 设置数据
                    foreach ($key as $i=>$k)
                    {
                        $data = array(
                            'type'     => $type,
                            'key_md5'  => md5($k),
                            'key_name' => $k,
                            'value'    => gzcompress(serialize($value[$i]),9),
                        );
                        $db->values($data);
                    }

                    $db->columns(
                        array(
                            'type',
                            'key_md5',
                            'key_name',
                            'value',
                        )
                    );
                    $db->insert($this->tablename);

                    $tr->commit();

                    $this->clear_cache();
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
                    'value'    => gzcompress(serialize($value),9),
                );
                $status = $db->replace( $this->tablename , $data );
                $status = $status[1];
            }
            if ($status)
            {
                if (is_array($this->config))
                {
                    $this->config[$key] = $value;
                }
                $this->clear_cache();
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
     * @param string $key
     * @return fixed
     */
    public function get($key,$type='')
    {
        if (null===$this->config)$this->reload(false);

        $type = (string)$type;
        if (isset($this->config[$type][$key]))return $this->config[$type][$key];

        return null;
    }

    /**
     * 获取制定type的所有值
     *
     * @param string $type
     * @return array
     */
    public function get_by_type($type='')
    {
        if (null===$this->config)$this->reload(false);

        $type = (string)$type;

        return (array)$this->config[$type];
    }

    /**
     * 删除制定key的配置，支持多个
     *
     * @param string/array $key
     * @param string $type
     * @return boolean
     */
    public function delete($key,$type='')
    {
        $db = new Database($this->database);
        $type = (string)$type;
        try
        {
            if ( is_array($key) )
            {
                # 多个key
                $db->where('type',$type);
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
                $db->delete( $this->tablename , array('type'=>$type,'key_md5'=>md5($key)) );
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
            $db->delete( $this->tablename , array('type'=>$type) );
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
     * @return Core_Config
     */
    public function reload($from_db=true)
    {
        $tmpfile = DIR_DATA.Core::$project.'/extends_config.txt';

        if ( !$from_db && is_file($tmpfile) )
        {
            # 在data目录中直接读取
            $this->config = unserialize(file_get_contents($tmpfile));
            if (!is_array($this->config))
            {
                $this->config = array();
            }
        }
        else
        {
            # 没有缓存数据，则直接在数据库里获取
            $db = new Database($this->database);
            $config = $db->from($this->tablename)->get(false,true)->as_array();

            if ($config)
            {
                foreach ($config as $item)
                {
                    $this->config[$item['type']][$item['key_name']] = @unserialize(gzuncompress($item['value']));
                }
            }
            else
            {
                $this->config = array();
            }

            // 写缓存
            $rs = File::create_file($tmpfile, serialize($this->config));

            if (IS_DEBUG)Core::debug()->log('save extends config cache '.($rs?'success':'fail').'.');
        }
    }

    /**
     * 清除配置缓存
     */
    public function clear_cache()
    {
        $projects = array_keys((array)Core::config('core.projects'));
        if ($projects)foreach ($projects as $project)
        {
            # 所有项目的配置文件
            $tmpfile[] = DIR_DATA.$project.'/extends_config.txt';
        }

        $rs = File::unlink($tmpfile);

        if (IS_DEBUG)Core::debug()->log('clear extends config cache '.($rs?'success':'fail').'.');
        return $rs;
    }
}
