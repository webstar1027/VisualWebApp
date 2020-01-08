<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use TheCodingMachine\FluidSchema\TdbmFluidSchema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191104102345 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $db = new TdbmFluidSchema($schema);

        $table = $db->table('nouveaux_foyer');
        $table->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('simulation_id')->references('simulations')->graphqlField()
            ->column('numero')->integer()->notNull()->graphqlField()
            ->column('nom_intervention')->string(255)->notNull()->graphqlField()
            ->column('nature')->string(255)->notNull()->graphqlField()
            ->column('nombre_logements')->integer()->notNull()->graphqlField()
            ->column('annee_agrement')->datetimeImmutable()->null()->graphqlField()
            ->column('date_acquisition')->datetimeImmutable()->null()->graphqlField()
            ->column('date_travaux')->datetimeImmutable()->null()->graphqlField()
            ->column('indexation_icc')->boolean()->notNull()->default(false)->graphqlField()
            ->column('prix_revient')->float(4)->graphqlField()
            ->column('fonds_propres')->float(4)->graphqlField()
            ->column('subventions_etat')->float(4)->null()->graphqlField()
            ->column('subventions_anru')->float(4)->null()->graphqlField()
            ->column('subventions_epci')->float(4)->null()->graphqlField()
            ->column('subventions_departement')->float(4)->null()->graphqlField()
            ->column('subventions_region')->float(4)->null()->graphqlField()
            ->column('subventions_collecteur')->float(4)->null()->graphqlField()
            ->column('autres_subventions')->float(4)->null()->graphqlField()
            ->column('tfpb_taux_evolution')->float(4)->null()->graphqlField()
            ->column('redevances_taux_evolution')->float(4)->null()->graphqlField()
            ->column('maintenance_taux_evolution')->float(4)->null()->graphqlField()
            ->column('gros_taux_evolution')->float(4)->null()->graphqlField()
            ->column('cout_foncier')->float(4)->null()->graphqlField()
            ->column('total_emprunt')->float(4)->null()->graphqlField()
            ->column('modele_damortissement_id')->references('modele_damortissement')->null()->graphqlField();

        $table->unique(['numero', 'simulation_id']);

        $db->table('type_emprunt_nouveaux_foyer')
            ->column('types_emprunts_id')->references('types_emprunts')->graphqlField()
            ->column('nouveaux_foyer_id')->references('nouveaux_foyer')->graphqlField()->endGraphql()
            ->then()->primaryKey(['types_emprunts_id', 'nouveaux_foyer_id'])
            ->column('montant')->float(4)->null()->graphqlField()
            ->column('date_premiere')->datetimeImmutable()->null()->graphqlField();

        $db->table('nouveaux_foyer_periodique')
            ->column('nouveaux_foyer_id')->references('nouveaux_foyer')->graphqlField()
            ->column('iteration')->integer()->notNull()->graphqlField()->endGraphql()
            ->then()->primaryKey(['nouveaux_foyer_id', 'iteration'])
            ->column('redevances')->float(4)->null()->default(null)->graphqlField()
            ->column('complements_capital')->float(4)->null()->default(null)->graphqlField()
            ->column('complements_interet')->float(4)->null()->default(null)->graphqlField()
            ->column('tfpb')->float(4)->null()->default(null)->graphqlField()
            ->column('maintenance_courante')->float(4)->null()->default(null)->graphqlField()
            ->column('gros_entretien')->float(4)->null()->default(null)->graphqlField();
    }

    public function down(Schema $schema) : void
    {
    }
}
