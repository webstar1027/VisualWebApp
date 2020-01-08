<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use TheCodingMachine\FluidSchema\FluidSchemaException;
use TheCodingMachine\FluidSchema\TdbmFluidSchema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190704153909 extends AbstractMigration
{
    /**
     * @param Schema $schema
     * @throws FluidSchemaException
     */
    public function up(Schema $schema) : void
    {
        $db = new TdbmFluidSchema($schema);
        $table = $db->table('profils_evolution_loyers');
        $table
            ->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('numero')->string(255)->graphqlField()
            ->column('nom')->string(255)->default(null)->graphqlField()
            ->column('appliquer_irl')->boolean()->notNull()->default(false)->graphqlField()
            ->column('simulation_id')->references('simulations')->notNull()->graphqlField();

        $table->unique(['numero', 'simulation_id']);

        $db->table('profils_evolution_loyers_periodique')
            ->column('profil_evolution_loyer_id')->references('profils_evolution_loyers')->graphqlField()
            ->column('iteration')->integer()->notNull()->graphqlField()->endGraphql()
            ->then()->primaryKey(['profil_evolution_loyer_id', 'iteration'])
            ->column('s1')->float(4)->null()->default(null)->graphqlField()
            ->column('s2')->float(4)->null()->default(null)->graphqlField();


        $db->table('profils_evolution_loyers_parametre')
            ->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('simulation_id')->references('simulations')->graphqlField()
            ->column('plafonnement_des_loyers_pratiques_au_loyer_plafond')->boolean()->graphqlField();
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
