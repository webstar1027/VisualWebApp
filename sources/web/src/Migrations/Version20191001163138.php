<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use TheCodingMachine\FluidSchema\TdbmFluidSchema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191001163138 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $db = new TdbmFluidSchema($schema);

        $table = $db->table('travaux_foyer');
        $table->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('simulation_id')->references('simulations')->graphqlField()
            ->column('numero')->integer()->notNull()->graphqlField()
            ->column('nom_intervention')->string(255)->notNull()->graphqlField()
            ->column('nombre_logements')->integer()->null()->graphqlField()
            ->column('annee_agrement')->integer()->null()->graphqlField()
            ->column('date_acquisition')->datetimeImmutable()->notNull()->graphqlField()
            ->column('date_travaux')->datetimeImmutable()->notNull()->graphqlField()
            ->column('indexation_icc')->boolean()->notNull()->default(false)->graphqlField()
            ->column('prix_revient')->float(4)->notNull()->graphqlField()
            ->column('fonds_propres')->float(4)->notNull()->graphqlField()
            ->column('subventions_etat')->float(4)->null()->graphqlField()
            ->column('subventions_anru')->float(4)->null()->graphqlField()
            ->column('subventions_epci')->float(4)->null()->graphqlField()
            ->column('subventions_departement')->float(4)->null()->graphqlField()
            ->column('subventions_region')->float(4)->null()->graphqlField()
            ->column('subventions_collecteur')->float(4)->null()->graphqlField()
            ->column('autres_subventions')->float(4)->null()->graphqlField()
            ->column('total_emprunt')->float(4)->null()->graphqlField()
            ->column('modele_damortissement_id')->references('modele_damortissement')->null()->graphqlField();

    $table->unique(['numero', 'simulation_id']);

        $db->table('type_emprunt_travaux_foyer')
            ->column('types_emprunts_id')->references('types_emprunts')->graphqlField()
            ->column('travaux_foyer_id')->references('travaux_foyer')->graphqlField()->endGraphql()
            ->then()->primaryKey(['types_emprunts_id', 'travaux_foyer_id'])
            ->column('montant')->float(4)->null()->graphqlField()
            ->column('date_premiere')->datetimeImmutable()->null()->graphqlField();

        $db->table('travaux_foyer_periodique')
            ->column('travaux_foyer_id')->references('travaux_foyer')->graphqlField()
            ->column('iteration')->integer()->notNull()->graphqlField()->endGraphql()
            ->then()->primaryKey(['travaux_foyer_id', 'iteration'])
            ->column('revedance')->float(4)->null()->default(null)->graphqlField();

    }

    public function down(Schema $schema) : void
    {
    }
}
