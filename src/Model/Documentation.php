<?php

declare(strict_types=1);

namespace Adlarge\FixturesDocumentationBundle\Model;

use Adlarge\FixturesDocumentationBundle\Exception\BadFixtureLinkException;
use Adlarge\FixturesDocumentationBundle\Exception\BadLinkReferenceException;
use Adlarge\FixturesDocumentationBundle\Exception\DuplicateFixtureException;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use TypeError;
use ReflectionClass;
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

    /**
     * Documentation constructor.
     *
     * @param array $configEntities
     * @param string $jsonString
     * 
     * @throws DuplicateFixtureException
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
     * @throws DuplicateFixtureException
     */
    protected function init(string $jsonString): void
    {
        $doc = json_decode($jsonString, true);
        foreach ($doc as $sectionTitle => $section) {
            foreach ($section['fixtures'] as $item) {
                $this->addFixture($sectionTitle, $item['data'])
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
     * @param array  $fixture
     *
     * @return Fixture
     *
     * @throws DuplicateFixtureException
     */
    public function addFixture(string $sectionTitle, array $fixture): Fixture
    {
        if (count($fixture) !== count($fixture, COUNT_RECURSIVE)) {
            throw new TypeError('A fixture can\'t be a multidimensional array.');
        }
        $section = $this->addSection($sectionTitle);

        return $section->addFixture($fixture);
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
     * @throws DuplicateFixtureException
     * @throws ReflectionException
     */
    public function addFixtureEntity($entity): ?Fixture
    {
        $className = (new ReflectionClass($entity))->getShortName();
        if (array_key_exists($className, $this->configEntities)) {
            $propertyAccessor = PropertyAccess::createPropertyAccessor();
            /** @var array $properties */
            $properties = $this->configEntities[$className];
            $fixture = [];
            foreach ($properties as $property) {
                try {
                    $value = $propertyAccessor->getValue($entity, $property);
                    if (is_scalar($value)) {
                        $fixture[$property] = $value;
                    } else if (is_array($value)) {
                        $fixture[$property] = count($value);
                    } else {
                        if (method_exists($value, '__toString')) {
                            $fixture[$property] = $value->__toString();
                        }
                    }
                } catch (NoSuchPropertyException $exception) {
                    // ignore this exception silently
                }
            }

            return $this->addFixture($className, $fixture);
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
