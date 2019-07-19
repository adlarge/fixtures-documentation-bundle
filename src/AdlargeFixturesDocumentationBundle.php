<?php

namespace Adlarge\FixturesDocumentationBundle;

use Adlarge\FixturesDocumentationBundle\DependencyInjection\FixturesDocumentationExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @codeCoverageIgnore
 */
class AdlargeFixturesDocumentationBundle extends Bundle
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
