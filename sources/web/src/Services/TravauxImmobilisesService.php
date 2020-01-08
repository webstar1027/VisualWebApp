<?php

declare(strict_types=1);

namespace App\Services;

use App\Dao\SimulationDao;
use App\Dao\TravauxImmobiliseDao;
use App\Dao\TravauxImmobilisePeriodiqueDao;
use App\Dao\TypeEmpruntTravauxImmobiliseDao;
use App\Exceptions\HTTPException;
use App\Model\Factories\TravauxImmobiliseFactory;
use App\Model\Simulation;
use App\Model\TravauxImmobilise;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv as ReaderCsv;
use PhpOffice\PhpSpreadsheet\Reader\Ods as ReaderOds;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ReaderXlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\HttpFoundation\Request;
use TheCodingMachine\TDBM\ResultIterator;
use TheCodingMachine\TDBM\TDBMException;
use Throwable;
use function array_pop;
use function array_push;
use function array_values;
use function chr;
use function count;
use function end;
use function in_array;
use function intval;
use function Safe\json_encode;
use function strval;

class TravauxImmobilisesService
{
    /** @var SimulationDao */
    private $simulationDao;
    /** @var TravauxImmobiliseDao */
    private $travauxImmobiliseDao;
    /** @var TypeEmpruntTravauxImmobiliseDao */
    private $typeEmpruntTravauxImmobiliseDao;
    /** @var TravauxImmobilisePeriodiqueDao */
    private $periodiqueDao;
    /** @var TypeEmpruntService */
    private $typeEmpruntService;
    /** @var ModeleDamortissementService */
    private $modeleDamortissementService;
     /** @var ProfilEvolutionLoyerService */
    private $profilEvolutionLoyerService;
     /** @var TravauxImmobiliseFactory */
    private $factory;

    public function __construct(
        TravauxImmobiliseDao $travauxImmobiliseDao,
        TypeEmpruntTravauxImmobiliseDao $typeEmpruntTravauxImmobiliseDao,
        TravauxImmobilisePeriodiqueDao $periodiqueDao,
        TypeEmpruntService $typeEmpruntService,
        ModeleDamortissementService $modeleDamortissementService,
        ProfilEvolutionLoyerService $profilEvolutionLoyerService,
        TravauxImmobiliseFactory $factory,
        SimulationDao $simulationDao
    ) {
        $this->travauxImmobiliseDao = $travauxImmobiliseDao;
        $this->typeEmpruntTravauxImmobiliseDao = $typeEmpruntTravauxImmobiliseDao;
        $this->periodiqueDao = $periodiqueDao;
        $this->typeEmpruntService = $typeEmpruntService;
        $this->modeleDamortissementService = $modeleDamortissementService;
        $this->profilEvolutionLoyerService = $profilEvolutionLoyerService;
        $this->factory = $factory;
        $this->simulationDao = $simulationDao;
    }

    public function save(TravauxImmobilise $travauxImmobilise): void
    {
        try {
            $this->travauxImmobiliseDao->save($travauxImmobilise);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Ces travaux immobilisés existent déjà.', $e);
        }
    }

    /**
     * @throws TDBMException
     * @throws HTTPException
     */
    public function remove(string $travauxImmobiliseUUID): void
    {
        try {
            $travauxImmobilise = $this->travauxImmobiliseDao->getById($travauxImmobiliseUUID);
            $this->travauxImmobiliseDao->delete($travauxImmobilise, true);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Ces travaux immobilisés n\'existent pas.', $e);
        }
    }

    public function removeTypeDempruntTravauxImmobilise(string $typesEmpruntsUUID, string $travauxImmobiliseUUID): void
    {
        $typeEmpruntTravauxImmobilise = $this->typeEmpruntTravauxImmobiliseDao->findByTypeEmpruntAndTravauxImmobilise(
            $typesEmpruntsUUID,
            $travauxImmobiliseUUID
        );

        if ($typeEmpruntTravauxImmobilise === null) {
            throw HTTPException::badRequest('Ce type d\'emprunt travaux immobilisés existe déjà.');
        }

        $this->typeEmpruntTravauxImmobiliseDao->delete($typeEmpruntTravauxImmobilise);
    }

    /**
     * @return TravauxImmobilise[]|ResultIterator
     */
    public function findBySimulation(string $simulationId): ResultIterator
    {
        return $this->travauxImmobiliseDao->findBySimulationID($simulationId);
    }

    /**
     * @return TravauxImmobilise[]|ResultIterator
     */
    public function findBySimulationAndType(string $simulationId, int $type): ResultIterator
    {
        return $this->travauxImmobiliseDao->findBySimulationIDAndType($simulationId, $type);
    }

    public function exporttravauxImmobilises(Worksheet $sheet, int $type, string $simulationId): Worksheet
    {
        switch ($type) {
            case 0:
                $sheet = $this->writeRenouvellementComposant($sheet, $simulationId, $type);
                break;
            case 1:
                $sheet = $this->writeTravauxIdentifes($sheet, $simulationId, $type);
                break;
            case 2:
                $sheet = $this->writeTravauxNonIdentifes($sheet, $simulationId, $type);
                break;
        }

        return $sheet;
    }

    public function writeRenouvellementComposant(Worksheet $sheet, string $simulationId, int $type): Worksheet
    {
        $writeData = [];
        $headers = [
            'N°',
            'Nom catégorie',
            'Indexation à l\'ICC',
            'Convention ANRU',
            'Modèle d\'amortissement technique',
        ];

        $simulation = $this->simulationDao->getById($simulationId);
        $anneeDeReference = $simulation->getAnneeDeReference();

        for ($i = 0; $i < 50; $i++) {
            $headers[] = 'Montant' . (intval($anneeDeReference) + $i);
        }

        $headers[] = 'Année de première échéance';
        $headers[] = 'Quotité subventions d\'Etat en %';
        $headers[] = 'Quotité subvention ANRU en %';
        $headers[] = 'Quotité subvention EPCI / Commune en %';
        $headers[] = 'Quotité subvention département en %';
        $headers[] = 'Quotité subvention région en %';
        $headers[] = 'Quotité subvention collecteur en %';
        $headers[] = 'Quotité autres subventions en %';
        $headers[] = 'Quotité de fonds propres en %';
        $headers[] = 'Total Emprunt';
        $headers[] = 'Emprunt';
        $headers[] = 'Quotité emprunt';
        $writeData[] = $headers;

        $travauxImmobilises = $this->findBySimulationAndType($simulationId, $type);
        $typeEmpruntsTravauxImmobilisesNumber = 0;

        foreach ($travauxImmobilises as $travauxImmobilise) {
            $typeEmpruntTravauxImmobilises = $travauxImmobilise->getTypeEmpruntTravauxImmobilises();
            $typeEmpruntsTravauxImmobilisesNumber += count($typeEmpruntTravauxImmobilises);
            $row = [];

            if (count($typeEmpruntTravauxImmobilises) !== 0) {
                foreach ($typeEmpruntTravauxImmobilises as $key => $value) {
                    $row = [];
                    if ($key === 0) {
                        $row[] = $travauxImmobilise->getNGroupe();
                        $row[] = $travauxImmobilise->getNomCategorie();
                        $row[] = $travauxImmobilise->getIndexationIcc() === true ? 'Oui' : 'Non';
                        $row[] = $travauxImmobilise->getConventionAnru() === true? 'Oui' : 'Non';
                        $modeleDamortissement = $travauxImmobilise->getModeleDamortissement();
                        $row[] = isset($modeleDamortissement) ? $modeleDamortissement->getNom() : null;

                        $periodiques = $travauxImmobilise->getTravauxImmobilisesPeriodique();

                        foreach ($periodiques as $periodique) {
                            $row[] = $periodique->getMontant();
                        }

                        $row[] = $travauxImmobilise->getAnneePremiereEcheance();
                        $row[] = $travauxImmobilise->getSubventionsEtat();
                        $row[] = $travauxImmobilise->getSubventionsAnru();
                        $row[] = $travauxImmobilise->getSubventionsEpci();
                        $row[] = $travauxImmobilise->getSubventionsDepartement();
                        $row[] = $travauxImmobilise->getSubventionsRegion();
                        $row[] = $travauxImmobilise->getSubventionsCollecteur();
                        $row[] = $travauxImmobilise->getAutresSubventions();
                        $row[] = $travauxImmobilise->getFoundsPropres();
                        $row[] = 'SUM';
                        $row[] = $value->getTypesEmprunts()->getNumero();
                        $row[] = $value->getQuotiteEmprunt();
                    } else {
                        $row[] = $travauxImmobilise->getNGroupe();
                        $row[] = $travauxImmobilise->getNomCategorie();

                        for ($i = 0; $i < 63; $i++) {
                            $row[] = null;
                        }

                        $row[] = $value->getTypesEmprunts()->getNumero();
                        $row[] = $value->getQuotiteEmprunt();
                    }

                    $writeData[] = $row;
                }
            } else {
                $typeEmpruntsTravauxImmobilisesNumber++;

                $row[] = $travauxImmobilise->getNGroupe();
                $row[] = $travauxImmobilise->getNomCategorie();
                $row[] = $travauxImmobilise->getIndexationIcc() === true ? 'Oui' : 'Non';
                $row[] = $travauxImmobilise->getConventionAnru() === true? 'Oui' : 'Non';
                $modeleDamortissement = $travauxImmobilise->getModeleDamortissement();
                $row[] = isset($modeleDamortissement) ? $modeleDamortissement->getNom() : null;

                $periodiques = $travauxImmobilise->getTravauxImmobilisesPeriodique();

                foreach ($periodiques as $periodique) {
                    $row[] = $periodique->getMontant();
                }

                $row[] = $travauxImmobilise->getAnneePremiereEcheance();
                $row[] = $travauxImmobilise->getSubventionsEtat();
                $row[] = $travauxImmobilise->getSubventionsAnru();
                $row[] = $travauxImmobilise->getSubventionsEpci();
                $row[] = $travauxImmobilise->getSubventionsDepartement();
                $row[] = $travauxImmobilise->getSubventionsRegion();
                $row[] = $travauxImmobilise->getSubventionsCollecteur();
                $row[] = $travauxImmobilise->getAutresSubventions();
                $row[] = $travauxImmobilise->getFoundsPropres();
                $row[] = 'SUM';
                $row[] = null;
                $row[] = null;

                $writeData[] = $row;
            }
        }

        $sheet->setTitle('RC');
        $sheet->setCellValue('A1', 'Renouvellement de composants');
        $sheet->fromArray($writeData, null, 'A2', true);

        $sheet = $this->setBoldSheet([
            'A1',
            'A2:BO2',
        ], $sheet);

        $sheet->getStyle('A2:BO' . (count($writeData) + 1))->getFont()->setSize(10);
        $sheet->getStyle('A1')->getFont()->setSize(11);
        $sheet->getRowDimension(2)->setRowHeight(35);
        $sheet = $this->setBorderSheet(['A2:BO' . (count($writeData) + 1)], $sheet);
        $sheet->getStyle('A2:BO' . (count($writeData) + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        for ($i = 1; $i <= 67; $i++) {
            $column = $this->columnLetter($i);
            $sheet->getColumnDimension($column)->setwidth(25);
            $sheet->getStyle($column . '2')->getAlignment()->setWrapText(true);
        }

        for ($i = 3; $i <= count($writeData) + 1; $i++) {
            $value = $this->getCellValue($sheet, $i, 'BM');

            if ($value !== 'SUM') {
                continue;
            }

            $lastRow = 0;

            for ($j = $i + 1; $j <= count($writeData) + 1; $j++) {
                $nextValue = $this->getCellValue($sheet, $j, 'BM');

                if ($nextValue === 'SUM') {
                    $lastRow = $j - 1;
                    break;
                }

                if ($j !== count($writeData) + 1) {
                    continue;
                }

                $lastRow = count($writeData) + 1;
            }

            if ($lastRow === 0) {
                $sheet->setCellValue(
                    'BM' . $i,
                    '=BO' . $i
                );
            } else {
                $sheet->setCellValue(
                    'BM' . $i,
                    '=SUM(BO' . $i . ':BO' . $lastRow . ')'
                );
            }
        }

        return $sheet;
    }

    public function writeTravauxIdentifes(Worksheet $sheet, string $simulationId, int $type): Worksheet
    {
        $writeData = [];
        $headers = [
            'N° groupe',
            'N° sous-groupe',
            'Information',
            'Nom du groupe',
            'Loyer mensuel initial',
            'Profil n°',
            'N° tranche des travaux',
            'Nom de la tranche',
            'Convention ANRU',
            'Surface traitée',
            'Variation surface quittancée',
            'Nombre de logements',
            'Variation du nombre de logements',
            'Nombre de logement après travaux',
            'Année d\'agrément',
            'Date d\'ordre de service',
            'Date de fin de travaux',
            'Taux de variation du loyer',
            'Date application',
            'Indexation ICC',
            'Modèle d\'amortissement technique',
            'Prix de revient de l\'opération',
            'Fonds propres',
            'Subventions d\'Etat',
            'Subventions ANRU',
            'Subventions EPCI / Commune',
            'Subventions département',
            'Subventions Région',
            'Subventions collecteur',
            'Autres subventions',
            'Total emprunts',
            'Numéro d\'emprunt',
            'Date de première échéance',
            'Montant',
        ];
        $writeData[] = $headers;

        $travauxImmobilises = $this->findBySimulationAndType($simulationId, $type);
        $typeEmpruntsTravauxImmobilisesNumber = 0;

        foreach ($travauxImmobilises as $travauxImmobilise) {
            $typeEmpruntTravauxImmobilises = $travauxImmobilise->getTypeEmpruntTravauxImmobilises();
            $typeEmpruntsTravauxImmobilisesNumber += count($typeEmpruntTravauxImmobilises);
            $row = [];

            if (count($typeEmpruntTravauxImmobilises) !== 0) {
                foreach ($typeEmpruntTravauxImmobilises as $key => $value) {
                    $row = [];
                    if ($key === 0) {
                        $row[] = $travauxImmobilise->getNGroupe();
                        $row[] = $travauxImmobilise->getNSousGroupe();
                        $row[] = $travauxImmobilise->getInformation();
                        $row[] = $travauxImmobilise->getNomGroupe();
                        $row[] = $travauxImmobilise->getLoyerMensuelInitial();
                        $profilEvolutionLoyer = $travauxImmobilise->getProfilEvolutionLoyer();
                        $row[] = isset($profilEvolutionLoyer) ? $profilEvolutionLoyer->getNumero() : null;
                        $row[] = $travauxImmobilise->getNumeroTranche();
                        $row[] = $travauxImmobilise->getNomTranche();
                        $row[] = $travauxImmobilise->getConventionAnru() === true ? 'Oui' : 'Non';
                        $row[] = $travauxImmobilise->getSurfaceTraitee();
                        $row[] = $travauxImmobilise->getVariationSurfaceQuittance();
                        $row[] = $travauxImmobilise->getNombreLogement();
                        $row[] = $travauxImmobilise->getVariationNombreLogement();
                        $row[] = null;
                        $row[] = $travauxImmobilise->getAnneeAgreement();
                        $row[] = $travauxImmobilise->getDateOrdreService();
                        $row[] = $travauxImmobilise->getDateFinTravaux();
                        $row[] = $travauxImmobilise->getTauxVariationLoyer();
                        $row[] = $travauxImmobilise->getDateApplication();
                        $row[] = $travauxImmobilise->getIndexationIcc() === true ? 'Oui' : 'Non';
                        $modeleDamortissement = $travauxImmobilise->getModeleDamortissement();
                        $row[] = isset($modeleDamortissement) ? $modeleDamortissement->getNom() : null;
                        $row[] = $travauxImmobilise->getPrixRevient();
                        $row[] = $travauxImmobilise->getFoundsPropres();
                        $row[] = $travauxImmobilise->getSubventionsEtat();
                        $row[] = $travauxImmobilise->getSubventionsAnru();
                        $row[] = $travauxImmobilise->getSubventionsEpci();
                        $row[] = $travauxImmobilise->getSubventionsDepartement();
                        $row[] = $travauxImmobilise->getSubventionsRegion();
                        $row[] = $travauxImmobilise->getSubventionsCollecteur();
                        $row[] = $travauxImmobilise->getAutresSubventions();
                        $row[] = 'SUM';
                        $row[] = $value->getTypesEmprunts()->getNumero();
                        $row[] = $value->getDatePremiere();
                        $row[] = $value->getMontant();
                    } else {
                        $row[] = $travauxImmobilise->getNGroupe();
                        $row[] = null;
                        $row[] = null;
                        $row[] = $travauxImmobilise->getNomGroupe();

                        for ($i = 0; $i < 26; $i++) {
                            $row[] = null;
                        }

                        $row[] = $value->getTypesEmprunts()->getNumero();
                        $row[] = $value->getDatePremiere();
                        $row[] = $value->getMontant();
                    }
                    $writeData[] = $row;
                }
            } else {
                $typeEmpruntsTravauxImmobilisesNumber++;
                $row[] = $travauxImmobilise->getNGroupe();
                $row[] = $travauxImmobilise->getNSousGroupe();
                $row[] = $travauxImmobilise->getInformation();
                $row[] = $travauxImmobilise->getNomGroupe();
                $row[] = $travauxImmobilise->getLoyerMensuelInitial();
                $profilEvolutionLoyer = $travauxImmobilise->getProfilEvolutionLoyer();
                $row[] = isset($profilEvolutionLoyer) ? $profilEvolutionLoyer->getNumero() : null;
                $row[] = $travauxImmobilise->getNumeroTranche();
                $row[] = $travauxImmobilise->getNomTranche();
                $row[] = $travauxImmobilise->getConventionAnru() === true ? 'Oui' : 'Non';
                $row[] = $travauxImmobilise->getSurfaceTraitee();
                $row[] = $travauxImmobilise->getVariationSurfaceQuittance();
                $row[] = $travauxImmobilise->getNombreLogement();
                $row[] = $travauxImmobilise->getVariationNombreLogement();
                $row[] = null;
                $row[] = $travauxImmobilise->getAnneeAgreement();
                $row[] = $travauxImmobilise->getDateOrdreService();
                $row[] = $travauxImmobilise->getDateFinTravaux();
                $row[] = $travauxImmobilise->getTauxVariationLoyer();
                $row[] = $travauxImmobilise->getDateApplication();
                $row[] = $travauxImmobilise->getIndexationIcc() === true ? 'Oui' : 'Non';
                $modeleDamortissement = $travauxImmobilise->getModeleDamortissement();
                $row[] = isset($modeleDamortissement) ? $modeleDamortissement->getNom() : null;
                $row[] = $travauxImmobilise->getPrixRevient();
                $row[] = $travauxImmobilise->getFoundsPropres();
                $row[] = $travauxImmobilise->getSubventionsEtat();
                $row[] = $travauxImmobilise->getSubventionsAnru();
                $row[] = $travauxImmobilise->getSubventionsEpci();
                $row[] = $travauxImmobilise->getSubventionsDepartement();
                $row[] = $travauxImmobilise->getSubventionsRegion();
                $row[] = $travauxImmobilise->getSubventionsCollecteur();
                $row[] = $travauxImmobilise->getAutresSubventions();
                $row[] = 'SUM';
                $row[] = null;
                $row[] = null;
                $row[] = null;
                $writeData[] = $row;
            }
        }

        $totalRow = ['Total'];

        for ($i = 1; $i <= 34; $i++) {
            $totalRow[] = null;
        }

        $writeData[] = $totalRow;

        $sheet->setTitle('TII');
        $sheet->setCellValue('A1', 'Travaux immobilisés identifiés');
        $sheet->fromArray($writeData, null, 'A2', true);

        $sheet = $this->setBoldSheet([
            'A1',
            'A2:AH2',
        ], $sheet);

        $sheet->getStyle('A2:AH' . (count($writeData) + 1))->getFont()->setSize(10);
        $sheet->getStyle('A1')->getFont()->setSize(11);
        $sheet->getRowDimension(2)->setRowHeight(35);
        $sheet = $this->setBorderSheet(['A2:AH' . (count($writeData) + 1)], $sheet);
        $sheet->getStyle('A2:AH' . (count($writeData) + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sumColumns = [10, 11, 12, 13, 14, 22, 23, 24, 25, 26, 29, 30, 31, 34];
        for ($i = 3; $i < count($writeData) + 1; $i++) {
            $sheet->setCellValue(
                'N' . $i,
                '=SUM(L' . $i . ':M' . $i . ')'
            );
        }

        for ($i = 1; $i <= 34; $i++) {
            $column = $this->columnLetter($i);
            $sheet->getColumnDimension($column)->setwidth(25);
            $sheet->getStyle($column . '2')->getAlignment()->setWrapText(true);
            if (! in_array($i, $sumColumns)) {
                continue;
            }

            $sheet->setCellValue(
                $column . (count($writeData) + 1),
                '=SUM(' . $column . '3:' . $column . count($writeData) . ')'
            );
        }

        for ($i = 3; $i <= count($writeData); $i++) {
            $value = $this->getCellValue($sheet, $i, 'AE');

            if ($value !== 'SUM') {
                continue;
            }

            $lastRow = 0;

            for ($j = $i + 1; $j <= count($writeData); $j++) {
                $nextValue = $this->getCellValue($sheet, $j, 'AE');

                if ($nextValue === 'SUM') {
                    $lastRow = $j - 1;
                    break;
                }

                if ($j !== count($writeData)) {
                    continue;
                }

                $lastRow = count($writeData);
            }

            if ($lastRow === 0) {
                $sheet->setCellValue(
                    'AE' . $i,
                    '=AH' . $i
                );
            } else {
                $sheet->setCellValue(
                    'AE' . $i,
                    '=SUM(AH' . $i . ':AH' . $lastRow . ')'
                );
            }
        }

        return $sheet;
    }

    public function writeTravauxNonIdentifes(Worksheet $sheet, string $simulationId, int $type): Worksheet
    {
        $writeData = [];
        $headers = [
            'N°',
            'Nom catégorie',
            'Conventionné',
            'Convention ANRU',
            'Surface moyennem² / logt',
            'Loyer mensuel € / m² / logt',
            'Evolution du loyer après travaux',
            'Année d\'application taux de variation',
            'Indexation à l\'ICC',
            'Modèle d\'amortissement technique',
            'Montant des travaux en K€ / logt',
            'Durée de chantier en année',
        ];

        $simulation = $this->simulationDao->getById($simulationId);
        $anneeDeReference = $simulation->getAnneeDeReference();

        for ($i = 0; $i < 50; $i++) {
            $headers[] = 'Nombre d\'agrément' . (intval($anneeDeReference) + $i);
        }

        for ($i = 0; $i < 50; $i++) {
            $headers[] = 'Logts MEC' . (intval($anneeDeReference) + $i);
        }

        $headers[] = 'Quotité subventions d\'Etat en %';
        $headers[] = 'Quotité subvention ANRU en %';
        $headers[] = 'Quotité subvention EPCI / Commune en %';
        $headers[] = 'Quotité subvention département en %';
        $headers[] = 'Quotité subvention région en %';
        $headers[] = 'Quotité subvention collecteur en %';
        $headers[] = 'Quotité autres subventions en %';
        $headers[] = 'Quotité de fonds propres en %';
        $headers[] = 'Total Emprunt';
        $headers[] = 'Emprunt';
        $headers[] = 'Quotité emprunt';
        $writeData[] = $headers;

        $travauxImmobilises = $this->findBySimulationAndType($simulationId, $type);
        $typeEmpruntsTravauxImmobilisesNumber = 0;

        foreach ($travauxImmobilises as $travauxImmobilise) {
            $typeEmpruntTravauxImmobilises = $travauxImmobilise->getTypeEmpruntTravauxImmobilises();
            $typeEmpruntsTravauxImmobilisesNumber += count($typeEmpruntTravauxImmobilises);
            $row = [];

            if (count($typeEmpruntTravauxImmobilises) !== 0) {
                foreach ($typeEmpruntTravauxImmobilises as $key => $value) {
                    $row = [];
                    if ($key === 0) {
                        $row[] = $travauxImmobilise->getNGroupe();
                        $row[] = $travauxImmobilise->getNomCategorie();
                        $row[] = $travauxImmobilise->getLogementConventionnes() === true? 'Oui' : 'Non';
                        $row[] = $travauxImmobilise->getConventionAnru() === true? 'Oui' : 'Non';
                        $row[] = $travauxImmobilise->getSurfaceMoyenne();
                        $row[] = $travauxImmobilise->getLoyerMensuelMoyen();
                        $row[] = $travauxImmobilise->getVariationLoyer();
                        $row[] = $travauxImmobilise->getAnneeApplication() === 1 ? 'Livraison' : 'Livraison +1';
                        $row[] = $travauxImmobilise->getIndexationIcc() === true ? 'Oui' : 'Non';
                        $modeleDamortissement = $travauxImmobilise->getModeleDamortissement();
                        $row[] = isset($modeleDamortissement) ? $modeleDamortissement->getNom() : null;
                        $row[] = $travauxImmobilise->getMontantTravaux();
                        $row[] = $travauxImmobilise->getDureeChantier();

                        $periodiques = $travauxImmobilise->getTravauxImmobilisesPeriodique();

                        foreach ($periodiques as $periodique) {
                            $row[] = $periodique->getNombreAgrement();
                        }

                        foreach ($periodiques as $periodique) {
                            $row[] = $periodique->getLogement();
                        }

                        $row[] = $travauxImmobilise->getSubventionsEtat();
                        $row[] = $travauxImmobilise->getSubventionsAnru();
                        $row[] = $travauxImmobilise->getSubventionsEpci();
                        $row[] = $travauxImmobilise->getSubventionsDepartement();
                        $row[] = $travauxImmobilise->getSubventionsRegion();
                        $row[] = $travauxImmobilise->getSubventionsCollecteur();
                        $row[] = $travauxImmobilise->getAutresSubventions();
                        $row[] = $travauxImmobilise->getFoundsPropres();
                        $row[] = 'SUM';
                        $row[] = $value->getTypesEmprunts()->getNumero();
                        $row[] = $value->getQuotiteEmprunt();
                    } else {
                        $row[] = $travauxImmobilise->getNGroupe();
                        $row[] = $travauxImmobilise->getNomCategorie();

                        for ($i = 0; $i < 119; $i++) {
                            $row[] = null;
                        }

                        $row[] = $value->getTypesEmprunts()->getNumero();
                        $row[] = $value->getQuotiteEmprunt();
                    }

                    $writeData[] = $row;
                }
            } else {
                $typeEmpruntsTravauxImmobilisesNumber++;

                $row[] = $travauxImmobilise->getNGroupe();
                $row[] = $travauxImmobilise->getNomCategorie();
                $row[] = $travauxImmobilise->getLogementConventionnes() === true? 'Oui' : 'Non';
                $row[] = $travauxImmobilise->getConventionAnru() === true? 'Oui' : 'Non';
                $row[] = $travauxImmobilise->getSurfaceMoyenne();
                $row[] = $travauxImmobilise->getLoyerMensuelMoyen();
                $row[] = $travauxImmobilise->getVariationLoyer();
                $row[] = $travauxImmobilise->getAnneeApplication() === 1 ? 'Livraison' : 'Livraison +1';
                $row[] = $travauxImmobilise->getIndexationIcc() === true ? 'Oui' : 'Non';
                $modeleDamortissement = $travauxImmobilise->getModeleDamortissement();
                $row[] = isset($modeleDamortissement) ? $modeleDamortissement->getNom() : null;
                $row[] = $travauxImmobilise->getMontantTravaux();
                $row[] = $travauxImmobilise->getDureeChantier();

                $periodiques = $travauxImmobilise->getTravauxImmobilisesPeriodique();

                foreach ($periodiques as $periodique) {
                    $row[] = $periodique->getNombreAgrement();
                }

                foreach ($periodiques as $periodique) {
                    $row[] = $periodique->getLogement();
                }

                $row[] = $travauxImmobilise->getSubventionsEtat();
                $row[] = $travauxImmobilise->getSubventionsAnru();
                $row[] = $travauxImmobilise->getSubventionsEpci();
                $row[] = $travauxImmobilise->getSubventionsDepartement();
                $row[] = $travauxImmobilise->getSubventionsRegion();
                $row[] = $travauxImmobilise->getSubventionsCollecteur();
                $row[] = $travauxImmobilise->getAutresSubventions();
                $row[] = $travauxImmobilise->getFoundsPropres();
                $row[] = null;
                $row[] = null;
                $row[] = null;
                $writeData[] = $row;
            }
        }

        $sheet->setTitle('TINI');
        $sheet->setCellValue('A1', 'Travaux immobilisés non identifiés');
        $sheet->fromArray($writeData, null, 'A2', true);

        $sheet = $this->setBoldSheet([
            'A1',
            'A2:DS2',
        ], $sheet);

        $sheet->getStyle('A2:DS' . (count($writeData) + 1))->getFont()->setSize(10);
        $sheet->getStyle('A1')->getFont()->setSize(11);
        $sheet->getRowDimension(2)->setRowHeight(35);
        $sheet = $this->setBorderSheet(['A2:DS' . (count($writeData) + 1)], $sheet);
        $sheet->getStyle('A2:DS' . (count($writeData) + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        for ($i = 1; $i <= 123; $i++) {
            $column = $this->columnLetter($i);
            $sheet->getColumnDimension($column)->setwidth(25);
            $sheet->getStyle($column . '2')->getAlignment()->setWrapText(true);
        }

        for ($i = 3; $i <= count($writeData) + 1; $i++) {
            $value = $this->getCellValue($sheet, $i, 'DQ');

            if ($value !== 'SUM') {
                continue;
            }

            $lastRow = 0;

            for ($j = $i + 1; $j <= count($writeData) + 1; $j++) {
                $nextValue = $this->getCellValue($sheet, $j, 'DQ');

                if ($nextValue === 'SUM') {
                    $lastRow = $j - 1;
                    break;
                }

                if ($j !== count($writeData) + 1) {
                    continue;
                }

                $lastRow = count($writeData) + 1;
            }

            if ($lastRow === 0) {
                $sheet->setCellValue(
                    'DQ' . $i,
                    '=DS' . $i
                );
            } else {
                $sheet->setCellValue(
                    'DQ' . $i,
                    '=SUM(DS' . $i . ':DS' . $lastRow . ')'
                );
            }
        }

        return $sheet;
    }

    public function getCellValue(Worksheet $sheet, int $i, string $column): ?string
    {
        /** @var Cell $cell */
        $cell = $sheet->getCell($column . $i);

        return $cell->getValue();
    }

    /**
     * @param string[] $ranges
     */
    public function setBoldSheet(array $ranges, Worksheet $sheet): Worksheet
    {
        foreach ($ranges as $range) {
            $sheet->getStyle($range)->getFont()->setBold(true);
        }

        return $sheet;
    }

     /**
      * @param string[] $ranges
      */
    public function setBorderSheet(array $ranges, Worksheet $sheet): Worksheet
    {
        foreach ($ranges as $range) {
            $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        return $sheet;
    }

    public function columnLetter(int $c): string
    {
        if ($c <= 0) {
            return '';
        }

        $letter = '';

        while ($c !== 0) {
            $p = ($c - 1) % 26;
            $c = intval(($c - $p) / 26);
            $letter = chr(65 + $p) . $letter;
        }

        return $letter;
    }

    public function importTravauxImmobilises(Request $request, int $type, string $simulationId): string
    {
        $notification = '';
        switch ($type) {
            case 0:
                $notification = $this->importRenouvellementComposant($request, $simulationId);
                break;
            case 1:
                $notification = $this->importIdentifes($request, $type, $simulationId);
                break;
            case 2:
                $notification = $this->importNonIdentifes($request, $simulationId);
                break;
        }

        return $notification;
    }

    public function importRenouvellementComposant(Request $request, string $simulationId): string
    {
        $file = $request->files->get('file');
        $extension = $file->getclientOriginalExtension();

        switch ($extension) {
            case 'ods':
                $reader = new ReaderOds();
                break;
            case 'xlsx':
                $reader = new ReaderXlsx();
                break;
            case 'csv':
                $reader = new ReaderCsv();
                break;
            default:
                throw HTTPException::badRequest('Extension invalide.');
        }

        if ($file) {
            $spreadsheet = IOFactory::load($file);
            $changedIds = [];
            $isRenouvellement = false;
            // Get data from imported excel data

            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $worksheetTitle = $worksheet->getTitle();

                if ($worksheetTitle !== 'RC') {
                    continue;
                }

                $isRenouvellement = true;
            }

            if ($isRenouvellement === false) {
                throw HTTPException::badRequest('Il n\'y a pas de feuille correcte, veuillez vérifier à nouveau.');
            }

            $data['renouvellement_composant'] = [
                'columnNames' => [],
                'columnValues' => [],
            ];

            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $worksheetTitle = $worksheet->getTitle();

                if ($worksheetTitle !== 'RC') {
                    continue;
                }

                foreach ($worksheet->getRowIterator() as $row) {
                    $rowIndex = $row->getRowIndex();

                    if ($rowIndex < 2) {
                        continue;
                    }

                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(true);

                    foreach ($cellIterator as $cell) {
                        if ($rowIndex === 2) {
                            $data['renouvellement_composant']['columnNames'][] = $cell->getCalculatedValue();
                        } else {
                            $data['renouvellement_composant']['columnValues'][$rowIndex][] = $cell->getCalculatedValue();
                        }
                    }
                }
            }

            $data['renouvellement_composant']['columnValues'] = array_values($data['renouvellement_composant']['columnValues']);
            $travauxImmobilises = $this->findBySimulationAndType($simulationId, 0);

            foreach ($travauxImmobilises as $travauxImmobilise) {
                $this->remove($travauxImmobilise->getId());
            }

            $saveData = [];
            $temp = '';

            foreach ($data['renouvellement_composant']['columnValues'] as $item) {
                if ($temp === $item[0]) {
                    continue;
                }

                array_push($saveData, $item);
                $temp = $item[0];
            }

            $typeEmpruntsNumeros = $this->getTypeEmpruntsData($data['renouvellement_composant']['columnValues'], 0, 'numero');

            if ($typeEmpruntsNumeros[0] === 'wrong numero') {
                throw HTTPException::badRequest('Le numéro répété est détecté. S\'il vous plaît vérifier le numéro');
            }

            $typeEmpruntsQuotites = $this->getTypeEmpruntsData($data['renouvellement_composant']['columnValues'], 0, 'quotite');

            foreach ($saveData as $key => $item) {
                $typeEmprunts = [];
                $montant = [];

                if (count($typeEmpruntsNumeros) !== 0) {
                    for ($i = 0; $i < count($typeEmpruntsNumeros[$key]); $i++) {
                        if (! isset($typeEmpruntsNumeros[$key][$i])) {
                            continue;
                        }

                        $typeEmpts = $this->typeEmpruntService->fetchByNumero(strval($typeEmpruntsNumeros[$key][$i]));

                        if (count($typeEmpts) === 0) {
                            throw HTTPException::badRequest('Il n\'y a pas de tels typesemprunts');
                        }

                        $typeEmprunt = [
                            'id' => $typeEmpts[0]->getId() ,
                            'quotiteEmprunt' => $typeEmpruntsQuotites[$key][$i],
                        ];

                        array_push($typeEmprunts, json_encode($typeEmprunt));
                    }
                }

                $modeleDamortissementId = null;

                $valueArray = ['Oui', 'Non'];

                if (! in_array($item[2], $valueArray) || ! in_array($item[3], $valueArray)) {
                    throw HTTPException::badRequest('La valeur doit etre Non ou Oui');
                }

                if (isset($item[4])) {
                    $modeleDamortissement = $this->modeleDamortissementService->fetchBySimulationNom($simulationId, strval($item[4]));

                    if (count($modeleDamortissement) === 0) {
                        throw HTTPException::badRequest('Mauvais modèle Damortissment Nom - ' . $item[4]);
                    }

                    $modeleDamortissementId = $modeleDamortissement[0]->getId();
                }

                for ($i = 5; $i < 55; $i++) {
                    $montant[] = $item[$i];
                }

                $oldTravauxImmobilise = $this->travauxImmobiliseDao->findOneByNGroupe($simulationId, intval($item[0]), 0);

                try {
                    if (count($oldTravauxImmobilise) > 0) {
                        $oldIdentifee = $this->factory->createTravauxImmobilise(
                            $oldTravauxImmobilise[0]->getId(),
                            $simulationId,
                            strval($item[0]),
                            strval($item[1]),
                            $item[3] === 'Oui',
                            $item[2] === 'Oui',
                            $modeleDamortissementId,
                            strval($item[55]),
                            0,
                            $item[63],
                            $item[57],
                            $item[56],
                            $item[58],
                            $item[59],
                            $item[60],
                            $item[61],
                            $item[62],
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            count($typeEmprunts) === 0 ? null : $typeEmprunts,
                            json_encode(['montant' => $montant])
                        );
                        $this->save($oldIdentifee);
                        $changedIds[] = $oldTravauxImmobilise[0]->getId();
                    } else {
                        $newTravauxImmobilise = $this->factory->createTravauxImmobilise(
                            null,
                            $simulationId,
                            strval($item[0]),
                            strval($item[1]),
                            $item[3] === 'Oui',
                            $item[2] === 'Oui',
                            $modeleDamortissementId,
                            strval($item[55]),
                            0,
                            $item[63],
                            $item[57],
                            $item[56],
                            $item[58],
                            $item[59],
                            $item[60],
                            $item[61],
                            $item[62],
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            count($typeEmprunts) === 0 ? null : $typeEmprunts,
                            json_encode(['montant' => $montant])
                        );
                        $this->save($newTravauxImmobilise);
                        $changedIds[] = $newTravauxImmobilise->getId();
                    }
                } catch (Throwable $e) {
                    throw HTTPException::badRequest('Impossible d\'importer des données', $e);
                }
            }

            $allTravauxImmobilises = $this->findBySimulationAndType($simulationId, TravauxImmobilise::TYPE_RENOUVELLEMENT);

            foreach ($allTravauxImmobilises as $travauxImmobilise) {
                if (in_array($travauxImmobilise->getId(), $changedIds)) {
                    continue;
                }

                $this->remove($travauxImmobilise->getId());
            }
        }

        return 'Renouvellement de composants importé';
    }

    public function importIdentifes(Request $request, int $type, string $simulationId): string
    {
        $file = $request->files->get('file');
        $extension = $file->getclientOriginalExtension();

        switch ($extension) {
            case 'ods':
                $reader = new ReaderOds();
                break;
            case 'xlsx':
                $reader = new ReaderXlsx();
                break;
            case 'csv':
                $reader = new ReaderCsv();
                break;
            default:
                throw HTTPException::badRequest('Extension invalide.');
        }

        if ($file) {
            $spreadsheet = IOFactory::load($file);
            $changedIds = [];
            $isTravauxIdentifees = false;
            // Get data from imported excel data

            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $worksheetTitle = $worksheet->getTitle();

                if ($worksheetTitle !== 'TII') {
                    continue;
                }

                $isTravauxIdentifees = true;
            }

            if ($isTravauxIdentifees === false) {
                throw HTTPException::badRequest('Il n\'y a pas de feuille correcte, veuillez vérifier à nouveau.');
            }

            $data['travauxImmobilise_identifes'] = [
                'columnNames' => [],
                'columnValues' => [],
            ];

            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $worksheetTitle = $worksheet->getTitle();

                if ($worksheetTitle !== 'TII') {
                    continue;
                }

                foreach ($worksheet->getRowIterator() as $row) {
                    $rowIndex = $row->getRowIndex();

                    if ($rowIndex < 2) {
                        continue;
                    }

                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(true);

                    foreach ($cellIterator as $cell) {
                        if ($rowIndex === 2) {
                            $data['travauxImmobilise_identifes']['columnNames'][] = $cell->getCalculatedValue();
                        } else {
                            $data['travauxImmobilise_identifes']['columnValues'][$rowIndex][] = $cell->getCalculatedValue();
                        }
                    }
                }
            }

            $data['travauxImmobilise_identifes']['columnValues'] = array_values($data['travauxImmobilise_identifes']['columnValues']);
            array_pop($data['travauxImmobilise_identifes']['columnValues']);

            $saveData = [];
            $temp = '';

            foreach ($data['travauxImmobilise_identifes']['columnValues'] as $item) {
                if ($temp === $item[0]) {
                    continue;
                }

                array_push($saveData, $item);
                $temp = $item[0];
            }

            $typeEmpruntsNumeros = $this->getTypeEmpruntsData($data['travauxImmobilise_identifes']['columnValues'], 1, 'numero');

            if ($typeEmpruntsNumeros[0] === 'wrong numero') {
                throw HTTPException::badRequest('Le numéro répété est détecté. S\'il vous plaît vérifier le numéro');
            }

            $typeEmpruntsMontantEmprunts = $this->getTypeEmpruntsData($data['travauxImmobilise_identifes']['columnValues'], 1, 'montant');
            $typeEmpruntsdatePremieres = $this->getTypeEmpruntsData($data['travauxImmobilise_identifes']['columnValues'], 1, 'datePremiere');

            foreach ($saveData as $key => $item) {
                $typeEmprunts = [];
                if (count($typeEmpruntsNumeros) !== 0) {
                    for ($i = 0; $i < count($typeEmpruntsNumeros[$key]); $i++) {
                        if (! isset($typeEmpruntsNumeros[$key][$i])) {
                            continue;
                        }

                        $typeEmpts = $this->typeEmpruntService->fetchByNumero(strval($typeEmpruntsNumeros[$key][$i]));

                        if (count($typeEmpts) === 0) {
                            throw HTTPException::badRequest('Il n\'y a pas de tels typesemprunts');
                        }

                        $typeEmprunt = [
                            'id' => $typeEmpts[0]->getId() ,
                            'montant' => $typeEmpruntsMontantEmprunts[$key][$i],
                            'datePremiere' => $typeEmpruntsdatePremieres[$key][$i],
                        ];

                        array_push($typeEmprunts, json_encode($typeEmprunt));
                    }
                }

                $modeleDamortissementId = null;

                $valueArray = ['Oui', 'Non'];

                if (! in_array($item[8], $valueArray)) {
                    throw HTTPException::badRequest('La valeur doit etre Non ou Oui');
                }

                if (isset($item[20])) {
                    $modeleDamortissement = $this->modeleDamortissementService->fetchBySimulationNom($simulationId, strval($item[20]));

                    if (count($modeleDamortissement) === 0) {
                        throw HTTPException::badRequest('Mauvais modèle Damortissment Nom - "' . $item[20] . '"');
                    }

                    $modeleDamortissementId = $modeleDamortissement[0]->getId();
                }

                $profilEvolutionLoyerId = null;

                if (isset($item[5])) {
                    $profleEvolutionLoyers = $this->profilEvolutionLoyerService->fetchBySimulationIdAndNumero($simulationId, strval($item[5]));

                    if (count($profleEvolutionLoyers) === 0) {
                        throw HTTPException::badRequest('Il n’existe pas une telle évolution de profil loyer numero - "' . $item[5] . '"');
                    }

                    $profilEvolutionLoyerId = $profleEvolutionLoyers[0]->getId();
                }

                $oldTravauxImmobilise = $this->travauxImmobiliseDao->findOneByNGroupe($simulationId, intval($item[0]), 1);

                try {
                    if (count($oldTravauxImmobilise) > 0) {
                        $oldIdentifee = $this->factory->createTravauxImmobilise(
                            $oldTravauxImmobilise[0]->getId(),
                            $simulationId,
                            strval($item[0]),
                            null,
                            $item[8] === 'Oui',
                            $item[19] === 'Oui',
                            $modeleDamortissementId,
                            null,
                            1,
                            $item[22],
                            $item[24],
                            $item[23],
                            $item[25],
                            $item[26],
                            $item[27],
                            $item[28],
                            $item[29],
                            intval($item[1]),
                            $item[3],
                            $item[2],
                            $profilEvolutionLoyerId,
                            $item[4],
                            intval($item[6]),
                            strval($item[7]),
                            $item[9],
                            $item[10],
                            $item[11],
                            $item[12],
                            $item[14],
                            $item[15],
                            $item[16],
                            $item[17],
                            $item[18],
                            $item[21],
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            count($typeEmprunts) === 0 ? null : $typeEmprunts,
                            null
                        );
                        $this->save($oldIdentifee);
                        $changedIds[] = $oldTravauxImmobilise[0]->getId();
                    } else {
                        $newTravauxImmobilise = $this->factory->createTravauxImmobilise(
                            null,
                            $simulationId,
                            strval($item[0]),
                            null,
                            $item[8] === 'Oui',
                            $item[19] === 'Oui',
                            $modeleDamortissementId,
                            null,
                            1,
                            $item[22],
                            $item[24],
                            $item[23],
                            $item[25],
                            $item[26],
                            $item[27],
                            $item[28],
                            $item[29],
                            intval($item[1]),
                            $item[3],
                            $item[2],
                            $profilEvolutionLoyerId,
                            $item[4],
                            intval($item[6]),
                            strval($item[7]),
                            $item[9],
                            $item[10],
                            $item[11],
                            $item[12],
                            $item[14],
                            $item[15],
                            $item[16],
                            $item[17],
                            $item[18],
                            $item[21],
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            count($typeEmprunts) === 0 ? null : $typeEmprunts,
                            null
                        );
                        $this->save($newTravauxImmobilise);
                        $changedIds[] = $newTravauxImmobilise->getId();
                    }
                } catch (Throwable $e) {
                    throw HTTPException::badRequest('Impossible d\'importer des données', $e);
                }
            }

            $allTravauxImmobilises = $this->findBySimulationAndType($simulationId, TravauxImmobilise::TYPE_IDENTIFIEE);

            foreach ($allTravauxImmobilises as $travauxImmobilise) {
                if (in_array($travauxImmobilise->getId(), $changedIds)) {
                    continue;
                }

                $this->remove($travauxImmobilise->getId());
            }
        }

        return 'Travaux immobilisés identifiées importé';
    }

    public function importNonIdentifes(Request $request, string $simulationId): string
    {
        $file = $request->files->get('file');
        $extension = $file->getclientOriginalExtension();

        switch ($extension) {
            case 'ods':
                $reader = new ReaderOds();
                break;
            case 'xlsx':
                $reader = new ReaderXlsx();
                break;
            case 'csv':
                $reader = new ReaderCsv();
                break;
            default:
                throw HTTPException::badRequest('Extension invalide.');
        }

        if ($file) {
            $spreadsheet = IOFactory::load($file);
            $changedIds = [];
            $isTravauxNonIdentifees = false;
            // Get data from imported excel data

            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $worksheetTitle = $worksheet->getTitle();

                if ($worksheetTitle !== 'TINI') {
                    continue;
                }

                $isTravauxNonIdentifees = true;
            }

            if ($isTravauxNonIdentifees === false) {
                throw HTTPException::badRequest('Il n\'y a pas de feuille correcte, veuillez vérifier à nouveau.');
            }

            $data['travauxImmobilise_nonidentifes'] = [
                'columnNames' => [],
                'columnValues' => [],
            ];

            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $worksheetTitle = $worksheet->getTitle();

                if ($worksheetTitle !== 'TINI') {
                    continue;
                }

                foreach ($worksheet->getRowIterator() as $row) {
                    $rowIndex = $row->getRowIndex();

                    if ($rowIndex < 2) {
                        continue;
                    }

                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(true);

                    foreach ($cellIterator as $cell) {
                        if ($rowIndex === 2) {
                            $data['travauxImmobilise_nonidentifes']['columnNames'][] = $cell->getCalculatedValue();
                        } else {
                            $data['travauxImmobilise_nonidentifes']['columnValues'][$rowIndex][] = $cell->getCalculatedValue();
                        }
                    }
                }
            }

            $data['travauxImmobilise_nonidentifes']['columnValues'] = array_values($data['travauxImmobilise_nonidentifes']['columnValues']);

            $saveData = [];
            $temp = '';

            foreach ($data['travauxImmobilise_nonidentifes']['columnValues'] as $item) {
                if ($temp === $item[0]) {
                    continue;
                }

                array_push($saveData, $item);
                $temp = $item[0];
            }

            $typeEmpruntsNumeros = $this->getTypeEmpruntsData($data['travauxImmobilise_nonidentifes']['columnValues'], 2, 'numero');

            if ($typeEmpruntsNumeros[0] === 'wrong numero') {
                throw HTTPException::badRequest('Le numéro répété est détecté. S\'il vous plaît vérifier le numéro');
            }

            $typeEmpruntsQuotites = $this->getTypeEmpruntsData($data['travauxImmobilise_nonidentifes']['columnValues'], 2, 'quotite');

            foreach ($saveData as $key => $item) {
                $typeEmprunts = [];
                $nombreAgrement = [];
                $logement = [];

                if (count($typeEmpruntsNumeros) !== 0) {
                    for ($i = 0; $i < count($typeEmpruntsNumeros[$key]); $i++) {
                        if (! isset($typeEmpruntsNumeros[$key][$i])) {
                            continue;
                        }

                        $typeEmpts = $this->typeEmpruntService->fetchByNumero(strval($typeEmpruntsNumeros[$key][$i]));

                        if (count($typeEmpts) === 0) {
                            throw HTTPException::badRequest('Il n\'y a pas de tels typesemprunts');
                        }

                        $typeEmprunt = [
                            'id' => $typeEmpts[0]->getId() ,
                            'quotiteEmprunt' => $typeEmpruntsQuotites[$key][$i],
                        ];

                        array_push($typeEmprunts, json_encode($typeEmprunt));
                    }
                }

                $modeleDamortissementId = null;

                $valueArray = ['Oui', 'Non'];
                $anneeApplicationsDefaults = ['Livraison', 'Livraison +1'];

                if (! in_array($item[2], $valueArray) || ! in_array($item[3], $valueArray)) {
                    throw HTTPException::badRequest('La valeur doit etre Non ou Oui');
                }

                if (! in_array($item[7], $anneeApplicationsDefaults)) {
                    throw HTTPException::badRequest('Annee application inconnue - ' . $item[7]);
                }

                if (isset($item[9])) {
                    $modeleDamortissement = $this->modeleDamortissementService->fetchBySimulationNom($simulationId, strval($item[9]));

                    if (count($modeleDamortissement) === 0) {
                        throw HTTPException::badRequest('Mauvais modèle Damortissment Nom - ' . $item[9]);
                    }

                    $modeleDamortissementId = $modeleDamortissement[0]->getId();
                }

                for ($i = 10; $i < 112; $i++) {
                    if ($i > 11 && $i < 62) {
                        $nombreAgrement[] = $item[$i];
                    }

                    if ($i <= 61 || $i >= 111) {
                        continue;
                    }

                    $logement[] = $item[$i];
                }

                $oldTravauxImmobilise = $this->travauxImmobiliseDao->findOneByNGroupe($simulationId, intval($item[0]), 2);

                try {
                    if (count($oldTravauxImmobilise) > 0) {
                        $oldNonIdentifee = $this->factory->createTravauxImmobilise(
                            $oldTravauxImmobilise[0]->getId(),
                            $simulationId,
                            strval($item[0]),
                            strval($item[1]),
                            $item[3] === 'Oui',
                            $item[8] === 'Oui',
                            $modeleDamortissementId,
                            null,
                            2,
                            $item[119],
                            $item[113],
                            $item[112],
                            $item[114],
                            $item[115],
                            $item[116],
                            $item[117],
                            $item[118],
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            $item[2] === 'Oui',
                            $item[4],
                            $item[5],
                            $item[6],
                            strval($item[7]) === 'Livraison' ? 1 : 2,
                            intval($item[11]),
                            $item[10],
                            count($typeEmprunts) === 0 ? null : $typeEmprunts,
                            json_encode([
                                'nombre_agrement' => $nombreAgrement,
                                'logement' => $logement,
                            ])
                        );
                        $this->save($oldNonIdentifee);
                        $changedIds[] = $oldTravauxImmobilise[0]->getId();
                    } else {
                        $newTravauxImmobilise = $this->factory->createTravauxImmobilise(
                            null,
                            $simulationId,
                            strval($item[0]),
                            strval($item[1]),
                            $item[3] === 'Oui',
                            $item[8] === 'Oui',
                            $modeleDamortissementId,
                            null,
                            2,
                            $item[119],
                            $item[113],
                            $item[112],
                            $item[114],
                            $item[115],
                            $item[116],
                            $item[117],
                            $item[118],
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            null,
                            $item[2] === 'Oui',
                            $item[4],
                            $item[5],
                            $item[6],
                            strval($item[7]) === 'Livraison' ? 1 : 2,
                            intval($item[11]),
                            $item[10],
                            count($typeEmprunts) === 0 ? null : $typeEmprunts,
                            json_encode([
                                'nombre_agrement' => $nombreAgrement,
                                'logement' => $logement,
                            ])
                        );
                        $this->save($newTravauxImmobilise);
                        $changedIds[] = $newTravauxImmobilise->getId();
                    }
                } catch (Throwable $e) {
                    throw HTTPException::badRequest('Il y a une erreur à l\'importation.', $e);
                }
            }

            $allTravauxImmobilises = $this->findBySimulationAndType($simulationId, TravauxImmobilise::TYPE_NON_IDENTIFIEE);

            foreach ($allTravauxImmobilises as $travauxImmobilise) {
                if (in_array($travauxImmobilise->getId(), $changedIds)) {
                    continue;
                }

                $this->remove($travauxImmobilise->getId());
            }
        }

        return 'Travaux immobilisés Nonidentifiées importé';
    }

    /**
     *  @param mixed[] $data
     *
     *  @return mixed[]
     */
    public function getTypeEmpruntsData(array $data, int $type, string $target): array
    {
        $result = [];

        switch ($type) {
            case 0:
                $numero = $data[0][0];
                $item = [];

                foreach ($data as $key => $value) {
                    if ($target === 'numero') {
                        if ($value[0] === $numero) {
                            if (in_array($value[65], $item)) {
                                return ['wrong numero'];
                            }

                            array_push($item, $value[65]);
                        } else {
                            array_push($result, $item);
                            $numero = $value[0];
                            $item = [];
                            array_push($item, $value[65]);
                        }
                    }

                    if ($target === 'quotite') {
                        if ($value[0] === $numero) {
                            array_push($item, $value[66]);
                        } else {
                            array_push($result, $item);
                            $numero = $value[0];
                            $item = [];
                            array_push($item, $value[66]);
                        }
                    }

                    if (end($data) !== $value) {
                        continue;
                    }

                    array_push($result, $item);
                }
                break;
            case 1:
                $numero = $data[0][0];
                $item = [];

                foreach ($data as $key => $value) {
                    if ($target === 'numero') {
                        if ($value[0] === $numero) {
                            if (in_array($value[31], $item)) {
                                return ['wrong numero'];
                            }

                            array_push($item, $value[31]);
                        } else {
                            array_push($result, $item);
                            $numero = $value[0];
                            $item = [];
                            array_push($item, $value[31]);
                        }
                    }

                    if ($target === 'montant') {
                        if ($value[0] === $numero) {
                            array_push($item, $value[33]);
                        } else {
                            array_push($result, $item);
                            $numero = $value[0];
                            $item = [];
                            array_push($item, $value[33]);
                        }
                    }

                    if ($target === 'datePremiere') {
                        if ($value[0] === $numero) {
                            array_push($item, $value[32]);
                        } else {
                            array_push($result, $item);
                            $numero = $value[0];
                            $item = [];
                            array_push($item, $value[32]);
                        }
                    }

                    if (end($data) !== $value) {
                        continue;
                    }

                    array_push($result, $item);
                }
                break;
            case 2:
                $numero = $data[0][0];
                $item = [];

                foreach ($data as $key => $value) {
                    if ($target === 'numero') {
                        if ($value[0] === $numero) {
                            if (in_array($value[121], $item)) {
                                return ['wrong numero'];
                            }

                            array_push($item, $value[121]);
                        } else {
                            array_push($result, $item);
                            $numero = $value[0];
                            $item = [];
                            array_push($item, $value[121]);
                        }
                    }

                    if ($target === 'quotite') {
                        if ($value[0] === $numero) {
                            array_push($item, $value[122]);
                        } else {
                            array_push($result, $item);
                            $numero = $value[0];
                            $item = [];
                            array_push($item, $value[122]);
                        }
                    }

                    if (end($data) !== $value) {
                        continue;
                    }

                    array_push($result, $item);
                }
                break;
            default:
                break;
        }

        return $result;
    }

    public function cloneTravauxImmobilises(Simulation $newSimulation, Simulation $oldSimulation): void
    {
        $objects = $this->travauxImmobiliseDao->findBySimulation($oldSimulation);
        foreach ($objects as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getTravauxImmobilisesPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setTravauxImmobilises($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
    }

    public function fusionTravauxImmobilises(Simulation $newSimulation, Simulation $oldSimulation1, Simulation $oldSimulation2): void
    {
        $renouvellement1  = $this->travauxImmobiliseDao->findBySimulationIDAndType($oldSimulation1->getId(), 0);
        $identifees1 = $this->travauxImmobiliseDao->findBySimulationIDAndType($oldSimulation1->getId(), 1);
        $nonidentifees1 = $this->travauxImmobiliseDao->findBySimulationIDAndType($oldSimulation1->getId(), 2);

        $renouvellement2 = $this->travauxImmobiliseDao->findBySimulationIDAndType($oldSimulation2->getId(), 0);
        $identifees2 = $this->travauxImmobiliseDao->findBySimulationIDAndType($oldSimulation2->getId(), 1);
        $nonidentifees2 = $this->travauxImmobiliseDao->findBySimulationIDAndType($oldSimulation2->getId(), 2);

        foreach ($renouvellement1 as $key => $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $newObject->setNGroupe(strval($key + 1));
            $this->save($newObject);
            foreach ($object->getTravauxImmobilisesPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setTravauxImmobilises($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
        foreach ($identifees1 as $key => $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $newObject->setNGroupe(strval($key + 1));
            $this->save($newObject);
            foreach ($object->getTravauxImmobilisesPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setTravauxImmobilises($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
        foreach ($nonidentifees1 as $key => $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $newObject->setNGroupe(strval($key + 1));
            $this->save($newObject);
            foreach ($object->getTravauxImmobilisesPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setTravauxImmobilises($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }

        foreach ($renouvellement2 as $key => $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $newObject->setNGroupe(strval(count($renouvellement1) + $key + 1));
            $this->save($newObject);
            foreach ($object->getTravauxImmobilisesPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setTravauxImmobilises($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
        foreach ($identifees2 as $key => $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $newObject->setNGroupe(strval(count($identifees1) + $key + 1));
            $this->save($newObject);
            foreach ($object->getTravauxImmobilisesPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setTravauxImmobilises($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
        foreach ($nonidentifees2 as $key => $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $newObject->setNGroupe(strval(count($nonidentifees1) + $key + 1));
            $this->save($newObject);
            foreach ($object->getTravauxImmobilisesPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setTravauxImmobilises($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
    }
}
