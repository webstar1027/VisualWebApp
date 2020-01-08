<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use TheCodingMachine\FluidSchema\TdbmFluidSchema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190822131931 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $db = new TdbmFluidSchema($schema);

        $table = $db->table('vacance');

        $table->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('numero_groupe')->integer()->notNull()->graphqlField()
            ->column('numero_sous_groupe')->integer()->notNull()->graphqlField()
            ->column('nom_groupe')->string(255)->notNull()->graphqlField()
            ->column('information')->string(255)->notNull()->graphqlField()
            ->column('simulation_id')->references('simulations')->notNull()->graphqlField();

        $table->unique(['Numero_groupe', 'simulation_id']);

        $db->table('vacance_periodique')
            ->column('vacance_id')->references('vacance')->graphqlField()
            ->column('iteration')->integer()->notNull()->graphqlField()->endGraphql()
            ->then()->primaryKey(['vacance_id', 'iteration'])
            ->column('montant')->float(4)->null()->default(null)->graphqlField();

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
