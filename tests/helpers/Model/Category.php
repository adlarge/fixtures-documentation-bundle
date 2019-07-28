<?php 

namespace Adlarge\FixturesDocumentationBundle\helpers\Model;

class Category
{
    public $id;

    public $name;

    public $visibility;

    public function __toString(): string
    {
        return $this->name;
    }
}