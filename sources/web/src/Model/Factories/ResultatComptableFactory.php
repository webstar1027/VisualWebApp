<?php

declare(strict_types=1);

namespace App\Model\Factories;

use App\Dao\ResultatComptableDao;
use App\Dao\ResultatComptablePeriodiqueDao;
use App\Dao\SimulationDao;
use App\Exceptions\HTTPException;
use App\Model\ResultatComptable;
use App\Model\ResultatComptablePeriodique;
use Safe\Exceptions\JsonException;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\TDBM\TDBMException;
use Throwable;
use function floatval;
use function Safe\json_decode;

class ResultatComptableFactory
{
    /** @var SimulationDao */
    private $simulationDao;

    /** @var ResultatComptableDao */
    private $resultatComptableDao;

    /** @var ResultatComptablePeriodiqueDao */
    private $periodiqueDao;

    public function __construct(
        SimulationDao $simulationDao,
        ResultatComptableDao $resultatComptableDao,
        ResultatComptablePeriodiqueDao $periodiqueDao
    ) {
        $this->simulationDao = $simulationDao;
        $this->resultatComptableDao = $resultatComptableDao;
        $this->periodiqueDao = $periodiqueDao;
    }

    /**
     * @throws TDBMException
     * @throws JsonException
     *
     * @Factory()
     */
    public function createResultatComptable(
        ?string $uuid,
        string $simulationId,
        string $libelle,
        ?string $periodique
    ): ResultatComptable {
        $simulation = $this->simulationDao->getById($simulationId);

        if (empty($simulation)) {
            throw HTTPException::notFound('La simulation n\'existe pas');
        }

        if ($uuid === null) {
            if ($this->resultatComptableDao->findOneBySimulationIDAndLibelle($simulationId, $libelle) !== null) {
                throw HTTPException::badRequest('Libellé déjà existant');
            }
            $resultatComptable = new ResultatComptable($simulation, $libelle);
        } else {
            try {
                $resultatComptable = $this->resultatComptableDao->getById($uuid);
                $resultatComptable->setLibelle($libelle);
            } catch (Throwable $e) {
                throw HTTPException::notFound("Ce Resultat Comptable n'existe pas", $e);
            }
        }

        if ($periodique !== null) {
            $this->createResultatComptablePeriodique($periodique, $resultatComptable, $uuid);
        }

        return $resultatComptable;
    }

    /**
     * @throws JsonException
     */
    private function createResultatComptablePeriodique(
        string $periodique,
        ResultatComptable $resultatComptable,
        ?string $edit
    ): void {
        $periodique = json_decode($periodique);

        foreach ($periodique->periodique as $key => $value) {
            $iteration = $key + 1;

            if ($edit !== null) {
                /** @var ResultatComptablePeriodique $resultatComptablePeriodique */
                $resultatComptablePeriodique = $resultatComptable->getResultatComptablePeriodique()->offsetGet($key);
            } else {
                $resultatComptablePeriodique = new ResultatComptablePeriodique($resultatComptable, $iteration);
            }

            $resultatComptablePeriodique->setValue($value ? floatval($value) : null);
            $this->periodiqueDao->save($resultatComptablePeriodique);
        }
    }
}
