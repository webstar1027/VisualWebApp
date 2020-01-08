<?php

declare(strict_types=1);

namespace App\Services;

use App\Dao\DemolitionFoyerDao;
use App\Dao\DemolitionFoyerPeriodiqueDao;
use App\Dao\TypeEmpruntDemolitionFoyerDao;
use App\Exceptions\HTTPException;
use App\Model\DemolitionFoyer;
use App\Model\Simulation;
use TheCodingMachine\TDBM\ResultIterator;
use TheCodingMachine\TDBM\TDBMException;
use Throwable;
use function count;

final class DemolitionFoyerService
{
    /** @var DemolitionFoyerDao */
    private $demolitionFoyerDao;

    /** @var TypeEmpruntDemolitionFoyerDao */
    private $typeEmpruntDemolitionFoyerDao;
    /** @var DemolitionFoyerPeriodiqueDao */
    private $periodiqueDao;

    public function __construct(
        DemolitionFoyerDao $demolitionFoyerDao,
        TypeEmpruntDemolitionFoyerDao $typeEmpruntDemolitionFoyerDao,
        DemolitionFoyerPeriodiqueDao $periodiqueDao
    ) {
        $this->demolitionFoyerDao = $demolitionFoyerDao;
        $this->typeEmpruntDemolitionFoyerDao = $typeEmpruntDemolitionFoyerDao;
        $this->periodiqueDao = $periodiqueDao;
    }

    /**
     * @throws HTTPException
     */
    public function save(DemolitionFoyer $demolitionFoyer): void
    {
        try {
            $this->demolitionFoyerDao->save($demolitionFoyer);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Cette démolition foyer existe déjà.', $e);
        }
    }

    /**
     * @throws TDBMException
     */
    public function remove(string $demolitionFoyerUUID): void
    {
        try {
            $demolitionFoyer = $this->demolitionFoyerDao->getById($demolitionFoyerUUID);
            $this->demolitionFoyerDao->delete($demolitionFoyer, true);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Cette démolition foyer n\'existe pas.', $e);
        }
    }

    /**
     * @throws HTTPException
     */
    public function removeTypeDempruntDemolitionFoyer(string $typesEmpruntsUUID, string $demolitionFoyerUUID): void
    {
        $typeEmpruntDemolition = $this->typeEmpruntDemolitionFoyerDao->findByTypeEmpruntAndDemolitionFoyer(
            $typesEmpruntsUUID,
            $demolitionFoyerUUID
        );

        if ($typeEmpruntDemolition === null) {
            throw HTTPException::badRequest('Ce type d\'emprunt est déjà utilisé.');
        }

        $this->typeEmpruntDemolitionFoyerDao->delete($typeEmpruntDemolition);
    }

    /**
     * @return DemolitionFoyer[]|ResultIterator
     */
    public function findBySimulation(string $simulationId): ResultIterator
    {
        return $this->demolitionFoyerDao->findBySimulationID($simulationId);
    }

    public function cloneDemolitionFoyer(Simulation $newSimulation, Simulation $oldSimulation): void
    {
        $objects = $this->demolitionFoyerDao->findBySimulationId($oldSimulation->getId());
        foreach ($objects as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getDemolitionFoyersPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setDemolitionFoyers($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
            foreach ($object->getTypeEmpruntDemolitionFoyers() as $typeEmpruntObject) {
                $newTypeEmprunt = clone $typeEmpruntObject;
                $newTypeEmprunt->setDemolitionFoyers($newObject);
                $this->typeEmpruntDemolitionFoyerDao->save($newTypeEmprunt);
            }
        }
    }

    public function fusionDemolitionFoyer(Simulation $newSimulation, Simulation $oldSimulation1, Simulation $oldSimulation2): void
    {
        $objects1 = $this->demolitionFoyerDao->findBySimulationId($oldSimulation1->getId());
        $objects2 = $this->demolitionFoyerDao->findBySimulationId($oldSimulation2->getId());
        foreach ($objects1 as $key => $object) {
            $newObject = clone $object;
            $newObject->setNumero($key + 1);
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getDemolitionFoyersPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setDemolitionFoyers($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
            foreach ($object->getTypeEmpruntDemolitionFoyers() as $typeEmpruntObject) {
                $newTypeEmprunt = clone $typeEmpruntObject;
                $newTypeEmprunt->setDemolitionFoyers($newObject);
                $this->typeEmpruntDemolitionFoyerDao->save($newTypeEmprunt);
            }
        }
        foreach ($objects2 as $key => $object) {
            $newObject = clone $object;
            $newObject->setNumero(count($objects1) + $key + 1);
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getDemolitionFoyersPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setDemolitionFoyers($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
            foreach ($object->getTypeEmpruntDemolitionFoyers() as $typeEmpruntObject) {
                $newTypeEmprunt = clone $typeEmpruntObject;
                $newTypeEmprunt->setDemolitionFoyers($newObject);
                $this->typeEmpruntDemolitionFoyerDao->save($newTypeEmprunt);
            }
        }
    }
}
