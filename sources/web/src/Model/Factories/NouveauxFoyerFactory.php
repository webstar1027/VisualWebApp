<?php

declare(strict_types=1);

namespace App\Model\Factories;

use App\Dao\ModeleDamortissementDao;
use App\Dao\NouveauxFoyerDao;
use App\Dao\NouveauxFoyerPeriodiqueDao;
use App\Dao\SimulationDao;
use App\Dao\TypeEmpruntDao;
use App\Dao\TypeEmpruntNouveauxFoyerDao;
use App\Exceptions\HTTPException;
use App\Model\NouveauxFoyer;
use App\Model\NouveauxFoyerPeriodique;
use App\Model\Simulation;
use App\Model\TypeEmpruntNouveauxFoyer;
use DateTimeImmutable;
use Exception;
use Safe\Exceptions\JsonException;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\TDBM\TDBMException;
use Throwable;
use function floatval;
use function Safe\json_decode;

class NouveauxFoyerFactory
{
    /** @var SimulationDao */
    private $simulationDao;
    /** @var TypeEmpruntDao */
    private $typeEmpruntDao;
    /** @var NouveauxFoyerDao */
    private $nouveauxFoyerDao;
    /** @var NouveauxFoyerPeriodiqueDao */
    private $periodiqueDao;
    /** @var TypeEmpruntNouveauxFoyerDao */
    private $typeEmpruntNouveauxFoyerDao;
    /** @var ModeleDamortissementDao */
    private $modeleDamortissementDao;

    public function __construct(
        SimulationDao $simulationDao,
        TypeEmpruntDao $typeEmpruntDao,
        NouveauxFoyerDao $nouveauxFoyerDao,
        NouveauxFoyerPeriodiqueDao $periodiqueDao,
        TypeEmpruntNouveauxFoyerDao $typeEmpruntNouveauxFoyerDao,
        ModeleDamortissementDao $modeleDamortissementDao
    ) {
        $this->simulationDao = $simulationDao;
        $this->typeEmpruntDao = $typeEmpruntDao;
        $this->nouveauxFoyerDao = $nouveauxFoyerDao;
        $this->periodiqueDao = $periodiqueDao;
        $this->typeEmpruntNouveauxFoyerDao = $typeEmpruntNouveauxFoyerDao;
        $this->modeleDamortissementDao = $modeleDamortissementDao;
    }

    /**
     * @param string[] $typeEmprunts
     *
     * @throws Exception
     * @throws JsonException
     * @throws TDBMException
     *
     * @Factory()
     */
    public function createNouveauxFoyer(
        ?string $uuid,
        string $simulationId,
        int $numero,
        string $nomIntervention,
        string $nature,
        int $nombreLogements,
        ?string $anneeAgrement,
        ?string $dateAcquisition,
        ?string $dateTravaux,
        bool $indexation_icc,
        float $prixRevient,
        float $fondsPropres,
        ?float $subventionsEtat,
        ?float $subventionsAnru,
        ?float $subventionsEpci,
        ?float $subventionsDepartement,
        ?float $subventionsRegion,
        ?float $subventionsCollecteur,
        ?float $autresSubventions,
        ?float $redevancesTauxEvolution,
        ?float $tfpbTauxEvolution,
        ?float $maintenanceTauxEvolution,
        ?float $grosTauxEvolution,
        ?float $coutFoncier,
        ?float $totalEmprunt,
        ?string $modeleDamortissementUUID,
        ?array $typeEmprunts,
        ?string $periodique
    ): NouveauxFoyer {
        $simulation = $this->simulationDao->getById($simulationId);

        $this->validateRequest($simulation);

        if (empty($uuid)) {
            $nouveauxFoyerObj = $this->nouveauxFoyerDao->findOneByNumeroAndSimulation($numero, $simulation);
            if ($nouveauxFoyerObj !== null) {
                throw HTTPException::badRequest('Ce numéro est déjà utilisé');
            }
            $nouveauxFoyer = new NouveauxFoyer(
                $simulation,
                $numero,
                $nomIntervention,
                $nature,
                $nombreLogements,
                $prixRevient,
                $fondsPropres
            );
        } else {
            try {
                $nouveauxFoyer = $this->nouveauxFoyerDao->getById($uuid);
                if (empty($nouveauxFoyer)) {
                    throw HTTPException::notFound("Ce nouveau foyer n'existe pas");
                }
                $nouveauxFoyer->setNumero($numero);
                $nouveauxFoyer->setNomIntervention($nomIntervention);
                $nouveauxFoyer->setNombreLogements($nombreLogements);
                $nouveauxFoyer->setNature($nature);
                $nouveauxFoyer->setPrixRevient($prixRevient);
                $nouveauxFoyer->setFondsPropres($fondsPropres);
            } catch (Throwable $e) {
                throw HTTPException::notFound("Ce nouveau foyer n'existe pas", $e);
            }
        }

        if ($modeleDamortissementUUID !== null) {
            $modeleDamortissement = $this->modeleDamortissementDao->getById($modeleDamortissementUUID);
            $nouveauxFoyer->setModeleDamortissement($modeleDamortissement);
        }

        if ($anneeAgrement) {
            $nouveauxFoyer->setAnneeAgrement(new DateTimeImmutable($anneeAgrement));
        }

        if ($dateAcquisition) {
            $nouveauxFoyer->setDateAcquisition(new DateTimeImmutable($dateAcquisition));
        }

        if ($dateTravaux) {
            $nouveauxFoyer->setDateTravaux(new DateTimeImmutable($dateTravaux));
        }

        $nouveauxFoyer->setIndexationIcc($indexation_icc);

        $nouveauxFoyer->setRedevancesTauxEvolution($redevancesTauxEvolution);
        $nouveauxFoyer->setTfpbTauxEvolution($tfpbTauxEvolution);
        $nouveauxFoyer->setMaintenanceTauxEvolution($maintenanceTauxEvolution);
        $nouveauxFoyer->setGrosTauxEvolution($grosTauxEvolution);
        $nouveauxFoyer->setSubventionsEtat($subventionsEtat);
        $nouveauxFoyer->setSubventionsAnru($subventionsAnru);
        $nouveauxFoyer->setSubventionsEpci($subventionsEpci);
        $nouveauxFoyer->setSubventionsDepartement($subventionsDepartement);
        $nouveauxFoyer->setSubventionsRegion($subventionsRegion);
        $nouveauxFoyer->setSubventionsCollecteur($subventionsCollecteur);
        $nouveauxFoyer->setAutresSubventions($autresSubventions);
        $nouveauxFoyer->setTotalEmprunt($totalEmprunt);
        $nouveauxFoyer->setCoutFoncier($coutFoncier);

        if (! empty($typeEmprunts)) {
            foreach ($typeEmprunts as $typeEmprunt) {
                $typeEmprunt = json_decode($typeEmprunt);
                $nouveauxFoyerTypeEmprunt = $this->typeEmpruntNouveauxFoyerDao->findByTypeEmpruntAndNouveauxFoyer(
                    $typeEmprunt->id,
                    $nouveauxFoyer->getId()
                );
                if (! empty($nouveauxFoyerTypeEmprunt)) {
                    continue;
                }

                $existingTypeEmprunt = $this->typeEmpruntDao->getById($typeEmprunt->id);
                $nouveauxFoyerTypeEmprunt = new TypeEmpruntNouveauxFoyer($existingTypeEmprunt, $nouveauxFoyer);
                if (! empty($typeEmprunt->datePremiere)) {
                    $dateTimePremier = new DateTimeImmutable($typeEmprunt->datePremiere);
                    $nouveauxFoyerTypeEmprunt->setDatePremiere($dateTimePremier);
                }
                if (! empty($typeEmprunt->montant)) {
                    $nouveauxFoyerTypeEmprunt->setMontant(floatval($typeEmprunt->montant));
                }
                $this->typeEmpruntNouveauxFoyerDao->save($nouveauxFoyerTypeEmprunt);
            }
        }

        if ($periodique !== null) {
            $periodique = json_decode($periodique);

            if (isset($periodique->complements_capital)
                && isset($periodique->complements_interet)
                && isset($periodique->redevances)
                && isset($periodique->tfpb)
                && isset($periodique->maintenance_courante)
                && isset($periodique->gros_entretien)
            ) {
                foreach ($periodique->complements_capital as $key => $value) {
                    $nouveauxFoyerPeriodique = $this->fetchNouveauxFoyerPeriodique($nouveauxFoyer, $key, $uuid);
                    $nouveauxFoyerPeriodique->setComplementsCapital($value ? floatval($value) : null);

                    $nouveauxFoyerPeriodique->setComplementsInteret(
                        empty($periodique->complements_interet[$key]) ? null : floatval(
                            $periodique->complements_interet[$key]
                        )
                    );

                    $nouveauxFoyerPeriodique->setRedevances(
                        empty($periodique->redevances[$key]) ? null : floatval(
                            $periodique->redevances[$key]
                        )
                    );

                    $nouveauxFoyerPeriodique->setTfpb(
                        empty($periodique->tfpb[$key]) ? null : floatval(
                            $periodique->tfpb[$key]
                        )
                    );

                    $nouveauxFoyerPeriodique->setMaintenanceCourante(
                        empty($periodique->maintenance_courante[$key]) ? null : floatval(
                            $periodique->maintenance_courante[$key]
                        )
                    );

                    $nouveauxFoyerPeriodique->setGrosEntretien(
                        empty($periodique->gros_entretien[$key]) ? null : floatval(
                            $periodique->gros_entretien[$key]
                        )
                    );

                    $this->periodiqueDao->save($nouveauxFoyerPeriodique);
                }
            }
        }

        return $nouveauxFoyer;
    }

    protected function fetchNouveauxFoyerPeriodique(
        NouveauxFoyer $nouveauxFoyer,
        int $key,
        ?string $uuid
    ): NouveauxFoyerPeriodique {
        $iteration = $key + 1;
        if ($uuid !== null) {
            $nouveauxFoyerPeriodique = $nouveauxFoyer->getNouveauxFoyerPeriodique()->offsetGet($key);
        } else {
            $nouveauxFoyerPeriodique = new NouveauxFoyerPeriodique($nouveauxFoyer, $iteration);
        }

        return $nouveauxFoyerPeriodique;
    }

    protected function validateRequest(Simulation $simulation): void
    {
        if (empty($simulation)) {
            throw HTTPException::notFound("La simulation n'existe pas");
        }
    }
}
