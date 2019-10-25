<?php

namespace Tests\Model;

use Adlarge\FixturesDocumentationBundle\Exception\DuplicateIdFixtureException;
use org\bovigo\vfs\vfsStreamDirectory;
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
     * @throws DuplicateIdFixtureException
     */
    public function testGetDocumentation(): void
    {
        vfsStream::newDirectory('var')->at($this->root);
        $documentationManager = new FixturesDocumentationManager(
            $this->root->url(),
            ['dummyCommand'],
            [],
            null
        );

        $this->assertInstanceOf(Documentation::class, $documentationManager->getDocumentation());
    }

    /**
     * @throws DuplicateIdFixtureException
     */
    public function testReset(): void
    {
        vfsStream::newDirectory('var')->at($this->root);
        $documentationManager = new FixturesDocumentationManager(
            $this->root->url(),
            ['dummyCommand'],
            [],
            null
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
     * @throws DuplicateIdFixtureException
     */
    public function testSave(): void
    {
        vfsStream::newDirectory('var')->at($this->root);
        $documentationManager = new FixturesDocumentationManager(
            $this->root->url(),
            ['dummyCommand'],
            [],
            null
        );

        $documentationManager->save();
        $this->assertTrue($this->root->hasChild('var/fixtures.documentation.json'));
    }

    /**
     * @throws DuplicateIdFixtureException
     */
    public function testSaveToCustomDestination(): void
    {
        $dest = $this->root->url() . '/test';
        $documentationManager = new FixturesDocumentationManager(
            '',
            ['dummyCommand'],
            [],
            $dest
        );

        $documentationManager->save();

        $this->assertFalse($this->root->hasChild('var/fixtures.documentation.json'));
        $this->assertTrue($this->root->hasChild('test/fixtures.documentation.json'));
    }

    /**
     * @throws DuplicateIdFixtureException
     */
    public function testSaveToCustomDestinationWithEndSlash(): void
    {
        $dest = $this->root->url() . '/test/';
        $documentationManager = new FixturesDocumentationManager(
            '',
            ['dummyCommand'],
            [],
            $dest
        );

        $documentationManager->save();

        $this->assertFalse($this->root->hasChild('var/fixtures.documentation.json'));
        $this->assertTrue($this->root->hasChild('test/fixtures.documentation.json'));
    }

    /**
     * @throws DuplicateIdFixtureException
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
            ['dummyCommand'],
            [],
            null
        );

        $this->assertInstanceOf(Documentation::class, $documentationManager->getDocumentation());
    }

    /**
     * @throws DuplicateIdFixtureException
     */
    public function testReloadWithUnknownCommand(): void
    {
        $this->expectException(RuntimeException::class);
        
        $documentationManager = new FixturesDocumentationManager(
            $this->root->url(),
            ['knownCommand'],
            [],
            null
        );

        $documentationManager->reload();
    }

    /**
     * @throws DuplicateIdFixtureException
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
            ['workingCommand with args', 'workingCommand2 with other args'],
            [],
            null
        );

        $this->assertSame(1, $documentationManager->reload());
    }

    /**
     * @throws DuplicateIdFixtureException
     */
    public function testIsListeningByDefault(): void
    {
        $documentationManager = new FixturesDocumentationManager(
            '',
            ['dummyCommand'],
            [],
            null
        );

        $this->assertFalse($documentationManager->isListening());
    }

    /**
     * @throws DuplicateIdFixtureException
     */
    public function testIsListeningStart(): void
    {
        $documentationManager = new FixturesDocumentationManager(
            '',
            ['dummyCommand'],
            [],
            null
        );

        $documentationManager->startListening();

        $this->assertTrue($documentationManager->isListening());
    }

    /**
     * @throws DuplicateIdFixtureException
     */
    public function testIsListeningStartAndStop(): void
    {
        $documentationManager = new FixturesDocumentationManager(
            '',
            ['dummyCommand'],
            [],
            null
        );

        $documentationManager->startListening();
        $documentationManager->stopListening();

        $this->assertFalse($documentationManager->isListening());
    }
}
