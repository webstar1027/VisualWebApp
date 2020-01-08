<?php

declare(strict_types=1);

namespace App\Model\Factories;

use App\Dao\PslaDao;
use App\Dao\PslaPeriodiqueDao;
use App\Dao\SimulationDao;
use App\Dao\TypeEmpruntDao;
use App\Dao\TypeEmpruntPslaDao;
use App\Exceptions\HTTPException;
use App\Model\Psla;
use App\Model\PslaPeriodique;
use App\Model\Simulation;
use App\Model\TypeEmpruntPsla;
use DateTimeImmutable;
use Exception;
use Safe\Exceptions\JsonException;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\TDBM\TDBMException;
use Throwable;
use function floatval;
use function in_array;
use function Safe\json_decode;

class PslaFactory
{
    /** @var SimulationDao */
    private $simulationDao;
    /** @var TypeEmpruntDao */
    private $typeEmpruntDao;
    /** @var PslaDao */
    private $pslaDao;
    /** @var PslaPeriodiqueDao */
    private $periodiqueDao;
    /** @var TypeEmpruntPslaDao */
    private $typeEmpruntPslaDao;

    public function __construct(
        SimulationDao $simulationDao,
        TypeEmpruntDao $typeEmpruntDao,
        PslaDao $pslaDao,
        PslaPeriodiqueDao $periodiqueDao,
        TypeEmpruntPslaDao $typeEmpruntPslaDao
    ) {
        $this->simulationDao = $simulationDao;
        $this->typeEmpruntDao = $typeEmpruntDao;
        $this->pslaDao = $pslaDao;
        $this->periodiqueDao = $periodiqueDao;
        $this->typeEmpruntPslaDao = $typeEmpruntPslaDao;
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
    public function createPsla(
        ?string $uuid,
        string $simulationId,
        int $numero,
        string $nom,
        ?string $directSci,
        ?float $detention,
        bool $operationStock,
        ?int $nombreLogements,
        ?float $prixVente,
        ?float $tauxBrute,
        ?int $dureeConstruction,
        ?string $dateLivraison,
        ?float $loyerMensuel,
        ?float $tauxEvolution,
        ?int $dureePhase,
        ?float $montantSubventions,
        ?float $montantEmprunts,
        ?float $total,
        int $type,
        ?array $typeEmprunts,
        ?string $periodique
    ): Psla {
        $simulation = $this->simulationDao->getById($simulationId);

        $this->validateRequest($simulation, $type);

        if (empty($uuid)) {
            $pslaObj = $this->pslaDao->findOneByNumeroAndSimulationAndType($numero, $simulation, $type);
            if ($pslaObj !== null) {
                throw HTTPException::badRequest('Ce numéro est déjà utilisé');
            }
            $psla = new Psla($simulation, $numero, $nom, $type);
        } else {
            try {
                $psla = $this->pslaDao->getById($uuid);
                if (empty($psla)) {
                    throw HTTPException::notFound("Ce Psla n'existe pas");
                }
                $psla->setNom($nom);
                $psla->setNumero($numero);
                $psla->setType($type);
            } catch (Throwable $e) {
                throw HTTPException::notFound("Ce Psla n'existe pas", $e);
            }
        }

        if ($dateLivraison) {
            $psla->setDateLivraison(new DateTimeImmutable($dateLivraison));
        }

        $psla->setDirectSci($directSci);
        $psla->setDetention($detention);
        $psla->setOperationStock($operationStock);
        $psla->setNombreLogements($nombreLogements);
        $psla->setPrixVente($prixVente);
        $psla->setTauxBrute($tauxBrute);
        $psla->setDureeConstruction($dureeConstruction);
        $psla->setLoyerMensuel($loyerMensuel);
        $psla->setTauxEvolution($tauxEvolution);
        $psla->setDureePhase($dureePhase);
        $psla->setMontantSubventions($montantSubventions);
        $psla->setMontantEmprunts($montantEmprunts);
        $psla->setTotal($total);

        if (! empty($typeEmprunts)) {
            foreach ($typeEmprunts as $typeEmprunt) {
                $typeEmprunt = json_decode($typeEmprunt);
                $pslaTypeEmprunt = $this->typeEmpruntPslaDao->findByTypeEmpruntAndPsla($typeEmprunt->id, $psla->getId());
                if (! empty($pslaTypeEmprunt)) {
                    continue;
                }

                $existingTypeEmprunt = $this->typeEmpruntDao->getById($typeEmprunt->id);
                $pslaTypeEmprunt = new TypeEmpruntPsla($existingTypeEmprunt, $psla);
                if (! empty($typeEmprunt->datePremiere)) {
                    $dateTimePremier = new DateTimeImmutable($typeEmprunt->datePremiere);
                    $pslaTypeEmprunt->setDatePremiere($dateTimePremier);
                }
                if (! empty($typeEmprunt->montant)) {
                    $pslaTypeEmprunt->setMontant($typeEmprunt->montant);
                }
                $this->typeEmpruntPslaDao->save($pslaTypeEmprunt);
            }
        }

        if ($periodique !== null) {
            $periodique = json_decode($periodique);
            switch ($type) {
                case Psla::TYPE_IDENTIFIEE:
                    if (isset($periodique->contrats_accession) &&
                        isset($periodique->levees_option) &&
                        isset($periodique->couts_internes)
                    ) {
                        foreach ($periodique->contrats_accession as $key => $value) {
                            $pslaPeriodique = $this->fetchPslaPeriodique($psla, $key, $uuid);

                            $pslaPeriodique->setContratsAccession($value ? floatval($value) : null);

                            $pslaPeriodique->setLeveesOption(
                                empty($periodique->levees_option[$key]) ? null : floatval($periodique->levees_option[$key])
                            );
                            $pslaPeriodique->setCoutsInternes(
                                empty($periodique->couts_internes[$key]) ? null : floatval($periodique->couts_internes[$key])
                            );

                            $this->periodiqueDao->save($pslaPeriodique);
                        }

                        break;
                    }
                    throw HTTPException::badRequest('Type invalide');

                case Psla::TYPE_NON_IDENTIFIEE:
                    if (isset($periodique->portage_fp)
                        && isset($periodique->nombre_logements)
                        && isset($periodique->couts_internes)
                    ) {
                        foreach ($periodique->portage_fp as $key => $value) {
                            $pslaPeriodique = $this->fetchPslaPeriodique($psla, $key, $uuid);
                            $pslaPeriodique->setPortageFp($value ? floatval($value) : null);

                            $pslaPeriodique->setNombreLogements(
                                empty($periodique->nombre_logements[$key]) ? null : floatval($periodique->nombre_logements[$key])
                            );

                            $pslaPeriodique->setCoutsInternes(
                                empty($periodique->couts_internes[$key]) ? null : floatval($periodique->couts_internes[$key])
                            );

                            $this->periodiqueDao->save($pslaPeriodique);
                        }
                        break;
                    }
                    throw HTTPException::badRequest('Type invalide');
            }
        }

        return $psla;
    }

    protected function fetchPslaPeriodique(Psla $psla, int $key, ?string $uuid): PslaPeriodique
    {
        $iteration = $key + 1;
        if ($uuid !== null) {
            $pslaPeriodique = $psla->getPslaPeriodique()->offsetGet($key);
        } else {
            $pslaPeriodique = new PslaPeriodique($psla, $iteration);
        }

        return $pslaPeriodique;
    }

    protected function validateRequest(Simulation $simulation, int $type): void
    {
        if (empty($simulation)) {
            throw HTTPException::notFound("La simulation n'existe pas");
        }

        if (! in_array($type, Psla::TYPE_LIST)) {
            throw HTTPException::badRequest('Type invalide');
        }
    }
}
