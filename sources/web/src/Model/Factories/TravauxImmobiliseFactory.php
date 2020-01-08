<?php

declare(strict_types=1);

namespace App\Model\Factories;

use App\Dao\ModeleDamortissementDao;
use App\Dao\ProfilEvolutionLoyerDao;
use App\Dao\SimulationDao;
use App\Dao\TravauxImmobiliseDao;
use App\Dao\TravauxImmobilisePeriodiqueDao;
use App\Dao\TypeEmpruntDao;
use App\Dao\TypeEmpruntTravauxImmobiliseDao;
use App\Exceptions\HTTPException;
use App\Model\Simulation;
use App\Model\TravauxImmobilise;
use App\Model\TravauxImmobilisePeriodique;
use App\Model\TypeEmpruntTravauxImmobilise;
use DateTimeImmutable;
use Safe\Exceptions\JsonException;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\TDBM\TDBMException;
use Throwable;
use function floatval;
use function in_array;
use function Safe\json_decode;

class TravauxImmobiliseFactory
{
    /** @var TypeEmpruntDao */
    private $typeEmpruntDao;
    /** @var SimulationDao */
    private $simulationDao;
    /** @var TravauxImmobiliseDao */
    private $travauxImmobiliseDao;
    /** @var TravauxImmobilisePeriodiqueDao */
    private $periodiqueDao;
    /** @var TypeEmpruntTravauxImmobiliseDao */
    private $typeEmpruntTravauxImmobiliseDao;
    /** @var ProfilEvolutionLoyerDao */
    private $profilEvolutionLoyerDao;
    /** @var ModeleDamortissementDao */
    private $modeleDamortissementDao;

    public function __construct(
        SimulationDao $simulationDao,
        TypeEmpruntDao $typeEmpruntDao,
        TravauxImmobiliseDao $travauxImmobiliseDao,
        TravauxImmobilisePeriodiqueDao $periodiqueDao,
        TypeEmpruntTravauxImmobiliseDao $typeEmpruntTravauxImmobiliseDao,
        ProfilEvolutionLoyerDao $profilEvolutionLoyerDao,
        ModeleDamortissementDao $modeleDamortissementDao
    ) {
        $this->typeEmpruntDao = $typeEmpruntDao;
        $this->simulationDao = $simulationDao;
        $this->travauxImmobiliseDao = $travauxImmobiliseDao;
        $this->periodiqueDao = $periodiqueDao;
        $this->typeEmpruntTravauxImmobiliseDao = $typeEmpruntTravauxImmobiliseDao;
        $this->profilEvolutionLoyerDao = $profilEvolutionLoyerDao;
        $this->modeleDamortissementDao = $modeleDamortissementDao;
    }

    /**
     * @param string[]|null $typeEmprunts
     *
     * @throws JsonException
     * @throws TDBMException
     *
     * @Factory()
     */
    public function createTravauxImmobilise(
        ?string $uuid,
        string $simulationId,
        string $nGroupe,
        ?string $nomCategorie,
        ?bool $conventionAnru,
        ?bool $indexationIcc,
        ?string $modeleDamortissementId,
        ?string $anneePremiereEcheance,
        int $type,
        ?float $foundsPropres,
        ?float $subventionsAnru,
        ?float $subventionsEtat,
        ?float $subventionsEpci,
        ?float $subventionsDepartement,
        ?float $subventionsRegion,
        ?float $subventionsCollecteur,
        ?float $autresSubventions,
        ?int $nSousGroupe,
        ?string $nomGroupe,
        ?string $information,
        ?string $profilEvolutionLoyerId,
        ?float $loyerMensuelInitial,
        ?int $numeroTranche,
        ?string $nomTranche,
        ?float $surfaceTraitee,
        ?float $variationSurfaceQuittance,
        ?float $nombreLogement,
        ?float $variationNombreLogement,
        ?float $anneeAgreement,
        ?string $dateOrdreService,
        ?string $dateFinTravaux,
        ?float $tauxVariationLoyer,
        ?string $dateApplication,
        ?float $prixRevient,
        ?bool $logementConventionnes,
        ?float $surfaceMoyenne,
        ?float $loyerMensuelMoyen,
        ?float $variationLoyer,
        ?int $anneeApplication,
        ?int $dureeChantier,
        ?float $montantTravaux,
        ?array $typeEmprunts,
        ?string $periodique
    ): TravauxImmobilise {
        $simulation = $this->simulationDao->getById($simulationId);

        $this->validateRequest($simulation, $type, $nGroupe);

        if ($uuid === null) {
            if ($this->travauxImmobiliseDao->findOneBySimulationAndNGroupeAndType($simulation, $nGroupe, $type) !== null) {
                throw HTTPException::badRequest('Le numéro renseigné existe déjà');
            }
            $travauxImmobilise = new TravauxImmobilise($simulation, $nGroupe, $type);
        } else {
            try {
                $travauxImmobilise = $this->travauxImmobiliseDao->getById($uuid);
                $travauxImmobilise->setNGroupe($nGroupe);
                $travauxImmobilise->setType($type);
                if (empty($travauxImmobilise)) {
                    throw HTTPException::notFound("Ce travaux immobilise n'existe pas");
                }
            } catch (Throwable $e) {
                throw HTTPException::notFound("Ce travaux immobilise n'existe pas", $e);
            }
        }

        $travauxImmobilise->setNomCategorie($nomCategorie);
        $travauxImmobilise->setConventionAnru($conventionAnru);
        $travauxImmobilise->setIndexationIcc($indexationIcc);
        $travauxImmobilise->setAnneePremiereEcheance($anneePremiereEcheance);
        $travauxImmobilise->setFoundsPropres($foundsPropres);
        $travauxImmobilise->setSubventionsAnru($subventionsAnru);
        $travauxImmobilise->setSubventionsEtat($subventionsEtat);
        $travauxImmobilise->setSubventionsEpci($subventionsEpci);
        $travauxImmobilise->setSubventionsDepartement($subventionsDepartement);
        $travauxImmobilise->setSubventionsRegion($subventionsRegion);
        $travauxImmobilise->setSubventionsCollecteur($subventionsCollecteur);
        $travauxImmobilise->setAutresSubventions($autresSubventions);
        $travauxImmobilise->setNSousGroupe($nSousGroupe);
        $travauxImmobilise->setNomGroupe($nomGroupe);
        $travauxImmobilise->setInformation($information);
        $travauxImmobilise->setLoyerMensuelInitial($loyerMensuelInitial);
        $travauxImmobilise->setNumeroTranche($numeroTranche);
        $travauxImmobilise->setNomTranche($nomTranche);
        $travauxImmobilise->setSurfaceTraitee($surfaceTraitee);
        $travauxImmobilise->setVariationSurfaceQuittance($variationSurfaceQuittance);
        $travauxImmobilise->setNombreLogement($nombreLogement);
        $travauxImmobilise->setVariationNombreLogement($variationNombreLogement);
        $travauxImmobilise->setAnneeAgreement($anneeAgreement);
        if ($dateOrdreService) {
            $travauxImmobilise->setDateOrdreService(new DateTimeImmutable($dateOrdreService));
        }
        if ($dateFinTravaux) {
            $travauxImmobilise->setDateFinTravaux(new DateTimeImmutable($dateFinTravaux));
        }
        $travauxImmobilise->setTauxVariationLoyer($tauxVariationLoyer);
        if ($dateApplication) {
            $travauxImmobilise->setDateApplication(new DateTimeImmutable($dateApplication));
        }
        $travauxImmobilise->setPrixRevient($prixRevient);
        $travauxImmobilise->setLogementConventionnes($logementConventionnes);
        $travauxImmobilise->setSurfaceMoyenne($surfaceMoyenne);
        $travauxImmobilise->setLoyerMensuelMoyen($loyerMensuelMoyen);
        $travauxImmobilise->setVariationLoyer($variationLoyer);
        $travauxImmobilise->setAnneeApplication($anneeApplication);
        $travauxImmobilise->setDureeChantier($dureeChantier);
        $travauxImmobilise->setMontantTravaux($montantTravaux);

        if (! empty($modeleDamortissementId)) {
            $modeleDamortissement = $this->modeleDamortissementDao->getById($modeleDamortissementId);
            $travauxImmobilise->setModeleDamortissement($modeleDamortissement);
        }

        if (! empty($profilEvolutionLoyerId)) {
            $profilEvolutionLoyer = $this->profilEvolutionLoyerDao->getById($profilEvolutionLoyerId);
            $travauxImmobilise->setProfilEvolutionLoyer($profilEvolutionLoyer);
        }

        if (! empty($typeEmprunts)) {
            foreach ($typeEmprunts as $typeEmprunt) {
                $typeEmprunt = json_decode($typeEmprunt);
                $typeEmpruntTravauxImmobilise = $this->typeEmpruntTravauxImmobiliseDao->findByTypeEmpruntAndTravauxImmobilise($typeEmprunt->id, $travauxImmobilise->getId());
                if (! empty($typeEmpruntTravauxImmobilise)) {
                    continue;
                }

                $existingTypeEmprunt = $this->typeEmpruntDao->getById($typeEmprunt->id);
                $typeEmpruntTravauxImmobilise = new TypeEmpruntTravauxImmobilise($existingTypeEmprunt, $travauxImmobilise);
                if (! empty($typeEmprunt->datePremiere)) {
                    $dateTimePremier = new DateTimeImmutable($typeEmprunt->datePremiere);
                    $typeEmpruntTravauxImmobilise->setDatePremiere($dateTimePremier);
                }
                if (! empty($typeEmprunt->montant)) {
                    $typeEmpruntTravauxImmobilise->setMontant(floatval($typeEmprunt->montant));
                }
                if (! empty($typeEmprunt->quotiteEmprunt)) {
                    $typeEmpruntTravauxImmobilise->setQuotiteEmprunt($typeEmprunt->quotiteEmprunt);
                }
                $this->typeEmpruntTravauxImmobiliseDao->save($typeEmpruntTravauxImmobilise);
            }
        }

        if ($periodique !== null) {
            $periodique = json_decode($periodique);
            switch ($type) {
                case TravauxImmobilise::TYPE_RENOUVELLEMENT:
                    if (isset($periodique->montant)) {
                        foreach ($periodique->montant as $key => $value) {
                            $travauxImmobilisePeriodique = $this->fetchTravauxImmobilisePeriodique($travauxImmobilise, $key, $uuid);
                            $travauxImmobilisePeriodique->setMontant($value ? floatval($value) : null);

                            $this->periodiqueDao->save($travauxImmobilisePeriodique);
                        }

                        break;
                    }
                    throw HTTPException::badRequest('Type invalide');
                case TravauxImmobilise::TYPE_NON_IDENTIFIEE:
                    if (isset($periodique->nombre_agrement) && isset($periodique->logement)) {
                        foreach ($periodique->nombre_agrement as $key => $value) {
                            $travauxImmobilisePeriodique = $this->fetchTravauxImmobilisePeriodique($travauxImmobilise, $key, $uuid);
                            $travauxImmobilisePeriodique->setNombreAgrement($value ? floatval($value) : null);
                            $travauxImmobilisePeriodique->setLogement(
                                empty($periodique->logement[$key]) ? null : floatval($periodique->logement[$key])
                            );

                            $this->periodiqueDao->save($travauxImmobilisePeriodique);
                        }

                        break;
                    }
                    throw HTTPException::badRequest('Type invalide');
            }
        }

        return $travauxImmobilise;
    }

    /**
     * Create Periodique
     */
    protected function fetchTravauxImmobilisePeriodique(TravauxImmobilise $travauxImmobilise, int $key, ?string $uuid): TravauxImmobilisePeriodique
    {
        $iteration = $key + 1;
        if ($uuid !== null) {
            $travauxImmobilisePeriodique = $travauxImmobilise->getTravauxImmobilisesPeriodique()->offsetGet($key);
        } else {
            $travauxImmobilisePeriodique = new TravauxImmobilisePeriodique($travauxImmobilise, $iteration);
        }

        return $travauxImmobilisePeriodique;
    }

    /**
     * Validate the request
     */
    protected function validateRequest(Simulation $simulation, int $type, string $nGroupe): void
    {
        if (empty($simulation)) {
            throw HTTPException::notFound("La simulation n'existe pas");
        }

        if (! in_array($type, TravauxImmobilise::TYPE_LIST)) {
            throw HTTPException::badRequest('Type invalide');
        }

        if (empty($nGroupe)) {
            throw HTTPException::badRequest('Veuillez sélectionner un N° de groupe');
        }
    }
}
