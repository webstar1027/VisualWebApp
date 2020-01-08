<?php

declare(strict_types=1);

namespace App\Services;

use App\Dao\TypeEmpruntDao;
use App\Dao\TypeEmpruntPeriodiqueDao;
use App\Exceptions\HTTPException;
use App\Model\Factories\TypeEmpruntFactory;
use App\Model\Simulation;
use App\Model\TypeEmprunt;
use App\Model\TypeEmpruntPeriodique;
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

final class TypeEmpruntService
{
    /** @var TypeEmpruntDao */
    private $typeEmpruntDao;
    /** @var TypeEmpruntPeriodiqueDao */
    private $periodiqueDao;
     /** @var TypeEmpruntFactory */
    private $factory;

    public function __construct(TypeEmpruntDao $typeEmpruntDao, TypeEmpruntPeriodiqueDao $periodiqueDao, TypeEmpruntFactory $factory)
    {
        $this->typeEmpruntDao = $typeEmpruntDao;
        $this->periodiqueDao = $periodiqueDao;
        $this->factory = $factory;
    }

    public function save(TypeEmprunt $typeEmprunt): void
    {
        try {
            $this->typeEmpruntDao->save($typeEmprunt);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('La sauvegarde a échouée.', $e);
        }
    }

    /**
     * @throws TDBMException
     */
    public function findTypeEmpruntPeriodique(string $id, int $iteration): TypeEmpruntPeriodique
    {
        return $this->periodiqueDao->findOneByIDAndIteration($id, $iteration);
    }

    public function saveTypeEmpruntPeriodique(TypeEmpruntPeriodique $typeEmpruntPeriodique): void
    {
        $this->periodiqueDao->save($typeEmpruntPeriodique);
    }

    /**
     * @return TypeEmprunt[]|ResultIterator
     */
    public function fetchByNumero(string $numero): ResultIterator
    {
        return $this->typeEmpruntDao->findByNumero($numero);
    }

    /**
     * @throws TDBMException
     */
    public function remove(string $id): void
    {
        try {
            $typeEmprunt = $this->typeEmpruntDao->getById($id);
            foreach ($typeEmprunt->getTypesEmpruntsPeriodique() as $typeEmpruntPeriodique) {
                $this->periodiqueDao->delete($typeEmpruntPeriodique);
            }
            $this->typeEmpruntDao->delete($typeEmprunt);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Ce type d\'emprunt n\'existe pas.', $e);
        }
    }

    /**
     * @return TypeEmprunt[]|ResultIterator
     */
    public function fetchBySimulationId(string $simulationID): ResultIterator
    {
        return $this->typeEmpruntDao->findBySimulationID($simulationID);
    }

    public function exportTypesEmprunts(Worksheet $sheet, string $simulationId): Worksheet
    {
        $writeData = [];
        $headers = [
            'N° du type d\'emprunt',
            'Nom du type d\'emprunt',
            'Emprunts indexés/Livret A',
            'Taux d\'intérêt/Marge',
            'Application d\'un taux Plancher (*)',
            'Taux plancher en %',
            'Durée de l\'emprunt',
            'Taux de progressivité',
            'Durée du différé d\'amortissement',
            'Application dun taux spécifique pendant la période de différé ',
            'Taux d\'intérêt fixe de la période de différé',
            'Révisabilité',
        ];

        array_push($writeData, $headers);
        $typesEmprunts = $this->fetchBySimulationId($simulationId);

        foreach ($typesEmprunts as $typesEmprunt) {
            $tauxPlancherCheck = $typesEmprunt->getTauxPlancherCheck() === true ? 'Oui': 'Non';
            $livretA = $typesEmprunt->getLivretA();
            $livisability = $typesEmprunt->getRevisability();

            if ($livretA === true) {
                $livretA = 'Oui';
            }

            if ($livretA === false) {
                $livretA = 'Non';
            }

            if ($livisability === 0) {
                $livisability = 'Simple';
            }

            if ($livisability === 1) {
                $livisability = 'Double';
            }

            if ($livisability === 2) {
                $livisability = 'Double limité';
            }

            $row = [
                $typesEmprunt->getNumero(),
                $typesEmprunt->getNom(),
                $livretA,
                $typesEmprunt->getMargeEmprunt(),
                $tauxPlancherCheck,
                $typesEmprunt->getTauxPlancher(),
                $typesEmprunt->getDureeEmprunt(),
                $typesEmprunt->getTauxProgressivite(),
                $typesEmprunt->getDureeAmortissement(),
                null,
                $typesEmprunt->getTauxInteret(),
                $livisability,

            ];

            array_push($writeData, $row);
        }

        $sheet->setTitle('types_emprunts');
        $sheet->setCellValue('A1', 'Type d\'emprunts');
        $sheet->fromArray($writeData, null, 'A2', true);
        $sheet->getRowDimension(2)->setRowHeight(54);

        for ($i = 1; $i <= 12; $i++) {
            $column = $this->columnLetter($i);
            $sheet->getColumnDimension($column)->setWidth(16);
            $sheet->getStyle($column . '2')->getAlignment()->setWrapText(true);
        }

        for ($i = 3; $i < count($typesEmprunts) + 3; $i++) {
            $sheet->setCellValue(
                'J' . $i,
                '=(K' . $i . '- H' . $i . ')/(1 - (1 + H' . $i . ')/(1 + K' . $i . '))'
            );
        }

        $sheet->getStyle('A1')->getFont()->setBold(true);
        $sheet->getStyle('A2:L2')->getFont()->setBold(true);
        $sheet->getStyle('A2:L' . (count($typesEmprunts) + 2))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2:L' . (count($typesEmprunts) + 2))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A2:L' . (count($typesEmprunts) + 2))->getFont()->setSize(10);

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

    public function importTypesEmprunts(Request $request, string $simulationId): string
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

                if ($worksheetTitle !== 'types_emprunts') {
                    continue;
                }

                $isModelesAmortissement = true;
            }

            if ($isModelesAmortissement === false) {
                throw HTTPException::badRequest("Il n'y a pas de feuille correcte, veuillez vérifier à nouveau.");
            }

            $data['types_emprunts'] = [
                'columnNames' => [],
                'columnValues' => [],
            ];

            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $worksheetTitle = $worksheet->getTitle();

                if ($worksheetTitle !== 'types_emprunts') {
                    continue;
                }

                foreach ($worksheet->getRowIterator() as $row) {
                    $rowIndex = $row->getRowIndex();

                    if ($rowIndex > 2) {
                        $data['types_emprunts']['columnValues'][$rowIndex] = [];
                    }

                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(true);

                    foreach ($cellIterator as $cell) {
                        if ($rowIndex === 2) {
                            $data['types_emprunts']['columnNames'][] = $cell->getCalculatedValue();
                        }

                        if ($rowIndex <= 2) {
                            continue;
                        }

                        if ($rowIndex <= 2) {
                            continue;
                        }

                        $data['types_emprunts']['columnValues'][$rowIndex][] = $cell->getCalculatedValue();
                    }
                }
            }

            $data['types_emprunts']['columnValues'] = array_values($data['types_emprunts']['columnValues']);
            $oldTypesEmprunts = $this->fetchBySimulationId($simulationId);

            foreach ($oldTypesEmprunts as $typesEmprunt) {
                try {
                    $this->remove($typesEmprunt->getId());
                } catch (Throwable $e) {
                    throw HTTPException::badRequest('Vous ne pouvez pas changer ce type d\'emprunt', $e);
                }
            }

            foreach ($data['types_emprunts']['columnValues'] as $item) {
                $livretA = null;
                $tauxPlancherCheck = null;
                $livisability = null;

                if ($item[2] === 'Non') {
                    $livretA = false;
                }

                if ($item[2] === 'Oui') {
                    $livretA = true;
                }

                if ($item[4] === 'Non') {
                    $tauxPlancherCheck = false;
                }

                if ($item[4] === 'Oui') {
                    $tauxPlancherCheck = true;
                }

                if ($item[11] === 'Simple') {
                    $livisability = 0;
                } elseif ($item[11] === 'Double') {
                    $livisability = 1;
                } elseif ($item[11] === 'Double limité') {
                    $livisability = 2;
                } else {
                    throw HTTPException::badRequest("La révisabilité '" . $item[11] . "' n'existe pas.");
                }

                $newTypeEmprunt = $this->factory->createTypeEmprunt(
                    null,
                    strval($item[0]),
                    $simulationId,
                    $item[1],
                    $item[10],
                    intval($item[6]),
                    intval($item[8]),
                    $livisability,
                    $livretA,
                    $tauxPlancherCheck,
                    $item[3],
                    $item[5],
                    $item[7],
                    false
                );

                $this->save($newTypeEmprunt);
            }
        }

        return 'Types d\'emprunts importés';
    }

    public function cloneTypeEmprunt(Simulation $newSimulation, Simulation $oldSimulation): void
    {
        $objects = $this->typeEmpruntDao->findBySimulationID($oldSimulation->getId());
        foreach ($objects as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getTypesEmpruntsPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setTypesEmprunts($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
    }

    public function fusionTypeEmprunt(Simulation $newSimulation, Simulation $oldSimulation1, Simulation $oldSimulation2): void
    {
        $objects1 = $this->typeEmpruntDao->findBySimulationID($oldSimulation1->getId());
        $objects2 = $this->typeEmpruntDao->findBySimulationID($oldSimulation2->getId());
        foreach ($objects1 as $key => $object) {
            $newObject = clone $object;
            $newObject->setNumero(strval($key + 1));
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getTypesEmpruntsPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setTypesEmprunts($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
        foreach ($objects2 as $key => $object) {
            $newObject = clone $object;
            $newObject->setNumero(strval(count($objects1) + $key + 1));
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getTypesEmpruntsPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setTypesEmprunts($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
    }
}
