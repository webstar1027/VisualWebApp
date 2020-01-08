<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use TheCodingMachine\FluidSchema\TdbmFluidSchema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190805190242 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $db = new TdbmFluidSchema($schema);

        $db->table('loyer')
            ->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('nom')->string(255)->null()->graphqlField()
            ->column('taux_devolution')->float(4)->null()->default(0)->graphqlField()
            ->column('nombre_logements')->float(4)->null()->default(null)->graphqlField()
            ->column('montant_rls')->float(4)->null()->default(null)->graphqlField()
            ->column('type')->smallInt()->notNull()->graphqlField()
            ->column('deletable')->boolean()->notNull()->default(true)->graphqlField()
            ->column('simulation_id')->references('simulations')->notNull()->graphqlField();

        $db->table('loyer_periodique')
            ->column('loyer_id')->references('loyer')->graphqlField()
            ->column('iteration')->integer()->notNull()->graphqlField()->endGraphql()
            ->then()->primaryKey(['loyer_id', 'iteration'])
            ->column('value')->float(4)->null()->default(null)->graphqlField();

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
