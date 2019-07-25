<?php

namespace Tests\Model;

use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use Adlarge\FixturesDocumentationBundle\Model\Documentation;
use Adlarge\FixturesDocumentationBundle\Exception\DuplicateFixtureException;
use TypeError;
use org\bovigo\vfs\vfsStream;
use Mockery;
use Adlarge\FixturesDocumentationBundle\helpers\Model\Product;
use Adlarge\FixturesDocumentationBundle\helpers\Model\ProductPublic;
use Adlarge\FixturesDocumentationBundle\helpers\Model\ProductComplex;
use Adlarge\FixturesDocumentationBundle\helpers\Model\Category;

class DocumentationTest extends TestCase
{
    /** @var vfsStreamDirectory */
    private $root;

    public function setUp(): void
    {
        $this->root = vfsStream::setup();
    }

    /**
     * @throws DuplicateFixtureException
     */
    public function tearDown(): void
    {
        $documentation = new Documentation([]);
        $documentation->reset();
    }

    /**
     * @throws DuplicateFixtureException
     */
    public function testAddFixture(): void
    {
        $documentation = new Documentation([]);

        $documentation->addFixture('fixtures', ['id' => 1, 'name' => 'fixture1']);

        $this->assertCount(1, $documentation->getSections());
        $this->assertSame('fixtures', $documentation->getSections()[0]->getTitle());
    }

    /**
     * @throws DuplicateFixtureException
     */
    public function testAddFixtureWithSameSection(): void
    {
        $documentation = new Documentation([]);
        
        $documentation->addFixture('fixtures', ['id' => 1, 'name' => 'fixture1']);
        $documentation->addFixture('fixtures', ['id' => 2, 'name' => 'fixture2']);

        $this->assertCount(1, $documentation->getSections());
    }

    /**
     * @throws DuplicateFixtureException
     */
    public function testAddFixtureWithDifferentSection(): void
    {
        $documentation = new Documentation([]);
        
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
        
        $documentation = new Documentation([]);
        
        $documentation->addFixture('fixtures', ['id' => 1, 'array' => ['name' => 'fixture1', 'color' => 'red']]);
    }

    public function testAddFixtureEntity(): void
    {
        $mockDocumentation = Mockery::mock(
            Documentation::class,
            [
                ['Product' => ['name', 'category']]
            ]
        )
            ->makePartial();

        $mockDocumentation->shouldReceive('addFixture')
            ->once()
            ->with('Product', [
                'name' => 'product 1',
                'category' => 'category 1'
            ])
            ->andReturn($mockDocumentation);
        
        $product = (new Product())
            ->setId(1)
            ->setName('product 1')
            ->setCategory('category 1');

        $mockDocumentation->addFixtureEntity($product);
        $this->assertCount(0, $mockDocumentation->getSections());
    }

    public function testAddFixtureEntityWithPublicProperties(): void
    {
        $mockDocumentation = Mockery::mock(
            Documentation::class,
            [
                ['ProductPublic' => ['name', 'category']]
            ]
        )
            ->makePartial();

        $mockDocumentation->shouldReceive('addFixture')
            ->once()
            ->with('ProductPublic', [
                'name' => 'product 1',
                'category' => 'category 1'
            ])
            ->andReturn($mockDocumentation);
        
        $product = new ProductPublic();
        $product->id = 1;
        $product->name = 'product 1';
        $product->category = 'category 1';

        $mockDocumentation->addFixtureEntity($product);
        $this->assertCount(0, $mockDocumentation->getSections());
    }

    public function testAddFixtureEntityWithComplexProperties(): void
    {
        $mockDocumentation = Mockery::mock(
            Documentation::class,
            [
                [
                    'ProductComplex' => ['name', 'category', 'tags', 'categories'],
                    'Category' => ['id', 'name']
                ]
            ]
        )
            ->makePartial();

        $mockDocumentation->shouldReceive('addFixture')
            ->once()
            ->with('ProductComplex', [
                'name' => 'product 1',
                'category' => 'category name',
                'tags' => 3
            ])
            ->andReturn($mockDocumentation);

            $category = new Category();
            $category->name = 'category name';
            $category->visibility = true;


            $product = (new ProductComplex())
            ->setId(1)
            ->setName('product 1')
            ->setCategory($category)
            ->setTags(['tag1', 'tag2', 'tag3']);

        $mockDocumentation->addFixtureEntity($product);
        $this->assertCount(0, $mockDocumentation->getSections());
    }

    /**
     * @throws DuplicateFixtureException
     */
    public function testAddFixtureEntityWithNonExistingProperty(): void
    {
        $mockDocumentation = Mockery::mock(
            Documentation::class,
            [
                ['Product' => ['names', 'category']]
            ]
        )
            ->makePartial();

        $mockDocumentation->shouldReceive('addFixture')
            ->once()
            ->with('Product', [
                'category' => 'category 1'
            ])
            ->andReturn($mockDocumentation);
        
        $product = (new Product())
            ->setId(1)
            ->setName('product 1')
            ->setCategory('category 1');

        $mockDocumentation->addFixtureEntity($product);
        $this->assertCount(0, $mockDocumentation->getSections());
    }

    /**
     * @throws DuplicateFixtureException
     */
    public function testReset(): void
    {
        $documentation = new Documentation([]);
        
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
        $documentation = new Documentation([]);
        
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
        
        $documentation = new Documentation([], $jsonString);
        $this->assertCount(2, $documentation->getSections());
    }

    /**
     * @throws DuplicateFixtureException
     */
    public function testInitEmpty(): void
    {
        $jsonString = null;

        $documentation = new Documentation([], $jsonString);
        $this->assertCount(0, $documentation->getSections());
    }
}
