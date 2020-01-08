<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add permission data
 */
final class Version20190328110217 extends AbstractMigration
{
    /** @var string  */
    public const UUID_DROIT_CREATE_USER = '8c768ba4-d537-4fdd-9e17-1f7c3a7ad25e';
    /** @var string  */
    public const UUID_DROIT_READ_USER = 'd525c8b8-faf7-4ca5-830e-3f9c471489bf';
        /** @var string  */
    public const UUID_DROIT_UPDATE_USER_EMAIL = '0ef657a0-d0d0-4edd-8808-29fa92cc83cb';
        /** @var string  */
    public const UUID_DROIT_UPDATE_USER_EXCEPT_EMAIL = '9dd5fa76-1769-429d-85c5-e4789335c0ff';
        /** @var string  */
    public const UUID_DROIT_DELETE_USER = '5ecb6837-b25a-4607-a2fb-28d6599ca389';
        /** @var string  */
    public const UUID_DROIT_REACTIVATE_USER = 'b0843c80-0733-442c-9fb4-ea8dd8f9b7fd';
        /** @var string  */
    public const UUID_DROIT_ASSIGN_ROLE_USER = '7342fe32-bb02-4a9d-b2a4-c9aff85eba61';
        /** @var string  */
    public const UUID_DROIT_CREATE_ROLE = '7b42bad8-f505-49f4-bf27-52933f7f39ce';
        /** @var string  */
    public const UUID_DROIT_READ_ROLE = 'ae00f084-9c24-41c3-971c-3148c0428327';
        /** @var string  */
    public const UUID_DROIT_UPDATE_ROLE = '308b25d0-b60e-46dc-9369-958ccc07a6cc';
        /** @var string  */
    public const UUID_DROIT_DELETE_ROLE = '556f2115-4108-48ad-9a69-47fb916b4c02';
        /** @var string  */
    public const UUID_DROIT_REACTIVATE_ROLE = 'cdd81653-5c8a-41e7-a1df-f8a9d7d94cf9';
        /** @var string  */
    public const UUID_DROIT_CREATE_ENTITY = '3a0bd21f-25e9-4366-b1fb-7a14d69d93bb';
        /** @var string  */
    public const UUID_DROIT_READ_ENTITY = '28886672-0d10-4df7-a549-d3fbe3c3509c';
        /** @var string  */
    public const UUID_DROIT_UPDATE_ENTITY = '036d66a8-3e35-4298-a4a7-3a278150bd9d';
        /** @var string  */
    public const UUID_DROIT_DELETE_ENTITY = 'fd486984-82cc-40e3-845a-c7f7804db58d';
        /** @var string  */
    public const UUID_DROIT_REACTIVATE_ENTITY = 'd55562e5-7aad-45e4-a32e-e951fcdb9371';
        /** @var string  */
    public const UUID_DROIT_MERGE_ENTITY = '6167542c-81d2-4289-bd21-3a9310782874';
        /** @var string  */
    public const UUID_DROIT_READ_USER_INVITATION_ENTITY = 'befed82c-9da2-4afc-a3c5-88b79df25654';
        /** @var string  */
    public const UUID_DROIT_INVITE_USER_ENTITY = '3609f9ec-1fd8-455f-841d-f6529e605168';
        /** @var string  */
    public const UUID_DROIT_EXCLUDE_USER_ENTITY = '2fde0ca2-5e07-4e01-8620-ca90739c6335';
        /** @var string  */
    public const UUID_DROIT_CREATE_GROUP = '109a79c8-ac88-4fe5-9aa1-abaf64901887';
        /** @var string  */
    public const UUID_DROIT_READ_GROUP = '07e73f3a-1162-4a32-a9a5-0f5d339e8b89';
        /** @var string  */
    public const UUID_DROIT_UPDATE_GROUP = 'baffbff9-ed9d-4f2b-b9a7-a9a7bf24abe7';
        /** @var string  */
    public const UUID_DROIT_DELETE_GROUP = '3826b12a-a772-48d9-9dc2-a68062a2d6fe';
        /** @var string  */
    public const UUID_DROIT_REACTIVATE_GROUP = '2ca6323e-033f-463a-8edd-ff47e420cf2e';
        /** @var string  */
    public const UUID_DROIT_READ_ENTITY_INVITATION_GROUP = '8185903b-fcd8-41ed-8cce-3669a6dc32b0';
        /** @var string  */
    public const UUID_DROIT_INVITE_ENTITY_GROUP = '94973054-22e8-4016-a426-d3ef39ee08c2';
        /** @var string  */
    public const UUID_DROIT_EXCLUDE_ENTITY_GROUP = '3d3110a8-af9f-4b5f-ba42-e21809daac9e';
        /** @var string  */
    public const UUID_DROIT_CREATE_SIMULATION = '57a303fd-b4d2-4439-8547-b2934ff878c7';
        /** @var string  */
    public const UUID_DROIT_CREATE_SIMULATION_OTHER_ENTITY = 'df529d71-174f-4084-9958-38d29c9824b7';
        /** @var string  */
    public const UUID_DROIT_READ_SIMULATION = 'f0957fd8-967a-4b32-91c6-89ca2211e30b';
        /** @var string  */
    public const UUID_DROIT_UPDATE_SIMULATION = '6bd181d8-bbdb-496f-afeb-a5b555e634c3';
        /** @var string  */
    public const UUID_DROIT_DELETE_SIMULATION = '29a8e899-6f26-4d6e-9d8c-aebbd43873ed';
        /** @var string  */
    public const UUID_DROIT_DUPLICATE_SIMULATION = '6894c5e9-293d-40ee-ade6-7db819abf853';
        /** @var string  */
    public const UUID_DROIT_AGGREGATE_SIMULATION = '0290d8bc-dd21-4e60-a691-2f2ed55b394a';
        /** @var string  */
    public const UUID_DROIT_MERGE_SIMULATION = 'a17b9452-0144-4e35-9e4a-d9b123b9320f';
        /** @var string  */
    public const UUID_DROIT_SHARE_SIMULATION = 'b2725816-b7f8-4e81-a8f6-86bfe6d05c64';
        /** @var string  */
    public const UUID_DROIT_COMPARE_SIMULATION = 'a9610bf9-de52-49bf-bba5-0ca0c00d152f';

    /** @var string  */
    public const UUID_ROLE_UTILISATEUR = '10842880-3eb4-4225-bc7b-150b8dec899f';
    /** @var string  */
    public const UUID_ROLE_REFERENT_ENTITE = '1f2694ef-3a09-4f92-a720-ea27f4888487';
    /** @var string  */
    public const UUID_ROLE_REFERENT_ENSEMBLE = '418887a6-8cd4-475e-8bdc-b51ff24fb51a';
    /** @var string  */
    public const UUID_ROLE_ADMIN_CENTRAL = '4dcb4fbc-a2a0-4476-8921-52e369343204';
    /** @var string  */
    public const UUID_ROLE_ADMIN_SIMULATION = '4e6321b9-77e3-4abe-b518-6bd581e1ef23';

    public function up(Schema $schema) : void
    {
        $uuidDroitCreateUser = addslashes(self::UUID_DROIT_CREATE_USER);
        $uuidDroitReadUser = addslashes(self::UUID_DROIT_READ_USER);
        $uuidDroitUpdateUserEmail = addslashes(self::UUID_DROIT_UPDATE_USER_EMAIL);
        $uuidDroitUpdateUserExceptEmail = addslashes(self::UUID_DROIT_UPDATE_USER_EXCEPT_EMAIL);
        $uuidDroitDeleteUser = addslashes(self::UUID_DROIT_DELETE_USER);
        $uuidDroitReactivateUser = addslashes(self::UUID_DROIT_REACTIVATE_USER);
        $uuidDroitAssignRoleUser = addslashes(self::UUID_DROIT_ASSIGN_ROLE_USER);
        $uuidDroitCreateRole = addslashes(self::UUID_DROIT_CREATE_ROLE);
        $uuidDroitReadRole = addslashes(self::UUID_DROIT_READ_ROLE);
        $uuidDroitUpdateRole = addslashes(self::UUID_DROIT_UPDATE_ROLE);
        $uuidDroitDeleteRole = addslashes(self::UUID_DROIT_DELETE_ROLE);
        $uuidDroitReactivateRole = addslashes(self::UUID_DROIT_REACTIVATE_ROLE);
        $uuidDroitCreateEntity = addslashes(self::UUID_DROIT_CREATE_ENTITY);
        $uuidDroitReadEntity = addslashes(self::UUID_DROIT_READ_ENTITY);
        $uuidDroitUpdateEntity = addslashes(self::UUID_DROIT_UPDATE_ENTITY);
        $uuidDroitDeleteEntity = addslashes(self::UUID_DROIT_DELETE_ENTITY);
        $uuidDroitReactivateEntity = addslashes(self::UUID_DROIT_REACTIVATE_ENTITY);
        $uuidDroitMergeEntity = addslashes(self::UUID_DROIT_MERGE_ENTITY);
        $uuidDroitReadUserInvitationEntity = addslashes(self::UUID_DROIT_READ_USER_INVITATION_ENTITY);
        $uuidDroitInviteUserEntity = addslashes(self::UUID_DROIT_INVITE_USER_ENTITY);
        $uuidDroitExcludeUserEntity = addslashes(self::UUID_DROIT_EXCLUDE_USER_ENTITY);
        $uuidDroitCreateGroup = addslashes(self::UUID_DROIT_CREATE_GROUP);
        $uuidDroitReadGroup = addslashes(self::UUID_DROIT_READ_GROUP);
        $uuidDroitUpdateGroup = addslashes(self::UUID_DROIT_UPDATE_GROUP);
        $uuidDroitDeleteGroup = addslashes(self::UUID_DROIT_DELETE_GROUP);
        $uuidDroitReactivateGroup = addslashes(self::UUID_DROIT_REACTIVATE_GROUP);
        $uuidDroitReadEntityInvitationGroup = addslashes(self::UUID_DROIT_READ_ENTITY_INVITATION_GROUP);
        $uuidDroitInviteEntityGroup = addslashes(self::UUID_DROIT_INVITE_ENTITY_GROUP);
        $uuidDroitExcludeEntityGroup = addslashes(self::UUID_DROIT_EXCLUDE_ENTITY_GROUP);
        $uuidDroitCreateSimulation = addslashes(self::UUID_DROIT_CREATE_SIMULATION);
        $uuidDroitCreateSimulationOtherEntity = addslashes(self::UUID_DROIT_CREATE_SIMULATION_OTHER_ENTITY);
        $uuidDroitReadSimulation = addslashes(self::UUID_DROIT_READ_SIMULATION);
        $uuidDroitUpdateSimulation = addslashes(self::UUID_DROIT_UPDATE_SIMULATION);
        $uuidDroitDeleteSimulation = addslashes(self::UUID_DROIT_DELETE_SIMULATION);
        $uuidDroitDuplicateSimulation = addslashes(self::UUID_DROIT_DUPLICATE_SIMULATION);
        $uuidDroitAggregateSimulation = addslashes(self::UUID_DROIT_AGGREGATE_SIMULATION);
        $uuidDroitMergeSimulation = addslashes(self::UUID_DROIT_MERGE_SIMULATION);
        $uuidDroitShareSimulation = addslashes(self::UUID_DROIT_SHARE_SIMULATION);
        $uuidDroitCompareSimulation = addslashes(self::UUID_DROIT_COMPARE_SIMULATION);

        $uuidRoleUtilisateur = addslashes(self::UUID_ROLE_UTILISATEUR);
        $uuidRoleReferentEntite = addslashes(self::UUID_ROLE_REFERENT_ENTITE);
        $uuidRoleReferentEnsemble = addslashes(self::UUID_ROLE_REFERENT_ENSEMBLE);
        $uuidRoleAdminCentral = addslashes(self::UUID_ROLE_ADMIN_CENTRAL);
        $uuidRoleAdminSimulation = addslashes(self::UUID_ROLE_ADMIN_SIMULATION);

        // ----------------------------- DROITS -----------------------------

        // Category: Utilisateurs
        $this->addSql("INSERT INTO droits (id, nom, libelle, categorie, date_creation) VALUES ('$uuidDroitCreateUser', 'CREATE_USER', 'Créer un utilisateur','Utilisateurs', NOW())");
        $this->addSql("INSERT INTO droits (id, nom, libelle, categorie, date_creation) VALUES ('$uuidDroitReadUser', 'READ_USER', 'Consulter un utilisateur','Utilisateurs', NOW())");
        $this->addSql("INSERT INTO droits (id, nom, libelle, categorie, date_creation) VALUES ('$uuidDroitUpdateUserEmail', 'UPDATE_USER_EMAIL', 'Modifier l\'email d\'un utilisateur','Utilisateurs', NOW())");
        $this->addSql("INSERT INTO droits (id, nom, libelle, categorie, date_creation) VALUES ('$uuidDroitUpdateUserExceptEmail', 'UPDATE_USER_EXCEPT_EMAIL', 'Modifier un utilisateur (hors email)','Utilisateurs', NOW())");
        $this->addSql("INSERT INTO droits (id, nom, libelle, categorie, date_creation) VALUES ('$uuidDroitDeleteUser', 'DELETE_USER', 'Supprimer un utilisateur','Utilisateurs', NOW())");
        $this->addSql("INSERT INTO droits (id, nom, libelle, categorie, date_creation) VALUES ('$uuidDroitReactivateUser', 'REACTIVATE_USER', 'Réactiver un utilisateur supprimé','Utilisateurs', NOW())");
        $this->addSql("INSERT INTO droits (id, nom, libelle, categorie, date_creation) VALUES ('$uuidDroitAssignRoleUser', 'ASSIGN_ROLE_USER', 'Assigner un rôle à un utilisateur','Utilisateurs', NOW())");

        // Category: Rôles
        $this->addSql("INSERT INTO droits (id, nom, libelle, categorie, date_creation) VALUES ('$uuidDroitCreateRole', 'CREATE_ROLE', 'Créer un rôle','Rôles', NOW())");
        $this->addSql("INSERT INTO droits (id, nom, libelle, categorie, date_creation) VALUES ('$uuidDroitReadRole', 'READ_ROLE', 'Consulter un rôle','Rôles', NOW())");
        $this->addSql("INSERT INTO droits (id, nom, libelle, categorie, date_creation) VALUES ('$uuidDroitUpdateRole', 'UPDATE_ROLE', 'Modifier un rôle','Rôles', NOW())");
        $this->addSql("INSERT INTO droits (id, nom, libelle, categorie, date_creation) VALUES ('$uuidDroitDeleteRole', 'DELETE_ROLE', 'Supprimer un rôle','Rôles', NOW())");
        $this->addSql("INSERT INTO droits (id, nom, libelle, categorie, date_creation) VALUES ('$uuidDroitReactivateRole', 'REACTIVATE_ROLE', 'Réactiver un rôle supprimé','Rôles', NOW())");

        // Category: Entités
        $this->addSql("INSERT INTO droits (id, nom, libelle, categorie, date_creation) VALUES ('$uuidDroitCreateEntity', 'CREATE_ENTITY', 'Créer une entité','Entités', NOW())");
        $this->addSql("INSERT INTO droits (id, nom, libelle, categorie, date_creation) VALUES ('$uuidDroitReadEntity', 'READ_ENTITY', 'Consulter une entité','Entités', NOW())");
        $this->addSql("INSERT INTO droits (id, nom, libelle, categorie, date_creation) VALUES ('$uuidDroitUpdateEntity', 'UPDATE_ENTITY', 'Modifier une entité','Entités', NOW())");
        $this->addSql("INSERT INTO droits (id, nom, libelle, categorie, date_creation) VALUES ('$uuidDroitDeleteEntity', 'DELETE_ENTITY', 'Supprimer une entité','Entités', NOW())");
        $this->addSql("INSERT INTO droits (id, nom, libelle, categorie, date_creation) VALUES ('$uuidDroitReactivateEntity', 'REACTIVATE_ENTITY', 'Réactiver une entité supprimée','Entités', NOW())");
        $this->addSql("INSERT INTO droits (id, nom, libelle, categorie, date_creation) VALUES ('$uuidDroitMergeEntity', 'MERGE_ENTITY', 'Fusionner des entités','Entités', NOW())");
        $this->addSql("INSERT INTO droits (id, nom, libelle, categorie, date_creation) VALUES ('$uuidDroitReadUserInvitationEntity', 'READ_USER_INVITATION_ENTITY', 'Consulter les invitations des utilisateurs','Entités', NOW())");
        $this->addSql("INSERT INTO droits (id, nom, libelle, categorie, date_creation) VALUES ('$uuidDroitInviteUserEntity', 'INVITE_USER_ENTITY', 'Inviter un utilisateur dans une entité','Entités', NOW())");
        $this->addSql("INSERT INTO droits (id, nom, libelle, categorie, date_creation) VALUES ('$uuidDroitExcludeUserEntity', 'EXCLUDE_USER_ENTITY', 'Exclure un utilisateur d\'une entité','Entités', NOW())");

        // Category: Ensembles
        $this->addSql("INSERT INTO droits (id, nom, libelle, categorie, date_creation) VALUES ('$uuidDroitCreateGroup', 'CREATE_GROUP', 'Créer un ensemble','Ensembles', NOW())");
        $this->addSql("INSERT INTO droits (id, nom, libelle, categorie, date_creation) VALUES ('$uuidDroitReadGroup', 'READ_GROUP', 'Consulter un ensemble','Ensembles', NOW())");
        $this->addSql("INSERT INTO droits (id, nom, libelle, categorie, date_creation) VALUES ('$uuidDroitUpdateGroup', 'UPDATE_GROUP', 'Modifier un ensemble','Ensembles', NOW())");
        $this->addSql("INSERT INTO droits (id, nom, libelle, categorie, date_creation) VALUES ('$uuidDroitDeleteGroup', 'DELETE_GROUP', 'Supprimer un ensemble','Ensembles', NOW())");
        $this->addSql("INSERT INTO droits (id, nom, libelle, categorie, date_creation) VALUES ('$uuidDroitReactivateGroup', 'REACTIVATE_GROUP', 'Réactiver un ensemble supprimé','Ensembles', NOW())");
        $this->addSql("INSERT INTO droits (id, nom, libelle, categorie, date_creation) VALUES ('$uuidDroitReadEntityInvitationGroup', 'READ_ENTITY_INVITATION_GROUP', 'Consulter les invitations des entités','Ensembles', NOW())");
        $this->addSql("INSERT INTO droits (id, nom, libelle, categorie, date_creation) VALUES ('$uuidDroitInviteEntityGroup', 'INVITE_ENTITY_GROUP', 'Inviter une entité dans un ensemble','Ensembles', NOW())");
        $this->addSql("INSERT INTO droits (id, nom, libelle, categorie, date_creation) VALUES ('$uuidDroitExcludeEntityGroup', 'EXCLUDE_ENTITY_GROUP', 'Exclure une entité d\'un ensemble','Ensembles', NOW())");

        // Category: Simulations
        $this->addSql("INSERT INTO droits (id, nom, libelle, categorie, date_creation) VALUES ('$uuidDroitCreateSimulation', 'CREATE_SIMULATION', 'Créer une simulation','Simulations', NOW())");
        $this->addSql("INSERT INTO droits (id, nom, libelle, categorie, date_creation) VALUES ('$uuidDroitCreateSimulationOtherEntity', 'CREATE_SIMULATION_OTHER_ENTITY', 'Créer une simulation pour une autre entité','Simulations', NOW())");
        $this->addSql("INSERT INTO droits (id, nom, libelle, categorie, date_creation) VALUES ('$uuidDroitReadSimulation', 'READ_SIMULATION', 'Consulter une simulation','Simulations', NOW())");
        $this->addSql("INSERT INTO droits (id, nom, libelle, categorie, date_creation) VALUES ('$uuidDroitUpdateSimulation', 'UPDATE_SIMULATION', 'Modifier une simulation','Simulations', NOW())");
        $this->addSql("INSERT INTO droits (id, nom, libelle, categorie, date_creation) VALUES ('$uuidDroitDeleteSimulation', 'DELETE_SIMULATION', 'Supprimer une simulation','Simulations', NOW())");
        $this->addSql("INSERT INTO droits (id, nom, libelle, categorie, date_creation) VALUES ('$uuidDroitDuplicateSimulation', 'DUPLICATE_SIMULATION', 'Duppliquer une simulation','Simulations', NOW())");
        $this->addSql("INSERT INTO droits (id, nom, libelle, categorie, date_creation) VALUES ('$uuidDroitAggregateSimulation', 'AGGREGATE_SIMULATION', 'Aggréger des simulations','Simulations', NOW())");
        $this->addSql("INSERT INTO droits (id, nom, libelle, categorie, date_creation) VALUES ('$uuidDroitMergeSimulation', 'MERGE_SIMULATION', 'Fusionner des simulations','Simulations', NOW())");
        $this->addSql("INSERT INTO droits (id, nom, libelle, categorie, date_creation) VALUES ('$uuidDroitShareSimulation', 'SHARE_SIMULATION', 'Partager une simulation','Simulations', NOW())");
        $this->addSql("INSERT INTO droits (id, nom, libelle, categorie, date_creation) VALUES ('$uuidDroitCompareSimulation', 'COMPARE_SIMULATION', 'Comparer des simulations','Simulations', NOW())");

        // ----------------------------- ROLES -----------------------------

        $this->addSql("INSERT INTO roles (id, nom, est_visible, date_creation) VALUES ('$uuidRoleUtilisateur', 'Utilisateur', true, NOW())");
        $this->addSql("INSERT INTO roles (id, nom, est_visible, date_creation) VALUES ('$uuidRoleReferentEntite', 'Référent entité', false, NOW())");
        $this->addSql("INSERT INTO roles (id, nom, est_visible, date_creation) VALUES ('$uuidRoleReferentEnsemble', 'Référent ensemble', false, NOW())");
        $this->addSql("INSERT INTO roles (id, nom, est_visible, date_creation) VALUES ('$uuidRoleAdminCentral', 'Administrateur central', false, NOW())");
        $this->addSql("INSERT INTO roles (id, nom, est_visible, date_creation) VALUES ('$uuidRoleAdminSimulation', 'Administrateur simulation', false, NOW())");

        // ----------------------------- JUNCTURES -----------------------------

        // Role Utilisateur
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleUtilisateur', '$uuidDroitUpdateUserExceptEmail')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleUtilisateur', '$uuidDroitCreateSimulation')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleUtilisateur', '$uuidDroitReadSimulation')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleUtilisateur', '$uuidDroitUpdateSimulation')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleUtilisateur', '$uuidDroitDeleteSimulation')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleUtilisateur', '$uuidDroitDuplicateSimulation')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleUtilisateur', '$uuidDroitAggregateSimulation')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleUtilisateur', '$uuidDroitMergeSimulation')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleUtilisateur', '$uuidDroitShareSimulation')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleUtilisateur', '$uuidDroitCompareSimulation')");
        // Role Référent entité
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleReferentEntite', '$uuidDroitCreateUser')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleReferentEntite', '$uuidDroitReadUser')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleReferentEntite', '$uuidDroitUpdateUserExceptEmail')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleReferentEntite', '$uuidDroitDeleteUser')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleReferentEntite', '$uuidDroitAssignRoleUser')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleReferentEntite', '$uuidDroitReadEntity')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleReferentEntite', '$uuidDroitUpdateEntity')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleReferentEntite', '$uuidDroitReadUserInvitationEntity')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleReferentEntite', '$uuidDroitInviteUserEntity')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleReferentEntite', '$uuidDroitExcludeUserEntity')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleReferentEntite', '$uuidDroitReadGroup')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleReferentEntite', '$uuidDroitCreateSimulation')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleReferentEntite', '$uuidDroitReadSimulation')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleReferentEntite', '$uuidDroitUpdateSimulation')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleReferentEntite', '$uuidDroitDeleteSimulation')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleReferentEntite', '$uuidDroitDuplicateSimulation')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleReferentEntite', '$uuidDroitAggregateSimulation')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleReferentEntite', '$uuidDroitMergeSimulation')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleReferentEntite', '$uuidDroitShareSimulation')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleReferentEntite', '$uuidDroitCompareSimulation')");
        // Role Référent ensemble
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleReferentEnsemble', '$uuidDroitReadGroup')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleReferentEnsemble', '$uuidDroitUpdateGroup')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleReferentEnsemble', '$uuidDroitDeleteGroup')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleReferentEnsemble', '$uuidDroitReadEntityInvitationGroup')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleReferentEnsemble', '$uuidDroitInviteEntityGroup')");
        // Role Administrateur central
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleAdminCentral', '$uuidDroitCreateUser')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleAdminCentral', '$uuidDroitReadUser')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleAdminCentral', '$uuidDroitUpdateUserEmail')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleAdminCentral', '$uuidDroitUpdateUserExceptEmail')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleAdminCentral', '$uuidDroitDeleteUser')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleAdminCentral', '$uuidDroitReactivateUser')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleAdminCentral', '$uuidDroitAssignRoleUser')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleAdminCentral', '$uuidDroitCreateRole')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleAdminCentral', '$uuidDroitReadRole')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleAdminCentral', '$uuidDroitUpdateRole')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleAdminCentral', '$uuidDroitDeleteRole')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleAdminCentral', '$uuidDroitReactivateRole')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleAdminCentral', '$uuidDroitCreateEntity')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleAdminCentral', '$uuidDroitReadEntity')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleAdminCentral', '$uuidDroitUpdateEntity')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleAdminCentral', '$uuidDroitDeleteEntity')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleAdminCentral', '$uuidDroitReactivateEntity')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleAdminCentral', '$uuidDroitMergeEntity')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleAdminCentral', '$uuidDroitCreateGroup')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleAdminCentral', '$uuidDroitReadGroup')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleAdminCentral', '$uuidDroitUpdateGroup')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleAdminCentral', '$uuidDroitDeleteGroup')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleAdminCentral', '$uuidDroitReactivateGroup')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleAdminCentral', '$uuidDroitReadEntityInvitationGroup')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleAdminCentral', '$uuidDroitInviteEntityGroup')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleAdminCentral', '$uuidDroitExcludeEntityGroup')");
        // Role Administrateur simulation
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleAdminSimulation', '$uuidDroitCreateSimulation')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleAdminSimulation', '$uuidDroitCreateSimulationOtherEntity')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleAdminSimulation', '$uuidDroitReadSimulation')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleAdminSimulation', '$uuidDroitUpdateSimulation')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleAdminSimulation', '$uuidDroitDeleteSimulation')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleAdminSimulation', '$uuidDroitDuplicateSimulation')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleAdminSimulation', '$uuidDroitAggregateSimulation')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleAdminSimulation', '$uuidDroitMergeSimulation')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleAdminSimulation', '$uuidDroitShareSimulation')");
        $this->addSql("INSERT INTO roles_droits (role_id, droit_id) VALUES ('$uuidRoleAdminSimulation', '$uuidDroitCompareSimulation')");
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
