<?php

namespace FixturesDocumentation;

use FixturesDocumentation\DependencyInjection\FixturesDocumentationExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @codeCoverageIgnore
 */
class FixturesDocumentationBundle extends Bundle
{
    /**
     * @return FixturesDocumentationExtension
     */
    public function getContainerExtension(): FixturesDocumentationExtension
    {
        if (null === $this->extension) {
            $this->extension = new FixturesDocumentationExtension();
        }

        return $this->extension;
    }
}
