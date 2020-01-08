<?php

declare(strict_types=1);

namespace App\Controller\GraphQL;

use App\Controller\AbstractVisialWebController;
use App\Dao\FondDeRoulementParametreDao;
use App\Dao\SimulationDao;
use App\Dao\UtilisateurDao;
use App\Event\SimulationUpdatedEvent;
use App\Exceptions\HTTPException;
use App\Model\FondDeRoulement;
use App\Model\FondDeRoulementParametre;
use App\Security\SerializableUser;
use App\Services\ExportService;
use App\Services\FondDeRoulementService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\TDBM\ResultIterator;
use TheCodingMachine\TDBM\TDBMException;

class FondDeRoulementController extends AbstractVisialWebController
{
    /** @var FondDeRoulementService */
    private $fondDeRoulementService;

    /** @var FondDeRoulementParametreDao */
    private $fondDeRoulementParametreDao;

    /** @var SimulationDao */
    private $simulationDao;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var ExportService */
    private $exportService;

    public function __construct(UtilisateurDao $utilisateurDao, FondDeRoulementService $fondDeRoulementService, FondDeRoulementParametreDao $fondDeRoulementParametreDao, SimulationDao $simulationDao, EventDispatcherInterface $eventDispatcher, ExportService $exportService)
    {
        parent::__construct($utilisateurDao);
        $this->fondDeRoulementService = $fondDeRoulementService;
        $this->fondDeRoulementParametreDao = $fondDeRoulementParametreDao;
        $this->simulationDao = $simulationDao;
        $this->eventDispatcher = $eventDispatcher;
        $this->exportService = $exportService;
    }

    /**
     * @return FondDeRoulement[]|ResultIterator
     *
     * @throws HTTPException
     *
     * @Query()
     */
    public function fondDeRoulement(string $simulationId): ResultIterator
    {
        /** @var SerializableUser|null $user */
        $user = $this->getUser();

        if ($user === null || empty($user->getRoles())) {
            throw HTTPException::badRequest('Utilisateur invalide');
        }

        return $this->fondDeRoulementService->findBySimulation($simulationId);
    }

    /**
     *  @throws HTTPException
     *
     * @Mutation()
     */
    public function saveFondDeRoulement(FondDeRoulement $fondDeRoulement): FondDeRoulement
    {
        /** @var SerializableUser|null $user */
        $user = $this->getUser();

        if ($user === null || empty($user->getRoles())) {
            throw HTTPException::badRequest('Utilisateur invalide');
        }
        $this->fondDeRoulementService->save($fondDeRoulement);

        $event = new SimulationUpdatedEvent($fondDeRoulement->getSimulation());
        $this->eventDispatcher->dispatch($event);

        return $fondDeRoulement;
    }

    /**
     * @throws TDBMException
     * @throws HTTPException
     *
     * @Mutation()
     */
    public function removeFondDeRoulement(string $fondDeRoulementID, string $simulationId): bool
    {
        /** @var SerializableUser|null $user */
        $user = $this->getUser();

        if ($user === null || empty($user->getRoles())) {
            throw HTTPException::unauthorized("Vos droits ne vous permettent pas d'effectuer cette opération.");
        }
        $this->fondDeRoulementService->remove($fondDeRoulementID);

        $simulation = $this->simulationDao->getById($simulationId);
        $event = new SimulationUpdatedEvent($simulation);
        $this->eventDispatcher->dispatch($event);

        return true;
    }

    /**
     * @return FondDeRoulementParametre[]|ResultIterator
     *
     * @Query()
     */
    public function fondDeRoulementParametre(string $simulationId): ResultIterator
    {
        return $this->fondDeRoulementParametreDao->findBySimulationID($simulationId);
    }

    /**
     * @throws TDBMException
     *
     * @Mutation()
     */
    public function saveFondDeRoulementParametre(string $fondDeRoulementId, ?float $potentielFinancierTermination, ?float $fondsPropresSurOperation, ?float $depotDeGarantie, string $simulationId): FondDeRoulementParametre
    {
        $updateParametre = $this->fondDeRoulementParametreDao->getById($fondDeRoulementId);
        $updateParametre->setPotentielFinancierTermination($potentielFinancierTermination);
        $updateParametre->setFondsPropresSurOperation($fondsPropresSurOperation);
        $updateParametre->setDepotDeGarantie($depotDeGarantie);
        $this->fondDeRoulementParametreDao->save($updateParametre);

        $simulation = $this->simulationDao->getById($simulationId);
        $event = new SimulationUpdatedEvent($simulation);
        $this->eventDispatcher->dispatch($event);

        return $updateParametre;
    }

    /**
     * @Route("/export-fondroulement/{simulationId}", name="exportFondroulement")
     */
    public function exportFondroulement(string $simulationId): Response
    {
        $file = $this->exportService->export([$this->exportService::SHEETLIST[20]], $simulationId);

        return $this->file($file['file'], $file['name'], ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     *  @Route("/import-fondroulement/{simulationId}", name="importFondroulement")
     */
    public function importFondroulement(Request $request, string $simulationId): Response
    {
        $notification = $this->exportService->import([$this->exportService::SHEETLIST[20]], $request, $simulationId);

        return new Response($notification);
    }
}
