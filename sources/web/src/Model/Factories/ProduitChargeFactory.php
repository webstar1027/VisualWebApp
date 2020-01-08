<?php

declare(strict_types=1);

namespace App\Model\Factories;

use App\Dao\CodificationDao;
use App\Dao\ProduitChargeDao;
use App\Dao\ProduitChargePeriodiqueDao;
use App\Dao\SimulationDao;
use App\Exceptions\HTTPException;
use App\Model\ProduitCharge;
use App\Model\ProduitChargePeriodique;
use Safe\Exceptions\JsonException;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\TDBM\TDBMException;
use Throwable;
use function floatval;
use function in_array;
use function Safe\json_decode;

class ProduitChargeFactory
{
    /** @var SimulationDao */
    private $simulationDao;
    /** @var ProduitChargePeriodiqueDao */
    private $periodiqueDao;
    /** @var ProduitChargeDao */
    private $produitChargeDao;
    /** @var CodificationDao */
    private $codificationDao;

    public function __construct(
        SimulationDao $simulationDao,
        ProduitChargePeriodiqueDao $periodiqueDao,
        ProduitChargeDao $produitChargeDao,
        CodificationDao $codificationDao
    ) {
        $this->simulationDao = $simulationDao;
        $this->periodiqueDao = $periodiqueDao;
        $this->produitChargeDao = $produitChargeDao;
        $this->codificationDao = $codificationDao;
    }

    /**
     * @throws JsonException
     * @throws TDBMException
     *
     * @Factory()
     */
    public function createProduitCharge(
        ?string $id,
        string $simulationId,
        string $codificationId,
        string $libelle,
        ?float $taux_devolution,
        bool $indexation,
        int $type,
        ?string $periodique
    ): ProduitCharge {
        $simulation = $this->simulationDao->getById($simulationId);
        $codification = $this->codificationDao->getById($codificationId);

        if (empty($simulation)) {
            throw HTTPException::notFound('La simulation n\'existe pas');
        }

        if (! in_array($type, ProduitCharge::TYPE_LIST)) {
            throw HTTPException::badRequest('Type invalide');
        }

        if ($id === null) {
            if ($this->produitChargeDao->findOneBySimulationIDAndLibelle($simulationId, $libelle) !== null) {
                throw HTTPException::badRequest('Libellé de produit / charge déjà existant');
            }
            $produitCharge = new ProduitCharge($codification, $simulation, $libelle, $type);
        } else {
            try {
                $produitCharge = $this->produitChargeDao->getById($id);
                if (empty($produitCharge)) {
                    throw HTTPException::notFound('Ce produit charge n\'existe pas');
                }
            } catch (Throwable $e) {
                throw HTTPException::notFound("Ce produit charge n'existe pas", $e);
            }
        }

        $produitCharge->setTauxDevolution($taux_devolution);
        $produitCharge->setIndexation($indexation);

        if ($periodique !== null) {
            $this->createProduitsChargesPeriodique($periodique, $produitCharge, $id);
        }

        return $produitCharge;
    }

    /**
     * @throws JsonException
     */
    private function createProduitsChargesPeriodique(string $periodique, ProduitCharge $produitCharge, ?string $edit): void
    {
        $periodique = json_decode($periodique);

        foreach ($periodique->periodique as $key => $value) {
            $iteration = $key + 1;

            if ($edit !== null) {
                /** @var ProduitChargePeriodique $produitChargePeriodique */
                $produitChargePeriodique = $produitCharge->getProduitsChargesPeriodique()->offsetGet($key);
            } else {
                $produitChargePeriodique = new ProduitChargePeriodique($produitCharge, $iteration);
            }

            $produitChargePeriodique->setValue($value ? floatval($value) : null);
            $this->periodiqueDao->save($produitChargePeriodique);
        }
    }
}
