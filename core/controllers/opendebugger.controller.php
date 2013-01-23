<?php

/**
 * Controller_OpenDebugger.
 *
 * @package     Core
 * @subpackage  Controller
 * @author      VeryCD Dev Team
 * @copyright   Copyright (c) 2003 - 2010, VeryCD, Inc.
 * @license     http://www.verycd.com/license.html
 * @link        http://www.verycd.com/
 * @since       Version 4.0
 */
class Core_Controller_OpenDebugger extends Controller
{

    protected $password = array();

    public function before()
    {
        $this->password = Core::config('core.debug_open_password');

        if (!is_array($this->password) || !$this->password)
        {
            Core::show_404();
        }
    }

    public function action_default()
    {
        if ( Core::is_online_debug() )
        {
            $this->nodebug();
        }
        else
        {
            $this->debug();
        }
    }

    public function action_login()
    {
        $debug_user = $_POST['debug_user'];
        $debug_hash = $_POST['debug_hash'];
        if ( isset($this->password[$debug_user]) && $this->password[$debug_user]==$debug_hash )
        {
            Core::cookie()->set( '_debug_open', Core::get_debug_hash( $debug_user , $debug_hash ), null, '/' );

            if ( isset($_POST['forward']) && $_POST['forward'] )
            {
                $this->redirect( HttpIO::POST('forward',HttpIO::PARAM_TYPE_URL) );
            }
            else
            {
                $this->redirect('/opendebugger');
            }
        }
        else
        {
            $this->redirect( '/opendebugger' . ( isset($_POST['forward']) && $_POST['forward']?'?forward='.urlencode(HttpIO::POST('forward',HttpIO::PARAM_TYPE_URL)):'') );
        }
    }

    public function action_logout()
    {
        Core::cookie()->delete('_debug_open','/');
        $this->redirect( '/opendebugger' );
    }

    protected function debug()
    {
        $url = Core::url( '/opendebugger/login' );
        $str = '';
        if (isset($_GET['forward']) && $_GET['forward'])
        {
            $str = Form::hidden('forward',$_GET['forward']);
        }

        $view = new View('opendebugger');
        $view->str  = $str;
        $view->url  = $url;
        $view->open = true;
        $view->render();
    }

    protected function nodebug()
    {
        $url = Core::url('/opendebugger/logout');

        $str = '';
        if (isset($_GET['forward']) && $_GET['forward'])
        {
            $forward = HttpIO::GET('forward',HttpIO::PARAM_TYPE_URL);
            $str = '<a href="'.$forward.'">'.$forward.'</a>';
        }

        $view = new View('opendebugger');
        $view->str  = $str;
        $view->url  = $url;
        $view->open = false;
        $view->render();
    }
}