<?php
declare(strict_types=1);

namespace FixturesDocumentation\EventListener;

use FixturesDocumentation\Service\FixturesDocumentationManager;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;

/**
 * @codeCoverageIgnore
 */
class FixturesDocumentationListener
{
    /**
     * @var FixturesDocumentationManager
     */
    private $fixturesDocumentationManager;

    /**
     * FixturesDocumentationListener constructor.
     *
     * @param FixturesDocumentationManager $fixturesDocumentationManager
     */
    public function __construct(
        FixturesDocumentationManager $fixturesDocumentationManager
    ) {
        $this->fixturesDocumentationManager = $fixturesDocumentationManager;
    }

    /**
     * Remove existing documentation before fixtures are loaded
     *
     * @param ConsoleCommandEvent $event
     */
    public function onCommandExecution(ConsoleCommandEvent $event): void
    {
        if ($event->getCommand()->getName() === 'doctrine:fixtures:load') {
            $this->fixturesDocumentationManager->deleteDocumentation();
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
            $this->fixturesDocumentationManager->saveToFile();
        }
    }
}
