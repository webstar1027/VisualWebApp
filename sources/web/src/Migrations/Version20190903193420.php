<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use TheCodingMachine\FluidSchema\TdbmFluidSchema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190903193420 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $db = new TdbmFluidSchema($schema);

        $table = $db->table('operation');

        $table->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('n_operation')->integer()->graphqlField()
            ->column('nom')->string(255)->graphqlField()
            ->column('nature_operation')->integer()->null()->graphqlField()
            ->column('convention_anru')->boolean()->null()->default(false)->graphqlField()
            ->column('vide_occupe')->integer()->null()->graphqlField()
            ->column('secteur_financement')->integer()->null()->graphqlField()
            ->column('surface_quittancee')->float(4)->null()->graphqlField()
            ->column('nombre_logements')->float(4)->null()->graphqlField()
            ->column('loyer_mensuel')->float(4)->null()->graphqlField()
            ->column('profil_loyer_id')->references('profils_evolution_loyers')->null()->graphqlField()
            ->column('indexe_irl')->boolean()->null()->default(false)->graphqlField()
            ->column('nombre_garages')->float(4)->null()->graphqlField()
            ->column('loyer_mensuel_garages')->integer()->null()->graphqlField()
            ->column('nombre_commerces')->float(4)->null()->graphqlField()
            ->column('loyer_mensuel_commerces')->float(4)->null()->graphqlField()
            ->column('anne_agrement')->float(4)->null()->graphqlField()
            ->column('date_ordre_service')->datetimeImmutable()->null()->graphqlField()
            ->column('date_mise_service')->datetimeImmutable()->null()->graphqlField()
            ->column('acquisition_fin')->boolean()->null()->graphqlField()
            ->column('duree_travaux')->integer()->null()->graphqlField()
            ->column('duree_chantier')->smallInt()->null()->graphqlField()
            ->column('tfpb_logt')->integer()->null()->graphqlField()
            ->column('tfpb_duree')->integer()->null()->graphqlField();


        $table->column('indexation_icc')->boolean()->null()->default(false)->graphqlField()
            ->column('modele_damortissement_id')->references('modele_damortissement')->null()->graphqlField()
            ->column('prix_foncier')->integer()->null()->graphqlField()
            ->column('prix_revient')->integer()->null()->graphqlField()
            ->column('fonds_propres')->float(4)->null()->graphqlField()
            ->column('subventions_etat')->float(4)->null()->graphqlField()
            ->column('subventions_anru')->float(4)->null()->graphqlField()
            ->column('subventions_epci')->float(4)->null()->graphqlField()
            ->column('subventions_departement')->float(4)->null()->graphqlField()
            ->column('subventions_region')->float(4)->null()->graphqlField()
            ->column('subventions_collecteur')->float(4)->null()->graphqlField()
            ->column('subventions_autres')->float(4)->null()->graphqlField()
            ->column('total')->float(4)->graphqlField()
            ->column('reste_financer')->float(4)->graphqlField()
            ->column('moyen_operation')->integer()->null()->graphqlField()
            ->column('moyen_foncier')->integer()->null()->graphqlField()
            //Financement
            ->column('depot_garantie')->float(4)->null()->graphqlField()
            ->column('tfpb_taux_evolution')->float(4)->null()->graphqlField()
            ->column('maintenance_taux_evolution')->float(4)->null()->graphqlField()
            ->column('gros_taux_evolution')->float(4)->null()->graphqlField()
            ->column('type')->smallInt()->notNull()->graphqlField()
            ->column('simulation_id')->references('simulations')->notNull()->graphqlField();

        $table->unique(['n_operation', 'simulation_id', 'type']);

        $db->table('type_emprunt_operation')
            ->column('types_emprunts_id')->references('types_emprunts')->graphqlField()
            ->column('operation_id')->references('operation')->graphqlField()->endGraphql()
            ->then()->primaryKey(['types_emprunts_id', 'operation_id'])
            ->column('montant')->float(4)->null()->graphqlField()
            ->column('date_premiere')->datetimeImmutable()->null()->graphqlField();

        $db->table('operation_periodique')
            ->column('operation_id')->references('operation')->graphqlField()
            ->column('iteration')->integer()->notNull()->graphqlField()->endGraphql()
            ->then()->primaryKey(['operation_id', 'iteration'])
            ->column('taux_evolution_loyer')->float(4)->null()->default(null)->graphqlField()
            ->column('complement_loyer')->float(4)->null()->default(null)->graphqlField()
            ->column('complement_annuite_capital')->float(4)->null()->default(null)->graphqlField()
            ->column('complement_annuite_interet')->float(4)->null()->default(null)->graphqlField()
            ->column('taux_vacance')->float(4)->null()->default(null)->graphqlField()
            ->column('tfpb')->float(4)->null()->default(null)->graphqlField()
            ->column('maintenance_courante')->float(4)->null()->default(null)->graphqlField()
            ->column('gros_entretien')->float(4)->null()->default(null)->graphqlField()
            ->column('nombre_agrement')->float(4)->null()->default(null)->graphqlField()
            ->column('nombre_logement')->float(4)->null()->default(null)->graphqlField()
            ->column('depot_garantie')->float(4)->null()->default(null)->graphqlField()
            ->column('nb_garages')->float(4)->null()->default(null)->graphqlField();
    }

    public function down(Schema $schema) : void
    {
    }
}
