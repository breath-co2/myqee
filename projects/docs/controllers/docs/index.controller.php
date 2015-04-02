<?php
/**
 * API控制器
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   MyQEE
 * @package    Docs
 * @subpackage Core
 * @copyright  Copyright (c) 2008-2016 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Controller_Docs_Index extends Controller_Docs_Base
{
    /**
     * 首页
     */
    public function action_default()
    {
        $this->view->lefthtml = $this->execute( 'docs/index/left_menu', false );
        $this->execute( 'docs/index/center_guide' );
    }

    /**
     * 首页右侧项目列表
     */
    public function sub_action_project_list()
    {
        $view = new View( 'docs/api/project_list' );
        $view->render(true);
    }

    /**
     * 首页右侧项目列表
     */
    public function sub_action_left_menu()
    {
        $view = new View( 'docs/menu' );
        $view->menu = $this->file( 'menu' );
        $view->render(true);
    }

    public function sub_action_center_guide()
    {
        $this->md_file or $this->md_file = 'base.index';
        View::factory( 'docs/guide' )->set( 'file', $this->file( $this->md_file ) )->render();
    }

    /**
     * 列表
     */
    public function action_list()
    {

    }

    public function file( $page )
    {
        $file = Core::find_file( 'guide', strtolower( $page ), 'md' );
        return $file;
    }

	public function action_trace()
	{
	    if ( IS_DEBUG )
	    {
	        Core::trace('测试Core::trace()');
	    }
	    else
	    {
	        # 测试用
	        View::factory('docs/trace')->render();
	        exit;
	    }
	}
}