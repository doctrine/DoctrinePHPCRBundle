# Doctrine PHPCR Bundle

This bundle integrates Doctrine PHPCR ODM and PHPCR backends into Symfony2 like:

* Jackalope
* Midgard2 CR

# Installation

Follow the [installation tutorial](https://github.com/symfony-cmf/symfony-cmf-docs/blob/master/tutorials/installing-configuring-doctrine-phpcr-odm.rst)


## Configuration

The configuration is similar to Doctrine ORM and MongoDB configuration for Symfony2 as its based
on the `AbstractDoctrineBundle` aswell:

``` yaml
doctrine_phpcr:
    # configure the PHPCR session
    session:
        backend:
            ## backend type: jackrabbit, doctrinedbal or midgard
            type: jackrabbit

            ## doctrinedbal only, required
            connection: <service name of the doctrine dbal connection>

            ## jackrabbit only, required
            url: http://localhost:8080/server/
            ## jackrabbit only, optional. see https://github.com/jackalope/jackalope/blob/master/src/Jackalope/RepositoryFactoryJackrabbit.php
            default_header: ...
            expect: 'Expect: 100-continue'

            ## tweak options for jackrabbit and doctrinedbal (all jackalope versions)
            # optional, below set to the default
            # enable if you want to have an exception right away if backend login fails
            check_login_on_server: false
            # enable if you experience segmentation faults while working with binary data in documents
            disable_stream_wrapper: false
            # enable if you do not want to use transactions and you neither want the odm to automatically use transactions
            # its highly recommended NOT to disable transactions
            disable_transactions: false
        workspace: default
        username: admin
        password: admin
    # enable the ODM layer
    odm:
        auto_mapping: true
        # whether to automatically create proxy classes or create them manually
        auto_generate_proxy_classes: %kernel.debug%
        # overwrite the default location for generated proxies
        proxy_dir: ...
        # overwrite the default php namespace for proxies
        proxy_namespace: ...
        # set the language fallback order (for translatable documents)
        locales:
            en:
                - en
                - de
                - fr
            de:
                - de
                - en
                - fr
            fr:
                - fr
                - en
                - de
```

## Services

You can access the PHPCR services like this:

``` php
<?php

namespace Acme\DemoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        // PHPCR session instance
        $session = $this->container->get('doctrine_phpcr.default_session');
        // PHPCR ODM document manager instance
        $documentManager = $this->container->get('doctrine_phpcr.odm.default_document_manager');
    }
}
```

# Additional requirements for the doctrine:phpcr:fixtures:load command

To use the doctrine:phpcr:fixtures:load command, you additionally need the Doctrine
data-fixtures and the symfony doctrine fixtures bundle:
- https://github.com/symfony/DoctrineFixturesBundle
- https://github.com/doctrine/data-fixtures


# Commands

The bundle provides a couple of symfony commands. To execute them, from your
main project folder run

    app/console.php <command> [options] [arguments]

Look for the commands that start with `doctrine:phpcr`.


# Fixtures

The fixtures classes must implement `Doctrine\Common\DataFixtures\FixtureInterface`.

Here is an example of fixture:

``` php
<?php

namespace MyBundle\DataFixtures\PHPCR;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\FixtureInterface;

class LoadMyData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        // Create and persist your data here...
    }
}
```
