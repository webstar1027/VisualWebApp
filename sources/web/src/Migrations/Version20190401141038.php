<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use function Safe\password_hash;

/**
 * Add admin users
 */
final class Version20190401141038 extends AbstractMigration
{
    /** @var string  */
    public const UUID_USER_ADMIN_CENTRAL = '6035eb9f-2641-4b60-bb21-2b851fcc849f';
    /** @var string  */
    public const ADMIN_CENTRAL_PASSWORD = 'admin';
    /** @var string  */
    public const UUID_USER_ADMIN_SIMULATION = 'd1c29af3-3574-45c1-ba5d-fc60cdbfb889';
    /** @var string  */
    public const ADMIN_SIMU_PASSWORD = 'admin';

    public function up(Schema $schema) : void
    {
        $uuidUserAdminCentral = addslashes(self::UUID_USER_ADMIN_CENTRAL);
        $passwordAdminCentral = addslashes(password_hash(self::ADMIN_CENTRAL_PASSWORD, PASSWORD_BCRYPT));
        $uuidUserAdminSimulation = addslashes(self::UUID_USER_ADMIN_SIMULATION);
        $passwordAdminSimulation = addslashes(password_hash(self::ADMIN_SIMU_PASSWORD, PASSWORD_BCRYPT));


        $uuidRoleAdminCentral = addslashes(Version20190328110217::UUID_ROLE_ADMIN_CENTRAL);
        $uuidRoleAdminSimulation = addslashes(Version20190328110217::UUID_ROLE_ADMIN_SIMULATION);
        $uuidAdministrationEntity = addslashes('e66f2eb6-ca75-450a-a5f1-427e24242a85');

        // ----------------------------- UTILISATEURS -----------------------------

        $this->addSql("INSERT INTO utilisateurs (id, nom, prenom, email, mot_de_passe, est_administrateur_central, est_administrateur_simulation, est_active, date_creation) VALUES ('$uuidUserAdminCentral', 'Neuhart', 'Julien','admin.central@visialweb.com', '$passwordAdminCentral', true, false, true, NOW())");
        $this->addSql("INSERT INTO utilisateurs (id, nom, prenom, email, mot_de_passe, est_administrateur_central, est_administrateur_simulation, est_active, date_creation) VALUES ('$uuidUserAdminSimulation', 'Peguin', 'Nicolas','admin.simulation@visialweb.com', '$passwordAdminSimulation', false, true, true, NOW())");

        // ----------------------------- ENTITES -----------------------------

        $this->addSql("INSERT INTO entites (id, cree_par, siren, nom, code, `type`, est_activee, date_creation) VALUES ('$uuidAdministrationEntity', '$uuidUserAdminCentral', '000 000 000','Administration', 'ADMIN', 'Partenaire', false, NOW())");
        // ----------------------------- JUNCTURES -----------------------------

        $this->addSql("INSERT INTO utilisateurs_roles_entites (utilisateur_id, role_id, entite_id) VALUES ('$uuidUserAdminCentral', '$uuidRoleAdminCentral', '$uuidAdministrationEntity')");
        $this->addSql("INSERT INTO utilisateurs_roles_entites (utilisateur_id, role_id, entite_id) VALUES ('$uuidUserAdminSimulation', '$uuidRoleAdminSimulation', '$uuidAdministrationEntity')");
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
