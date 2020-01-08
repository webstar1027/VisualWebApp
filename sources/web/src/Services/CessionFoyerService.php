<?php

declare(strict_types=1);

namespace App\Services;

use App\Dao\CessionFoyerDao;
use App\Dao\CessionFoyerPeriodiqueDao;
use App\Dao\SimulationDao;
use App\Exceptions\HTTPException;
use App\Model\CessionFoyer;
use App\Model\Factories\CessionFoyerFactory;
use App\Model\Simulation;
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
use function array_values;
use function chr;
use function count;
use function in_array;
use function intval;
use function Safe\json_encode;
use function strval;

class CessionFoyerService
{
    /** @var CessionFoyerDao */
    private $cessionFoyerDao;
    /** @var CessionFoyerPeriodiqueDao */
    private $periodiqueDao;
    /** @var SimulationDao */
    private $simulationDao;
    /** @var CessionFoyerFactory */
    private $factory;

    public function __construct(CessionFoyerDao $cessionFoyerDao, CessionFoyerPeriodiqueDao $periodiqueDao, SimulationDao $simulationDao, CessionFoyerFactory $factory)
    {
        $this->cessionFoyerDao = $cessionFoyerDao;
        $this->periodiqueDao = $periodiqueDao;
        $this->simulationDao = $simulationDao;
        $this->factory = $factory;
    }

    public function save(CessionFoyer $cessionFoyer): void
    {
        try {
            $this->cessionFoyerDao->save($cessionFoyer);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Cette cession existe déjà.', $e);
        }
    }

    /**
     * @throws TDBMException
     * @throws HTTPException
     */
    public function remove(string $cessionFoyerUUID): void
    {
        try {
            $operation = $this->cessionFoyerDao->getById($cessionFoyerUUID);
            $this->cessionFoyerDao->delete($operation, true);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Cette cession n\'existe déjà.', $e);
        }
    }

    /**
     * @return CessionFoyer[]|ResultIterator
     */
    public function findBySimulation(string $simulationId): ResultIterator
    {
        return $this->cessionFoyerDao->findBySimulationID($simulationId);
    }

    public function cloneCessionFoyer(Simulation $newSimulation, Simulation $oldSimulation): void
    {
        $objects = $this->cessionFoyerDao->findBySimulationID($oldSimulation->getId());
        foreach ($objects as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getCessionsFoyersPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setCessionsFoyers($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
    }

    public function fusionCessionFoyer(Simulation $newSimulation, Simulation $oldSimulation1, Simulation $oldSimulation2): void
    {
        $objects1 = $this->cessionFoyerDao->findBySimulationID($oldSimulation1->getId());
        $objects2 = $this->cessionFoyerDao->findBySimulationID($oldSimulation2->getId());
        foreach ($objects1 as $key => $object) {
            $newObject = clone $object;
            $newObject->setNGroupe($key + 1);
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getCessionsFoyersPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setCessionsFoyers($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
        foreach ($objects2 as $key => $object) {
            $newObject = clone $object;
            $newObject->setNGroupe(count($objects1) + $key + 1);
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getCessionsFoyersPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setCessionsFoyers($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
    }

    /**
     *  @param string[] $init
     *
     *  @return mixed[]
     */
    public function getHeadersByAnneeDeReference(string $prefix, array $init, string $simulationId): array
    {
        $simulation = $this->simulationDao->getById($simulationId);
        $anneeDeReference = $simulation->getAnneeDeReference();

        for ($i = 0; $i < 50; $i++) {
            $init[] = $prefix . (intval($anneeDeReference) + $i);
        }

        return $init;
    }

    public function exportCessionFoyers(Worksheet $sheet, string $simulationId): Worksheet
    {
        $writeData = [];
        $headers = [
            'N°',
            'Nom de l\'intervention',
            'Nature',
            'Date fin de bail',
            'A indexer à l\'inflation',
            'Nb equivalent logts',
            'Date de cession',
            'Prix net de cession',
            'Valeur Nette Comptable',
            'Remboursement anticipé',
        ];

        $headers = $this->getHeadersByAnneeDeReference('Redevance ', $headers, $simulationId);
        $headers = $this->getHeadersByAnneeDeReference('Economie d\'Annuités - part capital ', $headers, $simulationId);
        $headers = $this->getHeadersByAnneeDeReference('Economie d\'Annuités - part intérêts ', $headers, $simulationId);
        $headers[] = 'Taux évolution TFPB';
        $headers = $this->getHeadersByAnneeDeReference('TFPB ', $headers, $simulationId);
        $headers[] = 'Taux évolution maintenance';
        $headers = $this->getHeadersByAnneeDeReference('Maintenance ', $headers, $simulationId);
        $headers[] = 'Taux évolution Gros entretien';
        $headers = $this->getHeadersByAnneeDeReference('Gros entretien ', $headers, $simulationId);

        $headers[] = 'Réduction d\'amortissement technique annuelle (k€)';
        $headers[] = 'Réduction de reprise de subvention annuelle (k€)';
        $headers[] = 'Durée d\'amortissement technique résiduelle (année)';
        $writeData[] = $headers;

        $cessionFoyers = $this->findBySimulation($simulationId);

        foreach ($cessionFoyers as $cessionFoyer) {
            $row = [];
            $row[] = $cessionFoyer->getNGroupe();
            $row[] = $cessionFoyer->getNomIntervention();
            $row[] = $cessionFoyer->getNature();
            $row[] = $cessionFoyer->getIndexerInflation()=== true ? 'Oui' : 'Non';
            $row[] = $cessionFoyer->getNombreLogements();
            $row[] = $cessionFoyer->getDateCession();
            $row[] = $cessionFoyer->getPrixNetCession();
            $row[] = $cessionFoyer->getValeurNetteComptable();
            $row[] = $cessionFoyer->getRemboursementAnticipe();

            $periodiques = $cessionFoyer->getCessionsFoyersPeriodique();

            foreach ($periodiques as $periodique) {
                $row[] = $periodique->getRedevance();
            }

            foreach ($periodiques as $periodique) {
                $row[] = $periodique->getPartCapital();
            }

            foreach ($periodiques as $periodique) {
                $row[] = $periodique->getPartInterets();
            }

            $row[] = $cessionFoyer->getTauxEvolutionTfpb();

            foreach ($periodiques as $periodique) {
                $row[] = $periodique->getTfpb();
            }

            $row[] = $cessionFoyer->getTauxEvolutionMaintenance();

            foreach ($periodiques as $periodique) {
                $row[] = $periodique->getMaintenanceCourante();
            }

            $row[] = $cessionFoyer->getTauxEvolutionGrosEntretien();

            foreach ($periodiques as $periodique) {
                $row[] = $periodique->getGrosEntretien();
            }

            $row[] = $cessionFoyer->getReductionAmortissementAnnuelle();
            $row[] = $cessionFoyer->getReductionRepriseAnnuelle();
            $row[] = $cessionFoyer->getDureeResiduelle();
            $writeData[] = $row;
        }

        $sheet->setTitle('CF');
        $sheet->setCellValue('A1', 'Cessions foyers');
        $sheet->fromArray($writeData, null, 'A2', true);

        $sheet = $this->setBoldSheet([
            'A1',
            'A2:LD2',
        ], $sheet);

        $sheet->getStyle('A2:LD' . (count($writeData) + 1))->getFont()->setSize(10);
        $sheet->getStyle('A1')->getFont()->setSize(11);
        $sheet->getRowDimension(2)->setRowHeight(35);
        $sheet = $this->setBorderSheet(['A2:LD' . (count($writeData) + 1)], $sheet);
        $sheet->getStyle('A2:LD' . (count($writeData) + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        for ($i = 1; $i <= 316; $i++) {
            $column = $this->columnLetter($i);
            $sheet->getColumnDimension($column)->setwidth(25);
            $sheet->getStyle($column . '2')->getAlignment()->setWrapText(true);
        }

        return $sheet;
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

    public function importcessionFoyers(Request $request, string $simulationId): string
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
            $isCessionFoyers = false;
            // Get data from imported excel data

            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $worksheetTitle = $worksheet->getTitle();

                if ($worksheetTitle !== 'CF') {
                    continue;
                }

                $isCessionFoyers = true;
            }

            if ($isCessionFoyers === false) {
                throw HTTPException::badRequest('Il n\'y a pas de feuille correcte, veuillez vérifier à nouveau.');
            }

            $data['cession_foyers'] = [
                'columnNames' => [],
                'columnValues' => [],
            ];

            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $worksheetTitle = $worksheet->getTitle();

                if ($worksheetTitle !== 'CF') {
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
                            $data['cession_foyers']['columnNames'][] = $cell->getCalculatedValue();
                        } else {
                            $data['cession_foyers']['columnValues'][$rowIndex][] = $cell->getCalculatedValue();
                        }
                    }
                }
            }

            $data['cession_foyers']['columnValues'] = array_values($data['cession_foyers']['columnValues']);

            foreach ($data['cession_foyers']['columnValues'] as $key => $item) {
                $valueArray = ['Oui', 'Non'];
                $defaultNatures = [
                    'Vente hors groupe',
                    'Vente groupe',
                    'Fin de bail LT',
                    'Fin Usufruit locatif',
                    'Autres',
                ];

                $redevance = [];
                $partCapital = [];
                $partInterets = [];
                $tfpb = [];
                $maintenanceCourante = [];
                $grosEntretien = [];

                if (! in_array($item[4], $valueArray)) {
                    throw HTTPException::badRequest('La valeur doit etre Non ou Oui');
                }

                if (! in_array($item[2], $defaultNatures)) {
                    throw HTTPException::badRequest('Il n\'y a pas une telle nature.');
                }

                for ($i = 10; $i < 313; $i++) {
                    if ($i < 60) {
                        $redevance[] = $item[$i];
                    }

                    if ($i >= 60 && $i < 110) {
                        $partCapital[] = $item[$i];
                    }

                    if ($i >= 110 && $i < 160) {
                        $partInterets[] = $item[$i];
                    }

                    if ($i >= 161 && $i < 211) {
                        $tfpb[] = $item[$i];
                    }

                    if ($i >= 212 && $i < 262) {
                        $maintenanceCourante[] = $item[$i];
                    }

                    if ($i < 263 || $i >= 313) {
                        continue;
                    }

                    $grosEntretien[] = $item[$i];
                }

                $oldCessionFoyers = $this->cessionFoyerDao->findOneByNGroupe($simulationId, intval($item[0]));

                try {
                    if (count($oldCessionFoyers) > 0) {
                        $oldCessionFoyer = $this->factory->createCessionFoyer(
                            $oldCessionFoyers[0]->getId(),
                            $simulationId,
                            intval($item[0]),
                            strval($item[1]),
                            $item[2],
                            $item[4] === 'Oui',
                            intval($item[5]),
                            strval($item[6]),
                            $item[7],
                            $item[8],
                            $item[9],
                            $item[160],
                            $item[211],
                            $item[262],
                            $item[313],
                            $item[314],
                            $item[315],
                            json_encode([
                                'redevance' => $redevance,
                                'part_capital' => $partCapital,
                                'part_interets' => $partInterets,
                                'tfpb' => $tfpb,
                                'maintenance_courante' => $maintenanceCourante,
                                'gros_entretien' => $grosEntretien,
                            ])
                        );

                        $this->save($oldCessionFoyer);
                        $changedIds[] = $oldCessionFoyers[0]->getId();
                    } else {
                        $newCessionFoyer = $this->factory->createCessionFoyer(
                            null,
                            $simulationId,
                            intval($item[0]),
                            strval($item[1]),
                            $item[2],
                            $item[4] === 'Oui',
                            intval($item[5]),
                            strval($item[6]),
                            $item[7],
                            $item[8],
                            $item[9],
                            $item[160],
                            $item[211],
                            $item[262],
                            $item[313],
                            $item[314],
                            $item[315],
                            json_encode([
                                'redevance' => $redevance,
                                'part_capital' => $partCapital,
                                'part_interets' => $partInterets,
                                'tfpb' => $tfpb,
                                'maintenance_courante' => $maintenanceCourante,
                                'gros_entretien' => $grosEntretien,
                            ])
                        );

                        $this->save($newCessionFoyer);
                        $changedIds[] = $newCessionFoyer->getId();
                    }
                } catch (Throwable $e) {
                    throw HTTPException::badRequest('Il y a une erreur à l\'importation.', $e);
                }
            }
            $allCessionFoyers = $this->findBySimulation($simulationId);

            foreach ($allCessionFoyers as $_cessionFoyer) {
                if (in_array($_cessionFoyer->getId(), $changedIds)) {
                    continue;
                }

                $this->remove($_cessionFoyer->getId());
            }
        }

        return 'Cession Foyers importé';
    }
}
