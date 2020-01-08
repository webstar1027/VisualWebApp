<?php

declare(strict_types=1);

namespace App\Services;

use App\Dao\TableauDeBordDao;
use App\Dao\TableauDeBordParametreDao;
use App\Dao\TableauDeBordPeriodiqueDao;
use App\Exceptions\HTTPException;
use App\Model\Factories\TableauBordFactory;
use App\Model\Simulation;
use App\Model\TableauDeBord;
use App\Model\TableauDeBordParametre;
use App\Model\TableauDeBordPeriodique;
use Safe\Exceptions\DatetimeException;
use Safe\Exceptions\JsonException;
use TheCodingMachine\TDBM\TDBMException;
use function date;
use function Safe\strtotime;

class TableauBordService
{
    /** @var TableauDeBordDao */
    private $tableauDeBordDao;
    /** @var TableauDeBordPeriodiqueDao */
    private $periodiqueDao;
    /** @var TableauBordFactory */
    private $factory;
    /** @var TableauDeBordParametreDao */
    private $tableauDeBordParametreDao;

    public function __construct(
        TableauDeBordDao $tableauDeBordDao,
        TableauDeBordPeriodiqueDao $periodiqueDao,
        TableauBordFactory $factory,
        TableauDeBordParametreDao $tableauDeBordParametreDao
    ) {
        $this->tableauDeBordDao = $tableauDeBordDao;
        $this->periodiqueDao = $periodiqueDao;
        $this->factory = $factory;
        $this->tableauDeBordParametreDao = $tableauDeBordParametreDao;
    }

    public function save(TableauDeBord $tableauDeBord): void
    {
        $this->tableauDeBordDao->save($tableauDeBord);
    }

    /**
     * @throws TDBMException
     */
    public function remove(string $tableauDeBordUUID): void
    {
        $psla = $this->tableauDeBordDao->getById($tableauDeBordUUID);
        $this->tableauDeBordDao->delete($psla, true);
    }

    public function findBySimulation(string $simulationId): ?TableauDeBord
    {
        return $this->tableauDeBordDao->findBySimulationID($simulationId);
    }

    /**
     * @throws TDBMException
     * @throws JsonException
     * @throws DatetimeException
     * @throws HTTPException
     */
    public function createDefaultTableauDeBord(Simulation $newSimulation): void
    {
            $date = strtotime($newSimulation->getAnneeDeReference());
            $tableauDeBord = $this->factory->createTableauDeBord(
                null,
                $newSimulation->getId(),
                (int) $newSimulation->getAnneeDeReference(),
                (int) date('Y', strtotime('+12 years', $date)),
                '',
                null,
                null
            );
            $this->save($tableauDeBord);
            $this->createDefaultPeriodique($tableauDeBord, $newSimulation);
    }

    /**
     * @throws DatetimeException
     */
    private function createDefaultPeriodique(TableauDeBord $tableauDeBord, Simulation $simulation): void
    {
        foreach (TableauDeBord::COMPOSANT_LIST as $composant => $info) {
            $param = $this->tableauDeBordParametreDao->findByComposantAndTableau($composant, $tableauDeBord);
            if ($param === null) {
                $param = new TableauDeBordParametre($tableauDeBord, $composant, $info['type'], $info['position']);
            }

            if ($info['iteration'] === 'years') {
                for ($i = 1; $i <= TableauDeBordPeriodique::NUMBER_OF_ITERATIONS; $i++) {
                    $year = $simulation->getAnneeDeReference();

                    if ($i > 1) {
                        $year = date('Y', strtotime('+' . $i . ' years', strtotime($simulation->getAnneeDeReference())));
                    }

                    $tableauDeBordPeriodique = new TableauDeBordPeriodique($tableauDeBord, $param, $year);

                    $value = 0.0;
                    if (isset($info['data'][$i])) {
                        $value = $info['data'][$i];
                    }
                    $tableauDeBordPeriodique->setValue($value);
                    $this->periodiqueDao->save($tableauDeBordPeriodique);
                }
            } else {
                $count = 0;
                foreach ($info['data'] as $data) {
                    $tableauDeBordPeriodique = new TableauDeBordPeriodique(
                        $tableauDeBord,
                        $param,
                        $info['iteration'][$count]
                    );
                    $tableauDeBordPeriodique->setValue($data);
                    $this->periodiqueDao->save($tableauDeBordPeriodique);
                    $count++;
                }
            }
        }
    }
}
