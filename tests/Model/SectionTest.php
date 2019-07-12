<?php

namespace Tests\Model;

use PHPUnit\Framework\TestCase;
use FixturesDocumentation\Model\Section;

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
}