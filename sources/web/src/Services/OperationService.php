<?php

declare(strict_types=1);

namespace App\Services;

use App\Dao\OperationDao;
use App\Dao\OperationPeriodiqueDao;
use App\Dao\TypeEmpruntOperationDao;
use App\Exceptions\HTTPException;
use App\Model\Operation;
use App\Model\Simulation;
use TheCodingMachine\TDBM\ResultIterator;
use TheCodingMachine\TDBM\TDBMException;
use Throwable;
use function count;

class OperationService
{
    /** @var OperationDao */
    private $operationDao;
    /** @var TypeEmpruntOperationDao */
    private $typeEmpruntOperationDao;
    /** @var OperationPeriodiqueDao */
    private $periodiqueDao;

    public function __construct(
        OperationDao $operationDao,
        TypeEmpruntOperationDao $typeEmpruntOperationDao,
        OperationPeriodiqueDao $periodiqueDao
    ) {
        $this->operationDao = $operationDao;
        $this->typeEmpruntOperationDao = $typeEmpruntOperationDao;
        $this->periodiqueDao = $periodiqueDao;
    }

    public function save(Operation $operation): void
    {
        try {
            $this->operationDao->save($operation);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Cette opération existe déjà.', $e);
        }
    }

    /**
     * @throws TDBMException
     * @throws HTTPException
     */
    public function remove(string $operationUUID): void
    {
        try {
            $operation = $this->operationDao->getById($operationUUID);
            $this->operationDao->delete($operation, true);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Cette opération n\'existe pas.', $e);
        }
    }

    public function removeTypeDempruntOperation(string $typesEmpruntsUUID, string $operationUUID): void
    {
        $typeEmpruntOperation = $this->typeEmpruntOperationDao->findByTypeEmpruntAndOperation(
            $typesEmpruntsUUID,
            $operationUUID
        );

        if (empty($typeEmpruntOperation)) {
            return;
        }

        $this->typeEmpruntOperationDao->delete($typeEmpruntOperation);
    }

    /**
     * @return Operation[]|ResultIterator
     */
    public function findBySimulation(string $simulationId): ResultIterator
    {
        return $this->operationDao->findBySimulationID($simulationId);
    }

    public function cloneOperation(Simulation $newSimulation, Simulation $oldSimulation): void
    {
        $objects = $this->operationDao->findBySimulation($oldSimulation);
        foreach ($objects as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getOperationPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setOperation($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
            foreach ($object->getTypeEmpruntOperation() as $typeEmpruntObject) {
                $newTypeEmprunt = clone $typeEmpruntObject;
                $newTypeEmprunt->setOperation($newObject);
                $this->typeEmpruntOperationDao->save($newTypeEmprunt);
            }
        }
    }

    public function fusionOperation(Simulation $newSimulation, Simulation $oldSimulation1, Simulation $oldSimulation2): void
    {
        $identifees1 = $this->operationDao->findBySimulationIDAndType($oldSimulation1->getId(), 0);
        $nonidentifees1 = $this->operationDao->findBySimulationIDAndType($oldSimulation1->getId(), 1);

        $identifees2 = $this->operationDao->findBySimulationIDAndType($oldSimulation2->getId(), 0);
        $nonidentifees2 = $this->operationDao->findBySimulationIDAndType($oldSimulation2->getId(), 1);

        foreach ($identifees1 as $key => $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $newObject->setNOperation($key + 1);
            $this->save($newObject);
            foreach ($object->getOperationPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setOperation($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
            foreach ($object->getTypeEmpruntOperation() as $typeEmpruntObject) {
                $newTypeEmprunt = clone $typeEmpruntObject;
                $newTypeEmprunt->setOperation($newObject);
                $this->typeEmpruntOperationDao->save($newTypeEmprunt);
            }
        }
        foreach ($nonidentifees1 as $key => $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $newObject->setNOperation($key + 1);
            $this->save($newObject);
            foreach ($object->getOperationPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setOperation($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
            foreach ($object->getTypeEmpruntOperation() as $typeEmpruntObject) {
                $newTypeEmprunt = clone $typeEmpruntObject;
                $newTypeEmprunt->setOperation($newObject);
                $this->typeEmpruntOperationDao->save($newTypeEmprunt);
            }
        }

        foreach ($identifees2 as $key => $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $newObject->setNOperation(count($identifees1) + $key + 1);
            $this->save($newObject);
            foreach ($object->getOperationPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setOperation($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
            foreach ($object->getTypeEmpruntOperation() as $typeEmpruntObject) {
                $newTypeEmprunt = clone $typeEmpruntObject;
                $newTypeEmprunt->setOperation($newObject);
                $this->typeEmpruntOperationDao->save($newTypeEmprunt);
            }
        }
        foreach ($nonidentifees2 as $key => $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $newObject->setNOperation(count($nonidentifees1) + $key + 1);
            $this->save($newObject);
            foreach ($object->getOperationPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setOperation($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
            foreach ($object->getTypeEmpruntOperation() as $typeEmpruntObject) {
                $newTypeEmprunt = clone $typeEmpruntObject;
                $newTypeEmprunt->setOperation($newObject);
                $this->typeEmpruntOperationDao->save($newTypeEmprunt);
            }
        }
    }
}
