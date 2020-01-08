<?php

declare(strict_types=1);

namespace App\Model\Factories;

use App\Dao\LoyerDao;
use App\Dao\LoyerPeriodiqueDao;
use App\Dao\SimulationDao;
use App\Model\Loyer;
use App\Model\LoyerPeriodique;
use Safe\Exceptions\JsonException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\TDBM\TDBMException;
use Throwable;
use function floatval;
use function in_array;
use function Safe\json_decode;

class LoyerFactory
{
    /** @var SimulationDao */
    private $simulationDao;

    /** @var LoyerDao */
    private $loyerDao;

    /** @var LoyerPeriodiqueDao */
    private $periodiqueDao;

    public function __construct(SimulationDao $simulationDao, LoyerDao $loyerDao, LoyerPeriodiqueDao $periodiqueDao)
    {
        $this->simulationDao = $simulationDao;
        $this->loyerDao = $loyerDao;
        $this->periodiqueDao = $periodiqueDao;
    }

    /**
     * @throws JsonException
     * @throws TDBMException
     *
     * @Factory()
     */
    public function constructLoyer(
        ?string $uuid,
        string $simulationId,
        ?string $nom,
        ?float $taux_devolution,
        ?float $nombre_logements,
        ?float $montant_rls,
        int $type,
        ?string $periodique
    ): Loyer {
        $simulation = $this->simulationDao->getById($simulationId);

        if (empty($simulation)) {
            throw new HttpException(Response::HTTP_NOT_FOUND, "La simulation n'existe pas");
        }

        if (! in_array($type, Loyer::TYPE_LIST)) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Type invalide');
        }

        if ($uuid === null) {
            $loyer = new Loyer($simulation, $type);
        } else {
            try {
                $loyer = $this->loyerDao->getById($uuid);
            } catch (Throwable $e) {
                throw new HttpException(Response::HTTP_NOT_FOUND, "Ce loyer n'existe pas", $e);
            }
        }

        $loyer->setNom($nom);
        $loyer->setTauxDevolution($taux_devolution);
        $nombre_logements === null ? $loyer->setNombreLogements(null) : $loyer->setNombreLogements($nombre_logements);
        $montant_rls === null ? $loyer->setMontantRls(null) : $loyer->setMontantRls($montant_rls);

        if ($periodique !== null && $type !== Loyer::TYPE_LOGEMENTS) {
            $this->createLoyerPeriodique($periodique, $loyer, $uuid);
        }

        return $loyer;
    }

    /**
     * @throws JsonException
     */
    private function createLoyerPeriodique(string $periodique, Loyer $loyer, ?string $edit): void
    {
        $periodique = json_decode($periodique);

        foreach ($periodique->periodique as $key => $value) {
            $iteration = $key + 1;

            if ($edit !== null) {
                /** @var LoyerPeriodique $loyerPeriodique */
                $loyerPeriodique = $loyer->getLoyerPeriodique()->offsetGet($key);
            } else {
                $loyerPeriodique = new LoyerPeriodique($loyer, $iteration);
            }

            $loyerPeriodique->setValue($value ? floatval($value) : null);
            $this->periodiqueDao->save($loyerPeriodique);
        }
    }
}
