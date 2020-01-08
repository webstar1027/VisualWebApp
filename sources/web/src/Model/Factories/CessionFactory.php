<?php

declare(strict_types=1);

namespace App\Model\Factories;

use App\Dao\CessionDao;
use App\Dao\CessionPeriodiqueDao;
use App\Dao\SimulationDao;
use App\Model\Cession;
use App\Model\CessionPeriodique;
use App\Model\Simulation;
use Safe\Exceptions\JsonException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\TDBM\TDBMException;
use Throwable;
use function floatval;
use function in_array;
use function Safe\json_decode;

class CessionFactory
{
    /** @var SimulationDao */
    private $simulationDao;
    /** @var CessionDao */
    private $cessionDao;
    /** @var CessionPeriodiqueDao */
    private $periodiqueDao;

    public function __construct(
        SimulationDao $simulationDao,
        CessionDao $cessionDao,
        CessionPeriodiqueDao $periodiqueDao
    ) {
        $this->simulationDao = $simulationDao;
        $this->cessionDao = $cessionDao;
        $this->periodiqueDao = $periodiqueDao;
    }

    /**
     * @throws TDBMException
     * @throws JsonException
     *
     * @Factory()
     */
    public function createCession(
        ?string $uuid,
        string $simulationId,
        ?int $nGroupe,
        ?int $nSousGroupe,
        ?string $informations,
        ?string $nomCategory,
        ?string $nomGroupe,
        ?string $nature,
        ?bool $indexerInflation,
        ?int $cessionFinMois,
        ?int $reductionAmortissementAnnuelle,
        ?float $reductionTfpb,
        ?float $reductionGe,
        ?float $reductionMaintenance,
        ?float $reductionRepriseAnnuelle,
        ?float $dureeResiduelle,
        ?float $valeurComptable,
        ?float $loyerMensuel,
        ?float $nombreResiduelle,
        int $type,
        ?string $periodique
    ): Cession {
        $simulation = $this->simulationDao->getById($simulationId);

        $this->validateRequest($simulation, $type);

        if ($uuid === null) {
            $cession = new Cession($simulation, $type);
            if ($type === Cession::TYPE_NON_IDENTIFIEE) {
                $numero = $this->cessionDao->calculateNumero($simulationId, Cession::TYPE_NON_IDENTIFIEE);
                $cession->setNumero($numero);
            }
        } else {
            try {
                $cession = $this->cessionDao->getById($uuid);
                $cession->setType($type);
            } catch (Throwable $e) {
                throw new HttpException(Response::HTTP_NOT_FOUND, "Ce Cession n'existe pas", $e);
            }
        }

        $cession->setNGroupe($nGroupe);
        $cession->setNSousGroupe($nSousGroupe);
        $cession->setInformations($informations);
        $cession->setNomCategory($nomCategory);
        $cession->setNomGroupe($nomGroupe);
        $cession->setNature($nature);
        $cession->setIndexerInflation($indexerInflation);
        $cession->setCessionFinMois($cessionFinMois);
        $cession->setReductionAmortissementAnnuelle($reductionAmortissementAnnuelle);
        $cession->setReductionTfpb($reductionTfpb);
        $cession->setReductionGe($reductionGe);
        $cession->setReductionMaintenance($reductionMaintenance);
        $cession->setReductionRepriseAnnuelle($reductionRepriseAnnuelle);
        $cession->setDureeResiduelle($dureeResiduelle);
        $cession->setValeurComptable($valeurComptable);
        $cession->setLoyerMensuel($loyerMensuel);
        $cession->setNombreResiduelle($nombreResiduelle);

        if ($periodique !== null) {
            $periodique = json_decode($periodique);
            switch ($type) {
                case Cession::TYPE_IDENTIFIEE:
                    if (isset($periodique->mois_cession) &&
                        isset($periodique->nombre_logements) &&
                        isset($periodique->prix_nets_cession) &&
                        isset($periodique->remboursement_anticipe) &&
                        isset($periodique->ecomonies_capital) &&
                        isset($periodique->ecomonies_interets) &&
                        isset($periodique->valeur_cede)
                    ) {
                        foreach ($periodique->mois_cession as $key => $value) {
                            $cessionPeriodique = $this->fetchCessionPeriodique($cession, $key, $uuid);

                            $cessionPeriodique->setMoisCession($value ? floatval($value) : null);

                            $cessionPeriodique->setNombreLogements(
                                empty($periodique->nombre_logements[$key]) ? null : floatval($periodique->nombre_logements[$key])
                            );

                            $cessionPeriodique->setPrixNetsCession(
                                empty($periodique->prix_nets_cession[$key]) ? null : floatval($periodique->prix_nets_cession[$key])
                            );

                            $cessionPeriodique->setRemboursementAnticipe(
                                empty($periodique->remboursement_anticipe[$key]) ? null : floatval($periodique->remboursement_anticipe[$key])
                            );

                            $cessionPeriodique->setEcomoniesCapital(
                                empty($periodique->ecomonies_capital[$key]) ? null : floatval($periodique->ecomonies_capital[$key])
                            );

                            $cessionPeriodique->setEcomoniesInterets(
                                empty($periodique->ecomonies_interets[$key]) ? null : floatval($periodique->ecomonies_interets[$key])
                            );

                            $cessionPeriodique->setValeurCede(
                                empty($periodique->valeur_cede[$key]) ? null : floatval($periodique->valeur_cede[$key])
                            );

                            $this->periodiqueDao->save($cessionPeriodique);
                        }

                        break;
                    }
                    throw new HttpException(Response::HTTP_BAD_REQUEST, 'Type invalide');
                case Cession::TYPE_NON_IDENTIFIEE:
                    if (isset($periodique->nombre_logements) &&
                        isset($periodique->prix_nets_cession) &&
                        isset($periodique->remboursement_anticipe) &&
                        isset($periodique->valeur_cede)
                    ) {
                        foreach ($periodique->nombre_logements as $key => $value) {
                            $cessionPeriodique = $this->fetchCessionPeriodique($cession, $key, $uuid);

                            $cessionPeriodique->setNombreLogements($value ? floatval($value) : null);

                            $cessionPeriodique->setPrixNetsCession(
                                empty($periodique->prix_nets_cession[$key]) ? null : floatval($periodique->prix_nets_cession[$key])
                            );

                            $cessionPeriodique->setRemboursementAnticipe(
                                empty($periodique->remboursement_anticipe[$key]) ? null : floatval($periodique->remboursement_anticipe[$key])
                            );

                            $cessionPeriodique->setValeurCede(
                                empty($periodique->valeur_cede[$key]) ? null : floatval($periodique->valeur_cede[$key])
                            );

                            $this->periodiqueDao->save($cessionPeriodique);
                        }
                        break;
                    }
                    throw new HttpException(Response::HTTP_BAD_REQUEST, 'Type invalide');
            }
        }

        return $cession;
    }

    /**
     * Fetch or create Cession Periodique
     */
    protected function fetchCessionPeriodique(Cession $cession, int $key, ?string $uuid): CessionPeriodique
    {
        $iteration = $key + 1;
        if ($uuid !== null) {
            $cessionPeriodique = $cession->getCessionPeriodique()->offsetGet($key);
        } else {
            $cessionPeriodique = new CessionPeriodique($cession, $iteration);
        }

        return $cessionPeriodique;
    }

    /**
     * Validate the request
     */
    protected function validateRequest(Simulation $simulation, int $type): void
    {
        if (empty($simulation)) {
            throw new HttpException(Response::HTTP_NOT_FOUND, "La simulation n'existe pas");
        }

        if (! in_array($type, Cession::TYPE_LIST)) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Type invalide');
        }
    }
}
