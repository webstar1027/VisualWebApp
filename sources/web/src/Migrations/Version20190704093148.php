<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use TheCodingMachine\FluidSchema\TdbmFluidSchema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190704093148 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $db = new TdbmFluidSchema($schema);

        $db->table('hypotheses')
            ->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('mobilisation')->boolean()->notNull()->default(false)->graphqlField()
            ->column('maintenance')->float(4)->default(0.00)->graphqlField()
            ->column('maintenance_differe')->float(4)->default(0.00)->graphqlField()
            ->column('gros_entretien')->float(4)->default(0.00)->graphqlField()
            ->column('gros_entretien_differe')->float(4)->default(0.00)->graphqlField()
            ->column('provision_gros')->float(4)->default(0.00)->graphqlField()
            ->column('provision_gros_differe')->float(4)->default(0.00)->graphqlField()
            ->column('taux_vacance')->float(4)->default(0.00)->graphqlField()
            ->column('taux_vacance_garages')->float(4)->default(0.00)->graphqlField()
            ->column('taux_vacance_commerces')->float(4)->default(0.00)->graphqlField()
            ->column('application_frais')->float(4)->default(1)->graphqlField()
            ->column('frais_personnel')->float(4)->default(0.00)->graphqlField()
            ->column('frais_gestion')->float(4)->default(0.00)->graphqlField()
            ->column('seuil_declenchement')->float(4)->default(0.00)->graphqlField()
            ->column('taux_directe')->float(4)->default(0.00)->graphqlField()
            ->column('taux_vefa')->float(4)->default(0.00)->graphqlField()
            ->column('taux_rehabilitation')->float(4)->default(0.00)->graphqlField()
            ->column('simulation_id')->references('simulations')->notNull()->graphqlField();

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
