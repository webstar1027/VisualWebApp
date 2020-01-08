<?php

declare(strict_types=1);

namespace App\Model\Factories;

use App\Dao\ModeleDamortissementDao;
use App\Dao\OperationDao;
use App\Dao\OperationPeriodiqueDao;
use App\Dao\ProfilEvolutionLoyerDao;
use App\Dao\SimulationDao;
use App\Dao\TypeEmpruntDao;
use App\Dao\TypeEmpruntOperationDao;
use App\Model\Operation;
use App\Model\OperationPeriodique;
use App\Model\Simulation;
use App\Model\TypeEmpruntOperation;
use DateTimeImmutable;
use Exception;
use Safe\Exceptions\JsonException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\TDBM\TDBMException;
use Throwable;
use function floatval;
use function in_array;
use function Safe\json_decode;

class OperationFactory
{
    /** @var TypeEmpruntDao */
    private $typeEmpruntDao;
    /** @var SimulationDao */
    private $simulationDao;
    /** @var OperationDao */
    private $operationDao;
    /** @var OperationPeriodiqueDao */
    private $periodiqueDao;
    /** @var TypeEmpruntOperationDao */
    private $typeEmpruntOperationDao;
    /** @var ModeleDamortissementDao */
    private $modeleDamortissementDao;
    /** @var ProfilEvolutionLoyerDao */
    private $profilEvolutionLoyerDao;

    public function __construct(
        SimulationDao $simulationDao,
        TypeEmpruntDao $typeEmpruntDao,
        OperationDao $operationDao,
        OperationPeriodiqueDao $periodiqueDao,
        TypeEmpruntOperationDao $typeEmpruntOperationDao,
        ModeleDamortissementDao $modeleDamortissementDao,
        ProfilEvolutionLoyerDao $profilEvolutionLoyerDao
    ) {
        $this->typeEmpruntDao = $typeEmpruntDao;
        $this->simulationDao = $simulationDao;
        $this->operationDao = $operationDao;
        $this->periodiqueDao = $periodiqueDao;
        $this->typeEmpruntOperationDao = $typeEmpruntOperationDao;
        $this->modeleDamortissementDao = $modeleDamortissementDao;
        $this->profilEvolutionLoyerDao = $profilEvolutionLoyerDao;
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
    public function createOperation(
        ?string $uuid,
        string $simulationId,
        string $nom,
        ?int $natureOperation,
        ?bool $conventionAnru,
        ?int $videOccupe,
        ?int $secteurFinancement,
        ?float $surfaceQuittancee,
        ?float $nombreLogements,
        ?float $loyerMensuel,
        ?string $profilLoyerUUID,
        ?bool $indexeIrl,
        ?float $nombreGarages,
        ?int $loyerMensuelGarages,
        ?float $nombreCommerces,
        ?float $loyerMensuelCommerces,
        ?float $anneAgrement,
        ?string $dateOrdreService,
        ?string $dateMiseService,
        ?bool $acquisitionFin,
        ?int $dureeTravaux,
        ?int $tfpbLogt,
        ?int $tfpbDuree,
        ?bool $indexationIcc,
        ?string $modeleDamortissementUUID,
        ?int $prixFoncier,
        ?int $prixRevient,
        ?float $fondsPropres,
        ?int $moyenOperation,
        ?int $moyenFoncier,
        float $subventionsEtat,
        float $subventionsAnru,
        float $subventionsEpci,
        float $subventionsDepartement,
        float $subventionsRegion,
        float $subventionsCollecteur,
        float $subventionsAutres,
        float $total,
        float $resteFinancer,
        ?float $tfpbTauxEvolution,
        ?float $maintenanceTauxEvolution,
        ?float $grosTauxEvolution,
        ?float $montant,
        ?string $datePremier,
        ?int $dureeChantier,
        int $type,
        ?array $typeEmprunts,
        ?string $periodique
    ): Operation {
        $simulation = $this->simulationDao->getById($simulationId);

        $this->validateRequest($simulation, $type);

        if (empty($uuid)) {
            $nOperation = $this->operationDao->calculateNoperation($simulationId, $type);

            $operation = new Operation(
                $simulation,
                $nOperation,
                $nom,
                $total,
                $resteFinancer,
                $type
            );
        } else {
            try {
                $operation = $this->operationDao->getById($uuid);
                if (empty($operation)) {
                    throw new HttpException(Response::HTTP_NOT_FOUND, "Ce Opération n'existe pas");
                }
                $operation->setNom($nom);
                $operation->setTotal($total);
                $operation->setResteFinancer($resteFinancer);
                $operation->setType($type);
            } catch (Throwable $e) {
                throw new HttpException(Response::HTTP_NOT_FOUND, "Ce Opération n'existe pas", $e);
            }
        }

        $operation->setPrixFoncier($prixFoncier);
        $operation->setPrixRevient($prixRevient);
        $operation->setFondsPropres($fondsPropres);
        $operation->setTfpbTauxEvolution($tfpbTauxEvolution);
        $operation->setMaintenanceTauxEvolution($maintenanceTauxEvolution);
        $operation->setGrosTauxEvolution($grosTauxEvolution);
        $operation->setNatureOperation($natureOperation);
        $operation->setConventionAnru($conventionAnru);
        $operation->setVideOccupe($videOccupe);
        $operation->setSecteurFinancement($secteurFinancement);
        $operation->setSurfaceQuittancee($surfaceQuittancee);
        $operation->setNombreLogements($nombreLogements);
        $operation->setLoyerMensuel($loyerMensuel);
        $operation->setMoyenFoncier($moyenFoncier);
        $operation->setMoyenOperation($moyenOperation);
        $operation->setSubventionsEtat($subventionsEtat);
        $operation->setSubventionsAnru($subventionsAnru);
        $operation->setSubventionsEpci($subventionsEpci);
        $operation->setSubventionsDepartement($subventionsDepartement);
        $operation->setSubventionsRegion($subventionsRegion);
        $operation->setSubventionsCollecteur($subventionsCollecteur);
        $operation->setSubventionsAutres($subventionsAutres);

        if ($modeleDamortissementUUID !== null) {
            $modeleDamortissement = $this->modeleDamortissementDao->getById($modeleDamortissementUUID);
            $operation->setModeleDamortissement($modeleDamortissement);
        }

        if ($profilLoyerUUID !== null) {
            $profilLoyer = $this->profilEvolutionLoyerDao->getById($profilLoyerUUID);
            $operation->setProfilLoyer($profilLoyer);
        }

        $operation->setIndexeIrl($indexeIrl);
        $operation->setNombreGarages($nombreGarages);
        $operation->setLoyerMensuelGarages($loyerMensuelGarages);
        $operation->setNombreCommerces($nombreCommerces);
        $operation->setLoyerMensuelCommerces($loyerMensuelCommerces);
        $operation->setAnneAgrement($anneAgrement);
        if ($dateOrdreService) {
            $operation->setDateOrdreService(new DateTimeImmutable($dateOrdreService));
        }
        if ($dateMiseService) {
            $operation->setDateMiseService(new DateTimeImmutable($dateMiseService));
        }
        $operation->setAcquisitionFin($acquisitionFin);
        $operation->setDureeTravaux($dureeTravaux);
        $operation->setTfpbLogt($tfpbLogt);
        $operation->setTfpbDuree($tfpbDuree);
        $operation->setIndexationIcc($indexationIcc);
        $operation->setDureeChantier($dureeChantier);

        if (! empty($typeEmprunts)) {
            foreach ($typeEmprunts as $typeEmprunt) {
                $typeEmprunt = json_decode($typeEmprunt);
                $operationTypeEmprunt = $this->typeEmpruntOperationDao->findByTypeEmpruntAndOperation($typeEmprunt->id, $operation->getId());
                if (! empty($operationTypeEmprunt)) {
                    continue;
                }

                $existingTypeEmprunt = $this->typeEmpruntDao->getById($typeEmprunt->id);
                $operationTypeEmprunt = new TypeEmpruntOperation($existingTypeEmprunt, $operation);
                if (! empty($typeEmprunt->datePremiere)) {
                    $dateTimePremier = new DateTimeImmutable($typeEmprunt->datePremiere);
                    $operationTypeEmprunt->setDatePremiere($dateTimePremier);
                }
                if (! empty($typeEmprunt->montant)) {
                    $operationTypeEmprunt->setMontant($typeEmprunt->montant);
                }
                $this->typeEmpruntOperationDao->save($operationTypeEmprunt);
            }
        }

        if ($periodique !== null) {
            $periodique = json_decode($periodique);
            switch ($type) {
                case Operation::TYPE_IDENTIFIEE:
                    if (isset($periodique->taux_evolution_loyer) &&
                        isset($periodique->complement_loyer) &&
                        isset($periodique->complement_annuite_capital) &&
                        isset($periodique->complement_annuite_interet) &&
                        isset($periodique->taux_vacance) &&
                        isset($periodique->tfpb) &&
                        isset($periodique->maintenance_courante) &&
                        isset($periodique->gros_entretien) &&
                        isset($periodique->depot_garantie)
                    ) {
                        foreach ($periodique->taux_evolution_loyer as $key => $value) {
                            $operationPeriodique = $this->fetchOperationPeriodique($operation, $key, $uuid);
                            $operationPeriodique->setTauxEvolutionLoyer($value ? floatval($value) : null);

                            $operationPeriodique->setComplementLoyer(
                                empty($periodique->complement_loyer[$key]) ? null : floatval($periodique->complement_loyer[$key])
                            );
                            $operationPeriodique->setComplementAnnuiteCapital(
                                empty($periodique->complement_annuite_capital[$key]) ? null : floatval($periodique->complement_annuite_capital[$key])
                            );

                            $operationPeriodique->setComplementAnnuiteInteret(
                                empty($periodique->complement_annuite_interet[$key]) ? null : floatval($periodique->complement_annuite_interet[$key])
                            );

                            $operationPeriodique->setTauxVacance(
                                empty($periodique->taux_vacance[$key]) ? null : floatval($periodique->taux_vacance[$key])
                            );

                            $operationPeriodique->setTfpb(
                                empty($periodique->tfpb[$key]) ? null : floatval($periodique->tfpb[$key])
                            );

                            $operationPeriodique->setMaintenanceCourante(
                                empty($periodique->maintenance_courante[$key]) ? null : floatval($periodique->maintenance_courante[$key])
                            );

                            $operationPeriodique->setGrosEntretien(
                                empty($periodique->gros_entretien[$key]) ? null : floatval($periodique->gros_entretien[$key])
                            );

                            $operationPeriodique->setDepotGarantie(
                                empty($periodique->depot_garantie[$key]) ? null : floatval($periodique->depot_garantie[$key])
                            );

                            $this->periodiqueDao->save($operationPeriodique);
                        }

                        break;
                    }
                    throw new HttpException(Response::HTTP_BAD_REQUEST, 'Type invalide');

                case Operation::TYPE_NON_IDENTIFIEE:
                    if (isset($periodique->nombre_agrement)
                        && isset($periodique->nombre_logement)
                        && isset($periodique->nb_garages)
                    ) {
                        foreach ($periodique->nombre_agrement as $key => $value) {
                            $operationPeriodique = $this->fetchOperationPeriodique($operation, $key, $uuid);
                            $operationPeriodique->setNombreAgrement($value ? floatval($value) : null);

                            $operationPeriodique->setNombreLogement(
                                empty($periodique->nombre_logement[$key]) ? null : floatval($periodique->nombre_logement[$key])
                            );

                            $operationPeriodique->setNbGarages(
                                empty($periodique->nb_garages[$key]) ? null : floatval($periodique->nb_garages[$key])
                            );

                            $this->periodiqueDao->save($operationPeriodique);
                        }
                        break;
                    }
                    throw new HttpException(Response::HTTP_BAD_REQUEST, 'Type invalide');
            }
        }

        return $operation;
    }

    protected function fetchOperationPeriodique(Operation $operation, int $key, ?string $uuid): OperationPeriodique
    {
        $iteration = $key + 1;
        if ($uuid !== null) {
            $operationPeriodique = $operation->getOperationPeriodique()->offsetGet($key);
        } else {
            $operationPeriodique = new OperationPeriodique($operation, $iteration);
        }

        return $operationPeriodique;
    }

    protected function validateRequest(Simulation $simulation, int $type): void
    {
        if (empty($simulation)) {
            throw new HttpException(Response::HTTP_NOT_FOUND, "La simulation n'existe pas");
        }

        if (! in_array($type, Operation::TYPE_LIST)) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Type invalide');
        }
    }
}
