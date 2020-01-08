<?php

declare(strict_types=1);

namespace App\Model\Factories;

use App\Dao\PatrimoineFoyerDao;
use App\Dao\PatrimoineFoyerPeriodiqueDao;
use App\Dao\SimulationDao;
use App\Exceptions\HTTPException;
use App\Model\PatrimoineFoyer;
use App\Model\PatrimoineFoyerPeriodique;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException as SymfonyHttpException;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\TDBM\TDBMException;
use Throwable;
use function Safe\json_decode;

class PatrimoineFoyerFactory
{
    /** @var PatrimoineFoyerDao */
    private $patrimoineFoyerDao;
    /** @var SimulationDao */
    private $simulationDao;
    /** @var PatrimoineFoyerPeriodiqueDao */
    private $patrimoineFoyerPeriodiqueDao;

    public function __construct(
        PatrimoineFoyerDao $patrimoineFoyerDao,
        SimulationDao $simulationDao,
        PatrimoineFoyerPeriodiqueDao $patrimoineFoyerPeriodiqueDao
    ) {
        $this->patrimoineFoyerDao = $patrimoineFoyerDao;
        $this->simulationDao = $simulationDao;
        $this->patrimoineFoyerPeriodiqueDao = $patrimoineFoyerPeriodiqueDao;
    }

    /**
     * @throws HTTPException
     * @throws TDBMException
     *
     * @Factory()
     */
    public function createPatrimoineFoyer(
        ?string $uuid,
        string $simulationId,
        int $nGroupe,
        int $nSousGroupe,
        string $nomGroupe,
        ?string $informations,
        float $nombreLogements,
        ?string $secteurFinancier,
        ?string $natureOperation,
        float $tauxEvolutionRedevances,
        ?string $periodique
    ): PatrimoineFoyer {
        $simulation = $this->simulationDao->getById($simulationId);

        if (empty($simulation)) {
            throw HTTPException::notFound("La simulation n'existe pas");
        }
        if (empty($nGroupe)) {
            throw HTTPException::badRequest('Le N° de groupe doit être renseigné');
        }
        if (empty($nomGroupe)) {
            throw HTTPException::badRequest('Le nom du groupe doit être renseigné');
        }
        if (empty($nombreLogements)) {
            throw HTTPException::badRequest('Le nombre de logements du groupe doit être renseigné');
        }

        if ($uuid !== null) {
            // Updating the existing one
            try {
                $patrimoine = $this->patrimoineFoyerDao->getById($uuid);
                $patrimoine->setSimulation($simulation);
                $patrimoine->setNGroupe($nGroupe);
                $patrimoine->setNSousGroupe($nSousGroupe);
                $patrimoine->setNomGroupe($nomGroupe);
                $patrimoine->setNombreLogements($nombreLogements);
                $patrimoine->setNatureOperation($natureOperation);
                $patrimoine->setSecteurFinancier($secteurFinancier);
                $patrimoine->setTauxEvolutionRedevances($tauxEvolutionRedevances);
                if (empty($patrimoine)) {
                    throw HTTPException::notFound("Ce Patrimoine n'existe pas");
                }
            } catch (Throwable $e) {
                throw new SymfonyHttpException(Response::HTTP_NOT_FOUND, "Ce Patrimoine n'existe pas", $e);
            }
        } else {
            // Creating a new object
            $patrimoineObj = $this->patrimoineFoyerDao->findOneBySimulationAndNGroupeAndNSousGroupe($simulation, $nGroupe, $nSousGroupe);

            if ($patrimoineObj !== null) {
                throw HTTPException::badRequest('Ce numéro de groupe/sous groupe est déjà utilisé');
            }

            $patrimoine = new PatrimoineFoyer(
                $simulation,
                $nGroupe,
                $nSousGroupe,
                $nomGroupe,
                $nombreLogements
            );
        }

        $patrimoine->setInformations($informations);
        $patrimoine->setSecteurFinancier($secteurFinancier);
        $patrimoine->setNatureOperation($natureOperation);
        $patrimoine->setTauxEvolutionRedevances($tauxEvolutionRedevances);

        if ($periodique !== null) {
            $this->handlePatrimoineFoyerPeriodique($periodique, $patrimoine, $uuid);
        }

        return $patrimoine;
    }

    private function handlePatrimoineFoyerPeriodique(string $periodique, PatrimoineFoyer $patrimoineFoyer, ?string $edit): void
    {
        $periodique = json_decode($periodique);

        foreach ($periodique->periodique as $index => $value) {
            $iteration = $index + 1;

            if ($edit !== null) {
                /** @var PatrimoineFoyerPeriodique $patrimoineFoyerPeriodique */
                $patrimoineFoyerPeriodique = $patrimoineFoyer->getPatrimoineFoyersPeriodique()->offsetGet($index);
            } else {
                $patrimoineFoyerPeriodique = new PatrimoineFoyerPeriodique($patrimoineFoyer, $iteration);
            }

            $patrimoineFoyerPeriodique->setValue($value ? (float) $value : null);
            $this->patrimoineFoyerPeriodiqueDao->save($patrimoineFoyerPeriodique);
        }
    }
}
