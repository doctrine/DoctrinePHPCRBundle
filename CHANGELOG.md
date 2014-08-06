Changelog
=========

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

