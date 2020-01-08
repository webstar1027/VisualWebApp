<?php

declare(strict_types=1);

namespace App\Event;

use App\Model\Simulation;
use Symfony\Component\EventDispatcher\Event;

class SimulationFusionedEvent extends Event
{
    public const NAME = 'simulation.fusioned';

    /** @var Simulation $simulation */
    protected $simulation;
    /** @var Simulation */
    private $oldsimulation1;
    /** @var Simulation */
    private $oldsimulation2;

    public function __construct(Simulation $simulation, Simulation $oldsimulation1, Simulation $oldsimulation2)
    {
        $this->simulation = $simulation;
        $this->oldsimulation1 = $oldsimulation1;
        $this->oldsimulation2 = $oldsimulation2;
    }

    public function getSimulation(): Simulation
    {
        return $this->simulation;
    }

    public function getOldsimulation1(): Simulation
    {
        return $this->oldsimulation1;
    }

    public function getOldsimulation2(): Simulation
    {
        return $this->oldsimulation2;
    }
}
