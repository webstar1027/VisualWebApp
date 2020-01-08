<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use TheCodingMachine\FluidSchema\TdbmFluidSchema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190811162359 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $db = new TdbmFluidSchema($schema);

        $table = $db->table('patrimoines');

        $table->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('n_groupe')->integer()->notNull()->graphqlField()
            ->column('n_sous_groupe')->integer()->notNull()->graphqlField()
            ->column('nom_groupe')->string(255)->notNull()->graphqlField()
            ->column('informations')->string(255)->null()->graphqlField()
            ->column('conventionne')->boolean()->notNull()->default(true)->graphqlField()
            ->column('surface_quittancee')->float(4)->null()->graphqlField()
            ->column('nombre_logements')->float(4)->notNull()->graphqlField()
            ->column('loyer_mensuel')->float(4)->notNull()->graphqlField()
            ->column('loyer_mensuel_plafond')->float(4)->null()->graphqlField()
            ->column('secteur_financier')->string(255)->null()->graphqlField()
            ->column('zone_geographique')->string(255)->null()->graphqlField()
            ->column('nature_operation')->string(255)->null()->graphqlField()
            ->column('type_habitat')->string(255)->null()->graphqlField()
            ->column('rehabilite')->boolean()->notNull()->default(false)->graphqlField()
            ->column('annee_mes')->float(4)->null()->graphqlField()
            ->column('profils_evolution_loyers_id')->references('profils_evolution_loyers')->null()->graphqlField()
            ->column('simulation_id')->references('simulations')->notNull()->graphqlField();

        $table->unique(['simulation_id', 'n_groupe', 'n_sous_groupe']);

        $paramsTable = $db->table('patrimoines_logements_parametres');

        $paramsTable->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('simulation_id')->references('simulations')->notNull()->graphqlField()
            ->column('nombre_pondere')->integer()->null()->graphqlField()
            ->column('montant_loyers')->float()->null()->graphqlField();

        $paramsTable->unique(['simulation_id']);
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
