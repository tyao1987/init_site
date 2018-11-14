<?php

$configProduction = require ROOT_PATH . '/module/Admin/config/config.production.php';

$config =  array (
    'cmsHost' => 'admin.test.com',
);

return array_merge($configProduction,$config);
