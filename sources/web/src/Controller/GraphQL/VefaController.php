<?php

declare(strict_types=1);

namespace App\Controller\GraphQL;

use App\Controller\AbstractVisialWebController;
use App\Dao\SimulationDao;
use App\Dao\UtilisateurDao;
use App\Event\SimulationUpdatedEvent;
use App\Exceptions\HTTPException;
use App\Model\Vefa;
use App\Security\SerializableUser;
use App\Services\VefaService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\TDBM\ResultIterator;

final class VefaController extends AbstractVisialWebController
{
    /** @var VefaService */
    private $vefaService;

    /** @var SimulationDao */
    private $simulationDao;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(UtilisateurDao $utilisateurDao, VefaService $vefaService, SimulationDao $simulationDao, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($utilisateurDao);
        $this->utilisateurDao = $utilisateurDao;
        $this->vefaService = $vefaService;
        $this->simulationDao = $simulationDao;
        $this->eventDispatcher = $eventDispatcher;
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
     * @return ResultIterator|Vefa[]
     *
     * @throws HTTPException
     *
     * @Query()
     */
    public function vefa(string $simulationId, string $type): ResultIterator
    {
        $this->validateUser();

        return $this->vefaService->fetchBySimulationIdAndType($simulationId, $type);
    }

    /**
     * @throws HTTPException
     *
     * @Mutation()
     */
    public function saveVefa(Vefa $vefa): Vefa
    {
        $this->validateUser();
        $this->vefaService->save($vefa);

        $event = new SimulationUpdatedEvent($vefa->getSimulation());
        $this->eventDispatcher->dispatch($event);

        return $vefa;
    }

    /**
     * @throws HTTPException
     *
     * @Mutation()
     */
    public function removeVefa(string $vefaId, string $simulationId): bool
    {
        $this->validateUser();
        $this->vefaService->remove($vefaId);

        $simulation = $this->simulationDao->getById($simulationId);
        $event = new SimulationUpdatedEvent($simulation);
        $this->eventDispatcher->dispatch($event);

        return true;
    }
}
