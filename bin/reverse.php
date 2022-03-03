<?php

include __DIR__.'/../src/KsProxyReverse.php';
$config = include __DIR__.'/../cfg/config.php';
$server = new KsProxyReverse();
$server->configure($config)->start();