<?php

declare(strict_types=1);

namespace App\Services;

use App\Dao\ModeleDamortissementDao;
use App\Exceptions\HTTPException;
use App\Model\Factories\ModeleDamortissementFactory;
use App\Model\ModeleDamortissement;
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
use function array_push;
use function array_values;
use function chr;
use function count;
use function intval;
use function strval;

final class ModeleDamortissementService
{
    /** @var ModeleDamortissementDao */
    private $damortissementDao;
    /** @var ModeleDamortissementFactory */
    private $factory;

    public function __construct(ModeleDamortissementDao $damortissementDao, ModeleDamortissementFactory $factory)
    {
        $this->damortissementDao = $damortissementDao;
        $this->factory = $factory;
    }

    /**
     * @throws HTTPException
     */
    public function save(ModeleDamortissement $modeleDamortissement): void
    {
        try {
            $this->damortissementDao->save($modeleDamortissement);
        } catch (Throwable $e) {
            throw HTTPException::badRequest("Ce modèle d'amortissement existe déjà.", $e);
        }
    }

    /**
     * @throws TDBMException
     */
    public function remove(string $modeleDamortissementUUID): void
    {
        try {
            $modeleDamortissement = $this->damortissementDao->getById($modeleDamortissementUUID);
            $this->damortissementDao->delete($modeleDamortissement, true);
        } catch (Throwable $e) {
            throw HTTPException::badRequest("Ce modèle d'amortissement n\'existe pas.", $e);
        }
    }

    /**
     * @return ModeleDamortissement[]|ResultIterator
     */
    public function fetchBySimulationId(string $simulationId): ResultIterator
    {
        return $this->damortissementDao->findBySimulationID($simulationId);
    }

    /**
     * @return ModeleDamortissement[]|ResultIterator
     */
    public function fetchBySimulationNom(string $simulationId, string $nom): ResultIterator
    {
        return $this->damortissementDao->fetchBySimulationNom($simulationId, $nom);
    }

    public function exportModelesAmortissement(Worksheet $sheet, string $simulationId): Worksheet
    {
        $writeData = [];
        $headers = [
            'N° du modèle d\'amortissement',
            'Nom du modèle d\'amortissement',
            'Durée de reprise de la subvention part foncier ',
            'Structure et assimilé',
            'Durée d\'amortissement Structure et assimilé',
            'Menuiserie extérieure',
            'Durée d\'amortissement Menuiserie extérieure',
            'Chauffage',
            'Durée d\'amortissement Chauffage',
            'Etanchéité',
            'Durée d\'amortissement Etanchéité',
            'Ravalement avec amélioration',
            'Durée d\'amortissement Ravalement avec amélioration',
            'Electricité',
            'Durée d\'amortissement Electricité',
            'Plomberie Sanitaire',
            'Durée d\'amortissement Plomberie Sanitaire',
            'Ascenseurs',
            'Durée d\'amortissement Ascenseurs',
        ];

        array_push($writeData, $headers);
        $modeleDamortissements = $this->fetchBySimulationId($simulationId);

        foreach ($modeleDamortissements as $modeleDamortissement) {
            $row = [
                $modeleDamortissement->getNumero(),
                $modeleDamortissement->getNom(),
                $modeleDamortissement->getDureeReprise(),
                $modeleDamortissement->getStructureVentilation(),
                $modeleDamortissement->getStructureAmortissement(),
                $modeleDamortissement->getMenuiserieVentilation(),
                $modeleDamortissement->getMenuiserieAmortissement(),
                $modeleDamortissement->getChauffageVentilation(),
                $modeleDamortissement->getChauffageAmortissement(),
                $modeleDamortissement->getEtancheiteVentilation(),
                $modeleDamortissement->getEtancheiteAmortissement(),
                $modeleDamortissement->getRavalementVentilation(),
                $modeleDamortissement->getRavalementAmortissement(),
                $modeleDamortissement->getElectriciteVentilation(),
                $modeleDamortissement->getElectriciteAmortissement(),
                $modeleDamortissement->getPlomberieVentilation(),
                $modeleDamortissement->getPlomberieAmortissement(),
                $modeleDamortissement->getAscenseursVentilation(),
                $modeleDamortissement->getAscenseursAmortissement(),
            ];

            array_push($writeData, $row);
        }

        $sheet->setTitle('modele_amortissement');
        $sheet->setCellValue('A1', 'Modèle d\'amortissement technique');
        $sheet->fromArray($writeData, null, 'A2', true);
        $sheet->getRowDimension(2)->setRowHeight(40);

        for ($i = 1; $i <= 19; $i++) {
            $column = $this->columnLetter($i);
            $sheet->getColumnDimension($column)->setWidth(20);
            $sheet->getStyle($column . '2')->getAlignment()->setWrapText(true);
            $sheet->getStyle($column . '2')->getFont()->setBold(true);
        }

        $sheet->getColumnDimension('A')->setwidth(40);
        $sheet->getStyle('A1')->getFont()->setBold(true);
        $sheet->getStyle('A2:S' . (count($modeleDamortissements) + 2))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A2:s' . (count($modeleDamortissements) + 2))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2:s' . (count($modeleDamortissements) + 2))->getFont()->setSize(10);

        return $sheet;
    }

    public function importModelesAmortissement(Request $request, string $simulationId): string
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
            $isModelesAmortissement = false;

            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $worksheetTitle = $worksheet->getTitle();

                if ($worksheetTitle !== 'modele_amortissement') {
                    continue;
                }

                $isModelesAmortissement = true;
            }

            if ($isModelesAmortissement === false) {
                throw HTTPException::badRequest('Il n\'y a pas de feuille correcte, veuillez vérifier à nouveau.');
            }

            $data['modele_amortissement'] = [
                'columnNames' => [],
                'columnValues' => [],
            ];

            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $worksheetTitle = $worksheet->getTitle();

                if ($worksheetTitle !== 'modele_amortissement') {
                    continue;
                }

                foreach ($worksheet->getRowIterator() as $row) {
                    $rowIndex = $row->getRowIndex();

                    if ($rowIndex > 2) {
                        $data['modele_amortissement']['columnValues'][$rowIndex] = [];
                    }

                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(true);

                    foreach ($cellIterator as $cell) {
                        if ($rowIndex === 2) {
                            $data['modele_amortissement']['columnNames'][] = $cell->getCalculatedValue();
                        }

                        if ($rowIndex < 2) {
                            continue;
                        }

                        if ($rowIndex <= 2) {
                            continue;
                        }

                        $data['modele_amortissement']['columnValues'][$rowIndex][] = $cell->getCalculatedValue();
                    }
                }
            }

            $data['modele_amortissement']['columnValues'] = array_values($data['modele_amortissement']['columnValues']);
            $oldModelesAmortissements = $this->fetchBySimulationId($simulationId);

            // check if current database numero exists on import file
            foreach ($oldModelesAmortissements as $modelesAmortissement) {
                $numero = $modelesAmortissement->getNumero();
                $uuId = $modelesAmortissement->getId();
                $flag = false;

                foreach ($data['modele_amortissement']['columnValues'] as $item) {
                    if (strval($item[0]) !== $numero) {
                        continue;
                    }

                    $flag = true;
                }

                if ($flag === true) {
                    continue;
                }

                $this->remove($uuId);
            }

            // save database from import file data

            foreach ($data['modele_amortissement']['columnValues'] as $item) {
                $uuId = null;

                foreach ($oldModelesAmortissements as $modelesAmortissement) {
                    $numero =  $modelesAmortissement->getNumero();

                    if (strval($item[0]) !== $numero) {
                        continue;
                    }

                    $uuId = $modelesAmortissement->getId();
                }

                $newModeleAmortissement = $this->factory->createModeleDamortissement(
                    $uuId,
                    strval($item[0]),
                    $item[1],
                    $item[2],
                    $item[3],
                    $item[5],
                    $item[7],
                    $item[9],
                    $item[11],
                    $item[13],
                    $item[15],
                    $item[17],
                    $item[4],
                    $item[6],
                    $item[8],
                    $item[10],
                    $item[12],
                    $item[14],
                    $item[16],
                    $item[18],
                    $simulationId
                );

                $this->save($newModeleAmortissement);
            }
        }

        return 'Modèle d\'amortissement importé.';
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

    public function cloneModeleDamortissement(Simulation $newSimulation, Simulation $oldSimulation): void
    {
        $objects = $this->damortissementDao->findBySimulationID($oldSimulation->getId());
        foreach ($objects as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
        }
    }

    public function fusionModeleDamortissement(Simulation $newSimulation, Simulation $oldSimulation1, Simulation $oldSimulation2): void
    {
        $objects1 = $this->damortissementDao->findBySimulationID($oldSimulation1->getId());
        $objects2 = $this->damortissementDao->findBySimulationID($oldSimulation2->getId());
        foreach ($objects1 as $key => $object) {
            $newObject = clone $object;
            $newObject->setNumero(strval($key + 1));
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
        }
        foreach ($objects2 as $key => $object) {
            $newObject = clone $object;
            $newObject->setNumero(strval(count($objects1) + $key + 1));
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
        }
    }
}
