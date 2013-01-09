<?php

/**
 * 静态文件输出
 *
 * @author jonwang
 *
 */
class Library_MyQEE_Develop_Controller_Assets extends Controller
{
    /**
     * assets允许的文件后缀名
     *
     * 可在config.php中修改$config['asset_allow_suffix']值
     *
     * @var string
     */
    protected $allow_suffix = 'js|css|jpg|jpeg|png|gif|bmp|pdf|html|htm|mp4|swf';

    /**
     * 文件名
     *
     * @var sting
     */
    protected $file;

    /**
     * 文件类型
     *
     * @var string
     */
    protected $type;

    /**
     * 项目
     *
     * @var string
     */
    protected $project;

    public function before()
    {
        # 只允许本地调试模式下使用
        if (!(IS_DEBUG & 1))Core::show_404(__('Only allows the development mode'));

        if ($this->action!='default')
        {
            $this->project = array_shift($this->arguments);
        }

        # 允许的后缀名
        $allow_suffix = Core::config('core.asset_allow_suffix');
        if ($allow_suffix)
        {
            $this->allow_suffix = $allow_suffix;
        }

        $arguments = $this->arguments;
        $f = array_pop($arguments);
        if ($f)
        {
            $rpos = strrpos($f,'.');
            $args       = $arguments;
            $args[]     = substr($f,0,$rpos);
            $this->file = implode('/', $args);
            $this->type = substr($f,$rpos+1);
        }

        $this->get_assets_md5();
        exit;
    }

    /**
     * 实时动态输出assets文件
     *
     * @throws Exception
     */
    public function action_devmode()
    {
        if (!$this->file)
        {
            Core::show_404();
        }

        if ( !preg_match('#^([a-zA-Z0-9_/\-\.]+)$#', $this->file) )
        {
            Core::show_404(__('Special characters of the file exists allowed.'));
        }

        if ( !$this->type || !preg_match('#('.$this->allow_suffix.')$#i', $this->type) )
        {
            Core::show_404(__('File suffix %s not allow.',array('%s'=>$this->type)));
        }

        # 设置项目
        Core::set_project($this->project);

        # 小写的后缀
        $low_type = strtolower($this->type);

        if ($low_type=='css'||$low_type=='js')
        {
            $this->output_css_js_file();
        }
        else
        {
            $this->output_other_file();
        }

        exit;
    }

    protected function send_header()
    {
        Core::close_buffers(false);

        # 清理所有已输出的header
        if ( version_compare(PHP_VERSION,'5.3','>=') )
        {
            $fun = 'header_remove';
            $fun();
        }

        $low_type = strtolower($this->type);

        if ( $low_type == 'jpg' )
        {
            header('Content-Type: image/jpeg');
        }
        elseif ( in_array( $low_type, array('gif','png') ) )
        {
            header('Content-Type: image/'.$low_type);
        }
        elseif ( $low_type == 'css' )
        {
            header('Content-Type: text/css');
        }
        elseif ( $low_type == 'js' )
        {
            header('Content-Type: application/x-javascript');
        }
        elseif ( $low_type == 'swf' )
        {
            header('Content-Type: application/swf');
        }
        else
        {
            # 在mimes表里获取
            $mimes = Core::config('mimes.'.$low_type);
            if ($mimes && is_array($mimes))
            {
                header('Content-Type: '.current($mimes));
            }
            else
            {
                header('Content-Type: text/plain');
            }
        }

        HttpIO::set_cache_header(86400);
    }

    /**
     * 根据文件名直接输出
     *
     * @param string $file
     */
    protected function output_by_file($file)
    {
        $rpos = strrpos($file,'.');
        $type = substr($file,$rpos);
        if ( !$type || !preg_match('#('.$this->allow_suffix.')$#i', $type) )
        {
            Core::show_500(__('File suffix %s not allow.',array('%s'=>$type)));
        }

        $this->send_header();

        $fun = 'apache_get_modules';
        if (function_exists($fun))
        {
            if (in_array('mod_xsendfile',$fun()))
            {
                $slen = strlen(DIR_SYSTEM);
                if (substr($file,0,$slen)==DIR_SYSTEM)
                {
                    # 采用xsendfile发送文件
                    header('X-Sendfile: '.substr($file,$slen));
                    exit();
                }
            }
        }
        else
        {
            header('Last-Modified: '.date('D, d M Y H:i:s \G\M\T', filemtime($file)));
        }

        # 直接读取文件输出
        readfile($file);
    }

    /**
     * 直接输出内容
     *
     * @param string $content
     */
    protected function output_by_content($content)
    {
        $this->send_header();

        echo $content;
    }

    /**
     * 执行输出JS或CSS
     *
     */
    protected function output_css_js_file()
    {
        $file_paths = $this->get_css_or_js_files_array();

        # md5存放的文件
        $cachefile = DIR_DATA . 'all_asset_files_md5';
        if (is_file($cachefile))
        {
            $asset_files_md5 = (array)unserialize(file_get_contents($cachefile));
        }
        else
        {
            $asset_files_md5 = array();
        }

        # 输出目录
        $out_dir = DIR_ASSETS . $this->project .'/';

        $out_file = $out_dir . $this->file .'.'. $this->type;

        if ( is_file($out_file) )
        {
            $changed = false;
            foreach ($file_paths['file_md5'] as $fullpath => $md5)
            {
                $debug_path = Core::debug_path($fullpath);
                if (!isset($asset_files_md5[$debug_path]) || $asset_files_md5[$debug_path]!=$md5)
                {
                    $changed = true;
                    break;
                }
            }
        }
        else
        {
            $changed = true;
        }

        if ($changed)
        {
            foreach ( $file_paths['main'] as $file=>$fullpath )
            {
                # 内容
                if (true===$fullpath)
                {
                    $content = '';
                }
                else
                {
                    $content = file_get_contents($fullpath);
                }

                # 当前文件的扩展
                if (isset($file_paths['extends'][$file]) && $file_paths['extends'][$file])
                {
                    $content .= CRLF.file_get_contents($file_paths['extends'][$file]);
                }

                # 加入模块
                if (isset($file_paths['modules'][$file]) && $file_paths['modules'][$file])
                {
                    foreach ($file_paths['modules'][$file] as $file2 => $fullpath2)
                    {
                        $content .= CRLF.file_get_contents($fullpath2);

                        # 模块文件的扩展文件
                        if (isset($file_paths['extends'][$file2]) && $file_paths['extends'][$file2])
                        {
                            $content .= CRLF.file_get_contents($file_paths['extends'][$file2]);
                        }
                    }
                }
            }

            if ($this->type=='css')
            {
                $this->add_css_image_version(&$content);

                if (isset($file_paths['is_less']) && $file_paths['is_less']===true)
                {
                    # 处理LESS
                    $this->prease_less($out_file,$content);
                }
            }

            # 保存文件
            if ( File::create_file($out_file, $content) )
            {
                # 写入文件
                foreach ($file_paths['file_md5'] as $fullpath => $md5)
                {
                    $debug_path = Core::debug_path($fullpath);
                    $asset_files_md5[$debug_path] = $md5;
                }

                # 排序2次的用途是保证一些特殊情况下排序能得到纠正，比如有2个key分别是 test1.js 和 test.js 在排序时会有bug
                asort($asset_files_md5);
                asort($asset_files_md5);

                $old_md5_content = serialize($asset_files_md5);
                if (!is_file($cachefile) || md5($old_md5_content)!=md5_file($cachefile))
                {
                    # 保存MD5列表
                    File::create_file($cachefile,$old_md5_content);
                }

                $this->output_by_file($out_file);
            }
            else
            {
                $this->output_by_content($content);
            }
        }
        else
        {
            $this->output_by_file($out_file);
        }
    }

    /**
     * 输出非css，js文件
     *
     * @throws Exception
     */
    protected function output_other_file()
    {
        $found_file = Core::find_file('assets', $this->file, '.'.$this->type);

        if ($found_file)
        {
            $this->output_by_file($found_file);
        }
        else
        {
            Core::show_404(__('Assets files : %s not found.',array('%s'=>$this->file.'.'.$this->type)));
        }
    }

    /**
     * 获取CSS或JS文件的整理后的数组
     *
     * @return array
     */
    protected function get_css_or_js_files_array()
    {
        $include_path = Core::include_path();
        $include_path = array_reverse($include_path);

        # 记录整理后的数据
        $file_paths = array();

        # 记录所有相关文件
        $all_files = array();
        # 搜索所有相关文件
        foreach ( $include_path as $path )
        {
            $path_len = strlen($path . 'assets' . DS);
            $glob_file = $path.'assets'.DS.$this->file.'.*'.($this->type=='css'?'':$this->type);
            $files = glob( $glob_file, GLOB_NOSORT );
            if ($files)foreach ($files as $tmpfile)
            {
                $filename = str_replace('\\','/',substr($tmpfile,$path_len));

                if ($this->type=='css')
                {
                    $tmptype = strtolower(substr($tmpfile,-5));
                    if (strtolower(substr($tmpfile,-4))!='.css' && $tmptype!='.less')
                    {
                        continue;
                    }

                    # 将less后缀转处理为.css
                    if ($tmptype=='.less')
                    {
                        $filename = substr($filename,0,-5).'.css';
                        $file_paths['is_less'] = true;
                    }
                }

                $all_files[$filename] = $tmpfile;
            }
        }

        if (!$all_files)
        {
            Core::show_404();
        }

        foreach ($all_files as $file=>$fullpath)
        {
            $path_rpos = strrpos($file,'/');
            $file_name = substr($file,$path_rpos+1);
            $rpos      = strrpos($file_name,'.');
            $suffix    = strtolower(substr($file_name,$rpos+1));

            if ($suffix=='css'||$suffix=='js')
            {
                # 将文件列表加入到all数组里
                $file_paths['file_md5'][$fullpath] = md5_file($fullpath);

                # 处理CSS，JS文件
                $file_name_arr       = explode('.',$file_name);
                $count_file_name_arr = count($file_name_arr);

                if ($count_file_name_arr>=3)
                {
                    $type = strtolower($file_name_arr[1]);
                    if ('extend'==$type || 'mod'==$type)
                    {
                        if ('extend'==$type)
                        {
                            if ($count_file_name_arr==3)
                            {
                                unset($file_name_arr[1]);
                            }
                            else
                            {
                                $file_name_arr[1] = 'mod';
                            }
                        }
                        else
                        {
                            $file_name_arr = array($file_name_arr[0],$file_name_arr[count($file_name_arr)-1]);
                        }

                        $parent_file_name = implode('.',$file_name_arr);
                        $parent_file_path = substr($file,0,-strlen($file_name)).$parent_file_name;

                        if ('mod'==$type)
                        {
                            $file_paths['modules'][$parent_file_path][substr($file,0,-strlen($file_name)).$file_name] = $fullpath;
                        }
                        else
                        {
                            $file_paths['extends'][$parent_file_path] = $fullpath;
                        }

                        if ($count_file_name_arr==3 && !isset($file_paths['main'][$parent_file_path]))
                        {
                            $file_paths['main'][$parent_file_path] = true;
                        }
                    }
                }
                else
                {
                    $file_paths['main'][$file] = $fullpath;
                }
            }

        }

        return $file_paths;
    }

    /**
     * 增加CSS文件版本号信息
     *
     * @param css文件内容 $content
     * @param css文件路径 $file
     */
    protected function add_css_image_version(&$content)
    {

        return ;

        # 拿到css里所有背景图片
        if ( preg_match_all('#url\((?:\'|")?([^\'"]*)(?:\'|")?\)#Uis',$content,$match) )
        {
            $file_path_arr = explode('/',$this->file);
            array_pop($file_path_arr);    // 移除文件名

            # 去重
            $allurl = array_unique($match[1]);

            foreach ($allurl as $img)
            {
                if (strpos($img,'://')!==false)continue;
                $tmp_file_path_arr = $file_path_arr;

                # 去掉?后面的东西
                list($format_path,$query) = explode('?',$img.'?');
                $imgdir = explode('/',$format_path);

                foreach ($imgdir as $k=>$dir)
                {
                    if ($dir=='.')
                    {
                        unset($imgdir[$k]);
                    }
                    else if ($dir=='..')
                    {
                        array_pop($tmp_file_path_arr);
                        unset($imgdir[$k]);
                    }
                    else
                    {
                        break;
                    }
                }

                # 拼装出最后的结果
                $format_path = implode('/',$tmp_file_path_arr).'/'.implode('/',$imgdir);
                echo $format_path;

                if (isset($file_md5[$format_path]))
                {
                    if ($query)
                    {
                        $img2 = $img.'&v='.$file_md5[$format_path];
                    }
                    else
                    {
                        $img2 = $img.'?v='.$file_md5[$format_path];
                    }
                }

                $content = str_replace($img,$img2,$content);
            }
        }
    }

    /**
     * 处理解析LESS文件
     *
     * @throws Exception
     */
    protected function prease_less($out_file,&$content)
    {
        # 通过recess处理less文件
        $tmpfile = DIR_TEMP . 'tmpless_'.md5($this->file) . '.less';

        if (!function_exists('exec'))
        {
            throw new Exception(__('The system does not allow you to execute exec function, you can not call the node processing less file'));
        }

        try
        {
            file_put_contents($tmpfile, $content);
        }
        catch (Exception $e)
        {
            if (strpos($e->getMessage(),'Permission denied')!==false)
            {
                Core::show_500(__('Permission denied : :file',array(':file'=>Core::debug_path($tmpfile))));
            }
            throw $e;
        }

        list($node_file,$node_modules_path) = $this->get_node_set();

        $cmd = 'cd '.(escapeshellcmd($node_modules_path)).' && ' . escapeshellcmd($node_file).' '.escapeshellarg('./node_modules/recess/bin/recess').' --compile '.escapeshellarg($tmpfile);

        if (IS_DEBUG)Core::debug()->info($cmd,'exec');

        # 执行
        exec($cmd,$output,$r);

        # 删除临时文件
        unlink($tmpfile);

        if (0===$r)
        {
            if ($output)
            {
                # 更新content
                $content = implode("\r\n",$output);
            }
        }
        else if (127===$r)
        {
            throw new Exception(__('Systems perform less processing failed, please check the implementation of the recess command'));
        }
        else
        {
            $err = array
            (
                1 => __('Please check no recess module installed or not set node_modules path'),
            );

            throw new Exception(__('Systems perform less handling failed,RsCode:%s',array('%s'=>$r.'.'.(isset($err[$r])?$err[$r]:''))));
        }
    }


    /**
     * 获取node的设置
     *
     * @return array(exe_file_path,modules_path)
     */
    protected function get_node_set()
    {
        # nodejs 配置
        $node_config = Core::config('core.nodejs');

        # 执行程序
        if (is_array($node_config) && isset($node_config[0]) && $node_config[0])
        {
            $node_file = $node_config[0];
        }
        else
        {
            $node_file = IS_WIN?'c:\\Program Files\\nodejs\\node.exe':'/usr/local/bin/node';
        }

        # node_modules 路径
        if (is_array($node_config) && isset($node_config[1]) && $node_config[1])
        {
            $node_modules_path = $node_config[1];
        }
        else
        {
            $node_modules_path = IS_WIN?'c:\\Program Files\\nodejs\\node_modules\\' : '/usr/local/lib/node_modules/';
        }

        $node_modules_path = explode('/', str_replace('\\', '/', rtrim($node_modules_path,'/\\')));
        $i = count($node_modules_path)-1;
        if ($node_modules_path[$i]=='node_modules')
        {
            # 将node_modules移除
            unset($node_modules_path[$i]);
        }

        # 得到node_modules的父目录
        $node_modules_path = implode(DS, $node_modules_path) . DS;

        if (!is_dir($node_modules_path))
        {
            if (is_dir(DIR_SYSTEM.'node_modules'.DS))
            {
                $node_modules_path = DIR_SYSTEM . 'node_modules' . DS;
            }
            else
            {
                throw new Exception(__('the node_modules directory does not exist, modify the config:ext :config configure',array(':ext'=>EXT,':config'=>'$config[\'nodejs\']')));
            }
        }

        return array($node_file,$node_modules_path);
    }

    /**
     * 获取输出的asset的md5
     *
     * @return array
     */
    protected function get_assets_md5()
    {
        $asset_md5_file = DIR_DATA.'asset_files_md5_by_project_'.$this->project;

        if (is_file($asset_md5_file))
        {
            $array = @unserialize(file_get_contents($asset_md5_file));
        }
        else
        {
            # 读取缓存锁
            $lock = Cache::instance()->get('asset_md5_temp_lock');
            if ($lock)
            {
                for($i=0;$i<5;$i++)
                {
                    # 可能有另外一个进程正在操作，等待一下
                    sleep(1);

                    if (is_file($asset_md5_file))
                    {
                        $array = @unserialize(file_get_contents($asset_md5_file));
                        break;
                    }
                }
            }

            if (!isset($array))
            {
                # 记录缓存锁
                Cache::instance()->set('asset_md5_temp_lock',1,6);

                # 循环获取所有文件列表
                $file_paths = array();

                $include_path = Core::include_path();
                $include_path = array_reverse($include_path);

                # 循环include path
                foreach ($include_path as $path)
                {
                    $dir = $path ."assets/";

                    if (is_dir($dir))
                    {
                        $this->glob_files($file_paths,$dir,strlen($dir));
                    }
                }

                print_r($file_paths);

                # 删除缓存锁
                Cache::instance()->delete('asset_md5_temp_lock');
            }
        }

        return $array;
    }


    /**
     * 递归的读取目录下所有文件到$file_paths中
     */
    function glob_files(&$file_paths,$dir,$dir_len)
    {
        $files = glob( $dir .'*', GLOB_NOSORT );

        if ($files)foreach ($files as $file)
        {
            if ($file==='.'||$file==='..'||substr($file,0,1)==='.')continue;

            # 文件夹
            if (is_dir($file))
            {
                $this->glob_files($file_paths,$file.'/',$dir_len);
                continue;
            }

            # 将文件列表加入到all数组里
            $file_paths['file_md5'][$file] = md5_file($file);

            $file = str_replace('\\', '/', $file);
            $path_rpos = strrpos($file,'/');

            # 文件名
            $file_name = substr($file,$path_rpos+1);
            $file_path = substr($file,$dir_len,-strlen($file_name)).$file_name;

            $rpos = strrpos($file_name,'.');
            if ($rpos>0)
            {
                # 后缀
                $suffix = strtolower(substr($file_name,$rpos+1));

                if ($suffix=='css'||$suffix=='less'||$suffix=='js')
                {
                    # 处理CSS，JS文件
                    $file_name_arr = explode('.',$file_name);
                    $count_file_name_arr = count($file_name_arr);

                    if ($count_file_name_arr>=3)
                    {
                        $type = strtolower($file_name_arr[1]);
                        if ('extend'==$type || 'mod'==$type)
                        {
                            if ('extend'==$type)
                            {
                                if ($count_file_name_arr==3)
                                {
                                    unset($file_name_arr[1]);
                                }
                                else
                                {
                                    $file_name_arr[1] = 'mod';
                                }
                            }
                            else
                            {
                                $file_name_arr = array($file_name_arr[0],$file_name_arr[count($file_name_arr)-1]);
                            }
                            $parent_file_name = implode('.',$file_name_arr);
                            $parent_file_path = substr($file,$dir_len,-strlen($file_name)).$parent_file_name;

                            if ($suffix=='less')
                            {
                                $parent_file_path = substr($file_path,0,-strlen($type)).'.css';
                            }

                            if ('mod'==$type)
                            {
                                $file_paths['modules'][$parent_file_path][substr($file,$dir_len,-strlen($file_name)).$file_name] = $file;
                            }
                            else
                            {
                                $file_paths['extends'][$parent_file_path] = $file;
                            }

                            if ($count_file_name_arr==3 && !$file_paths[$suffix][$parent_file_path])
                            {
                                $file_paths[$suffix][$parent_file_path] = true;
                            }
                            continue;
                        }
                    }

                    $file_paths[$suffix][$file_path] = $file;
                }
                elseif (in_array($suffix,explode('|',$this->allow_suffix)))
                {
                    $file_paths['other'][$file_path] = $file;
                }
            }
        }
    }
}
