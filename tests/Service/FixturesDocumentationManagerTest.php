<?php

namespace Tests\Model;

use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use Adlarge\FixturesDocumentationBundle\Service\FixturesDocumentationManager;
use Adlarge\FixturesDocumentationBundle\Model\Documentation;
use \Adlarge\FixturesDocumentationBundle\Exception\DuplicateFixtureException;
use org\bovigo\vfs\vfsStream;
use RuntimeException;
use Mockery;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class FixturesDocumentationManagerTest extends TestCase
{
    /** @var vfsStreamDirectory $root */
    private $root;

    public function setUp(): void
    {
        $this->root = vfsStream::setup();
        
    }

    public function tearDown()
    {
        Mockery::close();
    }

    /**
     * @throws DuplicateFixtureException
     */
    public function testGetDocumentation(): void
    {
        vfsStream::newDirectory('var')->at($this->root);
        $documentationManager = new FixturesDocumentationManager(
            $this->root->url(),
            ['dummyCommand']
        );

        $this->assertInstanceOf(Documentation::class, $documentationManager->getDocumentation());
    }

    /**
     * @throws DuplicateFixtureException
     */
    public function testReset(): void
    {
        vfsStream::newDirectory('var')->at($this->root);
        $documentationManager = new FixturesDocumentationManager(
            $this->root->url(),
            ['dummyCommand']
        );

        file_put_contents(
            $this->root->url() . '/var/fixtures.documentation.json',
            '{}'
        );
        $this->assertTrue($this->root->hasChild('var/fixtures.documentation.json'));
        $documentationManager->reset();
        $this->assertFalse($this->root->hasChild('var/fixtures.documentation.json'));
    }

    /**
     * @throws DuplicateFixtureException
     */
    public function testSave(): void
    {
        vfsStream::newDirectory('var')->at($this->root);
        $documentationManager = new FixturesDocumentationManager(
            $this->root->url(),
            ['dummyCommand']
        );

        $documentationManager->save();
        $this->assertTrue($this->root->hasChild('var/fixtures.documentation.json'));
    }

     /**
     * @throws DuplicateFixtureException
     */
    public function testInitDocumentation(): void
    {
        vfsStream::newDirectory('var')->at($this->root);
        
        file_put_contents(
            $this->root->url() . '/var/fixtures.documentation.json',
            '{}'
        );
        $documentationManager = new FixturesDocumentationManager(
            $this->root->url(),
            ['dummyCommand']
        );

        $this->assertInstanceOf(Documentation::class, $documentationManager->getDocumentation());
    }

    /**
     * @throws DuplicateFixtureException
     */
    public function testReloadWithUnknownCommand(): void
    {
        $this->expectException(RuntimeException::class);
        
        $documentationManager = new FixturesDocumentationManager(
            $this->root->url(),
            ['unknowCommand']
        );

        $documentationManager->reload();
    }

    /**
     * @throws DuplicateFixtureException
     */
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
