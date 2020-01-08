<?php

declare(strict_types=1);

namespace App\Services;

use App\Dao\NouveauxFoyerDao;
use App\Dao\NouveauxFoyerPeriodiqueDao;
use App\Dao\TypeEmpruntNouveauxFoyerDao;
use App\Exceptions\HTTPException;
use App\Model\NouveauxFoyer;
use App\Model\Simulation;
use TheCodingMachine\TDBM\ResultIterator;
use TheCodingMachine\TDBM\TDBMException;
use Throwable;
use function count;

class NouveauxFoyerService
{
    /** @var NouveauxFoyerDao */
    private $nouveauxFoyerDao;
    /** @var TypeEmpruntNouveauxFoyerDao */
    private $typeEmpruntNouveauxFoyerDao;
    /** @var NouveauxFoyerPeriodiqueDao */
    private $periodiqueDao;

    public function __construct(NouveauxFoyerDao $nouveauxFoyerDao, TypeEmpruntNouveauxFoyerDao $typeEmpruntNouveauxFoyerDao, NouveauxFoyerPeriodiqueDao $periodiqueDao)
    {
        $this->nouveauxFoyerDao = $nouveauxFoyerDao;
        $this->typeEmpruntNouveauxFoyerDao = $typeEmpruntNouveauxFoyerDao;
        $this->periodiqueDao = $periodiqueDao;
    }

    public function save(NouveauxFoyer $nouveauxFoyer): void
    {
        try {
            $this->nouveauxFoyerDao->save($nouveauxFoyer);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Ce nouveau foyer existe déjà.', $e);
        }
    }

    /**
     * @throws TDBMException
     * @throws HTTPException
     */
    public function remove(string $nouveauxFoyerUUID): void
    {
        try {
            $nouveauxFoyer = $this->nouveauxFoyerDao->getById($nouveauxFoyerUUID);
            $this->nouveauxFoyerDao->delete($nouveauxFoyer, true);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Ce nouveau foyer n\'existe déjà.', $e);
        }
    }

    public function removeTypeDempruntNouveauxFoyer(string $typesEmpruntsUUID, string $nouveauxFoyerUUID): void
    {
        $typeEmpruntNouveauxFoyer = $this->typeEmpruntNouveauxFoyerDao->findByTypeEmpruntAndNouveauxFoyer(
            $typesEmpruntsUUID,
            $nouveauxFoyerUUID
        );

        if (empty($typeEmpruntNouveauxFoyer)) {
            return;
        }

        $this->typeEmpruntNouveauxFoyerDao->delete($typeEmpruntNouveauxFoyer);
    }

    /**
     * @return NouveauxFoyer[]|ResultIterator
     */
    public function fetchBySimulationId(string $simulationID): ResultIterator
    {
        return $this->nouveauxFoyerDao->findBySimulationID($simulationID);
    }

    public function cloneNouveauxFoyer(Simulation $newSimulation, Simulation $oldSimulation): void
    {
        $objects = $this->nouveauxFoyerDao->findBySimulationID($oldSimulation->getId());
        foreach ($objects as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getNouveauxFoyerPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setNouveauxFoyer($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
            foreach ($object->getTypeEmpruntNouveauxFoyer() as $typeEmpruntObject) {
                $newTypeEmprunt = clone $typeEmpruntObject;
                $newTypeEmprunt->setNouveauxFoyer($newObject);
                $this->typeEmpruntNouveauxFoyerDao->save($newTypeEmprunt);
            }
        }
    }

    public function fusionNouveauxFoyer(Simulation $newSimulation, Simulation $oldSimulation1, Simulation $oldSimulation2): void
    {
        $objects1 = $this->nouveauxFoyerDao->findBySimulationID($oldSimulation1->getId());
        $objects2 = $this->nouveauxFoyerDao->findBySimulationID($oldSimulation2->getId());
        foreach ($objects1 as $key => $object) {
            $newObject = clone $object;
            $newObject->setNumero($key + 1);
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getNouveauxFoyerPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setNouveauxFoyer($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
            foreach ($object->getTypeEmpruntNouveauxFoyer() as $typeEmpruntObject) {
                $newTypeEmprunt = clone $typeEmpruntObject;
                $newTypeEmprunt->setNouveauxFoyer($newObject);
                $this->typeEmpruntNouveauxFoyerDao->save($newTypeEmprunt);
            }
        }
        foreach ($objects2 as $key => $object) {
            $newObject = clone $object;
            $newObject->setNumero(count($objects1) + $key + 1);
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getNouveauxFoyerPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setNouveauxFoyer($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
            foreach ($object->getTypeEmpruntNouveauxFoyer() as $typeEmpruntObject) {
                $newTypeEmprunt = clone $typeEmpruntObject;
                $newTypeEmprunt->setNouveauxFoyer($newObject);
                $this->typeEmpruntNouveauxFoyerDao->save($newTypeEmprunt);
            }
        }
    }
}
