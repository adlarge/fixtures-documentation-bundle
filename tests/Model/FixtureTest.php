<?php

namespace Tests\Model;

use Adlarge\FixturesDocumentationBundle\Exception\BadFixtureLinkException;
use Adlarge\FixturesDocumentationBundle\Model\Fixture;
use PHPUnit\Framework\TestCase;

class FixtureTest extends TestCase
{
    /**
     * @throws BadFixtureLinkException
     */
    public function testAddLink(): void
    {
        $fixture1 = new Fixture('test-1', []);
        $fixture2 = new Fixture('some-1', ['name' => 'test']);
        $fixture2->addLink('name', $fixture1);

        $this->assertEquals(['name' => 'test-1'], $fixture2->getLinks());
    }

    /**
     * @throws BadFixtureLinkException
     */
    public function testAddLinkWithNotExistingField(): void
    {
        $this->expectException(BadFixtureLinkException::class);

        $fixture1 = new Fixture('test-1', []);
        $fixture2 = new Fixture('some-1', ['name' => 'test']);
        $fixture2->addLink('name2', $fixture1);
    }

    /**
     * @throws BadFixtureLinkException
     */
    public function testAddLinkWithAlreadyUsedField(): void
    {
        $this->expectException(BadFixtureLinkException::class);

        $fixture1 = new Fixture('test-1', []);
        $fixture2 = new Fixture('some-1', ['name' => 'test']);
        $fixture2->addLink('name', $fixture1);
        $fixture2->addLink('name', $fixture1);
    }
}
