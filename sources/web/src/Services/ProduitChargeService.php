<?php

declare(strict_types=1);

namespace App\Services;

use App\Dao\ProduitChargeDao;
use App\Dao\ProduitChargePeriodiqueDao;
use App\Model\Factories\ProduitChargeFactory;
use App\Model\ProduitCharge;
use App\Model\ProduitChargePeriodique;
use App\Model\Simulation;
use Safe\Exceptions\JsonException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use TheCodingMachine\TDBM\ResultIterator;
use TheCodingMachine\TDBM\TDBMException;
use Throwable;

class ProduitChargeService
{
    /** @var ProduitChargeDao */
    private $produitChargeDao;
    /** @var ProduitChargeFactory */
    private $factory;
    /** @var ProduitChargePeriodiqueDao */
    private $periodiqueDao;

    public function __construct(ProduitChargeDao $produitChargeDao, ProduitChargeFactory $factory, ProduitChargePeriodiqueDao $periodiqueDao)
    {
        $this->produitChargeDao = $produitChargeDao;
        $this->factory = $factory;
        $this->periodiqueDao = $periodiqueDao;
    }

    public function save(ProduitCharge $produitCharge): void
    {
        try {
            $this->produitChargeDao->save($produitCharge);
        } catch (Throwable $e) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Ce produit existe déjà.', $e);
        }
    }

    /**
     * @throws TDBMException
     */
    public function remove(string $produitChargeId): void
    {
        $produitCharge = $this->produitChargeDao->getById($produitChargeId);
        $this->produitChargeDao->delete($produitCharge, true);
    }

    /**
     * @return ProduitCharge[]|ResultIterator
     */
    public function findBySimulation(string $simulationId): ResultIterator
    {
        return $this->produitChargeDao->findBySimulationID($simulationId);
    }

    /**
     * @throws TDBMException
     * @throws JsonException
     */
    public function createDefaultProduitsCharges(Simulation $newSimulation): void
    {
        foreach (ProduitCharge::TYPE_LIST as $label => $type) {
            $produitCharge = $this->factory->createProduitCharge(null, $newSimulation->getId(), '', $label, 0.0, true, $type, null);
            $this->save($produitCharge);
            $this->createDefaultPeriodique($produitCharge);
        }
    }

    private function createDefaultPeriodique(ProduitCharge $produitCharge): void
    {
        for ($i = 1; $i <= ProduitChargePeriodique::NUMBER_OF_ITERATIONS; $i++) {
            $produitChargePeriodique = new ProduitChargePeriodique($produitCharge, $i);
            $produitChargePeriodique->setValue(0.0);
            $this->periodiqueDao->save($produitChargePeriodique);
        }
    }

    public function cloneProduitCharge(Simulation $newSimulation, Simulation $oldSimulation): void
    {
        $objects = $this->produitChargeDao->findBySimulationId($oldSimulation->getId());
        foreach ($objects as $object) {
            $newObject = clone $object;
            $newObject->setSimulation($newSimulation);
            $this->save($newObject);
            foreach ($object->getProduitsChargesPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setProduitsCharges($newObject);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
    }

    public function fusionProduitCharge(Simulation $newSimulation, Simulation $oldSimulation1, Simulation $oldSimulation2): void
    {
        $objects1 = $this->produitChargeDao->findBySimulationId($oldSimulation1->getId());
        $objects2 = $this->produitChargeDao->findBySimulationId($oldSimulation2->getId());
        foreach ($objects1 as $object) {
            $codification = $object->getCodification();
            $libelle = $object->getLibelle();
            $type = $object->getType();
            $taux_devolution = $object->getTauxDevolution();
            $indexation = $object->getIndexation();

            $produitCharge = new ProduitCharge($codification, $newSimulation, $libelle, $type);
            $produitCharge->setTauxDevolution($taux_devolution);
            $produitCharge->setIndexation($indexation);

            $this->save($produitCharge);
            foreach ($object->getProduitsChargesPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setProduitsCharges($produitCharge);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
        foreach ($objects2 as $object) {
            $codification = $object->getCodification();
            $libelle = $object->getLibelle();
            $type = $object->getType();
            $taux_devolution = $object->getTauxDevolution();
            $indexation = $object->getIndexation();

            $produitCharge = new ProduitCharge($codification, $newSimulation, $libelle, $type);
            $produitCharge->setTauxDevolution($taux_devolution);
            $produitCharge->setIndexation($indexation);

            $this->save($produitCharge);
            foreach ($object->getProduitsChargesPeriodique() as $periodique) {
                $newPeriodique = clone $periodique;
                $newPeriodique->setProduitsCharges($produitCharge);
                $newPeriodique->setIteration($periodique->getIteration());
                $this->periodiqueDao->save($newPeriodique);
            }
        }
    }
}
