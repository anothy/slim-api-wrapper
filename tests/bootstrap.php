<?php

// Bootstrap for PHPUnit

// Load Composer's autoloader.
$dir = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR;

/** @var \Composer\Autoload\ClassLoader $autoloader */
$autoloader = require $dir . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

// Register test classes
$autoloader->addPsr4('Anothy\\SlimApiWrapper\\Tests\\', __DIR__);
