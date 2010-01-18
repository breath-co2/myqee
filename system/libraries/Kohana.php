<?php
/**
 * This file acts as the "front controller" to your application. You can
 * configure your application, modules, and system directories here.
 * PHP error_reporting level may also be changed.
 *
 * @see http://kohanaphp.com
 */

/**
 * Define the website environment status. When this flag is set to TRUE, some
 * module demonstration controllers will result in 404 errors. For more information
 * about this option, read the documentation about deploying Kohana.
 *
 * @see http://docs.kohanaphp.com/installation/deployment
 */
define('IN_PRODUCTION', FALSE);

/**
 * Turning off display_errors will effectively disable Kohana error display
 * and logging. You can turn off Kohana errors in application/config/config.php
 */
ini_set('display_errors', TRUE);

//
// DO NOT EDIT BELOW THIS LINE, UNLESS YOU FULLY UNDERSTAND THE IMPLICATIONS.
// ----------------------------------------------------------------------------
// $Id$
//

$kohana_pathinfo = pathinfo($_SERVER['SCRIPT_FILENAME']);

// Define the front controller name and docroot
define('DOCROOT', $kohana_pathinfo['dirname'].DIRECTORY_SEPARATOR);
define('KOHANA',  $kohana_pathinfo['basename']);

// If the front controller is a symlink, change to the real docroot
is_link(KOHANA) and chdir(dirname(realpath($_SERVER['SCRIPT_FILENAME'])));


// Define application and system paths
define('APPPATH', MYAPPPATH);
define('MODPATH', MODULEPATH);
define('SYSPATH', str_replace('\\', '/', realpath(MYQEEPATH.'api/kohana/')).'/');



define('KOHANA_VERSION',  '2.3.4');
define('KOHANA_CODENAME', 'buteo regalis');

// Test of Kohana is running in Windows
define('KOHANA_IS_WIN', DIRECTORY_SEPARATOR === '\\');

// Kohana benchmarks are prefixed to prevent collisions
define('SYSTEM_BENCHMARK', 'system_benchmark');


// Load core files
require SYSPATH.'core/utf8'.EXT;
require SYSPATH.'core/Kohana'.EXT;



// Prepare the environment
Kohana::setup();