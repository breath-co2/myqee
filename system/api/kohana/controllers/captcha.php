<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Outputs the dynamic Captcha resource.
 * Usage: Call the Captcha controller from a view, e.g.
 *        <img src="<?php echo url::site('captcha') ?>" />
 *
 * $Id: captcha.php,v 1.1 2009/06/30 03:34:33 jonwang Exp $
 *
 * @package    Captcha
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Captcha_Controller extends Controller {

	public function __call($method, $args)
	{
		// Output the Captcha challenge resource (no html)
		// Pull the config group name from the URL
		Captcha::factory($this->uri->segment(2))->render(FALSE);
	}

} // End Captcha_Controller