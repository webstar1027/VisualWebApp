<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use TheCodingMachine\FluidSchema\TdbmFluidSchema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190721142638 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $db = new TdbmFluidSchema($schema);

        $table = $db->table('autres_charges');

        $table->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('numero')->integer()->graphqlField()
            ->column('libelle')->string(255)->notNull()->graphqlField()
            ->column('taux_devolution')->float(4)->null()->default(0)->graphqlField()
            ->column('indexation')->boolean()->notNull()->default(true)->graphqlField()
            ->column('nature')->string(255)->notNull()->graphqlField()
            ->column('simulation_id')->references('simulations')->notNull()->graphqlField();

        $table->unique(['numero', 'simulation_id']);

        $table = $db->table('autres_charges_periodique');
        $table
            ->column('autres_charges_id')->references('autres_charges')->graphqlField()
            ->column('iteration')->integer()->notNull()->graphqlField()->endGraphql()
            ->then()->primaryKey(['autres_charges_id', 'iteration'])
            ->column('value')->float(4)->null()->default(null)->graphqlField();
    }

    public function down(Schema $schema) : void
    {
    }
}
