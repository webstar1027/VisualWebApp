<?php

declare(strict_types=1);

namespace App\Event;

use App\Model\Simulation;
use Symfony\Component\EventDispatcher\Event;

class SimulationClonedEvent extends Event
{
    public const NAME = 'simulation.cloned';

    /** @var Simulation $simulation */
    protected $simulation;
    /** @var Simulation */
    private $oldsimulation;

    public function __construct(Simulation $simulation, Simulation $oldsimulation)
    {
        $this->simulation = $simulation;
        $this->oldsimulation = $oldsimulation;
    }

    public function getSimulation(): Simulation
    {
        return $this->simulation;
    }

    public function getOldsimulation(): Simulation
    {
        return $this->oldsimulation;
    }
}
