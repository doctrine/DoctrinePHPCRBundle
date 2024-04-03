.. index::
    single: Initializers; DoctrinePHPCRBundle
    single: Fixtures; DoctrinePHPCRBundle
    single: Migrators; DoctrinePHPCRBundle

Maintaining Data in the Repository
==================================

PHPCR-ODM provides *initializers* that ensure a repository is ready for
production use, *migrators* to programmatically load data and
*fixture loading* for handling testing and demo fixtures.

.. _phpcr-odm-repository-initializers:

Repository Initializers
-----------------------

The Initializer is the PHPCR equivalent of the ORM schema tools. It is used to
let your application PHPCR node types and to create required base paths in the
repository.

.. note::

    The concept of base paths is needed because there are no separate "tables"
    as in a relational database, but one tree containing all data. To be able
    to add a document, you need to ensure the parent path is already present
    in the repository.

Initializers have to implement the
``Doctrine\Bundle\PHPCRBundle\Initializer\InitializerInterface``. If you don't
need any special logic and want to create plain PHPCR nodes and not documents,
you can simply define services with ``GenericInitializer``. The generic
Initializer expects a name to identify the Initializer, an array of repository
paths it will create if they do not exist and an optional string defining
namespaces and primary / mixin node types in the CND language that should be
registered with the repository.

A service to use the generic Initializer looks like this:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        app.phpcr_initializer:
            class: Doctrine\Bundle\PHPCRBundle\Initializer\GenericInitializer
            arguments:
                - App Basepaths
                - ["/my/content", "/my/menu"]
                - "%app.cnd%"
            tags:
                - { name: "doctrine_phpcr.initializer" }

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <service id="app.phpcr_initializer"
                 class="Doctrine\Bundle\PHPCRBundle\Initializer\GenericInitializer">
            <argument>App Basepaths</argument>
            <argument type="collection">
                <argument>/my/content</argument>
                <argument>/my/menu</argument>
            </argument>
            <argument>%app.cnd%</argument>
            <tag name="doctrine_phpcr.initializer"/>
        </service>

    .. code-block:: php

        // app/config/services.php
        use Doctrine\Bundle\PHPCRBundle\Initializer\GenericInitializer;
        use Symfony\Component\DependencyInjection\Definition

        // ...

        $definition = new Definition(
            GenericInitializer::class, [
                'App Basepaths',
                ['/my/content', '/my/menu'],
                '%app.cnd%',
            ]
        );
        $definition->addTag('doctrine_phpcr.initializer');
        $container->setDefinition('app.phpcr_initializer', $definition);

You can execute your Initializers using the following command:

.. code-block:: bash

    $ php bin/console doctrine:phpcr:repository:init

.. note::
    The load data fixtures command automatically executes the Initializers
    after purging the database, before executing the fixtures.

The generic Initializer only creates PHPCR nodes. If you want to create
specific documents, you need your own Initializer. The interesting method
to overwrite is the ``init`` method. It is passed the ``ManagerRegistry``,
from which you can retrieve the PHPCR session but also the document manager::

    // src/App/Initializer/SiteInitializer.php
    namespace App\Initializer;

    use App\Documents\Site;
    use Doctrine\Bundle\PHPCRBundle\Initializer\InitializerInterface;
    use Doctrine\Bundle\PHPCRBundle\ManagerRegistry;
    use PHPCR\SessionInterface;
    use PHPCR\Util\NodeHelper;

    class SiteInitializer implements InitializerInterface
    {
        private $basePath;

        public function __construct($basePath = '/cms')
        {
            $this->basePath = $basePath;
        }

        public function init(ManagerRegistry $registry)
        {
            $dm = $registry->getManagerForClass(Site::class);
            if ($dm->find(null, $this->basePath)) {
                return;
            }

            $site = new Site();
            $site->setId($this->basePath);
            $dm->persist($site);
            $dm->flush();

            $session = $registry->getConnection();
            // create the 'cms', 'pages', and 'posts' nodes
            NodeHelper::createPath($session, '/cms/pages');
            NodeHelper::createPath($session, '/cms/posts');
            NodeHelper::createPath($session, '/cms/routes');

            $session->save();
        }

        public function getName()
        {
            return 'Site Initializer';
        }
    }

Define a service for your Initializer as follows:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        services:
            # ...
            app.phpcr_initializer_site:
                class: App\Initializer\SiteInitializer
                tags:
                    - { name: doctrine_phpcr.initializer }

    .. code-block:: xml

        <!-- app/config/config.php
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:acme_demo="http://www.example.com/symfony/schema/"
            xsi:schemaLocation="http://symfony.com/schema/dic/services
                 http://symfony.com/schema/dic/services/services-1.0.xsd">

            <!-- ... -->
            <services>
                <!-- ... -->
                <service id="app.phpcr_initializer_site"
                    class="App\Initializer\SiteInitializer">
                    <tag name="doctrine_phpcr.initializer"/>
                </service>
            </services>

        </container>

    .. code-block:: php

        // app/config/config.php

        //  ...
        $container
            ->register(
                'app.phpcr_initializer_site',
                'App\Initializer\SiteInitializer'
            )
            ->addTag('doctrine_phpcr.initializer', ['name' => 'doctrine_phpcr.initializer']
        ;

Migration Loading
-----------------

The DoctrinePHPCRBundle also ships with a simple command to run migration
scripts. Migrations should implement the
``Doctrine\Bundle\PHPCRBundle\Migrator\MigratorInterface`` and registered as a
service with a ``doctrine_phpcr.migrator`` tag contains an ``alias`` attribute
uniquely identifying the migrator. There is an optional
``Doctrine\Bundle\PHPCRBundle\Migrator\AbstractMigrator`` class to use as a
basis.

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        app.migration:
            class: App\Migration\Migration
            arguments:
                - { "%app.content_basepath%", "%app.menu_basepath%" }
            tags:
                - { name: "doctrine_phpcr.migrator", alias: "app.migration" }

    .. code-block:: xml

        <!-- app/config/services.xml -->
        <?xml version="1.0" ?>
        <container xmlns="http://symfony.com/schema/dic/services">
            <service id="app.migration"
                     class="App\Migration\Migration">
                <argument type="collection">
                    <argument>%app.content_basepath%</argument>
                    <argument>%app.menu_basepath%</argument>
                </argument>

                <tag name="doctrine_phpcr.migrator" alias="app.migration"/>
            </service>
        </container>

    .. code-block:: php

        use App\Migration\Migration;
        use Symfony\Component\DependencyInjection\Definition;

        // ...
        $definition = new Definition(Migration::class, [
            [
                '%app.content_basepath%',
                '%app.menu_basepath%',
            ],
        ]);
        $definition->addTag('doctrine_phpcr.migrator', ['alias' => 'app.migration']);

        $container->setDefinition('app.migration', $definition);

To find out available migrations run:

.. code-block:: bash

    $ php bin/console doctrine:phpcr:migrator:migrate

Then pass in the name of the migrator to run it, optionally passing in an
``--identifier``, ``--depth`` or ``--session`` argument. The later argument
determines which session name to set on the migrator, while the first two
arguments will simply be passed to the ``migrate()`` method. You can find an
example migrator in the SimpleCmsBundle.

.. tip::

    A simple alternative if you do not need to reproduce the result can be to
    export part of your repository and re-import it on the target server. This
    is described in :ref:`phpcr-odm-backup-restore`.

.. _phpcr-odm-repository-fixtures:

Fixture Loading
---------------

To use the ``doctrine:phpcr:fixtures:load`` command, you additionally need to
install the `DoctrineFixturesBundle`_ which brings the
`Doctrine data-fixtures`_ into Symfony.

Fixtures work the same way they work for Doctrine ORM. You write fixture
classes implementing ``Doctrine\Common\DataFixtures\FixtureInterface``. If you
place them in ``<App|Bundle>\DataFixtures\PHPCR``, they will be auto detected if you
don't specify a path in the command.

A simple example fixture class looks like this::

    // src/App/DataFixtures/PHPCR/LoadPageData.php
    namespace App\DataFixtures\PHPCR;

    use Doctrine\Common\Persistence\ObjectManager;
    use Doctrine\Common\DataFixtures\FixtureInterface;
    use Doctrine\ODM\PHPCR\DocumentManager;

    class LoadPageData implements FixtureInterface
    {
        public function load(ObjectManager $manager)
        {
            if (!$manager instanceof DocumentManager) {
                $class = get_class($manager);
                throw new \RuntimeException("Fixture requires a PHPCR ODM DocumentManager instance, instance of '$class' given.");
            }

            // ... create and persist your data here
        }
    }

For more on fixtures, see the documentation of the `DoctrineFixturesBundle`_.

.. _`DoctrineFixturesBundle`: https://symfony.com/doc/current/bundles/DoctrineFixturesBundle/index.html
.. _`Doctrine data-fixtures`: https://github.com/doctrine/data-fixtures
