<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use TheCodingMachine\FluidSchema\TdbmFluidSchema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191008101337 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $db = new TdbmFluidSchema($schema);

        $table = $db->table('lotissement');
        $table->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('simulation_id')->references('simulations')->graphqlField()
            ->column('numero')->integer()->notNull()->graphqlField()
            ->column('nom')->string(255)->notNull()->graphqlField()
            ->column('nombre_lots')->integer()->null()->graphqlField()
            ->column('prix_vente')->float(4)->notNull()->graphqlField()
            ->column('prix_vente_lot')->float(4)->null()->graphqlField()
            ->column('taux_brute')->float(4)->null()->graphqlField()
            ->column('taux_evolution')->float(4)->null()->graphqlField()
            ->column('duree_construction')->integer()->null()->graphqlField()
            ->column('type')->smallInt()->notNull()->graphqlField();

        $table->unique(['numero', 'simulation_id', 'type']);

        $db->table('lotissement_periodique')
            ->column('lotissement_id')->references('lotissement')->graphqlField()
            ->column('iteration')->integer()->notNull()->graphqlField()->endGraphql()
            ->then()->primaryKey(['lotissement_id', 'iteration'])
            ->column('portage_propres')->float(4)->null()->default(null)->graphqlField()
            ->column('couts_internes')->float(4)->null()->default(null)->graphqlField()
            ->column('nombre_livres')->float(4)->null()->default(null)->graphqlField();

    }

    public function down(Schema $schema) : void
    {
    }
}
