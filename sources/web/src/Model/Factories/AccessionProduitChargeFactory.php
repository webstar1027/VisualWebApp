<?php

declare(strict_types=1);

namespace App\Model\Factories;

use App\Dao\AccessionCodificationDao;
use App\Dao\AccessionProduitChargeDao;
use App\Dao\AccessionProduitChargePeriodiqueDao;
use App\Dao\SimulationDao;
use App\Exceptions\HTTPException;
use App\Model\AccessionProduitCharge;
use App\Model\AccessionProduitChargePeriodique;
use Safe\Exceptions\JsonException;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\TDBM\TDBMException;
use Throwable;
use function floatval;
use function in_array;
use function Safe\json_decode;

class AccessionProduitChargeFactory
{
    /** @var SimulationDao */
    private $simulationDao;
    /** @var AccessionProduitChargePeriodiqueDao */
    private $periodiqueDao;
    /** @var AccessionProduitChargeDao */
    private $produitChargeDao;
    /** @var AccessionCodificationDao */
    private $codificationDao;

    public function __construct(
        SimulationDao $simulationDao,
        AccessionProduitChargePeriodiqueDao $periodiqueDao,
        AccessionProduitChargeDao $produitChargeDao,
        AccessionCodificationDao $codificationDao
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
    ): AccessionProduitCharge {
        $simulation = $this->simulationDao->getById($simulationId);
        $codification = $this->codificationDao->getById($codificationId);

        if (empty($simulation)) {
            throw HTTPException::notFound("La simulation n'existe pas");
        }

        if (! in_array($type, AccessionProduitCharge::TYPE_LIST)) {
            throw HTTPException::badRequest('Type invalide');
        }

        if ($id === null) {
            if ($this->produitChargeDao->findOneByLibelleAndSimulation($libelle, $simulation) !== null) {
                throw HTTPException::badRequest('Libellé de produit / charge déjà existant');
            }
            $produitCharge = new AccessionProduitCharge($codification, $simulation, $libelle, $type);
        } else {
            try {
                $produitCharge = $this->produitChargeDao->getById($id);
                if (empty($produitCharge)) {
                    throw HTTPException::notFound("Ce produit charge n'existe pas");
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
    private function createProduitsChargesPeriodique(string $periodique, AccessionProduitCharge $produitCharge, ?string $edit): void
    {
        $periodique = json_decode($periodique);

        foreach ($periodique->periodique as $key => $value) {
            $iteration = $key + 1;

            if ($edit !== null) {
                /** @var AccessionProduitChargePeriodique $produitChargePeriodique */
                $produitChargePeriodique = $produitCharge->getAccessionProduitsChargesPeriodique()->offsetGet($key);
            } else {
                $produitChargePeriodique = new AccessionProduitChargePeriodique($produitCharge, $iteration);
            }

            $produitChargePeriodique->setValue($value ? floatval($value) : 0);
            $this->periodiqueDao->save($produitChargePeriodique);
        }
    }
}
