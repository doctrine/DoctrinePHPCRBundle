<?php

namespace Doctrine\Bundle\PHPCRBundle\Tests\Fixtures\App;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    public function getCacheDir(): string
    {
        return __DIR__.'/var/cache/'.$this->environment;
    }

    public function getLogDir(): string
    {
        return __DIR__.'/var/log';
    }

    public function getProjectDir(): string
    {
        return __DIR__;
    }

    public function registerBundles(): iterable
    {
        $contents = require __DIR__.'/config/bundles.php';
        foreach ($contents as $class => $envs) {
            if (isset($envs['all']) || isset($envs[$this->environment])) {
                yield new $class();
            }
        }
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__.'/config/config.php');
    }
}
