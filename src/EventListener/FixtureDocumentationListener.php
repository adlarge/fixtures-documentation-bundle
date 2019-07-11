<?php
declare(strict_types=1);

namespace FixturesDoc\EventListener;

use FixturesDoc\Service\FixtureDocumentationManager;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;

class FixtureDocumentationListener
{
    /**
     * @var FixtureDocumentationManager
     */
    private $fixtureDocumentationManager;

    /**
     * FixtureDocumentationListener constructor.
     *
     * @param FixtureDocumentationManager $fixtureDocumentationManager
     */
    public function __construct(
        FixtureDocumentationManager $fixtureDocumentationManager
    ) {
        $this->fixtureDocumentationManager = $fixtureDocumentationManager;
    }

    /**
     * Remove existing documentation before fixtures are loaded
     *
     * @param ConsoleCommandEvent $event
     */
    public function onCommandExecution(ConsoleCommandEvent $event): void
    {
        if ($event->getCommand()->getName() === 'doctrine:fixtures:load') {
            $this->fixtureDocumentationManager->deleteDocumentation();
        }
    }

    /**
     * Save the documentation after fixtures loading
     *
     * @param ConsoleTerminateEvent $event
     */
    public function onTerminateExecution(ConsoleTerminateEvent $event): void
    {
        if ($event->getCommand()->getName() === 'doctrine:fixtures:load') {
            $this->fixtureDocumentationManager->saveToFile();
        }
    }
}
