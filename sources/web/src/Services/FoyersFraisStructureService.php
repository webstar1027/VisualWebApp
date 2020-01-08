<?php

declare(strict_types=1);

namespace App\Services;

use App\Dao\FoyerFraiStructureDao;
use App\Dao\FoyerFraiStructurePeriodiqueDao;
use App\Exceptions\HTTPException;
use App\Model\Factories\FoyersFraisStructureFactory;
use App\Model\FoyerFraiStructure;
use App\Model\FoyerFraiStructurePeriodique;
use App\Model\Simulation;
use Safe\Exceptions\JsonException;
use TheCodingMachine\TDBM\ResultIterator;
use TheCodingMachine\TDBM\TDBMException;
use Throwable;

class FoyersFraisStructureService
{
    /** @var FoyerFraiStructureDao */
    private $fraiStructureDao;
    /** @var FoyersFraisStructureFactory */
    private $factory;
    /** @var FoyerFraiStructurePeriodiqueDao */
    private $periodiqueDao;

    public function __construct(FoyerFraiStructureDao $fraiStructureDao, FoyersFraisStructureFactory $factory, FoyerFraiStructurePeriodiqueDao $periodiqueDao)
    {
        $this->fraiStructureDao = $fraiStructureDao;
        $this->factory = $factory;
        $this->periodiqueDao = $periodiqueDao;
    }

    public function save(FoyerFraiStructure $fraisStructure): void
    {
        try {
            $this->fraiStructureDao->save($fraisStructure);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Ce frais de structure existe déjà.', $e);
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
            throw HTTPException::notFound("Ce frais de structure n\'existe pas.", $e);
        }
    }

    /**
     * @return FoyerFraiStructure[]|ResultIterator
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
        foreach (FoyerFraiStructure::TYPE_LIST as $label => $type) {
            $fraisStructure = $this->factory->createFraiStructure(null, $newSimulation->getId(), $label, 0.0, true, $type, null);
            $this->save($fraisStructure);
            $this->createDefaultPeriodique($fraisStructure);
        }
    }

    private function createDefaultPeriodique(FoyerFraiStructure $fraisStructure): void
    {
        for ($i = 1; $i <= FoyerFraiStructurePeriodique::NUMBER_OF_ITERATIONS; $i++) {
            $fraiStructurePeriodique = new FoyerFraiStructurePeriodique($fraisStructure, $i);
            $fraiStructurePeriodique->setValue(0.0);
            $this->periodiqueDao->save($fraiStructurePeriodique);
        }
    }

    public function cloneFoyersFraisStructure(Simulation $newSimulation, Simulation $oldSimulation): void
    {
        $objects = $this->fraiStructureDao->findBySimulationID($oldSimulation->getId());
        foreach ($objects as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getFoyersFraisStructuresPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setFraisStructures($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
    }

    public function fusionFoyersFraisStructure(Simulation $newSimulation, Simulation $oldSimulation1, Simulation $oldSimulation2): void
    {
        $objects1 = $this->fraiStructureDao->findBySimulationID($oldSimulation1->getId());
        $objects2 = $this->fraiStructureDao->findBySimulationID($oldSimulation2->getId());
        foreach ($objects1 as $object) {
            $libelle = $object->getLibelle();
            $type = $object->getType();
            $taux_devolution = $object->getTauxDevolution();
            $indexation = $object->getIndexation();

            $fraiStructure = new FoyerFraiStructure($newSimulation, $libelle, $type);
            $fraiStructure->setTauxDevolution($taux_devolution);
            $fraiStructure->setIndexation($indexation);

            $this->save($fraiStructure);

            foreach ($object->getFoyersFraisStructuresPeriodique() as $periodique) {
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

            $fraiStructure = new FoyerFraiStructure($newSimulation, $libelle, $type);
            $fraiStructure->setTauxDevolution($taux_devolution);
            $fraiStructure->setIndexation($indexation);

            $this->save($fraiStructure);

            foreach ($object->getFoyersFraisStructuresPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setFraisStructures($fraiStructure);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
    }
}
