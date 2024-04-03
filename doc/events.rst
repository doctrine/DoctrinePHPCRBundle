.. index::
    single: Events; DoctrinePHPCRBundle

Doctrine PHPCR-ODM Events
=========================

Doctrine PHPCR-ODM provides an event system allowing to react to all
important operations that documents have during their lifecycle. Please
see the `Doctrine PHPCR-ODM event system documentation`_ for a full
list of supported events.

The DoctrinePHPCRBundle provides dependency injection support for the
event listeners and event subscribers.

Dependency Injection Tags
-------------------------

You can tag services to listen to Doctrine PHPCR-ODM events. It works the same
way as for `Doctrine ORM events`_. The only differences are:

* use the tag name ``doctrine_phpcr.event_listener`` resp.
  ``doctrine_phpcr.event_subscriber`` instead of ``doctrine.event_listener``;
* expect the argument to be of class
  ``Doctrine\Common\Persistence\Event\LifecycleEventArgs``.

To tag a service as event listener and another service as event subscriber,
use this configuration:

.. configuration-block::

    .. code-block:: yaml

        # app/config/services.yml
        services:
            app.phpcr_search_indexer:
                class: App\EventListener\SearchIndexer
                    tags:
                        - { name: doctrine_phpcr.event_listener, event: postPersist }

            app.phpcr_listener:
                class: App\EventListener\MyListener
                    tags:
                        - { name: doctrine_phpcr.event_subscriber }

    .. code-block:: xml

        <?xml version="1.0" ?>
        <!-- app/config/config.xml -->
        <container xmlns="http://symfony.com/schema/dic/services">
            <services>
                <service id="app.phpcr_search_indexer"
                         class="App\EventListener\SearchIndexer">
                    <tag name="doctrine_phpcr.event_listener" event="postPersist" />
                </service>
                <service id="app.phpcr_listener"
                         class="App\EventListener\MyListener">
                    <tag name="doctrine_phpcr.event_subscriber" />
                </service>
            </services>
        </container>

    .. code-block:: php

        // app/config/config.php
        use App\EventListener\SearchIndexer;
        use App\EventListener\MyListener;

        $container
            ->register(
                'app.phpcr_search_indexer',
                SearchIndexer::class
            )
            ->addTag('doctrine_phpcr.event_listener', [
                'event' => 'postPersist',
            ])
        ;

        $container
            ->register(
                'app.phpcr_listener',
                MySubscriber::class
            )
            ->addTag('doctrine_phpcr.event_subscriber')
        ;

.. tip::

    Doctrine event subscribers (both ORM and PHPCR-ODM) can **not** return a
    flexible array of methods to call like the `Symfony event subscriber`_.
    Doctrine event subscribers must return a simple array of the event
    names they subscribe to. Doctrine will then expect methods on the
    subscriber with the names of the subscribed events, just as when using an
    event listener.

You can find more information and examples of the doctrine event system
in "`How to Register Event Listeners and Subscribers`_" of the core documentation.

.. _`Doctrine PHPCR-ODM event system documentation`: http://docs.doctrine-project.org/projects/doctrine-phpcr-odm/en/latest/reference/events.html
.. _`Symfony event subscriber`: https://symfony.com/doc/current/components/event_dispatcher/introduction.html#using-event-subscribers
.. _`Doctrine ORM events`: https://symfony.com/doc/current/doctrine/event_listeners_subscribers.html
.. _`How to Register Event Listeners and Subscribers`: https://symfony.com/doc/current/doctrine/event_listeners_subscribers.html
