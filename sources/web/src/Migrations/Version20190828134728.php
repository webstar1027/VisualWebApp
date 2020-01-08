<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use TheCodingMachine\FluidSchema\TdbmFluidSchema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190828134728 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $db = new TdbmFluidSchema($schema);

        $db->table('cglls_ancols')
            ->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('simulation_id')->references('simulations')->graphqlField()
            ->column('type')->string(255)->notNull()->graphqlField();

        $db->table('cglls_ancols_periodique')
            ->column('cglls_ancols_id')->references('cglls_ancols')->graphqlField()
            ->column('iteration')->integer()->notNull()->graphqlField()->endGraphql()
            ->then()->primaryKey(['cglls_ancols_id', 'iteration'])
            ->column('valeur')->float(4)->null()->default(null)->graphqlField();

        $db->table('cglls_ancols_parametre')
            ->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('simulation_id')->references('simulations')->graphqlField()
            ->column('calcul_automatique')->integer()->null()->default(null)->graphqlField()->endGraphql()
            ->column('lissage_net')->float(4)->null()->default(null)->graphqlField();
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
