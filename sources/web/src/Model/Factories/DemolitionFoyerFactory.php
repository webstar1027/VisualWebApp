<?php

declare(strict_types=1);

namespace App\Model\Factories;

use App\Dao\DemolitionFoyerDao;
use App\Dao\DemolitionFoyerPeriodiqueDao;
use App\Dao\SimulationDao;
use App\Dao\TypeEmpruntDao;
use App\Dao\TypeEmpruntDemolitionFoyerDao;
use App\Exceptions\HTTPException;
use App\Model\DemolitionFoyer;
use App\Model\DemolitionFoyerPeriodique;
use App\Model\Simulation;
use App\Model\TypeEmpruntDemolitionFoyer;
use DateTimeImmutable;
use Exception;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\TDBM\TDBMException;
use Throwable;
use function floatval;
use function Safe\json_decode;

class DemolitionFoyerFactory
{
    /** @var TypeEmpruntDao */
    private $typeEmpruntDao;

    /** @var SimulationDao */
    private $simulationDao;

    /** @var DemolitionFoyerDao */
    private $demolitionFoyerDao;

    /** @var DemolitionFoyerPeriodiqueDao */
    private $periodiqueDao;

    /** @var TypeEmpruntDemolitionFoyerDao */
    private $typeEmpruntDemolitionFoyerDao;

    public function __construct(
        SimulationDao $simulationDao,
        TypeEmpruntDao $typeEmpruntDao,
        DemolitionFoyerDao $demolitionFoyerDao,
        DemolitionFoyerPeriodiqueDao $periodiqueDao,
        TypeEmpruntDemolitionFoyerDao $typeEmpruntDemolitionFoyerDao
    ) {
        $this->typeEmpruntDao = $typeEmpruntDao;
        $this->simulationDao = $simulationDao;
        $this->demolitionFoyerDao = $demolitionFoyerDao;
        $this->periodiqueDao = $periodiqueDao;
        $this->typeEmpruntDemolitionFoyerDao = $typeEmpruntDemolitionFoyerDao;
    }

    /**
     * @param string[]|null $typeEmprunts
     *
     * @throws Exception
     * @throws TDBMException
     *
     * @Factory()
     */
    public function createDemolitionFoyer(
        ?string $uuid,
        string $simulationId,
        int $numero,
        string $nomIntervention,
        ?int $nombreLogements,
        string $date,
        ?float $remboursementAnticipe,
        ?bool $indexationIcc,
        float $prixRevient,
        float $fondsPropres,
        ?float $subventionsAnru,
        ?float $subventionsEtat,
        ?float $subventionsEpci,
        ?float $subventionsDepartement,
        ?float $subventionsRegion,
        ?float $subventionsCollecteur,
        ?float $autresSubventions,
        ?float $tauxEvolutionTfpb,
        ?float $tauxEvolutionMaintenance,
        ?float $tauxEvolutionGrosEntretien,
        float $totalEmprutns,
        ?array $typeEmprunts,
        ?string $periodique
    ): DemolitionFoyer {
        $simulation = $this->simulationDao->getById($simulationId);

        $this->validateRequest($simulation);

        if ($uuid === null) {
            $demolitionObj = $this->demolitionFoyerDao->findOneBySimulationAndNumero($simulation, $numero);

            if ($demolitionObj !== null) {
                throw HTTPException::badRequest('Ce numéro est déjà utilisé');
            }

            $demolitionFoyer = new DemolitionFoyer(
                $simulation,
                $numero,
                $nomIntervention,
                new DateTimeImmutable($date),
                $prixRevient,
                $fondsPropres,
                $totalEmprutns
            );
        } else {
            try {
                $demolitionFoyer = $this->demolitionFoyerDao->getById($uuid);
                if (empty($demolitionFoyer)) {
                    throw HTTPException::notFound("Cette démolition n'existe pas");
                }
                $demolitionFoyer->setNomIntervention($nomIntervention);
                $demolitionFoyer->setDate(new DateTimeImmutable($date));
                $demolitionFoyer->setPrixRevient($prixRevient);
                $demolitionFoyer->setFondsPropres($fondsPropres);
                $demolitionFoyer->setTotalEmprutns($totalEmprutns);
            } catch (Throwable $e) {
                throw HTTPException::notFound("Cette démolition n'existe pas", $e);
            }
        }

        $demolitionFoyer->setNombreLogements($nombreLogements);
        $demolitionFoyer->setRemboursementAnticipe($remboursementAnticipe);
        $demolitionFoyer->setIndexationIcc($indexationIcc);
        $demolitionFoyer->setSubventionsEtat($subventionsEtat);
        $demolitionFoyer->setSubventionsAnru($subventionsAnru);
        $demolitionFoyer->setSubventionsEpci($subventionsEpci);
        $demolitionFoyer->setSubventionsDepartement($subventionsDepartement);
        $demolitionFoyer->setSubventionsRegion($subventionsRegion);
        $demolitionFoyer->setSubventionsCollecteur($subventionsCollecteur);
        $demolitionFoyer->setAutresSubventions($autresSubventions);
        $demolitionFoyer->setTauxEvolutionTfpb($tauxEvolutionTfpb);
        $demolitionFoyer->setTauxEvolutionMaintenance($tauxEvolutionMaintenance);
        $demolitionFoyer->setTauxEvolutionGrosEntretien($tauxEvolutionGrosEntretien);

        if (! empty($typeEmprunts)) {
            foreach ($typeEmprunts as $typeEmprunt) {
                $typeEmprunt = json_decode($typeEmprunt);
                $typeEmpruntDemolitionFoyer = $this->typeEmpruntDemolitionFoyerDao->findByTypeEmpruntAndDemolitionFoyer($typeEmprunt->id, $demolitionFoyer->getId());
                if (! empty($typeEmpruntDemolitionFoyer)) {
                    continue;
                }

                $existingTypeEmprunt = $this->typeEmpruntDao->getById($typeEmprunt->id);
                $typeEmpruntDemolitionFoyer = new TypeEmpruntDemolitionFoyer($existingTypeEmprunt, $demolitionFoyer);
                if (! empty($typeEmprunt->datePremiere)) {
                    $dateTimePremier = new DateTimeImmutable($typeEmprunt->datePremiere);
                    $typeEmpruntDemolitionFoyer->setDatePremiere($dateTimePremier);
                }
                if (! empty($typeEmprunt->montant)) {
                    $typeEmpruntDemolitionFoyer->setMontant(floatval($typeEmprunt->montant));
                }
                $this->typeEmpruntDemolitionFoyerDao->save($typeEmpruntDemolitionFoyer);
            }
        }

        if ($periodique !== null) {
            $periodique = json_decode($periodique);
            if (isset($periodique->part_capital)
                && isset($periodique->part_interets)
                && isset($periodique->redevances)
                && isset($periodique->tfpb)
                && isset($periodique->maintenance_courante)
                && isset($periodique->gros_entretien)) {
                foreach ($periodique->part_capital as $key => $value) {
                    $demolitionFoyerPeriodique = $this->fetchDemolitionFoyerPeriodique($demolitionFoyer, $key, $uuid);
                    $demolitionFoyerPeriodique->setPartCapital($value ? floatval($value) : null);

                    $demolitionFoyerPeriodique->setPartInterets(
                        empty($periodique->part_interets[$key]) ? null : floatval($periodique->part_interets[$key])
                    );

                    $demolitionFoyerPeriodique->setRedevances(
                        empty($periodique->redevances[$key]) ? null : floatval($periodique->redevances[$key])
                    );

                    $demolitionFoyerPeriodique->setTfpb(
                        empty($periodique->tfpb[$key]) ? null : floatval($periodique->tfpb[$key])
                    );

                    $demolitionFoyerPeriodique->setMaintenanceCourante(
                        empty($periodique->maintenance_courante[$key]) ? null : floatval($periodique->maintenance_courante[$key])
                    );

                    $demolitionFoyerPeriodique->setGrosEntretien(
                        empty($periodique->gros_entretien[$key]) ? null : floatval($periodique->gros_entretien[$key])
                    );

                    $this->periodiqueDao->save($demolitionFoyerPeriodique);
                }
            }
        }

        return $demolitionFoyer;
    }

    protected function fetchDemolitionFoyerPeriodique(DemolitionFoyer $demolitionFoyer, int $key, ?string $uuid): DemolitionFoyerPeriodique
    {
        $iteration = $key + 1;
        if ($uuid !== null) {
            $demolitionFoyerPeriodique = $demolitionFoyer->getDemolitionFoyersPeriodique()->offsetGet($key);
        } else {
            $demolitionFoyerPeriodique = new DemolitionFoyerPeriodique($demolitionFoyer, $iteration);
        }

        return $demolitionFoyerPeriodique;
    }

    protected function validateRequest(Simulation $simulation): void
    {
        if (empty($simulation)) {
            throw HTTPException::notFound("La simulation n'existe pas");
        }
    }
}
