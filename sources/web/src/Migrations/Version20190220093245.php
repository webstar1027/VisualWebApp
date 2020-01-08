<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use TheCodingMachine\FluidSchema\TdbmFluidSchema;

/**
 * Create back-office tables
 */
final class Version20190220093245 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $db = new TdbmFluidSchema($schema);

        $db->table('roles')
            ->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('nom')->string(255)->notNull()->unique()->graphqlField()
            ->column('description')->text()->null()->default(null)->graphqlField()
            ->column('est_visible')->boolean()->notNull()->default(true)->graphqlField()
            ->column('date_creation')->datetimeImmutable()->notNull()->graphqlField()
            ->column('date_modification')->datetimeImmutable()->null()->default(null)->graphqlField();

        $db->table('droits')
            ->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('nom')->string(255)->notNull()->unique()->graphqlField()
            ->column('libelle')->string(255)->notNull()->unique()->graphqlField()
            ->column('categorie')->string(100)->notNull()->graphqlField()
            ->column('date_creation')->datetimeImmutable()->notNull()->graphqlField()
            ->column('date_modification')->datetimeImmutable()->null()->default(null)->graphqlField();


        $db->junctionTable('roles', 'droits')->graphqlField();

        $db->table('utilisateurs')
            ->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('nom')->string(255)->notNull()->graphqlField()
            ->column('prenom')->string(255)->notNull()->graphqlField()
            ->column('email')->string(255)->notNull()->unique()->graphqlField()
            ->column('mot_de_passe')->string(255)->null()->default(null)
            ->column('telephone')->string(255)->null()->default(null)->graphqlField()
            ->column('fonction')->string(255)->null()->default(null)->graphqlField()
            ->column('est_administrateur_central')->boolean()->notNull()->default(false)->graphqlField()
            ->column('est_administrateur_simulation')->boolean()->notNull()->default(false)->graphqlField()
            ->column('token')->string(255)->null()->graphqlField()
            ->column('est_active')->boolean()->notNull()->default(true)->graphqlField()
            ->column('date_creation')->datetimeImmutable()->notNull()->graphqlField()
            ->column('cree_par')->references('utilisateurs')->null()->default(null)->graphqlField()
            ->column('date_modification')->datetimeImmutable()->null()->default(null)->graphqlField()
            ->column('modifie_par')->references('utilisateurs')->null()->default(null)->graphqlField();

        $db->table('entites')
            ->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('siren')->string(255)->notNull()->unique()->graphqlField()
            ->column('nom')->string(255)->notNull()->unique()->graphqlField()
            ->column('code')->string(255)->notNull()->unique()->graphqlField()
            ->column('type')->string(255)->notNull()->index()->graphqlField()
            ->column('code_organisme')->string(255)->null()->default(null)->graphqlField()
            ->column('type_organisme')->string(255)->null()->default(null)->graphqlField()
            ->column('est_activee')->boolean()->notNull()->default(true)->graphqlField()
            ->column('date_creation')->datetimeImmutable()->notNull()->graphqlField()
            ->column('cree_par')->references('utilisateurs')->notNull()->graphqlField()
            ->column('date_modification')->datetimeImmutable()->null()->default(null)->graphqlField()
            ->column('modifie_par')->references('utilisateurs')->null()->default(null)->graphqlField();

        $db->table('referents_entites')->addAnnotation('TheCodingMachine\\GraphQLite\\Annotations\\Field')
            ->column('utilisateur_id')->references('utilisateurs')
            ->column('entite_id')->references('entites')
            ->then()->primaryKey(['utilisateur_id', 'entite_id']);

        $db->table('utilisateurs_roles_entites')
            ->column('utilisateur_id')->references('utilisateurs')->graphqlField()
            ->column('role_id')->references('roles')->graphqlField()
            ->column('entite_id')->references('entites')->graphqlField()->endGraphql()
            ->then()->primaryKey(['utilisateur_id', 'role_id', 'entite_id']);

        $db->table('ensembles')
            ->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('nom')->string(255)->notNull()->unique()->graphqlField()
            ->column('description')->text()->null()->default(null)->graphqlField()
            ->column('est_active')->boolean()->notNull()->default(true)->graphqlField()
            ->column('date_creation')->datetimeImmutable()->notNull()->graphqlField()
            ->column('cree_par')->references('utilisateurs')->notNull()->graphqlField()
            ->column('date_modification')->datetimeImmutable()->null()->default(null)->graphqlField()
            ->column('modifie_par')->references('utilisateurs')->null()->default(null)->graphqlField();

        $db->table('referents_ensembles')->addAnnotation('TheCodingMachine\\GraphQLite\\Annotations\\Field')
            ->column('entite_id')->references('entites')
            ->column('ensemble_id')->references('ensembles')
            ->then()->primaryKey(['entite_id', 'ensemble_id']);

        $db->junctionTable('ensembles', 'entites')->graphqlField();

        $db->table('invitations_ensembles')->addAnnotation('TheCodingMachine\\GraphQLite\\Annotations\\Field')
            ->column('ensemble_id')->references('ensembles')->graphqlField()
            ->column('entite_id')->references('entites')->graphqlField()
            ->column('statut')->string(255)->notNull()->graphqlField()->endGraphql()
            ->then()->primaryKey(['ensemble_id', 'entite_id']);


    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
