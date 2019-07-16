<?php
declare(strict_types=1);

namespace Adlarge\FixturesDocumentationBundle\Service;

use Adlarge\FixturesDocumentationBundle\Exception\DuplicateFixtureException;
use Adlarge\FixturesDocumentationBundle\Model\Documentation;
use RuntimeException;
use Symfony\Component\Process\Process;

class FixturesDocumentationManager
{
    const FILE_NAME = 'fixtures.documentation.json';

    /**
     * @var string
     */
    private $projectDir;
    /**
     * @var string
     */
    private $jsonFilePath;
    /**
     * @var array
     */
    private $reloadCommands;
    /**
     * @var Documentation
     */
    private $documentation;

    /**
     * FixturesDocumentationManager constructor.
     *
     * @param string $projectDir
     * @param array  $reloadCommands
     *
     * @throws DuplicateFixtureException
     */
    public function __construct(string $projectDir, array $reloadCommands)
    {
        $this->projectDir = $projectDir;
        $this->jsonFilePath = $this->projectDir . '/var/' . self::FILE_NAME;
        $this->reloadCommands = $reloadCommands;
        $this->documentation = new Documentation();
    }

    /**
     * Get current Documentation.
     *
     * @return Documentation
     */
    public function getDocumentation(): Documentation
    {
        return $this->documentation;
    }

    /**
     * Get generated Documentation from file for display.
     *
     * @return Documentation
     *
     * @throws DuplicateFixtureException
     */
    public function getDocumentationFromFile(): Documentation
    {
        return new Documentation($this->jsonFilePath);
    }

    /**
     * Delete the file.
     */
    public function deleteDocumentation(): void
    {
        $this->getDocumentation()->reset();

        if (is_file($this->jsonFilePath)) {
            unlink($this->jsonFilePath);
        }
    }

    /**
     * Save the Json Array back to the file.
     */
    public function saveToFile(): void
    {
        $json = $this->getDocumentation()->toJson();

        $file = fopen($this->jsonFilePath, 'w');
        fwrite($file, $json);
        fclose($file);
    }

    /**
     * Reload database and fixtures.
     */
    public function reload(): int
    {
        // TODO: refacto the use of new Process to new Process(['command'])
        $process = new Process(implode(' && ', $this->reloadCommands));
        $process->setWorkingDirectory($this->projectDir);
        $exitCode = $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException($process->getErrorOutput());
        }

        return $exitCode;
    }
}
