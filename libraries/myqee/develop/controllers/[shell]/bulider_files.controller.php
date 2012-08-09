<?php

/**
 * 构建文件到bulider目录以优化系统性能
 *
 * @author     jonwang(jonwang@myqee.com)
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2012 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Controller_Bulider_Files extends Controller_Shell
{

    protected $allfiles;

    protected $myfiles;

    protected $project;

    protected $bulid_path;

    protected $config_file;

    /**
     * 导出项目为独立项目，并优化加载速度
     */
    public function action_export()
    {
        $this->output('请选择你的项目：');
        $i = 0;
        $arr = array();
        foreach ( Core::config('core.projects') as $item )
        {
            $i++;
            $arr[$i] = $item;
            $this->output($i . ':' .$item['name']);
        }
        $id = $this->input();
        if ( !isset($arr[$id]) )
        {
            $this->output('指定的项目错误');
            return ;
        }

        $this->output('请输入待导出的路径');

        $str = $this->input();

        if ( is_dir($str) )
        {
            $this->output('指定的路径已存在，是否将文件导出到此文件夹？yes or no');
            $str = strtolower($this->input());

            if ( $str == 'no' || $str == 'n' )
            {
                $this->output('操作已取消');
                return ;
            }
        }

        $this->output('now reading files...');

        //TODO
    }
    public function before()
    {
        set_time_limit( 0 );
        $this->project = Core::$project;

        $this->bulid_path = DIR_BULIDER . $this->project . DIRECTORY_SEPARATOR;

        $this->config_file = $this->bulid_path . 'project_all_files_list' . EXT;
    }

    /**
     * 构建项目
     */
    public function action_bulider()
    {
        # 移除旧文件
        $this->action_remove();

        $this->create_dir( $this->bulid_path );

        $include = Core::$include_path;
        $include = array_reverse( $include );

        # 获取所有类文件
        $myfiles = array();
        foreach ( $include as $path )
        {
            $myfiles = array_merge( $myfiles, $this->glob_path( $path . 'classes' . DIRECTORY_SEPARATOR . '*', strlen( $path ) ) );
            $myfiles = array_merge( $myfiles, $this->glob_path( $path . 'models' . DIRECTORY_SEPARATOR . '*', strlen( $path ) ) );
            $myfiles = array_merge( $myfiles, $this->glob_path( $path . 'controllers' . DIRECTORY_SEPARATOR . '*', strlen( $path ) ) );
            $myfiles = array_merge( $myfiles, $this->glob_path( $path . 'orm' . DIRECTORY_SEPARATOR . '*', strlen( $path ) ) );
        }
        $this->myfiles = $myfiles;
        $this->bulider_class_files( $myfiles );

        # models
        $myfiles = array();
        foreach ( $include as $path )
        {
            $myfiles = array_merge( $myfiles, $this->glob_path( $path . 'models' . DIRECTORY_SEPARATOR . '*', strlen( $path ) ) );
        }
        $this->myfiles = array_merge( $this->myfiles, $myfiles );
        $this->bulider_class_files( $myfiles, false );

        # 视图文件
        $myfiles = array();
        foreach ( $include as $path )
        {
            $myfiles = array_merge( $myfiles, $this->glob_path( $path . 'views' . DIRECTORY_SEPARATOR . '*', strlen( $path ) ) );
        }
        $this->myfiles = array_merge( $this->myfiles, $myfiles );
        $this->bulider_class_files( $myfiles, false );

        # 配置
        $myfiles = array();
        foreach ( $include as $path )
        {
            $myfiles = array_merge_recursive( $myfiles, $this->glob_config_or_i18n_path( $path . 'config' . DIRECTORY_SEPARATOR . '*', strlen( $path ) ) );
        }
        $this->create_dir( $this->bulid_path . 'config' . DIRECTORY_SEPARATOR );
        $this->bulider_config_files( $myfiles, 'config' );

        # 语言包
        $myfiles = array();
        foreach ( $include as $path )
        {
            $myfiles = array_merge_recursive( $myfiles, $this->glob_config_or_i18n_path( $path . 'i18n' . DIRECTORY_SEPARATOR . '*', strlen( $path ) ) );
        }
        $this->create_dir( $this->bulid_path . 'i18n' . DIRECTORY_SEPARATOR );

        $this->bulider_i18n_files( $myfiles );

        $this->file_put_contents( $this->config_file, '<?php' . CRLF . '$config = ' . var_export( $this->allfiles, true ) . ';' );

        echo 'MergeFiles OK.'.CRLF;
    }

    /**
     * 移除项目构建文件
     */
    public function action_remove()
    {
        if ( is_file( $this->config_file ) )
        {
            unlink( $this->config_file );
        }

        echo 'now sleep 2 seconds'.CRLF;
        sleep( 2 );
        echo 'now remove project bulider dirs and files'.CRLF;
        $this->removedir( $this->bulid_path );
        echo 'remove ok...'.CRLF;
    }

    protected function bulider_class_files( $myfiles, $includeSubFile = true )
    {
        $is_exc_key = array(); //处理过的key存在此处，避免二次处理
        foreach ( $myfiles as $key => $file )
        {
            if ( is_array( $file ) ) continue;
            if ( ! isset( $this->allfiles[$key] ) )
            {
                $this->allfiles[$key] = $this->bulid_path . $key;
            }
            $keypath = substr( $key, 0, strrpos( $key, DIRECTORY_SEPARATOR ) );
            $ext = self::_get_ext_by_filekey($key);
            if ( isset( $this->myfiles[$keypath . $ext] ) && ! isset( $this->myfiles[substr( $key, 0, - strlen( $ext ) )] ) && isset( $this->myfiles[$keypath] ) && is_array( $this->myfiles[$keypath] ) )
            {
                continue;
            }

            if ( $includeSubFile && preg_match( '#^([a-z0-9]+)' . preg_quote( DIRECTORY_SEPARATOR ) . '([a-z0-9]+)(' . preg_quote( DIRECTORY_SEPARATOR ) . '.*' . preg_quote( $ext, '#' ) . ')$#i', $key, $match ) )
            {
                //上级扩展类置前操作
                $newkey = $match[1] . $match[3];
                if ( isset( $this->myfiles[$newkey] ) )
                {
                    if ( ! isset( $is_exc_key[$newkey] ) )
                    {
                        if ( ! isset( $this->allfiles[$newkey] ) )
                        {
                            $this->allfiles[$newkey] = $this->bulid_path . $newkey;
                        }
                        $is_exc_key[$newkey] = true;
                        $file_content = $this->_get_content( $newkey );
                        if ( $file_content !== false )
                        {
                            $this->write_file( $newkey, '<?php' . CRLF . $file_content );
                        }
                    }
                }
            }

            if ( $this->myfiles[$key] == false )
            {
                continue;
            }

            if ( isset( $is_exc_key[$key] ) )
            {
                continue;
            }
            $is_exc_key[$key] = true;

            $file_content = $this->_get_content( $key, $includeSubFile );
            if ( $file_content !== false )
            {
                if ( $includeSubFile )
                {
                    $this->write_file( $key, '<?php' . CRLF . $file_content );
                }
                else
                {
                    $this->write_file( $key, $file_content );
                }
            }
        }

    }

    protected function bulider_config_files( $myfiles, $filekey )
    {
        $content = '<?php' . CRLF;
        $config_file = $this->bulid_path . 'config' . DIRECTORY_SEPARATOR . $filekey . EXT;
        foreach ( $myfiles as $key => $files )
        {
            if ( ! is_array( $files ) ) continue;
            if ( ! isset( $files[0] ) )
            {
                $this->bulider_config_files( $files, 'config.' . str_replace( array( '\\', '/' ), '.', substr( $key, 7 ) ) );
            }
            else
            {
                $ext = self::_get_ext_by_filekey($key);
                foreach ( $files as $f )
                {
                    $tmp_content = file_get_contents( $f );
                    $tmp_content = preg_replace( '#^( |\t|\r|\n)?<\?(php)?#', '', $tmp_content );
                    $tmp_content = preg_replace( '#(php)?\?>( |\t|\r|\n)?$#', '', $tmp_content );
                    $content .= CRLF . $tmp_content . CRLF;
                }
                //$key = config\test\abc.php;
                $mykey = str_replace( '\\', '/', substr( $key, 7, - strlen( $ext ) ) );

                $this->allfiles['config/' . $mykey . $ext] = $config_file;
                $content .= 'Core::$config[\'' . $mykey . '\'] = $config;' . CRLF . '$config = array();' . CRLF;
            }
        }

        $this->file_put_contents( $config_file, $content );
    }

    protected function bulider_i18n_files( $myfiles )
    {
        foreach ( $myfiles as $key => $files )
        {
            if ( !preg_match('#^i18n(?:\\\|/)(.*)\.lang$#i',$key,$m) )
            {
                $this->allfiles[$key] = $files;
                # 非.lang后缀的文件或略
                continue;
            }
            /*
                $key = i18n/zh-cn.lang
                则
                $mykey = zh-cn
            */
            $lang_file = $m[1];
            $content = '<?php' . CRLF;
            $config_file = $this->bulid_path . 'i18n' . DIRECTORY_SEPARATOR . $lang_file . EXT;
            if ( ! is_array( $files ) ) continue;
            if ( isset( $files[0] ) )
            {
                $tmp_arr = array();
                if ( count( $files ) > 1 )
                {
                    krsort( $files ); //逆向排序
                }
                foreach ( $files as $f )
                {
                    $tmp_content = @parse_ini_file( $f );
                    if ( is_array( $tmp_content ) )
                    {
                        $tmp_arr = array_merge_recursive( $tmp_arr, $tmp_content );
                    }
                }
                $content .= CRLF .'$lang=' . var_export( $tmp_arr, true ) . ';' . CRLF;

                $this->allfiles[$key] = $config_file;
            }
            $this->create_dir( substr( $config_file, 0, 1 + strrpos( $config_file, DIRECTORY_SEPARATOR ) ) );
            $this->file_put_contents( $config_file, $content );
        }
    }

    protected function glob_path( $path, $dir_len )
    {
        $files = glob( $path, GLOB_NOSORT );
        $myfiles = array();
        foreach ( $files as $file )
        {
            if ( $file[0] == '.' ) continue;
            if ( is_dir( $file ) )
            {
                $dir_files = $this->glob_path( $file . DIRECTORY_SEPARATOR . '*', $dir_len );
                $myfiles = array_merge( $myfiles, $dir_files );
                $keystr = substr( $file, $dir_len );
                $keylen = strlen( $keystr );
                foreach ( $dir_files as $k => $v )
                {
                    if ( ! is_array( $v ) && strpos( substr( $k, $keylen + 1 ), DIRECTORY_SEPARATOR ) === false )
                    {
                        //单文件且属于它的目录且非多级目录
                        $myfiles[$keystr][$k] = $v;
                    }
                }
            }
            else
            {
                $myfiles[substr( $file, $dir_len )] = $file;
            }
        }
        return $myfiles;
    }

    protected function glob_config_or_i18n_path( $path, $dir_len )
    {
        $files = glob( $path, GLOB_NOSORT );
        $myfiles = array();
        foreach ( $files as $file )
        {
            if ( $file[0] == '.' ) continue;
            if ( is_dir( $file ) )
            {
                $dir_files = $this->glob_config_or_i18n_path( $file . DIRECTORY_SEPARATOR . '*', $dir_len );
                $keystr = substr( $file, $dir_len );
                $myfiles[$keystr] = $dir_files;
            }
            else
            {
                $myfiles[substr( $file, $dir_len )][] = $file;
            }
        }
        return $myfiles;
    }

    protected function write_file( $filekey, $content )
    {
        $file = DIR_BULIDER . $this->project . DIRECTORY_SEPARATOR . $filekey;
        $path = substr( $file, 0, strrpos( $file, DIRECTORY_SEPARATOR ) );

        $this->create_dir( $path );

        if ( ! is_dir( $path ) )
        {
            $this->create_dir( $path );
        }
        $this->file_put_contents( $file, $content );
    }

    protected function create_dir( $path )
    {
        if ( is_dir( $path ) )
        {
            return true;
        }
        $dir_arr = explode( DIRECTORY_SEPARATOR, $path );
        $nowdir = '';
        foreach ( $dir_arr as $dir )
        {
            $nowdir .= $dir . DIRECTORY_SEPARATOR;
            if ( ! is_dir( $nowdir ) )
            {
                mkdir( $nowdir );
                chmod( $nowdir, 0775 );
            }
        }
        return true;
    }

    protected function removedir( $dirName )
    {
        if ( ! is_dir( $dirName ) )
        {
            return false;
        }

        $realpath = str_replace( '\\', '/', realpath( $dirName ) ) . '/';

        $handle = opendir( $dirName );
        while ( ($file = readdir( $handle )) !== false )
        {
            if ( $file != '.' && $file != '..' )
            {
                $dir = $dirName . DIRECTORY_SEPARATOR . $file;
                if ( is_dir( $dir ) ){
                    self::removedir( $dir );
                }
                else
                {
                    if ( !@unlink( $dir ) )
                    {
                        echo 'remove file ' .$dir.' fail.';
                        exit;
                    }
                }
            }
        }
        closedir( $handle );

        if ( !@rmdir( $dirName ) )
        {
            echo 'remove dir ' .$dirName.' fail.';
            exit;
        }
    }

    protected function _get_content( $key, $includeSubFile = true )
    {
        if ( ! isset( $this->myfiles[$key] ) || $this->myfiles[$key] == false ) return false;
        $file = $this->myfiles[$key];
        $ext = self::_get_ext_by_filekey($key);
        $extlen = strlen( $ext );

        if ( substr( $file, - $extlen ) == $ext )
        {
            # PHP 文件
            static $loaded_files = array();
            if ( isset( $loaded_files[$file] ) )
            {
                return '';
            }
            $loaded_files[$file] = true;
            $file_content = file_get_contents( $file );

            if ( ! $includeSubFile )
            {
                return $file_content;
            }

            $file_content = preg_replace( '#^( |\t|\r|\n)?<\?(php)?#', '', $file_content );

            $includefiles = false;
            $filePath = substr( $key, 0, - $extlen );
            if ( isset( $this->myfiles[$filePath] ) )
            {
                # 拿到所有子文件
                $includefiles = $this->myfiles[$filePath];
            }

            # 将文件写入合并目录
            if ( $includefiles )
            {
                $include_content = '';
                # 合并文件时需要把多余的php封闭符过滤掉
                foreach ( $includefiles as $k => $f )
                {
                    if ( ! isset( $this->myfiles[substr( $k, 0, - $extlen )] ) )
                    {
                        if ( ! isset( $loaded_files[$f] ) )
                        {
                            $loaded_files[$f] = true;

                            # 当前文件不含子目录才合并，否则不合并
                            $tmp_content = file_get_contents( $f );
                            $tmp_content = preg_replace( '#(php)?\?>( |\t|\r|\n)?$#', '', $tmp_content );

                            $tmp_content = preg_replace( '#^( |\t|\r|\n)?<\?(php)?#', '', $tmp_content );

                            $include_content .= $tmp_content . CRLF;

                            $this->allfiles[$k] = & $this->allfiles[$key];
                        }
                    }
                }
                $file_content = $include_content . CRLF . preg_replace( '#^( |\t|\r|\n)?<\?(php)?#', '', $file_content );
            }
            $this->_find_extends_files( $key, $file_content );
            return $file_content;
        }
        else
        {
            $this->allfiles[$key] = $this->myfiles[$key];
        }
        return false;
    }

    protected function _find_extends_files( $key, & $content )
    {
        if ( preg_match_all( '#class ([a-z0-9_]+) extends ([a-z0-9_]+)#i', $content, $match ) )
        {
            foreach ( $match[0] as $i => $v )
            {
                if ( preg_match( '#^([a-z0-9]+_)' . preg_quote( $match[1][$i], '#' ) . '$#i', $match[2][$i], $m ) )
                {
                    # 例如  class Database_Driver extends MyQEE_Database_Driver
                    # 则  $m[1] = MyQEE_
                    $newkey = 'classes' . DIRECTORY_SEPARATOR . str_replace( '_', DIRECTORY_SEPARATOR, $match[2][$i] );
                    $ext = self::_get_ext_by_filekey($key);
                    $newkey .= $ext;
                    if ( isset( $this->myfiles[$newkey] ) && $this->myfiles[$newkey] )
                    {
                        $content = preg_replace( '#(php)?\?>( |\t|\r|\n)?$#', '', $this->_get_content( $newkey ) ) . CRLF . preg_replace( '#^( |\t|\r|\n)?<\?(php)?#', '', $content );
                        $this->myfiles[$newkey] = false;
                        $this->allfiles[$newkey] = & $this->allfiles[$key];
                    }
                }
            }
        }
    }

    /**
     * 写文件
     */
    protected function file_put_contents( $filename, $data, $flags = null, $context = null )
    {
        echo "write file {$filename} ";
        $status = file_put_contents( $filename, $data, $flags, $context );
        echo $status ? 'ok' : 'fail';
        echo CRLF;
    }

    protected static function _get_ext_by_filekey($key)
    {
        $dir = substr( $key,0,strpos($key,DIRECTORY_SEPARATOR) );
        if ( isset(Core::$autoload_dir_ext[$dir]) )
        {
            $ext = Core::$autoload_dir_ext[$dir] . EXT;
        }else{
            $ext = EXT;
        }
        return $ext;
    }
}