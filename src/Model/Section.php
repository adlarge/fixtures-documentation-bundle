<?php
declare(strict_types=1);

namespace FixturesDocumentation\Model;

use FixturesDocumentation\Exception\DuplicateFixtureException;

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
     * @var array
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
     * @return array
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
     * @return Section
     *
     * @throws DuplicateFixtureException
     */
    public function addFixture(array $newFixture): self
    {
        foreach ($this->fixtures as $fixture) {
            if (empty(array_diff_assoc($fixture, $newFixture))) {
                $values = implode(',', $newFixture);
                throw new DuplicateFixtureException(
                    "Duplicate fixture in section {$this->title} : {$values}"
                );
            }
        }

        $this->fixtures[] = $newFixture;
        $this->updateHeaders($newFixture);

        return $this;
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
}
