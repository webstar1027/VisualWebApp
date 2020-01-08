<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use TheCodingMachine\FluidSchema\TdbmFluidSchema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190815102006 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $db = new TdbmFluidSchema($schema);

        $table = $db->table('demolition');

        $table->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('n_groupe')->integer()->graphqlField()
            ->column('n_sous_groupe')->integer()->null()->graphqlField()
            ->column('nom_groupe')->string(255)->null()->graphqlField()
            ->column('information')->string(255)->null()->graphqlField()
            ->column('numero')->integer()->null()->graphqlField()
            ->column('nom_tranche')->string(255)->null()->graphqlField()
            ->column('convention_anru')->boolean()->null()->graphqlField()
            ->column('surface_demolie')->float(4)->null()->graphqlField()
            ->column('nombre_logement_demolis')->integer()->null()->graphqlField()
            ->column('date_demolution')->datetimeImmutable()->null()->graphqlField()
            ->column('indexation_icc')->boolean()->null()->default(false)->graphqlField()

            //Prix revient
            ->column('cout_demolution')->float(4)->null()->graphqlField()
            ->column('remboursement_crd')->float(4)->null()->graphqlField()
            ->column('cout_annexes')->float(4)->null()->graphqlField()
            ->column('remboursement_subventions')->float(4)->null()->graphqlField();

            //Financement
        $table->column('founds_propres')->float(4)->null()->graphqlField()
            ->column('subventions_anru')->float(4)->null()->graphqlField()
            ->column('subventions_etat')->float(4)->null()->graphqlField()
            ->column('subventions_epci')->float(4)->null()->graphqlField()
            ->column('subventions_departement')->float(4)->null()->graphqlField()
            ->column('subventions_region')->float(4)->null()->graphqlField()
            ->column('subventions_collecteur')->float(4)->null()->graphqlField()
            ->column('autres_subventions')->float(4)->null()->graphqlField()

            ->column('montant')->float(4)->null()->graphqlField()
            ->column('tfpb')->float(4)->null()->graphqlField()
            ->column('maintenance_courante')->float(4)->null()->graphqlField()
            ->column('gros_entretien')->float(4)->null()->graphqlField();

        $table->column('nom_categorie')->string(255)->null()->graphqlField()
            ->column('surface_moyenne')->float(4)->null()->graphqlField()
            ->column('loyer_mensuel')->float(4)->null()->graphqlField()
            ->column('logements_conventionees')->boolean()->null()->graphqlField()
            ->column('nombre_annees_amortissements')->integer()->null()->graphqlField()

            ->column('type')->smallInt()->notNull()->graphqlField()
            ->column('simulation_id')->references('simulations')->notNull()->graphqlField();

        $table->unique(['n_groupe', 'simulation_id', 'type']);

        $db->table('demolition_periodique')
            ->column('demolition_id')->references('demolition')->graphqlField()
            ->column('iteration')->integer()->notNull()->graphqlField()->endGraphql()
            ->then()->primaryKey(['demolition_id', 'iteration'])
            ->column('part_capital')->float(4)->null()->default(null)->graphqlField()
            ->column('part_interets')->float(4)->null()->default(null)->graphqlField()
            ->column('nombre_logements')->float(4)->null()->default(null)->graphqlField()
            ->column('cout_moyen')->float(4)->null()->default(null)->graphqlField()
            ->column('remboursement')->float(4)->null()->default(null)->graphqlField()
            ->column('cout_annexes')->float(4)->null()->default(null)->graphqlField();

        $db->table('type_emprunt_demolition')
            ->column('types_emprunts_id')->references('types_emprunts')->graphqlField()
            ->column('demolition_id')->references('demolition')->graphqlField()->endGraphql()
            ->then()->primaryKey(['types_emprunts_id', 'demolition_id'])
            ->column('montant')->float(4)->null()->graphqlField()
            ->column('date_premiere')->datetimeImmutable()->null()->graphqlField()
            ->column('quotite_emprunt')->float(4)->null()->graphqlField();
    }

    public function down(Schema $schema) : void
    {
    }
}
