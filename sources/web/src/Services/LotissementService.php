<?php

declare(strict_types=1);

namespace App\Services;

use App\Dao\LotissementDao;
use App\Dao\LotissementPeriodiqueDao;
use App\Model\Lotissement;
use App\Model\Simulation;
use TheCodingMachine\TDBM\ResultIterator;
use TheCodingMachine\TDBM\TDBMException;
use function count;

class LotissementService
{
    /** @var LotissementDao */
    private $lotissementDao;
    /** @var LotissementPeriodiqueDao */
    private $periodiqueDao;

    public function __construct(
        LotissementDao $lotissementDao,
        LotissementPeriodiqueDao $periodiqueDao
    ) {
        $this->lotissementDao = $lotissementDao;
        $this->periodiqueDao = $periodiqueDao;
    }

    public function save(Lotissement $lotissement): void
    {
        $this->lotissementDao->save($lotissement);
    }

    /**
     * @throws TDBMException
     */
    public function remove(string $lotissementUUID): void
    {
        $lotissement = $this->lotissementDao->getById($lotissementUUID);
        $this->lotissementDao->delete($lotissement, true);
    }

    /**
     * @return Lotissement[]|ResultIterator
     */
    public function findBySimulationAndType(string $simulationId, int $type): ResultIterator
    {
        return $this->lotissementDao->findBySimulationIDAndType($simulationId, $type);
    }

    public function cloneLotissement(Simulation $newSimulation, Simulation $oldSimulation): void
    {
        $objects = $this->lotissementDao->findBySimulation($oldSimulation);
        foreach ($objects as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getLotissementPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setLotissement($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
    }

    public function fusionLotissement(Simulation $newSimulation, Simulation $oldSimulation1, Simulation $oldSimulation2): void
    {
        $identifees1 = $this->lotissementDao->findBySimulationIDAndType($oldSimulation1->getId(), 0);
        $nonidentifees1 = $this->lotissementDao->findBySimulationIDAndType($oldSimulation1->getId(), 1);

        $identifees2 = $this->lotissementDao->findBySimulationIDAndType($oldSimulation2->getId(), 0);
        $nonidentifees2 = $this->lotissementDao->findBySimulationIDAndType($oldSimulation2->getId(), 1);

        foreach ($identifees1 as $key => $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $newObject->setNumero($key + 1);
            $this->save($newObject);
            foreach ($object->getLotissementPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setLotissement($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
        foreach ($nonidentifees1 as $key => $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $newObject->setNumero($key + 1);
            $this->save($newObject);
            foreach ($object->getLotissementPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setLotissement($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }

        foreach ($identifees2 as $key => $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $newObject->setNumero(count($identifees1) + $key + 1);
            $this->save($newObject);
            foreach ($object->getLotissementPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setLotissement($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
        foreach ($nonidentifees2 as $key => $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $newObject->setNumero(count($nonidentifees1) + $key + 1);
            $this->save($newObject);
            foreach ($object->getLotissementPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setLotissement($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
    }

    /**
     * @return Lotissement[]|ResultIterator
     */
    public function findBySimulation(string $simulationId): ResultIterator
    {
        return $this->lotissementDao->findBySimulationID($simulationId);
    }
}
