<?php

declare(strict_types=1);

namespace App\Services;

use App\Dao\MaintenanceDao;
use App\Dao\MaintenancePeriodiqueDao;
use App\Dao\SimulationDao;
use App\Exceptions\HTTPException;
use App\Model\Factories\MaintenanceFactory;
use App\Model\Maintenance;
use App\Model\MaintenancePeriodique;
use App\Model\Simulation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv as ReaderCsv;
use PhpOffice\PhpSpreadsheet\Reader\Ods as ReaderOds;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ReaderXlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Safe\Exceptions\JsonException;
use Symfony\Component\HttpFoundation\Request;
use TheCodingMachine\TDBM\ResultIterator;
use TheCodingMachine\TDBM\TDBMException;
use Throwable;
use function array_merge;
use function array_push;
use function array_shift;
use function array_splice;
use function array_values;
use function chr;
use function count;
use function floor;
use function intval;
use function Safe\json_encode;
use function strval;

final class MaintenanceService
{
    /** @var MaintenanceFactory */
    private $factory;
    /** @var MaintenancePeriodiqueDao */
    private $periodiqueDao;

    /** @var MaintenanceDao */
    private $maintenanceDao;
    /** @var SimulationDao */
    private $simulationDao;
    /** @var string[] */
    private $range = [];

    public function __construct(
        MaintenanceDao $maintenanceDao,
        MaintenanceFactory $factory,
        SimulationDao $simulationDao,
        MaintenancePeriodiqueDao $periodiqueDao
    ) {
        $this->factory = $factory;
        $this->periodiqueDao = $periodiqueDao;
        $this->maintenanceDao = $maintenanceDao;
        $this->simulationDao = $simulationDao;
    }

    /**
     * @throws HTTPException
     */
    public function save(Maintenance $maintenance): void
    {
        try {
            $this->maintenanceDao->save($maintenance);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Cette maintenance existe déjà.', $e);
        }
    }

    /**
     * @throws TDBMException
     */
    public function remove(string $maintenanceUUId): void
    {
        try {
            $maintenance = $this->maintenanceDao->getById($maintenanceUUId);
            $this->maintenanceDao->delete($maintenance, true);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Cette maintenance n\'existe pas.', $e);
        }
    }

    /**
     * @return Maintenance[]|ResultIterator
     */
    public function findBySimulation(string $simulationId): ResultIterator
    {
        return $this->maintenanceDao->findBySimulationID($simulationId);
    }

    /**
     * @throws HTTPException
     * @throws JsonException
     * @throws TDBMException
     */
    public function createDefaultMaintenance(Simulation $newSimulation): void
    {
        foreach (Maintenance::DEFAULT_CONFIG as $name => $config) {
            $maintenance = $this->factory->createMaintenance(
                null,
                $newSimulation->getId(),
                $name,
                $config['regie'],
                0.0,
                true,
                $config['nature'],
                $config['type'],
                null
            );
            $this->save($maintenance);
            $this->createDefaultPeriodique($maintenance);
        }
    }

    private function createDefaultPeriodique(Maintenance $maintenance): void
    {
        for ($i = 1; $i <= MaintenancePeriodique::NUMBER_OF_ITERATIONS; $i++) {
            $maintenancePeriodique = new MaintenancePeriodique($maintenance, $i);
            $maintenancePeriodique->setValue(null);
            $this->periodiqueDao->save($maintenancePeriodique);
        }
    }

    public function exportChargesMaintenance(Worksheet $sheet, string $simulationId): Worksheet
    {
        $simulation = $this->simulationDao->getById($simulationId);
        $anneeDeReference = $simulation->getAnneeDeReference();
        $oldChargesMaintenances = $this->findBySimulation($simulationId);

        $writeData = [];
        $recapitulatifHeaders = [null];
        $maintenanceHeaders = ['N°', 'Nom de la catégorie', 'Régie', 'Consommation / Frais de Personnel', 'Indexation automatique','Taux d\'évolution'];

        for ($i = 0; $i < MaintenancePeriodique::NUMBER_OF_ITERATIONS; $i++) {
            array_push($recapitulatifHeaders, intval($anneeDeReference) + $i);
            array_push($maintenanceHeaders, intval($anneeDeReference) + $i);
        }

        array_push($writeData, $recapitulatifHeaders);

        $periodiques = [
            'maintenance_courante' => [],
            'gros_entretien' => [],
        ];

        foreach ($oldChargesMaintenances as $chargesMaintenance) {
            $chargesMaintenancePeriodiques = $chargesMaintenance->getMaintenancePeriodique();
            $type = $chargesMaintenance->getType();

            switch ($type) {
                case 0:
                    $periodiques['maintenance_courante'][] = $chargesMaintenancePeriodiques;
                    break;
                case 1:
                    $periodiques['gros_entretien'][] = $chargesMaintenancePeriodiques;
                    break;
            }
        }

        // Récapitulatif de la maintenance
        $maintenanceCouranteRow = $this->getTotalPeriodiques('maintenance');
        array_push($writeData, $maintenanceCouranteRow);
        $grosEntretienRow = $this->getTotalPeriodiques('gros');
        array_push($writeData, $grosEntretienRow);

        $row = [
            'maintenance_courante' => [],
            'gros_entretien' => [],
        ];

        foreach ($oldChargesMaintenances as $chargesMaintenance) {
            $item = [];
            $type = $chargesMaintenance->getType();
            $numero = $chargesMaintenance->getNumero();
            $nom = $chargesMaintenance->getNom();
            $regie = $chargesMaintenance->getRegie();
            $nature = $chargesMaintenance->getNature();
            $indexation = $chargesMaintenance->getIndexation();
            $tauxDevolution = $chargesMaintenance->getTauxDevolution();
            $chargesMaintenancePeriodiques = $chargesMaintenance->getMaintenancePeriodique();

            if ($nature === 1) {
                $nature = 'FP';
            }

            if ($nature === 0) {
                $nature = 'Conso';
            }

            if ($regie === true) {
                $regie = 'Oui';
            }

            if ($regie === false) {
                $regie = 'Non';
            }

            if ($indexation === true) {
                $indexation = 'Oui';
            }

            if ($indexation === false) {
                $indexation = 'Non';
            }

            $item[] = $numero;
            $item[] = $nom;
            $item[] = $regie;
            $item[] = $nature;
            $item[] = $indexation;
            $item[] = $tauxDevolution;

            foreach ($chargesMaintenancePeriodiques as $periodique) {
                $item[] = $periodique->getValue();
            }

            if ($type === 0) {
                $row['maintenance_courante'][] = $item;
            }

            if ($type !== 1) {
                continue;
            }

            $row['gros_entretien'][] = $item;
        }

        // Détail de la maintenance courante du patrimoine de référence y compris régie
        $maintenanceData = [];
        array_push($maintenanceData, $maintenanceHeaders);

        foreach ($row['maintenance_courante'] as $item) {
            $maintenanceData[] = $item;
        }
        $totalRow = ['Total', null, null, null, null, 0];
        array_shift($maintenanceCouranteRow);
        $lastMaintenanceRow = array_merge($totalRow, $maintenanceCouranteRow);
        array_push($maintenanceData, $lastMaintenanceRow);
        array_push($maintenanceData, []);

        // Détail du gros entretien du patrimoine de référence y compris régie
        $grosData = [];
        array_push($grosData, $maintenanceHeaders);

        foreach ($row['gros_entretien'] as $item) {
            $grosData[] = $item;
        }
        $totalRow = ['Total', null, null, null, null, 0];
        array_shift($grosEntretienRow);
        $lastGrosRow = array_merge($totalRow, $grosEntretienRow);
        array_push($grosData, $lastGrosRow);

        $sheet->setTitle('charges_maintenance');
        $sheet->getColumnDimension('A')->setwidth(76);
        $sheet->getColumnDimension('B')->setwidth(48);
        $sheet->getColumnDimension('D')->setwidth(30);
        $sheet->getColumnDimension('E')->setwidth(25);
        $sheet->getColumnDimension('F')->setwidth(15);
        $sheet->setCellValue('A1', 'Charges - Maintenance');
        $sheet->setCellValue('A2', 'Récapitulatif de la maintenance');
        $sheet->setCellValue('A7', 'Détail de la maintenance courante du patrimoine de référence y compris régie');
        $sheet->setCellValue('A' . (count($maintenanceData) + 8), 'Détail du gros entretien du patrimoine de référence y compris régie');

        $sheet->fromArray($writeData, null, 'A3', true);
        $sheet->fromArray($maintenanceData, null, 'A8', true);
        $sheet->fromArray($grosData, null, 'A' . (count($maintenanceData) + 9), true);

        $sheet->getStyle('A1:A' . $i)->getFont()->setBold(true);
        $sheet->getStyle('B3:AZ3')->getFont()->setBold(true);
        $sheet->getStyle('B8:BD8')->getFont()->setBold(true);
        $sheet->getStyle('B' . (count($maintenanceData) + 9) . ':BD' . (count($maintenanceData) + 9))->getFont()->setBold(true);
        $sheet->getStyle('A3:BD' . (count($maintenanceData) + count($grosData)+8))->getFont()->setSize(10);
        $sheet->getStyle('A7')->getFont()->setSize(11);
        $sheet->getStyle('A' . (count($maintenanceData) + 8))->getFont()->setSize(11);
        $sheet->getStyle('A1:BD' . (count($maintenanceData) + count($grosData)+8))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B3:AY3')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A4:AY5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A8:BD' . (count($maintenanceData) + 6))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A' . (count($maintenanceData) + 9) . ':BD' . (count($maintenanceData) + count($grosData)+8))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $this->setRange(['B9:F' . (count($periodiques['maintenance_courante']) + 8), 'B' . (count($maintenanceData) + 10) . ':F' . (count($maintenanceData) + 7 + count($grosData))]);

        // excel side calculation
        for ($i = 2; $i <= MaintenancePeriodique::NUMBER_OF_ITERATIONS + 1; $i++) {
            $column = $this->columnLetter($i);
            $targetColumn = $this->columnLetter($i + 5);
            $sheet->setCellValue(
                $column . '4',
                '=SUM(' . $targetColumn . '9:' . $targetColumn . (count($periodiques['maintenance_courante']) + 8) . ')'
            );
            $sheet->setCellValue(
                $column . '5',
                '=SUM(' . $targetColumn . (count($maintenanceData) + 10) . ':' . $targetColumn . (count($maintenanceData) + 7 + count($grosData)) . ')'
            );
        }

        for ($i = 6; $i <= MaintenancePeriodique::NUMBER_OF_ITERATIONS + 6; $i++) {
            $column = $this->columnLetter($i);
            $sheet->setCellValue(
                $column . (count($periodiques['maintenance_courante']) + 9),
                '=SUM(' . $column . '9:' . $column . (count($periodiques['maintenance_courante']) + 8) . ')'
            );
            $sheet->setCellValue(
                $column . (count($maintenanceData) + 8 + count($grosData)),
                '=SUM(' . $column . (count($maintenanceData) + 10) . ':' . $column . (count($maintenanceData) + 7 + count($grosData)) . ')'
            );
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

    /**
     * @return mixed[]
     */
    public function getTotalPeriodiques(string $type): array
    {
        $row = [];

        switch ($type) {
            case 'maintenance':
                array_push($row, 'Maintenance courante (y cis frais de personnel)');
                break;
            case 'gros':
                array_push($row, 'Gros entretien (y cis frais de personnel)');
                break;
        }

        for ($i = 0; $i < MaintenancePeriodique::NUMBER_OF_ITERATIONS; $i++) {
            $totalPeriodique = 0;
            array_push($row, $totalPeriodique);
        }

        return $row;
    }

    public function importChargesMaintenance(Request $request, string $simulationId): string
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
                throw HTTPException::badRequest('Extension invalide');
        }

        if ($file) {
            $spreadsheet = IOFactory::load($file);
            $isChargesMaintenance = false;

            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $worksheetTitle = $worksheet->getTitle();

                if ($worksheetTitle !== 'charges_maintenance') {
                    continue;
                }

                $isChargesMaintenance = true;
            }

            if ($isChargesMaintenance === false) {
                throw HTTPException::badRequest('Il n\'y a pas de feuille correcte, veuillez vérifier à nouveau.');
            }

            $indexes = [];

            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $worksheetTitle = $worksheet->getTitle();

                if ($worksheetTitle !== 'charges_maintenance') {
                    continue;
                }

                foreach ($worksheet->getRowIterator() as $row) {
                    $rowIndex = $row->getRowIndex();
                    $cellIterator = $row->getCellIterator();
                    foreach ($cellIterator as $cell) {
                        if ($cell->getValue() !== 'Total') {
                            continue;
                        }

                        $indexes[] = $rowIndex;
                    }
                }
            }

            $data = [
                'maintenance_courante' => [
                    'columnNames' => [],
                    'columnValues' => [],
                ],
                'gros_entretien' => [
                    'columnNames' => [],
                    'columnValues' => [],
                ],
            ];

            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $worksheetTitle = $worksheet->getTitle();

                if ($worksheetTitle !== 'charges_maintenance') {
                    continue;
                }

                foreach ($worksheet->getRowIterator() as $row) {
                    $rowIndex = $row->getRowIndex();

                    if ($rowIndex >= 8 && $rowIndex < $indexes[0]) {
                        $cellIterator = $row->getCellIterator();
                        $cellIterator->setIterateOnlyExistingCells(true);

                        foreach ($cellIterator as $cell) {
                            $column = $cell->getColumn();

                            if ($cell->getValue() === null && $column !== 'D') {
                                continue;
                            }

                            if ($rowIndex === 8) {
                                $data['maintenance_courante']['columnNames'][] = $cell->getCalculatedValue();
                            } else {
                                $data['maintenance_courante']['columnValues'][$rowIndex][] = $cell->getCalculatedValue();
                            }
                        }
                    }

                    if ($rowIndex < $indexes[0]+3 || $rowIndex >= $indexes[1]) {
                        continue;
                    }

                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(true);

                    foreach ($cellIterator as $cell) {
                        $column = $cell->getColumn();

                        if ($cell->getValue() === null && $column !== 'D') {
                            continue;
                        }

                        if ($rowIndex === $indexes[0]+3) {
                            $data['gros_entretien']['columnNames'][] = $cell->getCalculatedValue();
                        } else {
                            $data['gros_entretien']['columnValues'][$rowIndex][] = $cell->getCalculatedValue();
                        }
                    }
                }
            }

            $data['maintenance_courante']['columnValues'] = array_values($data['maintenance_courante']['columnValues']);
            $data['gros_entretien']['columnValues'] = array_values($data['gros_entretien']['columnValues']);

            $oldChargesMaintenances = $this->findBySimulation($simulationId);

            foreach ($oldChargesMaintenances as $chargesMaintenance) {
                $this->remove($chargesMaintenance->getUuid());
            }

            $this->saveImportedMaintenance($data['maintenance_courante']['columnValues'], 0, $simulationId);
            $this->saveImportedMaintenance($data['gros_entretien']['columnValues'], 1, $simulationId);
        }

        return 'Maintenance importée.';
    }

    /**
     * @param mixed[] $maintenances
     *
     * @throws HTTPException
     * @throws JsonException
     * @throws TDBMException
     */
    public function saveImportedMaintenance(array $maintenances, int $type, string $simulationId): void
    {
        foreach ($maintenances as $item) {
            $periodique = [];
            $numero = intval($item[0]);
            $nom = strval($item[1]);
            $regie = $item[2];

            if ($item[2] === 'Oui') {
                $regie = true;
            }

            if ($item[2] === 'Non') {
                $regie = false;
            }

            $nature = $item[3];
            if ($item[3] === 'FP') {
                $nature = 1;
            }

            if ($item[3] === 'Conso') {
                $nature = 0;
            }

            $indexation = $item[4];
            if ($item[4] === 'Oui') {
                $indexation = true;
            }

            if ($item[4] === 'Non') {
                $indexation = false;
            }
            $tauxDevolution = floor($item[5]);
            array_splice($item, 0, 6);
            foreach ($item as $value) {
                $periodique[] = floor($value);
            }

            $newMaintenance = $this->factory->createMaintenance(
                null,
                $simulationId,
                $nom,
                $regie,
                $tauxDevolution,
                $indexation,
                $nature,
                $type,
                json_encode(['periodique' => $periodique])
            );
            $this->save($newMaintenance);
        }
    }

    /**
     *  @return string[]
     */
    public function getRange(): array
    {
        return $this->range;
    }

    /**
     *  @param string[] $range
     */
    public function setRange(array $range): void
    {
        $this->range = $range;
    }

    public function cloneMaintenance(Simulation $newSimulation, Simulation $oldSimulation): void
    {
        $objects = $this->maintenanceDao->findBySimulation($oldSimulation);
        foreach ($objects as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getMaintenancePeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setMaintenance($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
    }

    public function fusionMaintenance(Simulation $newSimulation, Simulation $oldSimulation1, Simulation $oldSimulation2): void
    {
        $objects1 = $this->maintenanceDao->findBySimulation($oldSimulation1);
        $objects2 = $this->maintenanceDao->findBySimulation($oldSimulation2);
        foreach ($objects1 as $key => $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $newObject->setNumero($key + 1);
            $this->save($newObject);
            foreach ($object->getMaintenancePeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setMaintenance($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
        foreach ($objects2 as $key => $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $newObject->setNumero(count($objects1) + $key + 1);
            $this->save($newObject);
            foreach ($object->getMaintenancePeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setMaintenance($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
    }
}
