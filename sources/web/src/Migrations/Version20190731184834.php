<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use TheCodingMachine\FluidSchema\TdbmFluidSchema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190731184834 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $db = new TdbmFluidSchema($schema);

        $table = $db->table('annuite');

        $table->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('numero')->string(255)->graphqlField()
            ->column('capital_restant_patrimoine')->float(4)->null()->default(null)->graphqlField()
            ->column('prise_icne_acne')->boolean()->null()->graphqlField()
            ->column('libelle')->string(255)->null()->graphqlField()
            ->column('nature')->smallInt()->null()->graphqlField()
            ->column('type')->smallInt()->notNull()->graphqlField()
            ->column('deletable')->boolean()->notNull()->default(true)->graphqlField()
            ->column('simulation_id')->references('simulations')->notNull()->graphqlField();

        $table->unique(['numero', 'simulation_id']);

        $db->table('annuite_periodique')
            ->column('annuite_id')->references('annuite')->graphqlField()
            ->column('iteration')->integer()->notNull()->graphqlField()->endGraphql()
            ->then()->primaryKey(['annuite_id', 'iteration'])
            ->column('value')->float(4)->null()->default(null)->graphqlField();
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
