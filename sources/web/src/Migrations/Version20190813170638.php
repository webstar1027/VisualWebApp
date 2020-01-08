<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use TheCodingMachine\FluidSchema\TdbmFluidSchema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190813170638 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $db = new TdbmFluidSchema($schema);
        $table = $db->table('modele_damortissement');
        $table
            ->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('numero')->string(255)->notNull()->graphqlField()
            ->column('nom')->string(255)->notNull()->graphqlField()
            ->column('duree_reprise')->float(4)->notNull()->graphqlField()
            //ventilation
            ->column('structure_ventilation')->float(4)->notNull()->default(0.0)->graphqlField()
            ->column('menuiserie_ventilation')->float(4)->notNull()->default(0.0)->graphqlField()
            ->column('chauffage_ventilation')->float(4)->notNull()->default(0.0)->graphqlField()
            ->column('etancheite_ventilation')->float(4)->notNull()->default(0.0)->graphqlField()
            ->column('ravalement_ventilation')->float(4)->notNull()->default(0.0)->graphqlField()
            ->column('electricite_ventilation')->float(4)->notNull()->default(0.0)->graphqlField()
            ->column('plomberie_ventilation')->float(4)->notNull()->default(0.0)->graphqlField()
            ->column('ascenseurs_ventilation')->float(4)->notNull()->default(0.0)->graphqlField()
            //amortissement
            ->column('structure_amortissement')->float(4)->notNull()->default(0.0)->graphqlField()
            ->column('menuiserie_amortissement')->float(4)->notNull()->default(0.0)->graphqlField()
            ->column('chauffage_amortissement')->float(4)->notNull()->default(0.0)->graphqlField()
            ->column('etancheite_amortissement')->float(4)->notNull()->default(0.0)->graphqlField()
            ->column('ravalement_amortissement')->float(4)->notNull()->default(0.0)->graphqlField()
            ->column('electricite_amortissement')->float(4)->notNull()->default(0.0)->graphqlField()
            ->column('plomberie_amortissement')->float(4)->notNull()->default(0.0)->graphqlField()
            ->column('ascenseurs_amortissement')->float(4)->notNull()->default(0.0)->graphqlField();
            $table->column('simulation_id')->references('simulations')->notNull()->graphqlField();

        $table->unique(['numero', 'simulation_id']);
        $table->unique(['nom', 'simulation_id']);

    }

    public function down(Schema $schema) : void
    {
    }
}
