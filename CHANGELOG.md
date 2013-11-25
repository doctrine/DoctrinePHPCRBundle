Changelog
=========

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

