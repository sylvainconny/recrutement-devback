<?php

define('__ROOT__', __DIR__);
define('PAGE_SIZE', 10);

require_once __ROOT__ . '/lib/Router.class.php';

$router = new Router;
$router->start();
