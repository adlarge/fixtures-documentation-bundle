<?php
declare(strict_types=1);

namespace Adlarge\FixturesDocumentationBundle\Service;

use Adlarge\FixturesDocumentationBundle\Exception\DuplicateIdFixtureException;
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
     * @var array
     */
    private $configEntities;
    /**
     * @var Documentation
     */
    private $documentation;

    /**
     * @var bool
     */
    private $isListening = false;

    /**
     * FixturesDocumentationManager constructor.
     *
     * @param string      $projectDir
     * @param array       $reloadCommands
     * @param array       $configEntities
     * @param string|null $filePath
     *
     * @throws DuplicateIdFixtureException
     */
    public function __construct(
        string $projectDir,
        array $reloadCommands,
        array $configEntities,
        ?string $filePath
    ) {
        $this->projectDir = $projectDir;
        $this->jsonFilePath = $this->projectDir . $filePath ?: '/var/' . self::FILE_NAME;
        $this->reloadCommands = $reloadCommands;
        $this->configEntities = $configEntities;

        $this->initDocumentation();
    }

    /**
     * @throws DuplicateIdFixtureException
     */
    protected function initDocumentation(): void
    {
        $jsonString = null;
        if ($this->jsonFilePath && is_file($this->jsonFilePath)) {
            $jsonString = file_get_contents($this->jsonFilePath);
        }
        $this->documentation = new Documentation($this->configEntities, $jsonString);
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
     * @return int
     */
    public function reload(): int
    {
        $exitCode = 0;

        foreach ($this->reloadCommands as $command) {
            $process = new Process(explode(' ', $command));
            $process->setWorkingDirectory($this->projectDir);
            $exitCode = $process->run();

            if (!$process->isSuccessful()) {
                throw new RuntimeException($process->getErrorOutput());
            }
        }

        return $exitCode;
    }

    /**
     * @return FixturesDocumentationManager
     */
    public function startListening(): self
    {
        $this->isListening = true;
        return $this;
    }

    /**
     * @return FixturesDocumentationManager
     */
    public function stopListening(): self
    {
        $this->isListening = false;
        return $this;
    }

    /**
     * @return bool
     */
    public function isListening(): bool
    {
        return $this->isListening;
    }
}
