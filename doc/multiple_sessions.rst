.. index::
    single: Multisession; DoctrinePHPCRBundle

Configuring multiple sessions for PHPCR-ODM
===========================================

If you need more than one PHPCR backend, you can define ``sessions`` as child
of the ``session`` information. Each session has a name and the configuration
following the same schema as what is directly in ``session``. You can also
overwrite which session to use as ``default_session``. Once you have multiple
sessions, you can also configure multiple document managers with those
sessions.

.. tip::

    Autowiring always gives you the default session and the default document
    manager. When working with multiple sessions and managers, you need to
    explicitly specify the services. For the document managers, you can also
    go through the manager registry (see at the end of this page).

.. _bundles-phpcr-odm-multiple-phpcr-sessions:

Multiple PHPCR Sessions
-----------------------

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        doctrine_phpcr:
            session:
                default_session: ~
                sessions:
                    <name>:
                        workspace: ... # Required
                        username:  ~
                        password:  ~
                        backend:
                            # ...
                        options:
                            # ...

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services">
            <config xmlns="http://doctrine-project.org/schema/symfony-dic/odm/phpcr">
                <session default-session="null">
                    <!-- workspace: Required -->
                    <session name="<name>"
                        workspace="..."
                        username="null"
                        password="null"
                    >
                        <backend>
                            <!-- ... -->
                        </backend>
                        <option>
                            <!-- ... -->
                        </option>
                    </session>
                </session>
            </config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('doctrine_phpcr', [
            'session' => [
                'default_session' => null,
                'sessions' => [
                    '<name>' => [
                        'workspace' => '...', // Required
                        'username'  => null,
                        'password'  => null,
                        'backend'   => [
                            // ...
                        ],
                        'options'   => [
                            // ...
                        ],
                    ],
                ],
            ],
        ]);

Multiple Document Managers
--------------------------

If you are using the ODM, you will also want to configure multiple document
managers.

Inside the odm section, you can add named entries in the
``document_managers``. To use the non-default session, specify the session
attribute.

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        odm:
            default_document_manager: ~
            document_managers:
                <name>:
                    session: <sessionname>
                    # ... configuration as above

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services">
            <config xmlns="http://doctrine-project.org/schema/symfony-dic/odm/phpcr">
                <odm default-document-manager="null">
                    <document-manager
                        name="<name>"
                        session="<sessionname>"
                    >
                        <!-- ... configuration as above -->
                    </document-manager>
                </odm>
            </config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('doctrine_phpcr', [
            'odm' => [
                'default_document_manager' => null,
                'document_managers' => [
                    '<name>' => [
                        'session' => '<sessionname>',
                        // ... configuration as above
                    ],
                ],
            ],
        ]);

Bringing it all together
------------------------

The following full example uses the default manager for ``AppBundle``
and the documents provided by the CMF. Additionally, it has a website
and DMS manager that connects to the Jackrabbit of Magnolia CMS. That
manager looks for models in the MagnoliaBundle.

.. configuration-block::

    .. code-block:: yaml

        doctrine_phpcr:
            # configure the PHPCR sessions
            session:
                sessions:
                    default:
                        backend: "%phpcr_backend%"
                        workspace: "%phpcr_workspace%"
                        username: "%phpcr_user%"
                        password: "%phpcr_pass%"

                    website:
                        backend:
                            type: jackrabbit
                            url: "%magnolia_url%"
                        workspace: website
                        username: "%magnolia_user%"
                        password: "%magnolia_pass%"

                    dms:
                        backend:
                            type: jackrabbit
                            url: "%magnolia_url%"
                        workspace: dms
                        username: "%magnolia_user%"
                        password: "%magnolia_pass%"

            # enable the ODM layer
            odm:
                auto_generate_proxy_classes: "%kernel.debug%"
                document_managers:
                    default:
                        session: default
                        mappings:
                            AppBundle: ~
                            CmfContentBundle: ~
                            CmfMenuBundle: ~
                            CmfRoutingBundle: ~

                    website:
                        session: website
                        configuration_id: magnolia.odm_configuration
                        mappings:
                            MagnoliaBundle: ~

                    dms:
                        session: dms
                        configuration_id: magnolia.odm_configuration
                        mappings:
                            MagnoliaBundle: ~

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services">
            <config xmlns="http://doctrine-project.org/schema/symfony-dic/odm/phpcr">
                <session>
                    <session name="default"
                        backend="%phpcr_backend%"
                        workspace="%phpcr_workspace%"
                        username="%phpcr_user%"
                        password="%phpcr_pass%"
                    />
                    <session name="website"
                        workspace="website"
                        username="%magnolia_user%"
                        password="%magnolia_pass%"
                    >
                        <backend type="jackrabbit" url="%magnolia_url%"/>
                    </session>
                    <session name="dms"
                        workspace="dms"
                        username="%magnolia_user%"
                        password="%magnolia_pass%"
                    >
                        <backend type="jackrabbit" url="%magnolia_url%"/>
                    </session>
                </session>

                <!-- enable the ODM layer -->
                <odm auto-generate-proxy-classes="%kernel.debug%">
                    <document-manager
                        name="default"
                        session="default"
                    >
                        <mapping name="AppBundle" />
                        <mapping name="CmfContentBundle" />
                        <mapping name="CmfMenuBundle" />
                        <mapping name="CmfRoutingBundle" />
                    </document-manager>

                    <document-manager
                        name="website"
                        session="website"
                        configuration-id="magnolia.odm_configuration"
                    >
                        <mapping name="MagnoliaBundle" />
                    </document-manager>

                    <document-manager
                        name="dms"
                        session="dms"
                        configuration-id="magnolia.odm_configuration"
                    >
                        <mapping name="MagnoliaBundle" />
                    </document-manager>

                </odm>
            </config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('doctrine_phpcr', [
            'session' => [
                'sessions' => [
                    'default' => [
                        'backend'   => '%phpcr_backend%',
                        'workspace' => '%phpcr_workspace%',
                        'username'  => '%phpcr_user%',
                        'password'  => '%phpcr_pass%',
                    ],
                    'website' => [
                        'backend' => [
                            'type' => 'jackrabbit',
                            'url'  => '%magnolia_url%',
                        ],
                        'workspace' => 'website',
                        'username'  => '%magnolia_user%',
                        'password'  => '%magnolia_pass%',
                    ],
                    'dms' => [
                        'backend' => [
                            'type' => 'jackrabbit',
                            'url'  => '%magnolia_url%',
                        ],
                        'workspace' => 'dms',
                        'username'  => '%magnolia_user%',
                        'password'  => '%magnolia_pass%',
                    ],
                ],
            ],

            // enable the ODM layer
            'odm' => [
                'auto_generate_proxy_classes' => '%kernel.debug%',
                'document_managers' => [
                    'default' => [
                        'session'  => 'default',
                        'mappings' => [
                            'AppBundle' => null,
                            'CmfContentBundle'  => null,
                            'CmfMenuBundle'     => null,
                            'CmfRoutingBundle'  => null,
                        ],
                    ],
                    'website' => [
                        'session'          => 'website',
                        'configuration_id' => 'magnolia.odm_configuration',
                        'mappings'         => [
                            'MagnoliaBundle' => null,
                        ],
                    ],
                    'dms' => [
                        'session'          => 'dms',
                        'configuration_id' => 'magnolia.odm_configuration',
                        'mappings'         => [
                            'MagnoliaBundle' => null,
                        ],
                    ],
                ],
            ],
        ]);


You can access the managers through the manager registry available in the
service ``Doctrine\Bundle\PHPCRBundle\ManagerRegistry``::

    use Doctrine\Bundle\PHPCRBundle\ManagerRegistry;

    /** @var $container \Symfony\Component\DependencyInjection\ContainerInterface */

    // get the named manager from the registry
    $dm = $container->get(ManagerRegistry::class)->getManager('website');

    // get the manager for a specific document class
    $dm = $container->get(ManagerRegistry::class)->getManagerForClass('CmfContentBundle:StaticContent');

Additionally, each manager is available as a service in the DI container.
The service name pattern is ``doctrine_phpcr.odm.<name>_document_manager`` so for
example the website manager is called
``doctrine_phpcr.odm.website_document_manager``.
