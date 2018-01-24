<?php

namespace Doctrine\Bundle\PHPCRBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * A CompilerPass which registers available initializers.
 *
 * @author David Buchmann <mail@davidbu.ch>
 */
class InitializerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('doctrine_phpcr.initializer_manager')) {
            return;
        }

        $initializerManagerDef = $container->getDefinition('doctrine_phpcr.initializer_manager');
        $services = $container->findTaggedServiceIds('doctrine_phpcr.initializer');

        foreach ($services as $id => $attributes) {
            $priority = 0;

            if (isset($attributes[0]['priority'])) {
                $priority = $attributes[0]['priority'];
            }

            $initializerManagerDef->addMethodCall('addInitializer', [
                new Reference($id), $priority,
            ]);
        }
    }
}
