<?php
/**
 * 登录，退出控制器
 *
 * @author 呼吸二氧化碳 <jonwang@myqee.com>
 *
 */
abstract class Library_MyQEE_Administration_Controller_Login extends Controller
{
    protected $message = '';

    protected $error_input;

    public function action_index()
    {
        $member = array(
            'message' => '',
            'input'   => '',
        );
        $view = new View('admin/login');

        $not_login = false;
        $show_captcha = false;
        $db = Database::instance(Model_Admin::DATABASE);
        if ( $db->count_records('admin_login_error_log',array('timeline<='=>TIME-86400)) )
        {
            # 清除24小时前的登录错误信息
            $db->where('timeline<=',TIME-86400)->delete('admin_login_error_log');
        }
        $error = $db->from('admin_login_error_log')->where('ip',HttpIO::IP)->limit(1)->get()->current();
        if ( $error )
        {
            $error_num = $error['error_num'];
            $config = Core::config('admin/core');
            if ( $error_num>=$config['login_error_show_captcha_num']-1 )
            {
                $show_captcha = true;
            }
            if ( $config['login_max_error_num'] && $error_num>=$config['login_max_error_num'] )
            {
                $not_login = true;
                $this->message = '尝试次数太多，暂不可登录';
            }
        }

        if ( !$not_login && HttpIO::METHOD=='POST')
        {
            $member = $this->post($_POST,$error_num);
            if ( $member )
            {
                $member->last_login_ip = HttpIO::IP;
                $member->last_login_time = TIME;
                $member->last_login_session_id = $this->session()->id();
                $member->value_increment('login_num');
                $member->update();
                # 开启session
                $this->session()->start();
                $this->session()->set_member($member);
                $url = $_POST['forward'] ? HttpIO::POST('forward',HttpIO::PARAM_TYPE_URL) : Core::url('/');

                $this->redirect( $url );
            }
            else
            {
                $view->shake = true;
            }
        }
        $login_message = $this->session()->get('admin_member_login_message');

        $view->show_captcha = $show_captcha;
        $view->message = $login_message?$login_message:$this->message;
        $view->error_input = $this->error_input;
        if ($_POST)
        {
            $view->username = $_POST['username'];
        }
        $view->render();
    }

    public function action_out()
    {
        $this->session()->start()->destroy();
        $this->redirect(Core::url('login/'));
    }

    /**
     * 处理提交
     *
     * @param array $data
     * @return Member 失败则返回false
     */
    protected function post($data,$error_num)
    {
        if (!$data['username'])
        {
            $this->message = '用户名不能空';
            $this->error_input = 'username';
            return false;
        }
        if (!$data['password'])
        {
            $this->message = '密码不能空';
            $this->error_input = 'password';
            return false;
        }

        $db = Database::instance(Model_Admin::DATABASE);
        try {

            if ( $error_num )
            {
                # 有登录错误
                $config = Core::config('admin/core');
                if ( $error_num>=$config['login_error_show_captcha_num']-1 )
                {
                    if ( Captcha::valid($data['captcha'])<0 )
                    {
                        throw new Exception('验证码错误');
                    }
                }
            }

            $member = Auth::instance('admin')->check_user($data['username'], $data['password']);

            if ( $error_num )
            {
                # 清除登录记录
                $db->delete('admin_login_error_log', array('ip'=>HttpIO::IP));
            }

            $id = (int)$member->id;
            $_POST['password'] = '******';    //日志中隐藏密码项

            if ( $member->project!=Core::$project && !$member->perm()->is_super_perm() )
            {
                throw new Exception('不允许通过此页面登录',-1);
            }

            if ( $member->shielded )
            {
                throw new Exception('您已被屏蔽', -1 );
            }
        }
        catch (Exception $e)
        {
            if ($e->getCode()===0)
            {
                # 验证失败
                $error_num++;
                if ($error_num===1)
                {
                    $db->insert('admin_login_error_log',array(
                    	'ip' => HttpIO::IP,
                        'timeline' => TIME,
                        'error_num' => 1,
                        'last_error_msg' => $e->getMessage(),
                        'last_post_username' => $data['username'],
                    ));
                }
                else
                {
                    $db->update('admin_login_error_log',
                        array(
                            'timeline' => TIME,
                            'error_num+' => 1,
                            'last_error_msg' => $e->getMessage(),
                            'last_post_username' => $data['username'],
                        ),
                        array('ip' => HttpIO::IP,)
                    );
                }
            }
            $this->message = $e->getMessage();
            $this->error_input = 'password';
            $id = 0;
            $member = false;
        }

        # 记录登录日志
        $db->insert( Core::config('admin/log.tablename'),
            array(
                'uri' => $_SERVER["REQUEST_URI"],
                'type' => 'login',
                'ip' => HttpIO::IP,
                'referer' => $_SERVER["HTTP_REFERER"],
                'post' => serialize($_POST),
                'admin_id' => $id,
            )
        );
        return $member;
    }
}