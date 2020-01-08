<?php

declare(strict_types=1);

namespace App\Model\Factories;

use App\Dao\LotissementDao;
use App\Dao\LotissementPeriodiqueDao;
use App\Dao\SimulationDao;
use App\Exceptions\HTTPException;
use App\Model\Lotissement;
use App\Model\LotissementPeriodique;
use App\Model\Simulation;
use Safe\Exceptions\JsonException;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\TDBM\TDBMException;
use Throwable;
use function floatval;
use function in_array;
use function Safe\json_decode;

class LotissementFactory
{
    /** @var SimulationDao */
    private $simulationDao;
    /** @var LotissementDao */
    private $lotissementDao;
    /** @var LotissementPeriodiqueDao */
    private $periodiqueDao;

    public function __construct(
        SimulationDao $simulationDao,
        LotissementDao $lotissementDao,
        LotissementPeriodiqueDao $periodiqueDao
    ) {
        $this->simulationDao = $simulationDao;
        $this->lotissementDao = $lotissementDao;
        $this->periodiqueDao = $periodiqueDao;
    }

    /**
     * @throws HTTPException
     * @throws JsonException
     * @throws TDBMException
     *
     * @Factory()
     */
    public function createLotissement(
        ?string $uuid,
        string $simulationId,
        int $numero,
        string $nom,
        float $prixVente,
        ?int $nombreLots,
        ?float $prixVenteLot,
        ?float $tauxBrute,
        ?float $tauxEvolution,
        ?int $dureeConstruction,
        int $type,
        ?string $periodique
    ): Lotissement {
        $simulation = $this->simulationDao->getById($simulationId);

        $this->validateRequest($simulation, $type);

        if (empty($uuid)) {
            if ($this->lotissementDao->findOneByNumeroAndSimulationAndType($numero, $simulation, $type) !== null) {
                throw HTTPException::badRequest('Le numéro renseigné existe déjà');
            }
            $lotissement = new Lotissement($simulation, $numero, $nom, $prixVente, $type);
        } else {
            try {
                $lotissement = $this->lotissementDao->getById($uuid);
                if (empty($lotissement)) {
                    throw HTTPException::notFound("Ce lotissement n'existe pas");
                }
                $lotissement->setNom($nom);
                $lotissement->setNumero($numero);
                $lotissement->setType($type);
                $lotissement->setPrixVente($prixVente);
            } catch (Throwable $e) {
                throw HTTPException::notFound("Ce lotissement n'existe pas", $e);
            }
        }

        $lotissement->setNombreLots($nombreLots);
        $lotissement->setPrixVenteLot($prixVenteLot);
        $lotissement->setTauxBrute($tauxBrute);
        $lotissement->setTauxEvolution($tauxEvolution);
        $lotissement->setDureeConstruction($dureeConstruction);

        if ($periodique !== null) {
            $periodique = json_decode($periodique);

            if (isset($periodique->portage_propres)
                && isset($periodique->couts_internes)
                && isset($periodique->nombre_livres)
            ) {
                foreach ($periodique->portage_propres as $key => $value) {
                    $lotissementPeriodique = $this->fetchLotissementPeriodique($lotissement, $key, $uuid);
                    $lotissementPeriodique->setPortagePropres($value ? floatval($value) : null);

                    $lotissementPeriodique->setCoutsInternes(
                        empty($periodique->couts_internes[$key]) ? null : floatval($periodique->couts_internes[$key])
                    );

                    $lotissementPeriodique->setNombreLivres(
                        empty($periodique->nombre_livres[$key]) ? null : floatval($periodique->nombre_livres[$key])
                    );

                    $this->periodiqueDao->save($lotissementPeriodique);
                }
            }
        }

        return $lotissement;
    }

    protected function fetchLotissementPeriodique(Lotissement $lotissement, int $key, ?string $uuid): LotissementPeriodique
    {
        $iteration = $key + 1;
        if ($uuid !== null) {
            $lotissementPeriodique = $lotissement->getLotissementPeriodique()->offsetGet($key);
        } else {
            $lotissementPeriodique = new LotissementPeriodique($lotissement, $iteration);
        }

        return $lotissementPeriodique;
    }

    /**
     * @throws HTTPException
     */
    protected function validateRequest(Simulation $simulation, int $type): void
    {
        if (empty($simulation)) {
            throw HTTPException::notFound("La simulation n'existe pas");
        }

        if (! in_array($type, Lotissement::TYPE_LIST)) {
            throw HTTPException::badRequest('Type invalide');
        }
    }
}
