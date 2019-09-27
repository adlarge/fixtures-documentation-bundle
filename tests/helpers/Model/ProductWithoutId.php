<?php 

namespace Adlarge\FixturesDocumentationBundle\helpers\Model;

class ProductWithoutId
{
    private $name;

    private $category;

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

}