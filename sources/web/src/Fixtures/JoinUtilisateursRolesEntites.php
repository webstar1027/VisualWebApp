<?php

declare(strict_types=1);

namespace App\Fixtures;

use App\Dao\EntiteDao;
use App\Dao\RoleDao;
use App\Dao\UtilisateurDao;
use App\Dao\UtilisateurRoleEntiteDao;
use App\Model\Entite;
use App\Model\Role;
use App\Model\Utilisateur;
use App\Model\UtilisateurRoleEntite;

class JoinUtilisateursRolesEntites implements Fixture
{
    public const NOM_ROLE_UTILISATEUR = 'Utilisateur';
    public const NOM_ROLE_REFERENT_ENTITE = 'Référent entité';
    public const NOM_ROLE_REFERENT_ENSEMBLE = 'Référent ensemble';
    public const NOM_ROLE_ADMIN_CENTRAL = 'Administrateur central';
    public const NOM_ROLE_ADMIN_SIMU = 'Administrateur simulation';

    /** @var UtilisateurRoleEntiteDao */
    private $utilisateurRoleEntiteDao;

    /** @var UtilisateurDao */
    private $utilisateurDao;

    /** @var RoleDao */
    private $roleDao;

    /** @var EntiteDao */
    private $entiteDao;

    public function __construct(UtilisateurRoleEntiteDao $utilisateurRoleEntiteDao, UtilisateurDao $utilisateurDao, RoleDao $roleDao, EntiteDao $entiteDao)
    {
        $this->utilisateurRoleEntiteDao = $utilisateurRoleEntiteDao;
        $this->utilisateurDao = $utilisateurDao;
        $this->roleDao = $roleDao;
        $this->entiteDao = $entiteDao;
    }

    public function up(): void
    {
        $this->joinUtilisateursRolesEntites(CreateUtilisateurs::UTILISATEUR_1_EMAIL, self::NOM_ROLE_UTILISATEUR, CreateEntites::CODE_1);
        $this->joinUtilisateursRolesEntites(CreateUtilisateurs::UTILISATEUR_2_EMAIL, self::NOM_ROLE_UTILISATEUR, CreateEntites::CODE_1);
        $this->joinUtilisateursRolesEntites(CreateUtilisateurs::UTILISATEUR_3_EMAIL, self::NOM_ROLE_REFERENT_ENTITE, CreateEntites::CODE_1);
        $this->joinUtilisateursRolesEntites(CreateUtilisateurs::UTILISATEUR_3_EMAIL, self::NOM_ROLE_REFERENT_ENSEMBLE, CreateEntites::CODE_1);
        $this->joinUtilisateursRolesEntites(CreateUtilisateurs::UTILISATEUR_4_EMAIL, self::NOM_ROLE_UTILISATEUR, CreateEntites::CODE_1);
        $this->joinUtilisateursRolesEntites(CreateUtilisateurs::UTILISATEUR_5_EMAIL, self::NOM_ROLE_REFERENT_ENTITE, CreateEntites::CODE_2);
        $this->joinUtilisateursRolesEntites(CreateUtilisateurs::UTILISATEUR_6_EMAIL, self::NOM_ROLE_REFERENT_ENTITE, CreateEntites::CODE_3);
        $this->joinUtilisateursRolesEntites(CreateUtilisateurs::UTILISATEUR_6_EMAIL, self::NOM_ROLE_REFERENT_ENSEMBLE, CreateEntites::CODE_3);
        $this->joinUtilisateursRolesEntites(CreateUtilisateurs::UTILISATEUR_7_EMAIL, self::NOM_ROLE_REFERENT_ENTITE, CreateEntites::CODE_4);
        $this->joinUtilisateursRolesEntites(CreateUtilisateurs::UTILISATEUR_8_EMAIL, self::NOM_ROLE_REFERENT_ENTITE, CreateEntites::CODE_5);
        $this->joinUtilisateursRolesEntites(CreateUtilisateurs::UTILISATEUR_9_EMAIL, self::NOM_ROLE_REFERENT_ENTITE, CreateEntites::CODE_6);
        $this->joinUtilisateursRolesEntites(CreateUtilisateurs::UTILISATEUR_10_EMAIL, self::NOM_ROLE_UTILISATEUR, CreateEntites::CODE_6);
        $this->joinUtilisateursRolesEntites(CreateUtilisateurs::UTILISATEUR_11_EMAIL, self::NOM_ROLE_REFERENT_ENTITE, CreateEntites::CODE_7);
        $this->joinUtilisateursRolesEntites(CreateUtilisateurs::UTILISATEUR_11_EMAIL, self::NOM_ROLE_REFERENT_ENSEMBLE, CreateEntites::CODE_7);
        $this->joinUtilisateursRolesEntites(CreateUtilisateurs::UTILISATEUR_12_EMAIL, self::NOM_ROLE_UTILISATEUR, CreateEntites::CODE_7);
        $this->joinUtilisateursRolesEntites(CreateUtilisateurs::UTILISATEUR_13_EMAIL, self::NOM_ROLE_UTILISATEUR, CreateEntites::CODE_3);
        $this->joinUtilisateursRolesEntites(CreateUtilisateurs::UTILISATEUR_14_EMAIL, self::NOM_ROLE_UTILISATEUR, CreateEntites::CODE_4);

        $this->joinUtilisateursRolesEntites(CreateUtilisateurs::UTILISATEUR_15_EMAIL, self::NOM_ROLE_REFERENT_ENTITE, CreateEntites::CODE_7);
        $this->joinUtilisateursRolesEntites(CreateUtilisateurs::UTILISATEUR_16_EMAIL, self::NOM_ROLE_UTILISATEUR, CreateEntites::CODE_7);
    }

    private function joinUtilisateursRolesEntites(string $utilisateurEmail, string $roleName, string $entiteCode): void
    {
        /** @var Utilisateur $utilisateur */
        $utilisateur = $this->utilisateurDao->findOneByEmail($utilisateurEmail);
        /** @var Role $role */
        $role = $this->roleDao->findOneByNom($roleName);
        /** @var Entite $entite */
        $entite = $this->entiteDao->findOneByCode($entiteCode);

        $utilisateurRoleEntiteDao = new UtilisateurRoleEntite(
            $utilisateur,
            $role,
            $entite
        );

        $this->utilisateurRoleEntiteDao->save($utilisateurRoleEntiteDao);
    }

    public function getName(): string
    {
        return 'Join utilisateurs roles entites';
    }
}
