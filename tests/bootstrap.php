<?php

spl_autoload_register(function($class) {
	$ns = 'Vodacek\Form\\';
	if (strpos($class, $ns) === 0) {
		$path = substr($class, strlen($ns));
		$path = str_replace('\\', '/', $path);
		$path = dirname(__DIR__) . '/'. $path . '.php';
		if (file_exists($path)) {
			require $path;
		}
	}
});

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/nette/nette/Nette/loader.php';
