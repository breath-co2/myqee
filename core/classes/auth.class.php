<?php

/**
 * 验证类
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   MyQEE
 * @package    System
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2016 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Core_Auth
{
    /**
     * 数据库类型
     *
     * @var string
     */
    const DRIVE_DATABASE = 'Database';

    /**
     * 文件类型
     *
     * @var string
     */
    const DRIVE_FILE = 'File';

    /**
     * 默认配置名
     *
     * @var string
     */
    const DEFAULT_CONFIG_NAME = 'default';

    const ERROR_CODE_NO_MEMBER = -1;

    const ERROR_CODE_ERROR_PASSWORD = -2;


    /**
     * 当前配置
     *
     * @var array
     */
    protected $config;

    protected $config_name;

    /**
     * @var array
     */
    protected static $instances = array();

    /**
     * @return Auth
     */
    public static function instance($config_name = null)
    {
        if (null===$config_name)
        {
            $config_name = Auth::DEFAULT_CONFIG_NAME;
        }

        if (is_string($config_name))
        {
            $i_name = $config_name;
        }
        else
        {
            $i_name = '.config_'. md5(serialize($config_name));
        }

        if (!isset(Auth::$instances[$i_name]))
        {
            Auth::$instances[$i_name] = new Auth($config_name);
        }

        return Auth::$instances[$i_name];
    }

    public function __construct($config_name=null)
    {
        if (null===$config_name)
        {
            $config_name = Auth::DEFAULT_CONFIG_NAME;
        }

        $this->config_name = $config_name;
        $this->config      = Core::config('auth.'.$config_name);
    }

    /**
     * 检查用户名密码
     *
     * @param string $username
     * @param string $password
     * @return Member
     * @throws Exception
     */
    public function check_user($username, $password)
    {
        $member = $this->get_member_by_username($username);

        if (!$member)
        {
            throw new Exception(__('The user does not exist'), Auth::ERROR_CODE_NO_MEMBER);
        }

        if ($member->check_password($password))
        {
            return $member;
        }
        else
        {
            throw new Exception(__('Enter the wrong password'), Auth::ERROR_CODE_ERROR_PASSWORD);
        }
    }

    /**
     * 根据用户名获取用户
     *
     * @param string $username
     * @return Member 用户对象，不存在则返回false
     */
    public function get_member_by_username($username)
    {
        if (!$this->config['drive'] || $this->config['drive'] === Auth::DRIVE_DATABASE)
        {
            # 数据库类型
            $tables         = $this->config['tablename'];
            $user_field     = $this->config['username_field']?$this->config['username_field']:'username';
            $data           = Database::instance($this->config['database'])->from($tables)->where($user_field, $username)->limit(1)->get()->current();
        }
        elseif ($this->config['drive'] === Auth::DRIVE_FILE)
        {
            $file = DIR_DATA . 'auth-data-of-project-' . Core::$project . '.json';
            if (is_file($file))
            {
                $data = @json_decode(file_get_contents($file), true);
                if ($data && isset($data[$username]))
                {
                    $data = $data[$username];
                }
                else
                {
                    $data = array();
                }
            }
            else
            {
                $data = array();
            }
        }
        else
        {
            $data = null;
        }

        if ($data)
        {
            $member_obj = $this->config['member_object_name']?$this->config['member_object_name']:'Member';
            return new $member_obj($data);
        }
        else
        {
            return false;
        }
    }
}