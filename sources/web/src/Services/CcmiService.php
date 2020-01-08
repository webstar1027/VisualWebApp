<?php

declare(strict_types=1);

namespace App\Services;

use App\Dao\CcmiDao;
use App\Dao\CcmiPeriodiqueDao;
use App\Model\Ccmi;
use App\Model\Simulation;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use TheCodingMachine\TDBM\ResultIterator;
use Throwable;
use function count;

final class CcmiService
{
    /** @var CcmiDao */
    private $ccmiDao;
    /** @var CcmiPeriodiqueDao */
    private $periodiqueDao;

    public function __construct(CcmiDao $ccmiDao, CcmiPeriodiqueDao $periodiqueDao)
    {
        $this->ccmiDao = $ccmiDao;
        $this->periodiqueDao = $periodiqueDao;
    }

    /**
     * @return ResultIterator|Ccmi[]
     */
    public function fetchBySimulationId(string $simulationId): ResultIterator
    {
        return $this->ccmiDao->findBySimulationId($simulationId);
    }

    public function save(Ccmi $ccmi): void
    {
        try {
            $this->ccmiDao->save($ccmi);
        } catch (Throwable $e) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Ce CCMI existe déjà.', $e);
        }
    }

    public function remove(string $ccmiId): void
    {
        try {
            $ccmi = $this->ccmiDao->getById($ccmiId);
        } catch (Throwable $e) {
            throw new HttpException(Response::HTTP_NOT_FOUND, "Ce CCMI n'existe pas.", $e);
        }

        $this->ccmiDao->delete($ccmi, true);
    }

    public function cloneCcmi(Simulation $newSimulation, Simulation $oldSimulation): void
    {
        $objects = $this->ccmiDao->findBySimulationId($oldSimulation->getId());
        foreach ($objects as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getCcmiPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setCcmi($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
    }

    public function fusionCcmi(Simulation $newSimulation, Simulation $oldSimulation1, Simulation $oldSimulation2): void
    {
        $objects1 = $this->ccmiDao->findBySimulationId($oldSimulation1->getId());
        $objects2 = $this->ccmiDao->findBySimulationId($oldSimulation2->getId());
        foreach ($objects1 as $key => $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $newObject->setNumero($key + 1);
            $this->save($newObject);
            foreach ($object->getCcmiPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setCcmi($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
        foreach ($objects2 as $key => $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $newObject->setNumero(count($objects1) + $key + 1);
            $this->save($newObject);
            foreach ($object->getCcmiPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setCcmi($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
    }
}
