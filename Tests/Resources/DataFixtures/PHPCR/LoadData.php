<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Doctrine\Bundle\PHPCRBundle\Tests\Resources\DataFixtures\PHPCR;

use Doctrine\Bundle\PHPCRBundle\Tests\Resources\Document\ReferrerDocument;
use Doctrine\Bundle\PHPCRBundle\Tests\Resources\Document\TestDocument;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ODM\PHPCR\Document\Generic;
use Doctrine\ODM\PHPCR\DocumentManager;

class LoadData implements FixtureInterface
{
    /**
     * @param DocumentManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $base = new Generic;
        $base->setNodename('test');
        $base->setParentDocument($manager->find(null, '/'));

        $doc = new TestDocument();
        $doc->parent = $base;
        $doc->nodename = 'doc';
        $doc->bool = true;
        $doc->date = new \DateTime('2014-01-14');
        $doc->integer = 42;
        $doc->long = 24;
        $doc->number = 3.14;
        $doc->text = 'text content';

        $manager->persist($doc);

        $doc = new TestDocument();
        $doc->parent = $base;
        $doc->nodename = 'doc2';
        $doc->bool = true;
        $doc->date = new \DateTime('2014-01-14');
        $doc->integer = 42;
        $doc->long = 24;
        $doc->number = 3.14;
        $doc->text = 'text content';

        $manager->persist($doc);

        $ref = new ReferrerDocument();
        $ref->id = '/test/ref';
        $ref->addDocument($doc);

        $manager->persist($ref);

        $manager->flush();

        $node = $manager->getPhpcrSession()->getNode('/test/doc');
        $node->addNode('child');
        $node->addNode('second');
        $manager->getPhpcrSession()->save();

        $manager->clear();
    }
}
