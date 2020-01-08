<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use TheCodingMachine\FluidSchema\TdbmFluidSchema;


/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190725130050 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $db = new TdbmFluidSchema($schema);

        $table = $db->table('maintenance');
        $table
            ->uuid()->graphqlField()
            ->column('numero')->integer()->notNull()->graphqlField()
            ->column('nom')->string(255)->notNull()->graphqlField()
            ->column('regie')->boolean()->notNull()->default(false)->graphqlField()
            ->column('taux_devolution')->float(4)->null()->default(0)->graphqlField()
            ->column('indexation')->boolean()->notNull()->default(true)->graphqlField()
            ->column('nature')->smallInt()->null()->graphqlField()
            ->column('type')->smallInt()->notNull()->default(0)->graphqlField()
            ->column('simulation_id')->references('simulations')->notNull()->graphqlField();

        $table->unique(['numero', 'simulation_id']);

        $db->table('maintenance_periodique')
            ->column('maintenance_id')->references('maintenance')->graphqlField()
            ->column('iteration')->integer()->notNull()->graphqlField()->endGraphql()
            ->then()->primaryKey(['maintenance_id', 'iteration'])
            ->column('value')->float(4)->null()->default(null)->graphqlField();


    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
