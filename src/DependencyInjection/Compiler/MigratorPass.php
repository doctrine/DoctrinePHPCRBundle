<?php


namespace Doctrine\Bundle\PHPCRBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * A CompilerPass which registers available migrators.
 */
class MigratorPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $migrators = array();
        foreach ($container->findTaggedServiceIds('doctrine_phpcr.migrator') as $id => $attributes) {
            $alias = $attributes[0]['alias'] ?? null;
            $migrators[$alias] = $id;
        }

        $container->setParameter('doctrine_phpcr.migrate.migrators', $migrators);
    }
}
