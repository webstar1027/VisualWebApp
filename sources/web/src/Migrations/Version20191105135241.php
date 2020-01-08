<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use TheCodingMachine\FluidSchema\TdbmFluidSchema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191105135241 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $db = new TdbmFluidSchema($schema);

        $db->table('portage_tresorerie_parametre')
            ->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('solde_emplois')->float(4)->null()->graphqlField()
            ->column('dette_fournisseurs')->float(4)->null()->graphqlField()
            ->column('promotion_accession')->float(4)->null()->graphqlField()
            ->column('taux_interet_financement')->float(4)->null()->graphqlField()
            ->column('taux_interet_concours')->float(4)->null()->graphqlField()
            ->column('simulation_id')->references('simulations')->notNull()->graphqlField();

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
