<?php

declare(strict_types=1);

namespace Adlarge\FixturesDocumentationBundle\Model;

use Adlarge\FixturesDocumentationBundle\Exception\DuplicateFixtureException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use TypeError;
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
     * Documentation constructor.
     *
     * @param string $jsonString
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
                $this->addFixture($sectionTitle, $item);
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
     * @param array $fixture
     *
     * @return Documentation
     *
     * @throws DuplicateFixtureException
     */
    public function addFixture(string $sectionTitle, array $fixture): self
    {
        if (count($fixture) !== count($fixture, COUNT_RECURSIVE)) {
            throw new TypeError('A fixture can\'t be a multidimensional array.');
        }
        $section = $this->addSection($sectionTitle);
        $section->addFixture($fixture);

        return $this;
    }

    public function addFixtureEntity(object $entity): self
    {
        $className = (new \ReflectionClass($entity))->getShortName();
        if (array_key_exists($className, $this->configEntities)) {
            $propertyAccessor = PropertyAccess::createPropertyAccessor();
            /** @var array $properties */
            $properties = $this->configEntities[$className];
            $fixture = [];
            foreach ($properties as $property) {
                $value = $propertyAccessor->getValue($entity, $property);
                $fixture[$property] = $value;
            }

            $this->addFixture($className . 's', $fixture);
        }
        return $this;
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
                $doc[$section->getTitle()]['fixtures'][] = $fixture;
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
}
