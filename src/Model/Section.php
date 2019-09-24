<?php
declare(strict_types=1);

namespace Adlarge\FixturesDocumentationBundle\Model;

use Adlarge\FixturesDocumentationBundle\Exception\DuplicateIdFixtureException;

class Section
{
    /**
     * Section title.
     *
     * @var string
     */
    private $title;
    /**
     * Section fixtures list.
     *
     * @var Fixture[]|array
     */
    private $fixtures = [];
    /**
     * Section headers.
     *
     * @var array
     */
    private $headers = [];

    /**
     * Section constructor.
     *
     * @param string $title
     */
    public function __construct(string $title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return Fixture[]|array
     */
    public function getFixtures(): array
    {
        return $this->fixtures;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Add a fixture in the section.
     *
     * @param Fixture $newFixture
     *
     * @throws DuplicateIdFixtureException
     */
    public function addFixture(Fixture $newFixture): void
    {
        foreach ($this->fixtures as $fixture) {
            if ($fixture->getId() === $newFixture->getId()) {
                throw new DuplicateIdFixtureException(
                    "Duplicate fixture id in section {$this->title} : {$newFixture->getid()}"
                );
            }
        }

        $this->fixtures[] = $newFixture;
        $this->updateHeaders($newFixture->getData());
    }

    /**
     * Update headers with a given fixture.
     *
     * @param array $newFixture
     */
    private function updateHeaders(array $newFixture): void
    {
        $this->headers = array_unique(array_merge(
            $this->headers,
            array_keys($newFixture))
        );
    }

    /**
     * Generate next section fixture ID.
     *
     * @return string
     */
    public function getNextFixtureId(): string
    {
        $fixtureNumber = count($this->fixtures) + 1;
        return "{$this->getTitle()}-{$fixtureNumber}";
    }
}
