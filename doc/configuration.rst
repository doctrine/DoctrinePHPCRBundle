Configuration Reference
=======================

The DoctrinePHPCRBundle can be configured under the ``doctrine_phpcr`` key in
your application configuration. When using XML, you can use the
``http://doctrine-project.org/schema/symfony-dic/odm/phpcr`` namespace.


Configuration
-------------

``session``
~~~~~~~~~~~

.. tip::

    You can also configure multiple session. See
    :doc:`multiple_sessions` for details.

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        doctrine_phpcr:
            session:
                backend:
                    type: X
                    # optional parameters for Jackalope
                    parameters:
                        jackalope.factory:                Jackalope\Factory
                        jackalope.check_login_on_server:  false
                        jackalope.disable_stream_wrapper: false
                        jackalope.auto_lastmodified:      true
                        # see below for how to configure the backend of your choice
                workspace: default
                username:  admin
                password:  admin
                # tweak options for Jackalope (all versions)
                options:
                    jackalope.fetch_depth: 1

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" charset="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services">

            <config xmlns="http://doctrine-project.org/schema/symfony-dic/odm/phpcr">
                <session workspace="default" username="admin" password="admin">
                    <backend type="X">
                        <parameter key="jackalope.factory">Jackalope\Factory</parameter>
                        <parameter key="jackalope.check_login_on_server">false</parameter>
                        <parameter key="jackalope.disable_stream_wrapper">false</parameter>
                        <parameter key="jackalope.auto_lastmodified">true</parameter>
                    </backend>

                    <options jackalope.fetch_depth="1" />
                </session>
            </config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('doctrine_phpcr', [
            'session' => [
                'backend' => [
                    'type' => 'X',
                    'parameters' => [
                        'jackalope.factory'                => 'Jackalope\Factory',
                        'jackalope.check_login_on_server'  => false,
                        'jackalope.disable_stream_wrapper' => false,
                        'jackalope.auto_lastmodified'      => true,
                    ],
                ],
                'workspace' => 'default',
                'username'  => 'admin',
                'password'  => 'admin',
                'options'   => [
                    'jackalope.fetch_depth' => 1,
                ],
            ],
        ]);

``workspace``
"""""""""""""

**type**: ``string`` **required**

Defines the PHPCR workspace to use for this PHPCR session.

.. tip::

    Every PHPCR implementation should provide the workspace called *default*,
    but you can choose a different one. There is the
    ``doctrine:phpcr:workspace:create`` command to initialize a new workspace.
    See also :ref:`bundles-phpcr-odm-commands`.

``username and password``
"""""""""""""""""""""""""

**type**: ``string`` **default**: ``null``

These credentials are used on the PHPCR layer for the
``PHPCR\SimpleCredentials``. They are optional for jackalope doctrine-dbal.

Do not confuse these credentials with the username and password used by
Doctrine DBAL to connect to the underlying RDBMS where the data
is actually stored.

``backend type``
""""""""""""""""

**type**: ``string`` **default**: ``jackrabbit``

This designates the PHPCR implementation. Valid options are

* ``jackrabbit``;
* ``doctrinedbal``;
* ``prismic``.

``backend parameters``
""""""""""""""""""""""

If you are using one of the Jackalope backends, you can set a couple of
parameters. This section explains the general parameters that are
available with all Jackalope backends. You can also
:ref:`activate logging and profiling <reference-configuration-phpcr-odm-logging>`.

``jackalope.factory``
.....................

**type**: ``string or object`` **default**: ``Jackalope\Factory``

Use a custom factory class for Jackalope objects.

``jackalope.check_login_on_server``
...................................

**type**: ``boolean`` **default**: ``false``

If set to ``false``, skip initial check whether repository exists. You will
only notice connectivity problems on the first attempt to use the repository.

``jackalope.disable_stream_wrapper``
....................................

**type**: ``boolean`` **default**: ``false``

If set to ``true``, streams are read immediately instead of on first access.
If you run into problems with streams this might be useful for debugging.
Otherwise you probably don't want to disable the wrappers, or all binaries
will be loaded each time their containing document is loaded, resulting in a
severe performance penalty.

``jackalope.auto_lastmodified``
...............................

**type**: ``boolean`` **default**: ``true``

Whether to automatically update nodes having ``mix:lastModified``.
See `last modified listener cookbook entry`_.

``backend curl_options``
""""""""""""""""""""""""

If you are using one of the Jackalope Jackrabbit backend, you can set
the curl options which are described in the php-documentation
`curl-setopt`_.

PHPCR Session with Jackalope Jackrabbit
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        doctrine_phpcr:
            session:
                backend:
                    type: jackrabbit
                    url:  http://localhost:8080/server/
                    parameters:
                        # general parameters and options
                        # ...
                        # optional parameters specific to Jackalope Jackrabbit
                        jackalope.default_header:     "X-ID: %serverid%"
                        jackalope.jackrabbit_expect:  true
                        jackalope.jackrabbit_version: "2.18.3"

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services">

            <config xmlns="http://doctrine-project.org/schema/symfony-dic/odm/phpcr">

                <session>

                    <backend
                        type="jackrabbit"
                        url="http://localhost:8080/server/"
                    >
                        <parameter key="jackalope.default_header">X-ID: %serverid%</parameter>
                        <parameter key="jackalope.jackrabbit_expect">true</parameter>
                        <parameter key="jackalope.jackrabbit_version">2.18.3</parameter>
                    </backend>
                </session>
            </config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('doctrine_phpcr', [
            'session' => [
                'backend' => [
                    'type' => 'jackrabbit',
                    'url'  => 'http://localhost:8080/server/',
                    'parameters' => [
                        'jackalope.default_header'     => 'X-ID: %serverid%',
                        'jackalope.jackrabbit_expect'  => true,
                        'jackalope.jackrabbit_version' => '2.18.3',
                    ],
                ],
            ],
        ]);

``url``
"""""""

**type**: ``string``, **required**

The configuration needs the ``url`` parameter to point to your Jackrabbit.
This looks like http://localhost:8080/server/

``jackalope.default_header``
""""""""""""""""""""""""""""

**type**: ``string``, **default**: ``null``

Set a default header to send on each request to the backend.
This is useful when using a load balancer between the webserver and jackrabbit,
to identify sessions.

``jackalope.jackrabbit_expect``
"""""""""""""""""""""""""""""""

**type**: ``boolean``, **default**: ``false``

Send the ``Expect: 100-continue`` header on larger PUT and POST requests.
Disabled by default to avoid issues with proxies and load balancers.

``jackalope.jackrabbit_version``
""""""""""""""""""""""""""""

**type**: ``string``, **default**: ``null``

.. versionadded:: 1.4.2

    This configuration has been added in jackalope-jackrabbit version 1.4.2.

Set the version of the Jackrabbit server to allow the client to offer better functionality if possible.
For example, full UTF8 support including emojis in node and property names is possible when the Jackrabbit version is 2.18.0 or better.

PHPCR Session with Jackalope Doctrine DBAL
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

This type uses Jackalope with a Doctrine database abstraction layer transport
to provide PHPCR without any installation requirements beyond any of the RDBMS
supported by Doctrine.

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        doctrine_phpcr:
            session:
                backend:
                    type:       doctrinedbal
                    # if no explicit connection is specified, the default connection is used.
                    connection: default
                    # to configure caching
                    caches:
                        meta:  doctrine_cache.providers.phpcr_meta
                        nodes: doctrine_cache.providers.phpcr_nodes
                        query: doctrine_cache.providers.phpcr_query
                    parameters:
                        # ... general parameters and options

                        # optional parameters specific to Jackalope Doctrine Dbal
                        jackalope.disable_transactions: false

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services">

            <config xmlns="http://doctrine-project.org/schema/symfony-dic/odm/phpcr">

                <session>

                    <backend type="doctrinedbal" connection="default">
                        <caches
                            meta="doctrine_cache.providers.phpcr_meta"
                            nodes="doctrine_cache.providers.phpcr_nodes"
                            query="doctrine_cache.providers.phpcr_query"
                        />

                        <!-- ... general parameters and options -->

                        <!-- optional parameters specific to Jackalope Doctrine Dbal -->
                        <parameter key="jackalope.disable_transactions">false</parameter>
                    </backend>
                </session>
            </config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('doctrine_phpcr', [
            'session' => [
                'backend' => [
                    'type'       => 'doctrinedbal',
                    'connection' => 'default',
                    'caches' => [
                        'meta'  => 'doctrine_cache.providers.phpcr_meta',
                        'nodes' => 'doctrine_cache.providers.phpcr_nodes',
                        'query' => 'doctrine_cache.providers.phpcr_query',
                    ],
                    'parameters' => [
                        // ... general parameters and options

                        // optional parameters specific to Jackalope Doctrine Dbal
                        'jackalope.disable_transactions' => false,
                    ],
                ],
            ],
        ]);

``connection``
""""""""""""""

**type**: ``string``, **default**: ``default``

Specify the Doctrine DBAL connection name to use if you don't want to use the
default connection. The name must be one of the names of the ``doctrine.dbal``
section of your Doctrine configuration, see the `Symfony Doctrine documentation`_.

``jackalope.disable_transactions``
""""""""""""""""""""""""""""""""""

**type**: ``boolean``, **default**: ``false``

Set to ``true`` to disable transactions. If transactions are enabled but not
actively used, every save operation is wrapped into a transaction.

Only allowed for doctrine-dbal because jackrabbit does not support
transactions.

.. _reference-configuration-phpcr-odm-logging:

Logging and Profiling
~~~~~~~~~~~~~~~~~~~~~

When using any of the Jackalope PHPCR implementations, you can activate logging
to log to the symfony log, or profiling to show information in the Symfony
debug toolbar:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        doctrine_phpcr:
            session:
                backend:
                    # ...
                    logging:   true
                    profiling: true

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services">

            <config xmlns="http://doctrine-project.org/schema/symfony-dic/odm/phpcr">

                <session>

                    <backend
                        logging="true"
                        profiling="true"
                    />
                </session>
            </config>
        </container>

    .. code-block:: php

        // app/config/config.yml
        $container->loadFromExtension('doctrine_phpcr', [
            'session' => [
                'backend' => [
                    // ...
                    'logging'   => true,
                    'profiling' => true,
                ],
            ],
        ]);

Doctrine PHPCR-ODM Configuration
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

This configuration section manages the Doctrine PHPCR-ODM system. If you do
not configure anything here, the ODM services will not be loaded.

.. tip::

    If you want to only use plain PHPCR without the PHPCR-ODM, you can simply
    not configure the ``odm`` section to avoid loading the services at all.
    Note that most CMF bundles by default use PHPCR-ODM documents and thus
    need ODM enabled.

.. tip::

    You can also configure multiple document managers. See
    :doc:`multiple_sessions` for details.

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        doctrine_phpcr:
            odm:
                configuration_id: ~
                auto_mapping: true
                mappings:
                    # An array of mapping, which may be a bundle name or an unique name
                    <name>:
                        mapping:   true
                        type:      ~
                        dir:       ~
                        alias:     ~
                        prefix:    ~
                        is_bundle: ~
                auto_generate_proxy_classes: "%kernel.debug%"
                proxy_dir: "%kernel.cache_dir%/doctrine/PHPCRProxies"
                proxy_namespace: PHPCRProxies
                namespaces:
                    translation:
                        alias: phpcr_locale

                metadata_cache_driver:
                    type:           array
                    host:           ~
                    port:           ~
                    instance_class: ~
                    class:          ~
                    id:             ~
                    namespace:      ~


    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" charset="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services">
            <config xmlns="http://doctrine-project.org/schema/symfony-dic/odm/phpcr">
                <odm configuration-id="null"
                    auto-mapping="true"
                    auto-generate-proxy-classes="%kernel.debug%"
                    proxy-dir="%kernel.cache_dir%/doctrine/PHPCRProxies"
                    proxy-namespace="PHPCRProxies"
                >
                    <namespaces>
                        <translation alias="phpcr_locale" />
                    </namespaces>

                    <!-- An array of mapping, which may be a bundle name or an unique name -->
                    <mapping name="<name>">
                        mapping="true"
                        type="null"
                        dir="null"
                        alias="null"
                        prefix="null"
                        is-bundle="null"
                    />

                    <metadata-cache-driver
                        type="array"
                        host="null"
                        port="null"
                        instance-class="null"
                        class="null"
                        id="null"
                        namespace="null"
                    />
                </odm>
            </config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('doctrine_phpcr', [
            'odm' => [
                'configuration_id'            => null,
                'auto_mapping'                => true,
                'auto_generate_proxy_classes' => '%kernel.debug%',
                'proxy-dir'                   => '%kernel.cache_dir%/doctrine/PHPCRProxies',
                'proxy_namespace'             => 'PHPCRProxies',
                'namespaces' => [
                    'translation' => [
                        'alias' => 'phpcr_locale',
                    ],
                ],
                'mappings' => [
                    // An array of mapping, which may be a bundle name or an unique name
                    '<name>' => [
                        'mapping'   => true,
                        'type'      => null,
                        'dir'       => null,
                        'alias'     => null,
                        'prefix'    => null,
                        'is-bundle' => null,
                    ],
                ],
                'metadata_cache_driver' => [
                    'type'           => 'array',
                    'host'           => null,
                    'port'           => null,
                    'instance_class' => null,
                    'class'          => null,
                    'id'             => null,
                    'namespace'      => null,
                ],
            ],
        ]);

``configuration_id``
""""""""""""""""""""

**type**: ``string``, **default**: ``doctrine_phpcr.odm.configuration``

The service to use as base for building the PHPCR-ODM configuration.

``auto_mapping``
""""""""""""""""

**type**: ``boolean``, **default**: ``true``

When enabled, bundles will be automatically loaded and attempted to resolve
mappings by convention in
``<Bundle>/Resources/config/doctrine/<Document>.phpcr.xml`` resp. ``*.phpcr.yml``
to configure mappings for documents you provide in the ``<Bundle>/Document``
folder. Otherwise you need to manually configure the mappings section.

``auto_generate_proxy_classes``
"""""""""""""""""""""""""""""""

**type**: ``boolean``, **default**: ``%kernel.debug%``

When disabled, you need to run the ``cache:warmup`` command in order to have
the proxy classes generated after you modified a document.

``proxy_dir``
"""""""""""""

**type**: ``string``, **default**: ``%kernel.cache_dir%/doctrine/PHPCRProxies``

Change folder where proxy classes are generated.

``proxy_namespace``
"""""""""""""""""""

**type**: ``string``, **default**: ``PHPCRProxies``

Change namespace for generated proxy classes.

``namespaces``
""""""""""""""

This configuration section is intended to allow you to customize the
PHPCR namespaces used by PHPCR-ODM. Currently it is only possible to
set the alias used by the translation strategy.

``mappings``
""""""""""""

Explicitly define document mappings by configuration. For modern Symfony
applications that do not use a bundle, it is necessary to configure mappings.
For bundles, if ``auto_mapping`` is enabled, you don't usually need to.

.. tip::

    When ``auto_mapping`` is disabled, you need to explicitly list the
    bundles handled by this document manager. Usually its fine to just list
    the bundle names without any actual configuration.

.. tip::

    DoctrinePhpcrBundle is integrated with symfony/doctrine-bridge (in the same
    way that `Doctrine ORM`_ does), relying on the bridge to process mapping
    configuration options. Therefore, the mapping options work nearly the same
    across two bundles.

There are several configuration options that you can control as part of
a mapping definition:

``mapping``
    A boolean value and it is usually ``true``. Set it to ``true`` to
    declare it as a mapping and allow the document manager to pick it
    up.

``type``
    One of ``attribute``, ``xml``, ``yml``, ``php`` or ``staticphp``.
    This specifies which type of metadata type your mapping uses.

``dir``
    Path to the mapping or document files (depending on the driver). If this path
    is relative, it is assumed to be relative to the bundle root. This only works
    if the name of your mapping is a bundle name. If you want to use this option
    to specify absolute paths, you should prefix the path with the kernel
    parameters that exist in the DIC (for example ``%kernel.root_dir%``).

``prefix``
    A common namespace prefix that all documents of this mapping share. This
    prefix should never conflict with prefixes of other defined mappings
    otherwise some of your documents cannot be found by Doctrine. This option
    defaults to the bundle namespace + ``Document``, for example for an
    application bundle called ``AcmeHelloBundle`` prefix would be
    ``Acme\HelloBundle\Document``.

``alias``
    Doctrine offers a way to alias document namespaces to simpler, shorter names
    to be used in DQL queries or for Repository access. When using a bundle, the
    alias defaults to the bundle name.

``is_bundle``
    This option is a derived value from ``dir`` and by default is set to true if
    dir is relative proved by a ``file_exists()`` check that returns false. It
    is false if the existence check returns true. In this case, an absolute path
    was specified and the metadata files are most likely in a directory outside
    of a bundle.


``metadata_cache_driver``
"""""""""""""""""""""""""

Configure a cache driver for the Doctrine metadata. This is the same as for
`Doctrine ORM`_.

The ``namespace`` value is useful if you are using one primary caching server
for multiple sites that have similar code in their respective ``vendor/``
directories. By default, Symfony will try to generate a unique namespace
value for each application but if code is very similar between two
applications, it is very easy to have two applications share the same
namespace. This option also prevents Symfony from needing to re-build
application cache on each Composer update on a newly generated namespace.

General Settings
~~~~~~~~~~~~~~~~

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        doctrine_phpcr:
            jackrabbit_jar:       /path/to/jackrabbit.jar
            dump_max_line_length: 120

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services">

            <config xmlns="http://doctrine-project.org/schema/symfony-dic/odm/phpcr"
                jackrabbit-jar="/path/to/jackrabbit.jar"
                dump-max-line-length="120"
            />
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('doctrine_phpcr', [
            'jackrabbit_jar'       => '/path/to/jackrabbit.jar',
            'dump_max_line_length' => 120,
        ]);

``jackrabbit_jar``
""""""""""""""""""

**type**: ``string`` **default**: ``null``

Absolute path to the jackrabbit jar file. If this is set, you can use the
``doctrine:phpcr:jackrabbit`` console command to start and stop Jackrabbit.

``dump_max_line_length``
""""""""""""""""""""""""

**type**: ``integer`` **default**: ``120``

For tuning the output of the ``doctrine:phpcr:dump`` command.

.. _`Symfony Doctrine documentation`: https://symfony.com/doc/current/doctrine.html
.. _`last modified listener cookbook entry`: http://docs.doctrine-project.org/projects/doctrine-phpcr-odm/en/latest/cookbook/last-modified.html
.. _`Doctrine ORM`: https://symfony.com/doc/current/reference/configuration/doctrine.html#caching-drivers
.. _`curl-setopt`: http://php.net/manual/de/function.curl-setopt.php
