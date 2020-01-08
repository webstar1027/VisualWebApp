<?php

declare(strict_types=1);

namespace App\Controller\GraphQL;

use App\Controller\AbstractVisialWebController;
use App\Dao\AccessionCodificationDao;
use App\Dao\AccessionFraiStructureDao;
use App\Dao\AccessionProduitChargeDao;
use App\Dao\CcmiDao;
use App\Dao\CessionDao;
use App\Dao\CessionFoyerDao;
use App\Dao\DemolitionDao;
use App\Dao\DemolitionFoyerDao;
use App\Dao\FoyerFraiStructureDao;
use App\Dao\LotissementDao;
use App\Dao\NouveauxFoyerDao;
use App\Dao\OperationDao;
use App\Dao\PatrimoineFoyerDao;
use App\Dao\PslaDao;
use App\Dao\TravauxFoyerDao;
use App\Dao\TravauxImmobiliseDao;
use App\Dao\UtilisateurDao;
use App\Dao\VacanceDao;
use App\Dao\VefaDao;
use App\Services\PatrimoineService;
use TheCodingMachine\GraphQLite\Annotations\Query;
use function Safe\json_encode;

class CountController extends AbstractVisialWebController
{
    /** @var PatrimoineService */
    private $patrimoineService;
    /** @var TravauxImmobiliseDao */
    private $travauxImmobiliseDao;
    /** @var OperationDao */
    private $operationDao;
    /** @var CessionDao */
    private $cessionDao;
    /** @var VacanceDao */
    private $vacanceDao;
    /** @var DemolitionDao */
    private $demolitionDao;
    /** @var PatrimoineFoyerDao */
    private $patrimoineFoyerDao;
    /** @var TravauxFoyerDao */
    private $travauxFoyerDao;
    /** @var NouveauxFoyerDao */
    private $nouveauxFoyerDao;
    /** @var CessionFoyerDao */
    private $cessionFoyerDao;
    /** @var FoyerFraiStructureDao */
    private $foyerFraiStructureDao;
    /** @var DemolitionFoyerDao */
    private $demolitionFoyerDao;
    /** @var PslaDao */
    private $pslaDao;
    /** @var VefaDao */
    private $vefaDao;
    /** @var LotissementDao */
    private $lotissementDao;
    /** @var CcmiDao */
    private $ccmiDao;
    /** @var AccessionCodificationDao */
    private $accessionCodificationDao;
    /** @var AccessionFraiStructureDao */
    private $accessionFraiStructureDao;
    /** @var AccessionProduitChargeDao */
    private $accessionProduitChargeDao;

    public function __construct(
        UtilisateurDao $utilisateurDao,
        PatrimoineService $patrimoineService,
        TravauxImmobiliseDao $travauxImmobiliseDao,
        OperationDao $operationDao,
        CessionDao $cessionDao,
        VacanceDao $vacanceDao,
        DemolitionDao $demolitionDao,
        PatrimoineFoyerDao $patrimoineFoyerDao,
        TravauxFoyerDao $travauxFoyerDao,
        NouveauxFoyerDao $nouveauxFoyerDao,
        CessionFoyerDao $cessionFoyerDao,
        FoyerFraiStructureDao $foyerFraiStructureDao,
        DemolitionFoyerDao $demolitionFoyerDao,
        PslaDao $pslaDao,
        VefaDao $vefaDao,
        LotissementDao $lotissementDao,
        CcmiDao $ccmiDao,
        AccessionCodificationDao $accessionCodificationDao,
        AccessionFraiStructureDao $accessionFraiStructureDao,
        AccessionProduitChargeDao $accessionProduitChargeDao
    ) {
        parent::__construct($utilisateurDao);
        $this->patrimoineService = $patrimoineService;
        $this->travauxImmobiliseDao = $travauxImmobiliseDao;
        $this->operationDao = $operationDao;
        $this->cessionDao = $cessionDao;
        $this->vacanceDao = $vacanceDao;
        $this->demolitionDao = $demolitionDao;
        $this->patrimoineFoyerDao = $patrimoineFoyerDao;
        $this->travauxFoyerDao = $travauxFoyerDao;
        $this->nouveauxFoyerDao = $nouveauxFoyerDao;
        $this->cessionFoyerDao = $cessionFoyerDao;
        $this->foyerFraiStructureDao = $foyerFraiStructureDao;
        $this->demolitionFoyerDao = $demolitionFoyerDao;
        $this->pslaDao = $pslaDao;
        $this->vefaDao = $vefaDao;
        $this->lotissementDao = $lotissementDao;
        $this->ccmiDao = $ccmiDao;
        $this->accessionCodificationDao = $accessionCodificationDao;
        $this->accessionFraiStructureDao = $accessionFraiStructureDao;
        $this->accessionProduitChargeDao = $accessionProduitChargeDao;
    }

    /**
     * @Query()
     */
    public function fetchLogementCounts(string $simulationID): string
    {
        $patrimoines = $this->patrimoineService->fetchBySimulationId($simulationID);
        $travauxImmobilises1 = $this->travauxImmobiliseDao->findBySimulationIDAndType($simulationID, 0);
        $travauxImmobilises2 = $this->travauxImmobiliseDao->findBySimulationIDAndType($simulationID, 1);
        $travauxImmobilises3 = $this->travauxImmobiliseDao->findBySimulationIDAndType($simulationID, 2);
        $operations1 = $this->operationDao->findBySimulationIDAndType($simulationID, 0);
        $operations2 = $this->operationDao->findBySimulationIDAndType($simulationID, 1);
        $cessions1 = $this->cessionDao->findBySimulationIDAndType($simulationID, 0);
        $cessions2 = $this->cessionDao->findBySimulationIDAndType($simulationID, 1);
        $vacance = $this->vacanceDao->findBySimulationID($simulationID);
        $demolitions1 = $this->demolitionDao->findBySimulationIDAndType($simulationID, 0);
        $demolitions2 = $this->demolitionDao->findBySimulationIDAndType($simulationID, 1);

        $counts = [
            'patrimoines' => $patrimoines->count(),
            'travauxImmobilises' => [
                $travauxImmobilises1->count(),
                $travauxImmobilises2->count(),
                $travauxImmobilises3->count(),
            ],
            'operations' => [
                $operations1->count(),
                $operations2->count(),
            ],
            'cessions' => [
                $cessions1->count(),
                $cessions2->count(),
            ],
            'vacance' => $vacance->count(),
            'demolitions' => [
                $demolitions1->count(),
                $demolitions2->count(),
            ],
        ];

        return json_encode($counts);
    }

    /**
     * @Query()
     */
    public function fetchFoyerCounts(string $simulationID): string
    {
        $patrimoineFoyer = $this->patrimoineFoyerDao->findBySimulationID($simulationID);
        $travauxFoyer = $this->travauxFoyerDao->findBySimulationID($simulationID);
        $nouveauxFoyer = $this->nouveauxFoyerDao->findBySimulationID($simulationID);
        $cessionFoyer = $this->cessionFoyerDao->findBySimulationID($simulationID);
        $foyerFraiStructure = $this->foyerFraiStructureDao->findBySimulationID($simulationID);
        $demolitionFoyer = $this->demolitionFoyerDao->findBySimulationID($simulationID);

        $counts = [
            'patrimoinesFoyer' => $patrimoineFoyer->count(),
            'travauxFoyers' => $travauxFoyer->count(),
            'nouveauxFoyers' => $nouveauxFoyer->count(),
            'cessionFoyers' => $cessionFoyer->count(),
            'foyersFraisStructures' => $foyerFraiStructure->count(),
            'demolitionFoyers' => $demolitionFoyer->count(),
        ];

        return json_encode($counts);
    }

    /**
     * @Query()
     */
    public function fetchAccessionCounts(string $simulationID): string
    {
        $psla1 = $this->pslaDao->findBySimulationIDAndType($simulationID, 0);
        $psla2 = $this->pslaDao->findBySimulationIDAndType($simulationID, 1);
        $vefa1 = $this->vefaDao->findBySimulationIDAndType($simulationID, 'Identifiée');
        $vefa2 = $this->vefaDao->findBySimulationIDAndType($simulationID, 'Non identifiée');
        $lotissement1 = $this->lotissementDao->findBySimulationIDAndType($simulationID, 0);
        $lotissement2 = $this->lotissementDao->findBySimulationIDAndType($simulationID, 1);
        $ccmi = $this->ccmiDao->findBySimulationId($simulationID);
        $accessionCodification = $this->accessionCodificationDao->findBySimulationId($simulationID);
        $accessionFraiStructure = $this->accessionFraiStructureDao->findBySimulationID($simulationID);
        $accessionProduitCharge = $this->accessionProduitChargeDao->findBySimulationID($simulationID);

        $counts = [
            'pslas' => [
                $psla1->count(),
                $psla2->count(),
            ],
            'vefa' => [
                $vefa1->count(),
                $vefa2->count(),
            ],
            'lotissements' => [
                $lotissement1->count(),
                $lotissement2->count(),
            ],
            'ccmi' => $ccmi->count(),
            'accessionCodifications' => $accessionCodification->count(),
            'accessionFraisStructures' => $accessionFraiStructure->count(),
            'accessionProduitCharges' => $accessionProduitCharge->count(),
        ];

        return json_encode($counts);
    }
}
