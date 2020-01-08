<?php

declare(strict_types=1);

namespace App\Controller\GraphQL;

use App\Controller\AbstractVisialWebController;
use App\Dao\PatrimoineLogementParametreDao;
use App\Dao\SimulationDao;
use App\Dao\UtilisateurDao;
use App\Event\SimulationUpdatedEvent;
use App\Exceptions\HTTPException;
use App\Model\Patrimoine;
use App\Model\PatrimoineLogementParametre;
use App\Security\SerializableUser;
use App\Services\ExportService;
use App\Services\PatrimoineService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\TDBM\ResultIterator;

class PatrimoineController extends AbstractVisialWebController
{
    /** @var PatrimoineService */
    private $patrimoineService;

    /** @var ExportService */
    private $exportService;

    /** @var PatrimoineLogementParametreDao */
    private $patrimoineLogementParametreDao;

    /** @var SimulationDao */
    private $simulationDao;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(UtilisateurDao $utilisateurDao, PatrimoineService $patrimoineService, PatrimoineLogementParametreDao $patrimoineLogementParametreDao, ExportService $exportService, SimulationDao $simulationDao, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($utilisateurDao);
        $this->patrimoineService = $patrimoineService;
        $this->patrimoineLogementParametreDao = $patrimoineLogementParametreDao;
        $this->exportService = $exportService;
        $this->simulationDao = $simulationDao;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return Patrimoine[]|ResultIterator
     *
     * @throws HTTPException
     *
     * @Query()
     */
    public function patrimoines(string $simulationID): ResultIterator
    {
        $this->validateUser();

        return $this->patrimoineService->fetchBySimulationId($simulationID);
    }

    /**
     * @throws HTTPException
     *
     * @Mutation()
     */
    public function savePatrimoine(Patrimoine $patrimoine): Patrimoine
    {
        $this->validateUser();

        $this->patrimoineService->save($patrimoine);

        $event = new SimulationUpdatedEvent($patrimoine->getSimulation());
        $this->eventDispatcher->dispatch($event);

        return $patrimoine;
    }

    /**
     * @throws HTTPException
     *
     * @Mutation()
     */
    public function removePatrimoine(string $patrimoineUUID, string $simulationId): bool
    {
        $this->validateUser();

        $this->patrimoineService->remove($patrimoineUUID);

        $simulation = $this->simulationDao->getById($simulationId);
        $event = new SimulationUpdatedEvent($simulation);
        $this->eventDispatcher->dispatch($event);

        return true;
    }

    /**
     * @return PatrimoineLogementParametre[]|ResultIterator
     *
     * @throws HTTPException
     *
     * @Query()
     */
    public function patrimoinesLogementsParametres(string $simulationID): ResultIterator
    {
        $this->validateUser();

        return $this->patrimoineLogementParametreDao->findBySimulationId($simulationID);
    }

    /**
     * @return Patrimoine[]|ResultIterator
     *
     * @throws HTTPException
     *
     * @Query()
     */
    public function patrimoinesfindByNumeroGroupe(string $simulationID, int $numeroGroupe): ResultIterator
    {
        $this->validateUser();

        return $this->patrimoineService->findBySimulationIdAndNumeroGroupe($simulationID, $numeroGroupe);
    }

    /**
     * @throws HTTPException
     *
     * @Mutation()
     */
    public function savePatrimoineLogementParametre(string $simulationId, ?int $nombrePondere, ?float $montantLoyers): ?PatrimoineLogementParametre
    {
        $this->validateUser();

        return $this->patrimoineService->savePatrimoineLogementParametre($simulationId, $nombrePondere, $montantLoyers);
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
     * @Route("/export-patrimoines/{simulationId}", name="exportPatrimoines")
     */
    public function exportPatrimoines(string $simulationId): Response
    {
        $file = $this->exportService->export([$this->exportService::SHEETLIST[10]], $simulationId);

        return $this->file($file['file'], $file['name'], ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     *  @Route("/import-patrimoines/{simulationId}", name="importPatrimoines")
     */
    public function importPatrimoines(Request $request, string $simulationId): Response
    {
        $notification = $this->exportService->import([$this->exportService::SHEETLIST[10]], $request, $simulationId);

        return new Response($notification);
    }
}
