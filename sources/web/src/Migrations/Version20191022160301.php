<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use TheCodingMachine\FluidSchema\TdbmFluidSchema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191022160301 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $db = new TdbmFluidSchema($schema);

        $table = $db->table('tableau_de_bord');
        $table
            ->uuid()->graphqlField()
            ->column('simulation_id')->references('simulations')->notNull()->graphqlField()
            ->column('begin_projection')->integer()->notNull()->graphqlField()
            ->column('end_projection')->integer()->notNull()->graphqlField();

        $param = $db->table('tableau_de_bord_parametre');
        $param->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('composant')->string(255)->notNull()->graphqlField()
            ->column('type')->string(255)->notNull()->graphqlField()
            ->column('position')->integer()->notNull()->graphqlField()
            ->column('tableau_de_bord_id')->references('tableau_de_bord')->graphqlField();

        $db->table('tableau_de_bord_periodique')
            ->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('tableau_de_bord_id')->references('tableau_de_bord')->graphqlField()
            ->column('tableau_de_bord_param_id')->references('tableau_de_bord_parametre')->graphqlField()
            ->column('iteration')->string(255)->notNull()->graphqlField()->endGraphql()
            ->column('value')->float(4)->null()->default(null)->graphqlField();

    }

    public function down(Schema $schema) : void
    {
    }
}
