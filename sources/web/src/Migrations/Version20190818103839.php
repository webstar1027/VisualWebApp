<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use TheCodingMachine\FluidSchema\TdbmFluidSchema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190818103839 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $db = new TdbmFluidSchema($schema);

        $db->table('partagers')
            ->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('simulation_id')->references('simulations')->notNull()->graphqlField()
            ->column('utilisateur_id')->references('utilisateurs')->notNull()->graphqlField()
            ->column('entite_id')->references('entites')->notNull()->graphqlField()
            ->column('owner')->references('entites')->notNull()->graphqlField()
            ->column('owner_utilisateur_id')->references('utilisateurs')->notNull()->graphqlField()
            ->column('partage_type')->string(255)->notNull()->graphqlField()
           	->column('date_creation')->datetimeImmutable()->notNull()->graphqlField()
            ->column('date_modification')->datetimeImmutable()->null()->default(null)->graphqlField();
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
