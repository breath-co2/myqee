<?php

/**
 * 文件缓存驱动器
 *
 * @author     jonwang(jonwang@myqee.com)
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2012 myqee.com
 * @license	   http://www.myqee.com/license.html
 */
class Core_Cache_Driver_File
{
    protected $dir;

    public function __construct($config_name = 'default')
    {
        $this->dir = DIR_DATA . Core::$project . DS . 'cache' . DS;
    }

    public function __destruct()
    {

    }

    /**
     * 取得数据，支持批量取
     * @param string/array $key
     * @return mixed
     */
    public function get($key)
    {
        if ( is_array($key) )
        {
            # 支持多取
            $data = array();
            foreach ( $key as $k=>$v )
            {
                $data[$k] = $this->get((string)$v);
            }
            return $data;
        }

        $filename = $this->get_filename_by_key($key);
        if ( file_exists($filename) )
        {
            $data = @file_get_contents($filename);

            if ( $data && $this->get_expired_setting($key,$data) )
            {
                return $data;
            }
            else
            {
                # 删除失效文件
                $this->delete($key);
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    /**
     * 存数据
     *
     * @param string/array $key 支持多存
     * @param $data Value 多存时此项可空
     * @param $lifetime 有效期，默认3600，即1小时，0表示最大值30天（2592000）
     * @return boolean
     */
    public function set($key, $value = null, $lifetime = 3600)
    {
        if ( is_array($key) )
        {
            # 支持多存
            $i=0;
            foreach ( $key as $k=>$v )
            {
                if ($this->set((string)$k,$v,$lifetime))
                {
                    $i++;
                }
            }
            return $i==count($key)?true:false;
        }

        $filename = $this->get_filename_by_key($key);

        if ( !is_dir($this->dir) )
        {
            # 创建初始文件夹
            if ( !File::create_dir($this->dir) )
            {
                return false;
            }
        }

        $data = $this->format_data($lifetime, $value);

        return File::create_file($filename, $data);
    }

    /**
     * 删除指定key的缓存，若$key===true则表示删除全部
     *
     * @param string $key
     */
    public function delete($key)
    {
        if ( true===$key )
        {
            # 删除全部
            return File::remove_dir($this->dir);
        }
        if ( is_array($key) )
        {
            # 支持多取
            $data = array();
            $i=0;
            foreach ( $key as $k=>$v )
            {
                if ( $this->delete((string)$v) )
                {
                    $i++;
                }
            }
            return $i==count($key)?true:false;
        }

        $filename = $this->get_filename_by_key($key);

        if ( !file_exists($filename) )
        {
            return true;
        }

        return File::unlink($filename);
    }

    /**
     * 删除全部
     */
    public function delete_all()
    {
        return $this->delete(true);
    }

    /**
     * TODO 暂不支持
     *
     */
    public function delete_expired()
    {
        return false;
    }

    /**
     * 递减
     *
     * @param string $key
     * @param int $offset
     * @param int $lifetime 当递减失则时当作set使用
     */
    public function decrement($key, $offset = 1, $lifetime = 60)
    {
        return $this->increment($key, -$offset, $lifetime);
    }

    /**
     * 递增
     *
     * @param string $key
     * @param int $offset
     * @param int $lifetime 当递减失则时当作set使用
     */
    public function increment($key, $offset = 1, $lifetime = 60)
    {
        $filename = $this->get_filename_by_key($key);

        if ( !file_exists($filename) )
        {
            # 不存在，则设置
            return $this->set($key,$offset,$lifetime);
        }

        $fh = fopen($filename, 'r+');
        $i=1;
        while ( $i<=2 )
        {
            if ( flock( $fh, LOCK_EX ) )
            {
                $data = trim( fread( $fh, filesize( $filename ) ) );
                $expired_setting = $this->get_expired_setting($key,$data);
                if ( $expired_setting )
                {
                    $buffer = $data + $offset;
                    $lifetime = max($expired_setting['lifetime'],$lifetime);
                }
                else
                {
                    $buffer = $offset;
                }
                $data = $this->format_data($lifetime, $buffer);
                rewind( $fh );
                fwrite( $fh, $data );
                fflush( $fh );
                ftruncate( $fh, ftell( $fh ) );
                flock( $fh, LOCK_UN );

                return true;
            }

            usleep( 30*$i );
            $i++;
        }

        return false;
    }

    /**
     * 根据KEY获取文件路径
     *
     * @param string $key
     */
    protected function get_filename_by_key( $key )
    {
        return $this->dir . 'cache_file_' . substr(preg_replace('#[^a-z0-9_\-]*#i','',$key),0,100) . '_' . md5( $key . '_&@c)ac%he_file' );
    }

    protected function get_expired_setting( $key, & $data )
    {
        $dataArr = explode(CRLF, $data, 3);
        /*
        $dataArr[0] - 生存期
        $dataArr[1] - 设置时候的时间
        $dataArr[2] - serialize后的数据内容
        */
        if( $dataArr[0]==0 || TIME - $dataArr[1] <= $dataArr[0]   )
        {
            $data = @unserialize( $dataArr[2] );
            return array
            (
                'lifetime' => $dataArr[0],
                'settime'  => $dataArr[1],
            );
        }
        else
        {
            return false;
        }
    }

    protected function format_data($lifetime,$data)
    {
        return $lifetime . CRLF . TIME . CRLF . serialize($data);
    }
}