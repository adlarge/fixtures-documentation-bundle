<?php

namespace Tests\Model;

use Adlarge\FixturesDocumentationBundle\Model\Fixture;
use PHPUnit\Framework\TestCase;
use Adlarge\FixturesDocumentationBundle\Model\Section;
use Adlarge\FixturesDocumentationBundle\Exception\DuplicateFixtureException;

class SectionTest extends TestCase
{
    public function testConstruct(): void
    {
        $section = new Section('title');
        
        $this->assertSame('title', $section->getTitle());
    }

    public function testSetTitle(): void
    {
        $section = new Section('title');
        $section->setTItle('title2');
        
        $this->assertSame('title2', $section->getTitle());
    }

    /**
     * @throws DuplicateFixtureException
     */
    public function testAddFixture(): void
    {
        $section = new Section('title');
        $fixture1 = $section->addFixture(['id' => '1', 'name' => 'fixture1']);
        $fixture2 = $section->addFixture(['id' => '2', 'name' => 'fixture2']);

        $this->assertInstanceOf(Fixture::class, $fixture1);
        $this->assertInstanceOf(Fixture::class, $fixture2);
        $this->assertSame('title-1', $fixture1->getId());
        $this->assertSame('title-2', $fixture2->getId());
        $this->assertSame(['id' => '1', 'name' => 'fixture1'], $fixture1->getData());
        $this->assertSame(['id' => '2', 'name' => 'fixture2'], $fixture2->getData());
        $this->assertSame([], $fixture1->getLinks());
        $this->assertSame([], $fixture2->getLinks());

        $expectedHeaders = ['id', 'name'];
        $this->assertSame($expectedHeaders, $section->getHeaders());
    }

    /**
     * @throws DuplicateFixtureException
     */
    public function testAddFixtureMergeHeaders(): void
    {
        $section = new Section('title');
        $section->addFixture(['id' => '1', 'firstname' => 'Joe']);
        $section->addFixture(['id' => '2', 'lastname' => 'Dalton']);

        $expectedHeaders = ['id', 'firstname', 'lastname'];
        $this->assertEqualsCanonicalizing($expectedHeaders, $section->getHeaders());
    }

    /**
     * @throws DuplicateFixtureException
     */
    public function testAddFixtureRaiseDuplicateFixtureException(): void
    {
        $this->expectException(DuplicateFixtureException::class);
        $section = new Section('title');
        $section->addFixture(['id' => '1', 'name' => 'samefixture']);
        $section->addFixture(['id' => '1', 'name' => 'samefixture']);
    }
}
