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
        $mockManager = Mockery::mock('FixturesDocumentation\Service\FixturesDocumentationManager');
        $mockManager->shouldReceive('reload')
            ->once()
            ->andReturn(1);

        $mockController = Mockery::mock(
            'FixturesDocumentation\Controller\FixturesDocumentationController',
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
        $mockManager = Mockery::mock('FixturesDocumentation\Service\FixturesDocumentationManager');
        $mockManager->shouldReceive('reload')
            ->once()
            ->andThrow(new RuntimeException());

        $mockController = Mockery::mock(
            'FixturesDocumentation\Controller\FixturesDocumentationController',
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
        $mockManager = Mockery::mock('FixturesDocumentation\Service\FixturesDocumentationManager');
        $mockManager->shouldReceive('getDocumentation')
            ->once();

        $mockController = Mockery::mock(
            'FixturesDocumentation\Controller\FixturesDocumentationController',
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