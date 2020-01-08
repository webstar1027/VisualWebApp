<?php

declare(strict_types=1);

namespace App\Model\Factories;

use App\Dao\AutreChargeDao;
use App\Dao\AutreChargePeriodiqueDao;
use App\Dao\SimulationDao;
use App\Model\AutreCharge;
use App\Model\AutreChargePeriodique;
use Safe\Exceptions\JsonException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\TDBM\TDBMException;
use Throwable;
use function floatval;
use function Safe\json_decode;

class AutreChargeFactory
{
    /** @var SimulationDao */
    private $simulationDao;

    /** @var AutreChargePeriodiqueDao */
    private $periodiqueDao;

    /** @var AutreChargeDao */
    private $autreChargeDao;

    public function __construct(
        SimulationDao $simulationDao,
        AutreChargePeriodiqueDao $periodiqueDao,
        AutreChargeDao $autreChargeDao
    ) {
        $this->simulationDao = $simulationDao;
        $this->periodiqueDao = $periodiqueDao;
        $this->autreChargeDao = $autreChargeDao;
    }

    /**
     * @throws JsonException
     * @throws TDBMException
     *
     * @Factory()
     */
    public function createAutreCharge(
        ?string $id,
        string $simulationId,
        string $libelle,
        ?float $taux_devolution,
        bool $indexation,
        string $nature,
        ?string $periodique
    ): AutreCharge {
        $simulation = $this->simulationDao->getById($simulationId);

        $numero = $this->autreChargeDao->calculateNumero($simulationId);

        if (empty($simulation)) {
            throw new HttpException(Response::HTTP_NOT_FOUND, "La simulation n'existe pas");
        }

        if ($id === null) {
            $autreCharge = new AutreCharge($simulation, $numero, $libelle, $nature);
        } else {
            try {
                $autreCharge = $this->autreChargeDao->getById($id);
            } catch (Throwable $e) {
                throw new HttpException(Response::HTTP_NOT_FOUND, "Ce autre charge n'existe pas", $e);
            }
        }

        $autreCharge->setTauxDevolution($taux_devolution);
        $autreCharge->setIndexation($indexation);

        if ($periodique !== null) {
            $this->createAutreChargePeriodique($periodique, $autreCharge, $id);
        }

        return $autreCharge;
    }

    /**
     * @throws JsonException
     */
    private function createAutreChargePeriodique(string $periodique, AutreCharge $autreCharge, ?string $edit): void
    {
        $periodique = json_decode($periodique);

        foreach ($periodique->periodique as $key => $value) {
            $iteration = $key + 1;

            if ($edit !== null) {
                /** @var AutreChargePeriodique $autreChargePeriodique */
                $autreChargePeriodique = $autreCharge->getAutresChargesPeriodique()->offsetGet($key);
            } else {
                $autreChargePeriodique = new AutreChargePeriodique($autreCharge, $iteration);
            }

            $autreChargePeriodique->setValue($value ? floatval($value) : null);
            $this->periodiqueDao->save($autreChargePeriodique);
        }
    }
}
