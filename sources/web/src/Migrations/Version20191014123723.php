<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use TheCodingMachine\FluidSchema\TdbmFluidSchema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191014123723 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs

        $db = new TdbmFluidSchema($schema);

        //--------------------------- foyers ---------------------------//
        $db->table('foyers_frais_structures')
            ->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('number')->integer()->autoIncrement()->unique()->graphqlField()
            ->column('libelle')->string(255)->notNull()->graphqlField()
            ->column('taux_devolution')->float(4)->null()->default(0)->graphqlField()
            ->column('indexation')->boolean()->notNull()->default(true)->graphqlField()
            ->column('type')->smallInt()->notNull()->graphqlField()
            ->column('simulation_id')->references('simulations')->notNull()->graphqlField();

        $table = $db->table('foyers_frais_structures_periodique');
        $table
            ->column('frais_structures_id')->references('foyers_frais_structures')->graphqlField()
            ->column('iteration')->integer()->notNull()->graphqlField()->endGraphql()
            ->then()->primaryKey(['frais_structures_id', 'iteration'])
            ->column('value')->float(4)->null()->default(null)->graphqlField();

        //--------------------------- foyers ---------------------------//

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
