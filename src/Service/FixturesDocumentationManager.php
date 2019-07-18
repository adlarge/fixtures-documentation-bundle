<?php
declare(strict_types=1);

namespace Adlarge\FixturesDocumentationBundle\Service;

use Adlarge\FixturesDocumentationBundle\Exception\DuplicateFixtureException;
use Adlarge\FixturesDocumentationBundle\Model\Documentation;
use RuntimeException;
use Symfony\Component\Process\Process;

class FixturesDocumentationManager
{
    private const FILE_NAME = 'fixtures.documentation.json';

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

        $this->initDocumentation();
    }

    /**
     * @throws DuplicateFixtureException
     */
    protected function initDocumentation(): void
    {
        $jsonString = null;
        if ($this->jsonFilePath && is_file($this->jsonFilePath)) {
            $jsonString = file_get_contents($this->jsonFilePath);
        }
        $this->documentation = new Documentation($jsonString);
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
     * Delete the file.
     */
    public function reset(): void
    {
        $this->documentation->reset();

        if (is_file($this->jsonFilePath)) {
            unlink($this->jsonFilePath);
        }
    }

    /**
     * Save the Json Array back to the file.
     */
    public function save(): void
    {
        $json = $this->documentation->toJson();

        $file = fopen($this->jsonFilePath, 'wb');
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
