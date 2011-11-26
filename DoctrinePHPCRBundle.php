<?php

namespace Doctrine\Bundle\PHPCRBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Doctrine\Bundle\PHPCRBundle\DependencyInjection\Compiler\RegisterEventListenersAndSubscribersPass;

class DoctrinePHPCRBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterEventListenersAndSubscribersPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION);
    }
}
