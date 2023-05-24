Changelog
=========

<<<<<<< HEAD
3.0.0 (unreleased)
------------------

* Upgrade to phpcr-odm 2.0
* Support jackalope 2.0
* Replace doctrine cache with PSR-6 cache with the symfony/cache implementation.
  The configuration of metadata_cache_driver changed. By default, it creates an `array` cache.
  To configure a service, specify `type: service` and specify your service in the `id` property.
  To use a cache pool, specify the service id of that pool.
* Introduced the ManagerRegistryInterface for the ManagerRegistry and adjusted the service alias for autowiring to the interface.  
* The following container parameters are no longer taken into account (memcache and apc seem to have never been used anyways).
  If you have customised the array cache class, please check if this is still needed - and note that starting from this version,
  PSR-16 simple cache is used:

    doctrine_phpcr.odm.cache.array.class
    doctrine_phpcr.odm.cache.apc.class
    doctrine_phpcr.odm.cache.memcache.class
    doctrine_phpcr.odm.cache.memcache_host
    doctrine_phpcr.odm.cache.memcache_port
    doctrine_phpcr.odm.cache.memcache_instance.class
    doctrine_phpcr.odm.cache.memcached.class
    doctrine_phpcr.odm.cache.memcached_host
    doctrine_phpcr.odm.cache.memcached_port
    doctrine_phpcr.odm.cache.memcached_instance.class
    doctrine_phpcr.odm.cache.xcache.class
* If no username is defined for a session, and if you use Jackalope >= 2.0, the
  credentials service for this session is no longer created and `null` is
  passed as credentials.

2.4.3
-----

* Revert BC break with command return types. If you extend the commands, you should add return type declarations anyways to be ready for 3.x.

2.4.2
-----

* Drop support for Symfony 4.3 and 5.0 - 5.3. This release supports the LTS versions Symfony 4.4, 5.4 and Symfony 6.

2.4.1
-----

* Remove obsolete service definition for removed PHPCRODMReferenceCollectionType to avoid container linting errors.

2.4.0
-----

* Support for Symfony 6
* Drop support for Symfony 3

2.3.0
-----

* Explicitly require doctrine/cache to keep running Jackalope with cache.
* Drop support for PHP 7.1 - 7.3

2.2.2
-----

* Allow to configure a doctrine-dbal query cache, like you can configure the meta and nodes cache.

2.2.1
-----

* [fix] Use `Kernel::getProjectDir().'/src'` if `Kernel::getRootDir()` is not
  available (method was removed in Symfony 5).

2.2.0
-----

* Added support for PHP 8

2.1.2
-----

* [fix] Do not fail when doctrine/persistence 2.x is used.

2.1.1
-----

* [fix] Allow rfc5646 compatible locales like `en-gb`
  When using yaml configuration, Symfony converted locales defined under
  `doctrine_phpcr.odm.locales` to underscore, e.g. `en_gb`. With this fix,
  the locales are kept literal.
  CAUTION: If you previously used the workaround of defining both `en-gb` and
  `en_gb`, this will now break. See this github pull request for more details:
  https://github.com/doctrine/DoctrinePHPCRBundle/pull/347.

2.1.0
-----

* Allow Symfony 5
* [performance] Jackalope Doctrine DBAL schema listener marked as lazy.
  Install ocramius/proxy-manager to avoid unnecessary database calls.

2.0.4
------

* [fix] Alias to provide default dbal connection when the default session is not using dbal
* [fix] Avoid adding multiple doctrine.event_listener tags with same options
* [fix] Allow initializer services to be private
* [fix] Add kernel root directory to fixtures location paths

2.0.3
-----

* Fixed: Avoid problem with debug:autowiring command by reordering things in container extension #333.

2.0.2
-----

* Fixed: Removed problematic default mapping for the whole src/ directory. When not using a bundle,
  you need to explicitly configure your Document folder. See step 6 at
  https://symfony.com/doc/master/cmf/cookbook/database/create_new_project_phpcr_odm.html .

2.0.1
-----

* Fixed: NodeDumpCommand now respects the --max_line_length option

2.0.0
-----

* Prepared services for autowiring. Services now have aliases named the same as the class:
  - doctrine_phpcr => Doctrine\Bundle\PHPCRBundle\ManagerRegistry
  - doctrine_phpcr.session => PHPCR\SessionInterface
  - doctrine_phpcr.odm.document_manager => Doctrine\ODM\PHPCR\DocumentManagerInterface

* Removed deprecated `PHPCRODMReferenceCollectionType` and `ReferenceManyCollectionToArrayTransformer`.
* Dropped deprecated option session (use dm instead) and unused option name from command `doctrine:phpcr:fixtures:load`
* Dropped deprecated option session (use dm instead) from command `doctrine:phpcr:document:migrate-class`
* Made all command options required - omit the option completely when you don't need to set anything

1.3.11
------

* [performance] Jackalope Doctrine DBAL schema listener marked as lazy.
  Install ocramius/proxy-manager to avoid unnecessary database calls.

1.3.10
------

* [fix] Alias to provide default dbal connection when the default session is not using dbal
* [fix] Avoid adding multiple doctrine.event_listener tags with same options
* [fix] Allow initializer services to be private
* [fix] Add kernel root directory to fixtures location paths

1.3.9
-----

* Added connection parameter to RepositorySchema, so that the DBAL configuration applies. This will affect
  you if you configured connection options for DBAL, but will not affect already created tables.

  NOTE: Releases 1.3.6 - 1.3.8 have various non-complete or broken versions of this feature.

1.3.5
-----

* Added support for `session` parameter in repository initializers with the new `SessionAwareInitializerInterface`.
  GenericInitializer now implements this new interface.

1.3.4
-----

* Fixed bug with `default_locale` option in configuration.

1.3.3
-----

* Since version 1.3.0 `LocaleListener` has treated the `locale_fallback` strategy 'hardcoded' as 'merge', this is now
  rectified so and the default behaviour is now 'merge'

1.3.2
-----

* Added curl-options to configuration

1.3.1
-----

* Reverted jackalope.check_login_on_server depending on kernel.debug because
  it caused too many chicken and egg problems. That value now defaults to false.

1.2.1
-----

* Added support for priorities. This fixes a regression whereby the new CMF initializer services would
  be executed in an arbitrary order, causing unresolvable conflicts.

1.2.0-RC1
---------

* **2014-08-19**: Renamed PHPCRTypeGuesser to PhpcrOdmTypeGuesser as its about phpcr-odm.

* **2014-08-09**: Added PHPCR-Shell proxy command. This command deprecates the existing
  PHPCR commands and provides access to the full suite of commands provided by PHPCR shell.

* **2014-07-25**: jackalope.check_login_on_server now defaults to kernel.debug.
  Furthermore Proxy cache warming is disabled when jackalope.check_login_on_server
  is enabled and Jackalope Doctrine DBAL is used by any Document Manager
  to prevent issues while bootstrapping the repository

1.1.0
-----

* **2014-05-05**: XML configuration is now supported with namespace
  http://doctrine-project.org/schema/symfony-dic/odm/phpcr

* **2014-04-15**: doctrine:phpcr:fixtures:load command should be called with
  dm instead of session to avoid confusion. Throws error when the previously
  unused parameter `--name` is passed.

* **2014-04-11**: drop Symfony 2.2 compatibility

1.1.0-beta2
-----------

* **2014-03-14**: [Configuration] Cleaned up parameters that define service
  classes. A few needed to be renamed, if you use them you need to update:
  * doctrine_phpcr.odm.form.path_class => doctrine_phpcr.odm.form.path.type.class
  * doctrine_phpcr.console_dumper_class => doctrine_phpcr.console_dumper.class
  * doctrine_phpcr.initializer_manager => doctrine_phpcr.initializer_manager.class

* **2014-03-14**: [Configuration] Jackalope repository factory configuration is
  cleaned up. Instead of the explicit list of options, the DI now passes on all
  parameters you provide as phpcr_backend.parameters.<parameter-name>: value.
  The previously supported options directly specified on phpcr_backend are kept
  for backwards compatibility but it is recommended to switch to the new mode.
  The names need to be adjusted to the jackalope parameter names as follows:
  * check_login_on_server => jackalope.check_login_on_server
  * disable_stream_wrapper => jackalope.disable_stream_wrapper
  * disable_transactions => jackalope.disable_transactions

* **2014-02-01**: [Initializer] Initializer names
 * All initializers now must implement the `getName` method.
 * Pushed $name as the first argument for the `GenericInitializer`.
 * The object passed to the `init` method of classes implementing `InitializerInterface`
   is now the PHPCR `ManagerRegistry`. Custom implementations can now retrieve:
   * The document manager: `$registry->getManager()`
   * The PHPCR session: `$registry->getConnection()`
* **2013-12-11**: [Form] Deprecated the form type "phpcr_odm_reference_collection".
  It seems to not work and "phpcr_document" should cover everything we need.

* **2013-11-27**: [Initializer] Parameter "doctrine_phpcr.initialize.initializers" no longer defined
 * Initializers are now collected using a compiler pass and injected into the new InitializerManager
* **2013-11-27**: [Initializer] `doctrine:phpcr:fixtures:load` now executes all of the initializers
 * This behavior can be disabled using the `--no-initialize` command
* **2013-08-16**: [Form] moved image logic to the CmfMedia bundle
 * `phpcr_odm_image` is changed to `cmf_media_image`
 * the ImagineCacheInvalidatorSubscriber is moved
 * the ModelToFileTransformer is moved
