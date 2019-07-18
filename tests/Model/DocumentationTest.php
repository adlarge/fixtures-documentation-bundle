<?php

namespace Tests\Model;

use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use Adlarge\FixturesDocumentationBundle\Model\Documentation;
use \Adlarge\FixturesDocumentationBundle\Exception\DuplicateFixtureException;
use TypeError;
use org\bovigo\vfs\vfsStream;

class DocumentationTest extends TestCase
{
    /** @var vfsStreamDirectory */
    private $root;

    public function setUp(): void
    {
        $this->root = vfsStream::setup();
    }

    public function tearDown(): void
    {
        $documentation = new Documentation();
        $documentation->reset();
    }

    /**
     * @throws DuplicateFixtureException
     */
    public function testAddFixture(): void
    {
        $documentation = new Documentation();

        $documentation->addFixture('fixtures', ['id' => 1, 'name' => 'fixture1']);

        $this->assertCount(1, $documentation->getSections());
        $this->assertSame('fixtures', $documentation->getSections()[0]->getTitle());
    }

    /**
     * @throws DuplicateFixtureException
     */
    public function testAddFixtureWithSameSection(): void
    {
        $documentation = new Documentation();
        
        $documentation->addFixture('fixtures', ['id' => 1, 'name' => 'fixture1']);
        $documentation->addFixture('fixtures', ['id' => 2, 'name' => 'fixture2']);

        $this->assertCount(1, $documentation->getSections());
    }

    /**
     * @throws DuplicateFixtureException
     */
    public function testAddFixtureWithDifferentSection(): void
    {
        $documentation = new Documentation();
        
        $documentation->addFixture('fixtures', ['id' => 1, 'name' => 'fixture1']);
        $documentation->addFixture('other', ['id' => 1, 'name' => 'fixture1']);

        $this->assertCount(2, $documentation->getSections());
    }

    /**
     * @throws DuplicateFixtureException
     */
    public function testAddFixtureWithMultidimensionalArray(): void
    {
        $this->expectException(TypeError::class);
        
        $documentation = new Documentation();
        
        $documentation->addFixture('fixtures', ['id' => 1, 'array' => ['name' => 'fixture1', 'color' => 'red']]);
    }

    /**
     * @throws DuplicateFixtureException
     */
    public function testReset(): void
    {
        $documentation = new Documentation();
        
        $documentation->addFixture('fixtures', ['id' => 1, 'name' => 'fixture1']);
        $this->assertCount(1, $documentation->getSections());

        $documentation->reset();
        $this->assertCount(0, $documentation->getSections());
    }

    /**
     * @throws DuplicateFixtureException
     */
    public function testToJson(): void
    {
        $documentation = new Documentation();
        
        $documentation->addFixture('some', ['id' => 1, 'name' => 'fixture1']);
        $documentation->addFixture('some', ['id' => 2, 'name' => 'fixture2']);
        $documentation->addFixture('others', ['id' => 1, 'pseudo' => 'autre2']);

        $this->assertSame(
            '{"some":{"fixtures":[{"id":1,"name":"fixture1"},{"id":2,"name":"fixture2"}]},"others":{"fixtures":[{"id":1,"pseudo":"autre2"}]}}', 
            $documentation->toJson());
    }

    /**
     * @throws DuplicateFixtureException
     */
    public function testInit(): void
    {
        $jsonString = '{"some":{"fixtures":[{"id":1,"name":"fixture1"},{"id":2,"name":"fixture2"}]},"others":{"fixtures":[{"id":1,"pseudo":"autre2"}]}}';
        
        $documentation = new Documentation($jsonString);
        $this->assertCount(2, $documentation->getSections());
    }

    /**
     * @throws DuplicateFixtureException
     */
    public function testInitEmpty(): void
    {
        $jsonString = null;

        $documentation = new Documentation($jsonString);
        $this->assertCount(0, $documentation->getSections());
    }
}
