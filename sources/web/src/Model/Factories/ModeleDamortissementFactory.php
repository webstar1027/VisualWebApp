<?php

declare(strict_types=1);

namespace App\Model\Factories;

use App\Dao\ModeleDamortissementDao;
use App\Dao\SimulationDao;
use App\Exceptions\HTTPException;
use App\Model\ModeleDamortissement;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\TDBM\TDBMException;
use Throwable;

class ModeleDamortissementFactory
{
    /** @var SimulationDao */
    private $simulationDao;

    /** @var ModeleDamortissementDao */
    private $modeleDamortissementDao;

    public function __construct(SimulationDao $simulationDao, ModeleDamortissementDao $modeleDamortissementDao)
    {
        $this->simulationDao = $simulationDao;
        $this->modeleDamortissementDao = $modeleDamortissementDao;
    }

    /**
     * @throws TDBMException
     *
     * @Factory()
     */
    public function createModeleDamortissement(
        ?string $uuid,
        string $numero,
        string $nom,
        float $dureeReprise,
        float $structureVentilation,
        float $menuiserieVentilation,
        float $chauffageVentilation,
        float $etancheiteVentilation,
        float $ravalementVentilation,
        float $electriciteVentilation,
        float $plomberieVentilation,
        float $ascenseursVentilation,
        float $structureAmortissement,
        float $menuiserieAmortissement,
        float $chauffageAmortissement,
        float $etancheiteAmortissement,
        float $ravalementAmortissement,
        float $electriciteAmortissement,
        float $plomberieAmortissement,
        float $ascenseursAmortissement,
        string $simulationId
    ): ModeleDamortissement {
        $simulation = $this->simulationDao->getById($simulationId);

        if (empty($simulation)) {
            throw HTTPException::notFound("La simulation n'existe pas");
        }

        if ($uuid === null) {
            if ($this->modeleDamortissementDao->findOneByNumeroAndSimulation($numero, $simulation) !== null) {
                throw HTTPException::badRequest('Le numéro renseigné existe déjà');
            }
            if ($this->modeleDamortissementDao->findOneByNomAndSimulation($nom, $simulation) !== null) {
                throw HTTPException::badRequest('Le nom renseigné existe déjà');
            }
            $modeleDamortissement = new ModeleDamortissement($simulation, $numero, $nom, $dureeReprise);
        } else {
            try {
                $modeleDamortissement = $this->modeleDamortissementDao->getById($uuid);
                $modeleDamortissement->setNumero($numero);
                $modeleDamortissement->setNom($nom);
                $modeleDamortissement->setDureeReprise($dureeReprise);
            } catch (Throwable $e) {
                throw HTTPException::notFound("Ce Modèle Damortissement n'existe pas", $e);
            }
        }

        $modeleDamortissement->setStructureVentilation($structureVentilation);
        $modeleDamortissement->setMenuiserieVentilation($menuiserieVentilation);
        $modeleDamortissement->setChauffageVentilation($chauffageVentilation);
        $modeleDamortissement->setEtancheiteVentilation($etancheiteVentilation);
        $modeleDamortissement->setRavalementVentilation($ravalementVentilation);
        $modeleDamortissement->setElectriciteVentilation($electriciteVentilation);
        $modeleDamortissement->setPlomberieVentilation($plomberieVentilation);
        $modeleDamortissement->setAscenseursVentilation($ascenseursVentilation);
        $modeleDamortissement->setStructureAmortissement($structureAmortissement);
        $modeleDamortissement->setMenuiserieAmortissement($menuiserieAmortissement);
        $modeleDamortissement->setChauffageAmortissement($chauffageAmortissement);
        $modeleDamortissement->setEtancheiteAmortissement($etancheiteAmortissement);
        $modeleDamortissement->setRavalementAmortissement($ravalementAmortissement);
        $modeleDamortissement->setElectriciteAmortissement($electriciteAmortissement);
        $modeleDamortissement->setPlomberieAmortissement($plomberieAmortissement);
        $modeleDamortissement->setAscenseursAmortissement($ascenseursAmortissement);

        return $modeleDamortissement;
    }
}
