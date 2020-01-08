<?php

declare(strict_types=1);

namespace App\Services;

use App\Dao\PatrimoineFoyerDao;
use App\Dao\PatrimoineFoyerParametreDao;
use App\Dao\PatrimoineFoyerPeriodiqueDao;
use App\Dao\SimulationDao;
use App\Exceptions\HTTPException;
use App\Model\Factories\PatrimoineFoyerFactory;
use App\Model\PatrimoineFoyer;
use App\Model\PatrimoineFoyerParametre;
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
use Throwable;
use function array_values;
use function chr;
use function count;
use function in_array;
use function intval;
use function Safe\json_encode;
use function strval;

final class PatrimoineFoyersService
{
    /** @var PatrimoineFoyerDao */
    private $patrimoineFoyerDao;
    /** @var PatrimoineFoyerParametreDao */
    private $patrimoineFoyerParametreDao;
    /** @var SimulationDao */
    private $simulationDao;
    /** @var PatrimoineFoyerPeriodiqueDao */
    private $periodiqueDao;
    /** @var PatrimoineFoyerFactory */
    private $factory;

    public function __construct(
        PatrimoineFoyerDao $patrimoineFoyerDao,
        PatrimoineFoyerParametreDao $patrimoineFoyerParametreDao,
        SimulationDao $simulationDao,
        PatrimoineFoyerPeriodiqueDao $periodiqueDao,
        PatrimoineFoyerFactory $factory
    ) {
        $this->patrimoineFoyerDao = $patrimoineFoyerDao;
        $this->patrimoineFoyerParametreDao = $patrimoineFoyerParametreDao;
        $this->simulationDao = $simulationDao;
        $this->periodiqueDao = $periodiqueDao;
        $this->factory = $factory;
    }

    /**
     * @return ResultIterator|PatrimoineFoyer[]
     */
    public function fetchBySimulationId(string $simulationID): ResultIterator
    {
        return $this->patrimoineFoyerDao->findBySimulationID($simulationID);
    }

    public function save(PatrimoineFoyer $patrimoineFoyer): void
    {
        try {
            $this->patrimoineFoyerDao->save($patrimoineFoyer);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Ce patrimoine existe déjà.', $e);
        }
    }

    public function remove(string $patrimoineFoyerUUID): void
    {
        try {
            $patrimoineFoyer = $this->patrimoineFoyerDao->getById($patrimoineFoyerUUID);
        } catch (Throwable $e) {
            throw HTTPException::notFound("Ce patrimoine n'existe pas.", $e);
        }

        $this->patrimoineFoyerDao->delete($patrimoineFoyer, true);
    }

    public function savePatrimoineFoyerParametre(string $simulationId, ?int $nombrePondereLogement): ?PatrimoineFoyerParametre
    {
        if (empty($simulationId)) {
            throw HTTPException::badRequest('Erreur sur la simulation ou le nombre pondéré.');
        }
        $patrimoineFoyerParametre = $this->patrimoineFoyerParametreDao->findOneBySimulationId($simulationId);
        if ($patrimoineFoyerParametre === null) {
            // Create a new one if it doesn't exist
            try {
                $simulation = $this->simulationDao->getById($simulationId);
            } catch (Throwable $e) {
                throw HTTPException::notFound("La simulation n'existe pas.", $e);
            }
            $patrimoineFoyerParametre = new PatrimoineFoyerParametre($simulation);
            $patrimoineFoyerParametre->setNombrePondereLogement($nombrePondereLogement);
        } else {
            // Updating the existing one
            $patrimoineFoyerParametre->setNombrePondereLogement($nombrePondereLogement);
        }
        $this->patrimoineFoyerParametreDao->save($patrimoineFoyerParametre);

        return $patrimoineFoyerParametre;
    }

    public function fetchParametreBySimulationId(string $simulationID): ?PatrimoineFoyerParametre
    {
        return $this->patrimoineFoyerParametreDao->findOneBySimulationId($simulationID);
    }

    public function clonePatrimoineFoyer(Simulation $newSimulation, Simulation $oldSimulation): void
    {
        $objects = $this->patrimoineFoyerDao->findBySimulation($oldSimulation);
        foreach ($objects as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getPatrimoineFoyersPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setPatrimoineFoyers($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
    }

    public function fusionPatrimoineFoyer(Simulation $newSimulation, Simulation $oldSimulation1, Simulation $oldSimulation2): void
    {
        $objects1 = $this->patrimoineFoyerDao->findBySimulation($oldSimulation1);
        $objects2 = $this->patrimoineFoyerDao->findBySimulation($oldSimulation2);
        foreach ($objects1 as $key => $object) {
            $newObject = clone $object;
            $newObject->setNGroupe($key + 1);
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getPatrimoineFoyersPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setPatrimoineFoyers($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
        foreach ($objects2 as $key => $object) {
            $newObject = clone $object;
            $newObject->setNGroupe(count($objects1) + $key + 1);
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getPatrimoineFoyersPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setPatrimoineFoyers($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
    }

    public function exportPatrimoineFoyers(Worksheet $sheet, string $simulationId): Worksheet
    {
        $writeData = [];

        $headers = [
            'N°groupe',
            'N° sous groupe',
            'Nom du groupe',
            'information (Champ en saisie libre)',
            'Nombre d\'équivalent logements',
            'Secteur financier',
            'Nature de l\'opération',
            'Taux réel d\'évolution des redevances',
        ];

        $simulation = $this->simulationDao->getById($simulationId);
        $anneeDeReference = $simulation->getAnneeDeReference();

        for ($i = 0; $i < 50; $i++) {
            $headers[] = intval($anneeDeReference) + $i;
        }

        $writeData[] = $headers;

        $patrimoineFoyers = $this->fetchBySimulationId($simulationId);
        $patrimoineFoyerParametres = $this->patrimoineFoyerParametreDao->findOneBySimulationId($simulationId);

        $logementsData = [];
        $logementsData[] = ['Nombre pondéré équivalent logements', isset($patrimoineFoyerParametres) ? $patrimoineFoyerParametres->getNombrePondereLogement() : null];

        foreach ($patrimoineFoyers as $patrimoineFoyer) {
            $row = [];

            $row[] = $patrimoineFoyer->getNGroupe();
            $row[] = $patrimoineFoyer->getNSousGroupe();
            $row[] = $patrimoineFoyer->getNomGroupe();
            $row[] = $patrimoineFoyer->getInformations();
            $row[] = $patrimoineFoyer->getNombreLogements();
            $row[] = $patrimoineFoyer->getSecteurFinancier();
            $row[] = $patrimoineFoyer->getNatureOperation();
            $row[] = $patrimoineFoyer->getTauxEvolutionRedevances();

            $periodiques = $patrimoineFoyer->getPatrimoineFoyersPeriodique();

            foreach ($periodiques as $periodique) {
                $row[] = $periodique->getValue();
            }

            $writeData[] = $row;
        }

        $sheet->setTitle('patrimoine_foyers');
        $sheet->setCellValue('A1', 'Patrimoine foyers');
        $sheet->setCellValue('A3', 'Foyers patrimoine de référence');
        $sheet->setCellValue('A6', 'Redevances foyers patrimoine de référence');
        $sheet->fromArray($logementsData, null, 'A4', true);
        $sheet->fromArray($writeData, null, 'A7', true);
        $sheet->getStyle('A1:A7')->getFont()->setBold(true);
        $sheet->getStyle('B7:BG7')->getFont()->setBold(true);
        $sheet->getRowDimension(7)->setRowHeight(30);
        $sheet->getColumnDimension('A')->setWidth(45);

        for ($i = 2; $i <= count($headers); $i++) {
            $column = $this->columnLetter($i);
            $sheet->getColumnDimension($column)->setWidth(25);

            if ($i > 8) {
                $sheet->getColumnDimension($column)->setWidth(10);
            }

            $sheet->getStyle($column . '7')->getAlignment()->setWrapText(true);
        }

        $sheet->getStyle('A4:BF' . (count($patrimoineFoyers) + 7))->getFont()->setSize(10);
        $sheet->getStyle('A6')->getFont()->setSize(11);
        $sheet->getStyle('A2:BF' . (count($patrimoineFoyers) + 7))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A4:B4')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A7:BF' . (count($patrimoineFoyers) + 7))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

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

    public function importPatrimoineFoyers(Request $request, string $simulationId): string
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
            $changedIds = [];
            $isPatrimoineFoyers = false;
            // Get data from imported excel data

            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $worksheetTitle = $worksheet->getTitle();

                if ($worksheetTitle !== 'patrimoine_foyers') {
                    continue;
                }

                $isPatrimoineFoyers = true;
            }

            if ($isPatrimoineFoyers === false) {
                throw HTTPException::badRequest('Il n\'y a pas de feuille correcte, veuillez vérifier à nouveau.');
            }

            $data['patrimoine_foyers'] = [
                'columnNames' => [],
                'columnValues' => [],
            ];

            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $worksheetTitle = $worksheet->getTitle();

                if ($worksheetTitle !== 'patrimoine_foyers') {
                    continue;
                }

                foreach ($worksheet->getRowIterator() as $row) {
                    $rowIndex = $row->getRowIndex();

                    if ($rowIndex < 7) {
                        continue;
                    }

                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(true);

                    foreach ($cellIterator as $cell) {
                        if ($rowIndex === 7) {
                            $data['patrimoine_foyers']['columnNames'][] = $cell->getCalculatedValue();
                        } else {
                            $data['patrimoine_foyers']['columnValues'][$rowIndex][] = $cell->getCalculatedValue();
                        }
                    }
                }
            }

            $data['patrimoine_foyers']['columnValues'] = array_values($data['patrimoine_foyers']['columnValues']);

            foreach ($data['patrimoine_foyers']['columnValues'] as $item) {
                $periodique = [];

                for ($i = 8; $i < 58; $i++) {
                    $periodique[] = $item[$i];
                }

                $oldPatrimoineFoyers = $this->patrimoineFoyerDao->findOneByNGroupe($simulationId, intval($item[0]));

                try {
                    if (count($oldPatrimoineFoyers) > 0) {
                        $oldPatrimoineFoyer = $this->factory->createPatrimoineFoyer(
                            $oldPatrimoineFoyers[0]->getId(),
                            $simulationId,
                            intval($item[0]),
                            intval($item[1]),
                            strval($item[2]),
                            $item[3],
                            $item[4],
                            strval($item[5]),
                            strval($item[6]),
                            $item[7],
                            json_encode(['periodique' => $periodique])
                        );

                        $this->save($oldPatrimoineFoyer);
                        $changedIds[] = $oldPatrimoineFoyer->getId();
                    } else {
                        $newPatrimoine = $this->factory->createPatrimoineFoyer(
                            null,
                            $simulationId,
                            intval($item[0]),
                            intval($item[1]),
                            strval($item[2]),
                            $item[3],
                            $item[4],
                            strval($item[5]),
                            strval($item[6]),
                            $item[7],
                            json_encode(['periodique' => $periodique])
                        );

                        $this->save($newPatrimoine);
                        $changedIds[] = $newPatrimoine->getId();
                    }
                } catch (Throwable $e) {
                    throw HTTPException::badRequest('Il y a une erreur à l\'importation.', $e);
                }
            }

            $allPatrimoineFoyers = $this->fetchBySimulationId($simulationId);
            foreach ($allPatrimoineFoyers as $_patrimineFoyers) {
                if (in_array($_patrimineFoyers->getId(), $changedIds)) {
                    continue;
                }
                $this->remove($_patrimineFoyers->getId());
            }
        }

        return 'Patrimoine foyers importé.';
    }
}
