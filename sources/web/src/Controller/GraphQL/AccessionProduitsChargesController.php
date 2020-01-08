<?php

declare(strict_types=1);

namespace App\Controller\GraphQL;

use App\Controller\AbstractVisialWebController;
use App\Dao\SimulationDao;
use App\Dao\UtilisateurDao;
use App\Event\SimulationUpdatedEvent;
use App\Exceptions\HTTPException;
use App\Model\AccessionProduitCharge;
use App\Security\SerializableUser;
use App\Services\AccessionProduitChargeService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\TDBM\ResultIterator;
use TheCodingMachine\TDBM\TDBMException;

class AccessionProduitsChargesController extends AbstractVisialWebController
{
    /** @var AccessionProduitChargeService */
    private $produitChargeService;

    /** @var SimulationDao */
    private $simulationDao;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(UtilisateurDao $utilisateurDao, AccessionProduitChargeService $produitChargeService, SimulationDao $simulationDao, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($utilisateurDao);
        $this->produitChargeService = $produitChargeService;
        $this->simulationDao = $simulationDao;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return AccessionProduitCharge[]|ResultIterator
     *
     * @throws HTTPException
     *
     * @Query()
     */
    public function accessionProduitCharges(string $simulationId): ResultIterator
    {
        /** @var SerializableUser $user */
        $user = $this->getUser();

        if (empty($user->getRoles())) {
            throw HTTPException::unauthorized("Vos droits ne vous permettent pas d'effectuer cette opération.");
        }

        return $this->produitChargeService->findBySimulation($simulationId);
    }

    /**
     * @throws HTTPException
     *
     * @Mutation()
     */
    public function saveAccessionProduitCharge(AccessionProduitCharge $produitCharge): AccessionProduitCharge
    {
        /** @var SerializableUser $user */
        $user = $this->getUser();

        if (empty($user->getRoles())) {
            throw HTTPException::unauthorized("Vos droits ne vous permettent pas d'effectuer cette opération.");
        }

        $this->produitChargeService->save($produitCharge);

        $event = new SimulationUpdatedEvent($produitCharge->getSimulation());
        $this->eventDispatcher->dispatch($event);

        return $produitCharge;
    }

    /**
     * @throws TDBMException
     * @throws HTTPException
     *
     * @Mutation()
     */
    public function removeAccessionProduitCharge(string $produitChargeId, string $simulationId): bool
    {
        /** @var SerializableUser $user */
        $user = $this->getUser();

        if (empty($user->getRoles())) {
            throw HTTPException::unauthorized("Vos droits ne vous permettent pas d'effectuer cette opération.");
        }

        $this->produitChargeService->remove($produitChargeId);

        $simulation = $this->simulationDao->getById($simulationId);
        $event = new SimulationUpdatedEvent($simulation);
        $this->eventDispatcher->dispatch($event);

        return true;
    }
}
