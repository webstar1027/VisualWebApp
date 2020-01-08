<?php

declare(strict_types=1);

namespace App\Services;

use App\Dao\IndiceTauxDao;
use App\Dao\IndiceTauxPeriodiqueDao;
use App\Dao\SimulationDao;
use App\Exceptions\ValidatorException;
use App\Model\IndiceTaux;
use App\Model\IndiceTauxPeriodique;
use App\Model\Simulation;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Safe\Exceptions\StringsException;
use TheCodingMachine\TDBM\TDBMException;
use function array_push;
use function chr;
use function in_array;
use function intval;

final class IndiceTauxService
{
    /** @var SimulationDao */
    private $simulationDao;

    /** @var IndiceTauxDao */
    private $indiceTauxDao;

    /** @var IndiceTauxPeriodiqueDao */
    private $indiceTauxPeriodiqueDao;

    public function __construct(SimulationDao $simulationDao, IndiceTauxDao $indiceTauxDao, IndiceTauxPeriodiqueDao $indiceTauxPeriodiqueDao)
    {
        $this->simulationDao = $simulationDao;
        $this->indiceTauxDao = $indiceTauxDao;
        $this->indiceTauxPeriodiqueDao = $indiceTauxPeriodiqueDao;
    }

    /**
     * @throws TDBMException
     * @throws ValidatorException
     * @throws StringsException
     */
    public function createIndicesTaux(string $simulationID): void
    {
        $simulation = $this->simulationDao->getById($simulationID);
        $this->createIndiceTaux($simulation, IndiceTaux::TAUX_INFLATION_TYPE);
        $this->createIndiceTaux($simulation, IndiceTaux::TAUX_VARIATION_ICC_TYPE);
        $this->createIndiceTaux($simulation, IndiceTaux::TAUX_VARIATION_IRL_TYPE);
        $this->createIndiceTaux($simulation, IndiceTaux::TAUX_LIVRET_A_TYPE);
        $this->createIndiceTaux($simulation, IndiceTaux::TAUX_REMUNERATION_TRESORERIE_TYPE);
        $this->createIndiceTaux($simulation, IndiceTaux::TAUX_LIVRET_A_TYPE_SUB_TAUX_INFLATION_TYPE);
        $this->createIndiceTaux($simulation, IndiceTaux::TAUX_LIVRET_A_TYPE_SUB_TAUX_VARIATION_IRL_TYPE);
        $this->createIndiceTaux($simulation, IndiceTaux::TAUX_LIVRET_A_TYPE_SUB_TAUX_REMUNERATION_TRESORERIE_TYPE);
    }

    /**
     * @throws ValidatorException
     * @throws StringsException
     */
    private function createIndiceTaux(Simulation $simulation, string $type): void
    {
        $indiceTaux = new IndiceTaux($simulation, $type);
        switch ($type) {
            case IndiceTaux::TAUX_INFLATION_TYPE:
                $indiceTaux->setIndexationSurInflation(null);
                $indiceTaux->setEcart(null);
                break;
            case IndiceTaux::TAUX_LIVRET_A_TYPE_SUB_TAUX_INFLATION_TYPE:
                $indiceTaux->setIndexationSurInflation(null);
                $indiceTaux->setEcart(null);
                break;
            case IndiceTaux::TAUX_LIVRET_A_TYPE_SUB_TAUX_REMUNERATION_TRESORERIE_TYPE:
                $indiceTaux->setIndexationSurInflation(null);
                $indiceTaux->setEcart(null);
                break;
            case IndiceTaux::TAUX_LIVRET_A_TYPE_SUB_TAUX_VARIATION_IRL_TYPE:
                $indiceTaux->setIndexationSurInflation(null);
                $indiceTaux->setEcart(null);
                break;
            default:
                $indiceTaux->setIndexationSurInflation(false);
                $indiceTaux->setEcart(null);
                break;
        }
        $this->indiceTauxDao->save($indiceTaux);
        $this->createIndicesTauxPeriodique($indiceTaux);
    }

    /**
     * @throws StringsException
     * @throws ValidatorException
     */
    private function createIndicesTauxPeriodique(IndiceTaux $indiceTaux): void
    {
        $indiceTauxPeriodique = new IndiceTauxPeriodique($indiceTaux, 0);
        $indiceTauxPeriodique->setValeur(null);
        $this->indiceTauxPeriodiqueDao->save($indiceTauxPeriodique);
        for ($i = 1; $i < IndiceTauxPeriodique::NUMBER_OF_ITERATIONS; $i++) {
            $indiceTauxPeriodique = new IndiceTauxPeriodique($indiceTaux, $i);
            $indiceTauxPeriodique->setValeur(null);
            $this->indiceTauxPeriodiqueDao->save($indiceTauxPeriodique);
        }
    }

    public function exportIndicesTaux(Worksheet $sheet, string $simulationId): Worksheet
    {
        $simulation = $this->simulationDao->getById($simulationId);
        $indicesTauxes = $simulation->getIndicesTaux();
        $anneeDeReference = $simulation->getAnneeDeReference();

        $writeData = [];
        $headers = [null, 'Indexation sur inflation', 'Ecart'];

        for ($i = 0; $i < 12; $i++) {
            if ($i === 11) {
                array_push($headers, (intval($anneeDeReference) + $i) . ' à ' . (intval($anneeDeReference) + 50));
            } else {
                array_push($headers, intval($anneeDeReference) + $i);
            }
        }

        array_push($writeData, $headers);

        foreach ($indicesTauxes as $indicesTaux) {
            $row = [];
            $type = $indicesTaux->getType();

            if ($type === "Taux de livret A - Taux d'inflation") {
                for ($i = 0; $i < 15; $i++) {
                    array_push($row, null);
                }

                array_push($writeData, $row);
                $row = [];
            }

            array_push($row, $type);

            $indexactionSurInflation = $indicesTaux->getIndexationSurInflation();
            $surInflationValue = null;

            if ($indexactionSurInflation !== null) {
                if ($indexactionSurInflation === true) {
                    $surInflationValue = 'Oui';
                }
                if ($indexactionSurInflation === false) {
                    $surInflationValue = 'Non';
                }
            }

            array_push($row, $surInflationValue);

            $ecart = $indicesTaux->getEcart();
            array_push($row, $ecart);

            $indicesTauxPeriodiques = $indicesTaux->getIndicesTauxPeriodique();
            $i = 3;

            foreach ($indicesTauxPeriodiques as $key => $value) {
                if ($key > 11) {
                    continue;
                }

                switch ($type) {
                    case 'Taux de livret A - Taux d\'inflation':
                        array_push($row, intval($writeData[4][$i]) - intval($writeData[1][$i]));
                        $i++;
                        break;
                    case 'Taux de livret A - Taux de variation de l\'IRL':
                        array_push($row, intval($writeData[4][$i]) - intval($writeData[3][$i]));
                        $i++;
                        break;
                    case 'Taux de livret A - Taux de rémunération de la trésorerie':
                        array_push($row, intval($writeData[4][$i]) - intval($writeData[5][$i]));
                        $i++;
                        break;
                    default:
                        array_push($row, $value->getValeur());
                        break;
                }
            }

            array_push($writeData, $row);
        }

        $sheet->setTitle('Indices et taux');
        $sheet->setCellValue('A1', 'Indices et taux');
        $sheet->fromArray($writeData, null, 'A2', true);
        $sheet->getColumnDimension('A')->setwidth(50);
        $sheet->getColumnDimension('B')->setwidth(25);
        $sheet->getColumnDimension('O')->setwidth(10);
        $noBoldeColumns = [2, 8];

        for ($i = 1; $i <= 11; $i++) {
            if (in_array($i, $noBoldeColumns)) {
                continue;
            }

            $sheet->getStyle('A' . $i)->getFont()->setBold(true);
        }

        for ($i = 2; $i <= 53; $i++) {
            $column = $this->columnLetter($i);
            $sheet->getStyle($column . '2')->getFont()->setBold(true);
        }

        $sheet->getStyle('A2:O11')->getFont()->setSize(10);
        $sheet->getStyle('A1:O11')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B2:O2')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A3:O7')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A9:O11')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

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

    public function cloneIndiceTaux(Simulation $newSimulation, Simulation $oldSimulation): void
    {
        $objects = $this->indiceTauxDao->findBySimulationId($oldSimulation->getId());
        foreach ($objects as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $this->indiceTauxDao->save($newObject);
            foreach ($object->getIndicesTauxPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setIndiceTaux($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->indiceTauxPeriodiqueDao->save($newPeriodique);
            }
        }
    }
}
