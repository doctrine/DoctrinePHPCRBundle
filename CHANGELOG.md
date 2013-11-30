Changelog
=========

* **2013-11-27**: [Initializer] Parameter "doctrine_phpcr.initialize.initializers" no longer defined
 * Initializers are now collected using a compiler pass and injected into the new InitializerManager
* **2013-11-27**: [Initializer] `doctrine:phpcr:fixtures:load` now executes all of the initializers
 * This behavior can be disabled using the `--no-initialize` command
* **2013-08-16**: [Form] moved image logic to the CmfMedia bundle
 * `phpcr_odm_image` is changed to `cmf_media_image`
 * the ImagineCacheInvalidatorSubscriber is moved
 * the ModelToFileTransformer is moved

