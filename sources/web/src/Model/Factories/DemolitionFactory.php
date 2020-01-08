<?php

declare(strict_types=1);

namespace App\Model\Factories;

use App\Dao\DemolitionDao;
use App\Dao\DemolitionPeriodiqueDao;
use App\Dao\SimulationDao;
use App\Dao\TypeEmpruntDao;
use App\Dao\TypeEmpruntDemolitionDao;
use App\Exceptions\HTTPException;
use App\Model\Demolition;
use App\Model\DemolitionPeriodique;
use App\Model\Simulation;
use App\Model\TypeEmpruntDemolition;
use DateTimeImmutable;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\TDBM\TDBMException;
use Throwable;
use function floatval;
use function in_array;
use function Safe\json_decode;

class DemolitionFactory
{
    /** @var TypeEmpruntDao */
    private $typeEmpruntDao;

    /** @var SimulationDao */
    private $simulationDao;

    /** @var DemolitionDao */
    private $demolitionDao;

    /** @var DemolitionPeriodiqueDao */
    private $periodiqueDao;

    /** @var TypeEmpruntDemolitionDao */
    private $typeEmpruntDemolitionDao;

    public function __construct(
        SimulationDao $simulationDao,
        TypeEmpruntDao $typeEmpruntDao,
        DemolitionDao $demolitionDao,
        DemolitionPeriodiqueDao $periodiqueDao,
        TypeEmpruntDemolitionDao $typeEmpruntDemolitionDao
    ) {
        $this->typeEmpruntDao = $typeEmpruntDao;
        $this->simulationDao = $simulationDao;
        $this->demolitionDao = $demolitionDao;
        $this->periodiqueDao = $periodiqueDao;
        $this->typeEmpruntDemolitionDao = $typeEmpruntDemolitionDao;
    }

    /**
     * @param string[]|null $typeEmprunts
     *
     * @throws HTTPException
     * @throws TDBMException
     *
     * @Factory()
     */
    public function createDemolition(
        ?string $uuid,
        string $simulationId,
        int $nGroupe,
        ?int $nSousGroupe,
        ?string $nomGroupe,
        ?string $information,
        ?int $numero,
        ?string $nomTranche,
        ?bool $conventionAnru,
        ?float $surfaceDemolie,
        ?int $nombreLogementDemolis,
        ?string $dateDemolution,
        ?bool $indexationIcc,
        ?float $coutDemolution,
        ?float $remboursementCrd,
        ?float $subventionsEtat,
        ?float $coutAnnexes,
        ?float $remboursementSubventions,
        ?float $foundsPropres,
        ?float $subventionsAnru,
        ?float $subventionsEpci,
        ?float $subventionsDepartement,
        ?float $subventionsRegion,
        ?float $subventionsCollecteur,
        ?float $autresSubventions,
        ?float $montant,
        ?float $tfpb,
        ?float $maintenanceCourante,
        ?float $grosEntretien,
        ?string $nomCategorie,
        ?float $surfaceMoyenne,
        ?float $loyerMensuel,
        ?bool $logementsConventionees,
        ?int $nombreAnneesAmortissements,
        ?string $datePremier,
        ?float $quotiteEmprunt,
        int $type,
        ?array $typeEmprunts,
        ?string $periodique
    ): Demolition {
        $simulation = $this->simulationDao->getById($simulationId);

        $this->validateRequest($simulation, $type, $nGroupe);

        if ($uuid === null) {
            if ($this->demolitionDao->findOneByNGroupeAndSimulationAndType($nGroupe, $simulation, $type) !== null) {
                throw HTTPException::badRequest('Cette démolition existe déjà');
            }
            $demolition = new Demolition($simulation, $nGroupe, $type);
        } else {
            try {
                $demolition = $this->demolitionDao->getById($uuid);
                $demolition->setNGroupe($nGroupe);
                $demolition->setType($type);
            } catch (Throwable $e) {
                throw HTTPException::notFound("Cette démolition n'existe pas", $e);
            }
        }

        $demolition->setNSousGroupe($nSousGroupe);
        $demolition->setNomGroupe($nomGroupe);
        $demolition->setInformation($information);
        $demolition->setNumero($numero);
        $demolition->setNomTranche($nomTranche);
        $demolition->setConventionAnru($conventionAnru);
        $demolition->setSurfaceDemolie($surfaceDemolie);
        $demolition->setNombreLogementDemolis($nombreLogementDemolis);
        if (! empty($dateDemolution)) {
            $demolition->setDateDemolution(new DateTimeImmutable($dateDemolution));
        }
        $demolition->setIndexationIcc($indexationIcc);
        $demolition->setCoutDemolution($coutDemolution);
        $demolition->setRemboursementCrd($remboursementCrd);
        $demolition->setCoutAnnexes($coutAnnexes);
        $demolition->setRemboursementSubventions($remboursementSubventions);
        $demolition->setFoundsPropres($foundsPropres);
        $demolition->setSubventionsEtat($subventionsEtat);
        $demolition->setSubventionsAnru($subventionsAnru);
        $demolition->setSubventionsEpci($subventionsEpci);
        $demolition->setSubventionsDepartement($subventionsDepartement);
        $demolition->setSubventionsRegion($subventionsRegion);
        $demolition->setSubventionsCollecteur($subventionsCollecteur);
        $demolition->setAutresSubventions($autresSubventions);
        $demolition->setTfpb($tfpb);
        $demolition->setMaintenanceCourante($maintenanceCourante);
        $demolition->setGrosEntretien($grosEntretien);
        $demolition->setNomCategorie($nomCategorie);
        $demolition->setSurfaceMoyenne($surfaceMoyenne);
        $demolition->setLoyerMensuel($loyerMensuel);
        $demolition->setLogementsConventionees($logementsConventionees);
        $demolition->setNombreAnneesAmortissements($nombreAnneesAmortissements);

        if (! empty($typeEmprunts)) {
            foreach ($typeEmprunts as $typeEmprunt) {
                $typeEmprunt = json_decode($typeEmprunt);
                $typeEmpruntDemolition = $this->typeEmpruntDemolitionDao->findByTypeEmpruntAndDemolition($typeEmprunt->id, $demolition->getId());
                if (! empty($typeEmpruntDemolition)) {
                    continue;
                }

                $existingTypeEmprunt = $this->typeEmpruntDao->getById($typeEmprunt->id);
                $typeEmpruntDemolition = new TypeEmpruntDemolition($existingTypeEmprunt, $demolition);
                if (! empty($typeEmprunt->datePremier)) {
                    $dateTimePremier = new DateTimeImmutable($typeEmprunt->datePremier);
                    $typeEmpruntDemolition->setDatePremiere($dateTimePremier);
                }
                if (! empty($typeEmprunt->montant)) {
                    $typeEmpruntDemolition->setMontant(floatval($typeEmprunt->montant));
                }
                if (! empty($typeEmprunt->quotiteEmprunt)) {
                    $typeEmpruntDemolition->setQuotiteEmprunt($typeEmprunt->quotiteEmprunt);
                }
                $this->typeEmpruntDemolitionDao->save($typeEmpruntDemolition);
            }
        }

        if ($periodique !== null) {
            $periodique = json_decode($periodique);
            switch ($type) {
                case Demolition::TYPE_IDENTIFIEE:
                    if (isset($periodique->part_capital) && isset($periodique->part_interets)) {
                        foreach ($periodique->part_capital as $key => $value) {
                            $demolitionPeriodique = $this->fetchDemolitionPeriodique($demolition, $key, $uuid);
                            $demolitionPeriodique->setPartCapital($value ? floatval($value) : null);
                            $demolitionPeriodique->setPartInterets(
                                empty($periodique->part_interets[$key]) ? null : floatval($periodique->part_interets[$key])
                            );

                            $this->periodiqueDao->save($demolitionPeriodique);
                        }

                        break;
                    }
                    throw HTTPException::badRequest('Type invalide');
                case Demolition::TYPE_NON_IDENTIFIEE:
                    if (isset($periodique->nombre_logements)
                        && isset($periodique->cout_moyen)
                        && isset($periodique->remboursement)
                        && isset($periodique->cout_annexes)) {
                        foreach ($periodique->nombre_logements as $key => $value) {
                            $demolitionPeriodique = $this->fetchDemolitionPeriodique($demolition, $key, $uuid);
                            $demolitionPeriodique->setNombreLogements($value ? floatval($value) : null);

                            $demolitionPeriodique->setCoutMoyen(
                                empty($periodique->cout_moyen[$key]) ? null : floatval($periodique->cout_moyen[$key])
                            );

                            $demolitionPeriodique->setRemboursement(
                                empty($periodique->remboursement[$key]) ? null : floatval($periodique->remboursement[$key])
                            );

                            $demolitionPeriodique->setCoutAnnexes(
                                empty($periodique->cout_annexes[$key]) ? null : floatval($periodique->cout_annexes[$key])
                            );

                            $this->periodiqueDao->save($demolitionPeriodique);
                        }
                        break;
                    }
                    throw HTTPException::badRequest('Type invalide');
            }
        }

        return $demolition;
    }

    protected function fetchDemolitionPeriodique(Demolition $demolition, int $key, ?string $uuid): DemolitionPeriodique
    {
        $iteration = $key + 1;
        if ($uuid !== null) {
            $demolitionPeriodique = $demolition->getDemolitionPeriodique()->offsetGet($key);
        } else {
            $demolitionPeriodique = new DemolitionPeriodique($demolition, $iteration);
        }

        return $demolitionPeriodique;
    }

    protected function validateRequest(Simulation $simulation, int $type, int $nGroupe): void
    {
        if (empty($simulation)) {
            throw HTTPException::notFound('La simulation n\'existe pas');
        }

        if (! in_array($type, Demolition::TYPE_LIST)) {
            throw HTTPException::badRequest('Type invalide');
        }
    }
}
