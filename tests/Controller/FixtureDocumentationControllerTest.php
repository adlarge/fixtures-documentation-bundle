<?php

namespace Tests\Model;

use Adlarge\FixturesDocumentationBundle\Service\FixturesDocumentationManager;
use Adlarge\FixturesDocumentationBundle\Controller\FixturesDocumentationController;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use RuntimeException;
use Exception;
use Mockery;

class FixtureDocumentationControllerTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testReloadAction(): void
    {
        $mockManager = Mockery::mock(FixturesDocumentationManager::class);
        $mockManager->shouldReceive('reload')
            ->once()
            ->andReturn(1);

        $mockController = Mockery::mock(
            FixturesDocumentationController::class,
            [
                $mockManager,
                'title',
                []
            ]
        )
            ->makePartial();
            
        $this->assertSame(Response::HTTP_OK, $mockController->reloadAction()->getStatusCode());
    }

    public function testReloadActionWithException(): void
    {
        $mockManager = Mockery::mock(FixturesDocumentationManager::class);
        $mockManager->shouldReceive('reload')
            ->once()
            ->andThrow(new RuntimeException());

        $mockController = Mockery::mock(
            FixturesDocumentationController::class,
            [
                $mockManager,
                'title',
                []
            ]
        )
            ->makePartial();
            
        $this->assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $mockController->reloadAction()->getStatusCode());
    }

    public function testGenerateDocumentationAction(): void
    {
        $mockManager = Mockery::mock(FixturesDocumentationManager::class);
        $mockManager->shouldReceive('getDocumentation')
            ->once();

        $mockController = Mockery::mock(
            FixturesDocumentationController::class,
            [
                $mockManager,
                'title',
                []
            ]
        )
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();
        
        $mockController->shouldReceive('render')
            ->once()
            ->andReturn(new Response());
            
        $this->assertSame(Response::HTTP_OK, $mockController->generateDocumentationAction()->getStatusCode());
    }
}
