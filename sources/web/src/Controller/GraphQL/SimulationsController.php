<?php

declare(strict_types=1);

namespace App\Controller\GraphQL;

use App\Controller\AbstractVisialWebController;
use App\Dao\EntiteDao;
use App\Dao\SimulationDao;
use App\Dao\SimulationHistoriqueDao;
use App\Dao\UtilisateurDao;
use App\Event\SimulationClonedEvent;
use App\Event\SimulationCreatedEvent;
use App\Event\SimulationFusionedEvent;
use App\Exceptions\HTTPException;
use App\Exceptions\ValidatorException;
use App\Model\Simulation;
use App\Model\SimulationHistorique;
use App\Services\IndiceTauxService;
use Cake\Chronos\Chronos;
use DateTimeImmutable;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\TDBM\ResultIterator;
use TheCodingMachine\TDBM\TDBMException;

final class SimulationsController extends AbstractVisialWebController
{
    /** @var SimulationDao */
    private $simulationDao;
    /** @var SimulationHistoriqueDao */
    private $simulationHistoriqueDao;

    /** @var EntiteDao */
    private $entiteDao;
    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var IndiceTauxService */
    private $indiceTauxService;

    public function __construct(SimulationDao $simulationDao, SimulationHistoriqueDao $simulationHistoriqueDao, EntiteDao $entiteDao, UtilisateurDao $utilisateurDao, IndiceTauxService $indiceTauxService, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($utilisateurDao);
        $this->simulationDao = $simulationDao;
        $this->simulationHistoriqueDao = $simulationHistoriqueDao;
        $this->indiceTauxService = $indiceTauxService;
        $this->entiteDao = $entiteDao;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @Mutation()
     */
    public function copySimulation(string $id, string $nom): Simulation
    {
        $incremention = $this->simulationDao->findAll()->count() + 1;
        $simulation = $this->simulationDao->getById($id);
        $newSimulation = clone $simulation;
        $newSimulation->setNom($nom);
        $newSimulation->setIncrementation($incremention);
        $this->simulationDao->save($newSimulation);

        $event = new SimulationClonedEvent($newSimulation, $simulation);
        $this->eventDispatcher->dispatch($event);

        return $newSimulation;
    }

    /**
     * @return Simulation[]|ResultIterator
     *
     * @Query()
     */
    public function simulations(?string $entiteID, ?string $anneeDeReference, ?string $nom, ?string $ensembleId, ?string $entiteType, ?string $code): ResultIterator
    {
        $estAdministrateurCentral = $this->mustGetUtilisateur()->getEstAdministrateurCentral();
        $authenticatedUtilisateurId = $this->mustGetUtilisateur()->getId();

        if ($estAdministrateurCentral) {
            return $this->simulationDao->findByFilters($anneeDeReference, $nom, $entiteID, $ensembleId, $entiteType, $code);
        }

        return $this->simulationDao->filterSimulationByShare($authenticatedUtilisateurId, $entiteID, $anneeDeReference, $nom, $ensembleId, $entiteType, $code);
    }

    /**
     * @throws TDBMException
     *
     * @Query()
     * @Logged()
     */
    public function simulation(string $simulationID): Simulation
    {
        return $this->simulationDao->getById($simulationID);
    }

    /**
     * @throws TDBMException
     * @throws ValidatorException
     *
     * @Mutation()
     * @Logged()
     */
    public function saveSimulation(
        ?string $id,
        string $annee,
        string $entite,
        string $nom,
        ?string $description,
        bool $estVerrouillee,
        bool $estPartagee,
        bool $estFusionnee
    ): Simulation {
        $authenticatedUtilisateur = $this->mustGetUtilisateur();
        // we are creating a new simulation
        if (empty($id)) {
            $simulation = $this->simulationDao->findOneByNomAndUtilisateur($nom, $authenticatedUtilisateur);
            if (! empty($simulation)) {
                throw HTTPException::badRequest('Cette nom existe déjà');
            }

            $newSimulation = new Simulation(
                $authenticatedUtilisateur,
                $this->entiteDao->getById($entite),
                $nom,
                $annee
            );
            $newSimulation->setDescription($description);
            $newSimulation->setModifiePar(null);
            $newSimulation->setVerrouillePar($authenticatedUtilisateur);
            $this->simulationDao->save($newSimulation);
            $this->indiceTauxService->createIndicesTaux($newSimulation->getId());

            //If its a new simulation dispatch the simulation created event
            $event = new SimulationCreatedEvent($newSimulation);
            $this->eventDispatcher->dispatch($event);

            return $newSimulation;
        }
        //we are updating a simulation
        $existingSimulation = $this->simulationDao->getById($id);
        $existingSimulation->setNom($nom);
        $existingSimulation->setModifiePar($authenticatedUtilisateur);
        $existingSimulation->setAnneeDeReference($annee);
        $existingSimulation->setDescription($description);
        $existingSimulation->setDateModification(Chronos::now());
        $existingSimulation->setEntite($this->entiteDao->getById($entite));
        $existingSimulation->setEstVerrouillee($estVerrouillee);
        $existingSimulation->setEstPartagee($estPartagee);
        $existingSimulation->setEstFusionnee($estFusionnee);
        $existingSimulation->setVerrouillePar($authenticatedUtilisateur);
        $this->simulationDao->save($existingSimulation);

        return $existingSimulation;
    }

    /**
     * @throws TDBMException
     * @throws ValidatorException
     *
     * @Mutation()
     * @Logged()
     */
    public function deleteSimulation(string $simulationId): Simulation
    {
        $simulation = $this->simulationDao->getById($simulationId);
        if (empty($simulation)) {
            throw HTTPException::notFound('La simulation n\'existe pas');
        }
        $simulation->setSupprime(true);
        $this->simulationDao->save($simulation);

        return $simulation;
    }

    /**
     * @throws TDBMException
     * @throws ValidatorException
     *
     * @Mutation()
     * @Logged()
     */
    public function activateSimulation(string $simulationId): Simulation
    {
        $simulation = $this->simulationDao->getById($simulationId);
        if (empty($simulation)) {
            throw HTTPException::notFound('La simulation n\'existe pas');
        }
        $simulation->setSupprime(false);
        $this->simulationDao->save($simulation);

        return $simulation;
    }

    /**
     * @throws TDBMException
     * @throws ValidatorException
     *
     * @Mutation()
     * @Logged()
     */
    public function updateSimulationLock(string $simulationId, bool $locked): Simulation
    {
        $authenticatedUtilisateur = $this->mustGetUtilisateur();
        $simulation = $this->simulationDao->getById($simulationId);
        if (empty($simulation)) {
            throw HTTPException::notFound('La simulation n\'existe pas');
        }

        $simulation->setVerrouillePar($locked? $authenticatedUtilisateur: null);
        $this->simulationDao->save($simulation);

        return $simulation;
    }

    /**
     * @throws TDBMException
     * @throws ValidatorException
     *
     * @Mutation()
     * @Logged()
     */
    public function fusionSimulations(string $nom, string $simulationId1, string $simulationId2): Simulation
    {
        $authenticatedUtilisateur = $this->mustGetUtilisateur();

        $simulation1 = $this->simulationDao->getById($simulationId1);
        $simulation2 = $this->simulationDao->getById($simulationId2);
        if (empty($simulation1) || empty($simulation2)) {
            throw HTTPException::notFound('La simulation n\'existe pas');
        }

        // Deactivate old simulations
        $simulation1->setSupprime(true);
        $simulation2->setSupprime(true);
        $this->simulationDao->save($simulation1);
        $this->simulationDao->save($simulation2);

        // Fusion two simulations
        $simulation = $this->simulationDao->findOneByNomAndUtilisateur($nom, $authenticatedUtilisateur);
        if (! empty($simulation)) {
            throw HTTPException::badRequest('Cette nom existe déjà');
        }

        $newSimulation = new Simulation(
            $authenticatedUtilisateur,
            $simulation1->getEntite(),
            $nom,
            $simulation1->getAnneeDeReference()
        );
        $this->simulationDao->save($newSimulation);

        $event = new SimulationFusionedEvent($newSimulation, $simulation1, $simulation2);
        $this->eventDispatcher->dispatch($event);

        // Log fusion history
        $authenticatedUser = $this->mustGetUtilisateur();
        $note = "It's fusionned by " . $simulation1->getNom() . ' and ' . $simulation2->getNom();
        $newSimulationHistorique = new SimulationHistorique($newSimulation, $authenticatedUser, $note, new DateTimeImmutable());
        $this->simulationHistoriqueDao->save($newSimulationHistorique);

        return $newSimulation;
    }

    /**
     * @return SimulationHistorique[]|ResultIterator
     *
     * @throws TDBMException
     *
     * @Query
     */
    public function simulationHistoriques(string $simulationId): ResultIterator
    {
        $simulation = $this->simulationDao->getById($simulationId);

        return $this->simulationHistoriqueDao->getBySimulation($simulation);
    }
}
