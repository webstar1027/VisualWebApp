<?php

declare(strict_types=1);

namespace App\Services;

use App\Dao\FondDeRoulementDao;
use App\Dao\FondDeRoulementParametreDao;
use App\Dao\FondDeRoulementPeriodiqueDao;
use App\Dao\SimulationDao;
use App\Dao\TypeEmpruntDao;
use App\Exceptions\HTTPException;
use App\Model\Factories\FondDeRoulementFactory;
use App\Model\FondDeRoulement;
use App\Model\FondDeRoulementParametre;
use App\Model\FondDeRoulementPeriodique;
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
use function date_format;
use function floatval;
use function in_array;
use function intval;
use function Safe\json_encode;
use function strval;

class FondDeRoulementService
{
    /** @var FondDeRoulementDao */
    private $fondDeRoulementDao;

    /** @var FondDeRoulementParametreDao */
    private $fondDeRoulementParametreDao;

    /** @var FondDeRoulementPeriodiqueDao */
    private $fondDeRoulementPeriodiqueDao;

    /** @var FondDeRoulementFactory */
    private $factory;

    /** @var SimulationDao */
    private $simulationDao;

    /** @var TypeEmpruntDao */
    private $typeEmpruntDao;

    public function __construct(
        FondDeRoulementDao $fondDeRoulementDao,
        FondDeRoulementPeriodiqueDao $fondDeRoulementPeriodiqueDao,
        FondDeRoulementParametreDao $fondDeRoulementParametreDao,
        FondDeRoulementFactory $factory,
        SimulationDao $simulationDao,
        TypeEmpruntDao $typeEmpruntDao
    ) {
        $this->fondDeRoulementDao = $fondDeRoulementDao;
        $this->fondDeRoulementPeriodiqueDao = $fondDeRoulementPeriodiqueDao;
        $this->fondDeRoulementParametreDao = $fondDeRoulementParametreDao;
        $this->factory = $factory;
        $this->simulationDao = $simulationDao;
        $this->typeEmpruntDao = $typeEmpruntDao;
    }

    /**
     * @throws HTTPException
     */
    public function save(FondDeRoulement $fondDeRoulement): void
    {
        try {
            $this->fondDeRoulementDao->save($fondDeRoulement);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Ce fond de roulement existe déjà.', $e);
        }
    }

    /**
     * @throws TDBMException
     */
    public function remove(string $fondDeRoulementID): void
    {
        try {
            $fondDeRoulement = $this->fondDeRoulementDao->getById($fondDeRoulementID);
            $this->fondDeRoulementDao->delete($fondDeRoulement, true);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Ce fond de roulement n\'existe pas.', $e);
        }
    }

    /**
     * @return FondDeRoulement[]|ResultIterator
     */
    public function findBySimulationAndType(string $simulationId, string $type): ResultIterator
    {
        return $this->fondDeRoulementDao->findBySimulationAndType($simulationId, $type);
    }

    /**
     * @return FondDeRoulement[]|ResultIterator
     */
    public function findByNomAndType(string $nom, string $type): ResultIterator
    {
        return $this->fondDeRoulementDao->findByNomAndType($nom, $type);
    }

    /**
     * @return FondDeRoulement[]|ResultIterator
     */
    public function findBySimulation(string $simulationId): ResultIterator
    {
        return $this->fondDeRoulementDao->findBySimulationID($simulationId);
    }

    public function createFondsRoulements(Simulation $simulationID): void
    {
        $this->createFondDeRoulement($simulationID, FondDeRoulement::FONDS_PROPRES_SUR_IMMOBILISATION_DE_STRUCTURE, FondDeRoulement::AUTRES_VARIATIONS_POTENTIEL_FINANCIER);
        $this->createFondDeRoulement($simulationID, FondDeRoulement::VARIATION_DU_CAPITAL_OU_DOTATIONS, FondDeRoulement::AUTRES_VARIATIONS_POTENTIEL_FINANCIER);
        $this->createFondDeRoulement($simulationID, FondDeRoulement::REMBOURSEMENT_ANTICIPES_NON_REFINANCIES, FondDeRoulement::AUTRES_VARIATIONS_POTENTIEL_FINANCIER);
        $this->createFondDeRoulement($simulationID, FondDeRoulement::PRIX_DE_CESSION_ACTIF_HORS_PATRIMOINE, FondDeRoulement::AUTRES_VARIATIONS_POTENTIEL_FINANCIER);
        $this->createFondDeRoulement($simulationID, FondDeRoulement::PROVISION_POUR_GROS_ENTRETIEN, FondDeRoulement::PROVISIONS_DE_HAUT_BILAN);
        $this->createFondDeRoulement($simulationID, FondDeRoulement::PROVISIONS_POUR_RISQUES_SWAP, FondDeRoulement::PROVISIONS_DE_HAUT_BILAN);
        $this->createFondDeRoulement($simulationID, FondDeRoulement::PROVISIONS_POUR_INDEMNITES_DE_RETRAITE, FondDeRoulement::PROVISIONS_DE_HAUT_BILAN);
        $this->createFondDeRoulement($simulationID, FondDeRoulement::DETTE_INTERETS_COMPOSATEURS, FondDeRoulement::PROVISIONS_DE_HAUT_BILAN);

        $this->createFondDeRoulementParametre($simulationID);
    }

    private function createFondDeRoulement(Simulation $simulation, string $nom, string $type): void
    {
        $fondDeRoulement = new FondDeRoulement($simulation, $nom, $type);
        if (($nom === FondDeRoulement::FONDS_PROPRES_SUR_IMMOBILISATION_DE_STRUCTURE) ||
           ( $nom === FondDeRoulement::VARIATION_DU_CAPITAL_OU_DOTATIONS) ||
           ( $nom === FondDeRoulement::REMBOURSEMENT_ANTICIPES_NON_REFINANCIES) ||
           ( $nom === FondDeRoulement::PRIX_DE_CESSION_ACTIF_HORS_PATRIMOINE)) {
            $fondDeRoulement->setTauxEvolution(null);
        } else {
            $fondDeRoulement->setTauxEvolution(null);
        }
        $fondDeRoulement->setDeletable(false);
        $this->fondDeRoulementDao->save($fondDeRoulement);
        $this->createFondDeRoulementPeriodique($fondDeRoulement);
    }

    private function createFondDeRoulementPeriodique(FondDeRoulement $fondDeRoulement): void
    {
        for ($i=1; $i<= FondDeRoulementPeriodique::NUMBER_OF_ITERATIONS; $i++) {
            $fondDeRoulementPeriodique= new FondDeRoulementPeriodique($fondDeRoulement, $i);
            $fondDeRoulementPeriodique->setValeur(null);
            $this->fondDeRoulementPeriodiqueDao->save($fondDeRoulementPeriodique);
        }
    }

    private function createFondDeRoulementParametre(Simulation $simulation): void
    {
        $fondDeRoulementParametre = new FondDeRoulementParametre($simulation);
        $fondDeRoulementParametre->setDepotDeGarantie(null);
        $fondDeRoulementParametre->setFondsPropresSurOperation(null);
        $fondDeRoulementParametre->setPotentielFinancierTermination(null);
        $this->fondDeRoulementParametreDao->save($fondDeRoulementParametre);
    }

    /**
     *  @return mixed[]
     */
    public function getWriteData(string $simulationId, string $type): array
    {
        $simulation = $this->simulationDao->getById($simulationId);
        $anneeDeReference = $simulation->getAnneeDeReference();
        $headers = [null];
        $writeData = [];
        $fondDeRoulements = $this->findBySimulation($simulationId);
        $fetchFondDeRoulements = [];

        switch ($type) {
            case FondDeRoulement::PROVISIONS_DE_HAUT_BILAN:
                $headers[] = 'Taux d\'évolution';

                foreach ($fondDeRoulements as $key => $value) {
                    if ($value->getType() !== FondDeRoulement::PROVISIONS_DE_HAUT_BILAN) {
                        continue;
                    }

                    $fetchFondDeRoulements[] = $value;
                }
                break;
            case FondDeRoulement::AUTRES_VARIATIONS_POTENTIEL_FINANCIER:
                $headers[] = 'Numéro d\'emprunt';

                foreach ($fondDeRoulements as $key => $value) {
                    if ($value->getType() !== FondDeRoulement::AUTRES_VARIATIONS_POTENTIEL_FINANCIER) {
                        continue;
                    }

                    $fetchFondDeRoulements[] = $value;
                }
                break;
        }

        for ($i = 0; $i < FondDeRoulementPeriodique::NUMBER_OF_ITERATIONS; $i++) {
            $headers[] =  intval($anneeDeReference) + $i;
        }

        $writeData[] = $headers;

        foreach ($fetchFondDeRoulements as $fondDeRoulement) {
            $row = [];
            $row[] = $fondDeRoulement->getNom();

            switch ($type) {
                case FondDeRoulement::PROVISIONS_DE_HAUT_BILAN:
                    $row[] = $fondDeRoulement->getTauxEvolution();
                    break;
                case FondDeRoulement::AUTRES_VARIATIONS_POTENTIEL_FINANCIER:
                    $typeEmprunt = $fondDeRoulement->getTypeEmprunt();
                    $row[] = $typeEmprunt ? $typeEmprunt->getNumero() : null;
                    break;
            }

            $fondDeRoulementPeriodiques = $fondDeRoulement->getFondDeRoulementPeriodique();

            foreach ($fondDeRoulementPeriodiques as $fondDeRoulementPeriodique) {
                $row[] = $fondDeRoulementPeriodique->getValeur();
            }

            $writeData[] = $row;
        }

        $totalRow = ['Total'];
        for ($i = 0; $i < FondDeRoulementPeriodique::NUMBER_OF_ITERATIONS + 1; $i++) {
            $totalRow[] =  null;
        }

        $writeData[] = $totalRow;

        return $writeData;
    }

     /**
      *  @return mixed[]
      */
    public function getParametres(string $simulationId): array
    {
        $writeData = [[null, 'N']];
        $fontDeRoulementParametres = $this->fondDeRoulementParametreDao->findBySimulationID($simulationId);

        if (count($fontDeRoulementParametres) > 0) {
            $writeData[] = ['Potentiel financier à terminaison', $fontDeRoulementParametres[0]->getPotentielFinancierTermination()];
            $writeData[] = ['Fonds propres sur opérations en cours', $fontDeRoulementParametres[0]->getFondsPropresSurOperation()];
            $writeData[] = ['Dépôt de garantie', $fontDeRoulementParametres[0]->getDepotDeGarantie()];
        }

        return $writeData;
    }

    public function exportFondDeRoulement(Worksheet $sheet, string $simulationId): Worksheet
    {
        $fondDeRoulementParametreData = $this->getParametres($simulationId);
        $provisionData = $this->getWriteData($simulationId, FondDeRoulement::PROVISIONS_DE_HAUT_BILAN);
        $autresData = $this->getWriteData($simulationId, FondDeRoulement::AUTRES_VARIATIONS_POTENTIEL_FINANCIER);

        $sheet->setTitle('fond_de_roulement');
        $sheet->setCellValue('A1', 'FDR LT');
        $sheet->setCellValue('A2', 'FdR LT à fin N');
        $sheet->setCellValue('A8', 'Provisions de haut de bilan');
        $sheet->setCellValue('A' . (count($provisionData) + 10), 'Autres variations du potentiel financier');

        $sheet->fromArray($fondDeRoulementParametreData, null, 'A3', true);
        $sheet->fromArray($provisionData, null, 'A9', true);
        $sheet->fromArray($autresData, null, 'A' . (count($provisionData) + 11), true);

        $sheet->getColumnDimension('A')->setwidth(50);
        $sheet->getColumnDimension('B')->setwidth(20);

        $this->setBoldSheet(['A1:A' . (count($provisionData) + count($autresData) + 10), 'B3', 'B9:AZ9', 'B' . (count($provisionData) + 11) . ':AZ' . (count($provisionData) + 11)], $sheet);

        $sheet->getStyle('A3:AZ' . (count($provisionData) + count($autresData) + 10))->getFont()->setSize(10);
        $sheet->getStyle('A8')->getFont()->setSize(11);
        $sheet->getStyle('A' . (count($provisionData)  + 10))->getFont()->setSize(11);

        $sheet->getStyle('A1:AZ' . (count($provisionData) + count($autresData) + 10))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $this->setBorderSheet(['B3', 'A4:B6', 'B9:AZ9', 'A10:AZ' . (count($provisionData) + 8), 'B' . (count($provisionData) + 11) . ':AZ' . (count($provisionData) + 11), 'A' . (count($provisionData) + 12) . ':AZ' . (count($provisionData) + count($autresData) + 10)], $sheet);
        for ($i = 3; $i <= FondDeRoulementPeriodique::NUMBER_OF_ITERATIONS + 2; $i++) {
            $column = $this->columnLetter($i);
            $sheet->setCellValue(
                $column . (count($provisionData) + 8),
                '=SUM(' . $column . '10:' . $column . (count($provisionData) + 7) . ')'
            );
            $sheet->setCellValue(
                $column . (count($provisionData) + count($autresData) + 10),
                '=SUM(' . $column . (count($provisionData) + 12) . ':' . $column . (count($provisionData) + 9 + count($autresData)) . ')'
            );
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

    public function importFondDeRoulement(Request $request, string $simulationId): string
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
            $isFondRoulement = false;
            $changedIds = [];

            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $worksheetTitle = $worksheet->getTitle();

                if ($worksheetTitle !== 'fond_de_roulement') {
                    continue;
                }

                $isFondRoulement = true;
            }

            if ($isFondRoulement === false) {
                throw HTTPException::badRequest('Il n\'y a pas de feuille correcte, veuillez vérifier à nouveau.');
            }

            $data['fondRoulement'] = [
                'provision' => [
                    'columnNames' => [],
                    'columnValues' => [],
                ],
                'autures' => [
                    'columnNames' => [],
                    'columnValues' => [],
                ],
            ];

            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $worksheetTitle = $worksheet->getTitle();

                if ($worksheetTitle !== 'fond_de_roulement') {
                    continue;
                }

                $newConditionRow = 0;
                foreach ($worksheet->getRowIterator() as $row) {
                    $rowIndex = $row->getRowIndex();

                    if ($rowIndex <= 8) {
                        continue;
                    }

                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(true);

                    foreach ($cellIterator as $cell) {
                        if ($cell->getValue() === 'Total') {
                            $newConditionRow = intval($rowIndex);
                        }

                        if ($newConditionRow === 0) {
                            if ($rowIndex === 9) {
                                $data['fondRoulement']['provision']['columnNames'][] = $cell->getCalculatedValue();
                            } else {
                                $data['fondRoulement']['provision']['columnValues'][$rowIndex][] = $cell->getCalculatedValue();
                            }
                        } else {
                            if ($rowIndex === $newConditionRow + 3) {
                                $data['fondRoulement']['autures']['columnNames'][] = $cell->getCalculatedValue();
                            }
                            if ($rowIndex > $newConditionRow + 3) {
                                $data['fondRoulement']['autures']['columnValues'][$rowIndex][] = $cell->getCalculatedValue();
                            }
                        }
                    }
                }
            }

            $data['fondRoulement']['provision']['columnValues'] = array_values($data['fondRoulement']['provision']['columnValues']);
            $data['fondRoulement']['autures']['columnValues'] = array_values($data['fondRoulement']['autures']['columnValues']);
            $changedIds = $this->saveFondDeRoulement($changedIds, $simulationId, $data['fondRoulement']['provision']['columnValues'], FondDeRoulement::PROVISIONS_DE_HAUT_BILAN);
            $changedIds = $this->saveFondDeRoulement($changedIds, $simulationId, $data['fondRoulement']['autures']['columnValues'], FondDeRoulement::AUTRES_VARIATIONS_POTENTIEL_FINANCIER);

            $allFondDeRoulements = $this->findBySimulation($simulationId);
            foreach ($allFondDeRoulements as $_fondDeRoulement) {
                if (in_array($_fondDeRoulement->getId(), $changedIds) || $_fondDeRoulement->getDeletable() === false) {
                    continue;
                }

                $this->remove($_fondDeRoulement->getId());
            }
        }

        return 'Fond de roulement importé';
    }

    /**
     *  @param mixed[] $data
     *  @param string[] $changedIds
     *
     *  @return string[]
     */
    public function saveFondDeRoulement(array $changedIds, string $simulationId, array $data, string $type): array
    {
        $oldFondDeRoulements = $this->findBySimulationAndType($simulationId, $type);

        foreach ($data as $key => $value) {
            $nom = $value[0];
            $periodiques = [];

            if ($key < 4) {
                $defaultNom = $oldFondDeRoulements[$key]->getNom();

                if (strval($nom) !== strval($defaultNom)) {
                    throw HTTPException::badRequest('L\'ordre des lignes de données d\'importation n\'est pas correct. ---' . $key . ':' . $defaultNom);
                }
            }

            foreach ($value as $key1 => $item) {
                if ($key1 < 2) {
                    continue;
                }

                array_push($periodiques, $item);
            }

            if (isset($value[1]) && $type === FondDeRoulement::AUTRES_VARIATIONS_POTENTIEL_FINANCIER) {
                $typeEmprunt = $this->typeEmpruntDao->findByNumero(strval($value[1]));
                if (count($typeEmprunt) === 0) {
                    throw HTTPException::badRequest('Ce type d\'emprunt n\'existe pas');
                }
            }

            try {
                $fondDeRoulement = $this->findByNomAndType($nom, $type);
                $typeEmpruntId = null;
                $tauxEvolution = null;

                switch ($type) {
                    case $type === FondDeRoulement::PROVISIONS_DE_HAUT_BILAN:
                        $typeEmpruntId = null;
                        $tauxEvolution = floatval($value[1]);
                        break;
                    default:
                        $typeEmprunt = $this->typeEmpruntDao->findByNumero(strval($value[1]));
                        $tauxEvolution = null;
                        if (count($typeEmprunt) > 0) {
                            $typeEmpruntId = $typeEmprunt[0]->getId();
                        }
                        break;
                }

                if (count($fondDeRoulement) > 0) {
                    $oldFondDeRoulement = $this->factory->createFondDeRoulement(
                        $fondDeRoulement[0]->getId(),
                        $simulationId,
                        strval($nom),
                        $type,
                        $typeEmpruntId,
                        $tauxEvolution,
                        $fondDeRoulement[0]->getDateEcheance() ? date_format($fondDeRoulement[0]->getDateEcheance(), 'Y-m-d H:i:s'): null,
                        json_encode(['periodique' => $periodiques])
                    );
                    $this->save($oldFondDeRoulement);

                    if ($key > 3) {
                        $changedIds[] = $oldFondDeRoulement->getId();
                    }
                } else {
                    $newFondDeRoulement = $this->factory->createFondDeRoulement(
                        null,
                        $simulationId,
                        strval($nom),
                        $type,
                        $typeEmpruntId,
                        $tauxEvolution,
                        null,
                        json_encode(['periodique' => $periodiques])
                    );

                    $this->save($newFondDeRoulement);
                    $changedIds[] = $newFondDeRoulement->getId();
                }
            } catch (Throwable $e) {
                throw HTTPException::badRequest('Impossible d\'importer les données', $e);
            }
        }

        return $changedIds;
    }

    public function cloneFondDeRoulement(Simulation $newSimulation, Simulation $oldSimulation): void
    {
        $objects = $this->fondDeRoulementDao->findBySimulationId($oldSimulation->getId());
        foreach ($objects as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $this->fondDeRoulementDao->save($newObject);
            foreach ($object->getFondDeRoulementPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setFondDeRoulement($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->fondDeRoulementPeriodiqueDao->save($newPeriodique);
            }
        }
    }

    public function fusionFondDeRoulement(Simulation $newSimulation, Simulation $oldSimulation1, Simulation $oldSimulation2): void
    {
        $fondDeRoulementParametre1 = $this->fondDeRoulementParametreDao->findBySimulationID($oldSimulation1->getId());
        $fondDeRoulementParametre2 = $this->fondDeRoulementParametreDao->findBySimulationID($oldSimulation2->getId());

        $fondDeRoulementParametre = new FondDeRoulementParametre($newSimulation);

        $fondDeRoulementParametre->setPotentielFinancierTermination($fondDeRoulementParametre1[0]->getPotentielFinancierTermination() + $fondDeRoulementParametre2[0]->getPotentielFinancierTermination());
        $fondDeRoulementParametre->setFondsPropresSurOperation($fondDeRoulementParametre1[0]->getFondsPropresSurOperation() + $fondDeRoulementParametre2[0]->getFondsPropresSurOperation());
        $fondDeRoulementParametre->setDepotDeGarantie($fondDeRoulementParametre1[0]->getDepotDeGarantie() + $fondDeRoulementParametre2[0]->getDepotDeGarantie());
        $this->fondDeRoulementParametreDao->save($fondDeRoulementParametre);

        $objects1 = $this->fondDeRoulementDao->findBySimulationId($oldSimulation1->getId());
        $objects2 = $this->fondDeRoulementDao->findBySimulationId($oldSimulation2->getId());
        foreach ($objects1 as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $this->fondDeRoulementDao->save($newObject);
            foreach ($object->getFondDeRoulementPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setFondDeRoulement($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->fondDeRoulementPeriodiqueDao->save($newPeriodique);
            }
        }
        foreach ($objects2 as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $this->fondDeRoulementDao->save($newObject);
            foreach ($object->getFondDeRoulementPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setFondDeRoulement($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->fondDeRoulementPeriodiqueDao->save($newPeriodique);
            }
        }
    }
}
