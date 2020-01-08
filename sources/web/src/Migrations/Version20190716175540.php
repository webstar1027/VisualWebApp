<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use TheCodingMachine\FluidSchema\FluidSchemaException;
use TheCodingMachine\FluidSchema\TdbmFluidSchema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190716175540 extends AbstractMigration
{
    /**
     * @param Schema $schema
     * @throws FluidSchemaException
     */
    public function up(Schema $schema): void
    {
        $db = new TdbmFluidSchema($schema);
        $table = $db->table('risques_locatifs');
        $table
            ->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('simulation_id')->references('simulations')->notNull()->graphqlField()
            ->column('iteration')->integer()->null()->default(0)->graphqlField()
            ->column('acne')->float(4)->null()->graphqlField()
            ->column('taux_vacance_patrimoine')->float(4)->null()->graphqlField()
            ->column('taux_vacance_garages')->float(4)->null()->graphqlField()
            ->column('taux_vacance_commerciaux')->float(4)->null()->graphqlField()
            ->column('taux_annuel')->float(4)->null()->graphqlField();

        $table->unique(['simulation_id', 'iteration']);

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
