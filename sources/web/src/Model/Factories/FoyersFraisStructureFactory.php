<?php

declare(strict_types=1);

namespace App\Model\Factories;

use App\Dao\FoyerFraiStructureDao;
use App\Dao\FoyerFraiStructurePeriodiqueDao;
use App\Dao\SimulationDao;
use App\Exceptions\HTTPException;
use App\Model\FoyerFraiStructure;
use App\Model\FoyerFraiStructurePeriodique;
use Safe\Exceptions\JsonException;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\TDBM\TDBMException;
use Throwable;
use function floatval;
use function in_array;
use function Safe\json_decode;

class FoyersFraisStructureFactory
{
    /** @var SimulationDao */
    private $simulationDao;
    /** @var FoyerFraiStructurePeriodiqueDao */
    private $periodiqueDao;
    /** @var FoyerFraiStructureDao */
    private $fraiStructureDao;

    public function __construct(
        SimulationDao $simulationDao,
        FoyerFraiStructurePeriodiqueDao $periodiqueDao,
        FoyerFraiStructureDao $fraiStructureDao
    ) {
        $this->simulationDao = $simulationDao;
        $this->periodiqueDao = $periodiqueDao;
        $this->fraiStructureDao = $fraiStructureDao;
    }

    /**
     * @throws JsonException
     * @throws TDBMException
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
    ): FoyerFraiStructure {
        $simulation = $this->simulationDao->getById($simulationId);

        if (empty($simulation)) {
            throw HTTPException::notFound("La simulation n'existe pas");
        }

        if (! in_array($type, FoyerFraiStructure::TYPE_LIST)) {
            throw HTTPException::badRequest('Type invalide');
        }

        if ($id === null) {
            $fraiStructure = new FoyerFraiStructure($simulation, $libelle, $type);
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
    private function createFraiStructurePeriodique(string $periodique, FoyerFraiStructure $fraiStructure, ?string $edit): void
    {
        $periodique = json_decode($periodique);

        foreach ($periodique->periodique as $key => $value) {
            $iteration = $key + 1;

            if ($edit !== null) {
                /** @var FoyerFraiStructurePeriodique $fraiStructurePeriodique */
                $fraiStructurePeriodique = $fraiStructure->getFoyersFraisStructuresPeriodique()->offsetGet($key);
            } else {
                $fraiStructurePeriodique = new FoyerFraiStructurePeriodique($fraiStructure, $iteration);
            }

            $fraiStructurePeriodique->setValue($value ? floatval($value) : 0);
            $this->periodiqueDao->save($fraiStructurePeriodique);
        }
    }
}
