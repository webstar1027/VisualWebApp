<?php

declare(strict_types=1);

namespace App\Model\Factories;

use App\Dao\AnnuiteDao;
use App\Dao\AnnuitePeriodiqueDao;
use App\Dao\SimulationDao;
use App\Exceptions\HTTPException;
use App\Model\Annuite;
use App\Model\AnnuitePeriodique;
use Exception;
use Safe\Exceptions\JsonException;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\TDBM\TDBMException;
use Throwable;
use function bin2hex;
use function floatval;
use function in_array;
use function random_bytes;
use function Safe\json_decode;

class AnnuiteFactory
{
    /** @var SimulationDao */
    private $simulationDao;

    /** @var AnnuiteDao */
    private $annuiteDao;

    /** @var AnnuitePeriodiqueDao */
    private $periodiqueDao;

    public function __construct(
        SimulationDao $simulationDao,
        AnnuiteDao $annuiteDao,
        AnnuitePeriodiqueDao $periodiqueDao
    ) {
        $this->simulationDao = $simulationDao;
        $this->annuiteDao = $annuiteDao;
        $this->periodiqueDao = $periodiqueDao;
    }

    /**
     * @throws Exception
     * @throws JsonException
     * @throws TDBMException
     *
     * @Factory()
     */
    public function createAnnuite(
        ?string $uuid,
        ?string $numero,
        string $simulationId,
        ?float $capital_restant_patrimoine,
        ?bool $prise_icne_acne,
        ?string $libelle,
        ?int $nature,
        int $type,
        ?string $periodique
    ): Annuite {
        $simulation = $this->simulationDao->getById($simulationId);

        if (empty($simulation)) {
            throw HTTPException::notFound("La simulation n'existe pas.");
        }

        if (! in_array($type, Annuite::TYPE_LIST)) {
            throw HTTPException::badRequest('Type invalide');
        }

        if ($numero === null && $type === Annuite::TYPE_ANNUITE_EMPRUNTS) {
            throw HTTPException::badRequest('Numéro invalide');
        }

        if ($numero === null) {
            $bytes = random_bytes(10);
            $numero = bin2hex($bytes);
        }

        if ($uuid === null) {
            if ($type === Annuite::TYPE_ANNUITE_EMPRUNTS &&
                $this->annuiteDao->findOneByNumeroAndSimulation((string) $numero, $simulation) !== null) {
                throw HTTPException::badRequest('Le numéro renseigné existe déjà');
            }
            $annuite = new Annuite($simulation, (string) $numero, $type);
        } else {
            try {
                $annuite = $this->annuiteDao->getById($uuid);
                $annuite->setNumero((string) $numero);
            } catch (Throwable $e) {
                throw HTTPException::notFound("Cette annuité n'existe pas.", $e);
            }
        }

        $capital_restant_patrimoine === null ? $annuite->setCapitalRestantPatrimoine(null) : $annuite->setCapitalRestantPatrimoine($capital_restant_patrimoine);
        $annuite->setPriseIcneAcne($prise_icne_acne);
        $annuite->setNature($nature);
        $annuite->setLibelle($libelle);

        if ($periodique !== null) {
            $periodique = json_decode($periodique);
            if (isset($periodique->periodique)) {
                foreach ($periodique->periodique as $key => $value) {
                    $annuitePeriodique = $this->fetchAnnuitePeriodique($annuite, $key, $uuid);
                    $annuitePeriodique->setValue($value ? floatval($value) : null);

                    $this->periodiqueDao->save($annuitePeriodique);
                }
            }
        }

        return $annuite;
    }

    protected function fetchAnnuitePeriodique(Annuite $annuite, int $key, ?string $uuid): AnnuitePeriodique
    {
        $iteration = $key + 1;
        if ($uuid !== null) {
            /** @var AnnuitePeriodique $autreChargePeriodique */
            $annuitePeriodique = $annuite->getAnnuitePeriodique()->offsetGet($key);
        } else {
            $annuitePeriodique = new AnnuitePeriodique($annuite, $iteration);
        }

        return $annuitePeriodique;
    }
}
