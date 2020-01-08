<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use TheCodingMachine\FluidSchema\TdbmFluidSchema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190826100447 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $db = new TdbmFluidSchema($schema);

        $table = $db->table('patrimoine_foyers');

        $table->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('n_groupe')->integer()->notNull()->graphqlField()
            ->column('n_sous_groupe')->integer()->notNull()->graphqlField()
            ->column('nom_groupe')->string(255)->notNull()->graphqlField()
            ->column('informations')->text()->null()->graphqlField()
            ->column('nombre_logements')->float(4)->notNull()->graphqlField()
            ->column('secteur_financier')->string(255)->null()->graphqlField()
            ->column('nature_operation')->string(255)->null()->graphqlField()
            ->column('taux_evolution_redevances')->float()->null()->default(0)->graphqlField()
            ->column('simulation_id')->references('simulations')->notNull()->graphqlField();

        $table->unique(['simulation_id', 'n_groupe', 'n_sous_groupe']);

        $db->table('patrimoine_foyers_periodique')
            ->column('patrimoine_foyers_id')->references('patrimoine_foyers')->graphqlField()
            ->column('iteration')->integer()->notNull()->graphqlField()->endGraphql()
            ->then()->primaryKey(['patrimoine_foyers_id', 'iteration'])
            ->column('value')->float(4)->null()->default(null)->graphqlField();

        $db->table('patrimoine_foyers_parametres')
            ->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('nombre_pondere_logement')->integer()->null()->default(null)->graphqlField()
            ->column('simulation_id')->references('simulations')->notNull()->graphqlField();
        $db->table('patrimoine_foyers_parametres')->unique(['simulation_id']);
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
