<?php
/**
 * 运行各项目后台控制器
 *
 * @author jonwang
 *
 */
abstract class Library_MyQEE_Administration_Controller_Admin_P extends Controller
{
    /**
     * 项目管理统一进入接口
     *
     * 此控制器需要路由设置，路由会直接将project和uri传入到控制器的$this->project和$this->uri里
     *
     * 路由如下：
    'project' => array(
		# 匹配的URL
		'uri' => '/p/<project>(/<uri>)',
		# 匹配正则
		'preg' => array(
			'project' => '[a-zA-Z0-9_]+',
     *',
		),
		# 默认值
		'defalut' => array(
			'controller' => 'Admin_P',
			'action'     => 'default',
		),
	),
     */
    public function action_default()
    {
        if ( !$this->project )
        {
            Core::show_404( '请指定项目' );
        }

        # 切换到指定项目
        Core::set_project( $this->project );

        # 执行项目的后台
        HttpIO::execute( ltrim( $this->uri, '/' ) );
    }
}