<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use TheCodingMachine\FluidSchema\TdbmFluidSchema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190812161356 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $db = new TdbmFluidSchema($schema);

        $table = $db->table('resultat_comptable');

        $table->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('libelle')->string(255)->notNull()->graphqlField()
            ->column('deletable')->boolean()->notNull()->default(true)->graphqlField()
            ->column('simulation_id')->references('simulations')->notNull()->graphqlField();

        $db->table('resultat_comptable_periodique')
            ->column('resultat_comptable_id')->references('resultat_comptable')->graphqlField()
            ->column('iteration')->integer()->notNull()->graphqlField()->endGraphql()
            ->then()->primaryKey(['resultat_comptable_id', 'iteration'])
            ->column('value')->float(4)->null()->default(null)->graphqlField();

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
