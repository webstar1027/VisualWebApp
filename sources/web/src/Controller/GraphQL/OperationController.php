<?php

declare(strict_types=1);

namespace App\Controller\GraphQL;

use App\Controller\AbstractVisialWebController;
use App\Dao\SimulationDao;
use App\Dao\UtilisateurDao;
use App\Event\SimulationUpdatedEvent;
use App\Model\Operation;
use App\Security\SerializableUser;
use App\Services\OperationService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\TDBM\ResultIterator;
use Throwable;

class OperationController extends AbstractVisialWebController
{
    /** @var OperationService */
    private $operationService;

    /** @var SimulationDao */
    private $simulationDao;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(UtilisateurDao $utilisateurDao, OperationService $operationService, SimulationDao $simulationDao, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($utilisateurDao);
        $this->operationService = $operationService;
        $this->simulationDao = $simulationDao;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return Operation[]|ResultIterator
     *
     * @Query()
     */
    public function operations(string $simulationId): ResultIterator
    {
        $this->validateUser();

        return $this->operationService->findBySimulation(
            $simulationId
        );
    }

    /**
     * @Mutation()
     */
    public function saveOperation(Operation $operation): Operation
    {
        $this->validateUser();

        try {
            $this->operationService->save($operation);

            $event = new SimulationUpdatedEvent($operation->getSimulation());
            $this->eventDispatcher->dispatch($event);
        } catch (Throwable $e) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Ce Operation existe déjà', $e);
        }

        return $operation;
    }

    /**
     * @Mutation()
     */
    public function removeOperation(string $operationUUID, string $simulationId): bool
    {
        $this->validateUser();

        try {
            $this->operationService->remove($operationUUID);

            $simulation = $this->simulationDao->getById($simulationId);
            $event = new SimulationUpdatedEvent($simulation);
            $this->eventDispatcher->dispatch($event);
        } catch (Throwable $e) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Ce Operation existe déjà', $e);
        }

        return true;
    }

    /**
     * @Mutation()
     */
    public function removeTypeDempruntOperation(string $typesEmpruntsUUID, string $operationUUID): bool
    {
        $this->validateUser();

        try {
            $this->operationService->removeTypeDempruntOperation($typesEmpruntsUUID, $operationUUID);
        } catch (Throwable $e) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Ce TypeDemprunt Operation existe déjà', $e);
        }

        return true;
    }

    /**
     * @throws HttpException
     */
    protected function validateUser(): void
    {
        /** @var SerializableUser $user */
        $user = $this->getUser();

        if (empty($user) || empty($user->getRoles())) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "Vos droits ne vous permettent pas d'effectuer cette opération.");
        }
    }
}
