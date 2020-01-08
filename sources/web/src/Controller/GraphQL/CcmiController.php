<?php

declare(strict_types=1);

namespace App\Controller\GraphQL;

use App\Controller\AbstractVisialWebController;
use App\Dao\SimulationDao;
use App\Dao\UtilisateurDao;
use App\Event\SimulationUpdatedEvent;
use App\Exceptions\HTTPException;
use App\Model\Ccmi;
use App\Security\SerializableUser;
use App\Services\CcmiService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\TDBM\ResultIterator;

final class CcmiController extends AbstractVisialWebController
{
    /** @var CcmiService */
    private $ccmiService;

    /** @var SimulationDao */
    private $simulationDao;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(UtilisateurDao $utilisateurDao, CcmiService $ccmiService, SimulationDao $simulationDao, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($utilisateurDao);
        $this->utilisateurDao = $utilisateurDao;
        $this->ccmiService = $ccmiService;
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
     * @return ResultIterator|Ccmi[]
     *
     * @throws HTTPException
     *
     * @Query()
     */
    public function ccmi(string $simulationId): ResultIterator
    {
        $this->validateUser();

        return $this->ccmiService->fetchBySimulationId($simulationId);
    }

    /**
     * @throws HTTPException
     *
     * @Mutation()
     */
    public function saveCcmi(Ccmi $ccmi): Ccmi
    {
        $this->validateUser();
        $this->ccmiService->save($ccmi);

        $event = new SimulationUpdatedEvent($ccmi->getSimulation());
        $this->eventDispatcher->dispatch($event);

        return $ccmi;
    }

    /**
     * @throws HTTPException
     *
     * @Mutation()
     */
    public function removeCcmi(string $ccmiId, string $simulationId): bool
    {
        $this->validateUser();
        $this->ccmiService->remove($ccmiId);

        $simulation = $this->simulationDao->getById($simulationId);
        $event = new SimulationUpdatedEvent($simulation);
        $this->eventDispatcher->dispatch($event);

        return true;
    }
}
