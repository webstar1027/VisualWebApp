<?php

declare(strict_types=1);

namespace App\Controller\GraphQL;

use App\Controller\AbstractVisialWebController;
use App\Dao\SimulationDao;
use App\Dao\UtilisateurDao;
use App\Event\SimulationUpdatedEvent;
use App\Exceptions\HTTPException;
use App\Model\ModeleDamortissement;
use App\Security\SerializableUser;
use App\Services\ExportService;
use App\Services\ModeleDamortissementService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\TDBM\ResultIterator;
use TheCodingMachine\TDBM\TDBMException;

class ModeleDamortissementController extends AbstractVisialWebController
{
    /** @var ModeleDamortissementService */
    private $modeleDamortissementService;

    /** @var ExportService */
    private $exportService;

    /** @var SimulationDao */
    private $simulationDao;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(UtilisateurDao $utilisateurDao, ModeleDamortissementService $modeleDamortissementService, ExportService $exportService, SimulationDao $simulationDao, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($utilisateurDao);
        $this->modeleDamortissementService = $modeleDamortissementService;
        $this->exportService = $exportService;
        $this->simulationDao = $simulationDao;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return ModeleDamortissement[]|ResultIterator
     *
     * @throws HTTPException
     *
     * @Query()
     */
    public function modeleDamortissements(string $simulationId): ResultIterator
    {
        $this->validateUser();

        return $this->modeleDamortissementService->fetchBySimulationId($simulationId);
    }

    /**
     * @throws HTTPException
     *
     * @Mutation()
     */
    public function saveModeleDamortissement(ModeleDamortissement $modeleDamortissement): ModeleDamortissement
    {
        $this->validateUser();

        $this->modeleDamortissementService->save($modeleDamortissement);

        $event = new SimulationUpdatedEvent($modeleDamortissement->getSimulation());
        $this->eventDispatcher->dispatch($event);

        return $modeleDamortissement;
    }

    /**
     * @throws TDBMException
     * @throws HTTPException
     *
     * @Mutation()
     */
    public function removeModeleDamortissement(string $modeleDamortissementUUID, string $simulationId): bool
    {
        $this->validateUser();

        $this->modeleDamortissementService->remove($modeleDamortissementUUID);

        $simulation = $this->simulationDao->getById($simulationId);
        $event = new SimulationUpdatedEvent($simulation);
        $this->eventDispatcher->dispatch($event);

        return true;
    }

    /**
     * @throws HTTPException
     */
    protected function validateUser(): void
    {
        /** @var SerializableUser|null $user */
        $user = $this->getUser();

        if ($user === null || empty($user->getRoles())) {
            throw HTTPException::unauthorized("Vos droits ne vous permettent pas d'effectuer cette opération.");
        }
    }

    /**
     * @Route("/export-modeles-amortissement/{simulationId}", name="exportModelesAmortissement")
     */
    public function exportModelesAmortissement(string $simulationId): Response
    {
        $file = $this->exportService->export([$this->exportService::SHEETLIST[3]], $simulationId);

        return $this->file($file['file'], $file['name'], ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     *  @Route("/import-modeles-amortissement/{simulationId}", name="importModelesAmortissement")
     */
    public function importModelesAmortissement(Request $request, string $simulationId): Response
    {
        $notification = $this->exportService->import([$this->exportService::SHEETLIST[3]], $request, $simulationId);

        return new Response($notification);
    }
}
