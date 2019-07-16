<?php

namespace Tests\Model;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use RuntimeException;
use Mockery;

class FixturesDocumentationControllerTest extends TestCase
{
    public function testReloadAction(): void
    {
        $mockManager = Mockery::mock('Adlarge\FixturesDocumentationBundle\Service\FixturesDocumentationManager');
        $mockManager->shouldReceive('reload')
            ->once()
            ->andReturn(1);

        $mockController = Mockery::mock(
            'Adlarge\FixturesDocumentationBundle\Controller\FixturesDocumentationController',
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
        $mockManager = Mockery::mock('Adlarge\FixturesDocumentationBundle\Service\FixturesDocumentationManager');
        $mockManager->shouldReceive('reload')
            ->once()
            ->andThrow(new RuntimeException());

        $mockController = Mockery::mock(
            'Adlarge\FixturesDocumentationBundle\Controller\FixturesDocumentationController',
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
        $mockManager = Mockery::mock('Adlarge\FixturesDocumentationBundle\Service\FixturesDocumentationManager');
        $mockManager->shouldReceive('getDocumentationFromFile')
            ->once();

        $mockController = Mockery::mock(
            'Adlarge\FixturesDocumentationBundle\Controller\FixturesDocumentationController',
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
