<?php

declare(strict_types=1);

namespace App\Model\Factories;

use App\Dao\SimulationDao;
use App\Dao\VacanceDao;
use App\Dao\VacancePeriodiqueDao;
use App\Exceptions\HTTPException;
use App\Model\Vacance;
use App\Model\VacancePeriodique;
use Safe\Exceptions\JsonException;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\TDBM\TDBMException;
use Throwable;
use function Safe\json_decode;

final class VacanceFactory
{
    /** @var VacancePeriodiqueDao */
    private $periodiqueDao;

    /** @var SimulationDao */
    private $simulationDao;

    /** @var VacanceDao */
    private $vacanceDao;

    public function __construct(
        VacanceDao $vacanceDao,
        SimulationDao $simulationDao,
        VacancePeriodiqueDao $periodiqueDao
    ) {
        $this->periodiqueDao = $periodiqueDao;
        $this->simulationDao = $simulationDao;
        $this->vacanceDao = $vacanceDao;
    }

    /**
     * @throws JsonException
     * @throws TDBMException
     *
     * @Factory()
     */
    public function createVacance(
        ?string $id,
        int $numeroGroupe,
        int $numeroSousGroupe,
        string $nom,
        string $information,
        string $simulationId,
        string $periodique
    ): Vacance {
        $simulation = $this->simulationDao->getById($simulationId);
        if (empty($simulation)) {
            throw HTTPException::notFound("La simulation n'existe pas");
        }

        if ($id === null) {
            if ($this->vacanceDao->findOneByNumeroGroupeAndSimulation($numeroGroupe, $simulation) !== null) {
                throw HTTPException::badRequest('Le numéro renseigné existe déjà');
            }
            $vacance = new Vacance($simulation, $numeroGroupe, $numeroSousGroupe, $nom, $information);
        } else {
            try {
                $vacance = $this->vacanceDao->getById($id);
            } catch (Throwable $e) {
                throw HTTPException::notFound("Cette vacance n'exite pas", $e);
            }
        }

        if ($periodique !== null) {
            $this->createVacancePeriodique($vacance, $periodique, $id);
        }

        return $vacance;
    }

    /**
     * @throws JsonException
     */
    private function createVacancePeriodique(Vacance $vacance, string $periodique, ?string $edit): void
    {
        $periodique = json_decode($periodique);

        foreach ($periodique->periodique as $key => $value) {
            $iteration = $key + 1;

            if ($edit !== null) {
                /** @var VacancePeriodique $vacancePeriodique */
                $vacancePeriodique = $vacance->getVacancePeriodique()->offsetGet($key);
            } else {
                $vacancePeriodique = new VacancePeriodique($vacance, $iteration);
            }
            $vacancePeriodique->setMontant($value ? $value : null);
            $this->periodiqueDao->save($vacancePeriodique);
        }
    }
}
