<?php
declare(strict_types=1);

namespace Adlarge\FixturesDocumentationBundle\EventListener;

use Adlarge\FixturesDocumentationBundle\Service\FixturesDocumentationManager;
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
     * @var string $listenedCommand
     */
    private $listenedCommand;

    /**
     * FixturesDocumentationListener constructor.
     *
     * @param FixturesDocumentationManager $fixturesDocumentationManager
     * @param string $listenedCommand
     */
    public function __construct(
        FixturesDocumentationManager $fixturesDocumentationManager,
        string $listenedCommand
    ) {
        $this->fixturesDocumentationManager = $fixturesDocumentationManager;
        $this->listenedCommand = $listenedCommand;
    }

    /**
     * Remove existing documentation before fixtures are loaded
     *
     * @param ConsoleCommandEvent $event
     */
    public function onCommandExecution(ConsoleCommandEvent $event): void
    {
        if ($event->getCommand() && $event->getCommand()->getName() === $this->listenedCommand) {
            $this->fixturesDocumentationManager->reset();
        }
    }

    /**
     * Save the documentation after fixtures loading
     *
     * @param ConsoleTerminateEvent $event
     */
    public function onTerminateExecution(ConsoleTerminateEvent $event): void
    {
        if ($event->getCommand() && $event->getCommand()->getName() === $this->listenedCommand) {
            $this->fixturesDocumentationManager->save();
        }
    }
}
