<?php

use Symfony\Component\Routing\RouteCollection;

$collection = new RouteCollection();

$collection->addCollection(
    $loader->import(__DIR__.'/routes/web_profiler.yaml')
);

$collection->addCollection(
    $loader->import(__DIR__.'/routes/routes.yaml')
);

return $collection;
