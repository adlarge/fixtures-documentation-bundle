<?php

namespace Tests\Model;

use PHPUnit\Framework\TestCase;
use FixturesDocumentation\Model\Section;
use FixturesDocumentation\Exception\DuplicateFixtureException;

class SectionTest extends TestCase
{
    public function testAddFixture()
    {
        $section = new Section('title');
        $section->addFixture(['id' => '1', 'name' => 'fixture1']);
        $section->addFixture(['id' => '2', 'name' => 'fixture2']);

        $expectedFixtures = [
            ['id' => '1', 'name' => 'fixture1'],
            ['id' => '2', 'name' => 'fixture2']
        ];
        $this->assertSame($expectedFixtures, $section->getFixtures());

        $expectedHeaders = ['id', 'name'];
        $this->assertSame($expectedHeaders, $section->getHeaders());
    }

    public function testAddFixtureMergeHeaders()
    {
        $section = new Section('title');
        $section->addFixture(['id' => '1', 'firstname' => 'Joe']);
        $section->addFixture(['id' => '2', 'lastname' => 'Dalton']);

        $expectedHeaders = ['id', 'firstname', 'lastname'];
        $this->assertEqualsCanonicalizing($expectedHeaders, $section->getHeaders());
    }

    public function testAddFixtureRaiseDuplicateFixtureException()
    {
        $this->expectException(DuplicateFixtureException::class);
        $section = new Section('title');
        $section->addFixture(['id' => '1', 'name' => 'samefixture']);
        $section->addFixture(['id' => '1', 'name' => 'samefixture']);
    }
}