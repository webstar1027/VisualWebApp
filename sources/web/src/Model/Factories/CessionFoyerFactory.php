<?php

declare(strict_types=1);

namespace App\Model\Factories;

use App\Dao\CessionFoyerDao;
use App\Dao\CessionFoyerPeriodiqueDao;
use App\Dao\SimulationDao;
use App\Exceptions\HTTPException;
use App\Model\CessionFoyer;
use App\Model\CessionFoyerPeriodique;
use App\Model\Simulation;
use DateTimeImmutable;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\TDBM\TDBMException;
use Throwable;
use function floatval;
use function Safe\json_decode;

class CessionFoyerFactory
{
    /** @var SimulationDao */
    private $simulationDao;
    /** @var CessionFoyerDao */
    private $cessionFoyerDao;
    /** @var CessionFoyerPeriodiqueDao */
    private $periodiqueDao;

    public function __construct(
        SimulationDao $simulationDao,
        CessionFoyerDao $cessionFoyerDao,
        CessionFoyerPeriodiqueDao $periodiqueDao
    ) {
        $this->simulationDao = $simulationDao;
        $this->cessionFoyerDao = $cessionFoyerDao;
        $this->periodiqueDao = $periodiqueDao;
    }

    /**
     * @throws TDBMException
     * @throws HTTPException
     *
     * @Factory()
     */
    public function createCessionFoyer(
        ?string $uuid,
        string $simulationId,
        int $nGroupe,
        string $nomIntervention,
        ?string $nature,
        ?bool $indexerInflation,
        ?int $nombreLogements,
        string $dateCession,
        float $prixNetCession,
        float $valeurNetteComptable,
        ?float $remboursementAnticipe,
        ?float $tauxEvolutionTfpb,
        ?float $tauxEvolutionMaintenance,
        ?float $tauxEvolutionGrosEntretien,
        ?float $reductionAmortissementAnnuelle,
        ?float $reductionRepriseAnnuelle,
        ?float $dureeResiduelle,
        ?string $periodique
    ): CessionFoyer {
        $simulation = $this->simulationDao->getById($simulationId);

        $this->validateRequest($simulation, $nGroupe, $nomIntervention);

        if ($uuid === null) {
            $cessionFoyerObj = $this->cessionFoyerDao->findOneBySimulationAndNGroupe($simulation, $nGroupe);
            if ($cessionFoyerObj !== null) {
                throw HTTPException::badRequest('Ce numéro de groupe est déjà utilisé');
            }
            $cessionFoyer = new CessionFoyer(
                $simulation,
                $nGroupe,
                $nomIntervention,
                new DateTimeImmutable($dateCession),
                $prixNetCession,
                $valeurNetteComptable
            );
        } else {
            try {
                $cessionFoyer = $this->cessionFoyerDao->getById($uuid);
                if (empty($cessionFoyer)) {
                    throw HTTPException::notFound("Cette cession n'existe pas");
                }
                $cessionFoyer->setNomIntervention($nomIntervention);
                $cessionFoyer->setDateCession(new DateTimeImmutable($dateCession));
                $cessionFoyer->setPrixNetCession($prixNetCession);
                $cessionFoyer->setValeurNetteComptable($valeurNetteComptable);
            } catch (Throwable $e) {
                throw HTTPException::notFound("Cette cession n'existe pas", $e);
            }
        }

        $cessionFoyer->setNature($nature);
        $cessionFoyer->setIndexerInflation($indexerInflation);
        $cessionFoyer->setNombreLogements($nombreLogements);
        $cessionFoyer->setTauxEvolutionTfpb($tauxEvolutionTfpb);
        $cessionFoyer->setTauxEvolutionMaintenance($tauxEvolutionMaintenance);
        $cessionFoyer->setTauxEvolutionGrosEntretien($tauxEvolutionGrosEntretien);
        $cessionFoyer->setReductionAmortissementAnnuelle($reductionAmortissementAnnuelle);
        $cessionFoyer->setReductionRepriseAnnuelle($reductionRepriseAnnuelle);
        $cessionFoyer->setRemboursementAnticipe($remboursementAnticipe);
        $cessionFoyer->setDureeResiduelle($dureeResiduelle);

        if ($periodique !== null) {
            $periodique = json_decode($periodique);
            if (isset($periodique->redevance) &&
                isset($periodique->part_capital) &&
                isset($periodique->part_interets) &&
                isset($periodique->tfpb) &&
                isset($periodique->maintenance_courante) &&
                isset($periodique->gros_entretien)
            ) {
                foreach ($periodique->redevance as $key => $value) {
                    $cessionPeriodique = $this->fetchCessionFoyerPeriodique($cessionFoyer, $key, $uuid);

                    $cessionPeriodique->setRedevance($value ? floatval($value) : null);

                    $cessionPeriodique->setPartCapital(
                        empty($periodique->part_capital[$key]) ? null : floatval($periodique->part_capital[$key])
                    );

                    $cessionPeriodique->setPartInterets(
                        empty($periodique->part_interets[$key]) ? null : floatval($periodique->part_interets[$key])
                    );

                    $cessionPeriodique->setTfpb(
                        empty($periodique->tfpb[$key]) ? null : floatval($periodique->tfpb[$key])
                    );

                    $cessionPeriodique->setMaintenanceCourante(
                        empty($periodique->maintenance_courante[$key]) ? null : floatval($periodique->maintenance_courante[$key])
                    );

                    $cessionPeriodique->setGrosEntretien(
                        empty($periodique->gros_entretien[$key]) ? null : floatval($periodique->gros_entretien[$key])
                    );

                    $this->periodiqueDao->save($cessionPeriodique);
                }
            }
        }

        return $cessionFoyer;
    }

    /**
     * Fetch or create CessionFoyer Periodique
     */
    protected function fetchCessionFoyerPeriodique(CessionFoyer $cessionFoyer, int $key, ?string $uuid): CessionFoyerPeriodique
    {
        $iteration = $key + 1;
        if ($uuid !== null) {
            $cessionPeriodique = $cessionFoyer->getCessionsFoyersPeriodique()->offsetGet($key);
        } else {
            $cessionPeriodique = new CessionFoyerPeriodique($cessionFoyer, $iteration);
        }

        return $cessionPeriodique;
    }

    /**
     * Validate the request
     */
    protected function validateRequest(Simulation $simulation, int $nGroupe, string $nomIntervention): void
    {
        if (empty($simulation)) {
            throw HTTPException::notFound("La simulation n'existe pas");
        }
    }
}
