<?php

declare(strict_types=1);

namespace App\Controller\GraphQL;

use App\Controller\AbstractVisialWebController;
use App\Dao\EntiteDao;
use App\Dao\RoleDao;
use App\Dao\SimulationDao;
use App\Dao\UtilisateurDao;
use App\Dao\UtilisateurRoleEntiteDao;
use App\Exceptions\HTTPException;
use App\Model\Entite;
use App\Model\Role;
use App\Model\Simulation;
use App\Model\UtilisateurRoleEntite;
use Cake\Chronos\Chronos;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\TDBM\ResultIterator;
use TheCodingMachine\TDBM\TDBMException;

final class EntitesController extends AbstractVisialWebController
{
    /** @var EntiteDao */
    private $entiteDao;

    /** @var SimulationDao */
    private $simulationDao;

    /** @var RoleDao */
    private $roleDao;

    /** @var UtilisateurRoleEntiteDao */
    private $utilisateurRoleEntiteDao;

    public function __construct(UtilisateurDao $utilisateurDao, EntiteDao $entiteDao, SimulationDao $simulationDao, UtilisateurRoleEntiteDao $utilisateurRoleEntiteDao, RoleDao $roleDao)
    {
        parent::__construct($utilisateurDao);
        $this->entiteDao = $entiteDao;
        $this->simulationDao = $simulationDao;
        $this->roleDao = $roleDao;
        $this->utilisateurRoleEntiteDao = $utilisateurRoleEntiteDao;
    }

    /**
     * @return Entite[]|ResultIterator
     *
     * @Query()
     * @Logged()
     */
    public function entites(
        ?string $nom,
        ?string $siren,
        ?string $code,
        ?string $type,
        ?string $codeOrganisme,
        ?string $typeOrganisme,
        ?string $creePar,
        ?string $modifiePar,
        ?string $utilisateur,
        ?string $referent,
        ?string $ensemble,
        string $sortColumn,
        string $sortOrder
    ): ResultIterator {
        $estAdministrateurCentral = $this->mustGetUtilisateur()->getEstAdministrateurCentral();

        return $this->entiteDao->findByFilters($nom, $siren, $code, $type, $codeOrganisme, $typeOrganisme, $creePar, $modifiePar, $utilisateur, $referent, $ensemble, $estAdministrateurCentral, $sortColumn, $sortOrder);
    }

    /**
     * @throws TDBMException
     *
     * @Query()
     * @Logged()
     */
    public function entite(string $entiteID): Entite
    {
        return $this->entiteDao->getById($entiteID);
    }

    /**
     * @throws TDBMException
     *
     * @Query()
     * @Logged()
     */
    public function isReferentEntite(string $entiteID): bool
    {
        $authenticatedUtilisateur = $this->mustGetUtilisateur();

        return $this->entiteDao->isReferentEntite($authenticatedUtilisateur, $entiteID);
    }

    /**
     * @return Entite[]|ResultIterator
     *
     * @Query()
     * @Logged()
     */
    public function allEntites()
    {
            return $this->entiteDao->getAll();
    }

    /**
     * @param string[] $referents
     *
     * @throws HTTPException
     * @throws TDBMException
     *
     * @Mutation()
     * @Logged()
     */
    public function saveEntite(?string $entiteID, string $nom, string $siren, string $code, string $type, ?string $codeOrganisme, ?string $typeOrganisme, bool $estActivee, array $referents): Entite
    {
        $authenticatedUtilisateur = $this->mustGetUtilisateur();
        $existingEntiteCode = $this->entiteDao->findOneByCode($code);
        $existingEntiteSiren = $this->entiteDao->findOneBySiren($siren);

        // we are creating an Entite
        if (empty($entiteID)) {
            if (isset($existingEntiteCode)) {
                throw HTTPException::badRequest('Ce Code est déja existant');
            }
            if (isset($existingEntiteSiren)) {
                throw HTTPException::badRequest('Ce SIREN est déja existant');
            }

            $newEntite = new Entite(
                $authenticatedUtilisateur,
                $siren,
                $nom,
                $code,
                $type,
                Chronos::now()
            );
            $newEntite->setEstActivee(true);
            $newEntite->setCodeOrganisme($codeOrganisme);
            $newEntite->setTypeOrganisme($typeOrganisme);
            $newEntite->setCreePar($authenticatedUtilisateur);

            foreach ($referents as $utilisateurId) {
                $utilisateur = $this->utilisateurDao->getById($utilisateurId);
                $newEntite->addUtilisateur($utilisateur);

                /** @var Role $role */
                $role = $this->roleDao->findOneByNom('Référent entité');
                $newUtilisateurRoleEntite = new UtilisateurRoleEntite($utilisateur, $role, $newEntite);
                $this->utilisateurRoleEntiteDao->save($newUtilisateurRoleEntite);
            }

            $this->entiteDao->save($newEntite);

            return $newEntite;
        }

        // we are updating or deleting an Entite.
        if ($estActivee === false && ! $authenticatedUtilisateur->getEstAdministrateurCentral()) {
            throw HTTPException::forbidden("Seul l'administrateur central a le droit de supprimer une entité.");
        }
        $existingEntite = $this->entiteDao->getById($entiteID);
        $existingEntite->setNom($nom);
        $existingEntite->setSiren($siren);
        $existingEntite->setCode($code);
        $existingEntite->setType($type);
        $existingEntite->setCodeOrganisme($codeOrganisme);
        $existingEntite->setTypeOrganisme($typeOrganisme);
        $existingEntite->setModifiePar($authenticatedUtilisateur);
        $existingEntite->setEstActivee($estActivee);
        $existingEntite->setDateModification(Chronos::now());

        /** @var Role $role */
        $role = $this->roleDao->findOneByNom('Référent entité');
        $utilisateurs = $existingEntite->getUtilisateurs();
        foreach ($utilisateurs as $utilisateur) {
            $existingEntite->removeUtilisateur($utilisateur);

            /** @var UtilisateurRoleEntite $oldUtilisateurRoleEntite */
            $oldUtilisateurRoleEntite = $this->utilisateurRoleEntiteDao->findOneByFilter($entiteID, $utilisateur->getId(), $role->getId());
            if (empty($oldUtilisateurRoleEntite)) {
                continue;
            }

            $this->utilisateurRoleEntiteDao->delete($oldUtilisateurRoleEntite);
        }
        $this->entiteDao->save($existingEntite);

        foreach ($referents as $utilisateurId) {
            $utilisateur = $this->utilisateurDao->getById($utilisateurId);
            $existingEntite->addUtilisateur($utilisateur);

            $newUtilisateurRoleEntite = new UtilisateurRoleEntite($utilisateur, $role, $existingEntite);
            $this->utilisateurRoleEntiteDao->save($newUtilisateurRoleEntite);
        }

        $this->entiteDao->save($existingEntite);

        return $existingEntite;
    }

    /**
     * @param string[] $entiteIds
     *
     * @throws HTTPException
     * @throws TDBMException
     *
     * @Mutation()
     * @Logged()
     */
    public function fusionEntites(string $nom, string $siren, string $code, string $type, ?string $codeOrganisme, ?string $typeOrganisme, array $entiteIds): Entite
    {
        $authenticatedUtilisateur = $this->mustGetUtilisateur();
        if (! $authenticatedUtilisateur->getEstAdministrateurCentral()) {
            throw HTTPException::forbidden("Seul l'administrateur central à le droit de fusionner deux entités");
        }
        // we are creating an Entite.
        $newEntite = new Entite(
            $authenticatedUtilisateur,
            $siren,
            $nom,
            $code,
            $type,
            Chronos::now()
        );
        $newEntite->setEstActivee(true);
        $newEntite->setCodeOrganisme($codeOrganisme);
        $newEntite->setTypeOrganisme($typeOrganisme);
        $newEntite->setCreePar($authenticatedUtilisateur);

        $existingEntiteCode = $this->entiteDao->findOneByCode($code);
        $existingEntiteSiren = $this->entiteDao->findOneBySiren($siren);
        $existingEntiteNom = $this->entiteDao->findOneByNom($nom);
        if (isset($existingEntiteCode)) {
            throw HTTPException::badRequest('Ce Code existe déjà');
        }
        if (isset($existingEntiteSiren)) {
            throw HTTPException::badRequest('Ce Siren existe déjà.');
        }
        if (isset($existingEntiteNom)) {
            throw HTTPException::badRequest('Ce nom existe déjà.');
        }

        foreach ($entiteIds as $entiteId) {
            // Disable old entites.
            $entite = $this->entiteDao->getById($entiteId);
            $entite->setEstActivee(false);
            $this->entiteDao->save($entite);

            // Duplicate referents entite.
            $utilisateurs = $entite->getUtilisateurs();
            foreach ($utilisateurs as $utilisateur) {
                $existingUtilisateur = $newEntite->hasUtilisateur($utilisateur);
                if ($existingUtilisateur) {
                    continue;
                }

                $newEntite->addUtilisateur($utilisateur);
            }

            // Duplicate referents ensembles
            $referentsEnsembles = $entite->getEnsemblesByReferentsEnsembles();
            foreach ($referentsEnsembles as $referentsEnsemble) {
                $existingReferentsEnsemble = $newEntite->hasEnsembleByReferentsEnsembles($referentsEnsemble);
                if ($existingReferentsEnsemble) {
                    continue;
                }

                $newEntite->addEnsembleByReferentsEnsembles($referentsEnsemble);
            }

            // Duplicate ensembles_entites.
            $ensemblesEntites = $entite->getEnsemblesByEnsemblesEntites();
            foreach ($ensemblesEntites as $ensemblesEntite) {
                $exstingEnsemblesEntite = $newEntite->hasEnsembleByEnsemblesEntites($ensemblesEntite);
                if ($exstingEnsemblesEntite) {
                    continue;
                }

                $newEntite->addEnsembleByEnsemblesEntites($ensemblesEntite);
            }

            // Duplicate simulations
            $simulations = $entite->getSimulations();
            foreach ($simulations as $simulation) {
                $newSimulation = new Simulation(
                    $simulation->getUtilisateur(),
                    $newEntite,
                    $simulation->getNom(),
                    $simulation->getAnneeDeReference()
                );
                $newSimulation->setModifiePar($simulation->getModifiePar());
                $newSimulation->setDateModification($simulation->getDateModification());
                $newSimulation->setEstVerrouillee($simulation->getEstVerrouillee());
                $newSimulation->setEstPartagee($simulation->getEstPartagee());
                $newSimulation->setEstFusionnee($simulation->getEstFusionnee());
                $this->simulationDao->save($newSimulation);
            }

            // Duplicate utilisateurs_roles_entites
            $utilisateursRolesEntites = $entite->getUtilisateursRolesEntites();
            foreach ($utilisateursRolesEntites as $utilisateursRolesEntite) {
                $utilisateur = $utilisateursRolesEntite->getUtilisateur();
                $role = $utilisateursRolesEntite->getRole();
                $newUtilisateurRoleEntite = new UtilisateurRoleEntite($utilisateur, $role, $newEntite);
                $this->utilisateurRoleEntiteDao->save($newUtilisateurRoleEntite);
            }
        }

        $this->entiteDao->save($newEntite);

        return $newEntite;
    }
}
