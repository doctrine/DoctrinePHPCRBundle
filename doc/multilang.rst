.. index::
    single: Multi-Language; DoctrinePHPCRBundle

Doctrine PHPCR-ODM Multi-Language Support
=========================================

PHPCR-ODM can handle translated documents. All translations of the same
document are considered the same document. Only one language version can be
loaded at the same time.

Note that the CMF routing component does not use translated routes but has a
`separate route per language`_.

To use the multi-language features of PHPCR-ODM you need to enable locales in
the configuration.

Translation Configuration
-------------------------

To use translated documents, you need to configure the available languages:

.. configuration-block::

    .. code-block:: yaml

        # app/config/config.yml
        doctrine_phpcr:
            odm:
                # ...
                locales:
                    en: [de, fr]
                    de: [en, fr]
                    fr: [en, de]
                locale_fallback: hardcoded
                default_locale: fr

    .. code-block:: xml

        <!-- app/config/config.xml -->
        <?xml version="1.0" encoding="UTF-8" ?>
        <container xmlns="http://symfony.com/schema/dic/services">

            <config xmlns="http://doctrine-project.org/schema/symfony-dic/odm/phpcr">

                <odm locale-fallback="hardcoded">
                    <!-- ... -->
                    <locale name="en">
                        <fallback>de</fallback>
                        <fallback>fr</fallback>
                    </locale>

                    <locale name="de">
                        <fallback>en</fallback>
                        <fallback>fr</fallback>
                    </locale>

                    <locale name="fr">
                        <fallback>en</fallback>
                        <fallback>de</fallback>
                    </locale>

                    <default_locale>fr</default_locale>
                </odm>
            </config>
        </container>

    .. code-block:: php

        // app/config/config.php
        $container->loadFromExtension('doctrine_phpcr', [
            'odm' => [
                // ...
                'locales' => [
                    'en' => ['de', 'fr'],
                    'de' => ['en', 'fr'],
                    'fr' => ['en', 'de'],
                ],
                'locale_fallback' => 'hardcoded',
                'default_locale'  => 'fr',
            ]
        ]);

The ``locales`` is a list of alternative locales to look up if a document
is not translated to the requested locale.

The default locale is used for the standard locale chooser strategy and
hence will be the default locale in the document manager. Specifying the
default locale is optional. If you do not specify a default locale then the
first locale listed is used as the default locale.

This bundle provides a request listener that gets activated when any locales
are configured. This listener updates PHPCR-ODM to use the locale Symfony
determined for this request, if that locale is in the list of keys defined
under ``locales``.

Fallback strategies
~~~~~~~~~~~~~~~~~~~

There are several strategies to adjust the fallback order for the selected
locale based on the accepted languages of the request (determined by Symfony
from the ``Accept-Language`` HTML header). All of them will never add any
locales that where not configured in the ``locales`` to avoid a request
injecting unexpected things into your repository:

* ``hardcoded``: This strategy does not update the fallback order from
  the request;
* ``replace``: takes the accepted locales from the request and updates the
  fallback order with them, removing any locales not found in the request;
* ``merge``: does the same as ``replace`` but then adds locales not found in
  the request but on the ``locales`` configuration back to the end of the
  fallback list. This reorders the locales without losing any of them. This is
  the default strategy.

Translated documents
--------------------

To make a document translated, you need to define the ``translator`` attribute
on the document mapping, and you need to map the ``locale`` field. Then you can
use the ``translated`` attribute on all fields that should be different
depending on the locale.

.. configuration-block::

    .. code-block:: php

        // src/App/Documents/Article.php
        namespace App\Documents\Article;

        use Doctrine\ODM\PHPCR\Mapping\Attributes as PHPCR;

        #[PHPCR\Document(translator: 'attribute')]
        class Article
        {
            /**
             * The language this document currently is in
             */
            #[PHPCR\Locale]
            private $locale;

            /**
             * Untranslated property
             */
            #[PHPCR\Date]
            private $publishDate;

            /**
             * Translated property
             */
            #[PHPCR\Field(type: 'string', translated: true)]
            private $topic;

            /**
             * Language specific image
             */
            #[PHPCR\Binary(translated: true)]
            private $image;
        }

    .. code-block:: xml

        <doctrine-mapping>
            <document class="App\Documents\Article"
                      translator="attribute">
                <locale fieldName="locale" />
                <field fieldName="publishDate" type="date" />
                <field fieldName="topic" type="string" translated="true" />
                <field fieldName="image" type="binary" translated="true" />
            </document>
        </doctrine-mapping>

    .. code-block:: yaml

        App\Documents\Article:
            translator: attribute
            locale: locale
            fields:
                publishDate:
                    type: date
                topic:
                    type: string
                    translated: true
                image:
                    type: binary
                    translated: true

Unless you explicitly interact with the multi-language features of PHPCR-ODM,
documents are loaded in the request locale and saved in the locale they where
loaded. (This could be a different locale, if the PHPCR-ODM did not find the
requested locale and had to fall back to an alternative locale.)

.. tip::

    For more information on multilingual documents, see the
    `PHPCR-ODM documentation on multi-language`_.

.. _`PHPCR-ODM documentation on multi-language`: http://docs.doctrine-project.org/projects/doctrine-phpcr-odm/en/latest/reference/multilang.html
.. _`separate route per language`: https://symfony.com/bundles/CMFRoutingBundle/current/routing-component/dynamic.html
