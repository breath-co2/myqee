<?php

/**
 * 静态文件输出
 *
 * @author 呼吸二氧化碳 <jonwang@myqee.com>
 *
 */
class Library_MyQEE_Develop_Controller_Assets_Dev extends Controller
{
    /**
     * assets允许的文件后缀名
     *
     * 可在config.php中修改 `$config['asset_allow_suffix']` 值
     *
     * @var string
     */
    public $allow_suffix = 'js|css|jpg|jpeg|png|gif|bmp|json|pdf|tpl|html|htm|mp4|swf|eot|svg|ttf|woff|map';

    /**
     * 文件名
     *
     * @var string
     */
    protected $file;

    /**
     * 当前文件是否.min后缀
     *
     * @var boolean
     */
    protected $is_min = false;

    public function before()
    {
        # 只允许本地调试模式下使用
        if (!(IS_DEBUG & 1))Core::show_404(__('Only allows the development mode'));

        $arguments = $this->arguments;
        $f = array_pop($arguments);
        if ($f)
        {
            $args       = $arguments;
            $args[]     = $f;
            $this->file = implode('/', $args);

            # 移除.min的后缀
            if (substr($this->file, -4)=='.min')
            {
                $this->file   = substr($this->file, 0, -4);
                $this->is_min = true;
            }
        }
    }

    /**
     * 实时动态输出assets文件
     *
     * @throws Exception
     */
    public function action_default()
    {
        if (!$this->file)
        {
            Core::show_404();
        }

        if (!preg_match('#^([a-zA-Z0-9_/\-\.]+)$#', $this->file))
        {
            Core::show_404(__('Special characters of the file exists allowed.'));
        }

        if (!$this->suffix || !preg_match('#('.$this->allow_suffix.')$#i', $this->suffix))
        {
            Core::show_404(__('File suffix %s not allow.', array('%s'=>$this->suffix)));
        }

        if ($this->suffix === 'css' || $this->suffix === 'js')
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
        if (IS_DEBUG)header_remove();

        Core::close_buffers(false);
        HttpIO::set_cache_header(86400);
    }

    /**
     * 根据文件名直接输出
     *
     * @param string $file
     */
    protected function output_by_file($file)
    {
        $rpos = strrpos($file, '.');
        $type = substr($file, $rpos);
        if (!$type || !preg_match('#('.$this->allow_suffix.')$#i', $type))
        {
            Core::show_500(__('File suffix %s not allow.', array('%s'=>$type)));
        }

        $this->send_header();


        # 输出目录
        $out_dir = DIR_ASSETS .'p-'. Core::$project . (IS_ADMIN_MODE?DS.'~admin':'') . DS;

        # 输出文件
        $out_file = $out_dir . $this->file . ($this->is_min?'.min':'') . '.' . $this->suffix;

        if (is_file($out_file))
        {
            if (filemtime($out_file)!=filemtime($file))
            {
                copy($file, $out_file);
                @touch($out_file, filemtime($file));
            }
        }
        else
        {
            if (File::create_dir(dirname($out_file)))
            {
                copy($file, $out_file);
                @touch($out_file, filemtime($file));
            }
        }

        $fun = 'apache_get_modules';
        if (function_exists($fun))
        {
            if (in_array('mod_xsendfile', $fun()))
            {
                $root_dir = $_SERVER["DOCUMENT_ROOT"];
                $root_len = strlen($root_dir);

                if (IS_WIN)
                {
                    $root_dir = str_replace('\\', '/', $root_dir);
                    $file     = str_replace('\\', '/', $file);
                }
                if (substr($file, 0, $root_len)==$root_dir)
                {
                    # 采用xsendfile发送文件
                    header('X-Sendfile: '.substr($file, $root_len));
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
        $file_paths = $this->get_css_or_js_files_array($this->file, $this->suffix);

        # 输出目录
        $out_dir = DIR_ASSETS . 'p-'. Core::$project . (IS_ADMIN_MODE?DS.'~admin':'') . DS;

        # 输出文件
        $out_file = $out_dir . $this->file . ($this->is_min?'.min':'') .'.'. $this->suffix;

        # md5存放的文件
        $cache_file = DIR_DATA . 'cache/asset_files_md5_' . Core::$project . (IS_ADMIN_MODE?'~admin':'') .'_'. str_replace('/', '~', $this->file) . ($this->is_min?'.min':'') . '.'. $this->suffix . '.serialize';

        if (is_file($cache_file))
        {
            $asset_files_md5 = (array)unserialize(file_get_contents($cache_file));
        }
        else
        {
            $asset_files_md5 = array();
        }

        if (is_file($out_file))
        {
            $changed = false;

            if ($asset_files_md5)
            {
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
                if (current($file_paths['file_md5'])!=md5_file($out_file))
                {
                    $changed = true;
                }
            }
        }
        else
        {
            $changed = true;
        }

        if ($changed)
        {
            $content = '';

            if (isset($file_paths['file']) && $file_paths['file'])foreach ($file_paths['file'] as $full_path)
            {
                $content .= file_get_contents($full_path);
            }

            if (isset($file_paths['main']) && $file_paths['main'])foreach ($file_paths['main'] as $file=>$full_path)
            {
                # 内容
                if (true!==$full_path)
                {
                    $content .= file_get_contents($full_path);
                }

                # 当前文件的扩展
                if (isset($file_paths['extends'][$file]) && $file_paths['extends'][$file])
                {
                    $content .= CRLF.file_get_contents($file_paths['extends'][$file]);
                }

                # 加入模块
                if (isset($file_paths['modules'][$file]) && $file_paths['modules'][$file])
                {
                    foreach ($file_paths['modules'][$file] as $file2 => $full_path2)
                    {
                        $content .= CRLF.file_get_contents($full_path2);

                        # 模块文件的扩展文件
                        if (isset($file_paths['extends'][$file2]) && $file_paths['extends'][$file2])
                        {
                            $content .= CRLF.file_get_contents($file_paths['extends'][$file2]);
                        }
                    }
                }
            }

            if ($this->suffix === 'css')
            {
                $this->add_css_image_version($content);

                if (isset($file_paths['prease_css']) && $file_paths['prease_css'])
                {
                    # 处理LESS,和SCSS
                    $this->prease_css($out_file, $file_paths['prease_css'], $content);
                }
            }

            # 创建文件夹
            File::create_dir(dirname($out_file));

            # 保存文件
            if (File::create_file($out_file, $content))
            {
                # 写入文件
                foreach ($file_paths['file_md5'] as $full_path => $md5)
                {
                    $debug_path = Core::debug_path($full_path);
                    $asset_files_md5[$debug_path] = $md5;
                }

                # 排序2次的用途是保证一些特殊情况下排序能得到纠正，比如有2个key分别是 test1.js 和 test.js 在排序时会有bug
                asort($asset_files_md5);
                asort($asset_files_md5);

                $old_md5_content = serialize($asset_files_md5);
                if (!is_file($cache_file) || md5($old_md5_content)!=md5_file($cache_file))
                {
                    # 保存MD5列表
                    File::create_file($cache_file, $old_md5_content);
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
        $file_name  = $this->file . ($this->is_min?'.min':'');
        $found_file = Core::find_file('assets', $file_name, '.'. $this->suffix);

        if ($found_file)
        {
            $this->output_by_file($found_file);
        }
        else
        {
            $this->get_assets_dir_file();
        }
    }

    /**
     * 获取CSS或JS文件的整理后的数组
     *
     * @return array | boolean
     */
    protected function get_css_or_js_files_array($the_file, $the_suffix)
    {
        $include_path = Core::include_path();
        $include_path = array_reverse($include_path);

        # 记录整理后的数据
        $file_paths = array();

        # 记录所有相关文件
        $all_files = array();
        # 搜索所有相关文件
        foreach ($include_path as $path)
        {
            $path_len = strlen($path . 'assets' . DS);
            $glob_file = $path .'assets'. DS .$the_file .'.*'. ($the_suffix=='css'?'':$the_suffix);
            $files = glob($glob_file, GLOB_NOSORT);

            if ($files)foreach($files as $tmpfile)
            {
                $filename = str_replace('\\', '/', substr($tmpfile, $path_len));

                if ($the_suffix=='css')
                {
                    $tmptype = strtolower(substr($tmpfile, -5));
                    if (strtolower(substr($tmpfile, -4))!='.css' && $tmptype!='.less' && $tmptype!='.scss' && $tmptype!='.sass')
                    {
                        continue;
                    }

                    # 将less后缀转处理为.css
                    if ($tmptype=='.less' || $tmptype=='.scss' || $tmptype=='.sass')
                    {
                        $filename = substr($filename, 0, -5).'.css';

                        $file_paths['prease_css'] = $tmptype;
                    }

                }

                $all_files[$filename] = $tmpfile;
            }
        }

        if (!$all_files)
        {
            $this->get_assets_dir_file();
        }

        foreach ($all_files as $file=>$fullpath)
        {
            $path_rpos = strrpos($file, '/');
            $file_name = substr($file, $path_rpos+1);
            $rpos      = strrpos($file_name, '.');
            $suffix    = strtolower(substr($file_name, $rpos+1));

            if ($suffix=='css'||$suffix=='js')
            {
                # 将文件列表加入到all数组里
                $file_paths['file_md5'][$fullpath] = md5_file($fullpath);

                # 处理CSS，JS文件
                $file_name_arr       = explode('.', $file_name);
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

                        $parent_file_name = implode('.', $file_name_arr);
                        $parent_file_path = substr($file,0, -strlen($file_name)).$parent_file_name;

                        if ('mod'==$type)
                        {
                            $file_paths['modules'][$parent_file_path][substr($file,0, -strlen($file_name)).$file_name] = $fullpath;
                        }
                        else
                        {
                            $file_paths['extends'][$parent_file_path] = $fullpath;
                        }

                        if ($count_file_name_arr==3)
                        {
                            if (!isset($file_paths['main'][$parent_file_path]))
                            {
                                $file_paths['main'][$parent_file_path] = true;
                            }
                        }
                        else
                        {
                            $file_paths['file'][$parent_file_path] = $fullpath;
                        }
                    }
                    else
                    {
                        $file_paths['file'][$file] = $fullpath;
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
     * 当项目类库中都没有找到文件时，尝试直接在assets目录里寻找文件并直接输出
     */
    protected function get_assets_dir_file()
    {
        $file_name   = $this->file . ($this->is_min?'.min':'');
        $assets_file = DIR_ASSETS . $file_name. '.'. $this->suffix;

        if (is_file($assets_file))
        {
            $this->output_by_file($assets_file);
            exit;
        }
        else
        {
            Core::show_404(__('Assets files : %s not found.', array('%s'=>$file_name .'.'. $this->suffix)));
        }
    }

    /**
     * 增加CSS文件版本号信息
     *
     * @param string $content css文件内容
     */
    protected function add_css_image_version(&$content)
    {
        # 拿到css里所有背景图片
        if (preg_match_all('#url\((?:\'|")?([^\'"]*)(?:\'|")?\)#Uis', $content, $match))
        {
            $file_path_arr = explode('/', $this->file);
            array_pop($file_path_arr);                      // 移除文件名

            # 去重
            $all_urls = array_unique($match[1]);

            foreach ($all_urls as $img)
            {
                if (strpos($img, '://')!==false)continue;
                $tmp_file_path_arr = $file_path_arr;

                # 去掉?后面的东西
                list($format_path, $query) = explode('?', $img .'?');
                $img_dir = explode('/', $format_path);

                foreach ($img_dir as $k=>$dir)
                {
                    if ($dir=='.')
                    {
                        unset($img_dir[$k]);
                    }
                    else if ($dir=='..')
                    {
                        array_pop($tmp_file_path_arr);
                        unset($img_dir[$k]);
                    }
                    else
                    {
                        break;
                    }
                }

                # 拼装出最后的结果
                $format_path = implode('/', $tmp_file_path_arr) .'/'. implode('/', $img_dir);
                echo $format_path;

                if (isset($file_md5[$format_path]))
                {
                    if ($query)
                    {
                        $img2 = $img .'&v='. $file_md5[$format_path];
                    }
                    else
                    {
                        $img2 = $img .'?v='. $file_md5[$format_path];
                    }

                    $content = str_replace($img, $img2, $content);
                }
            }
        }
    }

    /**
     * 处理解析LESS文件
     *
     * @throws Exception
     */
    protected function prease_css($out_file, $type, &$content)
    {
        # 通过recess处理less文件
        $tmpfile = DIR_TEMP . 'tmpless_'.md5($this->file) . $type;

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
            if (strpos($e->getMessage(), 'Permission denied')!==false)
            {
                Core::show_500(__('Permission denied : :file', array(':file'=>Core::debug_path($tmpfile))));
            }
            throw $e;
        }

        list($node_file, $node_modules_path) = $this->get_node_set();

        static $assets_path = null;
        if (null===$assets_path)
        {

            $include_path = Core::include_path();
            $include_path = array_reverse($include_path);

            # assets目录,经过 escapeshellarg 处理，用于sass，less处理时增加包含目录参数
            $assets_path = array();

            $tmp_dir = dirname($this->file);
            if ($tmp_dir && $tmp_dir!='.')
            {
                $tmp_dir .= '/';
            }
            else
            {
                $tmp_dir = '';
            }

            # 循环include path
            foreach ($include_path as $path)
            {
                $dir = $path ."assets/" . $tmp_dir;
                if (is_dir($dir))
                {
                    $assets_path[] = escapeshellarg($dir);
                }
            }
        }

        if ($type=='less')
        {
            if ($assets_path)$path_str = ' --includePath=' . implode(' --includePath=', $assets_path);
            $cmd = 'cd '.(escapeshellcmd($node_modules_path)).' && ' . escapeshellcmd($node_file).' '.escapeshellarg('./node_modules/recess/bin/recess').' --compile'.$path_str.' '.escapeshellarg($tmpfile);
        }
        else
        {
            if ($assets_path)$path_str = ' --load-path=' . implode(' --load-path=', $assets_path);
            $cmd = 'sass -t expanded'.$path_str.' '.escapeshellarg($tmpfile);
        }


        if (IS_DEBUG)Core::debug()->info($cmd, 'exec');

        # 执行
        exec($cmd, $output, $r);

        # 删除临时文件
        @unlink($tmpfile);

        if (0===$r)
        {
            if ($output)
            {
                # 更新content
                $content = implode("\r\n", $output);
            }
        }
        else if (127===$r)
        {
            throw new Exception(__('Systems perform less processing failed, please check the implementation of the recess command'));
        }
        else if (1===$r)
        {
            throw new Exception(__('Systems perform less processing failed, please check the file. cmd: %c, output: %s', array('%c' => $cmd, '%s' => implode("\n", $output))));
        }
        else
        {
            $err = array
            (
                1 => __('Please check no recess module installed or not set node_modules path'),
            );

            throw new Exception(__('Systems perform less handling failed,RsCode:%s', array('%s'=>$r.'.'.(isset($err[$r])?$err[$r]:''))));
        }
    }


    /**
     * 获取node的设置
     *
     * @return array(exe_file_path,modules_path)
     */
    protected function get_node_set()
    {
        # 读取nodejs配置
        $node_config_file = DIR_BIN . 'config.ini.php';

        if (is_file($node_config_file))
        {
            $config = array();
            include $node_config_file;
            $node_config = $config['nodejs'];
            unset($config);
        }
        else
        {
            $node_config = null;
        }

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
                throw new Exception(__('the node_modules directory does not exist, modify the config:ext :config configure', array(':ext'=>EXT, ':config'=>'$config[\'nodejs\']')));
            }
        }

        return array($node_file, $node_modules_path);
    }

    /**
     * 递归的读取目录下所有文件到$file_paths中
     */
    function glob_files(&$file_paths, $dir, $dir_len)
    {
        $files = glob($dir .'*', GLOB_NOSORT);

        if ($files)foreach ($files as $file)
        {
            if ($file==='.' || $file==='..' || substr($file, 0, 1)==='.')continue;

            # 文件夹
            if (is_dir($file))
            {
                $this->glob_files($file_paths, $file.'/', $dir_len);
                continue;
            }

            # 将文件列表加入到all数组里
            $file_paths['file_md5'][$file] = md5_file($file);

            $file = str_replace('\\', '/', $file);
            $path_rpos = strrpos($file, '/');

            # 文件名
            $file_name = substr($file, $path_rpos+1);
            $file_path = substr($file, $dir_len, -strlen($file_name)) . $file_name;

            $rpos = strrpos($file_name, '.');
            if ($rpos>0)
            {
                # 后缀
                $suffix = strtolower(substr($file_name, $rpos+1));

                if ($suffix=='css' || $suffix=='less' || $suffix=='js')
                {
                    # 处理CSS，JS文件
                    $file_name_arr = explode('.', $file_name);
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
                            $parent_file_name = implode('.', $file_name_arr);
                            $parent_file_path = substr($file, $dir_len, -strlen($file_name)) . $parent_file_name;

                            if ($suffix=='less')
                            {
                                $parent_file_path = substr($file_path, 0, -strlen($type)) .'.css';
                            }

                            if ('mod'==$type)
                            {
                                $file_paths['modules'][$parent_file_path][substr($file, $dir_len ,-strlen($file_name)).$file_name] = $file;
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
                elseif (in_array($suffix, explode('|', $this->allow_suffix)))
                {
                    $file_paths['other'][$file_path] = $file;
                }
            }
        }
    }
}
