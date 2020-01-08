<?php

declare(strict_types=1);

namespace App\Controller\GraphQL;

use App\Controller\AbstractVisialWebController;
use App\Dao\SimulationDao;
use App\Dao\UtilisateurDao;
use App\Event\SimulationUpdatedEvent;
use App\Model\TravauxFoyer;
use App\Security\SerializableUser;
use App\Services\TravauxFoyerService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\TDBM\ResultIterator;
use Throwable;

final class TravauxFoyerController extends AbstractVisialWebController
{
    /** @var TravauxFoyerService */
    private $travauxFoyerService;

    /** @var SimulationDao */
    private $simulationDao;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(UtilisateurDao $utilisateurDao, TravauxFoyerService $travauxFoyerService, SimulationDao $simulationDao, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($utilisateurDao);
        $this->travauxFoyerService = $travauxFoyerService;
        $this->simulationDao = $simulationDao;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return TravauxFoyer[]|ResultIterator
     *
     * @Query()
     */
    public function travauxFoyers(string $simulationId): ResultIterator
    {
        $this->validateUser();

        return $this->travauxFoyerService->fetchBySimulationId($simulationId);
    }

    /**
     * @Mutation()
     */
    public function saveTravauxFoyer(TravauxFoyer $travauxFoyer): TravauxFoyer
    {
        $this->validateUser();

        try {
            $this->travauxFoyerService->save($travauxFoyer);

            $event = new SimulationUpdatedEvent($travauxFoyer->getSimulation());
            $this->eventDispatcher->dispatch($event);
        } catch (Throwable $e) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Ce Travaux Foyer existe déjà', $e);
        }

        return $travauxFoyer;
    }

    /**
     * @Mutation()
     */
    public function removeTravauxFoyer(string $travauxFoyerUUID, string $simulationId): bool
    {
        $this->validateUser();

        try {
            $this->travauxFoyerService->remove($travauxFoyerUUID);

            $simulation = $this->simulationDao->getById($simulationId);
            $event = new SimulationUpdatedEvent($simulation);
            $this->eventDispatcher->dispatch($event);
        } catch (Throwable $e) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Ce Travaux Foyer existe déjà', $e);
        }

        return true;
    }

    /**
     * @Mutation()
     */
    public function removeTypeDempruntTravauxFoyer(string $typesEmpruntsUUID, string $travauxFoyerUUID): bool
    {
        $this->validateUser();

        try {
            $this->travauxFoyerService->removeTypeDempruntTravauxFoyer($typesEmpruntsUUID, $travauxFoyerUUID);
        } catch (Throwable $e) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Ce Type Demprunt Travaux Foyer existe déjà', $e);
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
            throw new HttpException(
                Response::HTTP_UNAUTHORIZED,
                "Vos droits ne vous permettent pas d'effectuer cette opération."
            );
        }
    }
}
