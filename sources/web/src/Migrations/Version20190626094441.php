<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use TheCodingMachine\FluidSchema\TdbmFluidSchema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190626094441 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $db = new TdbmFluidSchema($schema);
        $table = $db->table('types_emprunts');
        $table
            ->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('numero')->string(255)->graphqlField()
            ->column('nom')->string(255)->default(null)->graphqlField()
            ->column('livretA')->boolean()->notNull()->default(false)->graphqlField()
            ->column('marge_emprunt')->float(4)->notNull()->default(0)->graphqlField()
            ->column('taux_plancher_check')->boolean()->default(false)->graphqlField()
            ->column('taux_interet')->float(4)->notNull()->default(0)->graphqlField()
            ->column('duree_emprunt')->integer()->notNull()->default(0)->graphqlField()
            ->column('duree_amortissement')->integer()->notNull()->default(0)->graphqlField()
            ->column('taux_progressivite')->float(4)->notNull()->default(0)->graphqlField()
            ->column('revisability')->smallInt()->graphqlField()
            ->column('taux_plancher')->float(4)->notNull()->default(0)->graphqlField()
            ->column('simulation_id')->references('simulations')->null()->default(null)->graphqlField();

        $table->unique(['numero', 'simulation_id']);

        $db->table('types_emprunts_periodique')
            ->column('types_emprunts_id')->references('types_emprunts')->graphqlField()
            ->column('iteration')->integer()->notNull()->graphqlField()->endGraphql()
            ->then()->primaryKey(['types_emprunts_id', 'iteration'])
            ->column('taux_interet_initial')->float(4)->null()->default(null)->graphqlField()
            ->column('taux_premiere_annuite_payee')->float(4)->null()->default(null)->graphqlField();
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
