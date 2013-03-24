<?php
/**
 * API控制器
 *
 * @author     jonwang(jonwang@myqee.com)
 * @category   MyQEE
 * @package	Docs
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2013 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Controller_Docs_Api extends Controller_Docs_Base
{

    /**
     * 项目名
     * @var string
     */
    protected $project;

    /**
     * 项目配置
     * @var array
     */
    protected static $project_config;

    protected static $include_path = array();

    public function before()
    {
        parent::before();
        if ($this->project)
        {
            if ( !Core::config('core.projects.'.$this->project) )
            {
                echo '指定的项目不存在!';
                $this->view->lefthtml = $this->execute( 'docs/index/left_menu', false );
                $this->after();
                exit;
            }
            Core::set_project($this->project);
            self::$include_path = Core::$include_path;
            # 切回当前项目
            Core::reset_project();
            Core::include_project($this->project);
            define( 'DOCS_PROJECT', $this->project );
        }
    }

    public function action_default()
    {
        $this->view->lefthtml = $this->execute( 'docs/index/left_menu', false );
        $this->execute('docs/index/project_list');
    }

    public function action_api()
    {
        if ( substr( $this->file, - strlen( EXT ) ) == EXT )
        {
            $classname = substr( str_replace( '/', '_', $this->file ), 0, strpos($this->file,'.') );

            if ( $this->dir == 'controllers' || $this->dir == 'shell' )
            {
                $is_class = true;
                $class_name = 'Controller_' . strtolower($classname);
            }
            elseif ( $this->dir == 'models' )
            {
                $is_class = true;
                $class_name = 'Model_' . $classname;
            }
            elseif ( $this->dir == 'orm' )
            {
                $is_class = true;
                $class_name = 'orm_' . $classname . '_Finder';
            }
            elseif( $this->dir == 'config' )
            {
                $is_class = false;
                $config_key = substr($this->file,0,-11);
                $config = Core::config($config_key,$this->project);
                View::factory( 'docs/api/config_and_i18n' )->set('file',$this->file)->set('config', $config )->set( 'dir',$this->dir )->render();
                $this->filepath = true;
            }
            else
            {
                $is_class = true;
                $class_name = $classname;
            }

            if ( $is_class )
            {
                if ( class_exists( $class_name, true ) )
                {
                    $doc = Docs::factory( $class_name );
                    $this->filepath = $doc->class->getFileName();
                }

                if ( $this->filepath )
                {
                    $this->view->righthtml = View::factory( 'docs/api/classtoc' )->set( 'doc', $doc )->render( false );
                    # 输出结果
                    View::factory( 'docs/api/class' )->set( 'doc', $doc )->set( 'dir', $this->dir )->set( 'route', Core::route() )->render( true );
                }
                elseif ( class_exists($class_name,false) )
                {
                    # PHP 默认类
                    $this->redirect('http://www.php.net/manual/zh/class.'.strtolower($classname).'.php');
                }
            }
        }
        elseif (substr( $this->file,-5 )=='.lang' )
        {
            # 语言包
            foreach ( self::$include_path as $path )
            {
                $tmpfile = $path . 'i18n' . DIRECTORY_SEPARATOR . $this->file;
                if (is_file($tmpfile))
                {
                    $found[] = $tmpfile;
                }
            }
            $config = array();
            if ($found)
            {
                foreach ( $found as $file )
                {
                    $config = array_merge ( @parse_ini_file($file) , $config );
                }
            }
            View::factory( 'docs/api/config_and_i18n' )->set('file',$this->file)->set('config', $config )->set( 'dir',$this->dir )->render();
            $this->filepath = true;
        }

        if ( !$this->filepath )
        {
            $this->dir_classes();
        }

        $this->view->lefthtml = $this->file_tree();
    }

    protected function dir_classes()
    {
        $myfiles = array();
        foreach ( self::$include_path as $mypath )
        {
            $myfiles = array_merge( $myfiles, $this->glob_path( $mypath . $this->dir . DIRECTORY_SEPARATOR . $this->file . DIRECTORY_SEPARATOR . '*', strlen( $mypath ) ) );
        }
        $arr_docs = array();
        foreach ( $myfiles as $key => $item )
        {
            $tmpClassName = self::get_class_name( $key );
            if ( $tmpClassName )
            {
                if ( class_exists( $tmpClassName, true ) )
                {
                    $arr_docs[] = Docs::factory( $tmpClassName );
                    if ( $this->dir == 'orm' )
                    {
                        # ORM 特殊处理
                        $orm_class_base = substr( $tmpClassName, 0, - 5 );
                        foreach ( array( 'data', 'finder', 'result' ) as $item )
                        {
                            if ( class_exists( $orm_class_base . $item, false ) )
                            {
                                $arr_docs[] = Docs::factory( $orm_class_base . $item );
                            }
                        }
                    }
                }
            }
        }
        $main_view = new View( 'docs/api/dir_main' );
        $main_view->dir = $this->dir;
        $main_view->classname = $this->file;
        $main_view->arr_docs = $arr_docs;
        $main_view->render( true );
    }

    protected function file_tree()
    {
        if ( ! in_array( $this->dir, array( 'controllers', 'classes', 'shell', 'orm', 'config', 'i18n', 'models' ) ) )
        {
            return null;
        }
        $myfiles = array();
        foreach ( self::$include_path as $mypath )
        {
            $myfiles = array_merge( $myfiles, $this->glob_path( $mypath . $this->dir . DIRECTORY_SEPARATOR . '*', strlen( $mypath ) ) );
        }

        if ($this->file)
        {
            $fileArr = explode( '/', $this->file );
            if ( is_file( $this->filepath ) )
            {
                array_pop( $fileArr );
            }
            # 获取子文件夹、文件
            if ( $fileArr )
            {
                $tmppath = $this->dir;
                $tmpfiles = & $myfiles;
                $tmpfiles2 = null;
                foreach ( $fileArr as $v )
                {
                    $tmppath .= '/' . $v;
                    $tmpfiles2 = & $tmpfiles;
                    unset( $tmpfiles );
                    $tmpfiles = & $tmpfiles2[$tmppath];
                    if ($tmpfiles!==true)continue;
                    $tmpfiles = array();
                    foreach ( self::$include_path as $mypath )
                    {
                        $s = $mypath . str_replace( '/', DIRECTORY_SEPARATOR, trim( $tmppath, '/' ) ) . DIRECTORY_SEPARATOR . '*';
                        $tmpfiles = array_merge( $tmpfiles, ( array ) $this->glob_path( $s, strlen( $mypath ) ) );
                    }
                }
            }
        }

        # 调整文件夹和文件的位置
        $myfiles = self::arsort_file( $myfiles );

        return View::factory( 'docs/api/file_tree' )->set( 'files', $myfiles )->set( 'nowfile', $this->file )->set( 'path', $this->dir )->set( 'project', $this->project )->render( false );
    }

    protected function glob_path( $path, $dir_len )
    {
        $files = glob( $path, GLOB_NOSORT );
        $myfiles = array();
        if ( $files ) foreach ( $files as $file )
        {
            if ( $file[0] == '.' ) continue;
            $k = str_replace( '//', '/', substr( str_replace( '\\', '/', $file ), $dir_len ) );
            if (is_file($file))
            {
                $myfiles[$k] = $file;
            }
            else
            {
                $myfiles[$k] = true;
            }
        }
        return $myfiles;
    }

    protected static function arsort_file( $files )
    {
        # 调整文件夹和文件的位置
        $mydir = $myfile = array();
        foreach ( $files as $k => $v )
        {
            if ( is_array($v) )
            {
                $mydir[$k] = self::arsort_file($v);
            }
            else if ( is_dir( $v ) )
            {
                # 文件夹优先
                $mydir[$k] = $v;
            }
            else if ( true===$v )
            {
                $mydir[$k] = true;
            }
            else
            {
                $myfile[$k] = $v;
            }
        }
        ksort($mydir);
        ksort($myfile);
        return array_merge( $mydir, $myfile );
    }

    protected static function get_class_name( $key )
    {
        $key = explode( '/', $key );
        $profix = array_shift( $key );
        if ( $profix == 'classes' || $profix == 'models' || $profix == 'controllers' || $profix == 'shell' || $profix == 'orm' )
        {
            static $profixArr = array(
                'classes' => '.class',
            );
            $class = implode( '_', $key );
            if ( preg_match( '#^([a-z0-9_]+)(\.[a-z]+)?(' . preg_quote( EXT ) . ')$#i', $class, $m ) )
            {
                if ( $profix == 'controllers' || $profix == 'shell' )
                {
                    if ( ! class_exists( 'Controller_' . $m[1], false ) )
                    {
                        Core::find_file( $profix, $m[1], null, true );
                    }
                    return 'Controller_' . $m[1];
                }
                elseif ( $profix == 'models' )
                {
                    return 'Model_' . $m[1];
                }
                elseif ( $profix == 'orm' )
                {
                    return 'ORM_' . $m[1] . '_Field';
                }
                else
                {
                    return $m[1];
                }
            }
        }
    }
}