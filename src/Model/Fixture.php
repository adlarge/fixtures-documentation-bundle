<?php

declare(strict_types=1);

namespace Adlarge\FixturesDocumentationBundle\Model;

use Adlarge\FixturesDocumentationBundle\Exception\BadFixtureLinkException;

class Fixture
{
    /**
     * The Fixture ID.
     *
     * @var string
     */
    private $id;
    /**
     * The Fixture data.
     *
     * @var array
     */
    private $data;
    /**
     * The Fixture links with other fixtures.
     *
     * @var array
     */
    private $links = [];

    /**
     * Fixture constructor.
     *
     * @param string $id
     * @param array  $data
     */
    public function __construct(string $id, array $data)
    {
        $this->id = $id;
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * @param array|null $links
     *
     * @return Fixture
     */
    public function setLinks(?array $links): self
    {
        $this->links = $links;

        return $this;
    }

    /**
     * Add a link to an other Fixture.
     *
     * @param string $field
     * @param self   $fixtureToLink
     *
     * @return Fixture
     *
     * @throws BadFixtureLinkException
     */
    public function addLink(string $field, Fixture $fixtureToLink): self
    {
        if (!isset($this->data[$field])) {
            throw new BadFixtureLinkException(
                "The field {$field} does not exist in fixture"
            );
        } elseif (isset($this->links[$field])) {
            throw new BadFixtureLinkException(
                "The field {$field} is already linked to the fixture {$this->links[$field]}"
            );
        }

        $this->links[$field] = $fixtureToLink->getId();

        return $this;
    }
}
