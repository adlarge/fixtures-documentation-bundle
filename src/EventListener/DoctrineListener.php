<?php


namespace Adlarge\FixturesDocumentationBundle\EventListener;

use Adlarge\FixturesDocumentationBundle\Service\FixturesDocumentationManager;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use ReflectionException;
use Adlarge\FixturesDocumentationBundle\Exception\DuplicateFixtureException;

class DoctrineListener
{
    /** @var FixturesDocumentationManager $documentationManager */
    private $documentationManager;

    public function __construct(FixturesDocumentationManager $documentationManager)
    {
        $this->documentationManager = $documentationManager;
    }

    /**
     * @param LifecycleEventArgs $args
     * @throws DuplicateFixtureException
     * @throws ReflectionException
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        $this->documentationManager->getDocumentation()->addFixtureEntity($entity);

    }
}