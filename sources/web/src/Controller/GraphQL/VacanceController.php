<?php

declare(strict_types=1);

namespace App\Controller\GraphQL;

use App\Controller\AbstractVisialWebController;
use App\Dao\SimulationDao;
use App\Dao\UtilisateurDao;
use App\Event\SimulationUpdatedEvent;
use App\Exceptions\HTTPException;
use App\Model\Vacance;
use App\Model\VacancePeriodique;
use App\Security\SerializableUser;
use App\Services\ExportService;
use App\Services\VacanceService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\TDBM\ResultIterator;
use TheCodingMachine\TDBM\TDBMException;

class VacanceController extends AbstractVisialWebController
{
    /** @var VacanceService */
    private $vacanceService;
    /** @var ExportService */
    private $exportService;

    /** @var SimulationDao */
    private $simulationDao;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(UtilisateurDao $utilisateurDao, VacanceService $vacanceService, SimulationDao $simulationDao, EventDispatcherInterface $eventDispatcher, ExportService $exportService)
    {
        parent::__construct($utilisateurDao);
        $this->vacanceService = $vacanceService;
        $this->simulationDao = $simulationDao;
        $this->eventDispatcher = $eventDispatcher;
        $this->exportService = $exportService;
    }

    /**
     * @return Vacance[]|ResultIterator
     *
     * @throws HTTPException
     *
     * @Query()
     */
    public function vacance(string $simulationID): ResultIterator
    {
        /** @var SerializableUser $user */
        $user = $this->getUser();

        if (empty($user->getRoles())) {
            throw HTTPException::unauthorized("Vos droits ne vous permettent pas d'effectuer cette opération.");
        }

        return $this->vacanceService->fetchBySimulationId($simulationID);
    }

    /**
     * @Mutation()
     */
    public function saveVacance(Vacance $vacance): Vacance
    {
        $this->vacanceService->save($vacance);

        $event = new SimulationUpdatedEvent($vacance->getSimulation());
        $this->eventDispatcher->dispatch($event);

        return $vacance;
    }

    /**
     * @throws TDBMException
     *
     * @Mutation()
     */
    public function saveVacancePeriodique(
        string $uuid,
        int $iteration,
        float $montant
    ): VacancePeriodique {
        $vacancePeriodique = $this->vacanceService->findVacancePeriodique($uuid, $iteration);
        if ($vacancePeriodique === null) {
            throw new TDBMException('No row found for VacancePeriodique');
        }
        $vacancePeriodique->setMontant($montant);
        $this->vacanceService->saveProfilEvolutionLoyerPeriodique($vacancePeriodique);

        return $vacancePeriodique;
    }

    /**
     * @throws HTTPException
     * @throws TDBMException
     *
     * @Mutation()
     */
    public function removeVacance(string $vacanceId, string $simulationId): bool
    {
        /** @var SerializableUser $user */
        $user = $this->getUser();

        if (empty($user->getRoles())) {
            throw HTTPException::unauthorized("Vos droits ne vous permettent pas d'effectuer cette opération.");
        }
        $this->vacanceService->remove($vacanceId);

        $simulation = $this->simulationDao->getById($simulationId);
        $event = new SimulationUpdatedEvent($simulation);
        $this->eventDispatcher->dispatch($event);

        return true;
    }

    /**
     * @Route("/export-vacance-identifiees/{simulationId}", name="exportVacanceIdentifiees")
     */
    public function exportVacanceIdentifiees(string $simulationId): Response
    {
        $file = $this->exportService->export([$this->exportService::SHEETLIST[22]], $simulationId);

        return $this->file($file['file'], $file['name'], ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     *  @Route("/import-vacance-identifiees/{simulationId}", name="importVacanceIdentifiees")
     */
    public function importVacanceIdentifiees(Request $request, string $simulationId): Response
    {
        $notification = $this->exportService->import([$this->exportService::SHEETLIST[22]], $request, $simulationId);

        return new Response($notification);
    }
}
