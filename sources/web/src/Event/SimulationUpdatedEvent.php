<?php

declare(strict_types=1);

namespace App\Event;

use App\Model\Simulation;
use Symfony\Component\EventDispatcher\Event;

class SimulationUpdatedEvent extends Event
{
    public const NAME = 'simulation.updated';

    /** @var Simulation $simulation */
    protected $simulation;

    public function __construct(Simulation $simulation)
    {
        $this->simulation = $simulation;
    }

    public function getSimulation(): Simulation
    {
        return $this->simulation;
    }
}
