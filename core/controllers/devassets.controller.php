<?php

/**
 * 静态文件输出
 *
 * @author jonwang
 *
 */
class Controller_DevAssets extends Controller
{
    protected $allow_suffix = 'js|css|jpg|png|gif|bmp|html|htm|mp4|swf|zip';

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
        if ($this->action!='default')
        {
            $this->project = array_shift($this->arguments);
        }
        $arguments = $this->arguments;
        $f = array_pop($arguments);
        if ( $f && preg_match('#^([a-zA-Z0-9_/\-\.]+).('.$this->allow_suffix.')$#i', $f,$m) )
        {
            $args       = $arguments;
            $args[]     = $m[1];
            $this->file = implode('/', $args);
            $this->type = $m[2];
        }
    }

    public function action_default()
    {
        $file    = $this->file;
        $type    = $this->type;
        $project = $this->project;

        # 设置项目
        Core::set_project($project);

        if ( !preg_match('#^([a-zA-Z0-9_/\-\.]+)$#', $file) || !preg_match('#('.$this->allow_suffix.')$#', $type) )
        {
            Core::show_404();
        }
        $low_type = strtolower($type);

        $found_file = Core::find_file('assets', $file, '.'.$type);

        # 扩展文件
        $file_extend = Core::find_file('assets', $file.'.extend', '.'.$type);

        $file_less = null;
        if ($type=='css')
        {
            # 寻找LESS后缀的文件
            $file_less = Core::find_file('assets', $file, '.less');
            if ($file_less)
            {
                # 如果有less文件则使用less
                $found_file = $file_less;
            }

            # 扩展文件
            $file_less_extend = Core::find_file('assets', $file.'.extend', '.less');
            if ($file_less_extend)
            {
                $file_extend = $file_less_extend;
            }
        }

        if ($found_file)
        {
            Core::close_buffers(false);

            # 清理所有已输出的header
            if ( version_compare(PHP_VERSION,'5.3','>=') )
            {
                $fun = 'header_remove';
                $fun();
            }

            if ( $low_type == 'jpg' )
            {
                header( 'Content-Type: image/jpeg' );
            }
            elseif ( in_array( $type, array('gif','png') ) )
            {
                header( 'Content-Type: image/' . $type );
            }
            elseif ( $type == 'css' )
            {
                header( 'Content-Type: text/css' );
            }
            elseif ( $type == 'js' )
            {
                header( 'Content-Type: application/x-javascript' );
            }
            elseif ( $type == 'swf' )
            {
                header( 'Content-Type: application/swf' );
            }

            header( 'Cache-Control: max-age=3600' );
            header( 'Expires: ' . date('D, d M Y H:i:s \G\M\T', TIME + 3600) );
            header( 'Pragma: cache');

            $fun = 'apache_get_modules';
            if (!$file_less && !$file_extend && function_exists($fun))
            {
                if (in_array('mod_xsendfile',$fun()))
                {
                    $slen = strlen(DIR_SYSTEM);
                    if (substr($found_file,0,$slen)==DIR_SYSTEM)
                    {
                        # 采用xsendfile发送文件
                        header('X-Sendfile: '.substr($found_file,$slen));
                        exit();
                    }
                }
            }
            else
            {
                header('Last-Modified: '.date('D, d M Y H:i:s \G\M\T', filemtime($found_file)));
            }

            # 需要合并文件
            if ($file_less || $file_extend)
            {
                $content = file_get_contents($found_file);

                if ($file_extend)
                {
                    $content .= CRLF . file_get_contents($file_extend);
                }

                if ($file_less)
                {
                    # 通过recess处理less文件
                    $tmpfile = DIR_TEMP.'css_'.md5($found_file.'_'.$file_extend).'.less';

                    if (!is_file($tmpfile) || !is_file($tmpfile.'.css') || md5_file($tmpfile)!=md5($content))
                    {
                        if (!function_exists('exec'))
                        {
                            throw new Exception('您的系统不允许执行exec函数，无法调用node处理less文件');
                        }

                        file_put_contents($tmpfile, $content);

                        list($node_file,$node_modules_path) = $this->get_node_set();

                        $cmd = 'cd '.(escapeshellcmd($node_modules_path)).' && ' . escapeshellcmd($node_file).' '.escapeshellarg('./node_modules/recess/bin/recess').' --compile '.escapeshellarg($tmpfile);

                        if (IS_DEBUG)Core::debug()->info($cmd,'exec');

                        exec($cmd,$output,$r);

                        if (0===$r)
                        {
                            # 写入文件
                            file_put_contents($tmpfile.'.css',implode("\r\n",$output));
                        }
                        else if (127===$r)
                        {
                            throw new Exception('系统执行less处理失败，请检查执行recess命令');
                        }
                        else
                        {
                            $err = array
                            (
                                1 => '请检查是不是没安装recess模块或没有设定对node_modules路径',
                            );

                            throw new Exception('系统执行less处理失败,RsCode:'.$r.'.'.(isset($err[$r])?$err[$r]:''));
                        }
                    }

                    # 输出文件
                    readfile($tmpfile.'.css');
                }
                else
                {
                    # 直接输出
                    echo $content;
                }
            }
            else
            {
                # 直接读取文件输出
                readfile($found_file);
            }
            exit();
        }
        else
        {
            Core::show_404(__('Assets files : %s not found.',array('%s'=>$this->file.'.'.$this->type)));
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
                throw new Exception('node_modules目录不存在，请修改config'.EXT.'中$config[\'nodejs\']中配置');
            }
        }

        return array($node_file,$node_modules_path);
    }
}
