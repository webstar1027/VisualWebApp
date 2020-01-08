<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use TheCodingMachine\FluidSchema\TdbmFluidSchema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190619080604 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $db = new TdbmFluidSchema($schema);

        $table = $db->table('simulations');
        $table->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('nom')->string(255)->default(null)->graphqlField()
            ->column('description')->string(255)->null()->default(null)->graphqlField()
            ->column('utilisateur')->references('utilisateurs')->graphqlField()
            ->column('entite')->references('entites')->notNull()->graphqlField()
            ->column('annee_de_reference')->string(255)->graphqlField()
            ->column('incrementation')->integer()->autoIncrement()->unique()->graphqlField()
            ->column('date_modification')->datetimeImmutable()->null()->default(null)->graphqlField()
            ->column('modifie_par')->references('utilisateurs')->null()->default(null)->graphqlField()
            ->column('est_verrouillee')->boolean()->notNull()->default(false)->graphqlField()
            ->column('est_partagee')->boolean()->notNull()->default(false)->graphqlField()
            ->column('est_fusionnee')->boolean()->notNull()->default(false)->graphqlField()
            ->column('verrouille_par')->references('utilisateurs')->null()->default(null)->graphqlField()
            ->column('supprime')->boolean()->notNull()->default(false)->graphqlField();

        $table->unique(['nom', 'utilisateur']);

        // Indice et taux
        $db->table('indices_taux')
            ->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('simulation_id')->references('simulations')->graphqlField()
            ->column('type')->string(255)->notNull()->graphqlField()
            ->column('indexation_sur_inflation')->boolean()->null()->default(null)->graphqlField()
            ->column('ecart')->float(4)->null()->default(null)->graphqlField();

        $db->table('indices_taux_periodique')
            ->column('indice_taux_id')->references('indices_taux')->graphqlField()
            ->column('iteration')->integer()->notNull()->graphqlField()->endGraphql()
            ->then()->primaryKey(['indice_taux_id', 'iteration'])
            ->column('valeur')->float(4)->null()->default(null)->graphqlField();

    }


    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
