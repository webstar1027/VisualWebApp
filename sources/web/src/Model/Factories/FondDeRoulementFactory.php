<?php

declare(strict_types=1);

namespace App\Model\Factories;

use App\Dao\FondDeRoulementDao;
use App\Dao\FondDeRoulementPeriodiqueDao;
use App\Dao\SimulationDao;
use App\Dao\TypeEmpruntDao;
use App\Model\FondDeRoulement;
use App\Model\FondDeRoulementPeriodique;
use DateTimeImmutable;
use Safe\Exceptions\JsonException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\TDBM\TDBMException;
use Throwable;
use function floatval;
use function Safe\json_decode;

class FondDeRoulementFactory
{
    /** @var SimulationDao */
    private $simulationDao;

    /** @var FondDeRoulementPeriodiqueDao */
    private $fondDeRoulementPeriodiqueDao;

    /** @var FondDeRoulementDao */
    private $fondDeRoulementDao;

    /** @var TypeEmpruntDao */
    private $typeEmpruntDao;

    public function __construct(
        SimulationDao $simulationDao,
        FondDeRoulementDao $fondDeRoulementDao,
        FondDeRoulementPeriodiqueDao $fondDeRoulementPeriodiqueDao,
        TypeEmpruntDao $typeEmpruntDao
    ) {
        $this->simulationDao =$simulationDao;
        $this->fondDeRoulementPeriodiqueDao = $fondDeRoulementPeriodiqueDao;
        $this->fondDeRoulementDao = $fondDeRoulementDao;
        $this->typeEmpruntDao = $typeEmpruntDao;
    }

    /**
     * @throws JsonException
     * @throws TDBMException
     *
     * @Factory()
     */
    public function createFondDeRoulement(
        ?string $uuid,
        string $simulationId,
        string $nom,
        string $type,
        ?string $typeEmpruntId,
        ?float $tauxEvolution,
        ?string $dateEcheance,
        ?string $periodique
    ): FondDeRoulement {
        $simulation = $this->simulationDao->getById($simulationId);
        if (! empty($typeEmpruntId)) {
            $typeEmprunt = $this->typeEmpruntDao->getById($typeEmpruntId);
        } else {
            $typeEmprunt= null;
        }

        if ($uuid === null) {
            $fondDeRoulement = new FondDeRoulement($simulation, $nom, $type);
            $fondDeRoulement->setDeletable(true);
        } else {
            try {
                $fondDeRoulement = $this->fondDeRoulementDao->getById($uuid);
            } catch (Throwable $e) {
                throw new HttpException(Response::HTTP_NOT_FOUND, "Ce fond de roulement n'existe pas", $e);
            }
        }
            $fondDeRoulement->setTauxEvolution($tauxEvolution);
            $fondDeRoulement->setTypeEmprunt($typeEmprunt);
        if (! empty($dateEcheance)) {
            $fondDeRoulement->setDateEcheance(new DateTimeImmutable($dateEcheance));
        }
        if ($periodique !== null) {
            $this->createFondDeRoulementPeriodique($periodique, $fondDeRoulement, $uuid);
        }

            return $fondDeRoulement;
    }

    /**
     * @throws JsonException
     */
    private function createFondDeRoulementPeriodique(string $periodique, FondDeRoulement $fondDeRoulement, ?string $edit): void
    {
        $periodique = json_decode($periodique);
        foreach ($periodique->periodique as $key => $value) {
            $iteration = $key + 1;
            if ($edit !== null) {
                $fondDeRoulementPeriodique = $fondDeRoulement->getFondDeRoulementPeriodique()->offsetGet($key);
            } else {
                $fondDeRoulementPeriodique = new FondDeRoulementPeriodique($fondDeRoulement, $iteration);
            }

                $fondDeRoulementPeriodique->setValeur($value? floatval($value): null);
                $this->fondDeRoulementPeriodiqueDao->save($fondDeRoulementPeriodique);
        }
    }
}
