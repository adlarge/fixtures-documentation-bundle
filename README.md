Fixtures Documentation Bundle
=========

[![Package version](https://img.shields.io/packagist/v/adlarge/fixtures-documentation-bundle.svg?style=flat-square)](https://packagist.org/packages/adlarge/fixtures-documentation-bundle)
[![Build Status](https://travis-ci.org/adlarge/fixtures-documentation-bundle.svg?branch=master&style=flat-square)](https://travis-ci.org/adlarge/fixtures-documentation-bundle?branch=master)
[![Coverage Status](https://coveralls.io/repos/github/adlarge/fixtures-documentation-bundle/badge.svg?branch=master)](https://coveralls.io/github/adlarge/fixtures-documentation-bundle?branch=master)
[![License](https://img.shields.io/badge/license-MIT-red.svg?style=flat-square)](LICENSE)

This Symfony bundle generates and exposes a documentation of your fixtures.
An action to reload your fixtures can also be configured.

The goal of this bundle is to allow testers to be independent, they can see data and reload fixtures when they want to test again.

## Installation

This is installable via [Composer](https://getcomposer.org/) as
[adlarge/fixtures-documentation-bundle](https://packagist.org/packages/adlarge/fixtures-documentation-bundle):

    composer require --dev adlarge/fixtures-documentation-bundle

The default url to access the documentation is **/fixtures/doc**

## Configuration

Add the bundle to your `config/bundles.php` :

    return [
        // ...
        Adlarge\FixturesDocumentationBundle\AdlargeFixturesDocumentationBundle::class => ['dev' => true],
        // ...
    ];

Add the routing file `config/routes/dev/adlarge_fixtures_documentation.yaml` and paste the following content :

    AdlargeFixturesDocumentation:
        resource: '@AdlargeFixturesDocumentationBundle/Resources/config/routing.yml'

You can define vars by creating the file `config/packages/dev/adlarge_fixtures_documentation.yaml` :

    adlarge_fixtures_documentation:
        title: 'Your title'
        reloadCommands:
            - php bin/console doctrine:fixtures:load
            - ....
        entities:
            Product:
                - id
                - name
                - category
            Customer:
                - firstname
                - lastname

Then you can install assets :

    php bin/console assets:install --symlink

## Example

To add fixtures to your documentation you have to get the manager in your fixtures file :

### Adding fixtures manually

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

### Adding fixtures with configuration and entity

If you provided the good entity name and properties in configuration `entities` you can 
use the method `addFixtureEntity`.
It only parse scalar properties and can check public properties as well as private ones with a getter (property, getProperty(), hasProperty(), isProperty()).
It will ignore non scalar properties as well as non existing ones.

With the following configuration :

```yaml
    adlarge_fixtures_documentation:
        title: 'Your title'
        reloadCommands:
            - php bin/console doctrine:fixtures:load
        entities:
            Product:
                - name
                - category
            Customer:
                - firstname
                - lastname
                - email
```

You can use 

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
 
        $product = (new Product())
            ->setName("Product 1")
            ->setCategory("Category 1");

        $doc->addFixtureEntity($product);

        $product = (new Product())
            ->setName("Product 2")
            ->setCategory("Category 2");

        $doc->addFixtureEntity($product);

        $customer = (new Customer())
            ->setFirstname('John')
            ->setLastname('Doe')
            ->setEmail('john.doe@test.fr');

        $doc->addFixtureEntity($customer);
 
        $manager->flush();
     }
 }
```

Then to generate the doc you only have to run : 

    php bin/console doctrine:fixtures:load

## Result

![GitHub Logo](/doc/img/fixtures-documentation.png)
