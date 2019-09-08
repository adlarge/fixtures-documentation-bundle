<?php

namespace Tests\Model;

use Adlarge\FixturesDocumentationBundle\Exception\BadFixtureLinkException;
use Adlarge\FixturesDocumentationBundle\Exception\BadLinkReferenceException;
use Adlarge\FixturesDocumentationBundle\Exception\DuplicateFixtureException;
use Adlarge\FixturesDocumentationBundle\helpers\Model\Category;
use Adlarge\FixturesDocumentationBundle\helpers\Model\Product;
use Adlarge\FixturesDocumentationBundle\helpers\Model\ProductComplex;
use Adlarge\FixturesDocumentationBundle\helpers\Model\ProductPublic;
use Adlarge\FixturesDocumentationBundle\Model\Documentation;
use Adlarge\FixturesDocumentationBundle\Model\Fixture;
use Mockery;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use TypeError;

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

        $documentation->addFixture('fixtures', '1', ['id' => 1, 'name' => 'fixture1']);

        $this->assertCount(1, $documentation->getSections());
        $this->assertSame('fixtures', $documentation->getSections()[0]->getTitle());
    }

    /**
     * @throws DuplicateFixtureException
     */
    public function testAddFixtureWithSameSection(): void
    {
        $documentation = new Documentation([]);
        
        $documentation->addFixture('fixtures', '1', ['id' => 1, 'name' => 'fixture1']);
        $documentation->addFixture('fixtures', '2', ['id' => 2, 'name' => 'fixture2']);

        $this->assertCount(1, $documentation->getSections());
    }

    /**
     * @throws DuplicateFixtureException
     */
    public function testAddFixtureWithDifferentSection(): void
    {
        $documentation = new Documentation([]);
        
        $documentation->addFixture('fixtures', '1', ['id' => 1, 'name' => 'fixture1']);
        $documentation->addFixture('other', '1', ['id' => 1, 'name' => 'fixture1']);

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
        $mockFixture = Mockery::mock(Fixture::class)
            ->makePartial();
        $mockFixture->shouldReceive('setLinks')
            ->once();
            
        $mockDocumentation = Mockery::mock(
            Documentation::class,
            [
                ['Product' => ['name', 'category']]
            ]
        )
            ->makePartial();

        $mockDocumentation->shouldReceive('addFixture')
            ->once()
            ->with('Product', 'Product-1', [
                'name' => 'product 1',
                'category' => 'category 1'
            ]);
        
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
            ->with('ProductPublic', 'ProductPublic-1', [
                'name' => 'product 1',
                'category' => 'category 1'
            ]);
        
        $product = new ProductPublic();
        $product->id = 1;
        $product->name = 'product 1';
        $product->category = 'category 1';

        $mockDocumentation->addFixtureEntity($product);
        $this->assertCount(0, $mockDocumentation->getSections());
    }

    public function testAddFixtureEntityWithComplexProperties(): void
    {
        $mockFixture = Mockery::mock(Fixture::class)
            ->makePartial();
        $mockFixture->shouldReceive('setLinks')
            ->once();

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
            ->with('ProductComplex', 'ProductComplex-1', [
                'name' => 'product 1',
                'category' => 'category name',
                'tags' => 3
            ]);

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
            ->with('Product', 'Product-1', [
                'category' => 'category 1'
            ]);
        
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
    public function testAddFixtureEntityWithNonConfigEntity(): void
    {
        $mockDocumentation = Mockery::mock(Documentation::class, [[]])
            ->makePartial();

        $mockDocumentation->shouldNotReceive('addFixture')
            ->once()
            ->with('Product', [
                'category' => 'category 1'
            ]);

        $product = (new Product())
            ->setId(1)
            ->setName('product 1')
            ->setCategory('category 1');

        $this->assertNull($mockDocumentation->addFixtureEntity($product));
    }

    /**
     * @throws DuplicateFixtureException
     */
    public function testReset(): void
    {
        $documentation = new Documentation([]);
        
        $documentation->addFixture('fixtures', '1', ['id' => 1, 'name' => 'fixture1']);
        $this->assertCount(1, $documentation->getSections());

        $documentation->reset();
        $this->assertCount(0, $documentation->getSections());
    }

    /**
     * @throws DuplicateFixtureException
     * @throws BadFixtureLinkException
     */
    public function testToJson(): void
    {
        $documentation = new Documentation([]);
        
        $fixture1 = $documentation->addFixture('some', 'some-1', ['id' => 1, 'name' => 'fixture1']);
        $documentation->addFixture('some', 'some-2', ['id' => 2, 'name' => 'fixture2']);
        $documentation->addFixture('others', 'others-1', ['id' => 1, 'pseudo' => 'autre2'])
            ->addLink('pseudo', $fixture1->getId());

        $this->assertSame(
            '{"some":{"fixtures":[{"id":"some-1","data":{"id":1,"name":"fixture1"},"links":[]},{"id":"some-2","data":{"id":2,"name":"fixture2"},"links":[]}]},"others":{"fixtures":[{"id":"others-1","data":{"id":1,"pseudo":"autre2"},"links":{"pseudo":"some-1"}}]}}',
            $documentation->toJson()
        );
    }

    /**
     * @throws DuplicateFixtureException
     */
    public function testInit(): void
    {
        $jsonString = '{"some":{"fixtures":[{"id":"some-1","data":{"id":1,"name":"fixture1"},"links":[]},{"id":"some-2","data":{"id":2,"name":"fixture2"},"links":[]}]},"others":{"fixtures":[{"id":"others-1","data":{"id":1,"pseudo":"autre2"},"links":[]}]}}';
        
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

    /**
     * @throws DuplicateFixtureException
     * @throws BadLinkReferenceException
     */
    public function testLinkReference(): void
    {
        $documentation = new Documentation([]);
        $fixture1 = $documentation->addFixture('test', '1', ['name' => 'Test1']);
        $fixture2 = $documentation->addFixture('test', '2', ['name' => 'Test2']);

        $documentation->addLinkReference('ref1', $fixture1);
        $documentation->addLinkReference('ref2', $fixture2);
        $this->assertEquals($fixture1, $documentation->getLinkReference('ref1'));
        $this->assertEquals($fixture2, $documentation->getLinkReference('ref2'));
    }

    /**
     * @throws DuplicateFixtureException
     * @throws BadLinkReferenceException
     */
    public function testNotExistingLinkReference(): void
    {
        $this->expectException(BadLinkReferenceException::class);
        $documentation = new Documentation([]);

        $documentation->getLinkReference('ref1');
    }
}
