<?php

declare(strict_types=1);

namespace App\Model\Factories;

use App\Dao\SimulationDao;
use App\Dao\VefaDao;
use App\Dao\VefaPeriodiqueDao;
use App\Exceptions\HTTPException;
use App\Model\Vefa;
use App\Model\VefaPeriodique;
use Safe\Exceptions\JsonException;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\TDBM\TDBMException;
use Throwable;
use function Safe\json_decode;

final class VefaFactory
{
    /** @var VefaDao */
    private $vefaDao;
    /** @var SimulationDao */
    private $simulationDao;
    /** @var VefaPeriodiqueDao */
    private $vefaPeriodiqueDao;

    public function __construct(
        VefaDao $vefaDao,
        SimulationDao $simulationDao,
        VefaPeriodiqueDao $vefaPeriodiqueDao
    ) {
        $this->vefaDao = $vefaDao;
        $this->simulationDao = $simulationDao;
        $this->vefaPeriodiqueDao = $vefaPeriodiqueDao;
    }

    /**
     * @throws HTTPException
     * @throws TDBMException
     *
     * @Factory()
     */
    public function createVefa(
        ?string $uuid,
        string $simulationId,
        int $numero,
        ?string $nomOperation,
        ?string $nomCategorie,
        ?string $directSci,
        ?float $pourcentageDetention,
        ?int $nombreLogement,
        float $prixVente,
        ?float $tauxEvolution,
        ?float $tauxMargeBrute,
        ?int $dureePeriodeConstruction,
        string $type,
        string $periodiques
    ): Vefa {
        $simulation = $this->simulationDao->getById($simulationId);

        if (empty($simulation)) {
            throw HTTPException::notFound("La simulation n'existe pas");
        }

        if ($uuid !== null) {
            // Updating the existing one
            try {
                $vefa = $this->vefaDao->getById($uuid);
                $vefa->setSimulation($simulation);
                $vefa->setNumero($numero);
                if (empty($vefa)) {
                    throw HTTPException::notFound("Cette VEFA n'existe pas");
                }
            } catch (Throwable $e) {
                throw HTTPException::notFound("Cette VEFA n'existe pas", $e);
            }
        } else {
            if ($this->vefaDao->findOneByNumero($numero) !== null) {
                throw HTTPException::badRequest('Ce numéro est déjà utilisé par une VEFA');
            }

            // Creating a new object
            $vefa = new Vefa(
                $simulation,
                $numero,
                $type
            );
        }
        $vefa->setDirectSci($directSci);
        $vefa->setPourcentageDetention($pourcentageDetention);
        $vefa->setPrixVente($prixVente);
        $vefa->setTauxMargeBrute($tauxMargeBrute);

        if ($type === Vefa::TYPE_IDENTIFIEE) {
            $vefa->setNombreLogement($nombreLogement);
            $vefa->setNomOperation($nomOperation);
        }
        if ($type === Vefa::TYPE_NON_IDENTIFIEE) {
            $vefa->setTauxEvolution($tauxEvolution);
            $vefa->setDureePeriodeConstruction($dureePeriodeConstruction);
            $vefa->setNomCategorie($nomCategorie);
        }
        $this->handleVefaPeriodique($periodiques, $vefa, $uuid);

        return $vefa;
    }

    /**
     * @throws JsonException
     */
    private function handleVefaPeriodique(string $periodique, Vefa $vefa, ?string $edit): void
    {
        $periodique = json_decode($periodique);

        foreach ($periodique->nombreLogementsPeriodique as $index => $value) {
            $iteration = $index + 1;
            /** @var Vefa $vefaPeriodique */
            if ($edit !== null) {
                $vefaPeriodique = $vefa->getVefaPeriodique()->offsetGet($index);
            } else {
                $vefaPeriodique = new VefaPeriodique($vefa, $iteration);
            }
            $vefaPeriodique->setNombreLogements($value ? (float) $value : null);
            $vefaPeriodique->setPortageFp($periodique->portageFondsPropresPeriodique[$index] ? (float) $periodique->portageFondsPropresPeriodique[$index] : null);
            $vefaPeriodique->setCoutsInternes($periodique->coutsInternesPeriodique[$index] ? (float) $periodique->coutsInternesPeriodique[$index] : null);
            $this->vefaPeriodiqueDao->save($vefaPeriodique);
        }
    }
}
