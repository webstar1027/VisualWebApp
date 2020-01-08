<?php

declare(strict_types=1);

namespace App\Controller\GraphQL;

use App\Controller\AbstractVisialWebController;
use App\Dao\SimulationDao;
use App\Dao\UtilisateurDao;
use App\Event\SimulationUpdatedEvent;
use App\Exceptions\HTTPException;
use App\Model\Maintenance;
use App\Security\SerializableUser;
use App\Services\ExportService;
use App\Services\MaintenanceService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\TDBM\ResultIterator;
use TheCodingMachine\TDBM\TDBMException;
use function count;

class MaintenanceController extends AbstractVisialWebController
{
    /** @var MaintenanceService */
    private $maintenanceService;

    /** @var ExportService */
    private $exportService;

    /** @var SimulationDao */
    private $simulationDao;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(UtilisateurDao $utilisateurDao, MaintenanceService $maintenanceService, ExportService $exportService, SimulationDao $simulationDao, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($utilisateurDao);
        $this->maintenanceService = $maintenanceService;
        $this->exportService = $exportService;
        $this->simulationDao = $simulationDao;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return Maintenance[]|ResultIterator
     *
     * @throws HTTPException
     *
     * @Query()
     */
    public function maintenance(string $simulationId): ResultIterator
    {
        /** @var SerializableUser|null $user */
        $user = $this->getUser();

        if ($user === null || empty($user->getRoles())) {
            throw HTTPException::badRequest('Utilisateur invalide');
        }

        return $this->maintenanceService->findBySimulation($simulationId);
    }

    /**
     * @return int[]
     *
     * @Query()
     */
    public function maintenanceCount(string $simulationId): array
    {
        /** @var SerializableUser|null $user */
        $user = $this->getUser();

        if ($user === null || empty($user->getRoles())) {
            throw HTTPException::badRequest('Utilisateur invalide');
        }

        $maintenances = $this->maintenanceService->findBySimulation($simulationId);
        $detailMaintenance = $detailEntretien = [];
        foreach ($maintenances as $maintenance) {
            if ($maintenance->getType() === Maintenance::TYPE_DETAIL_MAINTENACE) {
                $detailMaintenance[] = $maintenance;
            } elseif ($maintenance->getType() === Maintenance::TYPE_DETAIL_GROS_ENTRETIEN) {
                $detailEntretien[] = $maintenance;
            }
        }

        return [count($detailMaintenance), count($detailEntretien)];
    }

    /**
     * @throws HTTPException
     *
     * @Mutation()
     */
    public function saveMaintenance(Maintenance $maintenance): Maintenance
    {
        /** @var SerializableUser|null $user */
        $user = $this->getUser();

        if ($user === null || empty($user->getRoles())) {
            throw HTTPException::badRequest('Utilisateur invalide');
        }

        $this->maintenanceService->save($maintenance);

        $event = new SimulationUpdatedEvent($maintenance->getSimulation());
        $this->eventDispatcher->dispatch($event);

        return $maintenance;
    }

    /**
     * @throws TDBMException
     * @throws HTTPException
     *
     * @Mutation()
     */
    public function removeMaintenance(string $maintenanceUUId, string $simulationId): bool
    {
        /** @var SerializableUser|null $user */
        $user = $this->getUser();

        if ($user === null || empty($user->getRoles())) {
            throw HTTPException::badRequest('Utilisateur invalide');
        }

        $this->maintenanceService->remove($maintenanceUUId);

        $simulation = $this->simulationDao->getById($simulationId);
        $event = new SimulationUpdatedEvent($simulation);
        $this->eventDispatcher->dispatch($event);

        return true;
    }

    /**
     * @Route("/export-charges-maintenance/{simulationId}", name="exportChargesMaintenance")
     */
    public function exportChargesMaintenance(string $simulationId): Response
    {
        $file = $this->exportService->export([$this->exportService::SHEETLIST[4]], $simulationId);

        return $this->file($file['file'], $file['name'], ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     *  @Route("/import-charges-maintenance/{simulationId}", name="importChargesMaintenance")
     */
    public function importChargesMaintenance(Request $request, string $simulationId): Response
    {
        $notification = $this->exportService->import([$this->exportService::SHEETLIST[4]], $request, $simulationId);

        return new Response($notification);
    }
}
