<?php

declare(strict_types=1);

namespace App\Controller\GraphQL;

use App\Controller\AbstractVisialWebController;
use App\Dao\CodificationDao;
use App\Dao\SimulationDao;
use App\Dao\UtilisateurDao;
use App\Event\SimulationUpdatedEvent;
use App\Exceptions\HTTPException;
use App\Model\Codification;
use App\Security\SerializableUser;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\TDBM\ResultIterator;
use TheCodingMachine\TDBM\TDBMException;
use Throwable;

class CodificationsController extends AbstractVisialWebController
{
    /** @var CodificationDao */
    private $codificationDao;

    /** @var SimulationDao */
    private $simulationDao;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(UtilisateurDao $utilisateurDao, CodificationDao $codificationDao, SimulationDao $simulationDao, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($utilisateurDao);
        $this->codificationDao = $codificationDao;
        $this->simulationDao = $simulationDao;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return Codification[]|ResultIterator
     *
     * @throws HTTPException
     *
     * @Query()
     */
    public function codifications(string $simulationId): ResultIterator
    {
        /** @var SerializableUser $user */
        $user = $this->getUser();

        if (empty($user->getRoles())) {
            throw HTTPException::unauthorized("Vos droits ne vous permettent pas d'effectuer cette opération.");
        }

        return $this->codificationDao->findBySimulationId($simulationId);
    }

    /**
     * @throws HTTPException
     * @throws TDBMException
     *
     * @Mutation()
     */
    public function saveCodification(?string $uuid, string $simulationId, string $activite): Codification
    {
        /** @var SerializableUser $user */
        $user = $this->getUser();

        if (empty($user->getRoles())) {
            throw HTTPException::unauthorized("Vos droits ne vous permettent pas d'effectuer cette opération.");
        }

        $simulation = $this->simulationDao->getById($simulationId);
        if (empty($uuid)) {
            $numero = $this->codificationDao->getMaxNumeroBySimulationId($simulationId);
            $codification = new Codification(
                $simulation,
                $numero,
                $activite
            );
            $this->codificationDao->save($codification);

            $event = new SimulationUpdatedEvent($simulation);
            $this->eventDispatcher->dispatch($event);

            return $codification;
        }

        $codification = $this->codificationDao->getById($uuid);
        $codification->setActivite($activite);

        try {
            $this->codificationDao->save($codification);

            $event = new SimulationUpdatedEvent($simulation);
            $this->eventDispatcher->dispatch($event);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Cette codification existe déjà', $e);
        }

        return $codification;
    }

    /**
     * @throws TDBMException
     * @throws HTTPException
     *
     * @Mutation()
     */
    public function removeCodification(string $codificationUUID, string $simulationId): bool
    {
        /** @var SerializableUser $user */
        $user = $this->getUser();

        if (empty($user->getRoles())) {
            throw HTTPException::unauthorized("Vos droits ne vous permettent pas d'effectuer cette opération.");
        }

        try {
            $codification = $this->codificationDao->getById($codificationUUID);
            $this->codificationDao->delete($codification);

            $simulation = $this->simulationDao->getById($simulationId);
            $event = new SimulationUpdatedEvent($simulation);
            $this->eventDispatcher->dispatch($event);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Cette codification ne peut pas être supprimée.', $e);
        }

        return true;
    }
}
