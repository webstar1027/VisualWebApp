<?php

declare(strict_types=1);

namespace App\Controller\GraphQL;

use App\Controller\AbstractVisialWebController;
use App\Dao\SimulationDao;
use App\Dao\UtilisateurDao;
use App\Event\SimulationUpdatedEvent;
use App\Exceptions\HTTPException;
use App\Model\Annuite;
use App\Security\SerializableUser;
use App\Services\AnnuiteService;
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

class AnnuiteController extends AbstractVisialWebController
{
    /** @var AnnuiteService */
    private $annuiteService;

    /** @var ExportService */
    private $exportService;

    /** @var SimulationDao */
    private $simulationDao;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(UtilisateurDao $utilisateurDao, AnnuiteService $annuiteService, ExportService $exportService, SimulationDao $simulationDao, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($utilisateurDao);
        $this->annuiteService = $annuiteService;
        $this->exportService = $exportService;
        $this->simulationDao = $simulationDao;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return Annuite[]|ResultIterator
     *
     * @throws HTTPException
     *
     * @Query()
     */
    public function annuites(string $simulationId, ?int $type): ResultIterator
    {
        /** @var SerializableUser|null $user */
        $user = $this->getUser();

        if ($user === null || empty($user->getRoles())) {
            throw HTTPException::unauthorized("Vos droits ne vous permettent pas d'effectuer cette opération.");
        }

        return $this->annuiteService->findBySimulationAndType($simulationId, $type ??Annuite::TYPE_ANNUITE_EMPRUNTS);
    }

    /**
     * @throws HTTPException
     *
     * @Mutation()
     */
    public function saveAnnuite(Annuite $annuite): Annuite
    {
        /** @var SerializableUser|null $user */
        $user = $this->getUser();

        if ($user === null || empty($user->getRoles())) {
            throw HTTPException::unauthorized("Vos droits ne vous permettent pas d'effectuer cette opération.");
        }

        $this->annuiteService->save($annuite);

        $event = new SimulationUpdatedEvent($annuite->getSimulation());
        $this->eventDispatcher->dispatch($event);

        return $annuite;
    }

    /**
     * @throws TDBMException
     * @throws HTTPException
     *
     * @Mutation()
     */
    public function removeAnnuite(string $annuiteUUID, string $simulationId): bool
    {
        /** @var SerializableUser|null $user */
        $user = $this->getUser();

        if ($user === null || empty($user->getRoles())) {
            throw HTTPException::unauthorized("Vos droits ne vous permettent pas d'effectuer cette opération.");
        }

        $this->annuiteService->remove($annuiteUUID);

        $simulation = $this->simulationDao->getById($simulationId);
        $event = new SimulationUpdatedEvent($simulation);
        $this->eventDispatcher->dispatch($event);

        return true;
    }

    /**
     * @Route("/export-charges-annuite/{simulationId}", name="exportChargesAnnuite")
     */
    public function exportChargesAnnuite(string $simulationId): Response
    {
        $file = $this->exportService->export([$this->exportService::SHEETLIST[16]], $simulationId);

        return $this->file($file['file'], $file['name'], ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     *  @Route("/import-charges-annuite/{simulationId}", name="importChargesAnnuite")
     */
    public function importChargesAnnuite(Request $request, string $simulationId): Response
    {
        $notification = $this->exportService->import([$this->exportService::SHEETLIST[16]], $request, $simulationId);

        return new Response($notification);
    }
}
