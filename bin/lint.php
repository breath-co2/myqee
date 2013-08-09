#!/usr/bin/env php
<?php

class Lint {
	const ANSI_RED = "\033[31m";
	const ANSI_GREEN = "\033[32m";
	const ANSI_RESET = "\033[0m";
	const ANSI_HIGHLIGHT = "\033[1m";
	
	function check($path) {

		if (is_file($path)) {
			if (preg_match('/\.(php|phtml)$/', $path)) {
				exec('php -l '.escapeshellarg($path), $output, $ret);
				$output = implode("\n", $output);
				if (preg_match('/^No syntax errors/', $output)) {
					echo '.';
				}
				else {
					echo "\n";
					echo self::ANSI_RED;
					echo $output."\n";
					echo self::ANSI_RESET;
				}
			}
		}
		elseif (is_dir($path)) {
			$dh = opendir($path);
			if ($dh) {
				while($name = readdir($dh)) {
					if ($name[0] == '.') continue;
					self::check($path.'/'.$name);
				}
				closedir($dh);
			}
		}

	}

}

if (!isset($argv[1])) {
    die("Usage: php lint.php file_path\n");
}
Lint::check($argv[1]);
echo "\n";
