UPGRADE FROM 2.x to 2.3
=======================

* [BC Break] In order to have compatibility with Symfony 6, return types have been added to the following methods:
    * `Doctrine\Bundle\PHPCRBundle\DependencyInjection\DoctrinePHPCRExtension::getMappingObjectDefaultName()`
    * `Doctrine\Bundle\PHPCRBundle\DependencyInjection\DoctrinePHPCRExtension::getMappingResourceConfigDirectory()`
    * `Doctrine\Bundle\PHPCRBundle\DependencyInjection\DoctrinePHPCRExtension::getMappingResourceExtension()`
    * `Doctrine\Bundle\PHPCRBundle\DependencyInjection\DoctrinePHPCRExtension::getObjectManagerElementName()`
    * `Doctrine\Bundle\PHPCRBundle\Form\ChoiceList\PhpcrOdmQueryBuilderLoader::getEntities`
    * `Doctrine\Bundle\PHPCRBundle\Form\ChoiceList\PhpcrOdmQueryBuilderLoader::getEntitiesByIds`
    * `Doctrine\Bundle\PHPCRBundle\Form\Type\DocumentType::getLoader`
