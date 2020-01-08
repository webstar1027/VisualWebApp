<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use TheCodingMachine\FluidSchema\TdbmFluidSchema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190923171557 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $db = new TdbmFluidSchema($schema);

        $table = $db->table('cessions_foyers');

        $table->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('n_groupe')->integer()->notNull()->graphqlField()
            ->column('nom_intervention')->string(255)->notNull()->graphqlField()
            ->column('nature')->string(255)->null()->graphqlField()
            ->column('indexer_inflation')->boolean()->null()->default(false)->graphqlField()
            ->column('nombre_logements')->integer()->null()->default(null)->graphqlField()
            ->column('date_cession')->datetimeImmutable()->notNull()->graphqlField()
            ->column('prix_net_cession')->float(4)->notNull()->graphqlField()
            ->column('valeur_nette_comptable')->float(4)->notNull()->graphqlField()
            ->column('remboursement_anticipe')->float(4)->null()->graphqlField()
            ->column('taux_evolution_tfpb')->float(4)->null()->graphqlField()
            ->column('taux_evolution_maintenance')->float(4)->null()->graphqlField()
            ->column('taux_evolution_gros_entretien')->float(4)->null()->graphqlField()
            ->column('reduction_amortissement_annuelle')->float(4)->null()->graphqlField()
            ->column('reduction_reprise_annuelle')->float(4)->null()->graphqlField()
            ->column('duree_residuelle')->float(4)->null()->graphqlField()
            ->column('simulation_id')->references('simulations')->notNull()->graphqlField();

        $table->unique(['simulation_id', 'n_groupe'])
            ->unique(['simulation_id', 'nom_intervention']);

        $db->table('cessions_foyers_periodique')
            ->column('cessions_foyers_id')->references('cessions_foyers')->graphqlField()
            ->column('iteration')->integer()->notNull()->graphqlField()->endGraphql()
            ->then()->primaryKey(['cessions_foyers_id', 'iteration'])
            ->column('redevance')->float(4)->null()->default(null)->graphqlField()
            ->column('part_capital')->float(4)->null()->default(null)->graphqlField()
            ->column('part_interets')->float(4)->null()->default(null)->graphqlField()
            ->column('tfpb')->float(4)->null()->default(null)->graphqlField()
            ->column('maintenance_courante')->float(4)->null()->default(null)->graphqlField()
            ->column('gros_entretien')->float(4)->null()->default(null)->graphqlField();
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
