<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use TheCodingMachine\FluidSchema\TdbmFluidSchema;
/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190829053044 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $db = new TdbmFluidSchema($schema);

        $db->table('produits_charges')
            ->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('number')->integer()->autoIncrement()->unique()->graphqlField()
            ->column('libelle')->string(255)->notNull()->graphqlField()
            ->column('taux_devolution')->float(4)->null()->default(0)->graphqlField()
            ->column('indexation')->boolean()->notNull()->default(true)->graphqlField()
            ->column('type')->smallInt()->notNull()->graphqlField()
            ->column('codification_id')->references('codifications')->notNull()->graphqlField()
            ->column('simulation_id')->references('simulations')->notNull()->graphqlField();

        $table = $db->table('produits_charges_periodique');
        $table
            ->column('produits_charges_id')->references('produits_charges')->graphqlField()
            ->column('iteration')->integer()->notNull()->graphqlField()->endGraphql()
            ->then()->primaryKey(['produits_charges_id', 'iteration'])
            ->column('value')->float(4)->null()->default(null)->graphqlField();


    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
