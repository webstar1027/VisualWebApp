<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use TheCodingMachine\FluidSchema\TdbmFluidSchema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191003081403 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $db = new TdbmFluidSchema($schema);

        $db->table('notifications')
            ->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('utilisateur_id')->references('utilisateurs')->graphqlField()
            ->column('type')->string(255)->notNull()->graphqlField()
            ->column('message')->string(255)->notNull()->graphqlField()
            ->column('statut')->string(255)->notNull()->graphqlField();
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
