<?php

declare(strict_types=1);

namespace Adlarge\FixturesDocumentationBundle\Model;

use Adlarge\FixturesDocumentationBundle\Exception\BadLinkReferenceException;
use Adlarge\FixturesDocumentationBundle\Exception\DuplicateIdFixtureException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use TypeError;
use ReflectionClass;
use ReflectionMethod;
use ReflectionException;
use function array_key_exists;

class Documentation
{
    /**
     * List of sections in the doc.
     *
     * @var Section[]
     */
    private $sections = [];
    /**
     * @var array
     */
    private $configEntities;
    /**
     * List of linkable Fixtures.
     *
     * @var array
     */
    private $linkReferences = [];

    private $CONFIG_WITH_PROPERTIES = 'CONFIG_WITH_PROPERTIES';
    private $CONFIG_WITHOUT_PROPERTIES = 'CONFIG_WITHOUT_PROPERTIES';

    /**
     * Documentation constructor.
     *
     * @param array $configEntities
     * @param string $jsonString
     * 
     * @throws DuplicateIdFixtureException
     */
    public function __construct(array $configEntities, string $jsonString = null)
    {
        $this->configEntities = $configEntities;
        if ($jsonString) {
            $this->init($jsonString);
        }
    }

    /**
     * Create the documentation from jsonFile.
     *
     * @param string $jsonString
     * @throws DuplicateIdFixtureException
     */
    protected function init(string $jsonString): void
    {
        $doc = json_decode($jsonString, true);
        foreach ($doc as $sectionTitle => $section) {
            foreach ($section['fixtures'] as $item) {
                $this->addFixture($sectionTitle,$item['data'], $item['id'])
                    ->setLinks($item['links']);
            }
        }
    }

    /**
     * @return Section[]
     */
    public function getSections(): array
    {
        return $this->sections;
    }

    /**
     * Add a fixture to the documentation.
     *
     * @param string $sectionTitle
     * @param string $id
     * @param array $fixtureData
     * @return Fixture
     *
     * @throws DuplicateIdFixtureException
     */
    public function addFixture(string $sectionTitle, array $fixtureData, string $id=null): Fixture
    {
        if (count($fixtureData) !== count($fixtureData, COUNT_RECURSIVE)) {
            throw new TypeError('A fixture can\'t be a multidimensional array.');
        }
        $section = $this->addSection($sectionTitle);

        if ($id === null) {
            $id = $section->getNextFixtureId();
        }
        $fixture = new Fixture($id, $fixtureData);
        $section->addFixture($fixture);

        return $fixture;
    }

    /**
     * Add a fixture to the documentation when passing directly the entity.
     * Use configEntities and their property to create the array of value
     * to pass to addFixture method
     *
     * @param mixed $entity
     *
     * @return Fixture|null
     *
     * @throws DuplicateIdFixtureException
     * @throws ReflectionException
     */
    public function addFixtureEntity($entity): ?Fixture
    {
        $className = (new ReflectionClass($entity))->getShortName();
        $links = [];
        $config = $this->CONFIG_WITH_PROPERTIES;
        if (array_key_exists($className, $this->configEntities)) {
            $propertyAccessor = PropertyAccess::createPropertyAccessor();
            /** @var array $properties */
            $properties = $this->configEntities[$className];
            if (sizeof($properties) === 0){
                $config = $this->CONFIG_WITHOUT_PROPERTIES;
                $properties = (new ReflectionClass($entity))->getMethods(ReflectionMethod::IS_PUBLIC);
            }
            $fixtureData = [];
            foreach ($properties as $property) {
                try {
                    if ($config === $this->CONFIG_WITH_PROPERTIES) {
                        $value = $propertyAccessor->getValue($entity, $property);
                    } else {
                        if (strpos($property->name, 'get') !== 0) {
                            // We only took method that begin with 'get'
                            continue;
                        }
                        $value = $entity->{$property->name}();
                        $property = substr($property->name, 3, strlen($property->name) - 3);
                    }
                    if (is_scalar($value)) {
                        // For a scalar value (int, string, bool), we just display it
                        $fixtureData[$property] = $value;
                    } else if (is_array($value)) {
                        // For an array we just count the total
                        $fixtureData[$property] = count($value);
                    } else {
                        // We are in an object context
                        if (method_exists($value, '__toString')) {
                            $fixtureData[$property] = $value->__toString();
                            $propertyClassName = (new ReflectionClass($value))->getShortName();
                            // Means that the object is one of the wanted class to be documented
                            if (array_key_exists($propertyClassName, $this->configEntities)) {
                                $links[$property] = $propertyClassName . '-' . spl_object_id($value);
                            }
                        }
                    }
                } catch (NoSuchPropertyException $exception) {
                    // ignore this exception silently
                }
            }
            $fixture = $this->addFixture($className, $fixtureData, $className . '-' . spl_object_id($entity));
            if ($fixture && $links) {
                $fixture->setLinks($links);
            }

            return $fixture; 
        }
        return null;
    }


    /**
     * Reset the documentation by removing all sections.
     *
     * @return Documentation
     */
    public function reset(): self
    {
        $this->sections = [];

        return $this;
    }

    /**
     * Convert the documentation to json.
     *
     * @return string
     */
    public function toJson(): string
    {
        $doc = [];

        foreach ($this->getSections() as $section) {
            foreach ($section->getFixtures() as $fixture) {
                $doc[$section->getTitle()]['fixtures'][] = [
                    'id' => $fixture->getId(),
                    'data' => $fixture->getData(),
                    'links' => $fixture->getLinks(),
                ];
            }
        }

        return json_encode($doc);
    }

    /**
     * Add a section to the documentation
     *
     * @param string $sectionTitle
     *
     * @return Section
     */
    private function addSection(string $sectionTitle): Section
    {
        foreach ($this->sections as $section) {
            if ($section->getTitle() === $sectionTitle) {
                return $section;
            }
        }

        $section = new Section($sectionTitle);
        $this->sections[] = $section;

        return $section;
    }

    /**
     * Add a linkable fixture reference.
     *
     * @param string  $refName
     * @param Fixture $fixture
     *
     * @return Documentation
     */
    public function addLinkReference(string $refName, Fixture $fixture): self
    {
        $this->linkReferences[$refName] = $fixture;

        return $this;
    }

    /**
     * Get a fixture with the reference.
     *
     * @param string $refName
     *
     * @return Fixture
     *
     * @throws BadLinkReferenceException
     */
    public function getLinkReference(string $refName): Fixture
    {
        if (!isset($this->linkReferences[$refName])) {
            throw new BadLinkReferenceException(
                "No reference found for {$refName}"
            );
        }

        return $this->linkReferences[$refName];
    }
}
