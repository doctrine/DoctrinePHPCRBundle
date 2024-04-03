.. index::
    single: Form Types; DoctrinePHPCRBundle

Doctrine PHPCR-ODM Form Types
=============================

This bundle provides some handy form types for PHPCR and PHPCR-ODM specific
cases, along with a type guesser that uses these types.

There is also a validator constraint for PHPCR-ODM documents.

Form Types
----------

.. tip::

    When editing associative multivalue fields, have a look at the
    BurgovKeyValueFormBundle_.

phpcr_document
~~~~~~~~~~~~~~

This form type is suitable to edit associations of PHPCR-ODM documents. It
works for ReferenceOne, ReferenceMany and Referrers but also for
ParentDocument associations. Make sure to set the ``multiple`` option
for ReferenceMany and Referrers, and to not set it for the others.

.. note::

    While ``Children`` is also an association, it makes no sense to edit it
    with this form type. Children are automatically attached to their parent.
    ``MixedReferrers`` could be shown as a ``disabled`` field but never edited,
    because this association is immutable.

This form type is equivalent to the ``entity`` form type provided by Symfony
for Doctrine ORM. It has the same options as the ``entity`` type, including
that the option for the document manager is called ``em``.

A simple example of using the ``phpcr_document`` form type looks as follows::

    use App\Document\TargetClass;

    $form
        ->add(
            'speakers',
            'phpcr_document',
            [
                'property' => 'title',
                'class'    => TargetClass::class,
                'multiple' => true,
            ]
        )
    ;

This will produce a multiple choice select field with the value of
``getTitle`` called on each instance of ``TargetClass`` found in the
content repository. Alternatively, you can set the ``choices`` option
to a list of allowed managed documents. Please refer to the
`Symfony documentation on the entity form type`_ for more details,
including how you can configure a query.

If you are using SonataDoctrinePHPCRAdminBundle_, you might want to look into
``sonata_type_collection``. That form type allows to edit related
documents (references as well as children) in-line and also to create
and remove them on the fly.

phpcr_odm_reference_collection
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. caution::

    This form type was deprecated in DoctrinePHPCRBundle 1.1 and will be
    removed in DoctrinePHPCRBundle 1.2. You should use the `phpcr_document`_
    type instead, which can do the same but better.

This form type handles editing ``ReferenceMany`` collections on PHPCR-ODM
documents.  It is a choice field with an added ``referenced_class`` required
option that specifies the class of the referenced target document.

To use this form type, you also need to specify the list of possible reference
targets as an array of PHPCR-ODM ids or PHPCR paths.

The minimal code required to use this type looks as follows::

    use App\Document\Article;

    $dataArr = [
        '/some/phpcr/path/item_1' => 'first item',
        '/some/phpcr/path/item_2' => 'second item',
    ];

    $formMapper
        ->with('form.group_general')
            ->add('myCollection', 'phpcr_odm_reference_collection', [
                'choices'   => $dataArr,
                'referenced_class'  => Article::class,
            ])
        ->end();

.. tip::

    When building an admin interface with the SonataDoctrinePHPCRAdminBundle_
    there is also the ``sonata_type_model``, which is more powerful, allowing to
    add to the referenced documents on the fly.

phpcr_reference
~~~~~~~~~~~~~~~

The ``phpcr_reference`` represents a PHPCR Property of type REFERENCE or
WEAKREFERENCE within a form.  The input will be rendered as a text field
containing either the PATH or the UUID as per the configuration. The form will
resolve the path or id back to a PHPCR node to set the reference.

This type extends the ``text`` form type. It adds an option
``transformer_type`` that can be set to either ``path`` or ``uuid``.


Validator Constraint
--------------------

The bundle provides a ``ValidPhpcrOdm`` constraint validator you can use to
check if your document ``Id`` or ``Nodename`` and ``Parent`` fields are
correct.

.. configuration-block::

    .. code-block:: yaml

        # src/App/Resources/config/validation.yml
        App\Document\Author:
            constraints:
                - Doctrine\Bundle\PHPCRBundle\Validator\Constraints\ValidPhpcrOdm

    .. code-block:: php

        // src/App/Document/Author.php

        // ...
        use Doctrine\Bundle\PHPCRBundle\Validator\Constraints as OdmAssert;

        #[OdmAssert\ValidPhpcrOdm]
        class Author
        {
            // ...
        }

    .. code-block:: xml

        <!-- Resources/config/validation.xml -->
        <?xml version="1.0" ?>
        <constraint-mapping xmlns="http://symfony.com/schema/dic/constraint-mapping"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/dic/constraint-mapping
                http://symfony.com/schema/dic/constraint-mapping/constraint-mapping-1.0.xsd">

            <class name="App\Document\Author">
                <constraint name="Doctrine\Bundle\PHPCRBundle\Validator\Constraints\ValidPhpcrOdm" />
            </class>

        </constraint-mapping>

    .. code-block:: php

        // src/App/Document/Author.php

        // ...
        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Doctrine\Bundle\PHPCRBundle\Validator\Constraints as OdmAssert;

        #[OdmAssert\ValidPhpcrOdm]
        class Author
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addConstraint(new OdmAssert\ValidPhpcrOdm());
            }
        }

.. _BurgovKeyValueFormBundle: https://github.com/Burgov/KeyValueFormBundle
.. _`Symfony documentation on the entity form type`: https://symfony.com/doc/current/reference/forms/types/entity.html
.. _SonataDoctrinePHPCRAdminBundle: https://sonata-project.org/bundles/doctrine-phpcr-admin/master/doc/index.html
