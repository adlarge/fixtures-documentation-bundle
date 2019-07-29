<?php

namespace Tests\Model;

use Adlarge\FixturesDocumentationBundle\EventListener\DoctrineListener;
use Adlarge\FixturesDocumentationBundle\Model\Documentation;
use Adlarge\FixturesDocumentationBundle\Service\FixturesDocumentationManager;
use Mockery;
use PHPUnit\Framework\TestCase;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Adlarge\FixturesDocumentationBundle\Exception\DuplicateFixtureException;
use ReflectionException;

class DoctrineListenerTest extends TestCase
{
    public function tearDown()
    {
        $this->addToAssertionCount(
            Mockery::getContainer()->mockery_getExpectationCount()
        );
    }

    /**
     * @throws DuplicateFixtureException
     * @throws ReflectionException
     */
    public function testCallIfIsListening(): void
    {
        $mockDocumentation = Mockery::mock(Documentation::class);
        $mockDocumentation->shouldReceive('addFixtureEntity')
            ->once();

        $mockService = Mockery::mock(FixturesDocumentationManager::class);
        $mockService->shouldReceive('getDocumentation')
            ->once()
            ->andReturn($mockDocumentation);
        $mockService->shouldReceive('isListening')
            ->once()
            ->andReturn(true);

        $mockEvent = Mockery::mock(LifecycleEventArgs::class);
        $mockEvent->shouldReceive('getObject')
            ->once()
            ->andReturn(1);

        $listener = new DoctrineListener($mockService, true);

        $listener->postPersist($mockEvent);
    }

    /**
     * @throws DuplicateFixtureException
     * @throws ReflectionException
     */
    public function testCallIfIsntListening(): void
    {
        $mockDocumentation = Mockery::mock(Documentation::class);
        $mockDocumentation->shouldReceive('addFixtureEntity')
            ->never();

        $mockService = Mockery::mock(FixturesDocumentationManager::class);
        $mockService->shouldReceive('getDocumentation')
            ->never();
        $mockService->shouldReceive('isListening')
            ->once()
            ->andReturn(false);

        $mockEvent = Mockery::mock(LifecycleEventArgs::class);
        $mockEvent->shouldReceive('getObject')
            ->never();

        $listener = new DoctrineListener($mockService, true);

        $listener->postPersist($mockEvent);
    }

    /**
     * @throws DuplicateFixtureException
     * @throws ReflectionException
     */
    public function testCallIfIsntEnabled(): void
    {
        $mockDocumentation = Mockery::mock(Documentation::class);
        $mockDocumentation->shouldReceive('addFixtureEntity')
            ->never();

        $mockService = Mockery::mock(FixturesDocumentationManager::class);
        $mockService->shouldReceive('getDocumentation')
            ->never();
        $mockService->shouldReceive('isListening')
            ->once()
            ->andReturn(true);

        $mockEvent = Mockery::mock(LifecycleEventArgs::class);
        $mockEvent->shouldReceive('getObject')
            ->never();

        $listener = new DoctrineListener($mockService, false);

        $listener->postPersist($mockEvent);
    }
}
