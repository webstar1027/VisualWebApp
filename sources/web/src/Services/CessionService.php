<?php

declare(strict_types=1);

namespace App\Services;

use App\Dao\CessionDao;
use App\Dao\CessionPeriodiqueDao;
use App\Dao\PatrimoineDao;
use App\Dao\SimulationDao;
use App\Exceptions\HTTPException;
use App\Model\Cession;
use App\Model\Factories\CessionFactory;
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
use function array_values;
use function chr;
use function count;
use function in_array;
use function intval;
use function Safe\json_encode;

class CessionService
{
    /** @var CessionDao */
    private $cessionDao;
    /** @var SimulationDao */
    private $simulationDao;
    /** @var PatrimoineDao */
    private $patrimoineDao;
     /** @var CessionFactory */
    private $factory;
    /** @var CessionPeriodiqueDao */
    private $periodiqueDao;

    public function __construct(CessionDao $cessionDao, CessionFactory $factory, SimulationDao $simulationDao, PatrimoineDao $patrimoineDao, CessionPeriodiqueDao $periodiqueDao)
    {
        $this->cessionDao = $cessionDao;
        $this->factory = $factory;
        $this->simulationDao = $simulationDao;
        $this->patrimoineDao = $patrimoineDao;
        $this->periodiqueDao = $periodiqueDao;
    }

    public function save(Cession $cession): void
    {
        try {
            $this->cessionDao->save($cession);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Cette cession existe déjà.', $e);
        }
    }

    /**
     * @throws TDBMException
     */
    public function remove(string $cessionUUID): void
    {
        try {
            $operation = $this->cessionDao->getById($cessionUUID);
            $this->cessionDao->delete($operation, true);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Cette cession n\'existe déjà.', $e);
        }
    }

    /**
     * @return Cession[]|ResultIterator
     */
    public function findBySimulation(string $simulationId): ResultIterator
    {
        return $this->cessionDao->findBySimulationID($simulationId);
    }

    /**
     * @return Cession[]|ResultIterator
     */
    public function findBySimulationAndType(string $simulationId, int $type): ResultIterator
    {
        return $this->cessionDao->findBySimulationIDAndType($simulationId, $type);
    }

    public function exportCession(Worksheet $sheet, int $type, string $simulationId): Worksheet
    {
        switch ($type) {
            case 0:
                $sheet = $this->writeCessionIdentifes($sheet, $simulationId);
                break;
            case 1:
                break;
        }

        return $sheet;
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

    public function writeCessionIdentifes(Worksheet $sheet, string $simulationId): Worksheet
    {
        $writeData = [];
        $headers = [
            'N° groupe',
            'N° sous-groupe',
            'Information',
            'Nom du groupe',
            'Nb de logts',
            'Nature',
            'A indexer à l\'inflation',
            'Réduction TFPB en € / lgt cédé',
            'Réduction de GE / lgt cédé',
            'Réduction de maintenance courante en € / lgt cédé',
            'Cession fin de mois',
        ];

        $headers = $this->getHeadersByAnneeDeReference('Mois cession ', $headers, $simulationId);
        $headers = $this->getHeadersByAnneeDeReference('Nb logts ', $headers, $simulationId);
        $headers = $this->getHeadersByAnneeDeReference('Prix net de cession K€/logt ', $headers, $simulationId);
        $headers = $this->getHeadersByAnneeDeReference('Remboursements anticipés en K€ ', $headers, $simulationId);
        $headers = $this->getHeadersByAnneeDeReference('Economies d\'annuités cumulée suite à RA - part capital ', $headers, $simulationId);
        $headers = $this->getHeadersByAnneeDeReference('Economies d\'annuités cumulée suite à RA - part intérêts  ', $headers, $simulationId);
        $headers = $this->getHeadersByAnneeDeReference('Valeur comptable en K€ / lgt cédé ', $headers, $simulationId);

        $headers[] = 'Réduction d\'amortissement technique annuelle (k€)';
        $headers[] = 'Réduction de reprise de subvention annuelle (k€)';
        $headers[] = 'Durée d\'amortissement technique résiduelle (année)';
        $writeData[] = $headers;

        $cessions = $this->findBySimulationAndType($simulationId, 0);

        foreach ($cessions as $cession) {
            $row = [];
            $row[] = $cession->getNGroupe();
            $row[] = $cession->getNSousGroupe();
            $row[] = $cession->getInformations();
            $row[] = $cession->getNomGroupe();
            /** @var int $nGoupe */
            $nGoupe = $cession->getNGroupe();
            $patrimoine = $this->patrimoineDao->findOneByNGroupe($simulationId, $nGoupe)[0];
            $row[] = isset($patrimoine) ? $patrimoine->getNombreLogements() : null;
            $row[] = $cession->getNature();
            $row[] = $cession->getIndexerInflation()=== true ? 'Oui' : 'Non';
            $row[] = $cession->getReductionTfpb();
            $row[] = $cession->getReductionGe();
            $row[] = $cession->getReductionMaintenance();
            $row[] = $cession->getCessionFinMois();

            $periodiques = $cession->getCessionPeriodique();

            foreach ($periodiques as $periodique) {
                $row[] = $periodique->getMoisCession();
            }

            foreach ($periodiques as $periodique) {
                $row[] = $periodique->getNombreLogements();
            }

            foreach ($periodiques as $periodique) {
                $row[] = $periodique->getPrixNetsCession();
            }

            foreach ($periodiques as $periodique) {
                $row[] = $periodique->getRemboursementAnticipe();
            }

            foreach ($periodiques as $periodique) {
                $row[] = $periodique->getEcomoniesCapital();
            }

            foreach ($periodiques as $periodique) {
                $row[] = $periodique->getEcomoniesInterets();
            }

            foreach ($periodiques as $periodique) {
                $row[] = $periodique->getValeurCede();
            }

            $row[] = $cession->getReductionAmortissementAnnuelle();
            $row[] = $cession->getReductionRepriseAnnuelle();
            $row[] = $cession->getDureeResiduelle();
            $writeData[] = $row;
        }

        $sheet->setTitle('CI');
        $sheet->setCellValue('A1', 'Cessions identifiées');
        $sheet->fromArray($writeData, null, 'A2', true);

        $sheet = $this->setBoldSheet([
            'A1',
            'A2:MZ2',
        ], $sheet);

        $sheet->getStyle('A2:MZ' . (count($writeData) + 1))->getFont()->setSize(10);
        $sheet->getStyle('A1')->getFont()->setSize(11);
        $sheet->getRowDimension(2)->setRowHeight(35);
        $sheet = $this->setBorderSheet(['A2:MZ' . (count($writeData) + 1)], $sheet);
        $sheet->getStyle('A2:MZ' . (count($writeData) + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        for ($i = 1; $i <= 364; $i++) {
            $column = $this->columnLetter($i);
            $sheet->getColumnDimension($column)->setwidth(25);
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

    public function importCession(Request $request, int $type, string $simulationId): string
    {
        $notification = '';
        switch ($type) {
            case 0:
                $notification = $this->importIdentifes($request, $simulationId);
                break;
            case 1:
                break;
        }

        return $notification;
    }

    public function importIdentifes(Request $request, string $simulationId): string
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
            $isIdentifees = false;
            // Get data from imported excel data

            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $worksheetTitle = $worksheet->getTitle();

                if ($worksheetTitle !== 'CI') {
                    continue;
                }

                $isIdentifees = true;
            }

            if ($isIdentifees === false) {
                throw HTTPException::badRequest('Il n\'y a pas de feuille correcte, veuillez vérifier à nouveau.');
            }

            $data['cession_identifees'] = [
                'columnNames' => [],
                'columnValues' => [],
            ];

            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $worksheetTitle = $worksheet->getTitle();

                if ($worksheetTitle !== 'CI') {
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
                            $data['cession_identifees']['columnNames'][] = $cell->getCalculatedValue();
                        } else {
                            $data['cession_identifees']['columnValues'][$rowIndex][] = $cell->getCalculatedValue();
                        }
                    }
                }
            }

            $data['cession_identifees']['columnValues'] = array_values($data['cession_identifees']['columnValues']);

            foreach ($data['cession_identifees']['columnValues'] as $key => $item) {
                $patrimoine = null;

                if (isset($item[0])) {
                    $patrimoines = $this->patrimoineDao->findOneByNGroupe($simulationId, intval($item[0]));

                    if (count($patrimoines) === 0) {
                        throw HTTPException::badRequest('Il n’existe pas une telle patrimoine numero - "' . $item[0] . '"');
                    }

                    $patrimoine = $patrimoines[0];
                }

                $valueArray = ['Oui', 'Non'];
                $defaultNatures = [
                    'Vente Hlm',
                    'Vente en bloc hors groupe',
                    'Vente en bloc groupe',
                    'Fin de bail LT',
                    'Fin usufruit locatif',
                    'Autres',
                ];

                $moisCession = [];
                $nombreLogements = [];
                $prixNetsCession = [];
                $remboursementAnticipe = [];
                $ecomoniesCapital = [];
                $ecomoniesInterets = [];
                $valeurCede = [];

                if (! in_array($item[6], $valueArray)) {
                    throw HTTPException::badRequest('La valeur doit etre Non ou Oui');
                }

                if (! in_array($item[5], $defaultNatures)) {
                    throw HTTPException::badRequest('Il n\'y a pas une telle nature.');
                }

                for ($i = 11; $i < 361; $i++) {
                    if ($i < 61) {
                        $moisCession[] = $item[$i];
                    }

                    if ($i >= 61 && $i < 111) {
                        $nombreLogements[] = $item[$i];
                    }

                    if ($i >= 111 && $i < 161) {
                        $prixNetsCession[] = $item[$i];
                    }

                    if ($i >= 161 && $i < 211) {
                        $remboursementAnticipe[] = $item[$i];
                    }

                    if ($i >= 211 && $i < 261) {
                        $ecomoniesCapital[] = $item[$i];
                    }

                    if ($i >= 261 && $i < 311) {
                        $ecomoniesInterets[] = $item[$i];
                    }

                    if ($i < 311 || $i >= 361) {
                        continue;
                    }

                    $valeurCede[] = $item[$i];
                }

                $oldCession = $this->cessionDao->findOneByNGroupe($simulationId, intval($item[0]), 1);

                try {
                    if (count($oldCession) > 0) {
                        $oldIdentifiee = $this->factory->createCession(
                            $oldCession[0]->getId(),
                            $simulationId,
                            intval($item[0]),
                            $patrimoine->getNSousGroupe(),
                            $patrimoine->getInformations(),
                            null,
                            $patrimoine->getNomGroupe(),
                            $item[5],
                            $item[6] === 'Oui',
                            intval($item[10]),
                            intval($item[361]),
                            $item[7],
                            $item[8],
                            $item[9],
                            $item[362],
                            $item[363],
                            null,
                            null,
                            null,
                            0,
                            json_encode([
                                'mois_cession' => $moisCession,
                                'nombre_logements' => $nombreLogements,
                                'prix_nets_cession' => $prixNetsCession,
                                'remboursement_anticipe' => $remboursementAnticipe,
                                'ecomonies_capital' => $ecomoniesCapital,
                                'ecomonies_interets' => $ecomoniesInterets,
                                'valeur_cede' => $valeurCede,
                            ])
                        );

                        $this->save($oldIdentifiee);
                        $changedIds[] = $oldCession[0]->getId();
                    } else {
                        $newCession = $this->factory->createCession(
                            null,
                            $simulationId,
                            intval($item[0]),
                            $patrimoine->getNSousGroupe(),
                            $patrimoine->getInformations(),
                            null,
                            $patrimoine->getNomGroupe(),
                            $item[5],
                            $item[6] === 'Oui',
                            intval($item[10]),
                            intval($item[361]),
                            $item[7],
                            $item[8],
                            $item[9],
                            $item[362],
                            $item[363],
                            null,
                            null,
                            null,
                            0,
                            json_encode([
                                'mois_cession' => $moisCession,
                                'nombre_logements' => $nombreLogements,
                                'prix_nets_cession' => $prixNetsCession,
                                'remboursement_anticipe' => $remboursementAnticipe,
                                'ecomonies_capital' => $ecomoniesCapital,
                                'ecomonies_interets' => $ecomoniesInterets,
                                'valeur_cede' => $valeurCede,
                            ])
                        );

                        $this->save($newCession);
                        $changedIds[] = $newCession->getId();
                    }
                } catch (Throwable $e) {
                    throw HTTPException::badRequest('Il y a une erreur à l\'importation.', $e);
                }
            }
            $allCessions = $this->findBySimulationAndType($simulationId, Cession::TYPE_IDENTIFIEE);

            foreach ($allCessions as $_cession) {
                if (in_array($_cession->getId(), $changedIds)) {
                    continue;
                }

                $this->remove($_cession->getId());
            }
        }

        return 'Cessions identifiées importé';
    }

    public function cloneCession(Simulation $newSimulation, Simulation $oldSimulation): void
    {
        $objects = $this->cessionDao->findBySimulation($oldSimulation);
        foreach ($objects as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getCessionPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setCession($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
    }

    public function fusionCession(Simulation $newSimulation, Simulation $oldSimulation1, Simulation $oldSimulation2): void
    {
        $identifees1 = $this->cessionDao->findBySimulationIDAndType($oldSimulation1->getId(), 0);
        $nonidentifees1 = $this->cessionDao->findBySimulationIDAndType($oldSimulation1->getId(), 1);

        $identifees2 = $this->cessionDao->findBySimulationIDAndType($oldSimulation2->getId(), 0);
        $nonidentifees2 = $this->cessionDao->findBySimulationIDAndType($oldSimulation2->getId(), 1);

        foreach ($identifees1 as $key => $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $newObject->setNGroupe($key + 1);
            $this->save($newObject);
            foreach ($object->getCessionPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setCession($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
        foreach ($nonidentifees1 as $key => $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $newObject->setNumero($key + 1);
            $this->save($newObject);
            foreach ($object->getCessionPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setCession($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }

        foreach ($identifees2 as $key => $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $newObject->setNGroupe(count($identifees1) + $key + 1);
            $this->save($newObject);
            foreach ($object->getCessionPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setCession($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
        foreach ($nonidentifees2 as $key => $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $newObject->setNumero(count($nonidentifees1) + $key + 1);
            $this->save($newObject);
            foreach ($object->getCessionPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setCession($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
    }
}
