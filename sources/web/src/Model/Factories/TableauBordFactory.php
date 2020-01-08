<?php

declare(strict_types=1);

namespace App\Model\Factories;

use App\Dao\SimulationDao;
use App\Dao\TableauDeBordDao;
use App\Dao\TableauDeBordParametreDao;
use App\Dao\TableauDeBordPeriodiqueDao;
use App\Exceptions\HTTPException;
use App\Model\Simulation;
use App\Model\TableauDeBord;
use Safe\Exceptions\JsonException;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\TDBM\TDBMException;
use Throwable;
use function Safe\json_decode;

class TableauBordFactory
{
    /** @var SimulationDao */
    private $simulationDao;
    /** @var TableauDeBordDao */
    private $tableauDeBordDao;
    /** @var TableauDeBordPeriodiqueDao */
    private $periodiqueDao;
    /** @var TableauDeBordParametreDao */
    private $parametreDao;

    public function __construct(
        SimulationDao $simulationDao,
        TableauDeBordDao $tableauDeBordDao,
        TableauDeBordPeriodiqueDao $periodiqueDao,
        TableauDeBordParametreDao $parametreDao
    ) {
        $this->simulationDao = $simulationDao;
        $this->tableauDeBordDao = $tableauDeBordDao;
        $this->periodiqueDao = $periodiqueDao;
        $this->parametreDao = $parametreDao;
    }

    /**
     * @throws TDBMException
     * @throws JsonException
     * @throws HTTPException
     *
     * @Factory()
     */
    public function createTableauDeBord(
        ?string $uuid,
        string $simulationId,
        int $beginProjection,
        int $endProjection,
        string $composant,
        ?int $position,
        ?string $periodique
    ): TableauDeBord {
        $simulation = $this->simulationDao->getById($simulationId);

        $this->validateRequest($simulation);

        if ($uuid === null) {
            $tableau = new TableauDeBord($simulation, $beginProjection, $endProjection);
        } else {
            try {
                $tableau = $this->tableauDeBordDao->getById($uuid);
                $tableau->setBeginProjection($beginProjection);
                $tableau->setEndProjection($endProjection);
            } catch (Throwable $e) {
                throw HTTPException::badRequest("Ce Tableau De Bord n'existe pas", $e);
            }
        }

        if ($composant !== '') {
            $param = $this->parametreDao->findByComposantAndTableau($composant, $tableau);
            if ($param === null) {
                throw HTTPException::badRequest("Ce Composant n'existe pas");
            }

            if ($position !== null) {
                $param->setPosition($position);
                $this->parametreDao->save($param);
            }

            if ($periodique !== null) {
                $periodique = json_decode($periodique);
                if (isset($periodique->periodique)) {
                    foreach ($periodique->periodique as $iteration => $value) {
                        $tableauDeBordPeriodique = $this->periodiqueDao->findByTableauParamIteration($tableau->getUuid(), $param->getId(), $iteration);
                        if ($tableauDeBordPeriodique === null) {
                            continue;
                        }
                        $tableauDeBordPeriodique->setValue($value ? $value : null);
                        $this->periodiqueDao->save($tableauDeBordPeriodique);
                    }
                }
            }
        }

        return $tableau;
    }

    /**
     * @throws HTTPException
     */
    protected function validateRequest(Simulation $simulation): void
    {
        if (empty($simulation)) {
            throw HTTPException::notFound("La simulation n'existe pas");
        }
    }
}
