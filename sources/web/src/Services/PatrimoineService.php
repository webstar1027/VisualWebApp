<?php

declare(strict_types=1);

namespace App\Services;

use App\Dao\PatrimoineDao;
use App\Dao\PatrimoineLogementParametreDao;
use App\Dao\ProfilEvolutionLoyerDao;
use App\Dao\SimulationDao;
use App\Exceptions\HTTPException;
use App\Model\Factories\PatrimoineFactory;
use App\Model\Patrimoine;
use App\Model\PatrimoineLogementParametre;
use App\Model\Simulation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv as ReaderCsv;
use PhpOffice\PhpSpreadsheet\Reader\Ods as ReaderOds;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ReaderXlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use TheCodingMachine\TDBM\ResultIterator;
use Throwable;
use function array_pop;
use function array_push;
use function array_values;
use function chr;
use function count;
use function in_array;
use function intval;
use function strval;

final class PatrimoineService
{
    /** @var PatrimoineDao */
    private $patrimoineDao;

    /** @var ProfilEvolutionLoyerDao */
    private $profilEvolutionLoyerDao;

    /** @var PatrimoineLogementParametreDao */
    private $patrimoineLogementParametreDao;

    /** @var SimulationDao */
    private $simulationDao;

    /** @var PatrimoineFactory */
    private $factory;

    public function __construct(PatrimoineDao $patrimoineDao, PatrimoineLogementParametreDao $patrimoineLogementParametreDao, SimulationDao $simulationDao, ProfilEvolutionLoyerDao $profilEvolutionLoyerDao, PatrimoineFactory $factory)
    {
        $this->patrimoineDao = $patrimoineDao;
        $this->patrimoineLogementParametreDao = $patrimoineLogementParametreDao;
        $this->simulationDao = $simulationDao;
        $this->profilEvolutionLoyerDao = $profilEvolutionLoyerDao;
        $this->factory = $factory;
    }

    /**
     * @return ResultIterator|Patrimoine[]
     */
    public function fetchBySimulationId(string $simulationID): ResultIterator
    {
        return $this->patrimoineDao->findBySimulationID($simulationID);
    }

    /**
     * @return ResultIterator|Patrimoine[]
     */
    public function findBySimulationIdAndNumeroGroupe(string $simulationID, int $numeroGroupe): ResultIterator
    {
        return $this->patrimoineDao->findBySimulationIdAndNumeroGroupe($simulationID, $numeroGroupe);
    }

    /**
     * @throws HTTPException
     */
    public function save(Patrimoine $patrimoine): void
    {
        try {
            $this->patrimoineDao->save($patrimoine);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Ce patrimoine existe déjà.', $e);
        }
    }

    /**
     * @throws HTTPException
     */
    public function remove(string $patrimoineUUID): void
    {
        try {
            $patrimoine = $this->patrimoineDao->getById($patrimoineUUID);
        } catch (Throwable $e) {
            throw HTTPException::badRequest("Ce patrimoine n'existe pas.", $e);
        }

        $this->patrimoineDao->delete($patrimoine);
    }

    public function createDefaultParametrePatrimoineLogements(Simulation $simulationID): void
    {
        $parametrePatrimoineLogement = new PatrimoineLogementParametre($simulationID);
        $parametrePatrimoineLogement->setNombrePondere(null);
        $parametrePatrimoineLogement->setMontantLoyers(null);
        $this->patrimoineLogementParametreDao->save($parametrePatrimoineLogement);
    }

    public function savePatrimoineLogementParametre(string $simulationId, ?int $nombrePondereLogement, ?float $montant): ?PatrimoineLogementParametre
    {
        $patrimoineLogementParametre = $this->patrimoineLogementParametreDao->findOneBySimulationId($simulationId);
        if ($patrimoineLogementParametre === null) {
            try {
                $simulation = $this->simulationDao->getById($simulationId);
            } catch (Throwable $e) {
                throw new \Symfony\Component\HttpKernel\Exception\HttpException(Response::HTTP_NOT_FOUND, "La simulation n'existe pas.", $e);
            }
            $patrimoineLogementParametre = new PatrimoineLogementParametre($simulation);
            $patrimoineLogementParametre->setNombrePondere($nombrePondereLogement);
            $patrimoineLogementParametre->setMontantLoyers($montant);
        } else {
            $patrimoineLogementParametre->setNombrePondere($nombrePondereLogement);
            $patrimoineLogementParametre->setMontantLoyers($montant);
        }
        $this->patrimoineLogementParametreDao->save($patrimoineLogementParametre);

        return $patrimoineLogementParametre;
    }

    public function exportPatrimoines(Worksheet $sheet, string $simulationId): Worksheet
    {
        $writeData = [];
        $headers = [
            'N°groupe',
            'N° sous groupe',
            'Nom du groupe',
            'information (Champ en saisie libre)',
            'Conventionné',
            'Surface quittancée (Infobulle : Surface habitable utile ou corrigée servant au calcul du quittancement avant vacance)',
            'Nombre de logements (Infobulle : Uniquement les logements familiaux)',
            'Loyer mensuel € / m²',
            'Loyer mensuel plafond € / m²',
            'Profil d\'évolution des loyers',
            'Secteur financier',
            'Zone géographique',
            'Nature l\'opération',
            'Type habitat',
            'Réhabilité',
            'Année MES',
        ];

        array_push($writeData, $headers);
        $patrimoines = $this->fetchBySimulationId($simulationId);

        foreach ($patrimoines as $patrimoine) {
            $profilEvolutionLoyer = $patrimoine->getProfilsEvolutionLoyers();
            $row = [
                $patrimoine->getNGroupe(),
                $patrimoine->getNSousGroupe(),
                $patrimoine->getNomGroupe(),
                $patrimoine->getInformations(),
                $patrimoine->getConventionne() === true ? 'Oui' : 'Non',
                $patrimoine->getSurfaceQuittancee(),
                $patrimoine->getNombreLogements(),
                $patrimoine->getLoyerMensuel(),
                $patrimoine->getLoyerMensuelPlafond(),
                empty($profilEvolutionLoyer) ? null : $profilEvolutionLoyer->getNumero(),
                $patrimoine->getSecteurFinancier(),
                $patrimoine->getZoneGeographique(),
                $patrimoine->getNatureOperation(),
                $patrimoine->getTypeHabitat(),
                $patrimoine->getRehabilite() === true ? 'Oui' : 'Non',
                $patrimoine->getAnneeMes(),
            ];

            array_push($writeData, $row);
        }

        $totalRow = ['Total', null, null, null, null, null, null, null, null, null, null, null, null, null, null, null];
        array_push($writeData, $totalRow);

        $sheet->setTitle('patrimoine');
        $sheet->setCellValue('A1', 'Patrimoine');
        $sheet->fromArray($writeData, null, 'A2', true);
        $sheet->getStyle('A1:A' . (count($patrimoines) + 3))->getFont()->setBold(true);
        $sheet->getStyle('B2:P2')->getFont()->setBold(true);
        $sheet->getRowDimension(2)->setRowHeight(50);
        $sheet->getColumnDimension('A')->setWidth(20);

        for ($i = 2; $i <= count($headers); $i++) {
            $column = $this->columnLetter($i);
            $sheet->getColumnDimension($column)->setWidth(20);
            $sheet->getStyle($column . '2')->getAlignment()->setWrapText(true);

            if ($i >= 6 && $i < 8) {
                $sheet->setCellValue(
                    $column . (count($patrimoines) + 3),
                    '=SUM(' . $column . '3:' . $column . (count($patrimoines) + 2) . ')'
                );
            }

            if ($i < 8 || $i >= 10) {
                continue;
            }

            $sheet->setCellValue(
                $column . (count($patrimoines) + 3),
                '=AVERAGE(' . $column . '3:' . $column . (count($patrimoines) + 2) . ')'
            );
        }

        $sheet->getColumnDimension('F')->setWidth(38);
        $sheet->getColumnDimension('G')->setWidth(30);
        $sheet->getStyle('A2:P' . (count($patrimoines) + 3))->getFont()->setSize(10);
        $sheet->getStyle('A2:P' . (count($patrimoines) + 3))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2:P' . (count($patrimoines) + 3))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

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

    public function importPatrimoines(Request $request, string $simulationId): string
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
            $isPatrimoines = false;
            // Get data from imported excel data
            $changedIds = [];
            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $worksheetTitle = $worksheet->getTitle();

                if ($worksheetTitle !== 'patrimoine') {
                    continue;
                }

                $isPatrimoines = true;
            }

            if ($isPatrimoines === false) {
                throw HTTPException::badRequest('Il n\'y a pas de feuille correcte, veuillez vérifier à nouveau.');
            }

            $data['patrimoine'] = [
                'columnNames' => [],
                'columnValues' => [],
            ];

            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $worksheetTitle = $worksheet->getTitle();

                if ($worksheetTitle !== 'patrimoine') {
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
                            $data['patrimoine']['columnNames'][] = $cell->getCalculatedValue();
                        } else {
                            $data['patrimoine']['columnValues'][$rowIndex][] = $cell->getCalculatedValue();
                        }
                    }
                }
            }

            $data['patrimoine']['columnValues'] = array_values($data['patrimoine']['columnValues']);
            array_pop($data['patrimoine']['columnValues']);

            foreach ($data['patrimoine']['columnValues'] as $item) {
                $valueArray = ['Oui', 'Non'];

                if (! in_array($item[4], $valueArray) || ! in_array($item[14], $valueArray)) {
                    throw HTTPException::badRequest('La valeur doit etre Non ou Oui');
                }

                $profilsEvolutionLoyersId = null;
                if ($item[9]) {
                    $profilEvolutionLoyers = $this->profilEvolutionLoyerDao->findByNumero(strval($item[9]));
                    if (count($profilEvolutionLoyers) === 0) {
                        throw HTTPException::badRequest('Le numéro de profil d\'évolution de loyer n\'existe pas.');
                    }
                    $profilsEvolutionLoyersId = $profilEvolutionLoyers[0]->getId();
                }
                $oldPatrimoine = $this->patrimoineDao->findOneByNGroupe($simulationId, intval($item[0]));

                try {
                    if (count($oldPatrimoine) > 0) {
                        $patrimoine = $this->factory->createPatrimoine(
                            $oldPatrimoine[0]->getId(),
                            $simulationId,
                            intval($item[0]),
                            intval($item[1]),
                            strval($item[2]),
                            strval($item[3]),
                            $item[4] === 'Oui',
                            $item[5],
                            $item[6],
                            $item[7],
                            $item[8],
                            strval($item[10]),
                            strval($item[11]),
                            strval($item[12]),
                            strval($item[13]),
                            $item[14] === 'Oui',
                            $item[15],
                            $profilsEvolutionLoyersId
                        );
                        $this->save($patrimoine);
                        $changedIds[] = $patrimoine->getId();
                    } else {
                        $patrimoine = $this->factory->createPatrimoine(
                            null,
                            $simulationId,
                            intval($item[0]),
                            intval($item[1]),
                            strval($item[2]),
                            strval($item[3]),
                            $item[4] === 'Oui',
                            $item[5],
                            $item[6],
                            $item[7],
                            $item[8],
                            strval($item[10]),
                            strval($item[11]),
                            strval($item[12]),
                            strval($item[13]),
                            $item[14] === 'Oui',
                            $item[15],
                            $profilsEvolutionLoyersId
                        );

                        $this->save($patrimoine);
                        $changedIds[] = $patrimoine->getId();
                    }
                } catch (Throwable $e) {
                    throw HTTPException::badRequest('Impossible d\'importer des données', $e);
                }
            }

            $allPatrimoine = $this->fetchBySimulationId($simulationId);
            foreach ($allPatrimoine as $key => $value) {
                $id = $value->getId();
                if (in_array($id, $changedIds)) {
                    continue;
                }

                $this->remove($value->getId());
            }
        }

        return 'Patrimoine importé.';
    }

    public function clonePatrimoine(Simulation $newSimulation, Simulation $oldSimulation): void
    {
        //todo test
        $objects = $this->patrimoineDao->findBySimulation($oldSimulation);
        foreach ($objects as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $newProfil = clone $object;
            $newProfil= $newProfil->getProfilsEvolutionLoyers();
            $newObject->setProfilsEvolutionLoyers($newProfil);
            $this->save($newObject);
        }
    }

    public function fusionPatrimoine(Simulation $newSimulation, Simulation $oldSimulation1, Simulation $oldSimulation2): void
    {
        $objects1 = $this->patrimoineDao->findBySimulation($oldSimulation1);
        $objects2 = $this->patrimoineDao->findBySimulation($oldSimulation2);
        foreach ($objects1 as $key => $object) {
            $newObject = clone $object;
            $newObject->setNGroupe($key + 1);
            $newObject->setSimulation($newSimulation);
            $newProfil = clone $object;
            $newProfil= $newProfil->getProfilsEvolutionLoyers();
            $newObject->setProfilsEvolutionLoyers($newProfil);
            $this->save($newObject);
        }
        foreach ($objects2 as $key => $object) {
            $newObject = clone $object;
            $newObject->setNGroupe(count($objects1) + $key + 1);
            $newObject->setSimulation($newSimulation);
            $newProfil = clone $object;
            $newProfil= $newProfil->getProfilsEvolutionLoyers();
            $newObject->setProfilsEvolutionLoyers($newProfil);
            $this->save($newObject);
        }
    }
}
