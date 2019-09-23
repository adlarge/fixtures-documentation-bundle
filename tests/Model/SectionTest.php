<?php

namespace Tests\Model;

use Adlarge\FixturesDocumentationBundle\Model\Fixture;
use PHPUnit\Framework\TestCase;
use Adlarge\FixturesDocumentationBundle\Model\Section;
use Adlarge\FixturesDocumentationBundle\Exception\DuplicateIdFixtureException;

class SectionTest extends TestCase
{
    public function testConstruct(): void
    {
        $section = new Section('title');
        
        $this->assertSame('title', $section->getTitle());
    }

    /**
     * @throws DuplicateIdFixtureException
     */
    public function testAddFixture(): void
    {
        $section = new Section('title');
        $section->addFixture(new Fixture(1, ['id' => '1', 'name' => 'fixture1']));
        $section->addFixture(new Fixture(2, ['id' => '2', 'name' => 'fixture2']));

        $expectedHeaders = ['id', 'name'];
        $this->assertSame($expectedHeaders, $section->getHeaders());
    }

    /**
     * @throws DuplicateIdFixtureException
     */
    public function testAddFixtureMergeHeaders(): void
    {
        $section = new Section('title');
        $section->addFixture(new Fixture(1, ['id' => '1', 'firstname' => 'Joe']));
        $section->addFixture(new Fixture(2, ['id' => '2', 'lastname' => 'Dalton']));

        $expectedHeaders = ['id', 'firstname', 'lastname'];
        $this->assertEqualsCanonicalizing($expectedHeaders, $section->getHeaders());
    }

    /**
     * @throws DuplicateIdFixtureException
     */
    public function testAddFixtureRaiseDuplicateFixtureException(): void
    {
        $this->expectException(DuplicateIdFixtureException::class);
        $section = new Section('title');
        $section->addFixture(new Fixture(1, ['id' => '1', 'name' => 'samefixture']));
        $section->addFixture(new Fixture(1, ['id' => '1', 'name' => 'samefixture']));
    }
}
