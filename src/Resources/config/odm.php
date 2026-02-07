<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();
    $parameters->set('doctrine_phpcr.odm.cache.array.class', \Doctrine\Common\Cache\ArrayCache::class);
    $parameters->set('doctrine_phpcr.odm.cache.apc.class', \Doctrine\Common\Cache\ApcCache::class);
    $parameters->set('doctrine_phpcr.odm.cache.memcache.class', \Doctrine\Common\Cache\MemcacheCache::class);
    $parameters->set('doctrine_phpcr.odm.cache.memcache_host', 'localhost');
    $parameters->set('doctrine_phpcr.odm.cache.memcache_port', 11211);
    $parameters->set('doctrine_phpcr.odm.cache.memcache_instance.class', 'Memcache');
    $parameters->set('doctrine_phpcr.odm.cache.memcached.class', \Doctrine\Common\Cache\MemcachedCache::class);
    $parameters->set('doctrine_phpcr.odm.cache.memcached_host', 'localhost');
    $parameters->set('doctrine_phpcr.odm.cache.memcached_port', 11211);
    $parameters->set('doctrine_phpcr.odm.cache.memcached_instance.class', 'Memcached');
    $parameters->set('doctrine_phpcr.odm.cache.xcache.class', \Doctrine\Common\Cache\XcacheCache::class);
    $parameters->set('doctrine_phpcr.odm.metadata.xml.class', \Doctrine\Bundle\PHPCRBundle\Mapping\Driver\XmlDriver::class);
    $parameters->set('doctrine_phpcr.odm.metadata.yml.class', \Doctrine\Bundle\PHPCRBundle\Mapping\Driver\YamlDriver::class);
    $parameters->set('doctrine_phpcr.odm.metadata.php.class', \Doctrine\Persistence\Mapping\Driver\StaticPHPDriver::class);
    $parameters->set('doctrine_phpcr.odm.metadata.driver_chain.class', \Doctrine\Persistence\Mapping\Driver\MappingDriverChain::class);
    $parameters->set('doctrine_phpcr.odm.metadata.attribute.class', \Doctrine\ODM\PHPCR\Mapping\Driver\AttributeDriver::class);

    $services->set('doctrine_phpcr.odm.proxy_cache_warmer', \Symfony\Bridge\Doctrine\CacheWarmer\ProxyCacheWarmer::class)
        ->private()
        ->args([service('doctrine_phpcr')])
        ->tag('kernel.cache_warmer');

    $services->set('doctrine_phpcr.odm.unique_node_type_cache_warmer', \Doctrine\Bundle\PHPCRBundle\CacheWarmer\UniqueNodeTypeCacheWarmer::class)
        ->private()
        ->args([service('doctrine_phpcr')])
        ->tag('kernel.cache_warmer');

    $services->alias('doctrine_phpcr.odm.metadata.attribute_reader', 'attribute_reader')
        ->private();

    $services->set('doctrine_phpcr.odm.configuration', \Doctrine\ODM\PHPCR\Configuration::class)
        ->private()
        ->abstract();

    $services->set('doctrine_phpcr.odm.document_manager.abstract', \Doctrine\ODM\PHPCR\DocumentManager::class)
        ->public()
        ->abstract();

    $services->set('form.type.phpcr.document', \Doctrine\Bundle\PHPCRBundle\Form\Type\DocumentType::class)
        ->args([service('doctrine_phpcr')])
        ->tag('form.type', ['alias' => 'phpcr_document']);

    $services->set('doctrine_phpcr.odm.form.type.path', \Doctrine\Bundle\PHPCRBundle\Form\Type\PathType::class)
        ->args([service('doctrine_phpcr')])
        ->tag('form.type', ['alias' => 'phpcr_odm_path']);

    $services->set('form.type_guesser.doctrine_phpcr', \Doctrine\Bundle\PHPCRBundle\Form\PhpcrOdmTypeGuesser::class)
        ->args([
            service('doctrine_phpcr'),
            '%doctrine_phpcr.form.type_guess%',
        ])
        ->tag('form.type_guesser');

    $services->set('doctrine_phpcr.odm.validator.valid_phpcr_odm', \Doctrine\Bundle\PHPCRBundle\Validator\Constraints\ValidPhpcrOdmValidator::class)
        ->args([service('doctrine_phpcr')])
        ->tag('validator.constraint_validator', ['alias' => 'doctrine_phpcr.odm.validator.valid_phpcr_odm']);

    $services->set('doctrine_phpcr.odm.translation.strategy.attribute', \Doctrine\ODM\PHPCR\Translation\TranslationStrategy\AttributeTranslationStrategy::class)
        ->args([''])
        ->call('setPrefix', ['%doctrine_phpcr.odm.namespaces.translation.alias%']);

    $services->set('doctrine_phpcr.odm.translation.strategy.child', \Doctrine\ODM\PHPCR\Translation\TranslationStrategy\ChildTranslationStrategy::class)
        ->args(['']);

    $services->set(\Doctrine\Bundle\PHPCRBundle\Command\LoadFixtureCommand::class, \Doctrine\Bundle\PHPCRBundle\OptionalCommand\ODM\LoadFixtureCommand::class)
        ->args([service('doctrine_phpcr.initializer_manager')])
        ->tag('console.command');
};
