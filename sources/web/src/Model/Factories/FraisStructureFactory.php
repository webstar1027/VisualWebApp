<?php

declare(strict_types=1);

namespace App\Model\Factories;

use App\Dao\FraiStructureDao;
use App\Dao\FraiStructurePeriodiqueDao;
use App\Dao\SimulationDao;
use App\Exceptions\HTTPException;
use App\Model\FraiStructure;
use App\Model\FraiStructurePeriodique;
use Safe\Exceptions\JsonException;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\TDBM\TDBMException;
use Throwable;
use function floatval;
use function in_array;
use function Safe\json_decode;

class FraisStructureFactory
{
    /** @var SimulationDao */
    private $simulationDao;
    /** @var FraiStructurePeriodiqueDao */
    private $periodiqueDao;
    /** @var FraiStructureDao */
    private $fraiStructureDao;

    public function __construct(
        SimulationDao $simulationDao,
        FraiStructurePeriodiqueDao $periodiqueDao,
        FraiStructureDao $fraiStructureDao
    ) {
        $this->simulationDao = $simulationDao;
        $this->periodiqueDao = $periodiqueDao;
        $this->fraiStructureDao = $fraiStructureDao;
    }

    /**
     * @throws HTTPException
     * @throws TDBMException
     * @throws JsonException
     *
     * @Factory()
     */
    public function createFraiStructure(
        ?string $id,
        string $simulationId,
        string $libelle,
        ?float $taux_devolution,
        bool $indexation,
        int $type,
        ?string $periodique
    ): FraiStructure {
        $simulation = $this->simulationDao->getById($simulationId);

        if (empty($simulation)) {
            throw HTTPException::notFound("La simulation n'existe pas");
        }

        if (! in_array($type, FraiStructure::TYPE_LIST)) {
            throw HTTPException::badRequest('Type invalide');
        }

        if ($id === null) {
            $fraiStructure = new FraiStructure($simulation, $libelle, $type);
        } else {
            try {
                $fraiStructure = $this->fraiStructureDao->getById($id);
            } catch (Throwable $e) {
                throw HTTPException::notFound("Ce frais de structure n'existe pas", $e);
            }
        }

        $fraiStructure->setTauxDevolution($taux_devolution);
        $fraiStructure->setIndexation($indexation);

        if ($periodique !== null) {
            $this->createFraiStructurePeriodique($periodique, $fraiStructure, $id);
        }

        return $fraiStructure;
    }

    /**
     * @throws JsonException
     */
    private function createFraiStructurePeriodique(string $periodique, FraiStructure $fraiStructure, ?string $edit): void
    {
        $periodique = json_decode($periodique);

        foreach ($periodique->periodique as $key => $value) {
            $iteration = $key + 1;

            if ($edit !== null) {
                /** @var FraiStructurePeriodique $fraiStructurePeriodique */
                $fraiStructurePeriodique = $fraiStructure->getFraisStructuresPeriodique()->offsetGet($key);
            } else {
                $fraiStructurePeriodique = new FraiStructurePeriodique($fraiStructure, $iteration);
            }

            $fraiStructurePeriodique->setValue($value ? floatval($value) : null);
            $this->periodiqueDao->save($fraiStructurePeriodique);
        }
    }
}
