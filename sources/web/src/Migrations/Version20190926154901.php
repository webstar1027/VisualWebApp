<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use TheCodingMachine\FluidSchema\TdbmFluidSchema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190926154901 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $db = new TdbmFluidSchema($schema);

        $table = $db->table('demolition_foyers');

        $table->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('simulation_id')->references('simulations')->graphqlField()
            ->column('numero')->integer()->notNull()->graphqlField()
            ->column('nom_intervention')->string(255)->notNull()->graphqlField()
            ->column('nombre_logements')->integer()->null()->default(null)->graphqlField()
            ->column('date')->datetimeImmutable()->notNull()->graphqlField()
            ->column('remboursement_anticipe')->float(4)->null()->graphqlField()
            ->column('indexation_icc')->boolean()->null()->default(false)->graphqlField()
            ->column('prix_revient')->float(4)->graphqlField()
            ->column('fonds_propres')->float(4)->graphqlField()
            ->column('subventions_etat')->float(4)->null()->graphqlField()
            ->column('subventions_anru')->float(4)->null()->graphqlField()
            ->column('subventions_epci')->float(4)->null()->graphqlField()
            ->column('subventions_departement')->float(4)->null()->graphqlField()
            ->column('subventions_region')->float(4)->null()->graphqlField()
            ->column('subventions_collecteur')->float(4)->null()->graphqlField()
            ->column('autres_subventions')->float(4)->null()->graphqlField()
            ->column('taux_evolution_tfpb')->float(4)->null()->graphqlField()
            ->column('taux_evolution_maintenance')->float(4)->null()->graphqlField()
            ->column('taux_evolution_gros_entretien')->float(4)->null()->graphqlField()
            ->column('total_emprutns')->float(4)->graphqlField();

        $table->unique(['simulation_id', 'numero'])
            ->unique(['simulation_id', 'nom_intervention']);

        $db->table('demolition_foyers_periodique')
            ->column('demolition_foyers_id')->references('demolition_foyers')->graphqlField()
            ->column('iteration')->integer()->notNull()->graphqlField()->endGraphql()
            ->then()->primaryKey(['demolition_foyers_id', 'iteration'])
            ->column('redevances')->float(4)->null()->default(null)->graphqlField()
            ->column('part_capital')->float(4)->null()->default(null)->graphqlField()
            ->column('part_interets')->float(4)->null()->default(null)->graphqlField()
            ->column('tfpb')->float(4)->null()->default(null)->graphqlField()
            ->column('maintenance_courante')->float(4)->null()->default(null)->graphqlField()
            ->column('gros_entretien')->float(4)->null()->default(null)->graphqlField();

        $db->table('type_emprunt_demolition_foyers')
            ->column('types_emprunts_id')->references('types_emprunts')->graphqlField()
            ->column('demolition_foyers_id')->references('demolition_foyers')->graphqlField()->endGraphql()
            ->then()->primaryKey(['types_emprunts_id', 'demolition_foyers_id'])
            ->column('montant')->float(4)->null()->graphqlField()
            ->column('date_premiere')->datetimeImmutable()->null()->graphqlField();
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
