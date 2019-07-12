<?php
declare(strict_types=1);

namespace FixturesDocumentation\Service;

use FixturesDocumentation\Exception\DuplicateFixtureException;
use FixturesDocumentation\Model\Documentation;
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
     * FixturesDocumentationManager constructor.
     *
     * @param string $projectDir
     * @param array  $reloadCommands
     */
    public function __construct(string $projectDir, array $reloadCommands)
    {
        $this->projectDir = $projectDir;
        $this->jsonFilePath = $this->projectDir . '/var/' . self::FILE_NAME;
        $this->reloadCommands = $reloadCommands;
    }

    /**
     * Get the fixtures Json File as array.
     *
     * @return Documentation
     *
     * @throws DuplicateFixtureException
     */
    public function getDocumentation(): Documentation
    {
        return Documentation::getInstance($this->jsonFilePath);
    }

    /**
     * Delete the file.
     *
     * @throws DuplicateFixtureException
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
     *
     * @throws DuplicateFixtureException
     */
    public function saveToFile(): void
    {
        $json = Documentation::getInstance($this->jsonFilePath)->toJson();

        $file = fopen($this->jsonFilePath, 'w');
        fwrite($file, $json);
        fclose($file);
    }

    /**
     * Reload database and fixtures.
     */
    public function reload(): void
    {
        $process = new Process(implode(' && ', $this->reloadCommands));
        $process->setWorkingDirectory($this->projectDir);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException($process->getErrorOutput());
        }
    }
}
