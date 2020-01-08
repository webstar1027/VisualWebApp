<?php

declare(strict_types=1);

namespace App\Controller\GraphQL;

use App\Controller\AbstractVisialWebController;
use App\Dao\SimulationDao;
use App\Dao\UtilisateurDao;
use App\Event\SimulationUpdatedEvent;
use App\Exceptions\HTTPException;
use App\Model\DemolitionFoyer;
use App\Security\SerializableUser;
use App\Services\DemolitionFoyerService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\TDBM\ResultIterator;
use TheCodingMachine\TDBM\TDBMException;

class DemolitionFoyersController extends AbstractVisialWebController
{
    /** @var DemolitionFoyerService */
    private $demolitionFoyerService;

    /** @var SimulationDao */
    private $simulationDao;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(UtilisateurDao $utilisateurDao, DemolitionFoyerService $demolitionFoyerService, SimulationDao $simulationDao, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($utilisateurDao);
        $this->demolitionFoyerService = $demolitionFoyerService;
        $this->simulationDao = $simulationDao;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return DemolitionFoyer[]|ResultIterator
     *
     * @throws HTTPException
     *
     * @Query()
     */
    public function demolitionFoyers(string $simulationId): ResultIterator
    {
        $this->validateUser();

        return $this->demolitionFoyerService->findBySimulation($simulationId);
    }

    /**
     * @throws HTTPException
     *
     * @Mutation()
     */
    public function saveDemolitionFoyer(DemolitionFoyer $demolitionFoyer): DemolitionFoyer
    {
        $this->validateUser();

        $this->demolitionFoyerService->save($demolitionFoyer);

        $event = new SimulationUpdatedEvent($demolitionFoyer->getSimulation());
        $this->eventDispatcher->dispatch($event);

        return $demolitionFoyer;
    }

    /**
     * @throws TDBMException
     * @throws HTTPException
     *
     * @Mutation()
     */
    public function removeDemolitionFoyer(string $demolitionFoyerUUID, string $simulationId): bool
    {
        $this->validateUser();

        $this->demolitionFoyerService->remove($demolitionFoyerUUID);

        $simulation = $this->simulationDao->getById($simulationId);
        $event = new SimulationUpdatedEvent($simulation);
        $this->eventDispatcher->dispatch($event);

        return true;
    }

    /**
     * @throws HTTPException
     *
     * @Mutation()
     */
    public function removeTypeDempruntDemolitionFoyer(string $typesEmpruntsUUID, string $demolitionFoyerUUID): bool
    {
        $this->validateUser();

        $this->demolitionFoyerService->removeTypeDempruntDemolitionFoyer($typesEmpruntsUUID, $demolitionFoyerUUID);

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
}
