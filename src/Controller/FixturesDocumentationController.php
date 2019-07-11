<?php
declare(strict_types=1);

namespace FixturesDoc\Controller;

use Exception;
use FixturesDoc\Exception\DuplicateFixtureException;
use FixturesDoc\Service\FixtureDocumentationManager;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class FixturesDocumentationController extends Controller
{
    /**
     * @var FixtureDocumentationManager
     */
    private $documentationManager;
    /**
     * @var string
     */
    private $docTitle;
    /**
     * @var array
     */
    private $reloadCommands;

    /**
     * FixtureDocumentationController constructor.
     *
     * @param FixtureDocumentationManager $documentationManager
     * @param string                      $docTitle
     * @param array                       $reloadCommands
     */
    public function __construct(
        FixtureDocumentationManager $documentationManager,
        string $docTitle,
        array $reloadCommands
    ) {
        $this->documentationManager = $documentationManager;
        $this->docTitle = $docTitle;
        $this->reloadCommands = $reloadCommands;
    }

    /**
     * @return Response
     *
     * @throws DuplicateFixtureException
     */
    public function generateDocumentationAction(): Response
    {
        return $this->render(
            '@FixtureDocumentation/fixture.documentation.html.twig',
            [
                'doc' => $this->documentationManager->getDocumentation(),
                'docTitle' => $this->docTitle,
                'canReload' => !empty($this->reloadCommands)
            ]
        );
    }

    /**
     * Reload fixtures.
     *
     * @return Response
     *
     * @throws Exception
     */
    public function reloadAction(): Response
    {
        try {
            $this->documentationManager->reload();
        } catch (RuntimeException $e) {
            return new JsonResponse(
                ['error' => 'An error occurred.'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return new JsonResponse();
    }
}
