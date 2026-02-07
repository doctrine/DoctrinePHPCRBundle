<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('doctrine_phpcr.jackalope.repository.factory.service.jackrabbit', \Jackalope\RepositoryFactoryJackrabbit::class);

    $services->set('doctrine_phpcr.jackalope.repository.factory.jackrabbit', \Jackalope\Repository::class)
        ->args([[]])
        ->factory([service('doctrine_phpcr.jackalope.repository.factory.service.jackrabbit'), 'getRepository']);

    $services->set('doctrine_phpcr.jackalope.repository.factory.service.doctrinedbal', \Jackalope\RepositoryFactoryDoctrineDBAL::class);

    $services->set('doctrine_phpcr.jackalope.repository.factory.doctrinedbal', \Jackalope\Repository::class)
        ->args([[]])
        ->factory([service('doctrine_phpcr.jackalope.repository.factory.service.doctrinedbal'), 'getRepository']);

    $services->set('doctrine_phpcr.jackalope.repository.factory.service.prismic', \Jackalope\RepositoryFactoryPrismic::class);

    $services->set('doctrine_phpcr.jackalope.repository.factory.prismic', \Jackalope\Repository::class)
        ->args([[]])
        ->factory([service('doctrine_phpcr.jackalope.repository.factory.service.prismic'), 'getRepository']);

    $services->set('doctrine_phpcr.jackalope.session', \Jackalope\Session::class)
        ->abstract()
        ->args([
            '',
            '',
        ]);
};
