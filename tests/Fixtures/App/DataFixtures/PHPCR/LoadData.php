<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Doctrine\Bundle\PHPCRBundle\Tests\Fixtures\App\DataFixtures\PHPCR;

use Doctrine\Bundle\PHPCRBundle\Tests\Fixtures\App\Document\ReferrerDocument;
use Doctrine\Bundle\PHPCRBundle\Tests\Fixtures\App\Document\TestDocument;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\ODM\PHPCR\Document\Generic;
use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\Persistence\ObjectManager;

class LoadData implements FixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $base = new Generic();
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

        $doc = new TestDocument();
        $doc->parent = $base;
        $doc->nodename = 'doc-very-long';
        $doc->bool = true;
        $doc->date = new \DateTime('2014-01-14');
        $doc->integer = 42;
        $doc->long = 24;
        $doc->number = 3.14;
        $doc->text = 'Lorem ipsum dolor sit amet, consectetur adipiscing'.
            ' elit. Aenean ultrices consectetur ex. Integer fringilla'.
            ' augue sed lacus blandit, non aliquam leo dapibus. Sed'.
            ' ac dolor lorem. Sed non ullamcorper nisl.';

        $manager->persist($doc);

        $manager->flush();

        $node = $manager->getPhpcrSession()->getNode('/test/doc');
        $node->addNode('child');
        $node->addNode('second');
        $manager->getPhpcrSession()->save();

        $manager->clear();
    }
}
