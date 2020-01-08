<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use TheCodingMachine\FluidSchema\TdbmFluidSchema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190826161452 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $db = new TdbmFluidSchema($schema);
        $table = $db->table('travaux_immobilises');
        $table
            ->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('n_groupe')->string(64)->graphqlField()
            ->column('nom_categorie')->string(255)->null()->graphqlField()
            ->column('conventionAnru')->boolean()->null()->graphqlField()
            ->column('indexationIcc')->boolean()->null()->graphqlField()
            ->column('modele_damortissement_id')->references('modele_damortissement')->null()->graphqlField()
            ->column('annee_premiere_echeance')->string(255)->null()->graphqlField()
            ->column('type')->smallInt()->notNull()->graphqlField()

            //Financement
            ->column('founds_propres')->float(4)->null()->graphqlField()
            ->column('subventions_anru')->float(4)->null()->graphqlField()
            ->column('subventions_etat')->float(4)->null()->graphqlField()
            ->column('subventions_epci')->float(4)->null()->graphqlField()
            ->column('subventions_departement')->float(4)->null()->graphqlField()
            ->column('subventions_region')->float(4)->null()->graphqlField()
            ->column('subventions_collecteur')->float(4)->null()->graphqlField()
            ->column('autres_subventions')->float(4)->null()->graphqlField()

            // Travaux identifiée
            ->column('n_sous_groupe')->integer()->null()->graphqlField()
            ->column('nom_groupe')->string(255)->null()->graphqlField()
            ->column('information')->string(255)->null()->graphqlField()
            ->column('profil_evolution_loyer_id')->references('profils_evolution_loyers')->null()->graphqlField()
            ->column('loyer_mensuel_initial')->float(4)->null()->graphqlField()
            ->column('numero_tranche')->integer()->null()->graphqlField()
            ->column('nom_tranche')->string(255)->null()->graphqlField()
            ->column('surface_traitee')->float(4)->null()->graphqlField()
            ->column('variation_surface_quittance')->float(4)->null()->graphqlField()
            ->column('nombre_logement')->float(4)->null()->graphqlField()
            ->column('variation_nombre_logement')->float(4)->null()->graphqlField()
            ->column('annee_agreement')->float(4)->null()->graphqlField()
            ->column('date_ordre_service')->datetimeImmutable()->null()->graphqlField()
            ->column('date_fin_travaux')->datetimeImmutable()->null()->graphqlField()
            ->column('taux_variation_loyer')->float(4)->null()->graphqlField()
            ->column('date_application')->datetimeImmutable()->null()->graphqlField()
            ->column('prix_revient')->float(4)->null()->graphqlField()

            // Travaux non identifiée
            ->column('logement_conventionnes')->boolean()->null()->graphqlField()
            ->column('surface_moyenne')->float(4)->null()->graphqlField()
            ->column('loyer_mensuel_moyen')->float(4)->null()->graphqlField()
            ->column('variation_loyer')->float(4)->null()->graphqlField()
            ->column('annee_application')->smallInt()->null()->graphqlField()
            ->column('duree_chantier')->smallInt()->null()->graphqlField()
            ->column('montant_travaux')->float(4)->null()->graphqlField()

            ->column('simulation_id')->references('simulations')->notNull()->graphqlField();

        $table->unique(['simulation_id', 'n_groupe', 'type']);

        $db->table('travaux_immobilises_periodique')
            ->column('travaux_immobilises_id')->references('travaux_immobilises')->graphqlField()
            ->column('iteration')->integer()->notNull()->graphqlField()->endGraphql()
            ->then()->primaryKey(['travaux_immobilises_id', 'iteration'])
            ->column('montant')->float(4)->null()->default(null)->graphqlField()
            ->column('nombre_agrement')->float(4)->null()->default(null)->graphqlField()
            ->column('logement')->float(4)->null()->default(null)->graphqlField();

        $db->table('type_emprunt_travaux_immobilises')
            ->column('types_emprunts_id')->references('types_emprunts')->graphqlField()
            ->column('travaux_immobilises_id')->references('travaux_immobilises')->graphqlField()->endGraphql()
            ->then()->primaryKey(['types_emprunts_id', 'travaux_immobilises_id'])
            ->column('quotite_emprunt')->float(4)->null()->graphqlField()
            ->column('montant')->float(4)->null()->graphqlField()
            ->column('date_premiere')->datetimeImmutable()->null()->graphqlField();

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
