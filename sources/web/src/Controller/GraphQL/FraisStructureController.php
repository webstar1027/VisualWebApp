<?php

declare(strict_types=1);

namespace App\Controller\GraphQL;

use App\Controller\AbstractVisialWebController;
use App\Dao\SimulationDao;
use App\Dao\UtilisateurDao;
use App\Event\SimulationUpdatedEvent;
use App\Exceptions\HTTPException;
use App\Model\FraiStructure;
use App\Security\SerializableUser;
use App\Services\FraisStructureService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\TDBM\ResultIterator;
use TheCodingMachine\TDBM\TDBMException;

class FraisStructureController extends AbstractVisialWebController
{
        /** @var FraisStructureService */
    private $fraisStructureService;

    /** @var SimulationDao */
    private $simulationDao;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(UtilisateurDao $utilisateurDao, FraisStructureService $fraisStructureService, SimulationDao $simulationDao, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($utilisateurDao);
        $this->fraisStructureService = $fraisStructureService;
        $this->simulationDao = $simulationDao;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return FraiStructure[]|ResultIterator
     *
     * @throws HTTPException
     *
     * @Query()
     */
    public function fraisStructures(string $simulationId): ResultIterator
    {
            /** @var SerializableUser $user */
            $user = $this->getUser();

        if (empty($user->getRoles())) {
                throw HTTPException::unauthorized("Vos droits ne vous permettent pas d'effectuer cette opération.");
        }

            return $this->fraisStructureService->findBySimulation($simulationId);
    }

    /**
     * @throws HTTPException
     *
     * @Mutation()
     */
    public function saveFraisStructure(FraiStructure $fraiStructure): FraiStructure
    {
        /** @var SerializableUser $user */
        $user = $this->getUser();

        if (empty($user->getRoles())) {
            throw HTTPException::unauthorized("Vos droits ne vous permettent pas d'effectuer cette opération.");
        }

        $this->fraisStructureService->save($fraiStructure);

        $event = new SimulationUpdatedEvent($fraiStructure->getSimulation());
        $this->eventDispatcher->dispatch($event);

        return $fraiStructure;
    }

    /**
     * @throws TDBMException
     * @throws HTTPException
     *
     * @Mutation()
     */
    public function removeFraisStructure(string $fraisStructureId, string $simulationId): bool
    {
        /** @var SerializableUser $user */
        $user = $this->getUser();

        if (empty($user->getRoles())) {
            throw HTTPException::unauthorized("Vos droits ne vous permettent pas d'effectuer cette opération.");
        }

        $this->fraisStructureService->remove($fraisStructureId);

        $simulation = $this->simulationDao->getById($simulationId);
        $event = new SimulationUpdatedEvent($simulation);
        $this->eventDispatcher->dispatch($event);

        return true;
    }
}
