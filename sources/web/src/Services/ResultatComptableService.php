<?php

declare(strict_types=1);

namespace App\Services;

use App\Dao\ResultatComptableDao;
use App\Dao\ResultatComptablePeriodiqueDao;
use App\Dao\SimulationDao;
use App\Exceptions\HTTPException;
use App\Model\Factories\ResultatComptableFactory;
use App\Model\ResultatComptable;
use App\Model\ResultatComptablePeriodique;
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
use function array_push;
use function array_shift;
use function array_values;
use function chr;
use function count;
use function in_array;
use function intval;
use function Safe\json_encode;
use function strval;

final class ResultatComptableService
{
    /** @var ResultatComptableDao */
    private $resultatComptableDao;
    /** @var ResultatComptableFactory */
    private $factory;
    /** @var ResultatComptablePeriodiqueDao */
    private $periodiqueDao;
    /** @var SimulationDao */
    private $simulationDao;

    public function __construct(
        ResultatComptableDao $resultatComptableDao,
        ResultatComptableFactory $factory,
        SimulationDao $simulationDao,
        ResultatComptablePeriodiqueDao $periodiqueDao
    ) {
        $this->resultatComptableDao = $resultatComptableDao;
        $this->factory = $factory;
        $this->periodiqueDao = $periodiqueDao;
        $this->simulationDao = $simulationDao;
    }

    public function save(ResultatComptable $resultatComptable): void
    {
        try {
            $this->resultatComptableDao->save($resultatComptable);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Ce résultat comptable existe déjà.', $e);
        }
    }

    /**
     * @throws HTTPException
     * @throws TDBMException
     */
    public function remove(string $resultatComptableUUID): void
    {
        try {
            $resultatComptable = $this->resultatComptableDao->getById($resultatComptableUUID);
            if ($resultatComptable->getDeletable() === false) {
                throw HTTPException::badRequest('Ce résultat comptable ne peut pas être supprimé.');
            }
            $this->resultatComptableDao->delete($resultatComptable, true);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Ce résultat comptable n\'existe pas.', $e);
        }
    }

    /**
     * @return ResultIterator|ResultatComptable[]
     */
    public function fetchBySimulationId(string $simulationID): ResultIterator
    {
        return $this->resultatComptableDao->findBySimulationID($simulationID);
    }

    /**
     * @throws HTTPException
     * @throws JsonException
     * @throws TDBMException
     */
    public function createDefaultResultatComptable(Simulation $newSimulation): void
    {
        foreach (ResultatComptable::DEFAULT_CONFIG as $label) {
            $resultatComptable = $this->factory->createResultatComptable(null, $newSimulation->getId(), $label, null);
            $resultatComptable->setDeletable(false);
            $this->save($resultatComptable);
            $this->createDefaultPeriodique($resultatComptable);
        }
    }

    private function createDefaultPeriodique(ResultatComptable $resultatComptable): void
    {
        for ($i = 1; $i <= ResultatComptablePeriodique::NUMBER_OF_ITERATIONS; $i++) {
            $resutatPeriodique = new ResultatComptablePeriodique($resultatComptable, $i);
            $resutatPeriodique->setValue(null);
            $this->periodiqueDao->save($resutatPeriodique);
        }
    }

    public function findUUID(string $libelle): ResultIterator
    {
        return $this->resultatComptableDao->findByLibelle($libelle);
    }

    public function exportResultatCompatible(Worksheet $sheet, string $simulationId): Worksheet
    {
        $simulation = $this->simulationDao->getById($simulationId);
        $anneeDeReference = $simulation->getAnneeDeReference();
        $resultatComptables = $this->fetchBySimulationId($simulationId);

        $writeData = [];
        $headers = ['Libellé'];

        for ($i = 0; $i < ResultatComptablePeriodique::NUMBER_OF_ITERATIONS; $i++) {
            array_push($headers, intval($anneeDeReference) + $i);
        }

        array_push($writeData, $headers);

        foreach ($resultatComptables as $resultatComptable) {
            $row = [];
            $libelle = $resultatComptable->getLibelle();
            array_push($row, $libelle);

            $resultatComptablePeriodiques = $resultatComptable->getResultatComptablePeriodique();

            foreach ($resultatComptablePeriodiques as $resultatComptablePeriodique) {
                array_push($row, $resultatComptablePeriodique->getValue() ?? 0);
            }

            array_push($writeData, $row);
        }

        $sheet->setTitle('donnees_a_saisir');
        $sheet->setCellValue('A1', 'Données à saisir pour le résultat comptable');
        $sheet->fromArray($writeData, null, 'A2', true);
        $sheet->getColumnDimension('A')->setwidth(70);

        for ($i = 1; $i <= count($resultatComptables)+2; $i++) {
            $sheet->getStyle('A' . $i)->getFont()->setBold(true);
        }

        for ($i= 2; $i<= 51; $i++) {
            $column = $this->columnLetter($i);
            $sheet->getStyle($column . '2')->getFont()->setBold(true);
        }

        $sheet->getStyle('A2:AY' . (count($resultatComptables)+2))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A1:AY' . (count($resultatComptables)+2))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2:AY' . (count($resultatComptables)+2))->getFont()->setSize(10);

        return $sheet;
    }

    public function importResultatCompatible(Request $request, string $simulationId): string
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
            $changedIds = [];
            $isResultatCompatible = false;
            // Get data from imported excel data

            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $worksheetTitle = $worksheet->getTitle();

                if ($worksheetTitle !== 'donnees_a_saisir') {
                    continue;
                }

                $isResultatCompatible = true;
            }

            if ($isResultatCompatible === false) {
                throw HTTPException::badRequest('Il n\'y a pas de feuille correcte, veuillez vérifier à nouveau.');
            }

            $data['resultat_comptable'] = [
                'columnNames' => [],
                'columnValues' => [],
            ];

            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $worksheetTitle = $worksheet->getTitle();

                if ($worksheetTitle !== 'donnees_a_saisir') {
                    continue;
                }

                foreach ($worksheet->getRowIterator() as $row) {
                    $rowIndex = $row->getRowIndex();

                    if ($rowIndex > 2) {
                        $data['resultat_comptable']['columnValues'][$rowIndex] = [];
                    }

                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(true);

                    foreach ($cellIterator as $cell) {
                        if ($rowIndex === 2) {
                            $data['resultat_comptable']['columnNames'][] = $cell->getCalculatedValue();
                        }

                        if ($rowIndex < 2) {
                            continue;
                        }

                        if ($rowIndex <= 2) {
                            continue;
                        }

                        $data['resultat_comptable']['columnValues'][$rowIndex][] = $cell->getCalculatedValue();
                    }
                }
            }

            $data['resultat_comptable']['columnValues'] = array_values($data['resultat_comptable']['columnValues']);

            foreach ($data['resultat_comptable']['columnValues'] as $item) {
                $resultatCompatableArray = $this->findUUID($item[0]);

                $label = strval($item[0]);
                array_shift($item);
                $periodique['periodique'] = $item;

                try {
                    if (count($resultatCompatableArray) > 0) {
                        $resultatComptable = $this->factory->createResultatComptable($resultatCompatableArray[0]->getId(), $simulationId, $label, json_encode($periodique));

                        $this->save($resultatComptable);
                        $changedIds[] = $resultatComptable->getId();
                    } else {
                        $resultatComptable = $this->factory->createResultatComptable(null, $simulationId, $label, json_encode($periodique));

                        $this->save($resultatComptable);
                        $changedIds[] = $resultatComptable->getId();
                    }
                } catch (Throwable $e) {
                    throw HTTPException::badRequest('Il y a une erreur à l\'importation.', $e);
                }
            }
            $allResultatComptables = $this->fetchBySimulationId($simulationId);

            foreach ($allResultatComptables as $_resultatComptable) {
                if (in_array($_resultatComptable->getId(), $changedIds)) {
                    continue;
                }

                $this->remove($_resultatComptable->getId());
            }
        }

        return 'Résultat comptable importé.';
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

    public function cloneResultatComptable(Simulation $newSimulation, Simulation $oldSimulation): void
    {
        $objects = $this->resultatComptableDao->findBySimulationID($oldSimulation->getId());
        foreach ($objects as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getResultatComptablePeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setResultatComptable($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
    }

    public function fusionResultatComptable(Simulation $newSimulation, Simulation $oldSimulation1, Simulation $oldSimulation2): void
    {
        $objects1 = $this->resultatComptableDao->findBySimulationID($oldSimulation1->getId());
        $objects2 = $this->resultatComptableDao->findBySimulationID($oldSimulation2->getId());
        foreach ($objects1 as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getResultatComptablePeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setResultatComptable($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
        foreach ($objects2 as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getResultatComptablePeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setResultatComptable($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
    }
}
