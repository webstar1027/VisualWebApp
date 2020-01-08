<?php

declare(strict_types=1);

namespace App\Services;

use App\Dao\AutreChargeDao;
use App\Dao\AutreChargePeriodiqueDao;
use App\Dao\SimulationDao;
use App\Exceptions\HTTPException;
use App\Model\AutreCharge;
use App\Model\AutreChargePeriodique;
use App\Model\Factories\AutreChargeFactory;
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
use function array_pop;
use function array_push;
use function chr;
use function count;
use function floor;
use function in_array;
use function intval;
use function Safe\json_encode;

final class AutreChargeService
{
    /** @var AutreChargeDao */
    private $autreChargeDao;
    /** @var AutreChargeFactory */
    private $factory;
    /** @var AutreChargePeriodiqueDao */
    private $periodiqueDao;
     /** @var SimulationDao */
    private $simulationDao;

    public function __construct(AutreChargeDao $autreChargeDao, SimulationDao $simulationDao, AutreChargeFactory $factory, AutreChargePeriodiqueDao $periodiqueDao)
    {
        $this->autreChargeDao = $autreChargeDao;
        $this->factory = $factory;
        $this->periodiqueDao = $periodiqueDao;
        $this->simulationDao = $simulationDao;
    }

    /**
     * @throws HTTPException
     */
    public function save(AutreCharge $autreCharge): void
    {
        try {
            $this->autreChargeDao->save($autreCharge);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Cette autre charge existe déjà.', $e);
        }
    }

    /**
     * @throws TDBMException
     */
    public function remove(string $autreChargeId): void
    {
        try {
            $autreCharge = $this->autreChargeDao->getById($autreChargeId);
            $this->autreChargeDao->delete($autreCharge, true);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Cette autre charge n\'existe pas.', $e);
        }
    }

    /**
     * @return AutreCharge[]|ResultIterator
     */
    public function findBySimulation(string $simulationId): ResultIterator
    {
        return $this->autreChargeDao->findBySimulationID($simulationId);
    }

    /**
     * @throws TDBMException
     * @throws JsonException
     */
    public function createDefaultAutresCharges(Simulation $newSimulation): void
    {
        foreach (AutreCharge::NATURE_LIST as $nature) {
            $autreCharge = $this->factory->createAutreCharge(null, $newSimulation->getId(), $nature, 0.0, true, $nature, null);
            $this->save($autreCharge);
            $this->createDefaultPeriodique($autreCharge);
        }
    }

    public function cloneAutresCharges(Simulation $newSimulation, Simulation $oldSimulation): void
    {
        $objects = $this->autreChargeDao->findBySimulation($oldSimulation);
        foreach ($objects as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getAutresChargesPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setAutresCharges($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
    }

    public function fusionAutresCharges(Simulation $newSimulation, Simulation $oldSimulation1, Simulation $oldSimulation2): void
    {
        $objects1 = $this->autreChargeDao->findBySimulation($oldSimulation1);
        $objects2 = $this->autreChargeDao->findBySimulation($oldSimulation2);
        foreach ($objects1 as $key => $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $newObject->setNumero($key + 1);
            $this->save($newObject);
            foreach ($object->getAutresChargesPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setAutresCharges($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
        foreach ($objects2 as $key => $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $newObject->setNumero(count($objects1) + $key + 1);
            $this->save($newObject);
            foreach ($object->getAutresChargesPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setAutresCharges($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
    }

    private function createDefaultPeriodique(AutreCharge $autreCharge): void
    {
        for ($i = 1; $i <= AutreChargePeriodique::NUMBER_OF_ITERATIONS; $i++) {
            $autreChargePeriodique = new AutreChargePeriodique($autreCharge, $i);
            $autreChargePeriodique->setValue(null);
            $this->periodiqueDao->save($autreChargePeriodique);
        }
    }

    public function exportAutresCharges(Worksheet $sheet, string $simulationId): Worksheet
    {
        $simulation = $this->simulationDao->getById($simulationId);
        $anneeDeReference = $simulation->getAnneeDeReference();

        $recapitulatifWriteData = [];
        $detailWriteData = [];
        $recapitulatifHeaders = [null];
        $detailHeaders = ['N° ', 'Libellé de la charge', 'Nature', 'Indexation automatique', 'Taux d\'évolution'];

        for ($i = 0; $i < AutreChargePeriodique::NUMBER_OF_ITERATIONS; $i++) {
            array_push($recapitulatifHeaders, intval($anneeDeReference) + $i);
            array_push($detailHeaders, intval($anneeDeReference) + $i);
        }

        array_push($recapitulatifWriteData, $recapitulatifHeaders);
        array_push($detailWriteData, $detailHeaders);
        $autreCharges = $this->findBySimulation($simulationId);

        foreach ($autreCharges as $autreCharge) {
            $row = [];
            $numero = $autreCharge->getNumero();
            $libelle = $autreCharge->getLibelle() !== 'Taxes foncières' ? $autreCharge->getLibelle() : 'TFPB';
            $nature = $autreCharge->getNature();
            $indexation = $autreCharge->getIndexation();

            if ($indexation === true) {
                $indexation = 'Oui';
            }

            if ($indexation === false) {
                $indexation = 'Non';
            }

            $tauxDevolution = $autreCharge->getTauxDevolution();

            array_push($row, $numero);
            array_push($row, $libelle);
            array_push($row, $nature);
            array_push($row, $indexation);
            array_push($row, $tauxDevolution);

            $autreChargePeriodiques = $autreCharge->getAutresChargesPeriodique();

            foreach ($autreChargePeriodiques as $autreChargePeriodique) {
                array_push($row, $autreChargePeriodique->getValue());
            }

            array_push($detailWriteData, $row);
        }

        $totalRow = ['Total', null, null, null, null];

        for ($i = 0; $i < AutreChargePeriodique::NUMBER_OF_ITERATIONS; $i++) {
            array_push($totalRow, 0);
        }

        array_push($detailWriteData, $totalRow);

        for ($i = 0; $i < count(AutreCharge::NATURE_LIST); $i++) {
            $row = [];
            $row[] = AutreCharge::NATURE_LIST[$i] !== 'Taxes foncières' ? AutreCharge::NATURE_LIST[$i] : 'Taxe foncière (TFPB)';

            for ($j = 0; $j < AutreChargePeriodique::NUMBER_OF_ITERATIONS; $j++) {
                $row[] = 0;
            }

            array_push($recapitulatifWriteData, $row);
        }

        $sheet->setTitle('charges_autres');
        $sheet->setCellValue('A1', 'Autres charges');
        $sheet->setCellValue('A2', 'Récapitulatif des autres charges');
        $sheet->setCellValue('A10', 'Détail des autres charges');
        $sheet->fromArray($recapitulatifWriteData, null, 'A3', true);
        $sheet->fromArray($detailWriteData, null, 'A11', true);

        for ($i = 6; $i < AutreChargePeriodique::NUMBER_OF_ITERATIONS + 6; $i++) {
            $column = $this->columnLetter($i);
            $sheet->setCellValue(
                $column . (count($autreCharges) + 12),
                '=SUM(' . $column . '12:' . $column . (count($autreCharges) + 11) . ')'
            );
        }

        for ($j = 0; $j < count(AutreCharge::NATURE_LIST); $j++) {
            for ($i = 2; $i <= AutreChargePeriodique::NUMBER_OF_ITERATIONS + 1; $i++) {
                $column = $this->columnLetter($i);
                $targetColumn = $this->columnLetter($i + 4);
                $sheet->setCellValue(
                    $column . ($j + 4),
                    '=SUMIF(C12:C' . (count($autreCharges)+11) . ', "' . AutreCharge::NATURE_LIST[$j] . '", ' . $targetColumn . '12:' . $targetColumn . (count($autreCharges) + 11) . ')'
                );
            }
        }

        $sheet->getColumnDimension('A')->setwidth(35);
        $sheet->getColumnDimension('B')->setwidth(28);
        $sheet->getColumnDimension('C')->setwidth(28);
        $sheet->getColumnDimension('D')->setwidth(22);
        $sheet->getColumnDimension('E')->setwidth(20);
        $sheet->getStyle('A1:A' . (count($autreCharges) + 12))->getFont()->setBold(true);
        $sheet->getStyle('B3:AY3')->getFont()->setBold(true);
        $sheet->getStyle('B11:BC11')->getFont()->setBold(true);
        $sheet->getStyle('A3:BC' . (count($autreCharges) + 12))->getFont()->setSize(10);
        $sheet->getStyle('A10')->getFont()->setSize(10);
        $sheet->getStyle('A1:BC' . (count($autreCharges) + 12))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A11:BC' . (count($autreCharges) + 12))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A3:AY8')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

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

    public function importAutresCharges(Request $request, string $simulationId): string
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
            $data = [];
            $isAutreCharge = false;
            // Get data from imported excel data

            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $worksheetTitle = $worksheet->getTitle();

                if ($worksheetTitle !== 'charges_autres') {
                    continue;
                }

                $isAutreCharge = true;
            }

            if ($isAutreCharge === false) {
                throw HTTPException::badRequest('Il n\'y a pas de feuille correcte, veuillez vérifier à nouveau.');
            }

            $data['charges_autres'] = [
                'columnNames' => [],
                'columnValues' => [],
            ];

            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $worksheetTitle = $worksheet->getTitle();

                if ($worksheetTitle !== 'charges_autres') {
                    continue;
                }

                foreach ($worksheet->getRowIterator() as $row) {
                    $rowIndex = $row->getRowIndex();

                    if ($rowIndex < 10) {
                        continue;
                    }

                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(true);

                    foreach ($cellIterator as $cell) {
                        if ($rowIndex === 11) {
                            $data['charges_autres']['columnNames'][] = $cell->getCalculatedValue();
                        }

                        if ($rowIndex <= 11) {
                            continue;
                        }

                        $data['charges_autres']['columnValues'][$rowIndex][] = $cell->getCalculatedValue();
                    }
                }
            }

            array_pop($data['charges_autres']['columnValues']);

            $autreCharges = $this->findBySimulation($simulationId);
            $simulation = $this->simulationDao->getById($simulationId);

            foreach ($autreCharges as $autreCharge) {
                $this->remove($autreCharge->getId());
            }

            // save data
            foreach ($data['charges_autres']['columnValues'] as $item) {
                $libelle = $item[1] !== 'TFPB' ? $item[1] : 'Taxes foncières';
                $tauxDevolution =  floor($item[4]);
                $indexation = $item[3];
                $nature = $item[2];
                $periodique = [];

                if ($item[3] === 'Oui') {
                    $indexation = true;
                }

                if ($item[3] === 'Non') {
                    $indexation = false;
                }

                for ($i = 5; $i < 55; $i++) {
                    $periodique[] = floor($item[$i]);
                }

                if (! in_array($nature, AutreCharge::NATURE_LIST)) {
                    throw HTTPException::badRequest('la nature "' . $nature . '" n\'existe pas.');
                }

                $autreCharge = $this->factory->createAutreCharge(
                    null,
                    $simulationId,
                    $libelle,
                    $tauxDevolution,
                    $indexation,
                    $nature,
                    json_encode(['periodique' => $periodique])
                );

                $this->save($autreCharge);
            }
        }

        return 'Autres charges importées.';
    }
}
