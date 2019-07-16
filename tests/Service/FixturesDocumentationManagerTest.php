<?php

namespace Tests\Model;

use PHPUnit\Framework\TestCase;
use Adlarge\FixturesDocumentationBundle\Service\FixturesDocumentationManager;
use Adlarge\FixturesDocumentationBundle\Model\Documentation;
use org\bovigo\vfs\vfsStream;
use RuntimeException;
use Mockery;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class FixturesDocumentationManagerTest extends TestCase
{
    public function setUp(): void
    {
        $this->root = vfsStream::setup();
        
    }

    public function tearDown()
    {
        Mockery::close();
    }
    
    public function testGetDocumentation(): void
    {
        vfsStream::newDirectory('var')->at($this->root);
        $documentationManager = new FixturesDocumentationManager(
            $this->root->url(),
            ['dummyCommand']
        );

        $this->assertInstanceOf(Documentation::class, $documentationManager->getDocumentation());
    }

    public function testGetDocumentationFromFile(): void
    {
        vfsStream::newDirectory('var')->at($this->root);
        $documentationManager = new FixturesDocumentationManager(
            $this->root->url(),
            ['dummyCommand']
        );

        $this->assertInstanceOf(Documentation::class, $documentationManager->getDocumentationFromFile());
    }

    public function testDeleteDocumentation(): void
    {
        vfsStream::newDirectory('var')->at($this->root);
        $documentationManager = new FixturesDocumentationManager(
            $this->root->url(),
            ['dummyCommand']
        );

        file_put_contents(
            $this->root->url() . "/var/fixtures.documentation.json", 
            '{}'
        );
        $this->assertTrue($this->root->hasChild('var/fixtures.documentation.json'));
        $documentationManager->deleteDocumentation();
        $this->assertFalse($this->root->hasChild('var/fixtures.documentation.json'));
    }

    public function testSaveToFile(): void
    {
        vfsStream::newDirectory('var')->at($this->root);
        $documentationManager = new FixturesDocumentationManager(
            $this->root->url(),
            ['dummyCommand']
        );

        $documentationManager->saveToFile();
        $this->assertTrue($this->root->hasChild('var/fixtures.documentation.json'));
    }

    public function testReloadWithUnknownCommand(): void
    {
        $this->expectException(RuntimeException::class);
        
        $documentationManager = new FixturesDocumentationManager(
            $this->root->url(),
            ['unknowCommand']
        );

        $documentationManager->reload();
    }

    public function testReload(): void
    {
        $mockProcess = Mockery::mock('overload:Symfony\Component\Process\Process');
        $mockProcess->shouldReceive('run')
            ->once()
            ->andReturn(1);
        $mockProcess->shouldReceive('setWorkingDirectory')
            ->once();
        $mockProcess->shouldReceive('isSuccessful')
            ->once()
            ->andReturn(true);
        
        $documentationManager = new FixturesDocumentationManager(
            $this->root->url(),
            ['workingCommand']
        );

        $this->assertSame(1, $documentationManager->reload());
    }
}
