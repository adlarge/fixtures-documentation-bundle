<?php 

namespace Adlarge\FixturesDocumentationBundle\helpers\Model;

class ProductWithGetters
{
    private $id;

    private $name;

    private $category;

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;
        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getUniqId(): string
    {
        return 'uniqid';
    }

    public function getWithParameters(int $parameter): string
    {
        return 'shouldnt be called';
    }

    private function getPrivate(): string
    {
        return 'shouldnt be called';
    }

}