<?php

declare(strict_types=1);

namespace App\Services;

use App\Dao\PatrimoineDao;
use App\Dao\SimulationDao;
use App\Dao\VacanceDao;
use App\Dao\VacancePeriodiqueDao;
use App\Exceptions\HTTPException;
use App\Model\Factories\VacanceFactory;
use App\Model\Simulation;
use App\Model\Vacance;
use App\Model\VacancePeriodique;
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

class VacanceService
{
    /** @var VacanceDao */
    private $vacanceDao;
    /** @var SimulationDao */
    private $simulationDao;
    /** @var PatrimoineDao */
    private $patrimoineDao;
    /** @var VacanceFactory */
    private $factory;
    /** @var VacancePeriodiqueDao */
    private $periodiqueDao;

    public function __construct(
        VacanceDao $vacanceDao,
        VacancePeriodiqueDao $periodiqueDao,
        VacanceFactory $factory,
        SimulationDao $simulationDao,
        PatrimoineDao $patrimoineDao
    ) {
        $this->vacanceDao = $vacanceDao;
        $this->periodiqueDao = $periodiqueDao;
        $this->factory = $factory;
        $this->simulationDao = $simulationDao;
        $this->patrimoineDao = $patrimoineDao;
    }

    public function save(Vacance $vacance): void
    {
        try {
            $this->vacanceDao->save($vacance);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Cette vacance existe déjà.', $e);
        }
    }

    public function findVacancePeriodique(string $uuid, int $iteration): ?VacancePeriodique
    {
        return $this->periodiqueDao->findOneByIDAndIteration($uuid, $iteration);
    }

    public function saveProfilEvolutionLoyerPeriodique(VacancePeriodique $vacancePeriodique): void
    {
        try {
            $this->periodiqueDao->save($vacancePeriodique);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Une erreur est survenue.', $e);
        }
    }

    /**
     * @throws TDBMException
     */
    public function remove(string $id): void
    {
        $vacance = $this->vacanceDao->getById($id);
        $this->vacanceDao->delete($vacance, true);
    }

    /**
     * @return Vacance[]|ResultIterator
     */
    public function fetchBySimulationId(string $simulationID): ResultIterator
    {
        return $this->vacanceDao->findBySimulationID($simulationID);
    }

    public function cloneVacance(Simulation $newSimulation, Simulation $oldSimulation): void
    {
        $objects = $this->vacanceDao->findBySimulationID($oldSimulation->getId());
        foreach ($objects as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getVacancePeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setVacance($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
    }

    public function fusionVacance(Simulation $newSimulation, Simulation $oldSimulation1, Simulation $oldSimulation2): void
    {
        $objects1 = $this->vacanceDao->findBySimulationID($oldSimulation1->getId());
        $objects2 = $this->vacanceDao->findBySimulationID($oldSimulation2->getId());
        foreach ($objects1 as $key => $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $newObject->setNumeroGroupe($key + 1);
            $this->save($newObject);
            foreach ($object->getVacancePeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setVacance($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
        foreach ($objects2 as $key => $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $newObject->setNumeroGroupe(count($objects1) + $key + 1);
            $this->save($newObject);
            foreach ($object->getVacancePeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setVacance($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
    }

    public function exportVacance(Worksheet $sheet, string $simulationId): Worksheet
    {
        $writeData = [];
        $headers = [
            'N° groupe',
            'N° sous-groupe',
            'Information',
            'Nom du groupe',
            'Nombre de logts',
        ];

        $simulation = $this->simulationDao->getById($simulationId);
        $anneeDeReference = $simulation->getAnneeDeReference();

        for ($i = 0; $i < 50; $i++) {
            $headers[] = intval($anneeDeReference) + $i;
        }

        $writeData[] = $headers;

        $vacances = $this->fetchBySimulationId($simulationId);

        foreach ($vacances as $vacance) {
            $row = [];

            $row[] = $vacance->getNumeroGroupe();
            $row[] = $vacance->getNumeroSousGroupe();
            $row[] = $vacance->getInformation();
            $row[] = $vacance->getNomGroupe();
            $patrimoine = $this->patrimoineDao->findOneByNGroupe($simulationId, $vacance->getNumeroGroupe())[0];
            $row[] = isset($patrimoine) ? $patrimoine->getNombreLogements() : null;

            $periodiques = $vacance->getVacancePeriodique();

            foreach ($periodiques as $periodique) {
                $row[] = $periodique->getMontant();
            }

            $writeData[] = $row;
        }

        $sheet->setTitle('VI');
        $sheet->setCellValue('A1', 'Vacances identifiées');
        $sheet->fromArray($writeData, null, 'A2', true);

        $sheet = $this->setBoldSheet([
            'A1',
            'A2:BC2',
        ], $sheet);

        $sheet->getStyle('A2:BC' . (count($writeData) + 1))->getFont()->setSize(10);
        $sheet->getStyle('A1')->getFont()->setSize(11);
        $sheet->getRowDimension(2)->setRowHeight(35);
        $sheet = $this->setBorderSheet(['A2:BC' . (count($writeData) + 1)], $sheet);
        $sheet->getStyle('A2:BC' . (count($writeData) + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        for ($i = 1; $i <= 55; $i++) {
            $column = $this->columnLetter($i);
            $sheet->getColumnDimension($column)->setwidth(25);

            if ($i > 5) {
                $sheet->getColumnDimension($column)->setwidth(10);
            }

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

    public function importVacance(Request $request, string $simulationId): string
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
            $isVacances = false;
            // Get data from imported excel data

            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $worksheetTitle = $worksheet->getTitle();

                if ($worksheetTitle !== 'VI') {
                    continue;
                }

                $isVacances = true;
            }

            if ($isVacances === false) {
                throw HTTPException::badRequest('Il n\'y a pas de feuille correcte, veuillez vérifier à nouveau.');
            }

            $data['vacance_identifees'] = [
                'columnNames' => [],
                'columnValues' => [],
            ];

            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $worksheetTitle = $worksheet->getTitle();

                if ($worksheetTitle !== 'VI') {
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
                            $data['vacance_identifees']['columnNames'][] = $cell->getCalculatedValue();
                        } else {
                            $data['vacance_identifees']['columnValues'][$rowIndex][] = $cell->getCalculatedValue();
                        }
                    }
                }
            }

            $data['vacance_identifees']['columnValues'] = array_values($data['vacance_identifees']['columnValues']);

            foreach ($data['vacance_identifees']['columnValues'] as $key => $item) {
                $patrimoine = null;

                if (isset($item[0])) {
                    $patrimoines = $this->patrimoineDao->findOneByNGroupe($simulationId, intval($item[0]));

                    if (count($patrimoines) === 0) {
                        throw HTTPException::badRequest('Il n’existe pas une telle patrimoine numero - "' . $item[0] . '"');
                    }

                    $patrimoine = $patrimoines[0];
                }

                $periodique = [];

                for ($i = 5; $i < 55; $i++) {
                    $periodique[] = $item[$i];
                }

                $oldVacances = $this->vacanceDao->findOneByNumeroGroupe($simulationId, intval($item[0]));

                try {
                    if (count($oldVacances) > 0) {
                        $oldVacance = $this->factory->createVacance(
                            $oldVacances[0]->getId(),
                            intval($item[0]),
                            $patrimoine->getNSousGroupe(),
                            $patrimoine->getNomGroupe(),
                            $patrimoine->getInformations(),
                            $simulationId,
                            json_encode(['periodique' => $periodique])
                        );

                        $this->save($oldVacance);
                        $changedIds[] = $oldVacances[0]->getId();
                    } else {
                        $newVacance = $this->factory->createVacance(
                            null,
                            intval($item[0]),
                            $patrimoine->getNSousGroupe(),
                            $patrimoine->getNomGroupe(),
                            $patrimoine->getInformations(),
                            $simulationId,
                            json_encode(['periodique' => $periodique])
                        );

                        $this->save($newVacance);
                        $changedIds[] = $newVacance->getId();
                    }
                } catch (Throwable $e) {
                    throw HTTPException::badRequest('Il y a une erreur à l\'importation.', $e);
                }
            }

            $allVacances = $this->fetchBySimulationId($simulationId);

            foreach ($allVacances as $_vacance) {
                if (in_array($_vacance->getId(), $changedIds)) {
                    continue;
                }

                $this->remove($_vacance->getId());
            }
        }

        return 'Vacances identifiées importé';
    }
}
