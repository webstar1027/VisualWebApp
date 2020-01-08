<?php

declare(strict_types=1);

namespace App\Controller\GraphQL;

use App\Controller\AbstractVisialWebController;
use App\Dao\SimulationDao;
use App\Dao\UtilisateurDao;
use App\Event\SimulationUpdatedEvent;
use App\Model\NouveauxFoyer;
use App\Security\SerializableUser;
use App\Services\NouveauxFoyerService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\TDBM\ResultIterator;
use Throwable;

class NouveauxFoyerController extends AbstractVisialWebController
{
    /** @var NouveauxFoyerService */
    private $nouveauxFoyerService;

    /** @var SimulationDao */
    private $simulationDao;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(UtilisateurDao $utilisateurDao, NouveauxFoyerService $nouveauxFoyerService, SimulationDao $simulationDao, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($utilisateurDao);
        $this->nouveauxFoyerService = $nouveauxFoyerService;
        $this->simulationDao = $simulationDao;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return NouveauxFoyer[]|ResultIterator
     *
     * @Query()
     */
    public function nouveauxFoyers(string $simulationId): ResultIterator
    {
        $this->validateUser();

        return $this->nouveauxFoyerService->fetchBySimulationId($simulationId);
    }

    /**
     * @Mutation()
     */
    public function saveNouveauxFoyer(NouveauxFoyer $nouveauxFoyer): NouveauxFoyer
    {
        $this->validateUser();

        try {
            $this->nouveauxFoyerService->save($nouveauxFoyer);

            $event = new SimulationUpdatedEvent($nouveauxFoyer->getSimulation());
            $this->eventDispatcher->dispatch($event);
        } catch (Throwable $e) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Ce Nouveaux Foyer existe déjà', $e);
        }

        return $nouveauxFoyer;
    }

    /**
     * @Mutation()
     */
    public function removeNouveauxFoyer(string $nouveauxFoyerUUID, string $simulationId): bool
    {
        $this->validateUser();

        try {
            $this->nouveauxFoyerService->remove($nouveauxFoyerUUID);

            $simulation = $this->simulationDao->getById($simulationId);
            $event = new SimulationUpdatedEvent($simulation);
            $this->eventDispatcher->dispatch($event);
        } catch (Throwable $e) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Ce Nouveaux Foyer existe déjà', $e);
        }

        return true;
    }

    /**
     * @Mutation()
     */
    public function removeTypeDempruntNouveauxFoyer(string $typesEmpruntsUUID, string $nouveauxFoyerUUID): bool
    {
        $this->validateUser();

        try {
            $this->nouveauxFoyerService->removeTypeDempruntNouveauxFoyer($typesEmpruntsUUID, $nouveauxFoyerUUID);
        } catch (Throwable $e) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Ce Type Demprunt Nouveaux Foyer existe déjà', $e);
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
