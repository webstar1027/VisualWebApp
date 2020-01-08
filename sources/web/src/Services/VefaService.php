<?php

declare(strict_types=1);

namespace App\Services;

use App\Dao\VefaDao;
use App\Dao\VefaPeriodiqueDao;
use App\Exceptions\HTTPException;
use App\Model\Simulation;
use App\Model\Vefa;
use TheCodingMachine\TDBM\ResultIterator;
use Throwable;
use function count;
use function strval;

final class VefaService
{
    /** @var VefaDao */
    private $vefaDao;
    /** @var VefaPeriodiqueDao */
    private $periodiqueDao;

    public function __construct(VefaDao $vefaDao, VefaPeriodiqueDao $periodiqueDao)
    {
        $this->vefaDao = $vefaDao;
        $this->periodiqueDao = $periodiqueDao;
    }

    /**
     * @return ResultIterator|Vefa[]
     */
    public function fetchBySimulationIdAndType(string $simulationId, string $type): ResultIterator
    {
        return $this->vefaDao->findBySimulationIdAndType($simulationId, $type);
    }

    public function save(Vefa $vefa): void
    {
        try {
            $this->vefaDao->save($vefa);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Ce VEFA existe déjà', $e);
        }
    }

    public function remove(string $vefaId): void
    {
        try {
            $vefa = $this->vefaDao->getById($vefaId);
        } catch (Throwable $e) {
            throw HTTPException::notFound("Ce VEFA n'existe pas", $e);
        }

        $this->vefaDao->delete($vefa, true);
    }

    public function cloneVefa(Simulation $newSimulation, Simulation $oldSimulation): void
    {
        $objects = $this->vefaDao->findBySimulation($oldSimulation);
        foreach ($objects as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getVefaPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setVefa($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
    }

    public function fusionVefa(Simulation $newSimulation, Simulation $oldSimulation1, Simulation $oldSimulation2): void
    {
        $identifees1 = $this->vefaDao->findBySimulationIdAndType($oldSimulation1->getId(), strval(0));
        $nonidentifees1 = $this->vefaDao->findBySimulationIdAndType($oldSimulation1->getId(), strval(1));

        $identifees2 = $this->vefaDao->findBySimulationIdAndType($oldSimulation2->getId(), strval(0));
        $nonidentifees2 = $this->vefaDao->findBySimulationIdAndType($oldSimulation2->getId(), strval(1));

        foreach ($identifees1 as $key => $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $newObject->setNumero($key + 1);
            $this->save($newObject);

            foreach ($object->getVefaPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setVefa($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
        foreach ($nonidentifees1 as $key => $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $newObject->setNumero($key + 1);
            $this->save($newObject);

            foreach ($object->getVefaPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setVefa($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }

        foreach ($identifees2 as $key => $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $newObject->setNumero(count($identifees1) + $key + 1);
            $this->save($newObject);

            foreach ($object->getVefaPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setVefa($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
        foreach ($nonidentifees2 as $key => $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $newObject->setNumero(count($nonidentifees1) + $key + 1);
            $this->save($newObject);

            foreach ($object->getVefaPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setVefa($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
    }
}
