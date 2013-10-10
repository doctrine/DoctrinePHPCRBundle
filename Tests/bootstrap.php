<?php

$vendorDir = realpath(__DIR__.'/../vendor');

if (!$loader = include $vendorDir.'/autoload.php') {
    $nl = PHP_SAPI === 'cli' ? PHP_EOL : '<br />';
    echo "$nl$nl";
    die('You must set up the project dependencies.'.$nl.
        'Run the following commands in '.dirname(__DIR__).':'.$nl.$nl.
        'curl -s http://getcomposer.org/installer | php'.$nl.
        'php composer.phar install'.$nl);
}
