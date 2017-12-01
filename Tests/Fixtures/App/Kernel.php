<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Fixtures\App;

use Symfony\Cmf\Component\Testing\HttpKernel\TestKernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class Kernel extends TestKernel
{
    public function configure()
    {
        $this->requireBundleSets(array(
            'default', 'phpcr_odm',
        ));

        $this->registerConfiguredBundles();
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config.php');
    }
}
