<?php

declare(strict_types=1);

namespace App\Services;

use App\Dao\TravauxFoyerDao;
use App\Dao\TravauxFoyerPeriodiqueDao;
use App\Dao\TypeEmpruntTravauxFoyerDao;
use App\Exceptions\HTTPException;
use App\Model\Simulation;
use App\Model\TravauxFoyer;
use TheCodingMachine\TDBM\ResultIterator;
use TheCodingMachine\TDBM\TDBMException;
use Throwable;
use function count;

class TravauxFoyerService
{
    /** @var TravauxFoyerDao */
    private $travauxFoyerDao;
    /** @var TypeEmpruntTravauxFoyerDao */
    private $typeEmpruntTravauxFoyerDao;
    /** @var TravauxFoyerPeriodiqueDao */
    private $periodiqueDao;

    public function __construct(
        TravauxFoyerDao $travauxFoyerDao,
        TypeEmpruntTravauxFoyerDao $typeEmpruntTravauxFoyerDao,
        TravauxFoyerPeriodiqueDao $periodiqueDao
    ) {
        $this->travauxFoyerDao = $travauxFoyerDao;
        $this->typeEmpruntTravauxFoyerDao = $typeEmpruntTravauxFoyerDao;
        $this->periodiqueDao = $periodiqueDao;
    }

    /**
     * @throws HTTPException
     */
    public function save(TravauxFoyer $travauxFoyer): void
    {
        try {
            $this->travauxFoyerDao->save($travauxFoyer);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Ces travaux existent déjà.', $e);
        }
    }

    /**
     * @throws TDBMException
     */
    public function remove(string $travauxFoyerUUID): void
    {
        try {
            $travauxFoyer = $this->travauxFoyerDao->getById($travauxFoyerUUID);
            $this->travauxFoyerDao->delete($travauxFoyer, true);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Ces travaux n\'existent pas.', $e);
        }
    }

    public function removeTypeDempruntTravauxFoyer(string $typesEmpruntsUUID, string $travauxFoyerUUID): void
    {
        $typeEmpruntTravauxFoyer = $this->typeEmpruntTravauxFoyerDao->findByTypeEmpruntAndTravauxFoyer(
            $typesEmpruntsUUID,
            $travauxFoyerUUID
        );

        if (empty($typeEmpruntTravauxFoyer)) {
            return;
        }

        $this->typeEmpruntTravauxFoyerDao->delete($typeEmpruntTravauxFoyer);
    }

    /**
     * @return TravauxFoyer[]|ResultIterator
     */
    public function fetchBySimulationId(string $simulationID): ResultIterator
    {
        return $this->travauxFoyerDao->findBySimulationID($simulationID);
    }

    public function cloneTravauxFoyer(Simulation $newSimulation, Simulation $oldSimulation): void
    {
        $objects = $this->travauxFoyerDao->findBySimulationID($oldSimulation->getId());
        foreach ($objects as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $this->travauxFoyerDao->save($newObject);
            foreach ($object->getTravauxFoyerPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setTravauxFoyer($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
            foreach ($object->getTypeEmpruntTravauxFoyer() as $typeEmpruntObject) {
                $newTypeEmprunt = clone $typeEmpruntObject;
                $newTypeEmprunt->setTravauxFoyer($newObject);
                $this->typeEmpruntTravauxFoyerDao->save($newTypeEmprunt);
            }
        }
    }

    public function fusionTravauxFoyer(Simulation $newSimulation, Simulation $oldSimulation1, Simulation $oldSimulation2): void
    {
        $objects1 = $this->travauxFoyerDao->findBySimulationID($oldSimulation1->getId());
        $objects2 = $this->travauxFoyerDao->findBySimulationID($oldSimulation2->getId());
        foreach ($objects1 as $key => $object) {
            $newObject = clone $object;
            $newObject->setNumero($key + 1);
            $newObject->setSimulation($newSimulation);
            $this->travauxFoyerDao->save($newObject);
            foreach ($object->getTravauxFoyerPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setTravauxFoyer($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
            foreach ($object->getTypeEmpruntTravauxFoyer() as $typeEmpruntObject) {
                $newTypeEmprunt = clone $typeEmpruntObject;
                $newTypeEmprunt->setTravauxFoyer($newObject);
                $this->typeEmpruntTravauxFoyerDao->save($newTypeEmprunt);
            }
        }
        foreach ($objects2 as $key => $object) {
            $newObject = clone $object;
            $newObject->setNumero(count($objects1) + $key + 1);
            $newObject->setSimulation($newSimulation);
            $this->travauxFoyerDao->save($newObject);
            foreach ($object->getTravauxFoyerPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setTravauxFoyer($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
            foreach ($object->getTypeEmpruntTravauxFoyer() as $typeEmpruntObject) {
                $newTypeEmprunt = clone $typeEmpruntObject;
                $newTypeEmprunt->setTravauxFoyer($newObject);
                $this->typeEmpruntTravauxFoyerDao->save($newTypeEmprunt);
            }
        }
    }
}
