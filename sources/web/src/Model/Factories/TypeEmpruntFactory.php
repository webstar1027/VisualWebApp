<?php

declare(strict_types=1);

namespace App\Model\Factories;

use App\Dao\SimulationDao;
use App\Dao\TypeEmpruntDao;
use App\Dao\TypeEmpruntPeriodiqueDao;
use App\Exceptions\HTTPException;
use App\Model\TypeEmprunt;
use App\Model\TypeEmpruntPeriodique;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use Throwable;

class TypeEmpruntFactory
{
    /** @var SimulationDao */
    private $simulationDao;

    /** @var TypeEmpruntPeriodiqueDao */
    private $periodiqueDao;

    /** @var TypeEmpruntDao */
    private $typeEmpruntDao;

    public function __construct(SimulationDao $simulationDao, TypeEmpruntPeriodiqueDao $periodiqueDao, TypeEmpruntDao $typeEmpruntDao)
    {
        $this->simulationDao = $simulationDao;
        $this->periodiqueDao = $periodiqueDao;
        $this->typeEmpruntDao = $typeEmpruntDao;
    }

    /**
     * @Factory()
     */
    public function createTypeEmprunt(
        ?string $uuid,
        string $numero,
        string $simulationId,
        string $nom,
        ?float $tauxInteret,
        ?int $dureeEmprunt,
        ?int $dureeAmortissement,
        ?int $revisability,
        ?bool $livretA,
        ?bool $tauxPlancherEnable,
        ?float $margeEmprunt,
        ?float $tauxPlancher,
        ?float $tauxProgressivite,
        ?bool $edit
    ): TypeEmprunt {
        $simulation = $this->simulationDao->getById($simulationId);

        if (empty($simulation)) {
            throw HTTPException::badRequest("La simulation n'existe pas");
        }

        $revisability = $revisability ??TypeEmprunt::SIMPLE;
        if ($uuid === null) {
            if ($this->typeEmpruntDao->findOneByNumeroAndSimulation($numero, $simulation) !== null) {
                throw HTTPException::badRequest('Le numéro renseigné existe déjà');
            }
            $typeEmprunt = new TypeEmprunt($numero, $nom, $revisability);
        } else {
            try {
                $typeEmprunt = $this->typeEmpruntDao->getById($uuid);
                $typeEmprunt->setNumero($numero);
                $typeEmprunt->setNom($nom);
                $typeEmprunt->setRevisability($revisability);
            } catch (Throwable $e) {
                throw HTTPException::badRequest("Ce type d'emprunt n'existe pas", $e);
            }
        }

        $typeEmprunt->setTauxInteret($tauxInteret ?? 0.00);
        $typeEmprunt->setDureeEmprunt($dureeEmprunt ?? 0);
        $typeEmprunt->setDureeAmortissement($dureeAmortissement ?? 0);
        $typeEmprunt->setLivretA($livretA ?? false);
        $typeEmprunt->setTauxPlancherCheck($tauxPlancherEnable ?? false);
        $typeEmprunt->setMargeEmprunt($margeEmprunt ?? 0.00);
        $typeEmprunt->setTauxPlancher($tauxPlancher ?? 0.00);
        $typeEmprunt->setTauxProgressivite($tauxProgressivite ?? 0.00);
        $typeEmprunt->setSimulation($simulation);
        if ($edit !== true && empty($typeEmprunt->getTypesEmpruntsPeriodique()->count())) {
            $this->createTypeEmpruntPeriodique($typeEmprunt);
        }

        return $typeEmprunt;
    }

    private function createTypeEmpruntPeriodique(TypeEmprunt $typeEmprunt): void
    {
        for ($i = 1; $i < TypeEmpruntPeriodique::NUMBER_OF_ITERATIONS; $i++) {
            $typeEmpruntPeriodique = new TypeEmpruntPeriodique($typeEmprunt, $i);
            $typeEmpruntPeriodique->setTauxInteretInitial(0.0);
            $typeEmpruntPeriodique->setTauxPremiereAnnuitePayee(0.0);
            $this->periodiqueDao->save($typeEmpruntPeriodique);
        }
    }
}
