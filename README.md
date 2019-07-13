Fixtures Documentation Bundle
=========
This Symfony bundle generates and exposes a documentation of your fixtures.
An action to reload your fixtures can also be configured.

The goal of this bundle is to allow testers to be independent, they can see data and reload fixtures when they want to test again.

## Installation

This is installable via [Composer](https://getcomposer.org/) as
[adlarge/fixtures-documentation](https://packagist.org/packages/adlarge/fixtures-documentation):

    composer require --dev adlarge/fixtures-documentation

The default url to access the documentation is **/fixtures/doc**

## Configuration

    fixtures_documentation:
        title: 'Your title'
        reloadCommands:
            - php bin/console doctrine:fixtures:load
            - ....

## Example

To add fixtures to your documentation you have to get the manager in your fixtures file :

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
 
         $doc->addFixture('Products', [
             'id' => 1,
             'name' => 'Product 1',
         ]);
         $doc->addFixture('Products', [
             'id' => 2,
             'name' => 'Product 2',
         ]);
         $doc->addFixture('Customers', [
             'id' => 1,
             'first name' => 'John',
             'last name' => 'Doe',
             'email' => 'john.doe@test.fr'
         ]);
 
         $manager->flush();
     }
 }
```

Then to generate the doc you only have to run : 

    php bin/console doctrine:fixtures:load

## Result

![GitHub Logo](/doc/img/fixtures-documentation.png)
