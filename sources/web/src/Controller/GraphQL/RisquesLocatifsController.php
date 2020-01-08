<?php

declare(strict_types=1);

namespace App\Controller\GraphQL;

use App\Controller\AbstractVisialWebController;
use App\Dao\SimulationDao;
use App\Dao\UtilisateurDao;
use App\Event\SimulationUpdatedEvent;
use App\Exceptions\HTTPException;
use App\Model\RisqueLocatif;
use App\Security\SerializableUser;
use App\Services\ExportService;
use App\Services\RisqueLocatifService;
use Safe\Exceptions\JsonException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\TDBM\ResultIterator;
use TheCodingMachine\TDBM\TDBMException;

class RisquesLocatifsController extends AbstractVisialWebController
{
    /** @var RisqueLocatifService */
    private $risqueLocatifService;

    /** @var ExportService */
    private $exportService;

    /** @var SimulationDao */
    private $simulationDao;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(UtilisateurDao $utilisateurDao, RisqueLocatifService $risqueLocatifService, ExportService $exportService, SimulationDao $simulationDao, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($utilisateurDao);
        $this->risqueLocatifService = $risqueLocatifService;
        $this->exportService = $exportService;
        $this->simulationDao = $simulationDao;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return RisqueLocatif[]|ResultIterator
     *
     * @throws HTTPException
     *
     * @Query()
     */
    public function risquesLocatifs(string $simulationId): ResultIterator
    {
        /** @var SerializableUser|null $user */
        $user = $this->getUser();

        if ($user === null || empty($user->getRoles())) {
            throw HTTPException::unauthorized("Vos droits ne vous permettent pas d'effectuer cette opération.");
        }

        return $this->risqueLocatifService->getBySimulation($simulationId);
    }

    /**
     * @throws HTTPException
     * @throws TDBMException
     *
     * @Mutation()
     */
    public function saveRisqueLocatif(
        string $simulationId,
        int $iteration,
        float $value,
        int $type
    ): RisqueLocatif {
        /** @var SerializableUser|null $user */
        $user = $this->getUser();

        if ($user === null || empty($user->getRoles())) {
            throw HTTPException::unauthorized("Vos droits ne vous permettent pas d'effectuer cette opération.");
        }

        $simulation = $this->simulationDao->getById($simulationId);
        $event = new SimulationUpdatedEvent($simulation);
        $this->eventDispatcher->dispatch($event);

        return $this->risqueLocatifService->save($simulationId, $iteration, $value, $type);
    }

    /**
     * @throws JsonException
     * @throws TDBMException
     * @throws HTTPException
     *
     * @Mutation()
     */
    public function resetRisqueLocatif(string $simulationId, string $periodique, int $type): bool
    {
        /** @var SerializableUser|null $user */
        $user = $this->getUser();

        if ($user === null || empty($user->getRoles())) {
            throw HTTPException::unauthorized("Vos droits ne vous permettent pas d'effectuer cette opération.");
        }

        $this->risqueLocatifService->resetRisqueLocatif($simulationId, $periodique, $type);

        return true;
    }

    /**
     * @Route("/export-risques-locatifs/{simulationId}", name="exportRisquesLocatifs")
     */
    public function exportRisquesLocatifs(string $simulationId): Response
    {
        $file = $this->exportService->export([$this->exportService::SHEETLIST[7]], $simulationId);

        return $this->file($file['file'], $file['name'], ResponseHeaderBag::DISPOSITION_INLINE);
    }
}
