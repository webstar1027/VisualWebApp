<?php

declare(strict_types=1);

namespace App\Services;

use App\Dao\RisqueLocatifDao;
use App\Dao\SimulationDao;
use App\Exceptions\HTTPException;
use App\Model\RisqueLocatif;
use App\Model\Simulation;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Safe\Exceptions\JsonException;
use TheCodingMachine\TDBM\ResultIterator;
use TheCodingMachine\TDBM\TDBMException;
use function array_push;
use function floatval;
use function in_array;
use function intval;
use function Safe\json_decode;

final class RisqueLocatifService
{
    /** @var RisqueLocatifDao */
    private $risqueLocatifDao;
    /** @var SimulationDao */
    private $simulationDao;

    public function __construct(RisqueLocatifDao $risqueLocatifDao, SimulationDao $simulationDao)
    {
        $this->risqueLocatifDao = $risqueLocatifDao;
        $this->simulationDao = $simulationDao;
    }

    /**
     * @throws TDBMException
     */
    public function createRisqueLocatifPeriodique(Simulation $simulation): void
    {
        for ($i = 1; $i <= RisqueLocatif::NUMBER_OF_ITERATIONS; $i++) {
            $risqueLocatif = new RisqueLocatif($simulation);
            $risqueLocatif->setIteration($i);
            $this->risqueLocatifDao->save($risqueLocatif);
        }
    }

    /**
     * @throws HTTPException
     * @throws TDBMException
     */
    public function save(string $simulationId, int $iteration, float $value, int $type): RisqueLocatif
    {
        $simulation = $this->simulationDao->getById($simulationId);
        if (empty($simulation)) {
            throw HTTPException::notFound("La simulation n'existe pas.");
        }

        if (! in_array($type, RisqueLocatif::TYPE_LIST)) {
            throw HTTPException::badRequest('Type invalide');
        }

        $risque = $this->risqueLocatifDao->findOneBySimulationAndIteration($simulation, $iteration);

        if (empty($risque)) {
            $this->createRisqueLocatifPeriodique($simulation);
            $risque = $this->risqueLocatifDao->findOneBySimulationAndIteration($simulation, $iteration);
            if (empty($risque)) {
                throw HTTPException::notFound("Ce risque locatif n'existe pas.");
            }
        }

        $this->setPeriodiqueValue($risque, $type, $value);

        $this->risqueLocatifDao->save($risque);

        return $risque;
    }

    /**
     * @throws HTTPException
     * @throws TDBMException
     * @throws JsonException
     */
    public function resetRisqueLocatif(string $simulationId, string $periodique, int $type): void
    {
        $simulation = $this->simulationDao->getById($simulationId);
        if (empty($simulation)) {
            throw HTTPException::notFound("La simulation n'existe pas.");
        }

        $periodique = json_decode($periodique);

        switch ($type) {
            case RisqueLocatif::ACNE:
                $this->updateRefreshPeriodiqueBulk($simulation, $periodique->acne, $type);
                break;
            case RisqueLocatif::TAUX_VACANCE_PATRIMOINE:
                $this->updateRefreshPeriodiqueBulk($simulation, $periodique->patrimoine, $type);
                break;
            case RisqueLocatif::TAUX_VACANCE_GARAGES:
                $this->updateRefreshPeriodiqueBulk($simulation, $periodique->garages, $type);
                break;
            case RisqueLocatif::TAUX_VACANCE_COMMERCIAUX:
                $this->updateRefreshPeriodiqueBulk($simulation, $periodique->commerciaux, $type);
                break;
            case RisqueLocatif::TAUX_ANNUEL:
                $this->updateRefreshPeriodiqueBulk($simulation, $periodique->annuel, $type);
                break;
        }
    }

    /**
     * @param float[] $info
     */
    private function updateRefreshPeriodiqueBulk(Simulation $simulation, array $info, int $type): void
    {
        foreach ($info as $key => $value) {
            $iteration = $key+1;
            $periodique = $this->risqueLocatifDao->findOneBySimulationAndIteration($simulation, $iteration);
            if ($periodique === null) {
                continue;
            }

            $this->setPeriodiqueValue($periodique, $type, empty($value)?0: floatval($value));
            $this->risqueLocatifDao->save($periodique);
        }
    }

    private function setPeriodiqueValue(RisqueLocatif &$risque, int $type, float $value): void
    {
        switch ($type) {
            case RisqueLocatif::ACNE:
                $risque->setAcne($value);
                break;
            case RisqueLocatif::TAUX_VACANCE_PATRIMOINE:
                $risque->setTauxVacancePatrimoine($value);
                break;
            case RisqueLocatif::TAUX_VACANCE_GARAGES:
                $risque->setTauxVacanceGarages($value);
                break;
            case RisqueLocatif::TAUX_VACANCE_COMMERCIAUX:
                $risque->setTauxVacanceCommerciaux($value);
                break;
            case RisqueLocatif::TAUX_ANNUEL:
                $risque->setTauxAnnuel($value);
                break;
        }
    }

    /**
     * @return RisqueLocatif[]|ResultIterator
     */
    public function getBySimulation(string $simulationId): ResultIterator
    {
        return $this->risqueLocatifDao->findBySimulationID($simulationId);
    }

    public function exportRisquesLocatifs(Worksheet $sheet, string $simulationId): Worksheet
    {
        $simulation = $this->simulationDao->getById($simulationId);
        $anneeDeReference = $simulation->getAnneeDeReference();

        $acneWriteData = [];
        $tauxAnnuelWriteData = [];
        $tauxPertesWriteData = [];

        $headers = [null];
        for ($i = 0; $i < RisqueLocatif::NUMBER_OF_ITERATIONS; $i++) {
            array_push($headers, intval($anneeDeReference) + $i);
        }

        array_push($acneWriteData, $headers);
        array_push($tauxAnnuelWriteData, $headers);
        array_push($tauxPertesWriteData, $headers);

        $risquesLocatifs = $this->getBySimulation($simulationId);
        $row = [
            'ance' => ['Taux annuel'],
            'tauxAnnuel' => ['Taux annuel'],
            'tauxPertres' => [
                0 => ['Taux de vacance sur les logements'],
                1 => ['Taux de vacance sur les garages et les parkings'],
                2 => ['Taux de vacance sur les locaux commerciaux'],
            ],
        ];

        foreach ($risquesLocatifs as $risquesLocatif) {
            $row['ance'][] = $risquesLocatif->getAcne();
            $row['tauxAnnuel'][] = $risquesLocatif->getTauxAnnuel();
            $row['tauxPertres'][0][] = $risquesLocatif->getTauxVacancePatrimoine();
            $row['tauxPertres'][1][] = $risquesLocatif->getTauxVacanceGarages();
            $row['tauxPertres'][2][] = $risquesLocatif->getTauxVacanceCommerciaux();
        }

        array_push($acneWriteData, $row['ance']);
        array_push($tauxAnnuelWriteData, $row['tauxAnnuel']);

        foreach ($row['tauxPertres'] as $item) {
            array_push($tauxPertesWriteData, $item);
        }

        $sheet->setTitle('risques_locatifs');
        $sheet->setCellValue('A1', 'Risques locatifs');
        $sheet->setCellValue('A2', 'Taux des impayés');
        $sheet->setCellValue('A6', 'Taux de pertes de loyers dues à la vacance sur le patrimoine de référence');
        $sheet->setCellValue('A12', 'Taux de charges non récupérées sur la vacance logement');
        $sheet->fromArray($acneWriteData, null, 'A3', true);
        $sheet->fromArray($tauxPertesWriteData, null, 'A7', true);
        $sheet->fromArray($tauxAnnuelWriteData, null, 'A13', true);
        $sheet->getStyle('A1:A14')->getFont()->setBold(true);
        $sheet->getStyle('B3:AY3')->getFont()->setBold(true);
        $sheet->getStyle('B7:AY7')->getFont()->setBold(true);
        $sheet->getStyle('B13:AY13')->getFont()->setBold(true);
        $sheet->getColumnDimension('A')->setWidth(70);
        $sheet->getStyle('A3:AY4')->getFont()->setSize(10);
        $sheet->getStyle('A7:AY10')->getFont()->setSize(10);
        $sheet->getStyle('A13:AY14')->getFont()->setSize(10);
        $sheet->getStyle('A1:AY14')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B3:AY3')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A4:AY4')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('B7:AY7')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A8:AY10')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('B13:AY13')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A14:AY14')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        return $sheet;
    }

    public function cloneRisqueLocatif(Simulation $newSimulation, Simulation $oldSimulation): void
    {
        $objects = $this->risqueLocatifDao->findBySimulationID($oldSimulation->getId());
        foreach ($objects as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $this->risqueLocatifDao->save($newObject);
        }
    }
}
