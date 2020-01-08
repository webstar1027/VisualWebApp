<?php

declare(strict_types=1);

namespace App\Controller\GraphQL;

use App\Controller\AbstractVisialWebController;
use App\Dao\SimulationDao;
use App\Dao\UtilisateurDao;
use App\Event\SimulationUpdatedEvent;
use App\Exceptions\HTTPException;
use App\Model\Lotissement;
use App\Security\SerializableUser;
use App\Services\LotissementService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\TDBM\ResultIterator;
use Throwable;

class LotissementController extends AbstractVisialWebController
{
    /** @var LotissementService */
    private $lotissementService;

    /** @var SimulationDao */
    private $simulationDao;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(UtilisateurDao $utilisateurDao, LotissementService $lotissementService, SimulationDao $simulationDao, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($utilisateurDao);
        $this->lotissementService = $lotissementService;
        $this->simulationDao = $simulationDao;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return Lotissement[]|ResultIterator
     *
     * @Query()
     */
    public function lotissements(string $simulationId): ResultIterator
    {
        $this->validateUser();

        return $this->lotissementService->findBySimulation($simulationId);
    }

    /**
     * @Mutation()
     */
    public function saveLotissement(Lotissement $lotissement): Lotissement
    {
        $this->validateUser();

        try {
            $this->lotissementService->save($lotissement);

            $event = new SimulationUpdatedEvent($lotissement->getSimulation());
            $this->eventDispatcher->dispatch($event);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Ce Lotissement existe déjà', $e);
        }

        return $lotissement;
    }

    /**
     * @Mutation()
     */
    public function removeLotissement(string $lotissementUUID, string $simulationId): bool
    {
        $this->validateUser();

        try {
            $this->lotissementService->remove($lotissementUUID);

            $simulation = $this->simulationDao->getById($simulationId);
            $event = new SimulationUpdatedEvent($simulation);
            $this->eventDispatcher->dispatch($event);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Ce Lotissement existe déjà', $e);
        }

        return true;
    }

    protected function validateUser(): void
    {
        /** @var SerializableUser $user */
        $user = $this->getUser();

        if (empty($user) || empty($user->getRoles())) {
            throw HTTPException::unauthorized("Vos droits ne vous permettent pas d'effectuer cette opération.");
        }
    }
}
