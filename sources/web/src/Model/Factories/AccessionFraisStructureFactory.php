<?php

declare(strict_types=1);

namespace App\Model\Factories;

use App\Dao\AccessionFraiStructureDao;
use App\Dao\AccessionFraiStructurePeriodiqueDao;
use App\Dao\SimulationDao;
use App\Model\AccessionFraiStructure;
use App\Model\AccessionFraiStructurePeriodique;
use Safe\Exceptions\JsonException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\TDBM\TDBMException;
use Throwable;
use function floatval;
use function in_array;
use function Safe\json_decode;

class AccessionFraisStructureFactory
{
    /** @var SimulationDao */
    private $simulationDao;
    /** @var AccessionFraiStructurePeriodiqueDao */
    private $periodiqueDao;
    /** @var AccessionFraiStructureDao */
    private $fraiStructureDao;

    public function __construct(
        SimulationDao $simulationDao,
        AccessionFraiStructurePeriodiqueDao $periodiqueDao,
        AccessionFraiStructureDao $fraiStructureDao
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
    ): AccessionFraiStructure {
        $simulation = $this->simulationDao->getById($simulationId);

        if (empty($simulation)) {
            throw new HttpException(Response::HTTP_NOT_FOUND, "La simulation n'existe pas");
        }

        if (! in_array($type, AccessionFraiStructure::TYPE_LIST)) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Type invalide');
        }

        if ($id === null) {
            $fraiStructure = new AccessionFraiStructure($simulation, $libelle, $type);
        } else {
            try {
                $fraiStructure = $this->fraiStructureDao->getById($id);
            } catch (Throwable $e) {
                throw new HttpException(Response::HTTP_NOT_FOUND, "Ce frais structure n'existe pas", $e);
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
    private function createFraiStructurePeriodique(string $periodique, AccessionFraiStructure $fraiStructure, ?string $edit): void
    {
        $periodique = json_decode($periodique);

        foreach ($periodique->periodique as $key => $value) {
            $iteration = $key + 1;

            if ($edit !== null) {
                /** @var AccessionFraiStructurePeriodique $fraiStructurePeriodique */
                $fraiStructurePeriodique = $fraiStructure->getAccessionFraisStructuresPeriodique()->offsetGet($key);
            } else {
                $fraiStructurePeriodique = new AccessionFraiStructurePeriodique($fraiStructure, $iteration);
            }

            $fraiStructurePeriodique->setValue($value ? floatval($value) : 0);
            $this->periodiqueDao->save($fraiStructurePeriodique);
        }
    }
}
