<?php

declare(strict_types=1);

namespace App\Controller\GraphQL;

use App\Controller\AbstractVisialWebController;
use App\Dao\SimulationDao;
use App\Dao\UtilisateurDao;
use App\Event\SimulationUpdatedEvent;
use App\Exceptions\HTTPException;
use App\Model\AutreCharge;
use App\Security\SerializableUser;
use App\Services\AutreChargeService;
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

class AutresChargesController extends AbstractVisialWebController
{
    /** @var AutreChargeService */
    private $autreChargeService;
    /** @var ExportService */
    private $exportService;
    /** @var SimulationDao */
    private $simulationDao;
    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(UtilisateurDao $utilisateurDao, AutreChargeService $autreChargeService, ExportService $exportService, SimulationDao $simulationDao, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($utilisateurDao);
        $this->autreChargeService = $autreChargeService;
        $this->exportService = $exportService;
        $this->simulationDao = $simulationDao;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return AutreCharge[]|ResultIterator
     *
     * @throws HTTPException
     *
     * @Query()
     */
    public function autresCharges(string $simulationId): ResultIterator
    {
        /** @var SerializableUser|null $user */
        $user = $this->getUser();

        if ($user === null || empty($user->getRoles())) {
            throw HTTPException::unauthorized("Vos droits ne vous permettent pas d'effectuer cette opération.");
        }

        return $this->autreChargeService->findBySimulation($simulationId);
    }

    /**
     * @throws HTTPException
     *
     * @Mutation()
     */
    public function saveAutreCharge(AutreCharge $autreCharge): AutreCharge
    {
        /** @var SerializableUser|null $user */
        $user = $this->getUser();

        if ($user === null || empty($user->getRoles())) {
            throw HTTPException::unauthorized("Vos droits ne vous permettent pas d'effectuer cette opération.");
        }

        $this->autreChargeService->save($autreCharge);

        $event = new SimulationUpdatedEvent($autreCharge->getSimulation());
        $this->eventDispatcher->dispatch($event);

        return $autreCharge;
    }

    /**
     * @throws TDBMException
     * @throws HTTPException
     *
     * @Mutation()
     */
    public function removeAutreCharge(string $autreChargeId, string $simulationId): bool
    {
        /** @var SerializableUser|null $user */
        $user = $this->getUser();

        if ($user === null || empty($user->getRoles())) {
            throw HTTPException::unauthorized("Vos droits ne vous permettent pas d'effectuer cette opération.");
        }

        $this->autreChargeService->remove($autreChargeId);

        $simulation = $this->simulationDao->getById($simulationId);
        $event = new SimulationUpdatedEvent($simulation);
        $this->eventDispatcher->dispatch($event);

        return true;
    }

    /**
     * @Route("/export-autres-charges/{simulationId}", name="exportAutresCharges")
     */
    public function exportAutresCharges(string $simulationId): Response
    {
        $file = $this->exportService->export([$this->exportService::SHEETLIST[9]], $simulationId);

        return $this->file($file['file'], $file['name'], ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     *  @Route("/import-autres-charges/{simulationId}", name="importAutresCharges")
     */
    public function importAutresCharges(Request $request, string $simulationId): Response
    {
        $notification = $this->exportService->import([$this->exportService::SHEETLIST[9]], $request, $simulationId);

        return new Response($notification);
    }
}
