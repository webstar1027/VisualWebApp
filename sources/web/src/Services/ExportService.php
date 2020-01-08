<?php

declare(strict_types=1);

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Request;
use TheCodingMachine\TDBM\TDBMException;
use function count;
use function explode;
use function intval;
use function Safe\tempnam;
use function sys_get_temp_dir;

class ExportService
{
    /** @var HypotheseService */
    private $hypotheseService;
    /** @var IndiceTauxService */
    private $indiceTauxService;
    /** @var ModeleDamortissementService */
    private $modeleDamortissementService;
    /** @var ResultatComptableService */
    private $resultatComptableService;
    /** @var MaintenanceService */
    private $maintenanceService;
    /** @var ProfilEvolutionLoyerService */
    private $profilEvolutionLoyerService;
    /** @var TypeEmpruntService */
    private $typeEmpruntService;
    /** @var RisqueLocatifService */
    private $risqueLocatifService;
    /** @var CgllsAncolsService */
    private $cgllsAncolsService;
    /** @var AutreChargeService */
    private $autreChargeService;
    /** @var PatrimoineService */
    private $patrimoineService;
    /** @var DemolitionService */
    private $demolitionService;
     /** @var LoyerService */
    private $loyerService;
     /** @var PortageTresorerieService */
    private $portageTresorerieService;
    /** @var ProduitAutreService */
    private $produitAutreService;
    /** @var AnnuiteService */
    private $annuiteService;
    /** @var TravauxImmobilisesService */
    private $travauxImmobilisesService;
    /** @var CessionService */
    private $cessionService;
     /** @var FondDeRoulementService */
    private $fondDeRoulementService;
    /** @var VacanceService */
    private $vacanceService;
    /** @var PatrimoineFoyersService */
    private $patrimoineFoyersService;
    /** @var CessionFoyerService */
    private $cessionFoyerService;
    /** @var PslaService */
    private $pslaService;

    public const SHEETLIST = [
        'hypothese',
        'indice_taux',
        'resultat_compatible',
        'modele_amortissement',
        'charge_maintenance',
        'profil_evolution_loyer',
        'type_Emprunt',
        'risques_locatif',
        'cgll_ancol',
        'autres_charge',
        'patrimoine',
        'nonidentifees',
        'identifees',
        'produit_loyer',
        'portage_tresorerie',
        'produit_autres',
        'charges_annuite',
        'travaux_immobilises_identifiees',
        'travaux_immobilises_nonidentifiees',
        'cession_identifees',
        'fondroulement',
        'renouvellement-composant',
        'vancance_identifees',
        'patrimoine_foyers',
        'cession_foyers',
        'demolition_foyers',
        'travaux_foyers',
        'foyers_frais_structures',
        'psla_identifiees',
    ];

    public function __construct(
        HypotheseService $hypotheseService,
        IndiceTauxService $indiceTauxService,
        ResultatComptableService $resultatComptableService,
        ModeleDamortissementService $modeleDamortissementService,
        MaintenanceService $maintenanceService,
        ProfilEvolutionLoyerService $profilEvolutionLoyerService,
        TypeEmpruntService $typeEmpruntService,
        RisqueLocatifService $risqueLocatifService,
        CgllsAncolsService $cgllsAncolsService,
        AutreChargeService $autreChargeService,
        PatrimoineService $patrimoineService,
        DemolitionService $demolitionService,
        LoyerService $loyerService,
        PortageTresorerieService $portageTresorerieService,
        ProduitAutreService $produitAutreService,
        AnnuiteService $annuiteService,
        TravauxImmobilisesService $travauxImmobilisesService,
        CessionService $cessionService,
        FondDeRoulementService $fondDeRoulementService,
        VacanceService $vacanceService,
        PatrimoineFoyersService $patrimoineFoyersService,
        CessionFoyerService $cessionFoyerService,
        PslaService $pslaService
    ) {
        $this->hypotheseService = $hypotheseService;
        $this->resultatComptableService = $resultatComptableService;
        $this->indiceTauxService = $indiceTauxService;
        $this->modeleDamortissementService = $modeleDamortissementService;
        $this->maintenanceService = $maintenanceService;
        $this->profilEvolutionLoyerService = $profilEvolutionLoyerService;
        $this->typeEmpruntService = $typeEmpruntService;
        $this->risqueLocatifService = $risqueLocatifService;
        $this->cgllsAncolsService = $cgllsAncolsService;
        $this->autreChargeService = $autreChargeService;
        $this->patrimoineService = $patrimoineService;
        $this->demolitionService = $demolitionService;
        $this->loyerService = $loyerService;
        $this->portageTresorerieService = $portageTresorerieService;
        $this->produitAutreService = $produitAutreService;
        $this->annuiteService = $annuiteService;
        $this->travauxImmobilisesService = $travauxImmobilisesService;
        $this->cessionService = $cessionService;
        $this->fondDeRoulementService = $fondDeRoulementService;
        $this->vacanceService = $vacanceService;
        $this->patrimoineFoyersService = $patrimoineFoyersService;
        $this->cessionFoyerService = $cessionFoyerService;
        $this->pslaService = $pslaService;
    }

    /**
     *  @param string[] $types
     *
     *  @return mixed[]
     */
    public function export(array $types, string $simulationId): array
    {
        $spreadsheet = new Spreadsheet();

        foreach ($types as $key => $value) {
            if ($key === 0) {
                $sheet = $spreadsheet->getActiveSheet();
            } else {
                $sheet = $spreadsheet->createSheet();
            }

            switch ($value) {
                case self::SHEETLIST[0]:
                    $sheet = $this->hypotheseService->exportHypothese($sheet, $simulationId);
                    break;
                case self::SHEETLIST[1]:
                    $sheet = $this->indiceTauxService->exportIndicesTaux($sheet, $simulationId);
                    $this->fillGreyColor($sheet, ['A3:O7', 'A9:O11']);
                    break;
                case self::SHEETLIST[2]:
                    $sheet = $this->resultatComptableService->exportResultatCompatible($sheet, $simulationId);
                    break;
                case self::SHEETLIST[3]:
                    $sheet = $this->modeleDamortissementService->exportModelesAmortissement($sheet, $simulationId);
                    break;
                case self::SHEETLIST[4]:
                    $sheet = $this->maintenanceService->exportChargesMaintenance($sheet, $simulationId);
                    $range = $this->maintenanceService->getRange();
                    $this->fillGreyColor($sheet, $range);
                    $this->collapseSheet($sheet, '14:57');
                    break;
                case self::SHEETLIST[5]:
                    $sheet = $this->profilEvolutionLoyerService->exportProfilsEvolutionLoyers($sheet, $simulationId);
                    $this->collapseSheet($sheet, '15:104');
                    break;
                case self::SHEETLIST[6]:
                    $sheet = $this->typeEmpruntService->exportTypesEmprunts($sheet, $simulationId);
                    break;
                case self::SHEETLIST[7]:
                    $sheet = $this->risqueLocatifService->exportRisquesLocatifs($sheet, $simulationId);
                    $this->collapseSheet($sheet, '12:51');
                    break;
                case self::SHEETLIST[8]:
                    $sheet = $this->cgllsAncolsService->exportCgllsAncols($sheet, $simulationId);
                    $this->fillGreyColor($sheet, ['A3:D3', 'A5:D11', 'A13:D13', 'A16:D17', 'A19:D19']);
                    $this->collapseSheet($sheet, '14:53');
                    break;
                case self::SHEETLIST[9]:
                    $sheet = $this->autreChargeService->exportAutresCharges($sheet, $simulationId);
                    $this->collapseSheet($sheet, '16:56');
                    break;
                case self::SHEETLIST[10]:
                    $sheet = $this->patrimoineService->exportPatrimoines($sheet, $simulationId);
                    break;
                case self::SHEETLIST[11]:
                    $sheet = $this->demolitionService->exportDemolition(1, $sheet, $simulationId);
                    $this->collapseSheet($sheet, '11:60');
                    $this->collapseSheet($sheet, '62:111');
                    $this->collapseSheet($sheet, '112:161');
                    $this->collapseSheet($sheet, '162:211');
                    break;
                case self::SHEETLIST[12]:
                    $sheet = $this->demolitionService->exportDemolition(0, $sheet, $simulationId);
                    $this->collapseSheet($sheet, '32:81');
                    $this->collapseSheet($sheet, '82:131');
                    break;
                case self::SHEETLIST[13]:
                    $sheet = $this->loyerService->exportProduitLoyer($sheet, $simulationId);
                    $this->collapseSheet($sheet, '13:53');
                    break;
                case self::SHEETLIST[14]:
                    $sheet = $this->portageTresorerieService->exportPortageTresorerie($sheet, $simulationId);
                    $this->fillGreyColor($sheet, ['A4:AY9', 'A4:Z9', 'A13:AY17', 'A13:Z17']);
                    break;
                case self::SHEETLIST[15]:
                    $sheet = $this->produitAutreService->exportProduitAutres($sheet, $simulationId);
                    $range = $this->produitAutreService->getRange();
                    $_range = ['B4:Z4', 'AA4:AY4', 'B9:Z9', 'AA9:AY9', 'AA5:AY5', 'AA10:AY10'];

                    if (count($range) > 0) {
                        foreach ($range as $item) {
                            $_range[] = $item;
                        }
                    }
                    $this->fillGreyColor($sheet, $_range);
                    $this->collapseSheet($sheet, '14:52');
                    break;
                case self::SHEETLIST[16]:
                    $sheet = $this->annuiteService->exportChargesAnnuite($sheet, $simulationId);
                    $this->collapseSheet($sheet, '14:54');
                    break;
                case self::SHEETLIST[17]:
                    $sheet = $this->travauxImmobilisesService->exporttravauxImmobilises($sheet, 1, $simulationId);
                    break;
                case self::SHEETLIST[18]:
                    $sheet = $this->travauxImmobilisesService->exporttravauxImmobilises($sheet, 2, $simulationId);
                    $this->collapseSheet($sheet, '12:61');
                    $this->collapseSheet($sheet, '62:111');
                    break;
                case self::SHEETLIST[19]:
                    $sheet = $this->cessionService->exportCession($sheet, 0, $simulationId);
                    $this->collapseSheet($sheet, '13:62');
                    $this->collapseSheet($sheet, '63:112');
                    $this->collapseSheet($sheet, '113:162');
                    $this->collapseSheet($sheet, '163:212');
                    $this->collapseSheet($sheet, '213:262');
                    $this->collapseSheet($sheet, '263:312');
                    $this->collapseSheet($sheet, '313:362');
                    break;
                case self::SHEETLIST[20]:
                    $sheet = $this->fondDeRoulementService->exportFondDeRoulement($sheet, $simulationId);
                    $this->collapseSheet($sheet, '12:52');
                    break;
                case self::SHEETLIST[21]:
                    $sheet = $this->travauxImmobilisesService->exporttravauxImmobilises($sheet, 0, $simulationId);
                    $this->collapseSheet($sheet, '6:55');
                    break;
                case self::SHEETLIST[22]:
                    $sheet = $this->vacanceService->exportVacance($sheet, $simulationId);
                    $this->collapseSheet($sheet, '6:55');
                    break;
                case self::SHEETLIST[23]:
                    $sheet = $this->patrimoineFoyersService->exportPatrimoineFoyers($sheet, $simulationId);
                    $this->collapseSheet($sheet, '9:58');
                    break;
                case self::SHEETLIST[24]:
                    $sheet = $this->cessionFoyerService->exportCessionFoyers($sheet, $simulationId);
                    $this->collapseSheet($sheet, '12:61');
                    $this->collapseSheet($sheet, '62:111');
                    $this->collapseSheet($sheet, '112:161');
                    $this->collapseSheet($sheet, '163:212');
                    $this->collapseSheet($sheet, '214:263');
                    $this->collapseSheet($sheet, '265:314');
                    break;
                case self::SHEETLIST[28]:
                    $sheet = $this->pslaService->exportPsla($sheet, 0, $simulationId);
                    $this->collapseSheet($sheet, '15:64');
                    $this->collapseSheet($sheet, '65:114');
                    $this->collapseSheet($sheet, '115:164');
                    break;
                default:
                    break;
            }
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'export.xlsx';

        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($temp_file);

        return [
            'file' => $temp_file,
            'name' => $fileName,
        ];
    }

    /**
     * @param string[] $types
     *
     * @throws TDBMException
     */
    public function import(array $types, Request $request, string $simulationId): string
    {
        $notification = '';

        foreach ($types as $key => $value) {
            switch ($value) {
                case self::SHEETLIST[2]:
                    $notification = $this->resultatComptableService->importResultatCompatible($request, $simulationId);
                    break;
                case self::SHEETLIST[3]:
                    $notification = $this->modeleDamortissementService->importModelesAmortissement($request, $simulationId);
                    break;
                case self::SHEETLIST[4]:
                    $notification = $this->maintenanceService->importChargesMaintenance($request, $simulationId);
                    break;
                case self::SHEETLIST[5]:
                    $notification = $this->profilEvolutionLoyerService->importProfilsEvolutionLoyers($request, $simulationId);
                    break;
                case self::SHEETLIST[6]:
                    $notification = $this->typeEmpruntService->importTypesEmprunts($request, $simulationId);
                    break;
                case self::SHEETLIST[9]:
                    $notification = $this->autreChargeService->importAutresCharges($request, $simulationId);
                    break;
                case self::SHEETLIST[10]:
                    $notification = $this->patrimoineService->importPatrimoines($request, $simulationId);
                    break;
                case self::SHEETLIST[11]:
                    $notification = $this->demolitionService->importDemolition(1, $request, $simulationId);
                    break;
                case self::SHEETLIST[12]:
                    $notification = $this->demolitionService->importDemolition(0, $request, $simulationId);
                    break;
                case self::SHEETLIST[13]:
                    $notification = $this->loyerService->importProduitLoyer($request, $simulationId);
                    break;
                case self::SHEETLIST[15]:
                    $notification = $this->produitAutreService->importProduitAutres($request, $simulationId);
                    break;
                case self::SHEETLIST[16]:
                    $notification = $this->annuiteService->importChargesAnnuite($request, $simulationId);
                    break;
                case self::SHEETLIST[17]:
                    $notification = $this->travauxImmobilisesService->importTravauxImmobilises($request, 1, $simulationId);
                    break;
                case self::SHEETLIST[18]:
                    $notification = $this->travauxImmobilisesService->importTravauxImmobilises($request, 2, $simulationId);
                    break;
                case self::SHEETLIST[19]:
                    $notification = $this->cessionService->importCession($request, 0, $simulationId);
                    break;
                case self::SHEETLIST[20]:
                    $notification = $this->fondDeRoulementService->importFondDeRoulement($request, $simulationId);
                    break;
                case self::SHEETLIST[21]:
                    $notification = $this->travauxImmobilisesService->importTravauxImmobilises($request, 0, $simulationId);
                    break;
                case self::SHEETLIST[22]:
                    $notification = $this->vacanceService->importVacance($request, $simulationId);
                    break;
                case self::SHEETLIST[23]:
                    $notification = $this->patrimoineFoyersService->importPatrimoineFoyers($request, $simulationId);
                    break;
                case self::SHEETLIST[24]:
                    $notification = $this->cessionFoyerService->importCessionFoyers($request, $simulationId);
                    break;
                case self::SHEETLIST[28]:
                    $notification = $this->pslaService->importPsla($request, 0, $simulationId);
                    break;
                default:
                    break;
            }
        }

        return $notification;
    }

    /**
     *  @param string[] $target
     */
    public function fillGreyColor(Worksheet $sheet, array $target): Worksheet
    {
        foreach ($target as $item) {
            $columns = explode(':', $item);
            $start = $columns[0];
            $end = $columns[1];

            foreach ($sheet->getRowIterator() as $row) {
                $rowIndex = $row->getRowIndex();
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(true);

                /** @var Cell $startCell */
                $startCell = $sheet->getCell($start);
                $startRow = $startCell->getRow();
                $startColumn = $startCell->getColumn();

                /** @var Cell $endCell */
                $endCell = $sheet->getCell($end);
                $endRow = $endCell->getRow();
                $endColumn = $endCell->getColumn();

                if ($rowIndex < $startRow || $rowIndex > $endRow) {
                    continue;
                }

                foreach ($cellIterator as $cell) {
                    $currentColumn = $cell->getColumn();

                    if ($currentColumn < $startColumn || $currentColumn > $endColumn) {
                        continue;
                    }

                    $value = $cell->getValue();

                    if (isset($value)) {
                        continue;
                    }

                    $cell->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('C0C0C0');
                }
            }
        }

        return $sheet;
    }

    public function collapseSheet(Worksheet $sheet, string $target): Worksheet
    {
        $columns = explode(':', $target);

        for ($i=intval($columns[0]); $i < intval($columns[1]); $i++) {
            $column = $this->maintenanceService->columnLetter($i);
            $sheet->getColumnDimension($column)->setOutlineLevel(2)->setVisible(false)->setCollapsed(true);
        }

        return $sheet;
    }
}
