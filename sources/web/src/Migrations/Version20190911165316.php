<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use TheCodingMachine\FluidSchema\TdbmFluidSchema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190911165316 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $db = new TdbmFluidSchema($schema);

        $table = $db->table('cession');

        $table->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('n_groupe')->integer()->null()->graphqlField()
            ->column('n_sous_groupe')->integer()->null()->graphqlField()
            ->column('informations')->string(255)->null()->graphqlField()
            ->column('nom_groupe')->string(255)->null()->graphqlField()
            ->column('nature')->string(255)->null()->graphqlField()
            ->column('indexer_inflation')->boolean()->null()->default(true)->graphqlField()
            ->column('reduction_tfpb')->float(4)->null()->graphqlField()
            ->column('reduction_ge ')->float(4)->null()->graphqlField()
            ->column('reduction_maintenance')->float(4)->null()->graphqlField()
            ->column('cession_fin_mois')->integer()->null()->graphqlField()
            ->column('reduction_amortissement_annuelle')->integer()->null()->graphqlField()
            ->column('reduction_reprise_annuelle')->float(4)->null()->graphqlField()
            ->column('duree_residuelle')->float(4)->null()->graphqlField()
            ->column('numero')->integer()->null()->graphqlField()
            ->column('nom_category')->string(255)->null()->graphqlField()
            ->column('loyer_mensuel')->float(4)->null()->graphqlField()
            ->column('nombre_residuelle')->float(4)->null()->graphqlField()
            ->column('valeur_comptable')->float(4)->null()->graphqlField()
            ->column('type')->smallInt()->notNull()->graphqlField()
            ->column('simulation_id')->references('simulations')->notNull()->graphqlField();

        $db->table('cession_periodique')
            ->column('cession_id')->references('cession')->graphqlField()
            ->column('iteration')->integer()->notNull()->graphqlField()->endGraphql()
            ->then()->primaryKey(['cession_id', 'iteration'])
            ->column('mois_cession')->float(4)->null()->default(null)->graphqlField()
            ->column('nombre_logements')->float(4)->null()->default(null)->graphqlField()
            ->column('prix_nets_cession')->float(4)->null()->default(null)->graphqlField()
            ->column('remboursement_anticipe')->float(4)->null()->default(null)->graphqlField()
            ->column('ecomonies_capital')->float(4)->null()->default(null)->graphqlField()
            ->column('ecomonies_interets')->float(4)->null()->default(null)->graphqlField()
            ->column('valeur_cede')->float(4)->null()->default(null)->graphqlField()
        ;

    }

    public function down(Schema $schema) : void
    {
    }
}
