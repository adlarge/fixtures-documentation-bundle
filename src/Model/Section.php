<?php
declare(strict_types=1);

namespace Adlarge\FixturesDocumentationBundle\Model;

use Adlarge\FixturesDocumentationBundle\Exception\DuplicateFixtureException;

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
     * @param string $title
     *
     * @return Section
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Add a fixture in the section.
     *
     * @param array $newFixture
     *
     * @return Fixture
     *
     * @throws DuplicateFixtureException
     */
    public function addFixture(array $newFixture): Fixture
    {
        foreach ($this->fixtures as $fixture) {
            if (empty(array_diff_assoc($fixture->getData(), $newFixture))) {
                $values = implode(',', $newFixture);
                throw new DuplicateFixtureException(
                    "Duplicate fixture in section {$this->title} : {$values}"
                );
            }
        }

        $fixture = new Fixture($this->getNextFixtureId(), $newFixture);

        $this->fixtures[] = $fixture;
        $this->updateHeaders($newFixture);

        return $fixture;
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
    private function getNextFixtureId(): string
    {
        $fixtureNumber = count($this->fixtures) + 1;

       return "{$this->getTitle()}-{$fixtureNumber}";
    }
}
