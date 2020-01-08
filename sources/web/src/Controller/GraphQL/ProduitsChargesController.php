<?php

declare(strict_types=1);

namespace App\Controller\GraphQL;

use App\Controller\AbstractVisialWebController;
use App\Dao\SimulationDao;
use App\Dao\UtilisateurDao;
use App\Event\SimulationUpdatedEvent;
use App\Exceptions\HTTPException;
use App\Model\ProduitCharge;
use App\Security\SerializableUser;
use App\Services\ProduitChargeService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\TDBM\ResultIterator;
use TheCodingMachine\TDBM\TDBMException;
use Throwable;

class ProduitsChargesController extends AbstractVisialWebController
{
        /** @var ProduitChargeService */
    private $produitChargeService;

    /** @var SimulationDao */
    private $simulationDao;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(UtilisateurDao $utilisateurDao, ProduitChargeService $produitChargeService, SimulationDao $simulationDao, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($utilisateurDao);
        $this->produitChargeService = $produitChargeService;
        $this->simulationDao = $simulationDao;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return ProduitCharge[]|ResultIterator
     *
     * @throws HTTPException
     *
     * @Query()
     */
    public function produitCharges(string $simulationId): ResultIterator
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
     * @throws TDBMException
     *
     * @Mutation()
     */
    public function saveProduitCharge(ProduitCharge $produitCharge): ProduitCharge
    {
        /** @var SerializableUser $user */
        $user = $this->getUser();

        if (empty($user->getRoles())) {
            throw HTTPException::unauthorized("Vos droits ne vous permettent pas d'effectuer cette opération.");
        }

        try {
            $this->produitChargeService->save($produitCharge);

            $event = new SimulationUpdatedEvent($produitCharge->getSimulation());
            $this->eventDispatcher->dispatch($event);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Ce produit existe déjà', $e);
        }

        return $produitCharge;
    }

    /**
     * @throws TDBMException
     * @throws HTTPException
     *
     * @Mutation()
     */
    public function removeProduitCharge(string $produitChargeId, string $simulationId): bool
    {
        /** @var SerializableUser $user */
        $user = $this->getUser();

        if (empty($user->getRoles())) {
            throw HTTPException::unauthorized("Vos droits ne vous permettent pas d'effectuer cette opération.");
        }

        try {
            $this->produitChargeService->remove($produitChargeId);

            $simulation = $this->simulationDao->getById($simulationId);
            $event = new SimulationUpdatedEvent($simulation);
            $this->eventDispatcher->dispatch($event);
        } catch (Throwable $e) {
            throw HTTPException::badRequest("Ce produit n\'existe pas", $e);
        }

        return true;
    }
}
