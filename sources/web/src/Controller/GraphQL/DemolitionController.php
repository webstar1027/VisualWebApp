<?php

declare(strict_types=1);

namespace App\Controller\GraphQL;

use App\Controller\AbstractVisialWebController;
use App\Dao\SimulationDao;
use App\Dao\UtilisateurDao;
use App\Event\SimulationUpdatedEvent;
use App\Exceptions\HTTPException;
use App\Model\Demolition;
use App\Security\SerializableUser;
use App\Services\DemolitionService;
use App\Services\ExportService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\TDBM\ResultIterator;
use TheCodingMachine\TDBM\TDBMException;

class DemolitionController extends AbstractVisialWebController
{
    /** @var DemolitionService */
    private $demolitionService;
    /** @var ExportService */
    private $exportService;
    /** @var SimulationDao */
    private $simulationDao;
    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(UtilisateurDao $utilisateurDao, DemolitionService $demolitionService, ExportService $exportService, SimulationDao $simulationDao, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($utilisateurDao);
        $this->demolitionService = $demolitionService;
        $this->exportService = $exportService;
        $this->simulationDao = $simulationDao;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return Demolition[]|ResultIterator
     *
     * @throws HTTPException
     *
     * @Query()
     */
    public function demolitions(string $simulationId): ResultIterator
    {
        $this->validateUser();

        return $this->demolitionService->findBySimulationID($simulationId);
    }

    /**
     * @throws HTTPException
     *
     * @Mutation()
     */
    public function saveDemolition(Demolition $demolition): Demolition
    {
        $this->validateUser();

        $this->demolitionService->save($demolition);

        $event = new SimulationUpdatedEvent($demolition->getSimulation());
        $this->eventDispatcher->dispatch($event);

        return $demolition;
    }

    /**
     * @throws TDBMException
     * @throws HTTPException
     *
     * @Mutation()
     */
    public function removeDemolition(string $demolitionUUID, string $simulationId): bool
    {
        $this->validateUser();

        $this->demolitionService->remove($demolitionUUID);

        $simulation = $this->simulationDao->getById($simulationId);
        $event = new SimulationUpdatedEvent($simulation);
        $this->eventDispatcher->dispatch($event);

        return true;
    }

    /**
     * @throws HTTPException
     *
     * @Mutation()
     */
    public function removeTypeDempruntDemolition(string $typesEmpruntsUUID, string $demolitionUUID): bool
    {
        $this->validateUser();

        $this->demolitionService->removeTypeDempruntDemolition($typesEmpruntsUUID, $demolitionUUID);

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
     * @Route("/export-identifees/{simulationId}", name="exportIdentifees")
     */
    public function exportIdentifees(string $simulationId): Response
    {
        $file = $this->exportService->export([$this->exportService::SHEETLIST[12]], $simulationId);

        return $this->file($file['file'], $file['name'], ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     *  @Route("/import-identifiees/{simulationId}", name="importIdentifees")
     */
    public function importIdentifees(Request $request, string $simulationId): Response
    {
        $notification = $this->exportService->import([$this->exportService::SHEETLIST[12]], $request, $simulationId);

        return new Response($notification);
    }

    /**
     * @Route("/export-nonIdentifees/{simulationId}", name="exportNonIdentifees")
     */
    public function exportNonIdentifees(string $simulationId): Response
    {
        $file = $this->exportService->export([$this->exportService::SHEETLIST[11]], $simulationId);

        return $this->file($file['file'], $file['name'], ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     *  @Route("/import-nonIdentifiees/{simulationId}", name="importNonIdentifees")
     */
    public function importNonIdentifees(Request $request, string $simulationId): Response
    {
        $notification = $this->exportService->import([$this->exportService::SHEETLIST[11]], $request, $simulationId);

        return new Response($notification);
    }
}
