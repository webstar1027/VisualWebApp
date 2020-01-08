<?php

declare(strict_types=1);

namespace App\Services;

use App\Dao\CgllAncolDao;
use App\Dao\CgllAncolParametreDao;
use App\Dao\CgllAncolPeriodiqueDao;
use App\Dao\SimulationDao;
use App\Model\CgllAncol;
use App\Model\CgllAncolParametre;
use App\Model\CgllAncolPeriodique;
use App\Model\Simulation;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\TDBM\AlterableResultIterator;
use TheCodingMachine\TDBM\TDBMException;
use function array_push;
use function intval;

class CgllsAncolsService
{
    /** @var CgllAncolDao */
    private $cgllsAncolsDao;

    /** @var CgllAncolPeriodiqueDao */
    private $cgllsAncolsPeriodiqueDao;

    /** @var CgllAncolParametreDao */
    private $cgllAncolParametreDao;

    /** @var SimulationDao */
    private $simulationDao;

    public function __construct(
        CgllAncolParametreDao $cgllAncolParametreDao,
        CgllAncolDao $cgllAncolDao,
        CgllAncolPeriodiqueDao $cgllAncolPeriodiqueDao,
        SimulationDao $simulationDao
    ) {
        $this->cgllsAncolsDao = $cgllAncolDao;
        $this->cgllAncolParametreDao = $cgllAncolParametreDao;
        $this->cgllsAncolsPeriodiqueDao = $cgllAncolPeriodiqueDao;
        $this->simulationDao = $simulationDao;
    }

    /**
     * @throws TDBMException
     */
    public function createCgllsAncols(Simulation $simulationID): void
    {
        $this->createCgllAncol($simulationID, CgllAncol::COTISATION_DE_BASE_CGLLS);
        $this->createCgllAncol($simulationID, CgllAncol::TAUX_APPLICABLE_AUX_PRODUITS_LOCATIFS_HORS_SLS);
        $this->createCgllAncol($simulationID, CgllAncol::TAUX_APPLICABLE_AU_SLS);
        $this->createCgllAncol($simulationID, CgllAncol::MONTANT_DE_LA_REDUCTION_POUR_CHAQUE_MISE_EN_SERVICE_DE_LOGEMENT_ET_FOYER);
        $this->createCgllAncol($simulationID, CgllAncol::LOCATAIRES_BENEFICIAIRES_DE_APL);
        $this->createCgllAncol($simulationID, CgllAncol::MONTANT_DE_LA_REDUCTION_PAR_BENEFICIAIRE_APL);
        $this->createCgllAncol($simulationID, CgllAncol::LOGEMENTS_ET_DE_FOYERS_EN_QPV);
        $this->createCgllAncol($simulationID, CgllAncol::REDUCTION_POUR_LES_LOGEMENTS_EN_QPV);
        $this->createCgllAncol($simulationID, CgllAncol::COTISATION_ADDITIONNELLE_CGLLS);
        $this->createCgllAncol($simulationID, CgllAncol::TAUX_DE_RLS_MOYEN_POUR_LE_CALCUL_DU_LISSAGE);
        $this->createCgllAncol($simulationID, CgllAncol::COEFFICIENT_DE_VARIATION_DE_LA_RLS);
        $this->createCgllAncol($simulationID, CgllAncol::TAUX_DE_COTISATION_ANCOLS);

        $this->createCgllsAncolsParametre($simulationID);
    }

    private function createCgllAncol(Simulation $simulation, string $type): void
    {
        $cgllsAncols = new CgllAncol($simulation, $type);
        $this->cgllsAncolsDao->save($cgllsAncols);
        $this->createCgllsAncolsPeriodique($cgllsAncols);
    }

    private function createCgllsAncolsPeriodique(CgllAncol $cgllAncol): void
    {
        for ($i = 1; $i <= CgllAncolPeriodique::NUMBER_OF_ITERATIONS; $i++) {
            $cgllsAncolsPeriodique = new CgllAncolPeriodique($cgllAncol, $i);
            $cgllsAncolsPeriodique->setValeur(null);
            $this->cgllsAncolsPeriodiqueDao->save($cgllsAncolsPeriodique);
        }
    }

    private function createCgllsAncolsParametre(Simulation $simulation): void
    {
        $cgllsParametre = new CgllAncolParametre($simulation);
        $cgllsParametre->setCalculAutomatique(null);
        $cgllsParametre->setLissageNet(0.0);
        $this->cgllAncolParametreDao->save($cgllsParametre);
    }

    public function exportCgllsAncols(Worksheet $sheet, string $simulationId): Worksheet
    {
        $simulation = $this->simulationDao->getById($simulationId);
        $anneeDeReference = $simulation->getAnneeDeReference();
        $cgllsAncolsParametre = $this->cgllAncolParametreDao->findOneBySimulation($simulationId);

        $writeData = [];
        $headers = [null, 'Calcul automatique à partir de', 'Tx d\'évolution'];

        for ($i = 0; $i < CgllAncolPeriodique::NUMBER_OF_ITERATIONS; $i++) {
            array_push($headers, intval($anneeDeReference) + $i);
        }

        array_push($writeData, $headers);
        $cgllsAncols = $this->cgllsAncols($simulationId);
        $calcul_automatique = $cgllsAncolsParametre->getCalculAutomatique();
        $lissage_net = $cgllsAncolsParametre->getLissageNet();

        foreach ($cgllsAncols as $cgllsAncol) {
            $row = [];
            $type = $cgllsAncol->getType();
            array_push($row, $this->getTextByType($type));
            array_push($row, $calcul_automatique);
            array_push($row, null);
            $cgllsAncolPeriodiques = $cgllsAncol->getCgllsAncolsPeriodique();

            foreach ($cgllsAncolPeriodiques as $cgllsAncolPeriodique) {
                array_push($row, $cgllsAncolPeriodique->getValeur());
            }
            array_push($writeData, $row);

            if ($type === CgllAncol::COTISATION_ADDITIONNELLE_CGLLS) {
                array_push($writeData, []);
                $row = ['Lissage net au 31/12/' . $anneeDeReference, $calcul_automatique, $lissage_net];
                array_push($writeData, $row);
            }

            if ($type !== CgllAncol::COTISATION_DE_BASE_CGLLS &&
                $type !== CgllAncol::REDUCTION_POUR_LES_LOGEMENTS_EN_QPV &&
                $type !== CgllAncol::COEFFICIENT_DE_VARIATION_DE_LA_RLS
            ) {
                continue;
            }

            array_push($writeData, []);
        }

        $sheet->setTitle('cglls_ancols');
        $sheet->setCellValue('A1', 'CGLLS et ANCOLS');
        $sheet->fromArray($writeData, null, 'A2', true);
        $sheet->getRowDimension(2)->setRowHeight(54);
        $sheet->getColumnDimension('A')->setwidth(80);
        $sheet->getColumnDimension('B')->setwidth(16);
        $sheet->getColumnDimension('C')->setwidth(16);
        $sheet->getStyle('B2')->getAlignment()->setWrapText(true);
        $sheet->getStyle('A1:A19')->getFont()->setBold(true);
        $sheet->getStyle('B2:BA2')->getFont()->setBold(true);
        $sheet->getStyle('A2:BA19')->getFont()->setSize(10);
        $sheet->getStyle('A2:BA19')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B2:BA2')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A3:BA3')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A5:BA11')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A13:BA13')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A15:c15')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A16:BA17')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A19:BA19')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        return $sheet;
    }

    public function getTextByType(string $type): string
    {
        $text = '';
        switch ($type) {
            case CgllAncol::COTISATION_DE_BASE_CGLLS:
                $text = 'Cotisation de Base CGLLS';
                break;
            case CgllAncol::TAUX_APPLICABLE_AUX_PRODUITS_LOCATIFS_HORS_SLS:
                $text = 'Taux applicable aux produits locatifs hors SLS';
                break;
            case CgllAncol::TAUX_APPLICABLE_AU_SLS:
                $text = 'Taux applicable à l\'assiette SLS';
                break;
            case CgllAncol::MONTANT_DE_LA_REDUCTION_POUR_CHAQUE_MISE_EN_SERVICE_DE_LOGEMENT_ET_FOYER:
                $text = 'Montant de la réduction pour chaque mise en service de logement et foyer';
                break;
            case CgllAncol::LOCATAIRES_BENEFICIAIRES_DE_APL:
                $text = '% locataires bénéficaires de l\'APL';
                break;
            case CgllAncol::MONTANT_DE_LA_REDUCTION_PAR_BENEFICIAIRE_APL:
                $text = 'Montant de la réduction par bénéficiaire de l\'APL';
                break;
            case CgllAncol::LOGEMENTS_ET_DE_FOYERS_EN_QPV:
                $text = '% de logements et de foyers en QPV';
                break;
            case CgllAncol::REDUCTION_POUR_LES_LOGEMENTS_EN_QPV:
                $text = 'Montant de la réduction pour chaque logement et foyer en QPV';
                break;
            case CgllAncol::COTISATION_ADDITIONNELLE_CGLLS:
                $text = 'Cotisation additionnelle CGLLS';
                break;
            case CgllAncol::TAUX_DE_RLS_MOYEN_POUR_LE_CALCUL_DU_LISSAGE:
                $text = 'Taux de RLS moyen pour le calcul du lissage';
                break;
            case CgllAncol::COEFFICIENT_DE_VARIATION_DE_LA_RLS:
                $text = 'Coefficient de variation de la RLS';
                break;
            case CgllAncol::TAUX_DE_COTISATION_ANCOLS:
                $text = 'Taux de cotisation ANCOLS';
                break;
            default:
                break;
        }

        return $text;
    }

    /**
     * @return CgllAncol[]|AlterableResultIterator
     *
     * @throws TDBMException
     *
     * @Query()
     */
    public function cgllsAncols(string $simulationID): AlterableResultIterator
    {
        return $this->simulationDao->getById($simulationID)->getCgllsAncols();
    }

    public function cloneCgllsAncols(Simulation $newSimulation, Simulation $oldSimulation): void
    {
        $objects = $this->cgllsAncolsDao->findBySimulation($oldSimulation);
        foreach ($objects as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $this->cgllsAncolsDao->save($newObject);
            foreach ($object->getCgllsAncolsPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setCgllsAncols($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->cgllsAncolsPeriodiqueDao->save($newPeriodique);
            }
        }
    }
}
