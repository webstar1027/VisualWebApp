<?php

declare(strict_types=1);

namespace App\Controller\GraphQL;

use App\Controller\AbstractVisialWebController;
use App\Dao\AccessionCodificationDao;
use App\Dao\SimulationDao;
use App\Dao\UtilisateurDao;
use App\Event\SimulationUpdatedEvent;
use App\Exceptions\HTTPException;
use App\Model\AccessionCodification;
use App\Security\SerializableUser;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\TDBM\ResultIterator;
use TheCodingMachine\TDBM\TDBMException;
use Throwable;

class AccessionCodificationsController extends AbstractVisialWebController
{
    /** @var AccessionCodificationDao */
    private $codificationDao;

    /** @var SimulationDao */
    private $simulationDao;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(UtilisateurDao $utilisateurDao, AccessionCodificationDao $codificationDao, SimulationDao $simulationDao, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($utilisateurDao);
        $this->codificationDao = $codificationDao;
        $this->simulationDao = $simulationDao;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return AccessionCodification[]|ResultIterator
     *
     * @throws HTTPException
     *
     * @Query()
     */
    public function accessionCodifications(string $simulationId): ResultIterator
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
     *
     * @Mutation()
     */
    public function saveAccessionCodification(?string $uuid, string $simulationId, string $activite): AccessionCodification
    {
        /** @var SerializableUser $user */
        $user = $this->getUser();

        if (empty($user->getRoles())) {
            throw HTTPException::unauthorized("Vos droits ne vous permettent pas d'effectuer cette opération.");
        }

        $simulation = $this->simulationDao->getById($simulationId);
        if (empty($uuid)) {
            $codification = new AccessionCodification(
                $simulation,
                $activite
            );
            $this->codificationDao->save($codification);
        } else {
            $codification = $this->codificationDao->getById($uuid);
            $codification->setActivite($activite);

            $this->codificationDao->save($codification);
        }

        $event = new SimulationUpdatedEvent($simulation);
        $this->eventDispatcher->dispatch($event);

        return $codification;
    }

    /**
     * @throws HTTPException
     * @throws TDBMException
     *
     * @Mutation()
     */
    public function removeAccessionCodification(string $codificationUUID, string $simulationId): bool
    {
        /** @var SerializableUser $user */
        $user = $this->getUser();

        if (empty($user->getRoles())) {
            throw HTTPException::unauthorized("Vos droits ne vous permettent pas d'effectuer cette opération.");
        }

        try {
            $codification = $this->codificationDao->getById($codificationUUID);
        } catch (Throwable $e) {
            throw HTTPException::notFound("Cette codification n\'existe pas.", $e);
        }

        try {
            $this->codificationDao->delete($codification);

            $simulation = $this->simulationDao->getById($simulationId);
            $event = new SimulationUpdatedEvent($simulation);
            $this->eventDispatcher->dispatch($event);
        } catch (Throwable $e) {
            throw HTTPException::forbidden('Ce codification ne peut être supprimé car il est déjà utilisé', $e);
        }

        return true;
    }
}
