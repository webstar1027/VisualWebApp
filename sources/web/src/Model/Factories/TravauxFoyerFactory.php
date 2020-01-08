<?php

declare(strict_types=1);

namespace App\Model\Factories;

use App\Dao\ModeleDamortissementDao;
use App\Dao\SimulationDao;
use App\Dao\TravauxFoyerDao;
use App\Dao\TravauxFoyerPeriodiqueDao;
use App\Dao\TypeEmpruntDao;
use App\Dao\TypeEmpruntTravauxFoyerDao;
use App\Exceptions\HTTPException;
use App\Model\Simulation;
use App\Model\TravauxFoyer;
use App\Model\TravauxFoyerPeriodique;
use App\Model\TypeEmpruntTravauxFoyer;
use DateTimeImmutable;
use Exception;
use Safe\Exceptions\JsonException;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\TDBM\TDBMException;
use Throwable;
use function floatval;
use function Safe\json_decode;

class TravauxFoyerFactory
{
    /** @var SimulationDao */
    private $simulationDao;
    /** @var TypeEmpruntDao */
    private $typeEmpruntDao;
    /** @var TravauxFoyerDao */
    private $travauxFoyerDao;
    /** @var TravauxFoyerPeriodiqueDao */
    private $periodiqueDao;
    /** @var TypeEmpruntTravauxFoyerDao */
    private $typeEmpruntTravauxFoyerDao;
    /** @var ModeleDamortissementDao */
    private $modeleDamortissementDao;

    public function __construct(
        SimulationDao $simulationDao,
        TypeEmpruntDao $typeEmpruntDao,
        TravauxFoyerDao $travauxFoyerDao,
        TravauxFoyerPeriodiqueDao $periodiqueDao,
        TypeEmpruntTravauxFoyerDao $typeEmpruntTravauxFoyerDao,
        ModeleDamortissementDao $modeleDamortissementDao
    ) {
        $this->simulationDao = $simulationDao;
        $this->typeEmpruntDao = $typeEmpruntDao;
        $this->travauxFoyerDao = $travauxFoyerDao;
        $this->periodiqueDao = $periodiqueDao;
        $this->typeEmpruntTravauxFoyerDao = $typeEmpruntTravauxFoyerDao;
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
    public function createTravauxFoyer(
        ?string $uuid,
        string $simulationId,
        int $numero,
        string $nomIntervention,
        ?int $nombreLogements,
        ?int $anneeAgrement,
        string $dateAcquisition,
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
        ?float $totalEmprunt,
        ?string $modeleDamortissementUUID,
        ?array $typeEmprunts,
        ?string $periodique
    ): TravauxFoyer {
        $simulation = $this->simulationDao->getById($simulationId);

        $this->validateRequest($simulation);

        $dateAcquisition = new DateTimeImmutable($dateAcquisition);
        $dateTravaux = new DateTimeImmutable($dateTravaux);

        if (empty($uuid)) {
            $travauxFoyer = $this->travauxFoyerDao->findOneByNumeroAndSimulation($numero, $simulation);

            if (! empty($travauxFoyer)) {
                throw HTTPException::badRequest('Ce numéro de travaux existe déjà');
            }

            $travauxFoyer = new TravauxFoyer(
                $simulation,
                $numero,
                $nomIntervention,
                $dateAcquisition,
                $dateTravaux,
                $prixRevient,
                $fondsPropres
            );
        } else {
            try {
                $travauxFoyer = $this->travauxFoyerDao->getById($uuid);
                if (empty($travauxFoyer)) {
                    throw HTTPException::notFound("Ce Travaux Foyer n'existe pas");
                }
                $travauxFoyer->setNumero($numero);
                $travauxFoyer->setNomIntervention($nomIntervention);
                $travauxFoyer->setDateAcquisition($dateAcquisition);
                $travauxFoyer->setDateTravaux($dateTravaux);
                $travauxFoyer->setPrixRevient($prixRevient);
                $travauxFoyer->setFondsPropres($fondsPropres);
            } catch (Throwable $e) {
                throw HTTPException::notFound("Ce Travaux Foyer n'existe pas", $e);
            }
        }

        if ($modeleDamortissementUUID !== null) {
            $modeleDamortissement = $this->modeleDamortissementDao->getById($modeleDamortissementUUID);
            $travauxFoyer->setModeleDamortissement($modeleDamortissement);
        }

        $travauxFoyer->setAnneeAgrement($anneeAgrement);
        $travauxFoyer->setNombreLogements($nombreLogements);
        $travauxFoyer->setIndexationIcc($indexation_icc);
        $travauxFoyer->setSubventionsEtat($subventionsEtat);
        $travauxFoyer->setSubventionsAnru($subventionsAnru);
        $travauxFoyer->setSubventionsEpci($subventionsEpci);
        $travauxFoyer->setSubventionsDepartement($subventionsDepartement);
        $travauxFoyer->setSubventionsRegion($subventionsRegion);
        $travauxFoyer->setSubventionsCollecteur($subventionsCollecteur);
        $travauxFoyer->setAutresSubventions($autresSubventions);
        $travauxFoyer->setTotalEmprunt($totalEmprunt);

        if (! empty($typeEmprunts)) {
            foreach ($typeEmprunts as $typeEmprunt) {
                $typeEmprunt = json_decode($typeEmprunt);
                $travauxFoyerTypeEmprunt = $this->typeEmpruntTravauxFoyerDao->findByTypeEmpruntAndTravauxFoyer(
                    $typeEmprunt->id,
                    $travauxFoyer->getId()
                );
                if (! empty($travauxFoyerTypeEmprunt)) {
                    continue;
                }

                $existingTypeEmprunt = $this->typeEmpruntDao->getById($typeEmprunt->id);
                $travauxFoyerTypeEmprunt = new TypeEmpruntTravauxFoyer($existingTypeEmprunt, $travauxFoyer);
                if (! empty($typeEmprunt->datePremiere)) {
                    $dateTimePremier = new DateTimeImmutable($typeEmprunt->datePremiere);
                    $travauxFoyerTypeEmprunt->setDatePremiere($dateTimePremier);
                }
                if (! empty($typeEmprunt->montant)) {
                    $travauxFoyerTypeEmprunt->setMontant(floatval($typeEmprunt->montant));
                }
                $this->typeEmpruntTravauxFoyerDao->save($travauxFoyerTypeEmprunt);
            }
        }

        if ($periodique !== null) {
            $periodique = json_decode($periodique);

            if (isset($periodique->revedance)
                && isset($periodique->complements_capital)
                && isset($periodique->complements_interet)
            ) {
                foreach ($periodique->revedance as $key => $value) {
                    $travauxFoyerPeriodique = $this->fetchTravauxFoyerPeriodique($travauxFoyer, $key, $uuid);
                    $travauxFoyerPeriodique->setRevedance($value ? floatval($value) : null);

                    $this->periodiqueDao->save($travauxFoyerPeriodique);
                }
            }
        }

        return $travauxFoyer;
    }

    protected function fetchTravauxFoyerPeriodique(
        TravauxFoyer $travauxFoyer,
        int $key,
        ?string $uuid
    ): TravauxFoyerPeriodique {
        $iteration = $key + 1;
        if ($uuid !== null) {
            $travauxPeriodique = $travauxFoyer->getTravauxFoyerPeriodique()->offsetGet($key);
        } else {
            $travauxPeriodique = new TravauxFoyerPeriodique($travauxFoyer, $iteration);
        }

        return $travauxPeriodique;
    }

    protected function validateRequest(Simulation $simulation): void
    {
        if (empty($simulation)) {
            throw HTTPException::notFound("La simulation n'existe pas");
        }
    }
}
