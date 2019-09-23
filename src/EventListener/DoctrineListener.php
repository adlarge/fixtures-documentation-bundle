<?php


namespace Adlarge\FixturesDocumentationBundle\EventListener;

use Adlarge\FixturesDocumentationBundle\Exception\DuplicateIdFixtureException;
use Adlarge\FixturesDocumentationBundle\Service\FixturesDocumentationManager;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use ReflectionException;

class DoctrineListener
{
    /** @var FixturesDocumentationManager $documentationManager */
    private $documentationManager;

    /** @var bool $enableAutoDocumentation */
    private $enableAutoDocumentation;

    public function __construct(FixturesDocumentationManager $documentationManager, bool $enableAutoDocumentation)
    {
        $this->documentationManager = $documentationManager;
        $this->enableAutoDocumentation = $enableAutoDocumentation;
    }

    /**
     * @param LifecycleEventArgs $args
     * @throws ReflectionException
     * @throws DuplicateIdFixtureException
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        if ($this->documentationManager->isListening() && $this->enableAutoDocumentation) {
            $entity = $args->getObject();
            $this->documentationManager->getDocumentation()->addFixtureEntity($entity);
        }
    }
}