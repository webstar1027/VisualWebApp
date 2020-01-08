<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use TheCodingMachine\FluidSchema\TdbmFluidSchema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191014123047 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $db = new TdbmFluidSchema($schema);

        //--------------------------- accession ---------------------------//
        $db->table('accession_frais_structures')
            ->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('number')->integer()->autoIncrement()->unique()->graphqlField()
            ->column('libelle')->string(255)->notNull()->graphqlField()
            ->column('taux_devolution')->float(4)->null()->default(0)->graphqlField()
            ->column('indexation')->boolean()->notNull()->default(true)->graphqlField()
            ->column('type')->smallInt()->notNull()->graphqlField()
            ->column('simulation_id')->references('simulations')->notNull()->graphqlField();

        $table = $db->table('accession_frais_structures_periodique');
        $table
            ->column('frais_structures_id')->references('accession_frais_structures')->graphqlField()
            ->column('iteration')->integer()->notNull()->graphqlField()->endGraphql()
            ->then()->primaryKey(['frais_structures_id', 'iteration'])
            ->column('value')->float(4)->null()->default(null)->graphqlField();


        $table = $db->table('accession_codifications');
        $table
            ->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('numero')->integer()->autoIncrement()->unique()->graphqlField()
            ->column('activite')->string(255)->notNull()->graphqlField()
            ->column('simulation_id')->references('simulations')->notNull()->graphqlField();

        $table->unique(['numero', 'simulation_id']);

        $produitsTable = $db->table('accession_produits_charges');
        $produitsTable
            ->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('number')->integer()->autoIncrement()->unique()->graphqlField()
            ->column('libelle')->string(255)->notNull()->graphqlField()
            ->column('taux_devolution')->float(4)->null()->default(0)->graphqlField()
            ->column('indexation')->boolean()->notNull()->default(true)->graphqlField()
            ->column('type')->smallInt()->notNull()->graphqlField()
            ->column('codification_id')->references('accession_codifications')->notNull()->graphqlField()
            ->column('simulation_id')->references('simulations')->notNull()->graphqlField();

        $produitsTable->unique(['libelle', 'simulation_id']);

        $table = $db->table('accession_produits_charges_periodique');
        $table
            ->column('produits_charges_id')->references('accession_produits_charges')->graphqlField()
            ->column('iteration')->integer()->notNull()->graphqlField()->endGraphql()
            ->then()->primaryKey(['produits_charges_id', 'iteration'])
            ->column('value')->float(4)->null()->default(null)->graphqlField();

        //--------------------------- accession ---------------------------//
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
