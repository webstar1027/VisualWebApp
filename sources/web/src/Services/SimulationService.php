<?php

declare(strict_types=1);

namespace App\Services;

use App\Dao\SimulationDao;
use App\Model\Simulation;
use Cake\Chronos\Chronos;

class SimulationService
{
    /** @var SimulationDao */
    private $simulationDao;

    public function __construct(SimulationDao $simulationDao)
    {
        $this->simulationDao = $simulationDao;
    }

    public function updateDateModification(Simulation $simulation): void
    {
        $simulation->setDateModification(Chronos::now());
        $this->simulationDao->save($simulation);
    }
}
