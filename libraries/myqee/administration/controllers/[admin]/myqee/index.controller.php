<?php
/**
 * 首页控制器
 *
 * @author jonwang
 *
 */
class Controller_MyQEE__Index extends Controller_Admin
{

    /**
     * 管理页面首页控制器
     */
    public function action_default()
    {
        $this->page_title = '欢迎';
        $view = new View('admin/index');
        $view->render(true);
    }

    /**
     * PHPINFO
     */
    public function action_phpinfo()
    {
        $view = new View( 'admin/phpinfo' );
        $view->render( true );
    }

    public function action_admin_menu()
    {
        $admin_menu = Core::config('admin/menu/'.$this->session()->member()->get_menu_config(),$this->project?$this->project:null);
        $this->header_check_perm($admin_menu);

        # 输出js头信息
        header( 'Content-Type: application/x-javascript' );
        # 输出缓存信息
        header( 'Cache-Control: max-age=604800' );
        header( 'Last-Modified: ' . date( 'D, d M Y H:i:s \G\M\T' ) );
        header( 'Expires: ' . date('D, d M Y H:i:s \G\M\T', TIME + 86400) );
        header( 'Pragma: cache');
        echo 'var _myqee_admin_menu = ',json_encode($admin_menu).';';
        echo 'change_menu(myqee_top_menu,null,myqee_menu);';
        exit;
    }

    /**
     * 输出头尾等视图供其它程序加载显示
     *
     * 需要通过$_POST传过来一些数据
     */
    public function action_view_api()
    {
        $view = new View( 'admin/header' );
        $view_data = $_POST['data'];
        if ( $view_data['title'] )
        {
            $view->page_title = $view_data['title'];
        }
        if ( $view_data['menu'] )
        {
            $menu = explode( '.', $view_data['menu'] );
            $view->menu = $menu;
        }
        $data = array();
        $data['header'] = $view->render( false );
        $view = new View( 'admin/bottom' );
        $data['bottom'] = $view->render( false );

        echo json_encode( $data );
        exit();
    }

    /**
     * 保存用户note
     */
    public function action_notepad()
    {
        if (null===$this->session()->member()->notepad)$this->session()->member()->notepad='';
        $this->session()->member()->notepad = HttpIO::POST('data');
        try {
            $s = $this->session()->member()->update();
            if ($s)
            {
                $this->message('保存成功',1);
            }
            else {
                $this->message('操作成功',0);
            }
        }
        catch (Exception $e)
        {
            $this->message('保存数据失败。',-1);
        }
    }
}