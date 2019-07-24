<?php 

namespace Adlarge\FixturesDocumentationBundle\helpers\Model;

use Adlarge\FixturesDocumentationBundle\helpers\Model\Category;

class ProductComplex
{
    private $id;

    private $name;

    private $category;

    private $tags;

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

    public function setCategory(Category $category): self
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

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function setTags(array $tags): self
    {
        $this->tags = $tags;
        return $this;
    }

    public function getTags(): array
    {
        return $this->tags;
    }
}