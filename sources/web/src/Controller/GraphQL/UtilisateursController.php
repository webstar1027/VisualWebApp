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
use App\Model\Utilisateur;
use App\Model\UtilisateurRoleEntite;
use App\Services\UtilisateurService;
use Cake\Chronos\Chronos;
use Safe\Exceptions\PasswordException;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\TDBM\ResultIterator;
use TheCodingMachine\TDBM\TDBMException;

final class UtilisateursController extends AbstractVisialWebController
{
    /** @var RoleDao */
    private $roleDao;

    /** @var EntiteDao */
    private $entiteDao;

    /** @var UtilisateurRoleEntiteDao */
    private $utilisateurRoleEntiteDao;

    /** @var SimulationDao */
    private $simulationDao;
    /** @var UtilisateurService */
    private $utilisateurService;

    public function __construct(
        UtilisateurDao $utilisateurDao,
        RoleDao $roleDao,
        EntiteDao $entiteDao,
        UtilisateurRoleEntiteDao $utilisateurRoleEntiteDao,
        SimulationDao $simulationDao,
        UtilisateurService $utilisateurService
    ) {
        parent::__construct($utilisateurDao);
        $this->roleDao = $roleDao;
        $this->entiteDao = $entiteDao;
        $this->utilisateurRoleEntiteDao = $utilisateurRoleEntiteDao;
        $this->simulationDao = $simulationDao;
        $this->utilisateurService = $utilisateurService;
    }

    /**
     * @return Utilisateur[]|ResultIterator
     *
     * @Query()
     * @Logged()
     */
    public function allUtilisateurs(): ResultIterator
    {
        return $this->utilisateurDao->findAll();
    }

    /**
     * @return Utilisateur[]|ResultIterator
     *
     * @Query()
     * @Logged()
     */
    public function utilisateurs(?string $nom, ?string $prenom, ?string $email, ?string $telephone, ?string $entite, ?string $role, ?string $fonction, string $sortColumn, string $sortOrder): ResultIterator
    {
        $estAdministrateurCentral = $this->mustGetUtilisateur()->getEstAdministrateurCentral();

        return $this->utilisateurDao->findByFilters($nom, $prenom, $email, $telephone, $entite, $role, $fonction, $estAdministrateurCentral, $sortColumn, $sortOrder);
    }

    /**
     * @return Utilisateur[]|ResultIterator
     *
     * @Query()
     * @Logged()
     */
    public function fetchUtilisateurs(?string $nom, ?string $entite, ?string $ensemble, string $simulationID, ?string $currentEntite, string $sortColumn, string $sortOrder): ResultIterator
    {
        $estAdministrateurCentral = $this->mustGetUtilisateur()->getEstAdministrateurCentral();

        if ($estAdministrateurCentral) {
            $currentUtilisateurId = $this->mustGetUtilisateur()->getId();
            $simulation = $this->simulationDao->getById($simulationID);
            $ownerEntiteId = $simulation->getEntite()->getId();

            return $this->utilisateurDao->fetchByFilters($nom, $entite, $ensemble, $simulationID, $currentUtilisateurId, $ownerEntiteId, $sortColumn, $sortOrder);
        }

        return $this->utilisateurDao->fetchByEntite($nom, $entite, $ensemble, $simulationID, $currentEntite, $sortColumn, $sortOrder);
    }

    /**
     * @throws TDBMException
     *
     * @Query()
     * @Logged()
     */
    public function utilisateur(string $utilisateurID): Utilisateur
    {
        return $this->utilisateurDao->getById($utilisateurID);
    }

    /**
     * @param string[] $entiteID
     *
     * @throws PasswordException
     * @throws TDBMException
     * @throws HTTPException
     *
     * @Mutation()
     * @Logged()
     */
    public function saveUtilisateur(?string $utilisateurID, string $nom, string $prenom, string $email, ?string $motDePasse, ?string $telephone, ?string $fonction, bool $estActive, ?string $roleID, ?array $entiteID): Utilisateur
    {
        $authenticatedUtilisateur = $this->mustGetUtilisateur();

        // we are creating an utilisateur
        if (empty($utilisateurID)) {
            $newUtilisateur = new Utilisateur(
                $nom,
                $prenom,
                $email,
                Chronos::now()
            );
            $newUtilisateur->setEstActive(true);
            $newUtilisateur->setTelephone($telephone);
            $newUtilisateur->setFonction($fonction);
            $newUtilisateur->setCreePar($authenticatedUtilisateur);
            $utilisateurWithEmail = $this->utilisateurDao->findOneByEmail($email);
            if ($utilisateurWithEmail !== null) {
                throw HTTPException::badRequest('Cet e-mail est déjà utilisé!');
            }
            $this->utilisateurDao->save($newUtilisateur);
            if (! empty($roleID) && ! empty($entiteID)) {
                $role = $this->roleDao->getById($roleID);
                $entites = $this->entiteDao->getEntitesByIds($entiteID);
                foreach ($entites as $oneEntite) {
                    $newUtilisateurRoleEntite = new UtilisateurRoleEntite($newUtilisateur, $role, $oneEntite);
                    $this->utilisateurRoleEntiteDao->save($newUtilisateurRoleEntite);
                }
            }

            $this->utilisateurService->generateAndSendToken($newUtilisateur);

            return $newUtilisateur;
        }

        // we are updating an utilisateur.
        if ($estActive === false && $utilisateurID === $authenticatedUtilisateur->getId()) {
            throw HTTPException::unauthorized('Vous ne pouvez pas vous désactiver vous-même!');
        }
        $result = $this->utilisateurDao->findOneByEmail($email);
        $utilisateur = $this->utilisateurDao->getById($utilisateurID);
        if ($result !== null && ($result !== $utilisateur)) {
            throw HTTPException::badRequest('Cet e-mail est déjà utilisé!');
        }
        $utilisateur->setNom($nom);
        $utilisateur->setPrenom($prenom);
        $utilisateur->setTelephone($telephone);
        $utilisateur->setFonction($fonction);
        $utilisateur->setEmail($email);
        $utilisateur->setEstActive($estActive);
        $utilisateur->setModifiePar($authenticatedUtilisateur);
        $utilisateur->setDateModification(Chronos::now());
        $this->utilisateurDao->save($utilisateur);
        if (! empty($roleID) && ! empty($entiteID)) {
            $role = $this->roleDao->getById($roleID);
            $entites = $this->entiteDao->getEntitesByIds($entiteID);
            $existingEntites = $this->utilisateurRoleEntiteDao->findByUser($utilisateur);
            /** @var UtilisateurRoleEntite $existingEntite */
            foreach ($existingEntites as $existingEntite) {
                $this->utilisateurRoleEntiteDao->delete($existingEntite);
            }
            foreach ($entites as $entite) {
                $updateUtilisateurRoleEntite = new UtilisateurRoleEntite($utilisateur, $role, $entite);
                $this->utilisateurRoleEntiteDao->save($updateUtilisateurRoleEntite);
            }
        }

        return $utilisateur;
    }
}
