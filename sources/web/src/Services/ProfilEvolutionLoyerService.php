<?php

declare(strict_types=1);

namespace App\Services;

use App\Dao\ProfilEvolutionLoyerDao;
use App\Dao\ProfilEvolutionLoyerParametreDao;
use App\Dao\ProfilEvolutionLoyerPeriodiqueDao;
use App\Dao\SimulationDao;
use App\Exceptions\HTTPException;
use App\Model\Factories\ProfilEvolutionLoyerFactory;
use App\Model\ProfilEvolutionLoyer;
use App\Model\ProfilEvolutionLoyerParametre;
use App\Model\ProfilEvolutionLoyerPeriodique;
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
use function array_shift;
use function array_values;
use function chr;
use function count;
use function intval;
use function Safe\json_encode;
use function strval;

final class ProfilEvolutionLoyerService
{
    /** @var ProfilEvolutionLoyerDao */
    private $profilEvolutionLoyerDao;

    /** @var ProfilEvolutionLoyerParametreDao */
    private $profilEvolutionLoyerParametreDao;

    /** @var ProfilEvolutionLoyerPeriodiqueDao */
    private $periodiqueDao;

     /** @var SimulationDao */
    private $simulationDao;

    /** @var ProfilEvolutionLoyerFactory */
    private $factory;

    public function __construct(
        ProfilEvolutionLoyerDao $profilEvolutionLoyerDao,
        ProfilEvolutionLoyerPeriodiqueDao $periodiqueDao,
        ProfilEvolutionLoyerParametreDao $profilEvolutionLoyerParametreDao,
        SimulationDao $simulationDao,
        ProfilEvolutionLoyerFactory $factory
    ) {
        $this->profilEvolutionLoyerDao = $profilEvolutionLoyerDao;
        $this->periodiqueDao = $periodiqueDao;
        $this->profilEvolutionLoyerParametreDao = $profilEvolutionLoyerParametreDao;
        $this->simulationDao = $simulationDao;
        $this->factory = $factory;
    }

    /**
     * @throws HTTPException
     */
    public function save(ProfilEvolutionLoyer $profilEvolutionLoyer): void
    {
        try {
            $this->profilEvolutionLoyerDao->save($profilEvolutionLoyer);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Ce profil existe déjà.', $e);
        }
    }

    public function findProfilEvolutionLoyerPeriodique(string $uuid, int $iteration): ?ProfilEvolutionLoyerPeriodique
    {
        return $this->periodiqueDao->findOneByIDAndIteration($uuid, $iteration);
    }

    public function saveProfilEvolutionLoyerPeriodique(ProfilEvolutionLoyerPeriodique $profilEvolutionLoyerPeriodique
    ): void
    {
        $this->periodiqueDao->save($profilEvolutionLoyerPeriodique);
    }

    /**
     * @throws TDBMException
     */
    public function remove(string $id): void
    {
        try {
            $profil = $this->profilEvolutionLoyerDao->getById($id);

            foreach ($profil->getProfilsEvolutionLoyersPeriodique() as $periodique) {
                $this->periodiqueDao->delete($periodique);
            }
            $this->profilEvolutionLoyerDao->delete($profil);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Ce profil n\'existe pas.', $e);
        }
    }

    /**
     * @return ProfilEvolutionLoyer[]|ResultIterator
     */
    public function fetchBySimulationId(string $simulationID): ResultIterator
    {
        return $this->profilEvolutionLoyerDao->findBySimulationID($simulationID);
    }

    /**
     * @return ProfilEvolutionLoyer[]|ResultIterator
     */
    public function fetchBySimulationIdAndNumero(string $simulationID, string $numero): ResultIterator
    {
        return $this->profilEvolutionLoyerDao->fetchBySimulationIdAndNumero($simulationID, $numero);
    }

    /**
     * @throws TDBMException
     */
    public function createProfilEvolutionLoyerParametre(Simulation $simulationID): void
    {
        $profilEvolutionLoyerParametre = new ProfilEvolutionLoyerParametre($simulationID, false);
        $this->profilEvolutionLoyerParametreDao->save($profilEvolutionLoyerParametre);
    }

    public function exportProfilsEvolutionLoyers(Worksheet $sheet, string $simulationId): Worksheet
    {
        $simulation = $this->simulationDao->getById($simulationId);
        $anneeDeReference = $simulation->getAnneeDeReference();
        $profilsEvolutionLoyers = $this->fetchBySimulationId($simulationId);
        $profilEvolutionLoyerParametre = $this->profilEvolutionLoyerParametreDao->findBySimulationID($simulationId);

        $writeData = [];
        $headers = ['N° profil', 'Nom du profil d\'évolution des loyers', 'Appliquer IRL (*)'];

        for ($i = 0; $i < ProfilEvolutionLoyerPeriodique::NUMBER_OF_ITERATIONS; $i++) {
            array_push($headers, strval(intval($anneeDeReference) + $i) . ' S1');
            array_push($headers, strval(intval($anneeDeReference) + $i) . ' S2');
        }

        array_push($writeData, $headers);

        foreach ($profilsEvolutionLoyers as $profilsEvolutionLoyer) {
            $numero = $profilsEvolutionLoyer->getNumero();
            $nom = $profilsEvolutionLoyer->getNom();
            $appliquerIrl = $profilsEvolutionLoyer->getAppliquerIrl() ? 'Oui' : 'Non';
            $row = [$numero, $nom, $appliquerIrl];

            $profilsEvolutionLoyerPeriodiques = $profilsEvolutionLoyer->getProfilsEvolutionLoyersPeriodique();

            foreach ($profilsEvolutionLoyerPeriodiques as $profilsEvolutionLoyerPeriodique) {
                array_push($row, $profilsEvolutionLoyerPeriodique->getS1());
                array_push($row, $profilsEvolutionLoyerPeriodique->getS2());
            }

            array_push($writeData, $row);
        }

        $sheet->setTitle('profils');
        $sheet->setCellValue('A1', 'Profil d\'évolution des loyers');
        $sheet->setCellValue('A2', 'Plafonnement des loyers pratiqués au loyer plafond');

        if (count($profilEvolutionLoyerParametre) > 0) {
            $sheet->setCellValue('B2', $profilEvolutionLoyerParametre[0]->getPlafonnementDesLoyersPratiquesAuLoyerPlafond() ? 'Oui': 'Non');
        } else {
            $sheet->setCellValue('B2', null);
        }

        $sheet->fromArray($writeData, null, 'A3', true);
        $sheet->getColumnDimension('A')->setwidth(50);
        $sheet->getColumnDimension('B')->setwidth(40);
        $sheet->getColumnDimension('C')->setwidth(20);
        $sheet->getStyle('A1:A2')->getFont()->setBold(true);
        $sheet->getStyle('A3:CY3')->getFont()->setBold(true);
        $sheet->getStyle('A2:CY' . (count($profilsEvolutionLoyers) + 3))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2:CY' . (count($profilsEvolutionLoyers) + 3))->getFont()->setSize(10);
        $sheet->getStyle('A2:B2')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A3:CY' . (count($profilsEvolutionLoyers) + 3))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        return $sheet;
    }

    public function importProfilsEvolutionLoyers(Request $request, string $simulationId): string
    {
        $file = $request->files->get('file');
        $extension = $file->getclientOriginalExtension();
        $simulation = $this->simulationDao->getById($simulationId);

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
            $isProfilEvolutionLoyer = false;

            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $worksheetTitle = $worksheet->getTitle();

                if ($worksheetTitle !== 'profils') {
                    continue;
                }

                $isProfilEvolutionLoyer = true;
            }

            if ($isProfilEvolutionLoyer === false) {
                throw HTTPException::badRequest('Il n\'y a pas de feuille correcte, veuillez vérifier à nouveau.');
            }

            $data['profils'] = [
                'columnNames' => [],
                'columnValues' => [],
            ];

            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $worksheetTitle = $worksheet->getTitle();

                if ($worksheetTitle !== 'profils') {
                    continue;
                }

                foreach ($worksheet->getRowIterator() as $row) {
                    $rowIndex = $row->getRowIndex();

                    if ($rowIndex > 3) {
                        $data['profils']['columnValues'][$rowIndex] = [];
                    }

                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(true);

                    foreach ($cellIterator as $cell) {
                        if ($rowIndex === 3) {
                            $data['profils']['columnNames'][] = $cell->getCalculatedValue();
                        }

                        if ($rowIndex < 3) {
                            continue;
                        }

                        if ($rowIndex <= 3) {
                            continue;
                        }

                        $data['profils']['columnValues'][$rowIndex][] = $cell->getCalculatedValue();
                    }
                }
            }

            $data['profils']['columnValues'] = array_values($data['profils']['columnValues']);
            $oldProfilsEvolutionLoyers = $this->fetchBySimulationId($simulationId);

            // check if current database numero exists on import file
            foreach ($oldProfilsEvolutionLoyers as $profilsEvolutionLoyers) {
                $numero = $profilsEvolutionLoyers->getNumero();
                $uuId = $profilsEvolutionLoyers->getId();
                $flag = false;

                foreach ($data['profils']['columnValues'] as $item) {
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

            // save data
            foreach ($data['profils']['columnValues'] as $record) {
                $profilEvolutionLoyer = $this->profilEvolutionLoyerDao->findOneByNumeroAndSimulation(strval($record[0]), $simulation);

                $isEdit = true;
                if (empty($profilEvolutionLoyer)) {
                    $isEdit = false;
                }

                $numero = $record[0];
                $nom = $record[1];
                $appliquerIrl = $record[2] === 'Oui';
                array_shift($record);
                array_shift($record);
                array_shift($record);

                $s1 = [];
                $s2 = [];

                foreach ($record as $key => $value) {
                    if ($key % 2 === 0) {
                        array_push($s1, $value);
                    } else {
                        array_push($s2, $value);
                    }
                }

                $periodique = [
                    's1' => $s1,
                    's2' => $s2,
                ];

                $profilEvolutionLoyer = $this->factory->createProfilEvolutionLoyer(strval($numero), $simulationId, $nom, $appliquerIrl, json_encode($periodique), $isEdit);
            }
        }

        return 'Profil d\'évolution des loyers importé.';
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

    public function cloneProfilEvolutionLoyer(Simulation $newSimulation, Simulation $oldSimulation): void
    {
        $objects = $this->profilEvolutionLoyerDao->findBySimulationId($oldSimulation->getId());
        foreach ($objects as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getProfilsEvolutionLoyersPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setProfilEvolutionLoyer($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
    }

    public function fusionProfilEvolutionLoyer(Simulation $newSimulation, Simulation $oldSimulation1, Simulation $oldSimulation2): void
    {
        $objects1 = $this->profilEvolutionLoyerDao->findBySimulationId($oldSimulation1->getId());
        $objects2 = $this->profilEvolutionLoyerDao->findBySimulationId($oldSimulation2->getId());
        foreach ($objects1 as $key => $object) {
            $newObject = clone $object;
            $newObject->setNumero(strval($key + 1));
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getProfilsEvolutionLoyersPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setProfilEvolutionLoyer($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
        foreach ($objects2 as $key => $object) {
            $newObject = clone $object;
            $newObject->setNumero(strval(count($objects1) + $key + 1));
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getProfilsEvolutionLoyersPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setProfilEvolutionLoyer($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
    }
}
