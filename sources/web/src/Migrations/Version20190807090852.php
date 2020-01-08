<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use TheCodingMachine\FluidSchema\TdbmFluidSchema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190807090852 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $db = new TdbmFluidSchema($schema);

        $db->table('produits_autres')
            ->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('nom')->string(255)->null()->graphqlField()
            ->column('montants')->integer()->null()->graphqlField()
            ->column('nature')->smallInt()->null()->graphqlField()
            ->column('type')->smallInt()->notNull()->graphqlField()
            ->column('taux_evolution')->float(4)->null()->graphqlField()
            ->column('calcul_automatique')->integer()->null()->graphqlField()
            ->column('simulation_id')->references('simulations')->notNull()->graphqlField();

        $db->table('produits_autres_periodique')
            ->column('produits_autres_id')->references('produits_autres')->graphqlField()
            ->column('iteration')->integer()->notNull()->graphqlField()->endGraphql()
            ->then()->primaryKey(['produits_autres_id', 'iteration'])
            ->column('value')->float(4)->null()->default(null)->graphqlField();
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
