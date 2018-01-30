<?php

$container->setParameter('cmf_testing.bundle_name', 'DoctrinePHPCRBundle');
$container->setParameter('cmf_testing.bundle_fqn', 'Doctrine\Bundle\PHPCRBundle');

$loader->import(CMF_TEST_CONFIG_DIR.'/default.php');
$loader->import(CMF_TEST_CONFIG_DIR.'/phpcr_odm.php');

$loader->import(__DIR__.'/web_profiler.yaml');
