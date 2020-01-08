<?php

declare(strict_types=1);

namespace App\Services;

use App\Dao\AnnuiteDao;
use App\Dao\AnnuitePeriodiqueDao;
use App\Dao\SimulationDao;
use App\Exceptions\HTTPException;
use App\Model\Annuite;
use App\Model\AnnuitePeriodique;
use App\Model\Factories\AnnuiteFactory;
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
use function array_splice;
use function array_values;
use function chr;
use function count;
use function in_array;
use function intval;
use function Safe\json_encode;
use function strval;

final class AnnuiteService
{
    /** @var AnnuiteDao */
    private $annuiteDao;
    /** @var AnnuiteFactory */
    private $factory;
    /** @var AnnuitePeriodiqueDao */
    private $periodiqueDao;
    /** @var SimulationDao */
    private $simulationDao;

    public function __construct(AnnuiteDao $annuiteDao, AnnuiteFactory $factory, AnnuitePeriodiqueDao $periodiqueDao, SimulationDao $simulationDao)
    {
        $this->annuiteDao = $annuiteDao;
        $this->factory = $factory;
        $this->simulationDao = $simulationDao;
        $this->periodiqueDao = $periodiqueDao;
    }

    /**
     * @throws HTTPException
     */
    public function save(Annuite $annuite): void
    {
        try {
            $this->annuiteDao->save($annuite);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Cette annuité existe déjà.', $e);
        }
    }

    /**
     * @throws TDBMException
     * @throws HTTPException
     */
    public function remove(string $annuiteUUID): void
    {
        $annuite = $this->annuiteDao->getById($annuiteUUID);
        if ($annuite->getDeletable() === false) {
            throw HTTPException::badRequest('Cette annuité ne peut pas être supprimée.');
        }
        $this->annuiteDao->delete($annuite, true);
    }

    /**
     * @return Annuite[]|ResultIterator
     */
    public function findBySimulationAndType(string $simulationId, int $type): ResultIterator
    {
        return $this->annuiteDao->findBySimulationIDAndType($simulationId, $type);
    }

    /**
     * @return Annuite[]|ResultIterator
     */
    public function findBySimulationAndLibelle(string $simulationId, string $libelle): ResultIterator
    {
        return $this->annuiteDao->findBySimulationAndLibelle($simulationId, $libelle);
    }

     /**
      * @return Annuite[]|ResultIterator
      */
    public function findBySimulationAndLibelleType(string $simulationId, string $libelle, int $type): ResultIterator
    {
        return $this->annuiteDao->findBySimulationAndLibelleType($simulationId, $libelle, $type);
    }

    /**
     * @throws TDBMException
     * @throws JsonException
     * @throws HTTPException
     */
    public function createDefaultAnnuite(Simulation $newSimulation): void
    {
        $numero = 1;
        foreach (Annuite::DEFAULT_CONFIG as $label => $nature) {
            $annuite = $this->factory->createAnnuite(
                null,
                strval($numero),
                $newSimulation->getId(),
                null,
                null,
                $label,
                $nature,
                Annuite::TYPE_ANNUITE_EMPRUNTS,
                null
            );
            if ($numero <= 2) {
                $annuite->setDeletable(false);
            }
            $this->save($annuite);
            $this->createDefaultPeriodique($annuite);
            $numero++;
        }

        foreach (Annuite::DEFAULT_CONFIG1 as $label => $type) {
            $annuite = $this->factory->createAnnuite(
                null,
                null,
                $newSimulation->getId(),
                null,
                null,
                $label,
                $nature,
                $type,
                null
            );
            $this->save($annuite);
            $this->createDefaultPeriodique($annuite);
        }
    }

    private function createDefaultPeriodique(Annuite $annuite): void
    {
        for ($i = 1; $i <= AnnuitePeriodique::NUMBER_OF_ITERATIONS; $i++) {
            $annuitePeriodique = new AnnuitePeriodique($annuite, $i);
            $annuitePeriodique->setValue(null);
            $this->periodiqueDao->save($annuitePeriodique);
        }
    }

    /**
     *  @param int[] $types
     *
     *  @return mixed[]
     */
    public function getAnnuiteData(string $simulationId, array $types): array
    {
        $headers = $this->getHeaders([null], $simulationId);
        $patrimoineData = [];
        $anneeDeReference = $this->getAnneeDeReference($simulationId);
        $patrimoineData[] = $headers;

        foreach ($types as $type) {
            $annuites = $this->findBySimulationAndType($simulationId, $type);
            $periodiques = $annuites[0]->getAnnuitePeriodique();
            $row = [];

            switch ($type) {
                case Annuite::TYPE_ACNE:
                    $row[] = 'ACNE du patrimoine de référence';
                    break;
                case Annuite::TYPE_ICNE:
                    $row[] = 'ICNE du patrimoine de référence';
                    break;
                case Annuite::TYPE_REMBOURSEMENT:
                    $row[] = 'Remboursement en capital des autres emprunts';
                    break;
                case Annuite::TYPE_CHARGES:
                    $row[] = 'Charges d\'intérêt des autres emprunts';
                    break;
            }

            foreach ($periodiques as $periodique) {
                $row[] = $periodique->getValue();
            }
            $patrimoineData[] = $row;
        }

        return $patrimoineData;
    }

    /**
     *  @param mixed[] $initialHeaders
     *
     *  @return mixed[]
     */
    public function getHeaders(array $initialHeaders, string $simulationId): array
    {
        $anneeDeReference = $this->getAnneeDeReference($simulationId);
        for ($i = 0; $i < 50; $i++) {
            $initialHeaders[] = intval($anneeDeReference) + $i;
        }

        return $initialHeaders;
    }

    public function getAnneeDeReference(string $simulationId): string
    {
        $simulation = $this->simulationDao->getById($simulationId);

        return $simulation->getAnneeDeReference();
    }

    /**
     *  @param mixed[] $locatifs
     *
     *  @return mixed[]
     */
    public function getEmpruntLocatifData(array $locatifs, int $type, string $simulationId): array
    {
        $locatifData[] = $this->getHeaders($locatifs, $simulationId);
        $annuites = $this->findBySimulationAndType($simulationId, Annuite::TYPE_ANNUITE_EMPRUNTS);

        foreach ($annuites as $annuite) {
            $libelle = $annuite->getLibelle();
            $row = [];

            if ($type === 0) {
                $defaultLibelle = [
                    'Remboursement en capital des emprunts locatifs',
                    'Charges d\'intérêts des emprunts locatifs',
                ];

                if (! in_array($libelle, $defaultLibelle)) {
                    continue;
                }

                $row[] = $libelle;
            }

            if ($type === 1) {
                $row[] = $annuite->getNumero();
                $row[] = $libelle;
                $row[] = $annuite->getNature() === 0 ? 'Remboursement en capital' : 'Charges d\'intérêts';
            }

            $periodiques = $annuite->getAnnuitePeriodique();

            foreach ($periodiques as $periodique) {
                $row[] = $periodique->getValue();
            }

            $locatifData[] = $row;
        }

        return $locatifData;
    }

    public function exportChargesAnnuite(Worksheet $sheet, string $simulationId): Worksheet
    {
        $annuites = $this->findBySimulationAndType($simulationId, Annuite::TYPE_ACNE);
        $chargesAnnuitePatrimoineData = ['Capital restant dû sur le patrimoine de référence', $annuites[0]->getCapitalRestantPatrimoine()];
        $priseData = ['Prise en compte des ICNE /ACNE (*)', $annuites[0]->getPriseIcneAcne() === true ? 'Oui' : 'Non'];
        $patrimoineData = $this->getAnnuiteData($simulationId, [Annuite::TYPE_ACNE, Annuite::TYPE_ICNE]);

        $locatifData = $this->getEmpruntLocatifData([null], 0, $simulationId);
        $totalRow = $this->getHeaders(['Total Annuités des emprunts locatifs sur le patrimoine de référence'], $simulationId);
        $locatifData[] = $totalRow;

        $detailLocatifData = $this->getEmpruntLocatifData(['N°', 'Libellé', 'Nature'], 1, $simulationId);
        $totalRow = $this->getHeaders(['Total', null, null], $simulationId);
        $detailLocatifData[] = $totalRow;

        $annuites = $this->findBySimulationAndType($simulationId, Annuite::TYPE_REMBOURSEMENT);
        $autresEmpruntData = [
            [null, 'N'],
            ['Capital restant dû autres emprunts long terme de structure ou non affectés', $annuites[0]->getCapitalRestantPatrimoine()],
        ];

        $annuiteAutresData = $this->getAnnuiteData($simulationId, [Annuite::TYPE_REMBOURSEMENT, Annuite::TYPE_CHARGES]);

        $sheet->setTitle('charges_annuites');
        $sheet->setCellValue('A1', 'Charges - Annuités du patrimoine de référence');
        $sheet->setCellValue('A4', 'Option de calcul des annuités d\'emprunt');
        $sheet->setCellValue('A11', 'Annuités locatives du patrimoine de référence');
        $sheet->setCellValue('A17', 'Détail des annuités des emprunts locatifs');
        $sheet->setCellValue('A' . (count($detailLocatifData) + 19), 'Autres emprunts long terme de structure ou non affectés');

        $sheet->fromArray($chargesAnnuitePatrimoineData, null, 'A2', true);
        $sheet->fromArray($priseData, null, 'A5', true);
        $sheet->fromArray($patrimoineData, null, 'A7', true);
        $sheet->fromArray($locatifData, null, 'A12', true);
        $sheet->fromArray($detailLocatifData, null, 'A18', true);
        $sheet->fromArray($autresEmpruntData, null, 'A' . (count($detailLocatifData) + 20), true);
        $sheet->fromArray($annuiteAutresData, null, 'A' . (count($detailLocatifData) + 23), true);

        $sheet = $this->setBoldSheet([
            'A1:A' . (count($detailLocatifData) + 25),
            'B7:AY7',
            'B12:AY12',
            'B18:BA18',
            'B' . (count($detailLocatifData) + 20),
            'B' . (count($detailLocatifData) + 23) . ':AY' . (count($detailLocatifData) + 23),
        ], $sheet);

        $sheet->getStyle('A2:BA' . (count($detailLocatifData) + 25))->getFont()->setSize(10);
        $sheet->getStyle('A4')->getFont()->setSize(11);
        $sheet->getStyle('A11')->getFont()->setSize(11);
        $sheet->getStyle('A17')->getFont()->setSize(11);
        $sheet->getStyle('A' . (count($detailLocatifData) + 19))->getFont()->setSize(11);
        $sheet->getStyle('A1:BA' . (count($detailLocatifData) + 25))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getColumnDimension('A')->setwidth(65);
        $sheet->getColumnDimension('B')->setwidth(45);
        $sheet->getColumnDimension('C')->setwidth(30);

        $sheet = $this->setBorderSheet([
            'A2:B2',
            'A5:B5',
            'B7:AY7',
            'A8:AY9',
            'B12:AY12',
            'B13:AY15',
            'A18:BA' . (count($detailLocatifData) + 17),
            'B' . (count($detailLocatifData) + 20),
            'A' . (count($detailLocatifData) + 21) . ':B' . (count($detailLocatifData) + 21),
            'B' . (count($detailLocatifData) + 23) . ':AY' . (count($detailLocatifData) + 23),
            'A' . (count($detailLocatifData) + 24) . ':AY' . (count($detailLocatifData) + 25),
        ], $sheet);

        for ($i = 2; $i <= 51; $i++) {
            $column = $this->columnLetter($i);
            $targetColumn = $this->columnLetter($i + 2);

            $sheet->setCellValue(
                $column . '13',
                '=SUMIF(C19:C' . (count($detailLocatifData) + 16) . ', "Remboursement en capital", ' . $targetColumn . '19:' . $targetColumn . (count($detailLocatifData) + 16) . ')'
            );

            $sheet->setCellValue(
                $column . '14',
                '=SUMIF(C19:C' . (count($detailLocatifData) + 16) . ', "Charges d\'intérêts", ' . $targetColumn . '19:' . $targetColumn . (count($detailLocatifData) + 16) . ')'
            );

            $sheet->setCellValue(
                $column . '15',
                '=SUM(' . $column . '13:' . $column . '14)'
            );
        }

        for ($i = 4; $i <= 53; $i++) {
            $column = $this->columnLetter($i);
            $sheet->setCellValue(
                $column . (count($detailLocatifData) + 17),
                '=SUM(' . $column . '19:' . $column . (count($detailLocatifData) + 16) . ')'
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

    public function importChargesAnnuite(Request $request, string $simulationId): string
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
            $isAnnuite = false;
            // Get data from imported excel data
            $changedIds = [];

            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $worksheetTitle = $worksheet->getTitle();

                if ($worksheetTitle !== 'charges_annuites') {
                    continue;
                }

                $isAnnuite = true;
            }

            if ($isAnnuite === false) {
                throw HTTPException::badRequest('Il n\'y a pas de feuille correcte, veuillez vérifier à nouveau.');
            }

            $data['charges_annuites'] = [
                'columnNames' => [],
                'columnValues' => [],
            ];

            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $worksheetTitle = $worksheet->getTitle();

                if ($worksheetTitle !== 'charges_annuites') {
                    continue;
                }

                foreach ($worksheet->getRowIterator() as $row) {
                    $rowIndex = $row->getRowIndex();

                    if ($rowIndex < 8) {
                        continue;
                    }

                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(true);

                    foreach ($cellIterator as $cell) {
                        if ($rowIndex === 18) {
                            $data['charges_annuites']['columnNames'][] = $cell->getCalculatedValue();
                        } else {
                            $data['charges_annuites']['columnValues'][$rowIndex][] = $cell->getCalculatedValue();
                        }
                    }
                }
            }

            $data['charges_annuites']['columnValues'] = array_values($data['charges_annuites']['columnValues']);

            array_splice($data['charges_annuites']['columnValues'], 2, 8);

            $position = 0;
            for ($i = 0; $i < count($data['charges_annuites']['columnValues']); $i++) {
                if ($data['charges_annuites']['columnValues'][$i][0] !== 'Total') {
                    continue;
                }

                $position = $i;
            }

            if ($position === 0) {
                throw HTTPException::badRequest('Please check total row');
            }

            array_splice($data['charges_annuites']['columnValues'], $position, 7);

            foreach ($data['charges_annuites']['columnValues'] as $key => $item) {
                $periodiques = [];
                switch ($key) {
                    case 0:
                        foreach ($item as $key1 => $value) {
                            if ($key1 < 1 || $key1 > 50) {
                                continue;
                            }

                            array_push($periodiques, $value);
                        }

                        try {
                            $oldAcne = $this->findBySimulationAndType($simulationId, Annuite::TYPE_ACNE);
                            $newAcne = $this->factory->createAnnuite(
                                $oldAcne[0]->getId(),
                                $oldAcne[0]->getNumero(),
                                $simulationId,
                                $oldAcne[0]->getCapitalRestantPatrimoine(),
                                $oldAcne[0]->getPriseIcneAcne(),
                                $oldAcne[0]->getLibelle(),
                                null,
                                1,
                                json_encode(['periodique' => $periodiques])
                            );

                            $this->save($newAcne);
                        } catch (Throwable $e) {
                            throw HTTPException::badRequest('Acne issue', $e);
                        }

                        break;
                    case 1:
                        foreach ($item as $key1 => $value) {
                            if ($key1 < 1 || $key1 > 50) {
                                continue;
                            }

                            array_push($periodiques, $value);
                        }

                        try {
                            $oldIcne = $this->findBySimulationAndType($simulationId, Annuite::TYPE_ICNE);
                            $newIcne = $this->factory->createAnnuite(
                                $oldIcne[0]->getId(),
                                $oldIcne[0]->getNumero(),
                                $simulationId,
                                null,
                                null,
                                $oldIcne[0]->getLibelle(),
                                null,
                                2,
                                json_encode(['periodique' => $periodiques])
                            );

                            $this->save($newIcne);
                        } catch (Throwable $e) {
                            throw HTTPException::badRequest('Icne', $e);
                        }

                        break;
                    case count($data['charges_annuites']['columnValues']) - 2:
                        foreach ($item as $key1 => $value) {
                            if ($key1 < 1 || $key1 > 50) {
                                continue;
                            }

                            array_push($periodiques, $value);
                        }

                        try {
                            $oldRemboursement = $this->findBySimulationAndType($simulationId, Annuite::TYPE_REMBOURSEMENT);
                            $newRemboursement = $this->factory->createAnnuite(
                                $oldRemboursement[0]->getId(),
                                $oldRemboursement[0]->getNumero(),
                                $simulationId,
                                $oldRemboursement[0]->getCapitalRestantPatrimoine(),
                                null,
                                $oldRemboursement[0]->getLibelle(),
                                null,
                                3,
                                json_encode(['periodique' => $periodiques])
                            );

                            $this->save($newRemboursement);
                        } catch (Throwable $e) {
                            throw HTTPException::badRequest('Remboursement issue', $e);
                        }

                        break;
                    case count($data['charges_annuites']['columnValues']) - 1:
                        foreach ($item as $key1 => $value) {
                            if ($key1 < 1 || $key1 > 50) {
                                continue;
                            }

                            array_push($periodiques, $value);
                        }

                        try {
                            $oldCharges = $this->findBySimulationAndType($simulationId, Annuite::TYPE_CHARGES);
                            $newCharges = $this->factory->createAnnuite(
                                $oldCharges[0]->getId(),
                                $oldCharges[0]->getNumero(),
                                $simulationId,
                                null,
                                null,
                                $oldCharges[0]->getLibelle(),
                                null,
                                4,
                                json_encode(['periodique' => $periodiques])
                            );

                            $this->save($newCharges);
                        } catch (Throwable $e) {
                            throw HTTPException::badRequest('Charges issue', $e);
                        }

                        break;
                    default:
                        $defaultNature = [
                            'Charges d\'intérêts',
                            'Remboursement en capital',
                        ];

                        if (! in_array($item[2], $defaultNature)) {
                            throw HTTPException::badRequest('Une nature renseignée n\'est plus valide');
                        }

                        foreach ($item as $key1 => $value) {
                            if ($key1 < 3) {
                                continue;
                            }

                            array_push($periodiques, $value);
                        }

                        if ($key === 2 || $key === 3) {
                            $nonDeletableLibelle = [
                                'Remboursement en capital des emprunts locatifs',
                                'Charges d\'intérêts des emprunts locatifs',
                            ];

                            if (! in_array($item[1], $nonDeletableLibelle)) {
                                throw HTTPException::badRequest('Vous ne pouvez pas supprimer les deux premières lignes');
                            }

                            try {
                                $annuite = $this->findBySimulationAndLibelle($simulationId, $item[1]);
                                $oldAnnuite = $this->factory->createAnnuite(
                                    $annuite[0]->getId(),
                                    strval($key - 1),
                                    $simulationId,
                                    null,
                                    null,
                                    strval($item[1]),
                                    strval($item[2]) === 'Charges d\'intérêts' ? 1 : 0,
                                    0,
                                    json_encode(['periodique' => $periodiques])
                                );

                                $this->save($oldAnnuite);
                                $changedIds[] = $annuite[0]->getId();
                            } catch (Throwable $e) {
                                throw HTTPException::badRequest('test1', $e);
                            }
                        } else {
                            $_oldannuite = $this->findBySimulationAndLibelleType($simulationId, $item[1], 0);

                            if (count($_oldannuite) > 0) {
                                $_updateAnnuite = $this->factory->createAnnuite(
                                    $_oldannuite[0]->getId(),
                                    strval($key - 1),
                                    $simulationId,
                                    null,
                                    null,
                                    strval($item[1]),
                                    strval($item[2]) === 'Charges d\'intérêts' ? 1 : 0,
                                    0,
                                    json_encode(['periodique' => $periodiques])
                                );

                                $this->save($_updateAnnuite);
                                $changedIds[] = $_oldannuite[0]->getId();
                            } else {
                                $newAnnuite = $this->factory->createAnnuite(
                                    null,
                                    strval($key - 1),
                                    $simulationId,
                                    null,
                                    null,
                                    strval($item[1]),
                                    strval($item[2]) === 'Charges d\'intérêts' ? 1 : 0,
                                    0,
                                    json_encode(['periodique' => $periodiques])
                                );

                                $this->save($newAnnuite);
                                $changedIds[] = $newAnnuite->getId();
                            }
                        }
                        break;
                }
            }

            $currentAllAnnuites = $this->findBySimulationAndType($simulationId, Annuite::TYPE_ANNUITE_EMPRUNTS);

            foreach ($currentAllAnnuites as $currentItem) {
                if (in_array($currentItem->getId(), $changedIds)) {
                    continue;
                }

                $this->remove($currentItem->getId());
            }
        }

        return 'Charges annuités importé';
    }

    public function cloneAnnuite(Simulation $newSimulation, Simulation $oldSimulation): void
    {
        $objects = $this->annuiteDao->findBySimulation($oldSimulation);
        foreach ($objects as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getAnnuitePeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setAnnuite($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
    }

    public function fusionAnnuite(Simulation $newSimulation, Simulation $oldSimulation1, Simulation $oldSimulation2): void
    {
        $autresEmprunt1 = $this->annuiteDao->findBySimulationIDAndType($oldSimulation1->getId(), 0);
        $autresEmprunt2 = $this->annuiteDao->findBySimulationIDAndType($oldSimulation2->getId(), 0);
        foreach ($autresEmprunt1 as $key => $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $newObject->setNumero(strval($key + 1));
            $this->save($newObject);
            foreach ($object->getAnnuitePeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setAnnuite($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
        foreach ($autresEmprunt2 as $key => $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $newObject->setNumero(strval(count($autresEmprunt1) + $key + 1));
            $this->save($newObject);
            foreach ($object->getAnnuitePeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setAnnuite($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }

        $acne1 = $this->annuiteDao->findBySimulationIDAndType($oldSimulation1->getId(), 1);
        $acne2 = $this->annuiteDao->findBySimulationIDAndType($oldSimulation2->getId(), 1);
        foreach ($acne1 as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getAnnuitePeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setAnnuite($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }

        $acne = $this->annuiteDao->findBySimulationIDAndType($newSimulation->getId(), 1);
        $acne[0]->setCapitalRestantPatrimoine($acne[0]->getCapitalRestantPatrimoine() + $acne2[0]->getCapitalRestantPatrimoine());
        $this->save($acne[0]);
        foreach ($acne[0]->getAnnuitePeriodique() as $periodique) {
            $oldAnnuitePeriodique = $this->periodiqueDao->findOneByAnnuiteIDAndIterartion($acne2[0]->getId(), $periodique->getIteration());
            $periodique->setValue($periodique->getValue() + $oldAnnuitePeriodique->getValue());
            $this->periodiqueDao->save($periodique);
        }

        $icne1 = $this->annuiteDao->findBySimulationIDAndType($oldSimulation1->getId(), 2);
        $icne2 = $this->annuiteDao->findBySimulationIDAndType($oldSimulation2->getId(), 2);
        foreach ($icne1 as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getAnnuitePeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setAnnuite($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }

        $icne = $this->annuiteDao->findBySimulationIDAndType($newSimulation->getId(), 2);
        foreach ($icne[0]->getAnnuitePeriodique() as $periodique) {
            $oldAnnuitePeriodique = $this->periodiqueDao->findOneByAnnuiteIDAndIterartion($icne2[0]->getId(), $periodique->getIteration());
            $periodique->setValue($periodique->getValue() + $oldAnnuitePeriodique->getValue());
            $this->periodiqueDao->save($periodique);
        }

        $remboursement1 = $this->annuiteDao->findBySimulationIDAndType($oldSimulation1->getId(), 3);
        $remboursement2 = $this->annuiteDao->findBySimulationIDAndType($oldSimulation2->getId(), 3);
        foreach ($remboursement1 as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getAnnuitePeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setAnnuite($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }

        $remboursement = $this->annuiteDao->findBySimulationIDAndType($newSimulation->getId(), 3);
        $remboursement[0]->setCapitalRestantPatrimoine($remboursement[0]->getCapitalRestantPatrimoine() + $remboursement2[0]->getCapitalRestantPatrimoine());
        $this->save($remboursement[0]);
        foreach ($remboursement[0]->getAnnuitePeriodique() as $periodique) {
            $oldAnnuitePeriodique = $this->periodiqueDao->findOneByAnnuiteIDAndIterartion($remboursement2[0]->getId(), $periodique->getIteration());
            $periodique->setValue($periodique->getValue() + $oldAnnuitePeriodique->getValue());
            $this->periodiqueDao->save($periodique);
        }

        $charges1 = $this->annuiteDao->findBySimulationIDAndType($oldSimulation1->getId(), 4);
        $charges2 = $this->annuiteDao->findBySimulationIDAndType($oldSimulation2->getId(), 4);
        foreach ($charges1 as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getAnnuitePeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setAnnuite($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }

        $charges = $this->annuiteDao->findBySimulationIDAndType($newSimulation->getId(), 4);
        foreach ($charges[0]->getAnnuitePeriodique() as $periodique) {
            $oldAnnuitePeriodique = $this->periodiqueDao->findOneByAnnuiteIDAndIterartion($charges2[0]->getId(), $periodique->getIteration());
            $periodique->setValue($periodique->getValue() + $oldAnnuitePeriodique->getValue());
            $this->periodiqueDao->save($periodique);
        }
    }
}
