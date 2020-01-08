<?php

declare(strict_types=1);

namespace App\Model\Factories;

use App\Dao\ProfilEvolutionLoyerDao;
use App\Dao\ProfilEvolutionLoyerPeriodiqueDao;
use App\Dao\SimulationDao;
use App\Exceptions\HTTPException;
use App\Model\ProfilEvolutionLoyer;
use App\Model\ProfilEvolutionLoyerPeriodique;
use Safe\Exceptions\JsonException;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\TDBM\TDBMException;
use Throwable;
use function floatval;
use function Safe\json_decode;

class ProfilEvolutionLoyerFactory
{
    /** @var ProfilEvolutionLoyerPeriodiqueDao */
    private $periodiqueDao;

    /** @var SimulationDao */
    private $simulationDao;


    /** @var ProfilEvolutionLoyerDao */
    private $profilEvolutionLoyerDao;

    public function __construct(
        ProfilEvolutionLoyerPeriodiqueDao $periodiqueDao,
        SimulationDao $simulationDao,
        ProfilEvolutionLoyerDao $profilEvolutionLoyerDao
    ) {
        $this->periodiqueDao = $periodiqueDao;
        $this->simulationDao = $simulationDao;
        $this->profilEvolutionLoyerDao = $profilEvolutionLoyerDao;
    }

    /**
     * @throws JsonException
     * @throws TDBMException
     * @throws HTTPException
     *
     * @Factory()
     */
    public function createProfilEvolutionLoyer(
        string $numero,
        string $simulationId,
        string $nom,
        ?bool $appliquerIrl,
        string $periodique,
        ?bool $edit
    ): ProfilEvolutionLoyer {
        $simulation = $this->simulationDao->getById($simulationId);

        if (empty($simulation)) {
            throw HTTPException::notFound("La simulation n'existe pas");
        }

        if ($edit !== true) {
            $profil = new ProfilEvolutionLoyer($simulation, $numero, $nom);
        } else {
            try {
                $profil = $this->profilEvolutionLoyerDao->findOneByNumeroAndSimulation($numero, $simulation);
                if ($profil === null) {
                    throw HTTPException::notFound("La simulation n'existe pas");
                }
            } catch (Throwable $e) {
                throw HTTPException::notFound("Ce Profil n'existe pas", $e);
            }
        }

        $profil->setNom($nom);
        $profil->setAppliquerIrl($appliquerIrl ??false);
        $this->profilEvolutionLoyerDao->save($profil);

        $this->createProfilEvolutionLoyerPeriodique($profil, $periodique);

        return $profil;
    }

    /**
     * @throws JsonException
     * @throws HTTPException
     */
    private function createProfilEvolutionLoyerPeriodique(ProfilEvolutionLoyer $profilEvolutionLoyer, string $periodique): void
    {
        $periodique = json_decode($periodique);

        foreach ($periodique->s1 as $key => $value) {
            $iteration = $key+1;
            $profilEvolutionLoyerPeriodique = $this->periodiqueDao->findOneByIDAndIteration($profilEvolutionLoyer->getId(), $iteration);
            if (empty($profilEvolutionLoyerPeriodique)) {
                $profilEvolutionLoyerPeriodique = new ProfilEvolutionLoyerPeriodique($profilEvolutionLoyer, $iteration);
            }

            $profilEvolutionLoyerPeriodique->setS1(empty($value)?null:floatval($value));
            $profilEvolutionLoyerPeriodique->setS2(empty($periodique->s2[$key])?null:floatval($periodique->s2[$key]));
            try {
                $this->periodiqueDao->save($profilEvolutionLoyerPeriodique);
            } catch (Throwable $e) {
                throw HTTPException::forbidden('Ce numéro de profil existe déja', $e);
            }
        }
    }
}
