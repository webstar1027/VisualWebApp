<?php

declare(strict_types=1);

namespace App\Services;

use App\Dao\AccessionFraiStructureDao;
use App\Dao\AccessionFraiStructurePeriodiqueDao;
use App\Exceptions\HTTPException;
use App\Model\AccessionFraiStructure;
use App\Model\AccessionFraiStructurePeriodique;
use App\Model\Factories\AccessionFraisStructureFactory;
use App\Model\Simulation;
use Safe\Exceptions\JsonException;
use TheCodingMachine\TDBM\ResultIterator;
use TheCodingMachine\TDBM\TDBMException;
use Throwable;

class AccessionFraisStructureService
{
    /** @var AccessionFraiStructureDao */
    private $fraiStructureDao;
    /** @var AccessionFraisStructureFactory */
    private $factory;
    /** @var AccessionFraiStructurePeriodiqueDao */
    private $periodiqueDao;

    public function __construct(AccessionFraiStructureDao $fraiStructureDao, AccessionFraisStructureFactory $factory, AccessionFraiStructurePeriodiqueDao $periodiqueDao)
    {
        $this->fraiStructureDao = $fraiStructureDao;
        $this->factory = $factory;
        $this->periodiqueDao = $periodiqueDao;
    }

    public function save(AccessionFraiStructure $fraisStructure): void
    {
        try {
            $this->fraiStructureDao->save($fraisStructure);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Ce frais de structure existe déjà', $e);
        }
    }

    /**
     * @throws TDBMException
     */
    public function remove(string $fraisStructureId): void
    {
        try {
            $fraisStructure = $this->fraiStructureDao->getById($fraisStructureId);
            $this->fraiStructureDao->delete($fraisStructure, true);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Ce frais de structure n\'existe pas', $e);
        }
    }

    /**
     * @return AccessionFraiStructure[]|ResultIterator
     */
    public function findBySimulation(string $simulationId): ResultIterator
    {
        return $this->fraiStructureDao->findBySimulationID($simulationId);
    }

    /**
     * @throws TDBMException
     * @throws JsonException
     */
    public function createDefaultFraiStructures(Simulation $newSimulation): void
    {
        foreach (AccessionFraiStructure::TYPE_LIST as $label => $type) {
            $fraisStructure = $this->factory->createFraiStructure(null, $newSimulation->getId(), $label, 0.0, true, $type, null);
            $this->save($fraisStructure);
            $this->createDefaultPeriodique($fraisStructure);
        }
    }

    private function createDefaultPeriodique(AccessionFraiStructure $fraisStructure): void
    {
        for ($i = 1; $i <= AccessionFraiStructurePeriodique::NUMBER_OF_ITERATIONS; $i++) {
            $fraiStructurePeriodique = new AccessionFraiStructurePeriodique($fraisStructure, $i);
            $fraiStructurePeriodique->setValue(0.0);
            $this->periodiqueDao->save($fraiStructurePeriodique);
        }
    }

    public function cloneFraiStructure(Simulation $newSimulation, Simulation $oldSimulation): void
    {
        $objects = $this->fraiStructureDao->findBySimulationID($oldSimulation->getId());
        foreach ($objects as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getAccessionFraisStructuresPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setFraisStructures($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
    }

    public function fusionAccessionFraisStructure(Simulation $newSimulation, Simulation $oldSimulation1, Simulation $oldSimulation2): void
    {
        $objects1 = $this->fraiStructureDao->findBySimulationID($oldSimulation1->getId());
        $objects2 = $this->fraiStructureDao->findBySimulationID($oldSimulation2->getId());

        foreach ($objects1 as $object) {
            $libelle = $object->getLibelle();
            $type = $object->getType();
            $taux_devolution = $object->getTauxDevolution();
            $indexation = $object->getIndexation();

            $fraiStructure = new AccessionFraiStructure($newSimulation, $libelle, $type);
            $fraiStructure->setTauxDevolution($taux_devolution);
            $fraiStructure->setIndexation($indexation);

            $this->save($fraiStructure);
            foreach ($object->getAccessionFraisStructuresPeriodique() as $periodique) {
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

            $fraiStructure = new AccessionFraiStructure($newSimulation, $libelle, $type);
            $fraiStructure->setTauxDevolution($taux_devolution);
            $fraiStructure->setIndexation($indexation);

            $this->save($fraiStructure);
            foreach ($object->getAccessionFraisStructuresPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setFraisStructures($fraiStructure);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
    }
}
