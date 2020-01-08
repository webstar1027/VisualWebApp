<?php

declare(strict_types=1);

namespace App\Services;

use App\Dao\ProduitAutreDao;
use App\Dao\ProduitAutrePeriodiqueDao;
use App\Dao\SimulationDao;
use App\Exceptions\HTTPException;
use App\Model\Factories\ProduitAutreFactory;
use App\Model\ProduitAutre;
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
use function array_pop;
use function array_push;
use function array_values;
use function chr;
use function count;
use function in_array;
use function intval;
use function Safe\json_encode;
use function strval;

final class ProduitAutreService
{
    /** @var ProduitAutreDao */
    private $produitAutreDao;
    /** @var ProduitAutrePeriodiqueDao */
    private $periodiqueDao;

    /** @var SimulationDao */
    private $simulationDao;

    /** @var ProduitAutreFactory */
    private $factory;

    /** @var string[] */
    private $range = [];

     /** @var int */
    private $position = 0;

    public function __construct(ProduitAutreDao $produitAutreDao, ProduitAutreFactory $factory, SimulationDao $simulationDao, ProduitAutrePeriodiqueDao $periodiqueDao)
    {
        $this->produitAutreDao = $produitAutreDao;
        $this->periodiqueDao = $periodiqueDao;
        $this->simulationDao = $simulationDao;
        $this->factory = $factory;
    }

    /**
     * @throws HTTPException
     */
    public function save(ProduitAutre $produitAutre): void
    {
        try {
            $this->produitAutreDao->save($produitAutre);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Cet autre produit existe déjà.', $e);
        }
    }

    /**
     * @throws TDBMException
     */
    public function remove(string $produitAutreUUID): void
    {
        try {
            $produitAutre = $this->produitAutreDao->getById($produitAutreUUID);
            $this->produitAutreDao->delete($produitAutre, true);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Cet autre produit n\'existe déjà.', $e);
        }
    }

    /**
     * @return ProduitAutre[]|ResultIterator
     */
    public function findBySimulationAndType(string $simulationId, int $type): ResultIterator
    {
        return $this->produitAutreDao->findBySimulationIDAndType($simulationId, $type);
    }

    public function cloneProduitAutre(Simulation $newSimulation, Simulation $oldSimulation): void
    {
        $objects = $this->produitAutreDao->findBySimulationID($oldSimulation->getId());
        foreach ($objects as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getProduitsAutresPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setProduitsAutres($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
    }

    public function fusionProduitAutre(Simulation $newSimulation, Simulation $oldSimulation1, Simulation $oldSimulation2): void
    {
        $immobilisee1 = $this->produitAutreDao->findBySimulationIDAndType($oldSimulation1->getId(), 0);
        $immobilisse2 = $this->produitAutreDao->findBySimulationIDAndType($oldSimulation2->getId(), 0);
        foreach ($immobilisee1 as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $newObject->setCalculAutomatique(null);
            $this->save($newObject);
            foreach ($object->getProduitsAutresPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setProduitsAutres($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
        $immobilisse = $this->produitAutreDao->findBySimulationIDAndType($newSimulation->getId(), 0);
        if (count($immobilisse) > 0 && count($immobilisse2) > 0) {
            foreach ($immobilisse[0]->getProduitsAutresPeriodique() as $periodique) {
                $oldProduitsAutresPeriodique = $this->periodiqueDao->findOneByProduitAutreIDAndIterartion($immobilisse2[0]->getId(), $periodique->getIteration());
                $periodique->setValue($periodique->getValue() + $oldProduitsAutresPeriodique->getValue());
                $this->periodiqueDao->save($periodique);
            }
        }

        $financier1 = $this->produitAutreDao->findBySimulationIDAndType($oldSimulation1->getId(), 1);
        $financier2 = $this->produitAutreDao->findBySimulationIDAndType($oldSimulation2->getId(), 1);
        foreach ($financier1 as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $newObject->setCalculAutomatique(null);
            $this->save($newObject);
            foreach ($object->getProduitsAutresPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setProduitsAutres($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
        $financier = $this->produitAutreDao->findBySimulationIDAndType($newSimulation->getId(), 1);
        if (count($financier) > 0 && count($financier2) > 0) {
            foreach ($financier[0]->getProduitsAutresPeriodique() as $periodique) {
                $oldProduitsAutresPeriodique = $this->periodiqueDao->findOneByProduitAutreIDAndIterartion($financier2[0]->getId(), $periodique->getIteration());
                $periodique->setValue($periodique->getValue() + $oldProduitsAutresPeriodique->getValue());
                $this->periodiqueDao->save($periodique);
            }
        }

        $autresproduit1 = $this->produitAutreDao->findBySimulationIDAndType($oldSimulation1->getId(), 2);
        $autresproduit2 = $this->produitAutreDao->findBySimulationIDAndType($oldSimulation2->getId(), 2);
        foreach ($autresproduit1 as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getProduitsAutresPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setProduitsAutres($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
        foreach ($autresproduit2 as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getProduitsAutresPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setProduitsAutres($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
    }

    /**
     *  @return mixed[]
     */
    public function getWriteData(string $simulationId, int $type): array
    {
        $headers = [];
        $writeData = [];
        $row = [];

        if ($type === 0 || $type ===1) {
            $headers = [null];
        } else {
            $headers = ['N°', 'Désignation', 'Nature', 'Taux évolution'];
        }

        $simulation = $this->simulationDao->getById($simulationId);
        $anneeDeReference = $simulation->getAnneeDeReference();

        for ($i = 0; $i < 50; $i++) {
            array_push($headers, intval($anneeDeReference) + $i);
        }

        array_push($writeData, $headers);
        $produitAutres = $this->findBySimulationAndType($simulationId, $type);
        switch ($type) {
            case ProduitAutre::TYPE_PRODUCTION_IMMOBILISEE:
                if (count($produitAutres) === 0) {
                    throw HTTPException::badRequest('Il n\'y a pas de production immobilisée');
                }
                $writeData = $this->getFinanciersAndImobilises($writeData, $produitAutres, $simulationId);
                break;
            case ProduitAutre::TYPE_PRODUITS_FINANCIERS:
                if (count($produitAutres) === 0) {
                    throw HTTPException::badRequest('Il n\'y a pas de produit financier');
                }
                $writeData = $this->getFinanciersAndImobilises($writeData, $produitAutres, $simulationId);
                break;
            case ProduitAutre::TYPE_PRODUIT_EXCEPTIONNEL:
                foreach ($produitAutres as $key => $value) {
                    $row = [];
                    $row[] = $key + 1;
                    $row[] = $value->getNom();
                    $nature = $value->getNature();

                    if ($nature === 0) {
                        $row[] = 'Exceptionnel';
                    } elseif ($nature === 1) {
                        $row[] = 'Autre produit courant';
                    } else {
                        $row[] = 'Produit d\'activités(Compte 70)';
                    }

                    $row[] = $value->getTauxEvolution();
                    $periodiques = $value->getProduitsAutresPeriodique();

                    foreach ($periodiques as $periodique) {
                        $row[] = $periodique->getValue();
                    }

                    array_push($writeData, $row);
                }
                break;
        }

        return $writeData;
    }

    /**
     *  @param mixed[] $writeData
     *
     *  @return mixed[]
     */
    public function getFinanciersAndImobilises(array $writeData, ResultIterator $produitAutres, string $simulationId): array
    {
        for ($i = 0; $i < 2; $i++) {
            $row = [];

            if ($i === 0) {
                $row[] = 'Calcul automatique à partir de';
                $row[] = $produitAutres[0]->getCalculAutomatique();

                for ($j = 1; $j < 50; $j++) {
                    $row[] = null;
                }
            }

            if ($i === 1) {
                $row[] = 'Montants';
                $periodiques = $produitAutres[0]->getProduitsAutresPeriodique();
                $calcul_automatique = $produitAutres[0]->getCalculAutomatique();
                $simulation = $this->simulationDao->getById($simulationId);
                $anneeDeReference = $simulation->getAnneeDeReference();

                foreach ($periodiques as $key => $value) {
                    if ((intval($anneeDeReference) + $key) === intval($calcul_automatique)) {
                        $this->setPosition($key + 3);
                    }

                    $position = $this->getPosition();

                    if ((intval($anneeDeReference) + $key) > intval($calcul_automatique)) {
                        $row[] = null;
                    } else {
                        $row[] = $value->getValue();
                    }
                }
            }

            array_push($writeData, $row);
        }

        return $writeData;
    }

    public function getFillColorRange(int $type): string
    {
        $position = $this->getPosition();
        $column = $this->columnLetter($position);
        $range = '';

        if ($position !==0) {
            switch ($type) {
                case 0:
                    $range = $column . '5:Z5';
                    break;
                default:
                    $range = $column . '10:Z10';
                    break;
            }
        }

        return $range;
    }

    public function exportProduitAutres(Worksheet $sheet, string $simulationId): Worksheet
    {
        $fillRange = [];
        $immobiliseeData = $this->getWriteData($simulationId, ProduitAutre::TYPE_PRODUCTION_IMMOBILISEE);
        $range1 = $this->getFillColorRange(0);
        if ($range1 !== '') {
            $fillRange[] = $range1;
        }

        $financierData = $this->getWriteData($simulationId, ProduitAutre::TYPE_PRODUITS_FINANCIERS);
        $range2 = $this->getFillColorRange(1);
        if ($range2 !== '') {
            $fillRange[] = $range2;
        }

        if (count($fillRange) !== 0) {
            $this->setRange($fillRange);
        }

        $courantData = $this->getWriteData($simulationId, ProduitAutre::TYPE_PRODUIT_EXCEPTIONNEL);

        $totalRow = ['Total'];
        for ($i = 0; $i < 54; $i++) {
            $totalRow[] = null;
        }
        array_push($courantData, $totalRow);

        $sheet->setTitle('produits_autres');
        $sheet->setCellValue('A1', 'Produits - Autres produits');
        $sheet->setCellValue('A2', 'Production immobilisée');
        $sheet->setCellValue('A7', 'Produits financiers');
        $sheet->setCellValue('A12', 'Autres produits courants et exceptionnels');

        $sheet->fromArray($immobiliseeData, null, 'A3', true);
        $sheet->fromArray($financierData, null, 'A8', true);
        $sheet->fromArray($courantData, null, 'A13', true);

        $sheet->getStyle('A1:A' . (count($courantData) + 12))->getFont()->setBold(true);
        $sheet->getStyle('B3:AY3')->getFont()->setBold(true);
        $sheet->getStyle('B8:AY8')->getFont()->setBold(true);
        $sheet->getStyle('B13:BB13')->getFont()->setBold(true);

        $sheet->getStyle('A3:AY5')->getFont()->setSize(10);
        $sheet->getStyle('A8:AY10')->getFont()->setSize(10);
        $sheet->getStyle('A13:BB' . (count($courantData) + 12))->getFont()->setSize(10);
        $sheet->getStyle('A7')->getFont()->setSize(11);
        $sheet->getStyle('A12')->getFont()->setSize(11);
        $sheet->getStyle('A1:AY10')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A12:BB' . (count($courantData) + 12))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getColumnDimension('A')->setwidth(40);
        $sheet->getColumnDimension('B')->setwidth(25);
        $sheet->getColumnDimension('C')->setwidth(25);
        $sheet->getColumnDimension('C')->setwidth(20);

        $sheet->getStyle('B3:AY3')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A4:AY5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('B8:AY8')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A9:AY10')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A13:BB' . (count($courantData) + 12))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        for ($i = 4; $i <= 54; $i++) {
            $column = $this->columnLetter($i);
            $sheet->setCellValue(
                $column . (count($courantData) + 12),
                '=SUM(' . $column . '14:' . $column . (count($courantData) + 11) . ')'
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

    public function importProduitAutres(Request $request, string $simulationId): string
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
            $isProduitAutres = false;
            // Get data from imported excel data

            $changedIds = [];

            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $worksheetTitle = $worksheet->getTitle();

                if ($worksheetTitle !== 'produits_autres') {
                    continue;
                }

                $isProduitAutres = true;
            }

            if ($isProduitAutres === false) {
                throw HTTPException::badRequest('Il n\'y a pas de feuille correcte, veuillez vérifier à nouveau.');
            }

            $data['produits_autres'] = [
                'columnNames' => [],
                'columnValues' => [],
            ];

            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $worksheetTitle = $worksheet->getTitle();

                if ($worksheetTitle !== 'produits_autres') {
                    continue;
                }

                foreach ($worksheet->getRowIterator() as $row) {
                    $rowIndex = $row->getRowIndex();

                    if ($rowIndex < 13) {
                        continue;
                    }

                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(true);

                    foreach ($cellIterator as $cell) {
                        if ($rowIndex === 13) {
                            $data['produits_autres']['columnNames'][] = $cell->getCalculatedValue();
                        } else {
                            $data['produits_autres']['columnValues'][$rowIndex][] = $cell->getCalculatedValue();
                        }
                    }
                }
            }

            $data['produits_autres']['columnValues'] = array_values($data['produits_autres']['columnValues']);
            array_pop($data['produits_autres']['columnValues']);

            $defaultNatureString = [
                'Exceptionnel',
                'Autre produit courant',
                'Produit d\'activités(Compte 70)',
            ];

            foreach ($data['produits_autres']['columnValues'] as $item) {
                if (! in_array($item[2], $defaultNatureString)) {
                    throw HTTPException::badRequest('Une nature renseignée n\'est plus valide');
                }

                $nature = null;

                switch ($item[2]) {
                    case 'Exceptionnel':
                        $nature = 0;
                        break;
                    case 'Autre produit courant':
                        $nature = 1;
                        break;
                    case 'Produit d\'activités(Compte 70)':
                        $nature = 2;
                        break;
                }

                if ($nature === null) {
                    throw HTTPException::badRequest('la nature ne peut pas être nulle');
                }

                $periodiques = [];

                foreach ($item as $key => $value) {
                    if ($key < 4) {
                        continue;
                    }

                    $periodiques[] = $value;
                }

                try {
                    $oldProduitAutre = $this->produitAutreDao->findBySimulatonIdTypeNatureAndNom($simulationId, 2, $nature, strval($item[1]));

                    if (count($oldProduitAutre) > 0) {
                        $oldAutre = $this->factory->constructProduitAutre(
                            $oldProduitAutre[0]->getId(),
                            $simulationId,
                            strval($item[1]),
                            null,
                            $nature,
                            2,
                            $item[3],
                            null,
                            json_encode(['periodique' => $periodiques])
                        );

                        $this->save($oldAutre);
                        $changedIds[] = $oldProduitAutre[0]->getId();
                    } else {
                        $newAutre = $this->factory->constructProduitAutre(
                            null,
                            $simulationId,
                            strval($item[1]),
                            null,
                            $nature,
                            2,
                            $item[3],
                            null,
                            json_encode(['periodique' => $periodiques])
                        );

                        $this->save($newAutre);
                        $changedIds[] = $newAutre->getId();
                    }
                } catch (Throwable $e) {
                    throw HTTPException::badRequest('Impossible d\'importer des données', $e);
                }
            }

            $produitAutres = $this->findBySimulationAndType($simulationId, 2);

            foreach ($produitAutres as $produitAutre) {
                if (in_array($produitAutre->getId(), $changedIds)) {
                    continue;
                }

                $this->remove($produitAutre->getId());
            }
        }

        return 'Produit Autres importé';
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

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }
}
