<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\SimulationClonedEvent;
use App\Event\SimulationCreatedEvent;
use App\Event\SimulationFusionedEvent;
use App\Event\SimulationUpdatedEvent;
use App\Services\AccessionFraisStructureService;
use App\Services\AccessionProduitChargeService;
use App\Services\AnnuiteService;
use App\Services\AutreChargeService;
use App\Services\CcmiService;
use App\Services\CessionFoyerService;
use App\Services\CessionService;
use App\Services\CgllsAncolsService;
use App\Services\DemolitionFoyerService;
use App\Services\DemolitionService;
use App\Services\FondDeRoulementService;
use App\Services\FoyersFraisStructureService;
use App\Services\FraisStructureService;
use App\Services\HypotheseService;
use App\Services\IndiceTauxService;
use App\Services\LotissementService;
use App\Services\LoyerService;
use App\Services\MaintenanceService;
use App\Services\ModeleDamortissementService;
use App\Services\NouveauxFoyerService;
use App\Services\OperationService;
use App\Services\PatrimoineFoyersService;
use App\Services\PatrimoineService;
use App\Services\PortageTresorerieService;
use App\Services\ProduitAutreService;
use App\Services\ProduitChargeService;
use App\Services\ProfilEvolutionLoyerService;
use App\Services\PslaService;
use App\Services\ResultatComptableService;
use App\Services\RisqueLocatifService;
use App\Services\SimulationService;
use App\Services\TableauBordService;
use App\Services\TravauxFoyerService;
use App\Services\TravauxImmobilisesService;
use App\Services\TypeEmpruntService;
use App\Services\VacanceService;
use App\Services\VefaService;
use Safe\Exceptions\JsonException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use TheCodingMachine\TDBM\TDBMException;

final class SimulationSubscriber implements EventSubscriberInterface
{
    /** @var MaintenanceService */
    private $maintenanceService;

    /** @var AutreChargeService */
    private $autreChargeService;

    /** @var ProfilEvolutionLoyerService */
    private $profilEvolutionLoyerService;

    /** @var CgllsAncolsService */
    private $cgllsAncolsService;

    /** @var AnnuiteService */
    private $annuiteService;

    /** @var ResultatComptableService */
    private $resultatComptableService;

    /** @var LoyerService */
    private $loyerService;

    /** @var FondDeRoulementService */
    private $fondDeRoulementService;

    /** @var PortageTresorerieService */
    private $portageTresorerieService;

    /** @var PatrimoineService */
    private $patrimoineService;
    /** @var TableauBordService */
    private $tableauBordService;

    /** @var AccessionFraisStructureService */
    private $accessionFraisStructureService;
    /** @var AccessionProduitChargeService */
    private $accessionProduitChargeService;
    /** @var CcmiService */
    private $ccmiService;
    /** @var CessionFoyerService */
    private $cessionFoyerService;
    /** @var CessionService */
    private $cessionService;
    /** @var DemolitionFoyerService */
    private $demolitionFoyerService;
    /** @var DemolitionService */
    private $demolitionService;
    /** @var FoyersFraisStructureService */
    private $foyersFraisStructureService;
    /** @var FraisStructureService */
    private $fraisStructureService;
    /** @var HypotheseService */
    private $hypotheseService;
    /** @var IndiceTauxService */
    private $indiceTauxService;
    /** @var LotissementService */
    private $lotissementService;
    /** @var ModeleDamortissementService */
    private $modeleDamortissementService;
    /** @var NouveauxFoyerService */
    private $nouveauxFoyerService;
    /** @var OperationService */
    private $operationService;
    /** @var PatrimoineFoyersService */
    private $patrimoineFoyersService;
    /** @var ProduitAutreService */
    private $produitAutreService;
    /** @var ProduitChargeService */
    private $produitChargeService;
    /** @var PslaService */
    private $pslaService;
    /** @var RisqueLocatifService */
    private $risqueLocatifService;
    /** @var TravauxFoyerService */
    private $travauxFoyerService;
    /** @var TravauxImmobilisesService */
    private $travauxImmobilisesService;
    /** @var TypeEmpruntService */
    private $typeEmpruntService;
    /** @var VacanceService */
    private $vacanceService;
    /** @var VefaService */
    private $vefaService;
    /** @var SimulationService */
    private $simulationService;

    public function __construct(
        AccessionFraisStructureService $accessionFraisStructureService,
        AccessionProduitChargeService $accessionProduitChargeService,
        AnnuiteService $annuiteService,
        AutreChargeService $autreChargeService,
        CcmiService $ccmiService,
        CessionFoyerService $cessionFoyerService,
        CessionService $cessionService,
        CgllsAncolsService $cgllsAncolsService,
        DemolitionFoyerService $demolitionFoyerService,
        DemolitionService $demolitionService,
        FondDeRoulementService $fondDeRoulementService,
        FoyersFraisStructureService $foyersFraisStructureService,
        FraisStructureService $fraisStructureService,
        HypotheseService $hypotheseService,
        IndiceTauxService $indiceTauxService,
        LotissementService $lotissementService,
        LoyerService $loyerService,
        MaintenanceService $maintenanceService,
        ModeleDamortissementService $modeleDamortissementService,
        NouveauxFoyerService $nouveauxFoyerService,
        OperationService $operationService,
        PatrimoineFoyersService $patrimoineFoyersService,
        PatrimoineService $patrimoineService,
        PortageTresorerieService $portageTresorerieService,
        RisqueLocatifService $risqueLocatifService,
        TableauBordService $tableauBordService,
        ProduitAutreService $produitAutreService,
        ProduitChargeService $produitChargeService,
        ProfilEvolutionLoyerService $profilEvolutionLoyerService,
        PslaService $pslaService,
        ResultatComptableService $resultatComptableService,
        TravauxFoyerService $travauxFoyerService,
        TravauxImmobilisesService $travauxImmobilisesService,
        TypeEmpruntService $typeEmpruntService,
        VacanceService $vacanceService,
        VefaService $vefaService,
        SimulationService $simulationService
    ) {
        $this->accessionFraisStructureService = $accessionFraisStructureService;
        $this->accessionProduitChargeService = $accessionProduitChargeService;
        $this->annuiteService = $annuiteService;
        $this->autreChargeService = $autreChargeService;
        $this->ccmiService = $ccmiService;
        $this->cessionFoyerService = $cessionFoyerService;
        $this->cessionService = $cessionService;
        $this->cgllsAncolsService = $cgllsAncolsService;
        $this->demolitionFoyerService = $demolitionFoyerService;
        $this->demolitionService = $demolitionService;
        $this->fondDeRoulementService = $fondDeRoulementService;
        $this->foyersFraisStructureService = $foyersFraisStructureService;
        $this->fraisStructureService = $fraisStructureService;
        $this->hypotheseService = $hypotheseService;
        $this->indiceTauxService = $indiceTauxService;
        $this->lotissementService = $lotissementService;
        $this->loyerService = $loyerService;
        $this->maintenanceService = $maintenanceService;
        $this->modeleDamortissementService = $modeleDamortissementService;
        $this->nouveauxFoyerService = $nouveauxFoyerService;
        $this->operationService = $operationService;
        $this->patrimoineFoyersService = $patrimoineFoyersService;
        $this->patrimoineService = $patrimoineService;
        $this->portageTresorerieService = $portageTresorerieService;
        $this->produitAutreService = $produitAutreService;
        $this->produitChargeService = $produitChargeService;
        $this->profilEvolutionLoyerService = $profilEvolutionLoyerService;
        $this->pslaService = $pslaService;
        $this->resultatComptableService = $resultatComptableService;
        $this->risqueLocatifService = $risqueLocatifService;
        $this->tableauBordService = $tableauBordService;
        $this->travauxFoyerService = $travauxFoyerService;
        $this->travauxImmobilisesService = $travauxImmobilisesService;
        $this->typeEmpruntService = $typeEmpruntService;
        $this->vacanceService = $vacanceService;
        $this->vefaService = $vefaService;
        $this->simulationService = $simulationService;
    }

    /**
     * @return mixed[]
     */
    public static function getSubscribedEvents(): array
    {
        /// return the subscribed events, their methods and priorities
        return [
            SimulationCreatedEvent::class => [
                ['createDefaultAutresCharges'],
                ['createDefaultMaintenance'],
                ['createDefaultAnnuite'],
                ['createDefaultResultatComptableService'],
                ['createDefaultLoyer'],
                ['createProfilEvolutionLoyerParametre'],
                ['createCgllsAncols'],
                ['createFondDeRoulement'],
                ['createDefaultPortageTresorerie'],
                ['createDefaultParametrePatrimoineLogement'],
                ['createDefaultRisqueLocatif'],
                ['createTableauDeBord'],
            ],
            SimulationClonedEvent::class => [
                ['cloneAccessionFraisStructure'],
                ['cloneAccessionProduitCharge'],
                ['cloneAnnuite'],
                ['cloneAutresCharges'],
                ['cloneCcmi'],
                ['cloneCessionFoyer'],
                ['cloneCession'],
                ['cloneCgllsAncols'],
                ['cloneDemolitionFoyer'],
                ['cloneDemolition'],
                ['cloneFondDeRoulement'],
                ['cloneFoyersFraisStructure'],
                ['cloneFraisStructure'],
                ['cloneHypothese'],
                ['cloneIndiceTaux'],
                ['cloneLotissement'],
                ['cloneLoyer'],
                ['cloneMaintenance'],
                ['cloneModeleDamortissement'],
                ['cloneNouveauxFoyer'],
                ['cloneOperation'],
                ['clonePatrimoineFoyer'],
                ['clonePatrimoine'],
                ['clonePortageTresorerie'],
                ['cloneProduitAutre'],
                ['cloneProduitCharge'],
                ['cloneProfilEvolutionLoyer'],
                ['clonePsla'],
                ['cloneResultatComptable'],
                ['cloneRisqueLocatif'],
                ['cloneTravauxFoyer'],
                ['cloneTravauxImmobilises'],
                ['cloneTypeEmprunt'],
                ['cloneVacance'],
                ['cloneVefa'],
                ['cloneTableauDeBord'],
            ],
            SimulationFusionedEvent::class => [
                ['fusionIndicesTaux'],
                ['fusionProfilEvolutionLoyer'],
                ['fusionTypeEmprunt'],
                ['fusionModeleDamortissement'],
                ['fusionResultatComptable'],
                ['fusionFondDeRoulement'],
                ['fusionPortageTresorerie'],
                ['fusionLoyer'],
                ['fusionProduitAutre'],
                ['fusionAnnuite'],
                ['fusionAutresCharges'],
                ['fusionMaintenance'],
                ['fusionRisqueLocatif'],
                ['fusionCgllsAncols'],
                ['fusionPatrimoine'],
                ['fusionTravauxImmobilises'],
                ['fusionOperation'],
                ['fusionCession'],
                ['fusionVacance'],
                ['fusionDemolition'],
                ['fusionPatrimoineFoyer'],
                ['fusionCessionFoyer'],
                ['fusionDemolitionFoyer'],
                ['fusionTravauxFoyer'],
                ['fusionNouveauxFoyer'],
                ['fusionFoyersFraisStructure'],
                ['fusionPsla'],
                ['fusionVefa'],
                ['fusionLotissement'],
                ['fusionCcmi'],
                ['fusionAccessionFraisStructure'],
                ['fusionAccessionProduitCharge'],
                ['fusionFraisStructure'],
                ['fusionProduitCharge'],
                ['fusionTableauDeBord'],
            ],
            SimulationUpdatedEvent::class => [
                ['updateDateModification'],
            ],
        ];
    }

    /**
     * @throws JsonException
     * @throws TDBMException
     */
    public function createDefaultAutresCharges(SimulationCreatedEvent $event): void
    {
        $this->autreChargeService->createDefaultAutresCharges($event->getSimulation());
    }

    public function cloneAccessionFraisStructure(SimulationClonedEvent $event): void
    {
        $this->accessionFraisStructureService->cloneFraiStructure($event->getSimulation(), $event->getOldsimulation());
    }

    public function cloneAccessionProduitCharge(SimulationClonedEvent $event): void
    {
        $this->accessionProduitChargeService->cloneProduitCharge($event->getSimulation(), $event->getOldsimulation());
    }

    public function cloneAnnuite(SimulationClonedEvent $event): void
    {
        $this->annuiteService->cloneAnnuite($event->getSimulation(), $event->getOldsimulation());
    }

    public function cloneAutresCharges(SimulationClonedEvent $event): void
    {
        $this->autreChargeService->cloneAutresCharges($event->getSimulation(), $event->getOldsimulation());
    }

    public function cloneCcmi(SimulationClonedEvent $event): void
    {
        $this->ccmiService->cloneCcmi($event->getSimulation(), $event->getOldsimulation());
    }

    public function cloneCessionFoyer(SimulationClonedEvent $event): void
    {
        $this->cessionFoyerService->cloneCessionFoyer($event->getSimulation(), $event->getOldsimulation());
    }

    public function cloneCession(SimulationClonedEvent $event): void
    {
        $this->cessionService->cloneCession($event->getSimulation(), $event->getOldsimulation());
    }

    public function cloneCgllsAncols(SimulationClonedEvent $event): void
    {
        $this->cgllsAncolsService->cloneCgllsAncols($event->getSimulation(), $event->getOldsimulation());
    }

    public function cloneDemolitionFoyer(SimulationClonedEvent $event): void
    {
        $this->demolitionFoyerService->cloneDemolitionFoyer($event->getSimulation(), $event->getOldsimulation());
    }

    public function cloneDemolition(SimulationClonedEvent $event): void
    {
        $this->demolitionService->cloneDemolition($event->getSimulation(), $event->getOldsimulation());
    }

    public function cloneFondDeRoulement(SimulationClonedEvent $event): void
    {
        $this->fondDeRoulementService->cloneFondDeRoulement($event->getSimulation(), $event->getOldsimulation());
    }

    public function cloneFoyersFraisStructure(SimulationClonedEvent $event): void
    {
        $this->foyersFraisStructureService->cloneFoyersFraisStructure($event->getSimulation(), $event->getOldsimulation());
    }

    public function cloneFraisStructure(SimulationClonedEvent $event): void
    {
        $this->fraisStructureService->cloneFraisStructure($event->getSimulation(), $event->getOldsimulation());
    }

    public function cloneHypothese(SimulationClonedEvent $event): void
    {
        $this->hypotheseService->cloneHypothese($event->getSimulation(), $event->getOldsimulation());
    }

    public function cloneIndiceTaux(SimulationClonedEvent $event): void
    {
        $this->indiceTauxService->cloneIndiceTaux($event->getSimulation(), $event->getOldsimulation());
    }

    public function cloneLotissement(SimulationClonedEvent $event): void
    {
        $this->lotissementService->cloneLotissement($event->getSimulation(), $event->getOldsimulation());
    }

    public function cloneLoyer(SimulationClonedEvent $event): void
    {
        $this->loyerService->cloneLoyer($event->getSimulation(), $event->getOldsimulation());
    }

    public function cloneMaintenance(SimulationClonedEvent $event): void
    {
        $this->maintenanceService->cloneMaintenance($event->getSimulation(), $event->getOldsimulation());
    }

    public function cloneModeleDamortissement(SimulationClonedEvent $event): void
    {
        $this->modeleDamortissementService->cloneModeleDamortissement($event->getSimulation(), $event->getOldsimulation());
    }

    public function cloneNouveauxFoyer(SimulationClonedEvent $event): void
    {
        $this->nouveauxFoyerService->cloneNouveauxFoyer($event->getSimulation(), $event->getOldsimulation());
    }

    public function cloneOperation(SimulationClonedEvent $event): void
    {
        $this->operationService->cloneOperation($event->getSimulation(), $event->getOldsimulation());
    }

    public function clonePatrimoineFoyer(SimulationClonedEvent $event): void
    {
        $this->patrimoineFoyersService->clonePatrimoineFoyer($event->getSimulation(), $event->getOldsimulation());
    }

    public function clonePatrimoine(SimulationClonedEvent $event): void
    {
        $this->patrimoineService->clonePatrimoine($event->getSimulation(), $event->getOldsimulation());
    }

    public function clonePortageTresorerie(SimulationClonedEvent $event): void
    {
        $this->portageTresorerieService->clonePortageTresorerie($event->getSimulation(), $event->getOldsimulation());
    }

    public function cloneProduitAutre(SimulationClonedEvent $event): void
    {
        $this->produitAutreService->cloneProduitAutre($event->getSimulation(), $event->getOldsimulation());
    }

    public function cloneProduitCharge(SimulationClonedEvent $event): void
    {
        $this->produitChargeService->cloneProduitCharge($event->getSimulation(), $event->getOldsimulation());
    }

    public function cloneProfilEvolutionLoyer(SimulationClonedEvent $event): void
    {
        $this->profilEvolutionLoyerService->cloneProfilEvolutionLoyer($event->getSimulation(), $event->getOldsimulation());
    }

    public function clonePsla(SimulationClonedEvent $event): void
    {
        $this->pslaService->clonePsla($event->getSimulation(), $event->getOldsimulation());
    }

    public function cloneResultatComptable(SimulationClonedEvent $event): void
    {
        $this->resultatComptableService->cloneResultatComptable($event->getSimulation(), $event->getOldsimulation());
    }

    public function cloneRisqueLocatif(SimulationClonedEvent $event): void
    {
        $this->risqueLocatifService->cloneRisqueLocatif($event->getSimulation(), $event->getOldsimulation());
    }

    public function cloneTravauxFoyer(SimulationClonedEvent $event): void
    {
        $this->travauxFoyerService->cloneTravauxFoyer($event->getSimulation(), $event->getOldsimulation());
    }

    public function cloneTravauxImmobilises(SimulationClonedEvent $event): void
    {
        $this->travauxImmobilisesService->cloneTravauxImmobilises($event->getSimulation(), $event->getOldsimulation());
    }

    public function cloneTypeEmprunt(SimulationClonedEvent $event): void
    {
        $this->typeEmpruntService->cloneTypeEmprunt($event->getSimulation(), $event->getOldsimulation());
    }

    public function cloneVacance(SimulationClonedEvent $event): void
    {
        $this->vacanceService->cloneVacance($event->getSimulation(), $event->getOldsimulation());
    }

    public function cloneVefa(SimulationClonedEvent $event): void
    {
        $this->vefaService->cloneVefa($event->getSimulation(), $event->getOldsimulation());
    }

    /**
     * @throws JsonException
     * @throws TDBMException
     */
    public function cloneTableauDeBord(SimulationClonedEvent $event): void
    {
        $this->tableauBordService->createDefaultTableauDeBord($event->getSimulation());
    }

    /**
     * @throws JsonException
     * @throws TDBMException
     */
    public function createDefaultMaintenance(SimulationCreatedEvent $event): void
    {
        $this->maintenanceService->createDefaultMaintenance($event->getSimulation());
    }

    /**
     * @throws JsonException
     * @throws TDBMException
     */
    public function createDefaultAnnuite(SimulationCreatedEvent $event): void
    {
        $this->annuiteService->createDefaultAnnuite($event->getSimulation());
    }

    /**
     * @throws JsonException
     * @throws TDBMException
     */
    public function createDefaultResultatComptableService(SimulationCreatedEvent $event): void
    {
        $this->resultatComptableService->createDefaultResultatComptable($event->getSimulation());
    }

    /**
     * @throws JsonException
     * @throws TDBMException
     */
    public function createDefaultLoyer(SimulationCreatedEvent $event): void
    {
        $this->loyerService->createDefaultLoyer($event->getSimulation());
    }

    /**
     * @throws JsonException
     * @throws TDBMException
     */
    public function createProfilEvolutionLoyerParametre(SimulationCreatedEvent $event): void
    {
        $this->profilEvolutionLoyerService->createProfilEvolutionLoyerParametre($event->getSimulation());
    }

    /**
     * @throws JsonException
     * @throws TDBMException
     */
    public function createCgllsAncols(SimulationCreatedEvent $event): void
    {
        $this->cgllsAncolsService->createCgllsAncols($event->getSimulation());
    }

    /**
     * @throws JsonException
     * @throws TDBMException
     */
    public function createFondDeRoulement(SimulationCreatedEvent $event): void
    {
        $this->fondDeRoulementService->createFondsRoulements($event->getSimulation());
    }

    /**
     * @throws JsonException
     * @throws TDBMException
     */
    public function createDefaultPortageTresorerie(SimulationCreatedEvent $event): void
    {
        $this->portageTresorerieService->createPortagesTresoreries($event->getSimulation());
    }

    /**
     * @throws JsonException
     * @throws TDBMException
     */
    public function createDefaultParametrePatrimoineLogement(SimulationCreatedEvent $event): void
    {
        $this->patrimoineService->createDefaultParametrePatrimoineLogements($event->getSimulation());
    }

    /**
     * @throws JsonException
     * @throws TDBMException
     */
    public function createDefaultRisqueLocatif(SimulationCreatedEvent $event): void
    {
        $this->risqueLocatifService->createRisqueLocatifPeriodique($event->getSimulation());
    }

    /**
     * @throws JsonException
     * @throws TDBMException
     */
    public function createTableauDeBord(SimulationCreatedEvent $event): void
    {
        $this->tableauBordService->createDefaultTableauDeBord($event->getSimulation());
    }

    public function fusionIndicesTaux(SimulationFusionedEvent $event): void
    {
        $this->indiceTauxService->createIndicesTaux($event->getSimulation()->getId());
    }

    public function fusionProfilEvolutionLoyer(SimulationFusionedEvent $event): void
    {
        $this->profilEvolutionLoyerService->fusionProfilEvolutionLoyer($event->getSimulation(), $event->getOldsimulation1(), $event->getOldsimulation2());
    }

    public function fusionTypeEmprunt(SimulationFusionedEvent $event): void
    {
        $this->typeEmpruntService->fusionTypeEmprunt($event->getSimulation(), $event->getOldsimulation1(), $event->getOldsimulation2());
    }

    public function fusionModeleDamortissement(SimulationFusionedEvent $event): void
    {
        $this->modeleDamortissementService->fusionModeleDamortissement($event->getSimulation(), $event->getOldsimulation1(), $event->getOldsimulation2());
    }

    public function fusionResultatComptable(SimulationFusionedEvent $event): void
    {
        $this->resultatComptableService->fusionResultatComptable($event->getSimulation(), $event->getOldsimulation1(), $event->getOldsimulation2());
    }

    public function fusionFondDeRoulement(SimulationFusionedEvent $event): void
    {
        $this->fondDeRoulementService->fusionFondDeRoulement($event->getSimulation(), $event->getOldsimulation1(), $event->getOldsimulation2());
    }

    public function fusionPortageTresorerie(SimulationFusionedEvent $event): void
    {
        $this->portageTresorerieService->fusionPortageTresorerie($event->getSimulation(), $event->getOldsimulation1(), $event->getOldsimulation2());
    }

    public function fusionLoyer(SimulationFusionedEvent $event): void
    {
        $this->loyerService->fusionLoyer($event->getSimulation(), $event->getOldsimulation1(), $event->getOldsimulation2());
    }

    public function fusionProduitAutre(SimulationFusionedEvent $event): void
    {
        $this->produitAutreService->fusionProduitAutre($event->getSimulation(), $event->getOldsimulation1(), $event->getOldsimulation2());
    }

    public function fusionAnnuite(SimulationFusionedEvent $event): void
    {
        $this->annuiteService->fusionAnnuite($event->getSimulation(), $event->getOldsimulation1(), $event->getOldsimulation2());
    }

    public function fusionAutresCharges(SimulationFusionedEvent $event): void
    {
        $this->autreChargeService->fusionAutresCharges($event->getSimulation(), $event->getOldsimulation1(), $event->getOldsimulation2());
    }

    public function fusionMaintenance(SimulationFusionedEvent $event): void
    {
        $this->maintenanceService->fusionMaintenance($event->getSimulation(), $event->getOldsimulation1(), $event->getOldsimulation2());
    }

    public function fusionRisqueLocatif(SimulationFusionedEvent $event): void
    {
        $this->risqueLocatifService->createRisqueLocatifPeriodique($event->getSimulation());
    }

    public function fusionCgllsAncols(SimulationFusionedEvent $event): void
    {
        $this->cgllsAncolsService->createCgllsAncols($event->getSimulation());
    }

    public function fusionPatrimoine(SimulationFusionedEvent $event): void
    {
        $this->patrimoineService->fusionPatrimoine($event->getSimulation(), $event->getOldsimulation1(), $event->getOldsimulation2());
    }

    public function fusionTravauxImmobilises(SimulationFusionedEvent $event): void
    {
        $this->travauxImmobilisesService->fusionTravauxImmobilises($event->getSimulation(), $event->getOldsimulation1(), $event->getOldsimulation2());
    }

    public function fusionOperation(SimulationFusionedEvent $event): void
    {
        $this->operationService->fusionOperation($event->getSimulation(), $event->getOldsimulation1(), $event->getOldsimulation2());
    }

    public function fusionCession(SimulationFusionedEvent $event): void
    {
        $this->cessionService->fusionCession($event->getSimulation(), $event->getOldsimulation1(), $event->getOldsimulation2());
    }

    public function fusionVacance(SimulationFusionedEvent $event): void
    {
        $this->vacanceService->fusionVacance($event->getSimulation(), $event->getOldsimulation1(), $event->getOldsimulation2());
    }

    public function fusionDemolition(SimulationFusionedEvent $event): void
    {
        $this->demolitionService->fusionDemolition($event->getSimulation(), $event->getOldsimulation1(), $event->getOldsimulation2());
    }

    public function fusionPatrimoineFoyer(SimulationFusionedEvent $event): void
    {
        $this->patrimoineFoyersService->fusionPatrimoineFoyer($event->getSimulation(), $event->getOldsimulation1(), $event->getOldsimulation2());
    }

    public function fusionCessionFoyer(SimulationFusionedEvent $event): void
    {
        $this->cessionFoyerService->fusionCessionFoyer($event->getSimulation(), $event->getOldsimulation1(), $event->getOldsimulation2());
    }

    public function fusionDemolitionFoyer(SimulationFusionedEvent $event): void
    {
        $this->demolitionFoyerService->fusionDemolitionFoyer($event->getSimulation(), $event->getOldsimulation1(), $event->getOldsimulation2());
    }

    public function fusionTravauxFoyer(SimulationFusionedEvent $event): void
    {
        $this->travauxFoyerService->fusionTravauxFoyer($event->getSimulation(), $event->getOldsimulation1(), $event->getOldsimulation2());
    }

    public function fusionNouveauxFoyer(SimulationFusionedEvent $event): void
    {
        $this->nouveauxFoyerService->fusionNouveauxFoyer($event->getSimulation(), $event->getOldsimulation1(), $event->getOldsimulation2());
    }

    public function fusionFoyersFraisStructure(SimulationFusionedEvent $event): void
    {
        $this->foyersFraisStructureService->fusionFoyersFraisStructure($event->getSimulation(), $event->getOldsimulation1(), $event->getOldsimulation2());
    }

    public function fusionPsla(SimulationFusionedEvent $event): void
    {
        $this->pslaService->fusionPsla($event->getSimulation(), $event->getOldsimulation1(), $event->getOldsimulation2());
    }

    public function fusionVefa(SimulationFusionedEvent $event): void
    {
        $this->vefaService->fusionVefa($event->getSimulation(), $event->getOldsimulation1(), $event->getOldsimulation2());
    }

    public function fusionLotissement(SimulationFusionedEvent $event): void
    {
        $this->lotissementService->fusionLotissement($event->getSimulation(), $event->getOldsimulation1(), $event->getOldsimulation2());
    }

    public function fusionCcmi(SimulationFusionedEvent $event): void
    {
        $this->ccmiService->fusionCcmi($event->getSimulation(), $event->getOldsimulation1(), $event->getOldsimulation2());
    }

    public function fusionAccessionFraisStructure(SimulationFusionedEvent $event): void
    {
        $this->accessionFraisStructureService->fusionAccessionFraisStructure($event->getSimulation(), $event->getOldsimulation1(), $event->getOldsimulation2());
    }

    public function fusionAccessionProduitCharge(SimulationFusionedEvent $event): void
    {
        $this->accessionProduitChargeService->fusionAccessionProduitCharge($event->getSimulation(), $event->getOldsimulation1(), $event->getOldsimulation2());
    }

    public function fusionFraisStructure(SimulationFusionedEvent $event): void
    {
        $this->fraisStructureService->fusionFraisStructure($event->getSimulation(), $event->getOldsimulation1(), $event->getOldsimulation2());
    }

    public function fusionProduitCharge(SimulationFusionedEvent $event): void
    {
        $this->produitChargeService->fusionProduitCharge($event->getSimulation(), $event->getOldsimulation1(), $event->getOldsimulation2());
    }

    public function fusionTableauDeBord(SimulationFusionedEvent $event): void
    {
        $this->tableauBordService->createDefaultTableauDeBord($event->getSimulation());
    }

    public function updateDateModification(SimulationUpdatedEvent $event): void
    {
        $this->simulationService->updateDateModification($event->getSimulation());
    }
}
