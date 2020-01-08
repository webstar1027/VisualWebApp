<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use TheCodingMachine\FluidSchema\TdbmFluidSchema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191003134830 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $db = new TdbmFluidSchema($schema);

        $table = $db->table('psla');
        $table->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('simulation_id')->references('simulations')->graphqlField()
            ->column('numero')->integer()->notNull()->graphqlField()
            ->column('nom')->string(255)->notNull()->graphqlField()
            ->column('direct_sci')->string(20)->null()->graphqlField()
            ->column('detention')->float(4)->null()->graphqlField()
            ->column('operation_stock')->boolean()->notNull()->default(false)->graphqlField()
            ->column('nombre_logements')->integer()->null()->graphqlField()
            ->column('prix_vente')->float(4)->null()->graphqlField()
            ->column('taux_brute')->float(4)->null()->graphqlField()
            ->column('duree_construction')->integer()->null()->graphqlField()
            ->column('date_livraison')->datetimeImmutable()->null()->graphqlField()
            ->column('loyer_mensuel')->float(4)->null()->graphqlField()
            ->column('taux_evolution')->float(4)->null()->graphqlField()
            ->column('duree_phase')->integer()->null()->graphqlField()
            ->column('montant_subventions')->float(4)->null()->graphqlField()
            ->column('montant_emprunts')->float(4)->null()->graphqlField()
            ->column('total')->float(4)->null()->graphqlField()
            ->column('type')->smallInt()->notNull()->graphqlField();

        $table->unique(['numero', 'simulation_id', 'type']);

        $db->table('type_emprunt_psla')
            ->column('types_emprunts_id')->references('types_emprunts')->graphqlField()
            ->column('psla_id')->references('psla')->graphqlField()->endGraphql()
            ->then()->primaryKey(['types_emprunts_id', 'psla_id'])
            ->column('montant')->float(4)->null()->graphqlField()
            ->column('date_premiere')->datetimeImmutable()->null()->graphqlField();

        $db->table('psla_periodique')
            ->column('psla_id')->references('psla')->graphqlField()
            ->column('iteration')->integer()->notNull()->graphqlField()->endGraphql()
            ->then()->primaryKey(['psla_id', 'iteration'])
            ->column('contrats_accession')->float(4)->null()->default(null)->graphqlField()
            ->column('levees_option')->float(4)->null()->default(null)->graphqlField()
            ->column('couts_internes')->float(4)->null()->default(null)->graphqlField()
            ->column('portage_fp')->float(4)->null()->default(null)->graphqlField()
            ->column('nombre_logements')->float(4)->null()->default(null)->graphqlField();

    }

    public function down(Schema $schema) : void
    {
    }
}
