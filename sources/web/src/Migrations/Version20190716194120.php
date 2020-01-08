<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use TheCodingMachine\FluidSchema\TdbmFluidSchema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190716194120 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $db = new TdbmFluidSchema($schema);

        $db->table('invitations_entites')->addAnnotation('TheCodingMachine\\GraphQLite\\Annotations\\Field')
            ->column('entite_id')->references('entites')->graphqlField()
            ->column('utilisateur_id')->references('utilisateurs')->graphqlField()
            ->column('statut')->string(255)->notNull()->graphqlField()->endGraphql()
            ->then()->primaryKey(['entite_id', 'utilisateur_id']);
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
