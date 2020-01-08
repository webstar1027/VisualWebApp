<?php

declare(strict_types=1);

namespace App\Model\Factories;

use App\Dao\PatrimoineDao;
use App\Dao\ProfilEvolutionLoyerDao;
use App\Dao\SimulationDao;
use App\Exceptions\HTTPException;
use App\Model\Patrimoine;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\TDBM\TDBMException;
use Throwable;

class PatrimoineFactory
{
    /** @var PatrimoineDao */
    private $patrimoineDao;

    /** @var SimulationDao */
    private $simulationDao;

    /** @var ProfilEvolutionLoyerDao */
    private $profilEvolutionLoyerDao;

    public function __construct(
        PatrimoineDao $patrimoineDao,
        SimulationDao $simulationDao,
        ProfilEvolutionLoyerDao $profilEvolutionLoyerDao
    ) {
        $this->patrimoineDao = $patrimoineDao;
        $this->simulationDao = $simulationDao;
        $this->profilEvolutionLoyerDao = $profilEvolutionLoyerDao;
    }

    /**
     * @throws HTTPException
     * @throws TDBMException
     *
     * @Factory()
     */
    public function createPatrimoine(
        ?string $uuid,
        string $simulationId,
        int $nGroupe,
        int $nSousGroupe,
        string $nomGroupe,
        ?string $informations,
        bool $conventionne,
        ?float $surfaceQuittancee,
        float $nombreLogements,
        float $loyerMensuel,
        ?float $loyerMensuelPlafond,
        ?string $secteurFinancier,
        ?string $zoneGeographique,
        ?string $natureOperation,
        ?string $typeHabitat,
        bool $rehabilite,
        ?float $anneeMes,
        ?string $profilsEvolutionLoyersId
    ): Patrimoine {
        $simulation = $this->simulationDao->getById($simulationId);

        if (empty($simulation)) {
            throw HTTPException::notFound("La simulation n'existe pas");
        }

        if ($surfaceQuittancee === null) {
            throw HTTPException::badRequest('La surface quittancée du patrimoine doit être renseigné');
        }

        if ($uuid !== null) {
            try {
                $patrimoine = $this->patrimoineDao->getById($uuid);
                $patrimoine->setSimulation($simulation);
                $patrimoine->setNGroupe($nGroupe);
                $patrimoine->setNSousGroupe($nSousGroupe);
                $patrimoine->setNomGroupe($nomGroupe);
                $patrimoine->setNombreLogements($nombreLogements);
                $patrimoine->setLoyerMensuel($loyerMensuel);
                if (empty($patrimoine)) {
                    throw HTTPException::notFound("Ce Patrimoine n'existe pas");
                }
            } catch (Throwable $e) {
                throw HTTPException::notFound("Ce Patrimoine n'existe pas", $e);
            }
        } else {
            $patrimoineObj = $this->patrimoineDao->findOneBySimulationAndNGroupeAndNSousGroupe($simulation, $nGroupe, $nSousGroupe);

            if ($patrimoineObj !== null) {
                throw HTTPException::badRequest('Ce numéro de groupe/sous groupe est déjà utilisé');
            }

            $patrimoine = new Patrimoine(
                $simulation,
                $nGroupe,
                $nSousGroupe,
                $nomGroupe,
                $nombreLogements,
                $loyerMensuel
            );
        }

        $patrimoine->setConventionne($conventionne);
        $patrimoine->setInformations($informations);
        $patrimoine->setSurfaceQuittancee($surfaceQuittancee);
        $patrimoine->setLoyerMensuelPlafond($loyerMensuelPlafond);
        $patrimoine->setSecteurFinancier($secteurFinancier);
        $patrimoine->setZoneGeographique($zoneGeographique);
        $patrimoine->setNatureOperation($natureOperation);
        $patrimoine->setTypeHabitat($typeHabitat);
        $patrimoine->setRehabilite($rehabilite);
        $patrimoine->setAnneeMes($anneeMes);
        if ($profilsEvolutionLoyersId) {
            $profilsEvolutionLoyers = $this->profilEvolutionLoyerDao->getById($profilsEvolutionLoyersId);

            if (empty($profilsEvolutionLoyers)) {
                throw HTTPException::notFound("La Profil Evolution Loyer n'existe pas");
            }
            $patrimoine->setProfilsEvolutionLoyers($profilsEvolutionLoyers);
        }

        return $patrimoine;
    }
}
