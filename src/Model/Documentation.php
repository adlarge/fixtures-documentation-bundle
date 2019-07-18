<?php

declare(strict_types=1);

namespace Adlarge\FixturesDocumentationBundle\Model;

use Adlarge\FixturesDocumentationBundle\Exception\DuplicateFixtureException;
use TypeError;

class Documentation
{
    /**
     * List of sections in the doc.
     *
     * @var Section[]
     */
    private $sections = [];

    /**
     * Documentation constructor.
     *
     * @param string $jsonString
     * @throws DuplicateFixtureException
     */
    public function __construct(string $jsonString = null)
    {
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
