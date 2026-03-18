<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('doctrine_phpcr.odm.locale_chooser', \Doctrine\ODM\PHPCR\Translation\LocaleChooser\LocaleChooser::class)
        ->args([
            '%doctrine_phpcr.odm.locales%',
            '%doctrine_phpcr.odm.default_locale%',
        ]);

    $services->set('doctrine_phpcr.odm.locale_listener', \Doctrine\Bundle\PHPCRBundle\EventListener\LocaleListener::class)
        ->args([
            service('doctrine_phpcr.odm.locale_chooser'),
            '%doctrine_phpcr.odm.allowed_locales%',
            '%doctrine_phpcr.odm.locale_fallback%',
        ])
        ->tag('kernel.event_subscriber');
};
