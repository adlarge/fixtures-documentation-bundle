<?php

namespace Tests\Model;

use Adlarge\FixturesDocumentationBundle\Exception\BadFixtureLinkException;
use Adlarge\FixturesDocumentationBundle\Exception\BadLinkReferenceException;
use Adlarge\FixturesDocumentationBundle\Exception\DuplicateIdFixtureException;
use Adlarge\FixturesDocumentationBundle\helpers\Model\Category;
use Adlarge\FixturesDocumentationBundle\helpers\Model\Product;
use Adlarge\FixturesDocumentationBundle\helpers\Model\ProductComplex;
use Adlarge\FixturesDocumentationBundle\helpers\Model\ProductPublic;
use Adlarge\FixturesDocumentationBundle\helpers\Model\ProductWithoutId;
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
     * @throws DuplicateIdFixtureException
     */
    public function tearDown(): void
    {
        $documentation = new Documentation([]);
        $documentation->reset();
        Mockery::close();
    }

    /**
     * @throws DuplicateIdFixtureException
     */
    public function testAddFixture(): void
    {
        $documentation = new Documentation([]);

        $fixture = $documentation->addFixture('fixtures', ['id' => 1, 'name' => 'fixture1']);

        $this->assertCount(1, $documentation->getSections());
        $this->assertSame('fixtures', $documentation->getSections()[0]->getTitle());
        $this->assertSame('fixtures-1', $fixture->getId());
    }

    /**
     * @throws DuplicateIdFixtureException
     */
    public function testAddFixtureWithSameSection(): void
    {
        $documentation = new Documentation([]);
        
        $documentation->addFixture('fixtures', ['id' => 1, 'name' => 'fixture1']);
        $documentation->addFixture('fixtures', ['id' => 2, 'name' => 'fixture2']);

        $this->assertCount(1, $documentation->getSections());
    }

    /**
     * @throws DuplicateIdFixtureException
     */
    public function testAddFixtureWithDifferentSection(): void
    {
        $documentation = new Documentation([]);
        
        $documentation->addFixture('fixtures', ['id' => 1, 'name' => 'fixture1']);
        $documentation->addFixture('other', ['id' => 1, 'name' => 'fixture1']);

        $this->assertCount(2, $documentation->getSections());
    }

    /**
     * @throws DuplicateIdFixtureException
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
        $mockFixture->shouldNotReceive('setLinks');

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
                ],
                Mockery::on(function($argument) {
                    return strpos($argument, 'Product-') !== false;
                })
            )
            ->andReturn($mockFixture);
        
        $product = (new Product())
            ->setId(1)
            ->setName('product 1')
            ->setCategory('category 1');

        $mockDocumentation->addFixtureEntity($product);
        $this->assertCount(0, $mockDocumentation->getSections());
    }

    public function testAddFixtureEntityWithPublicProperties(): void
    {
        $mockFixture = Mockery::mock(Fixture::class)
            ->makePartial();
        $mockFixture->shouldNotReceive('setLinks');

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
                ],
                Mockery::on(function($argument) {
                    return strpos($argument, 'ProductPublic-') !== false;
                })
            )
            ->andReturn($mockFixture);
        
        $product = new ProductPublic();
        $product->id = 1;
        $product->name = 'product 1';
        $product->category = 'category 1';

        $mockDocumentation->addFixtureEntity($product);
        // We expect 0 because if it has the good parameter it will be catch by the mock
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
            ->with('ProductComplex', [
                    'name' => 'product 1',
                    'category' => 'category name',
                    'tags' => 3
                ],
                Mockery::on(function($argument) {
                    return strpos($argument, 'ProductComplex-') !== false;
                })
            )
            ->andReturn($mockFixture);

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


    public function testAddFixtureEntityWithNonExistingProperty(): void
    {
        $mockFixture = Mockery::mock(Fixture::class)
            ->makePartial();
        $mockFixture->shouldNotReceive('setLinks');

        $mockDocumentation = Mockery::mock(
            Documentation::class,
            [
                ['Product' => ['names', 'category']]
            ]
        )
            ->makePartial();

        $mockDocumentation->shouldReceive('addFixture')
            ->once()
            ->with(
                'Product', [
                    'category' => 'category 1'
                ],
                Mockery::on(function($argument) {
                    return strpos($argument, 'Product-') !== false;
                })
            )
            ->andReturn($mockFixture);
        
        $product = (new Product())
            ->setId(1)
            ->setName('product 1')
            ->setCategory('category 1');

        $mockDocumentation->addFixtureEntity($product);
        $this->assertCount(0, $mockDocumentation->getSections());
    }

    public function testAddFixtureEntityWithEmptyConfigEntities(): void
    {
        $mockFixture = Mockery::mock(Fixture::class)
            ->makePartial();
        $mockFixture->shouldNotReceive('setLinks');

        $mockDocumentation = Mockery::mock(Documentation::class, [[]])
            ->makePartial();

        $mockDocumentation->shouldReceive('addFixture')
            ->once()
            ->with('Product', [
                'Id' => 1,
                'Name' => 'product 1',
                'Category' => 'category 1',
            ],
                Mockery::on(function($argument) {
                    return strpos($argument, 'Product-') !== false;
                })
            )
            ->andReturn($mockFixture);

        $product = (new Product())
            ->setId(1)
            ->setName('product 1')
            ->setCategory('category 1');

        $mockDocumentation->addFixtureEntity($product);
        $this->assertCount(0, $mockDocumentation->getSections());
    }

    public function testAddFixtureEntityWithEmptyConfigEntitiesEntity(): void
    {
        $mockFixture = Mockery::mock(Fixture::class)
            ->makePartial();
        $mockFixture->shouldNotReceive('setLinks');

        $mockDocumentation = Mockery::mock(Documentation::class, [
            ['Product' => []]
        ])
            ->makePartial();

        $mockDocumentation->shouldReceive('addFixture')
            ->once()
            ->with('Product', [
                'Id' => 1,
                'Name' => 'product 1',
                'Category' => 'category 1',
            ],
                Mockery::on(function($argument) {
                    return strpos($argument, 'Product-') !== false;
                })
            )
            ->andReturn($mockFixture);

        $product = (new Product())
            ->setId(1)
            ->setName('product 1')
            ->setCategory('category 1');

        $mockDocumentation->addFixtureEntity($product);
        $this->assertCount(0, $mockDocumentation->getSections());
    }

    public function testAddFixtureEntityWithNonEmptyConfigEntitiesEntity(): void
    {
        $mockFixture = Mockery::mock(Fixture::class)
            ->makePartial();
        $mockFixture->shouldNotReceive('setLinks');

        $mockDocumentation = Mockery::mock(Documentation::class, [
            ['Customer' => []]
        ])
            ->makePartial();

        $mockDocumentation->shouldNotReceive('addFixture');

        $product = (new Product())
            ->setId(1)
            ->setName('product 1')
            ->setCategory('category 1');

        $this->assertNull($mockDocumentation->addFixtureEntity($product));
    }

    /**
     * @throws DuplicateIdFixtureException
     */
    public function testReset(): void
    {
        $documentation = new Documentation([]);
        
        $documentation->addFixture('fixtures', ['id' => 1, 'name' => 'fixture1'], '1');
        $this->assertCount(1, $documentation->getSections());

        $documentation->reset();
        $this->assertCount(0, $documentation->getSections());
    }

    /**
     * @throws DuplicateIdFixtureException
     * @throws BadFixtureLinkException
     */
    public function testToJson(): void
    {
        $documentation = new Documentation([]);
        
        $fixture1 = $documentation->addFixture('some', ['id' => 1, 'name' => 'fixture1'], 'some-1');
        $documentation->addFixture('some', ['id' => 2, 'name' => 'fixture2'], 'some-2');
        $documentation->addFixture('others', ['id' => 1, 'pseudo' => 'autre2'], 'others-1')
            ->addLink('pseudo', $fixture1);

        $this->assertSame(
            '{"some":{"fixtures":[{"id":"some-1","data":{"id":1,"name":"fixture1"},"links":[]},{"id":"some-2","data":{"id":2,"name":"fixture2"},"links":[]}]},"others":{"fixtures":[{"id":"others-1","data":{"id":1,"pseudo":"autre2"},"links":{"pseudo":"some-1"}}]}}',
            $documentation->toJson()
        );
    }

    /**
     * @throws DuplicateIdFixtureException
     */
    public function testInit(): void
    {
        $jsonString = '{"some":{"fixtures":[{"id":"some-1","data":{"id":1,"name":"fixture1"},"links":[]},{"id":"some-2","data":{"id":2,"name":"fixture2"},"links":[]}]},"others":{"fixtures":[{"id":"others-1","data":{"id":1,"pseudo":"autre2"},"links":[]}]}}';
        
        $documentation = new Documentation([], $jsonString);
        $this->assertCount(2, $documentation->getSections());
    }

    /**
     * @throws DuplicateIdFixtureException
     */
    public function testInitEmpty(): void
    {
        $jsonString = null;

        $documentation = new Documentation([], $jsonString);
        $this->assertCount(0, $documentation->getSections());
    }

    /**
     * @throws DuplicateIdFixtureException
     * @throws BadLinkReferenceException
     */
    public function testLinkReference(): void
    {
        $documentation = new Documentation([]);
        $fixture1 = $documentation->addFixture('test', ['name' => 'Test1'], '1');
        $fixture2 = $documentation->addFixture('test', ['name' => 'Test2'], '2');

        $documentation->addLinkReference('ref1', $fixture1);
        $documentation->addLinkReference('ref2', $fixture2);
        $this->assertEquals($fixture1, $documentation->getLinkReference('ref1'));
        $this->assertEquals($fixture2, $documentation->getLinkReference('ref2'));
    }

    /**
     * @throws DuplicateIdFixtureException
     * @throws BadLinkReferenceException
     */
    public function testNotExistingLinkReference(): void
    {
        $this->expectException(BadLinkReferenceException::class);
        $documentation = new Documentation([]);

        $documentation->getLinkReference('ref1');
    }
}
