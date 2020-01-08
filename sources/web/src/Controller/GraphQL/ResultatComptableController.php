<?php

declare(strict_types=1);

namespace App\Controller\GraphQL;

use App\Controller\AbstractVisialWebController;
use App\Dao\SimulationDao;
use App\Dao\UtilisateurDao;
use App\Event\SimulationUpdatedEvent;
use App\Exceptions\HTTPException;
use App\Model\ResultatComptable;
use App\Security\SerializableUser;
use App\Services\ExportService;
use App\Services\ResultatComptableService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\TDBM\ResultIterator;
use TheCodingMachine\TDBM\TDBMException;

final class ResultatComptableController extends AbstractVisialWebController
{
    /** @var ResultatComptableService */
    private $resultatComptableService;

    /** @var ExportService */
    private $exportService;

    /** @var SimulationDao */
    private $simulationDao;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        UtilisateurDao $utilisateurDao,
        ResultatComptableService $resultatComptableService,
        ExportService $exportService,
        SimulationDao $simulationDao,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($utilisateurDao);
        $this->resultatComptableService = $resultatComptableService;
        $this->exportService = $exportService;
        $this->simulationDao = $simulationDao;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return ResultatComptable[]|ResultIterator
     *
     * @throws HTTPException
     *
     * @Query()
     */
    public function resultatComptables(string $simulationId): ResultIterator
    {
        $this->validateUser();

        return $this->resultatComptableService->fetchBySimulationId($simulationId);
    }

    /**
     * @throws HTTPException
     *
     * @Mutation()
     */
    public function saveResultatComptable(ResultatComptable $resultatComptable): ResultatComptable
    {
        $this->validateUser();

        $this->resultatComptableService->save($resultatComptable);

        $event = new SimulationUpdatedEvent($resultatComptable->getSimulation());
        $this->eventDispatcher->dispatch($event);

        return $resultatComptable;
    }

    /**
     * @throws TDBMException
     * @throws HTTPException
     *
     * @Mutation()
     */
    public function removeResultatComptable(string $resultatComptableUUID, string $simulationId): bool
    {
        $this->validateUser();

        $this->resultatComptableService->remove($resultatComptableUUID);

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
     * @Route("/export-resultat-compatible/{simulationId}", name="exportResultatCompatible")
     */
    public function exportResultatCompatible(string $simulationId): Response
    {
        $file = $this->exportService->export([$this->exportService::SHEETLIST[2]], $simulationId);

        return $this->file($file['file'], $file['name'], ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     *  @Route("/import-resultat-compatible/{simulationId}", name="importResultatCompatible")
     */
    public function importResultatCompatible(Request $request, string $simulationId): Response
    {
        $notification = $this->exportService->import([$this->exportService::SHEETLIST[2]], $request, $simulationId);

        return new Response($notification);
    }
}
