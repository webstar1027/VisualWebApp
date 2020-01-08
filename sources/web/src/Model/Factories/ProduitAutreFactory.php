<?php

declare(strict_types=1);

namespace App\Model\Factories;

use App\Dao\ProduitAutreDao;
use App\Dao\ProduitAutrePeriodiqueDao;
use App\Dao\SimulationDao;
use App\Model\ProduitAutre;
use App\Model\ProduitAutrePeriodique;
use Safe\Exceptions\JsonException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\TDBM\TDBMException;
use Throwable;
use function floatval;
use function in_array;
use function Safe\json_decode;

class ProduitAutreFactory
{
    /** @var SimulationDao */
    private $simulationDao;

    /** @var ProduitAutreDao */
    private $produitAutreDao;

    /** @var ProduitAutrePeriodiqueDao */
    private $periodiqueDao;

    public function __construct(SimulationDao $simulationDao, ProduitAutreDao $produitAutreDao, ProduitAutrePeriodiqueDao $periodiqueDao)
    {
        $this->simulationDao = $simulationDao;
        $this->produitAutreDao = $produitAutreDao;
        $this->periodiqueDao = $periodiqueDao;
    }

    /**
     * @throws JsonException
     * @throws TDBMException
     *
     * @Factory()
     */
    public function constructProduitAutre(
        ?string $uuid,
        string $simulationId,
        ?string $nom,
        ?int $montants,
        ?int $nature,
        int $type,
        ?float $tauxEvolution,
        ?int $calculAutomatique,
        ?string $periodique
    ): ProduitAutre {
        $simulation = $this->simulationDao->getById($simulationId);

        if (empty($simulation)) {
            // FIXME: USE ANOTHER KIND OF EXCEPTION FFS
            throw new HttpException(Response::HTTP_NOT_FOUND, "La simulation n'existe pas");
        }

        if (! in_array($type, ProduitAutre::TYPE_LIST)) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Type invalide');
        }

        if ($nature !== null && ! in_array($nature, ProduitAutre::NATURE_LIST)) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Nature invalide');
        }

        if ($uuid === null) {
            $produitAutre = new ProduitAutre($simulation, $type);
        } else {
            try {
                $produitAutre = $this->produitAutreDao->getById($uuid);
            } catch (Throwable $e) {
                throw new HttpException(Response::HTTP_NOT_FOUND, "Ce Produit Autre n'existe pas", $e);
            }
        }
        $produitAutre->setTauxEvolution($tauxEvolution);
        $produitAutre->setNom($nom);
        $produitAutre->setMontants($montants);
        $produitAutre->setNature($nature);
        $produitAutre->setCalculAutomatique($calculAutomatique);

        if ($periodique !== null) {
            $this->createProduitAutrePeriodique($periodique, $produitAutre, $uuid);
        }

        return $produitAutre;
    }

    /**
     * @throws JsonException
     */
    private function createProduitAutrePeriodique(string $periodique, ProduitAutre $produitAutre, ?string $edit): void
    {
        $periodique = json_decode($periodique);

        foreach ($periodique->periodique as $key => $value) {
            $iteration = $key + 1;

            if ($edit !== null) {
                /** @var ProduitAutrePeriodique $produitAutrePeriodique */
                $produitAutrePeriodique = $produitAutre->getProduitsAutresPeriodique()->offsetGet($key);
            } else {
                $produitAutrePeriodique = new ProduitAutrePeriodique($produitAutre, $iteration);
            }

            $produitAutrePeriodique->setValue($value ? floatval($value) : null);
            $this->periodiqueDao->save($produitAutrePeriodique);
        }
    }
}
