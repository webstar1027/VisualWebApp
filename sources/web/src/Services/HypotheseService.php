<?php

declare(strict_types=1);

namespace App\Services;

use App\Dao\HypotheseDao;
use App\Exceptions\HTTPException;
use App\Model\Hypothese;
use App\Model\Simulation;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use TheCodingMachine\TDBM\ResultIterator;
use Throwable;
use function in_array;

final class HypotheseService
{
    /** @var HypotheseDao */
    private $hypotheseDao;

    public function __construct(HypotheseDao $hypotheseDao)
    {
        $this->hypotheseDao = $hypotheseDao;
    }

    /**
     * @return ResultIterator|Hypothese[]
     */
    public function fetchBySimulationId(string $simulationID): ResultIterator
    {
        return $this->hypotheseDao->findBySimulationID($simulationID);
    }

    /**
     * @throws HTTPException
     */
    public function save(Hypothese $hypothese): void
    {
        try {
            $this->hypotheseDao->save($hypothese);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Cette hypothèse existe déjà.', $e);
        }
    }

    /**
     * @throws HTTPException
     */
    public function remove(string $hypotheseId): void
    {
        try {
            $hypothese = $this->hypotheseDao->getById($hypotheseId);
        } catch (Throwable $e) {
            throw HTTPException::badRequest("Cette hypothèse n'existe pas.", $e);
        }

        $this->hypotheseDao->delete($hypothese);
    }

    public function exportHypothese(Worksheet $sheet, string $simulationId): Worksheet
    {
        $hypotheseArray =  $this->fetchBySimulationId($simulationId);
        $hypothese = $hypotheseArray[0];

        $sheet->setCellValue('A1', 'Hypothèses liées aux investissements');
        $sheet->setCellValue('A2', 'Impact sur le potentiel financier');
        $sheet->setCellValue('A3', 'Mobilisation des fonds propres');
        $sheet->setCellValue('A5', 'Hypothèses liées aux opérations nouvelles de logements locatifs');
        $sheet->setCellValue('A6', 'Maintenance courante');
        $sheet->setCellValue('A7', 'Gros entretien');
        $sheet->setCellValue('A8', 'Provision pour gros entretien');
        $sheet->setCellValue('A10', 'Taux de vacance logements');
        $sheet->setCellValue('A11', 'Taux de vacance sur les garages / parkings');
        $sheet->setCellValue('A12', 'Taux de vacance sur les commerces');
        $sheet->setCellValue('A14', "Variation à la hausse / baisse selon l'évolution du patrimoine logements");
        $sheet->setCellValue('A15', 'Frais de personnel');
        $sheet->setCellValue('A16', 'Frais de gestion');
        $sheet->setCellValue('A17', 'Application des frais de personnel et de gestion sur la variation des logts par tranche de :');
        $sheet->setCellValue('A19', "Montage et conduite d'opération locative");
        $sheet->setCellValue('A20', "Taux pour la maîtrise d'ouvrage directe");
        $sheet->setCellValue('A21', 'Taux pour les acquisitions en VEFA');
        $sheet->setCellValue('A22', 'Taux pour la réhabilitation');

        $noBoldColumns = [4, 9, 13, 18];

        for ($i = 1; $i <= 22; $i++) {
            if (in_array($i, $noBoldColumns)) {
                continue;
            }

            $sheet->getStyle('A' . $i)->getFont()->setBold(true);
        }

        $noBoldColumns = [1, 2, 4, 5, 9, 13, 14, 18, 19];

        for ($i = 1; $i <= 22; $i++) {
            if (in_array($i, $noBoldColumns)) {
                continue;
            }

            $sheet->getStyle('A' . $i)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        $mobilisation = $hypothese->getMobilisation();
        $type = [0 => 'A l\'OS', 1 => 'A la livraison'];
        $sheet->setCellValue('B3', $type[$mobilisation]);
        $sheet->setCellValue('B6', $hypothese->getMaintenance());
        $sheet->setCellValue('B7', $hypothese->getGrosEntretien());
        $sheet->setCellValue('B8', $hypothese->getProvisionGros());
        $sheet->setCellValue('B10', $hypothese->getTauxVacance());
        $sheet->setCellValue('B11', $hypothese->getTauxVacanceGarages());
        $sheet->setCellValue('B12', $hypothese->getTauxVacanceCommerces());
        $sheet->setCellValue('B15', $hypothese->getFraisPersonnel());
        $sheet->setCellValue('B16', $hypothese->getFraisGestion());
        $sheet->setCellValue('B17', $hypothese->getSeuilDeclenchement());
        $sheet->setCellValue('B20', $hypothese->getTauxDirecte());
        $sheet->setCellValue('B21', $hypothese->getTauxVefa());
        $sheet->setCellValue('B22', $hypothese->getTauxRehabilitation());

        $boldColumns = [3, 6, 7, 8, 10, 11, 12, 15, 16, 17, 20, 21, 22];

        for ($i = 1; $i <= 22; $i++) {
            if (! in_array($i, $boldColumns)) {
                continue;
            }

            $sheet->getStyle('B' . $i)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        $sheet->setCellValue('C6', '€/lgt');
        $sheet->setCellValue('C7', '€/lgt');
        $sheet->setCellValue('C8', '€/lgt');
        $sheet->setCellValue('C10', '%');
        $sheet->setCellValue('C11', '%');
        $sheet->setCellValue('C12', '%');
        $sheet->setCellValue('C15', '€/logt');
        $sheet->setCellValue('C16', '€/logt');
        $sheet->setCellValue('C20', '%');
        $sheet->setCellValue('C21', '%');
        $sheet->setCellValue('C22', '%');

        $boldColumns = [6, 7, 8, 10, 11, 12, 15, 16, 20, 21, 22];

        for ($i = 1; $i <= 22; $i++) {
            if (! in_array($i, $boldColumns)) {
                continue;
            }

            $sheet->getStyle('C' . $i)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        $sheet->setCellValue('D6', 'avec un différé de  ' . $hypothese->getMaintenanceDiffere());
        $sheet->setCellValue('D7', 'avec un différé de  ' . $hypothese->getGrosEntretienDiffere());
        $sheet->setCellValue('D8', 'avec un différé de  ' . $hypothese->getProvisionGrosDiffere());

        $boldColumns = [6, 7, 8];

        for ($i = 1; $i <= 22; $i++) {
            if (! in_array($i, $boldColumns)) {
                continue;
            }

            $sheet->getStyle('D' . $i)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        $sheet->setCellValue('E6', 'ans');
        $sheet->setCellValue('E7', 'ans');
        $sheet->setCellValue('E8', 'ans');

        for ($i = 1; $i <= 22; $i++) {
            if (! in_array($i, $boldColumns)) {
                continue;
            }

            $sheet->getStyle('E' . $i)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        $sheet->setTitle('hypothese');
        $sheet->getColumnDimension('A')->setwidth(85);
        $sheet->getColumnDimension('B')->setwidth(20);
        $sheet->getColumnDimension('C')->setwidth(15);
        $sheet->getColumnDimension('D')->setwidth(25);
        $sheet->getColumnDimension('E')->setwidth(10);
        $sheet->getStyle('A3:E22')->getFont()->setSize(10);
        $sheet->getStyle('A1:E22')->getFont()->setSize(10);
        $sheet->getStyle('A2:A22')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('B1:E22')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return $sheet;
    }

    public function cloneHypothese(Simulation $newSimulation, Simulation $oldSimulation): void
    {
        $objects = $this->hypotheseDao->findBySimulationID($oldSimulation->getId());
        foreach ($objects as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
        }
    }
}
