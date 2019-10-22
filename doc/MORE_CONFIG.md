# Other configurations

You can set different configurations to manage the bundle at your needs adn choose globally
wich entities and which of their properties will be documented.

All following configurations will use the same example with

* 1 Customer, John Doe with
    * id
    * firstname
    * lastname
    * email
* 2 Products, linked to John Doe with
    * id
    * name
    * tags
    * owner
 
## Use of the configEntities option

### Empty configEntities option

if the option is present but empty, it will be the same result as without the option

```yaml
    adlarge_fixtures_documentation:
        title: 'Your title'
        reloadCommands:
            - php bin/console doctrine:fixtures:load
        configEntities:
```

![Auto configuration](/doc/img/fixtures-documentation-empty.png)

### Configure only entities

If you provide the entities names, only these entities will be documented but with all their accessible properties

It will take all public methods starting with 'get' and use them to document each entity.

Example :

with configuration

```yaml
    adlarge_fixtures_documentation:
        title: 'Your title'
        reloadCommands:
            - php bin/console doctrine:fixtures:load
        configEntities:
            Product:
```

With the following class

```php
class Product
{
    private $id;

    private $name;

    private $category;
    
    private $tags; 

    // Here you have setters of the class
    // ...

    // Here the getters
    private function getId(): int
    {
        return $this->id;
    }

    public function getUniqueId(): string
    {
        return uniqid();
    }

    public function getIdWithParameter(string $disciminant): string
    {
        return str($this->id) . $discriminant;
    }


    public function getName(): string
    {
        return $this->name;
    }
    
    public function getTags(): array
    {
        return $this->tags;
    }
}
```

with the classic example, you'll see the same

* minus category from product because it doesn't have a getter
* plus uniqueId because it's a public function that starts with 'get'
* minus getIdWithParameter because it has parameters
* minus the links towards customers because Customer entity won't be documented
* minus Customer entities, the option being absent in configuration, it won't be documented

Result :

![Entities configuration](/doc/img/fixtures-documentation-entities.png)

N.B. : In this configuration, only the links toward declared entities will be present.

### Configure entities and their properties

If you provide in detail the properties you want for an entities, only these properties of theses entities will be documented

It will parse scalar properties and can check public properties as well as private ones with a getter (property, getProperty(), hasProperty(), isProperty()).
It will parse non scalar properties as well, if it's an array it will display the count, if it's an entity it will display the result of __toString if it exists.
It will ignore non existing properties.

With the following configuration :

```yaml
    adlarge_fixtures_documentation:
        title: 'Your title'
        reloadCommands:
            - php bin/console doctrine:fixtures:load
        configEntities:
            Product:
                - name
                - category
                - owner
            Customer:
                - firstname
                - lastname
```

Result :

![Detailed configuration](/doc/img/fixtures-documentation-detailed.png)

N.B. : In this configuration, only the links toward configured entities will be present.

### Mixed

Of course, you can mix both types of configurations

```yaml
    adlarge_fixtures_documentation:
        title: 'Your title'
        reloadCommands:
            - php bin/console doctrine:fixtures:load
        configEntities:
            Product:
                - name
                - category
                - owner
            Customer:
```

Result :

![Mixed configuration](/doc/img/fixtures-documentation-detailed.png)

## Add entities manually

You can choose to select which entities instance you want in your doc, to keep only the most important ones

## Add fixtures manually with addFixtureEntity

Still configured with the `configEntities` option, you can use the method `addFixtureEntity` to add an instance manually without Doctrine or Alice listener.
You have to get the manager in your fixtures file :

```php
class AppFixtures extends Fixture
{
    /**
     * @var FixturesDocumentationManager
     */
    private $documentationManager;

    /**
     * AppFixtures constructor.
     *
     * @param FixturesDocumentationManager $documentationManager
     */
    public function __construct(FixturesDocumentationManager $documentationManager)
    {
        $this->documentationManager = $documentationManager;
    }

    /**
     * @param ObjectManager $manager
     *
     * @throws DuplicateFixtureException
     */
    public function load(ObjectManager $manager)
    {
        $doc = $this->documentationManager->getDocumentation();
        
        $customer = (new Customer())
            ->setId(1)
            ->setFirstname('John')
            ->setLastname('Doe')
            ->setEmail('john.doe@test.fr');

        $doc->addFixtureEntity($customer);

        $product1 = (new Product())
            ->setId(1)
            ->setName("Product 1")
            ->setCategory("Category 1")
            ->setTags(['tag1', 'tag2'])
            ->setOwner($customer);

        $doc->addFixtureEntity($product1);

        $product2 = (new Product())
            ->setId(2)
            ->setName("Product 2")
            ->setCategory("Category 2")
            ->setTags(['tag1', 'tag3', 'tag4'])
            ->setOwner($customer);

    }
}
```

With this example, you won't have product 2 in your documentation because only product1 was added via addFixtureEntity

Result :

![Mixed configuration](/doc/img/fixtures-documentation-addfixtureentity.png)

N.B : It depends on your configEntities. If you call addFixtureEntity on an instance whom he class is not configured, it won't be documented

## Add fixtures manually with AddFixture

To add fixtures to your documentation directly you have to get the manager in your fixtures file :

```php
class AppFixtures extends Fixture
{
    /**
     * @var FixturesDocumentationManager
     */
    private $documentationManager;

    /**
     * AppFixtures constructor.
     *
     * @param FixturesDocumentationManager $documentationManager
     */
    public function __construct(FixturesDocumentationManager $documentationManager)
    {
        $this->documentationManager = $documentationManager;
    }

    /**
     * @param ObjectManager $manager
     *
     * @throws DuplicateFixtureException
     */
    public function load(ObjectManager $manager)
    {
        $doc = $this->documentationManager->getDocumentation();

        $doc->addFixture('Customer', [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john.doe@test.fr'
        ]);

        $doc->addFixture('Products', [
            'name' => 'Product 1',
            'owner' => 'John Doe'
        ]);

        $doc->addFixture('Products', [
            'name' => 'Product 2',
            'owner' => 'John Doe'
        ]);
    }
}
```

Result :

![GitHub Logo](/doc/img/fixtures-documentation-manual.png)

N.B. : With this way, you can add whatever you want in your documentation, unrelated to what you have in your fixtures or database

### Link fixtures manually

It's possible to link fixtures between them, for example, if we have a list of comments with an author field that represent a user, we can link fixtures like this :

```php
class AppFixtures extends Fixture
{
    /**
     * @var FixturesDocumentationManager
     */
    private $documentationManager;

    /**
     * AppFixtures constructor.
     *
     * @param FixturesDocumentationManager $documentationManager
     */
    public function __construct(FixturesDocumentationManager $documentationManager)
    {
        $this->documentationManager = $documentationManager;
    }

    /**
     * @param ObjectManager $manager
     *
     * @throws DuplicateFixtureException
     */
    public function load(ObjectManager $manager)
    {
        $doc = $this->documentationManager->getDocumentation();        

        $userFixture = $doc->addFixture('Users', [
            'first name' => 'John',
            'last name' => 'Doe',
            'email' => 'john.doe@test.fr'
        ]);
        $doc->addFixture('Product', [
            'name' => 'Product 1',
            'author' => 'John Doe',
        ])
            ->addLink('author', $userFixture);

        $manager->flush();
    }
}
``` 

The `addLink` method needs the field on which we want to create the link and the Fixture we want to link to.

Result :

![GitHub Logo](/doc/img/fixtures-documentation-link.png)

### Sharing fixtures

It's possible to share fixtures between files. For this two methods are available on the Documentation object :

* addLinkReference('ref', $fixture)
* getLinkReference('ref')

### Mix manual and auto documentation

Of course you can mix these behaviors together : have all of your entities automatically documented and add a Section with some information manually

```php
    $doc = $this->documentationManager->getDocumentation();

    $john = (new Customer())
        ->setFirstname('John')
        ->setLastname('Doe')
        ->setEmail('john.doe@test.fr');

    $manager->persist($john);
    $customer = $doc->addFixtureEntity($john);
    $product = (new Product())
        ->setName("Product 1")
        ->setCategory("Category 1")
        ->setOwner($john)
        ->setTags(['tag1', 'tag2']);
    $product1 = $doc->addFixtureEntity($product);

    $product = (new Product())
        ->setName("Product 2")
        ->setCategory("Category 2")
        ->setOwner($john)
        ->setTags(['tag1', 'tag3']);
    $product2 = $doc->addFixtureEntity($product);

    $manager->persist($product);

    $doc->addFixture(' Most Important', [
        'name' => 'Main customer',
        'title' => 'John Doe',
        'link' => 'link'
    ])->addLink('link', $customer);

    $doc->addFixture(' Most Important', [
        'name' => 'Main product',
        'title' => 'Product 1',
        'link' => 'link'
    ])->addLink('link', $product1);

    $doc->addFixture(' Second Most Important', [
        'title' => 'version',
        'value' => '2.35.15'
    ]);

    $doc->addFixture(' Second Most Important', [
        'title' => 'token',
        'value' => 'A2G58TJD96YGD1SD&DFF#FD1123$FD%RD45'
    ]);
    
    $manager->flush();
```

Result :

![GitHub Logo](/doc/img/fixtures-documentation-custom.png)
