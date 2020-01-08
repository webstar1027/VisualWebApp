<?php

declare(strict_types=1);

namespace App\Controller\GraphQL;

use App\Controller\AbstractVisialWebController;
use App\Dao\SimulationDao;
use App\Dao\UtilisateurDao;
use App\Event\SimulationUpdatedEvent;
use App\Exceptions\HTTPException;
use App\Model\Hypothese;
use App\Security\SerializableUser;
use App\Services\ExportService;
use App\Services\HypotheseService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\TDBM\ResultIterator;

class HypothesesController extends AbstractVisialWebController
{
    /** @var HypotheseService */
    private $hypothesesService;

    /** @var ExportService */
    private $exportService;

    /** @var SimulationDao */
    private $simulationDao;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(UtilisateurDao $utilisateurDao, HypotheseService $hypothesesService, ExportService $exportService, SimulationDao $simulationDao, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($utilisateurDao);
        $this->hypothesesService = $hypothesesService;
        $this->exportService = $exportService;
        $this->simulationDao = $simulationDao;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return Hypothese[]|ResultIterator
     *
     * @throws HTTPException
     *
     * @Query()
     */
    public function hypotheses(string $simulationID): ResultIterator
    {
        /** @var SerializableUser|null $user */
        $user = $this->getUser();

        if ($user === null || empty($user->getRoles())) {
            throw HTTPException::unauthorized("Vos droits ne vous permettent pas d'effectuer cette opération.");
        }

        return $this->hypothesesService->fetchBySimulationId($simulationID);
    }

    /**
     * @Route("/export-hypothese/{simulationId}", name="exportHypothese")
     */
    public function exportHypothese(string $simulationId): Response
    {
        $file = $this->exportService->export([$this->exportService::SHEETLIST[0]], $simulationId);

        return $this->file($file['file'], $file['name'], ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     * @Mutation()
     */
    public function saveHypothese(Hypothese $hypothese): Hypothese
    {
        $this->hypothesesService->save($hypothese);

        $event = new SimulationUpdatedEvent($hypothese->getSimulation());
        $this->eventDispatcher->dispatch($event);

        return $hypothese;
    }

    /**
     * @throws HTTPException
     *
     * @Mutation()
     */
    public function removeHypothese(string $hypotheseId, string $simulationId): bool
    {
        /** @var SerializableUser|null $user */
        $user = $this->getUser();

        if ($user === null || empty($user->getRoles())) {
            throw HTTPException::unauthorized("Vos droits ne vous permettent pas d'effectuer cette opération.");
        }

        $this->hypothesesService->remove($hypotheseId);

        $simulation = $this->simulationDao->getById($simulationId);
        $event = new SimulationUpdatedEvent($simulation);
        $this->eventDispatcher->dispatch($event);

        return true;
    }
}
