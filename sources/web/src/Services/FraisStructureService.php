<?php

declare(strict_types=1);

namespace App\Services;

use App\Dao\FraiStructureDao;
use App\Dao\FraiStructurePeriodiqueDao;
use App\Exceptions\HTTPException;
use App\Model\Factories\FraisStructureFactory;
use App\Model\FraiStructure;
use App\Model\FraiStructurePeriodique;
use App\Model\Simulation;
use Safe\Exceptions\JsonException;
use TheCodingMachine\TDBM\ResultIterator;
use TheCodingMachine\TDBM\TDBMException;
use Throwable;

class FraisStructureService
{
    /** @var FraiStructureDao */
    private $fraiStructureDao;
    /** @var FraisStructureFactory */
    private $factory;
    /** @var FraiStructurePeriodiqueDao */
    private $periodiqueDao;

    public function __construct(FraiStructureDao $fraiStructureDao, FraisStructureFactory $factory, FraiStructurePeriodiqueDao $periodiqueDao)
    {
        $this->fraiStructureDao = $fraiStructureDao;
        $this->factory = $factory;
        $this->periodiqueDao = $periodiqueDao;
    }

    public function save(FraiStructure $fraisStructure): void
    {
        try {
            $this->fraiStructureDao->save($fraisStructure);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Ce frais de structure existe déjà.', $e);
        }
    }

    /**
     * @throws TDBMException
     * @throws HTTPException
     */
    public function remove(string $fraisStructureId): void
    {
        try {
            $fraisStructure = $this->fraiStructureDao->getById($fraisStructureId);
            $this->fraiStructureDao->delete($fraisStructure, true);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Ce frais de structure n\'existe pas.', $e);
        }
    }

    /**
     * @return FraiStructure[]|ResultIterator
     */
    public function findBySimulation(string $simulationId): ResultIterator
    {
        return $this->fraiStructureDao->findBySimulationID($simulationId);
    }

    /**
     * @throws TDBMException
     * @throws JsonException
     * @throws HTTPException
     */
    public function createDefaultFraiStructures(Simulation $newSimulation): void
    {
        foreach (FraiStructure::TYPE_LIST as $label => $type) {
            $fraisStructure = $this->factory->createFraiStructure(null, $newSimulation->getId(), $label, 0.0, true, $type, null);
            $this->save($fraisStructure);
            $this->createDefaultPeriodique($fraisStructure);
        }
    }

    private function createDefaultPeriodique(FraiStructure $fraisStructure): void
    {
        for ($i = 1; $i <= FraiStructurePeriodique::NUMBER_OF_ITERATIONS; $i++) {
            $fraiStructurePeriodique = new FraiStructurePeriodique($fraisStructure, $i);
            $fraiStructurePeriodique->setValue(0.0);
            $this->periodiqueDao->save($fraiStructurePeriodique);
        }
    }

    public function cloneFraisStructure(Simulation $newSimulation, Simulation $oldSimulation): void
    {
        $objects = $this->fraiStructureDao->findBySimulationID($oldSimulation->getId());
        foreach ($objects as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getFraisStructuresPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setFraisStructures($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
    }

    public function fusionFraisStructure(Simulation $newSimulation, Simulation $oldSimulation1, Simulation $oldSimulation2): void
    {
        $objects1 = $this->fraiStructureDao->findBySimulationID($oldSimulation1->getId());
        $objects2 = $this->fraiStructureDao->findBySimulationID($oldSimulation2->getId());
        foreach ($objects1 as $object) {
            $libelle = $object->getLibelle();
            $type = $object->getType();
            $taux_devolution = $object->getTauxDevolution();
            $indexation = $object->getIndexation();

            $fraiStructure = new FraiStructure($newSimulation, $libelle, $type);
            $fraiStructure->setTauxDevolution($taux_devolution);
            $fraiStructure->setIndexation($indexation);

            $this->save($fraiStructure);
            foreach ($object->getFraisStructuresPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setFraisStructures($fraiStructure);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
        foreach ($objects2 as $object) {
            $libelle = $object->getLibelle();
            $type = $object->getType();
            $taux_devolution = $object->getTauxDevolution();
            $indexation = $object->getIndexation();

            $fraiStructure = new FraiStructure($newSimulation, $libelle, $type);
            $fraiStructure->setTauxDevolution($taux_devolution);
            $fraiStructure->setIndexation($indexation);

            $this->save($fraiStructure);
            foreach ($object->getFraisStructuresPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setFraisStructures($fraiStructure);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
    }
}
