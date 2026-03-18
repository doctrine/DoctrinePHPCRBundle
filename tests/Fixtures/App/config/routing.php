<?php

use Symfony\Component\Routing\RouteCollection;

$collection = new RouteCollection();

$collection->addCollection(
    $loader->import(
        \file_exists(__DIR__.'/../../../../vendor/symfony/web-profiler-bundle/Resources/config/routing/wdt.php')
            ? __DIR__.'/routes/web_profiler_sf8.yaml'
            : __DIR__.'/routes/web_profiler.yaml'
    ),
);

$collection->addCollection(
    $loader->import(__DIR__.'/routes/routes.yaml'),
);

return $collection;
