<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use TheCodingMachine\FluidSchema\TdbmFluidSchema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190919154323 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $db = new TdbmFluidSchema($schema);

        $table = $db->table('vefa');
        $table->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('simulation_id')->references('simulations')->graphqlField()
            ->column('numero')->integer()->notNull()->unique()->graphqlField()
            ->column('nom_operation')->string(255)->null()->graphqlField()
            ->column('nom_categorie')->string(255)->null()->graphqlField()
            ->column('direct_sci')->string(255)->null()->graphqlField()
            ->column('pourcentage_detention')->float(4)->null()->graphqlField()
            ->column('nombre_logement')->integer()->null()->graphqlField()
            ->column('prix_vente')->float()->null()->graphqlField()
            ->column('taux_marge_brute')->float(4)->null()->graphqlField()
            ->column('taux_evolution')->float(4)->null()->graphqlField()
            ->column('duree_periode_construction')->integer()->null()->graphqlField()
            ->column('type')->string(30)->notNull()->graphqlField();

        $db->table('vefa_periodique')
            ->column('vefa_id')->references('vefa')->graphqlField()
            ->column('iteration')->integer()->notNull()->graphqlField()->endGraphql()
            ->then()->primaryKey(['vefa_id', 'iteration'])
            ->column('couts_internes')->float(4)->null()->default(null)->graphqlField()
            ->column('portage_fp')->float(4)->null()->default(null)->graphqlField()
            ->column('nombre_logements')->float(4)->null()->default(null)->graphqlField();
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
