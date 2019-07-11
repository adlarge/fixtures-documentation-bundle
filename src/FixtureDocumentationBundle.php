<?php

namespace FixturesDoc;

use FixturesDoc\DependencyInjection\FixtureDocumentationExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class FixtureDocumentationBundle extends Bundle
{
    /**
     * @return FixtureDocumentationExtension
     */
    public function getContainerExtension(): FixtureDocumentationExtension
    {
        if (null === $this->extension) {
            $this->extension = new FixtureDocumentationExtension();
        }

        return $this->extension;
    }
}
