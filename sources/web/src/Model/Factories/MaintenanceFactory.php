<?php

declare(strict_types=1);

namespace App\Model\Factories;

use App\Dao\MaintenanceDao;
use App\Dao\MaintenancePeriodiqueDao;
use App\Dao\SimulationDao;
use App\Exceptions\HTTPException;
use App\Model\Maintenance;
use App\Model\MaintenancePeriodique;
use App\Model\Simulation;
use Safe\Exceptions\JsonException;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\TDBM\TDBMException;
use Throwable;
use function floatval;
use function in_array;
use function Safe\json_decode;

class MaintenanceFactory
{
    /** @var SimulationDao */
    private $simulationDao;

    /** @var MaintenancePeriodiqueDao */
    private $periodiqueDao;

    /** @var MaintenanceDao */
    private $maintenanceDao;

    public function __construct(
        SimulationDao $simulationDao,
        MaintenancePeriodiqueDao $periodiqueDao,
        MaintenanceDao $maintenanceDao
    ) {
        $this->simulationDao = $simulationDao;
        $this->periodiqueDao = $periodiqueDao;
        $this->maintenanceDao = $maintenanceDao;
    }

    /**
     * @throws JsonException
     * @throws TDBMException
     * @throws HTTPException
     *
     * @Factory()
     */
    public function createMaintenance(
        ?string $uuid,
        string $simulationId,
        string $nom,
        bool $regie,
        ?float $taux_devolution,
        bool $indexation,
        ?int $nature,
        int $type,
        ?string $periodique
    ): Maintenance {
        $simulation = $this->simulationDao->getById($simulationId);

        $this->validateRequest($simulation, $type);

        if ($uuid === null) {
            $numero = $this->maintenanceDao->calculateNumero($simulationId);
            $maintenance = new Maintenance($simulation, $numero, $nom);
        } else {
            try {
                $maintenance = $this->maintenanceDao->getById($uuid);
                $maintenance->setNom($nom);
            } catch (Throwable $e) {
                throw HTTPException::badRequest("Cette maintenance n'exite pas", $e);
            }
        }

        $maintenance->setNature($nature);
        $maintenance->setRegie($regie);
        $maintenance->setTauxDevolution($taux_devolution);
        $maintenance->setIndexation($indexation);
        $maintenance->setType($type);

        if ($periodique !== null) {
            $this->createMaintenancePeriodique($periodique, $maintenance, $uuid);
        }

        return $maintenance;
    }

    /**
     * @throws JsonException
     */
    private function createMaintenancePeriodique(string $periodique, Maintenance $maintenance, ?string $edit): void
    {
        $periodique = json_decode($periodique);

        foreach ($periodique->periodique as $key => $value) {
            $iteration = $key + 1;

            if ($edit !== null) {
                /** @var MaintenancePeriodique $maintenancePeriodique */
                $maintenancePeriodique = $maintenance->getMaintenancePeriodique()->offsetGet($key);
            } else {
                $maintenancePeriodique = new MaintenancePeriodique($maintenance, $iteration);
            }

            $maintenancePeriodique->setValue($value ? floatval($value) : null);
            $this->periodiqueDao->save($maintenancePeriodique);
        }
    }

    /**
     * Validate the request
     */
    protected function validateRequest(Simulation $simulation, int $type): void
    {
        if (empty($simulation)) {
            throw HTTPException::notFound('La simulation n\'existe pas');
        }

        if (! in_array($type, Maintenance::TYPE_LIST)) {
            throw HTTPException::badRequest('Type invalide');
        }
    }
}
