<?php

declare(strict_types=1);

namespace FixturesDocumentation\Model;

use FixturesDocumentation\Exception\DuplicateFixtureException;
use TypeError;

class Documentation
{
    private static $_instance;

    /**
     * List of sections in the doc.
     *
     * @var Section[]
     */
    private $sections = [];

    /**
     * This class is a singleton use this method to get an instance.
     *
     * @param string $jsonFilePath
     *
     * @return Documentation
     *
     * @throws DuplicateFixtureException
     */
    public static function getInstance(string $jsonFilePath): self
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new Documentation($jsonFilePath);
        }

        return self::$_instance;
    }

    /**
     * Documentation constructor.
     *
     * @param string $jsonFilePath
     *
     * @throws DuplicateFixtureException
     */
    private function __construct(string $jsonFilePath)
    {
        $this->init($jsonFilePath);
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
     * @param string $section
     * @param array  $fixture
     *
     * @return Documentation
     *
     * @throws DuplicateFixtureException
     */
    public function addFixture(string $section, array $fixture): self
    {
        if (count($fixture) !== count($fixture, COUNT_RECURSIVE)) {
            throw new TypeError('A fixture can\'t be a multidimensional array.');
        }

        $section = $this->addSection($section);
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
     * Create the documentation from jsonFile.
     *
     * @param string $jsonFilePath
     *
     * @throws DuplicateFixtureException
     */
    private function init(string $jsonFilePath): void
    {
        if (is_file($jsonFilePath)) {
            $doc = json_decode(file_get_contents($jsonFilePath), true);

            foreach ($doc as $sectionTitle => $section) {
                foreach ($section['fixtures'] as $item) {
                    $this->addFixture($sectionTitle, $item);
                }
            }
        }
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
