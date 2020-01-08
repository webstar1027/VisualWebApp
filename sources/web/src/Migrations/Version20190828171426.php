<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use TheCodingMachine\FluidSchema\TdbmFluidSchema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190828171426 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $db = new TdbmFluidSchema($schema);
        $table = $db->table('codifications');
        $table
            ->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('numero')->integer()->notNull()->graphqlField()
            ->column('activite')->string(255)->notNull()->graphqlField()
            ->column('simulation_id')->references('simulations')->notNull()->graphqlField();

        $table->unique(['numero', 'simulation_id']);
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
