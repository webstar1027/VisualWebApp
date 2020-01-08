<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use TheCodingMachine\FluidSchema\TdbmFluidSchema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190917134104 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $db = new TdbmFluidSchema($schema);

        $table = $db->table('ccmi');
        $table->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('simulation_id')->references('simulations')->graphqlField()
            ->column('numero')->integer()->notNull()->unique()->graphqlField()
            ->column('nom_operation')->string(255)->notNull()->graphqlField()
            ->column('prix_vente')->float()->null()->graphqlField()
            ->column('taux_marge_brute')->float(4)->null()->graphqlField()
            ->column('portage_fonds_propres')->float(4)->null()->graphqlField()
            ->column('couts_internes_stockes')->float(4)->null()->graphqlField();

        $db->table('ccmi_periodique')
            ->column('ccmi_id')->references('ccmi')->graphqlField()
            ->column('iteration')->integer()->notNull()->graphqlField()
            ->then()->primaryKey(['ccmi_id', 'iteration'])
            ->column('nombre_maisons_livrees')->float(4)->null()->graphqlField();
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
