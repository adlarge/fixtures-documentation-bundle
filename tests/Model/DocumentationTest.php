<?php

namespace Tests\Model;

use PHPUnit\Framework\TestCase;
use Adlarge\FixturesDocumentationBundle\Model\Documentation;
use TypeError;
use org\bovigo\vfs\vfsStream;

class DocumentationTest extends TestCase
{
    public function setUp(): void
    {
        $this->root = vfsStream::setup();
        $this->documentation = Documentation::getInstance($this->root->url() . "/file.json");
    }
    
    public function tearDown(): void
    {
        $documentation = Documentation::getInstance('dummyPath');
        $documentation->reset();
    }

    public function testAddFixture(): void
    {
        $documentation = Documentation::getInstance('dummyPath');

        $documentation->addFixture('fixtures', ['id' => 1, 'name' => 'fixture1']);

        $this->assertCount(1, $documentation->getSections());
        $this->assertSame('fixtures', $documentation->getSections()[0]->getTitle());
    }

    public function testAddFixtureWithSameSection(): void
    {
        $documentation = Documentation::getInstance('dummyPath');
        
        $documentation->addFixture('fixtures', ['id' => 1, 'name' => 'fixture1']);
        $documentation->addFixture('fixtures', ['id' => 2, 'name' => 'fixture2']);

        $this->assertCount(1, $documentation->getSections());
    }

    public function testAddFixtureWithDifferentSection(): void
    {
        $documentation = Documentation::getInstance('dummyPath');
        
        $documentation->addFixture('fixtures', ['id' => 1, 'name' => 'fixture1']);
        $documentation->addFixture('other', ['id' => 1, 'name' => 'fixture1']);

        $this->assertCount(2, $documentation->getSections());
    }

    public function testAddFixtureWithMultidimensionnalArray(): void
    {
        $this->expectException(TypeError::class);
        
        $documentation = Documentation::getInstance('dummyPath');
        
        $documentation->addFixture('fixtures', ['id' => 1, 'array' => ['name' => 'fixture1', 'color' => 'red']]);
    }

    public function testReset(): void
    {
        $documentation = Documentation::getInstance('dummyPath');
        
        $documentation->addFixture('fixtures', ['id' => 1, 'name' => 'fixture1']);
        $this->assertCount(1, $documentation->getSections());

        $documentation->reset();
        $this->assertCount(0, $documentation->getSections());
    }

    public function testToJson(): void
    {
        $documentation = Documentation::getInstance('dummyPath');
        
        $documentation->addFixture('some', ['id' => 1, 'name' => 'fixture1']);
        $documentation->addFixture('some', ['id' => 2, 'name' => 'fixture2']);
        $documentation->addFixture('others', ['id' => 1, 'pseudo' => 'autre2']);

        $this->assertSame(
            '{"some":{"fixtures":[{"id":1,"name":"fixture1"},{"id":2,"name":"fixture2"}]},"others":{"fixtures":[{"id":1,"pseudo":"autre2"}]}}', 
            $documentation->toJson());
    }
    
    public function testInit(): void
    {
        file_put_contents(
            $this->root->url() . "/file.json", 
            '{"some":{"fixtures":[{"id":1,"name":"fixture1"},{"id":2,"name":"fixture2"}]},"others":{"fixtures":[{"id":1,"pseudo":"autre2"}]}}'
        );
        
        $documentation = Documentation::getInstance($this->root->url() . "/file.json");
        $this->assertCount(2, $documentation->getSections());
    }
}
