<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use TheCodingMachine\FluidSchema\TdbmFluidSchema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190912090917 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $db = new TdbmFluidSchema($schema);

        // List table (without iteration)
        $db->table('portage_tresorerie')
            ->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('simulation_id')->references('simulations')->graphqlField()
            ->column('nom')->string(255)->notNull()->graphqlField()
            ->column('type')->string(255)->notNull()->graphqlField()
            ->column('est_parametre')->boolean()->notNull()->default(false)->graphqlField();

        // Iteration table (N ----> N+50)
        $db->table('portage_tresorerie_periodique')
            ->column('portage_tresorerie_id')->references('portage_tresorerie')->graphqlField()
            ->column('iteration')->integer()->notNull()->graphqlField()->endGraphql()
            ->then()->primaryKey(['portage_tresorerie_id', 'iteration'])
            ->column('valeur')->float(4)->null()->default(null)->graphqlField();

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
