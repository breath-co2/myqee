<?php defined('MYQEEPATH') or die('No direct script access.');
/**
 * block library.
 *
 * $Id: Block.php,v 1.1 2009/09/11 07:53:45 jonwang Exp $
 *
 * @package    Core
 * @author     Myqee Team
 * @copyright  (c) 2007-2008 Myqee Team
 * @license    http://myqee.com/license.html
 */
class Block_Core {
	public static function render($num=1)
	{
		if ($_GET['_editblock']=='yes'){
			echo 'asdfsad';
		}
		echo 'as';
	}

} // End session Class
