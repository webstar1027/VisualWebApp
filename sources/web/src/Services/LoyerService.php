<?php

declare(strict_types=1);

namespace App\Services;

use App\Dao\LoyerDao;
use App\Dao\LoyerPeriodiqueDao;
use App\Dao\SimulationDao;
use App\Exceptions\HTTPException;
use App\Model\Factories\LoyerFactory;
use App\Model\Loyer;
use App\Model\LoyerPeriodique;
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
use function array_values;
use function chr;
use function count;
use function intval;
use function Safe\json_encode;
use function strval;

final class LoyerService
{
    /** @var LoyerDao */
    private $loyerDao;

    /** @var SimulationDao */
    private $simulationDao;

    /** @var LoyerFactory */
    private $factory;

    /** @var LoyerPeriodiqueDao */
    private $periodiqueDao;

    public function __construct(LoyerDao $loyerDao, LoyerFactory $factory, LoyerPeriodiqueDao $periodiqueDao, SimulationDao $simulationDao)
    {
        $this->loyerDao = $loyerDao;
        $this->factory = $factory;
        $this->periodiqueDao = $periodiqueDao;
        $this->simulationDao = $simulationDao;
    }

    /**
     * @throws HTTPException
     */
    public function save(Loyer $loyer): void
    {
        try {
            $this->loyerDao->save($loyer);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Ce loyer existe déjà.', $e);
        }
    }

    /**
     * @throws HTTPException
     * @throws TDBMException
     */
    public function remove(string $loyerUUID): void
    {
        try {
            $loyer = $this->loyerDao->getById($loyerUUID);
            if ($loyer->getDeletable() === false) {
                throw HTTPException::badRequest('Ce loyer ne peut pas être supprimé.');
            }
            $this->loyerDao->delete($loyer, true);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Ce loyer n\'existe pas.', $e);
        }
    }

    /**
     * @return Loyer[]|ResultIterator
     */
    public function findBySimulationAndType(string $simulationId, int $type): ResultIterator
    {
        return $this->loyerDao->findBySimulationIDAndType($simulationId, $type);
    }

    /**
     * @return Loyer[]|ResultIterator
     */
    public function findByNomAndType(string $nom, int $type): ResultIterator
    {
        return $this->loyerDao->findByNomAndType($nom, $type);
    }

    /**
     * @throws HTTPException
     * @throws JsonException
     * @throws TDBMException
     */
    public function createDefaultLoyer(Simulation $newSimulation): void
    {
        foreach (Loyer::DEFAULT_CONFIG as $nom => $type) {
            $loyer = $this->factory->constructLoyer(
                null,
                $newSimulation->getId(),
                $nom,
                $type === Loyer::TYPE_AUTRES_LOYER?0:null,
                null,
                null,
                $type,
                null
            );

            $loyer->setDeletable(false);
            $this->save($loyer);

            $this->createDefaultPeriodique($loyer);
        }
    }

    private function createDefaultPeriodique(Loyer $loyer): void
    {
        for ($i = 1; $i <= LoyerPeriodique::NUMBER_OF_ITERATIONS; $i++) {
            $loyerPeriodique = new LoyerPeriodique($loyer, $i);
            $loyerPeriodique->setValue(null);
            $this->periodiqueDao->save($loyerPeriodique);
        }
    }

    public function exportProduitLoyer(Worksheet $sheet, string $simulationId): Worksheet
    {
        $simulation = $this->simulationDao->getById($simulationId);
        $anneeDeReference = $simulation->getAnneeDeReference();
        $loyerLongements = $this->findBySimulationAndType($simulationId, Loyer::TYPE_LOGEMENTS);

        $longementsData = [];
        $longementsData[] = [
            'Nombre pondéré de logements',
            count($loyerLongements) > 0 ? $loyerLongements[0]->getNombreLogements():null,
        ];
        $longementsData[] = [
            'Montant des loyers théoriques avant RLS',
            count($loyerLongements) > 0 ? $loyerLongements[0]->getMontantRls():null,
        ];

        $solidariteData = [];
        $solidariteHeaders = [null];
        $autresLoyerHeaders = [null, 'Taux d\'évolution'];

        for ($i = 0; $i < 50; $i++) {
            array_push($solidariteHeaders, intval($anneeDeReference) + $i);
            array_push($autresLoyerHeaders, intval($anneeDeReference) + $i);
        }

        array_push($solidariteData, $solidariteHeaders);

        $solidarites = $this->findBySimulationAndType($simulationId, Loyer::TYPE_SOLIDARITE);

        foreach ($solidarites as $key => $value) {
            $row = [];
            $row[] = $value->getNom();
            $periodiques = $value->getLoyerPeriodique();

            foreach ($periodiques as $periodique) {
                $row[] = $periodique->getValue();
            }

            array_push($solidariteData, $row);
        }

        $autresLoyerData = [];
        array_push($autresLoyerData, $autresLoyerHeaders);

        $autresLoyers = $this->findBySimulationAndType($simulationId, Loyer::TYPE_AUTRES_LOYER);

        foreach ($autresLoyers as $autresLoyer) {
            $row = [];
            $row[] = $autresLoyer->getNom();
            $row[] = $autresLoyer->getTauxDevolution();
            $periodiques = $autresLoyer->getLoyerPeriodique();

            foreach ($periodiques as $periodique) {
                $row[] = $periodique->getValue();
            }

            array_push($autresLoyerData, $row);
        }

        $totalRow = ['Total'];

        for ($i = 2; $i <= count($autresLoyerHeaders); $i++) {
            $totalRow[] = null;
        }

        array_push($autresLoyerData, $totalRow);

        $sheet->setTitle('produits_loyers');
        $sheet->setCellValue('A1', 'Produits - Loyers');
        $sheet->setCellValue('A2', 'Loyers des logements de l\'année N');
        $sheet->setCellValue('A6', 'Réduction de loyer de solidarité');
        $sheet->setCellValue('A12', 'Autres loyers');

        $sheet->fromArray($longementsData, null, 'A3', true);
        $sheet->fromArray($solidariteData, null, 'A7', true);
        $sheet->fromArray($autresLoyerData, null, 'A13', true);

        $sheet->getStyle('A1:A' . (count($autresLoyers) + 14))->getFont()->setBold(true);
        $sheet->getStyle('B2:AY2')->getFont()->setBold(true);
        $sheet->getStyle('B7:AY7')->getFont()->setBold(true);
        $sheet->getStyle('B12:AZ13')->getFont()->setBold(true);

        $sheet->getStyle('A3:AZ' . (count($autresLoyers) + 14))->getFont()->setSize(10);
        $sheet->getStyle('A6')->getFont()->setSize(11);
        $sheet->getStyle('A12')->getFont()->setSize(11);
        $sheet->getStyle('A1:AZ' . (count($autresLoyers) + 14))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getColumnDimension('A')->setwidth(40);
        $sheet->getColumnDimension('B')->setwidth(15);

        $sheet->getStyle('A3:B4')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('B7:AY7')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A8:AY10')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('B13:AZ13')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A14:AZ' . (count($autresLoyers) + 14))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        for ($i = 2; $i <= count($autresLoyerHeaders); $i++) {
            $column = $this->columnLetter($i);
            $sheet->setCellValue(
                $column . (count($autresLoyers) + 14),
                '=SUM(' . $column . '14:' . $column . (count($autresLoyers) + 13) . ')'
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

    public function importProduitLoyer(Request $request, string $simulationId): string
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
            $isProduitLoyer = false;
            // Get data from imported excel data

            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $worksheetTitle = $worksheet->getTitle();

                if ($worksheetTitle !== 'produits_loyers') {
                    continue;
                }

                $isProduitLoyer = true;
            }

            if ($isProduitLoyer === false) {
                throw HTTPException::badRequest('Il n\'y a pas de feuille correcte, veuillez vérifier à nouveau.');
            }

            $data['produit_loyer'] = [
                'columnNames' => [],
                'columnValues' => [],
            ];

            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $worksheetTitle = $worksheet->getTitle();

                if ($worksheetTitle !== 'produits_loyers') {
                    continue;
                }

                foreach ($worksheet->getRowIterator() as $row) {
                    $rowIndex = $row->getRowIndex();

                    if ($rowIndex < 8 || $rowIndex === 11 || $rowIndex === 12) {
                        continue;
                    }

                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(true);

                    foreach ($cellIterator as $cell) {
                        if ($rowIndex === 13) {
                            $data['produit_loyer']['columnNames'][] = $cell->getCalculatedValue();
                        } else {
                            $data['produit_loyer']['columnValues'][$rowIndex][] = $cell->getCalculatedValue();
                        }
                    }
                }
            }

            $data['produit_loyer']['columnValues'] = array_values($data['produit_loyer']['columnValues']);
            array_pop($data['produit_loyer']['columnValues']);

            $produitLoyers = $this->findBySimulationAndType($simulationId, Loyer::TYPE_AUTRES_LOYER);

            foreach ($produitLoyers as $produitLoyer) {
                $isDeleteable = $produitLoyer->getDeletable();

                if ($isDeleteable !== true) {
                    continue;
                }

                $this->remove($produitLoyer->getId());
            }

            foreach ($data['produit_loyer']['columnValues'] as $key => $value) {
                $nom = strval($value[0]);
                $periodiques = [];

                if ($key < 3) {
                    foreach ($value as $key1 => $item1) {
                        if ($key1 < 1 || $key1 > 50) {
                            continue;
                        }
                        array_push($periodiques, $item1);
                    }

                    try {
                        $solidarites = $this->findByNomAndType($nom, Loyer::TYPE_SOLIDARITE);

                        if (count($solidarites) === 0) {
                            throw HTTPException::badRequest('Ce loyer ne peut pas être supprimé.');
                        }

                        $oldSolidarite = $this->factory->constructLoyer(
                            $solidarites[0]->getId(),
                            $simulationId,
                            $nom,
                            null,
                            null,
                            null,
                            Loyer::TYPE_SOLIDARITE,
                            json_encode(['periodique' => $periodiques])
                        );

                        $this->save($oldSolidarite);
                    } catch (Throwable $e) {
                        throw HTTPException::badRequest('Impossible d\'importer des données', $e);
                    }
                } else {
                    foreach ($value as $key2 => $item2) {
                        if ($key2 < 2) {
                            continue;
                        }
                        array_push($periodiques, $item2);
                    }

                    try {
                        if ($key < 8) {
                            $autresLoyer = $this->findByNomAndType($nom, Loyer::TYPE_AUTRES_LOYER);

                            if (count($autresLoyer) === 0) {
                                throw HTTPException::badRequest('Il manque une ligne de 5 autres loyers statiques ou 5 index statiques de autres loyer ne sont pas corrects.');
                            }

                            $oldLoyer = $this->factory->constructLoyer(
                                $autresLoyer[0]->getId(),
                                $simulationId,
                                $nom,
                                $value[1],
                                null,
                                null,
                                Loyer::TYPE_AUTRES_LOYER,
                                json_encode(['periodique' => $periodiques])
                            );

                            $this->save($oldLoyer);
                        } else {
                            $newLoyer = $this->factory->constructLoyer(
                                null,
                                $simulationId,
                                $nom,
                                $value[1],
                                null,
                                null,
                                Loyer::TYPE_AUTRES_LOYER,
                                json_encode(['periodique' => $periodiques])
                            );

                            $this->save($newLoyer);
                        }
                    } catch (Throwable $e) {
                        throw HTTPException::badRequest('Impossible d\'importer des données', $e);
                    }
                }
            }
        }

        return 'Produits loyers importé';
    }

    public function cloneLoyer(Simulation $newSimulation, Simulation $oldSimulation): void
    {
        $objects = $this->loyerDao->findBySimulation($oldSimulation);
        foreach ($objects as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getLoyerPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setLoyer($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
    }

    public function fusionLoyer(Simulation $newSimulation, Simulation $oldSimulation1, Simulation $oldSimulation2): void
    {
        $parametre1 = $this->loyerDao->findBySimulationIDAndType($oldSimulation1->getId(), 0);
        $parametre2 = $this->loyerDao->findBySimulationIDAndType($oldSimulation2->getId(), 0);
        $loyer = new Loyer($newSimulation, 0);
        $loyer->setNombreLogements($parametre1[0]->getNombreLogements() + $parametre2[0]->getNombreLogements());
        $loyer->setMontantRls($parametre1[0]->getMontantRls() + $parametre2[0]->getMontantRls());
        $this->loyerDao->save($loyer);

        $solidarite = $this->loyerDao->findBySimulationIDAndType($oldSimulation1->getId(), 1);
        $autresloyer1 = $this->loyerDao->findBySimulationIDAndType($oldSimulation1->getId(), 2);
        $autresloyer2 = $this->loyerDao->findBySimulationIDAndType($oldSimulation2->getId(), 2);

        foreach ($solidarite as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getLoyerPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setLoyer($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $newPeriodique->setValue(null);
                $this->periodiqueDao->save($newPeriodique);
            }
        }

        foreach ($autresloyer1 as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getLoyerPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setLoyer($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
        foreach ($autresloyer2 as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getLoyerPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setLoyer($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
    }
}
