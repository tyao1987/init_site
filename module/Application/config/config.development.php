<?php
$configProduction = require ROOT_PATH . '/module/Application/config/config.production.php';

$config = array(
	'writableDir' => array(
		'base'            => '',
		'dataCache'       => ROOT_PATH . '/data/data-cache/',
		'log'             => '/var/log/www/test/',
		'styles'          => ROOT_PATH . '/public/styles/',
	),
	'imageServer'            => 'http://www.test.com/images/',
);
return array_merge($configProduction,$config);

