<?php

declare(strict_types=1);

namespace App\Services;

use App\Dao\PslaDao;
use App\Dao\PslaPeriodiqueDao;
use App\Dao\SimulationDao;
use App\Dao\TypeEmpruntPslaDao;
use App\Exceptions\HTTPException;
use App\Model\Factories\PslaFactory;
use App\Model\Psla;
use App\Model\Simulation;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
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
use function end;
use function in_array;
use function intval;
use function Safe\json_encode;
use function strval;

class PslaService
{
    /** @var PslaDao */
    private $pslaDao;
    /** @var TypeEmpruntPslaDao */
    private $typeEmpruntPslaDao;
    /** @var PslaPeriodiqueDao */
    private $periodiqueDao;
    /** @var TypeEmpruntService */
    private $typeEmpruntService;
    /** @var SimulationDao */
    private $simulationDao;
    /** @var PslaFactory */
    private $factory;

    public function __construct(
        PslaDao $pslaDao,
        TypeEmpruntPslaDao $typeEmpruntPslaDao,
        PslaPeriodiqueDao $periodiqueDao,
        TypeEmpruntService $typeEmpruntService,
        SimulationDao $simulationDao,
        PslaFactory $factory
    ) {
        $this->pslaDao = $pslaDao;
        $this->typeEmpruntPslaDao = $typeEmpruntPslaDao;
        $this->periodiqueDao = $periodiqueDao;
        $this->typeEmpruntService = $typeEmpruntService;
        $this->simulationDao = $simulationDao;
        $this->factory = $factory;
    }

    public function save(Psla $psla): void
    {
        try {
            $this->pslaDao->save($psla);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Ce psla existe déjà.', $e);
        }
    }

    /**
     * @throws TDBMException
     * @throws HTTPException
     */
    public function remove(string $pslaUUID): void
    {
        try {
            $psla = $this->pslaDao->getById($pslaUUID);
            $this->pslaDao->delete($psla, true);
        } catch (Throwable $e) {
            throw HTTPException::notFound("Ce psla n'existe pas.", $e);
        }
    }

    public function removeTypeDempruntPsla(string $typesEmpruntsUUID, string $pslaUUID): void
    {
        $typeEmpruntPsla = $this->typeEmpruntPslaDao->findByTypeEmpruntAndPsla(
            $typesEmpruntsUUID,
            $pslaUUID
        );

        if (empty($typeEmpruntPsla)) {
            return;
        }

        $this->typeEmpruntPslaDao->delete($typeEmpruntPsla);
    }

    /**
     * @return Psla[]|ResultIterator
     */
    public function findBySimulationAndType(string $simulationId, int $type): ResultIterator
    {
        return $this->pslaDao->findBySimulationIDAndType($simulationId, $type);
    }

    public function clonePsla(Simulation $newSimulation, Simulation $oldSimulation): void
    {
        $objects = $this->pslaDao->findBySimulation($oldSimulation);
        foreach ($objects as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $this->pslaDao->save($newObject);
            foreach ($object->getPslaPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setPsla($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
            foreach ($object->getTypeEmpruntPsla() as $typeEmpruntObject) {
                $newTypeEmprunt = clone $typeEmpruntObject;
                $newTypeEmprunt->setPsla($newObject);
                $this->typeEmpruntPslaDao->save($newTypeEmprunt);
            }
        }
    }

    public function fusionPsla(Simulation $newSimulation, Simulation $oldSimulation1, Simulation $oldSimulation2): void
    {
        $identifees1 = $this->pslaDao->findBySimulationIDAndType($oldSimulation1->getId(), 0);
        $nonidentifees1 = $this->pslaDao->findBySimulationIDAndType($oldSimulation1->getId(), 1);

        $identifees2 = $this->pslaDao->findBySimulationIDAndType($oldSimulation2->getId(), 0);
        $nonidentifees2 = $this->pslaDao->findBySimulationIDAndType($oldSimulation2->getId(), 1);

        foreach ($identifees1 as $key => $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $newObject->setNumero($key + 1);
            $this->pslaDao->save($newObject);

            foreach ($object->getPslaPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setPsla($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
            foreach ($object->getTypeEmpruntPsla() as $typeEmpruntObject) {
                $newTypeEmprunt = clone $typeEmpruntObject;
                $newTypeEmprunt->setPsla($newObject);
                $this->typeEmpruntPslaDao->save($newTypeEmprunt);
            }
        }
        foreach ($nonidentifees1 as $key => $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $newObject->setNumero($key + 1);
            $this->pslaDao->save($newObject);

            foreach ($object->getPslaPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setPsla($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
            foreach ($object->getTypeEmpruntPsla() as $typeEmpruntObject) {
                $newTypeEmprunt = clone $typeEmpruntObject;
                $newTypeEmprunt->setPsla($newObject);
                $this->typeEmpruntPslaDao->save($newTypeEmprunt);
            }
        }

        foreach ($identifees2 as $key => $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $newObject->setNumero(count($identifees1) + $key + 1);
            $this->pslaDao->save($newObject);

            foreach ($object->getPslaPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setPsla($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
            foreach ($object->getTypeEmpruntPsla() as $typeEmpruntObject) {
                $newTypeEmprunt = clone $typeEmpruntObject;
                $newTypeEmprunt->setPsla($newObject);
                $this->typeEmpruntPslaDao->save($newTypeEmprunt);
            }
        }
        foreach ($nonidentifees2 as $key => $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $newObject->setNumero(count($nonidentifees1) + $key + 1);
            $this->pslaDao->save($newObject);

            foreach ($object->getPslaPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setPsla($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
            foreach ($object->getTypeEmpruntPsla() as $typeEmpruntObject) {
                $newTypeEmprunt = clone $typeEmpruntObject;
                $newTypeEmprunt->setPsla($newObject);
                $this->typeEmpruntPslaDao->save($newTypeEmprunt);
            }
        }
    }

    /**
     *  @param string[] $init
     *
     *  @return mixed[]
     */
    public function getHeadersByAnneeDeReference(string $prefix, array $init, string $simulationId): array
    {
        $simulation = $this->simulationDao->getById($simulationId);
        $anneeDeReference = $simulation->getAnneeDeReference();

        for ($i = 0; $i < 50; $i++) {
            $init[] = $prefix . (intval($anneeDeReference) + $i);
        }

        return $init;
    }

    public function exportPsla(Worksheet $sheet, int $type, string $simulationId): Worksheet
    {
        switch ($type) {
            case 0:
                $sheet = $this->writeIdentifiees($sheet, $simulationId, $type);
                break;
            case 1:
                break;
        }

        return $sheet;
    }

    public function writeIdentifiees(Worksheet $sheet, string $simulationId, int $type): Worksheet
    {
        $writeData = [];
        $headers = [
            'N°',
            'Nom de l\'opération',
            'Type',
            'DIRECT/SCI',
            '% de détention',
            'Opération en stock (*)',
            'Nombre de logts',
            'Prix de vente',
            'Taux marge brute',
            'Durée moyenne (mois)',
            'Date livraison',
            'Loyer mensuel (€/logt)',
            'Taux d\'évolution du loyer',
        ];

        $headers = $this->getHeadersByAnneeDeReference('Contrats location accession ', $headers, $simulationId);
        $headers = $this->getHeadersByAnneeDeReference('Levées d\'option ', $headers, $simulationId);
        $headers = $this->getHeadersByAnneeDeReference('Coûts internes ', $headers, $simulationId);

        $headers[] = 'Montant des subventions';
        $headers[] = 'Total emprunts';
        $headers[] = 'Numéro d\'emprunt';
        $headers[] = 'Date emprunt';
        $headers[] = 'Montant';

        $writeData[] = $headers;

        $pslas = $this->findBySimulationAndType($simulationId, $type);
        $typeEmpruntsPslaNumber = 0;

        foreach ($pslas as $psla) {
            $typeEmpruntPslas = $psla->getTypeEmpruntPsla();
            $typeEmpruntsPslaNumber += count($typeEmpruntPslas);
            $row = [];

            if (count($typeEmpruntPslas) !== 0) {
                foreach ($typeEmpruntPslas as $key => $value) {
                    $row = [];
                    if ($key === 0) {
                        $row[] = $psla->getNumero();
                        $row[] = $psla->getNom();
                        $row[] = 'Identifié';
                        $row[] = $psla->getDirectSci();
                        $row[] = $psla->getDetention();
                        $row[] = $psla->getOperationStock() === true ? 'Oui' : 'Non';
                        $row[] = $psla->getNombreLogements();
                        $row[] = $psla->getPrixVente();
                        $row[] = $psla->getTauxBrute();
                        $row[] = $psla->getDureeConstruction();
                        $row[] = $psla->getDateLivraison();
                        $row[] = $psla->getLoyerMensuel();
                        $row[] = $psla->getTauxEvolution();
                        $periodiques = $psla->getPslaPeriodique();

                        foreach ($periodiques as $periodique) {
                            $row[] = $periodique->getContratsAccession();
                        }

                        foreach ($periodiques as $periodique) {
                            $row[] = $periodique->getLeveesOption();
                        }

                        foreach ($periodiques as $periodique) {
                            $row[] = $periodique->getCoutsInternes();
                        }

                        $row[] = $psla->getMontantSubventions();
                        $row[] = 'SUM';
                        $row[] = $value->getTypesEmprunts()->getNumero();
                        $row[] = $value->getDatePremiere();
                        $row[] = $value->getMontant();
                    } else {
                        $row[] = $psla->getNumero();
                        $row[] = $psla->getNom();

                        for ($i = 0; $i < 163; $i++) {
                            $row[] = null;
                        }

                        $row[] = $value->getTypesEmprunts()->getNumero();
                        $row[] = $value->getDatePremiere();
                        $row[] = $value->getMontant();
                    }
                    $writeData[] = $row;
                }
            } else {
                $typeEmpruntsPslaNumber++;

                $row[] = $psla->getNumero();
                $row[] = $psla->getNom();
                $row[] = 'Identifié';
                $row[] = $psla->getDirectSci();
                $row[] = $psla->getDetention();
                $row[] = $psla->getOperationStock() === true ? 'Oui' : 'Non';
                $row[] = $psla->getNombreLogements();
                $row[] = $psla->getPrixVente();
                $row[] = $psla->getTauxBrute();
                $row[] = $psla->getDureeConstruction();
                $row[] = $psla->getDateLivraison();
                $row[] = $psla->getLoyerMensuel();
                $row[] = $psla->getTauxEvolution();
                $periodiques = $psla->getPslaPeriodique();

                foreach ($periodiques as $periodique) {
                    $row[] = $periodique->getContratsAccession();
                }

                foreach ($periodiques as $periodique) {
                    $row[] = $periodique->getLeveesOption();
                }

                foreach ($periodiques as $periodique) {
                    $row[] = $periodique->getCoutsInternes();
                }

                $row[] = $psla->getMontantSubventions();
                $row[] = 'SUM';
                $row[] = null;
                $row[] = null;
                $row[] = null;
                $writeData[] = $row;
            }
        }

        $totalRow = ['Total'];

        for ($i = 0; $i < 167; $i++) {
            $totalRow[] = null;
        }

        $writeData[] = $totalRow;

        $sheet->setTitle('PSLA_I');
        $sheet->setCellValue('A1', 'Accession - PSLA identifié');
        $sheet->fromArray($writeData, null, 'A2', true);

        $sheet = $this->setBoldSheet([
            'A1',
            'A2:FL2',
        ], $sheet);

        $sheet->getStyle('A2:FL' . (count($writeData) + 1))->getFont()->setSize(10);
        $sheet->getStyle('A1')->getFont()->setSize(11);
        $sheet->getRowDimension(2)->setRowHeight(35);
        $sheet = $this->setBorderSheet(['A2:FL' . (count($writeData) + 1)], $sheet);
        $sheet->getStyle('A2:FL' . (count($writeData) + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        for ($i = 1; $i <= 168; $i++) {
            $column = $this->columnLetter($i);
            $sheet->getColumnDimension($column)->setwidth(25);
            $sheet->getStyle($column . '2')->getAlignment()->setWrapText(true);
            if ($i < 14 || $i > 163) {
                continue;
            }

            $sheet->setCellValue(
                $column . (count($writeData) + 1),
                '=SUM(' . $column . '3:' . $column . count($writeData) . ')'
            );
        }

        for ($i = 3; $i <= count($writeData); $i++) {
            $value = $this->getCellValue($sheet, $i, 'FI');

            if ($value !== 'SUM') {
                continue;
            }

            $lastRow = 0;

            for ($j = $i + 1; $j <= count($writeData); $j++) {
                $nextValue = $this->getCellValue($sheet, $j, 'FI');

                if ($nextValue === 'SUM') {
                    $lastRow = $j - 1;
                    break;
                }

                if ($j !== count($writeData)) {
                    continue;
                }

                $lastRow = count($writeData);
            }

            if ($lastRow === 0) {
                $sheet->setCellValue(
                    'FI' . $i,
                    '=FL' . $i
                );
            } else {
                $sheet->setCellValue(
                    'FI' . $i,
                    '=SUM(FL' . $i . ':FL' . $lastRow . ')'
                );
            }
        }

        return $sheet;
    }

    public function getCellValue(Worksheet $sheet, int $i, string $column): ?string
    {
        /** @var Cell $cell */
        $cell = $sheet->getCell($column . $i);

        return $cell->getValue();
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

    public function importPsla(Request $request, int $type, string $simulationId): string
    {
        $notification = '';
        switch ($type) {
            case 0:
                $notification = $this->importIdentifiees($request, $type, $simulationId);
                break;
            case 1:
                break;
        }

        return $notification;
    }

    public function importIdentifiees(Request $request, int $type, string $simulationId): string
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
            $isPslaIdentifees = false;
            // Get data from imported excel data

            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $worksheetTitle = $worksheet->getTitle();

                if ($worksheetTitle !== 'PSLA_I') {
                    continue;
                }

                $isPslaIdentifees = true;
            }

            if ($isPslaIdentifees === false) {
                throw HTTPException::badRequest('Il n\'y a pas de feuille correcte, veuillez vérifier à nouveau.');
            }

            $data['psla_identifiees'] = [
                'columnNames' => [],
                'columnValues' => [],
            ];

            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $worksheetTitle = $worksheet->getTitle();

                if ($worksheetTitle !== 'PSLA_I') {
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
                            $data['psla_identifiees']['columnNames'][] = $cell->getCalculatedValue();
                        } else {
                            $data['psla_identifiees']['columnValues'][$rowIndex][] = $cell->getCalculatedValue();
                        }
                    }
                }
            }

            $data['psla_identifiees']['columnValues'] = array_values($data['psla_identifiees']['columnValues']);
            array_pop($data['psla_identifiees']['columnValues']);

            $saveData = [];
            $temp = '';

            foreach ($data['psla_identifiees']['columnValues'] as $item) {
                if ($temp === $item[0]) {
                    continue;
                }

                array_push($saveData, $item);
                $temp = $item[0];
            }

            $typeEmpruntsNumeros = $this->getTypeEmpruntsData($data['psla_identifiees']['columnValues'], 0, 'numero');

            if ($typeEmpruntsNumeros[0] === 'wrong numero') {
                throw HTTPException::badRequest('Le numéro répété est détecté. S\'il vous plaît vérifier le numéro');
            }

            $typeEmpruntsMontantEmprunts = $this->getTypeEmpruntsData($data['psla_identifiees']['columnValues'], 0, 'montant');
            $typeEmpruntsdatePremieres = $this->getTypeEmpruntsData($data['psla_identifiees']['columnValues'], 0, 'datePremiere');

            foreach ($saveData as $key => $item) {
                $typeEmprunts = [];
                if (count($typeEmpruntsNumeros) !== 0) {
                    for ($i = 0; $i < count($typeEmpruntsNumeros[$key]); $i++) {
                        if (! isset($typeEmpruntsNumeros[$key][$i])) {
                            continue;
                        }

                        $typeEmpts = $this->typeEmpruntService->fetchByNumero(strval($typeEmpruntsNumeros[$key][$i]));

                        if (count($typeEmpts) === 0) {
                            throw HTTPException::badRequest('Il n\'y a pas de tels typesemprunts');
                        }

                        $typeEmprunt = [
                            'id' => $typeEmpts[0]->getId() ,
                            'montant' => $typeEmpruntsMontantEmprunts[$key][$i],
                            'datePremiere' => $typeEmpruntsdatePremieres[$key][$i],
                        ];

                        array_push($typeEmprunts, json_encode($typeEmprunt));
                    }
                }

                $valueArray = ['Oui', 'Non'];
                if (! in_array($item[5], $valueArray)) {
                    throw HTTPException::badRequest('La valeur doit etre Non ou Oui');
                }

                $contratsAccession = [];
                $leveesOption = [];
                $coutsInternes = [];

                for ($i = 13; $i < 163; $i++) {
                    if ($i < 63) {
                        $contratsAccession[] = $item[$i];
                    }

                    if ($i >= 63 && $i < 113) {
                        $leveesOption[] = $item[$i];
                    }

                    if ($i < 113 || $i >= 163) {
                        continue;
                    }

                    $coutsInternes[] = $item[$i];
                }

                $oldPsla = $this->pslaDao->findOneByNumero($simulationId, intval($item[0]), 0);

                try {
                    if (count($oldPsla) > 0) {
                        $oldIdentifees = $this->factory->createPsla(
                            $oldPsla[0]->getId(),
                            $simulationId,
                            intval($item[0]),
                            strval($item[1]),
                            strval($item[3]),
                            $item[4],
                            $item[5] === 'Oui',
                            intval($item[6]),
                            $item[7],
                            $item[8],
                            intval($item[9]),
                            strval($item[10]),
                            $item[11],
                            $item[12],
                            null,
                            $item[163],
                            null,
                            $item[164],
                            0,
                            count($typeEmprunts) === 0 ? null : $typeEmprunts,
                            json_encode([
                                'contrats_accession' => $contratsAccession,
                                'levees_option' => $leveesOption,
                                'couts_internes' => $coutsInternes,
                            ])
                        );
                        $this->save($oldIdentifees);
                        $changedIds[] = $oldPsla[0]->getId();
                    } else {
                        $newIdentifees = $this->factory->createPsla(
                            null,
                            $simulationId,
                            intval($item[0]),
                            strval($item[1]),
                            strval($item[3]),
                            $item[4],
                            $item[5] === 'Oui',
                            intval($item[6]),
                            $item[7],
                            $item[8],
                            intval($item[9]),
                            strval($item[10]),
                            $item[11],
                            $item[12],
                            null,
                            $item[163],
                            null,
                            $item[164],
                            0,
                            count($typeEmprunts) === 0 ? null : $typeEmprunts,
                            json_encode([
                                'contrats_accession' => $contratsAccession,
                                'levees_option' => $leveesOption,
                                'couts_internes' => $coutsInternes,
                            ])
                        );
                        $this->save($newIdentifees);
                        $changedIds[] = $newIdentifees->getId();
                    }
                } catch (Throwable $e) {
                    throw HTTPException::badRequest('Impossible d\'importer des données', $e);
                }
            }

            $allPslas = $this->findBySimulationAndType($simulationId, Psla::TYPE_IDENTIFIEE);

            foreach ($allPslas as $psla) {
                if (in_array($psla->getId(), $changedIds)) {
                    continue;
                }

                $this->remove($psla->getId());
            }
        }

        return 'PSLA identifiées importé';
    }

    /**
     *  @param mixed[] $data
     *
     *  @return mixed[]
     */
    public function getTypeEmpruntsData(array $data, int $type, string $target): array
    {
        $result = [];

        switch ($type) {
            case 0:
                $numero = $data[0][0];
                $item = [];

                foreach ($data as $key => $value) {
                    if ($target === 'numero') {
                        if ($value[0] === $numero) {
                            if (in_array($value[165], $item)) {
                                return ['wrong numero'];
                            }

                            array_push($item, $value[165]);
                        } else {
                            array_push($result, $item);
                            $numero = $value[0];
                            $item = [];
                            array_push($item, $value[165]);
                        }
                    }

                    if ($target === 'montant') {
                        if ($value[0] === $numero) {
                            array_push($item, $value[167]);
                        } else {
                            array_push($result, $item);
                            $numero = $value[0];
                            $item = [];
                            array_push($item, $value[167]);
                        }
                    }

                    if ($target === 'datePremiere') {
                        if ($value[0] === $numero) {
                            array_push($item, $value[166]);
                        } else {
                            array_push($result, $item);
                            $numero = $value[0];
                            $item = [];
                            array_push($item, $value[166]);
                        }
                    }

                    if (end($data) !== $value) {
                        continue;
                    }

                    array_push($result, $item);
                }
                break;
            case 1:
                break;
            default:
                break;
        }

        return $result;
    }
}
