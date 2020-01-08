<?php

declare(strict_types=1);

namespace App\Controller\GraphQL;

use App\Controller\AbstractVisialWebController;
use App\Dao\SimulationDao;
use App\Dao\UtilisateurDao;
use App\Event\SimulationUpdatedEvent;
use App\Exceptions\HTTPException;
use App\Model\PatrimoineFoyer;
use App\Model\PatrimoineFoyerParametre;
use App\Security\SerializableUser;
use App\Services\ExportService;
use App\Services\PatrimoineFoyersService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\TDBM\ResultIterator;

final class PatrimoineFoyersController extends AbstractVisialWebController
{
    /** @var PatrimoineFoyersService */
    private $patrimoineFoyersService;
    /** @var ExportService */
    private $exportService;

    /** @var SimulationDao */
    private $simulationDao;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        UtilisateurDao $utilisateurDao,
        PatrimoineFoyersService $patrimoineFoyersService,
        SimulationDao $simulationDao,
        EventDispatcherInterface $eventDispatcher,
        ExportService $exportService
    ) {
        parent::__construct($utilisateurDao);
        $this->utilisateurDao = $utilisateurDao;
        $this->patrimoineFoyersService = $patrimoineFoyersService;
        $this->simulationDao = $simulationDao;
        $this->eventDispatcher = $eventDispatcher;
        $this->exportService = $exportService;
    }

    /**
     * @return PatrimoineFoyer[]|ResultIterator
     *
     * @throws HTTPException
     *
     * @Query()
     */
    public function patrimoinesFoyer(string $simulationID): ResultIterator
    {
        $this->validateUser();

        return $this->patrimoineFoyersService->fetchBySimulationId($simulationID);
    }

    /**
     * @throws HTTPException
     *
     * @Mutation()
     */
    public function savePatrimoineFoyer(PatrimoineFoyer $patrimoineFoyer): PatrimoineFoyer
    {
        $this->validateUser();
        $this->patrimoineFoyersService->save($patrimoineFoyer);

        $event = new SimulationUpdatedEvent($patrimoineFoyer->getSimulation());
        $this->eventDispatcher->dispatch($event);

        return $patrimoineFoyer;
    }

    /**
     * @throws HTTPException
     *
     * @Mutation()
     */
    public function removePatrimoineFoyer(string $patrimoineFoyerUUID, string $simulationId): bool
    {
        $this->validateUser();
        $this->patrimoineFoyersService->remove($patrimoineFoyerUUID);

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
     * @throws HTTPException
     *
     * @Mutation()
     */
    public function savePatrimoineFoyerParametre(string $simulationId, ?int $nombrePondereLogement): ?PatrimoineFoyerParametre
    {
        $this->validateUser();

        if ($nombrePondereLogement === null) {
            return $this->patrimoineFoyersService->savePatrimoineFoyerParametre($simulationId, null);
        }

        return $this->patrimoineFoyersService->savePatrimoineFoyerParametre($simulationId, $nombrePondereLogement);
    }

    /**
     * @throws HTTPException
     *
     * @Query()
     */
    public function patrimoineFoyerParametre(string $simulationId): ?PatrimoineFoyerParametre
    {
        $this->validateUser();

        return $this->patrimoineFoyersService->fetchParametreBySimulationId($simulationId);
    }

    /**
     * @Route("/export-patrimoine-foyers/{simulationId}", name="exportPatrimoineFoyers")
     */
    public function exportPatrimoineFoyers(string $simulationId): Response
    {
        $file = $this->exportService->export([$this->exportService::SHEETLIST[23]], $simulationId);

        return $this->file($file['file'], $file['name'], ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     *  @Route("/import-patrimoine-foyers/{simulationId}", name="importPatrimoineFoyers")
     */
    public function importPatrimoineFoyers(Request $request, string $simulationId): Response
    {
        $notification = $this->exportService->import([$this->exportService::SHEETLIST[23]], $request, $simulationId);

        return new Response($notification);
    }
}
