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
class Controller_OpenDebugger extends Controller
{

    protected $password = array();

    public function before()
    {
        $this->password = Core::config( 'core.debug_open_password' );
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
        $debug_hash = $_POST['debug_hash'];
        if ( in_array( $debug_hash, $this->password ) )
        {
            Core::cookie()->set( '_debug_open', Core::get_debug_hash( $debug_hash ), null, '/' );
        }
        $this->redirect( '/opendebugger' );
    }

    public function action_logout()
    {
        Core::cookie()->delete( '_debug_open', '/' );
        $this->redirect( '/opendebugger' );
    }

    protected function debug()
    {
        $url = Core::url( '/opendebugger/login' );
        echo <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="zh-CN" lang="zh-CN">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>我要调试</title>
</head>
<body>
<form method="post" action="{$url}" name="form1" id="form1">
<input type="hidden" name="action" value="login" />
认证密码:<input type="password" name="debug_hash" /><input type="submit" name="submit" value="我要调试" />
</form>
<script type="text/javascript">
document.forms['form1']['debug_hash'].focus();
</script>
</body>
</html>
EOF;
    }

    protected function nodebug()
    {
        $url = Core::url( '/opendebugger/logout' );
        echo <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="zh-CN" lang="zh-CN">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>我不想调试</title>
</head>
<body>
<form method="post" action="{$url}" name="form1">
<input type="submit" name="submit" value="我不想调试" />
</form>
</body>
</html>
EOF;
    }
}