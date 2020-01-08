<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use TheCodingMachine\FluidSchema\TdbmFluidSchema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190913085151 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $db = new TdbmFluidSchema($schema);

        $db->table('fond_de_roulement')
            ->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('simulation_id')->references('simulations')->graphqlField()
            ->column('nom')->string(255)->notNull()->graphqlField()
            ->column('type')->string(255)->notNull()->graphqlField()
            ->column('taux_evolution')->float(4)->null()->default(null)->graphqlField()
            ->column('type_emprunt_id')->references('types_emprunts')->null()->default(null)->graphqlField()
            ->column('date_echeance')->datetimeImmutable()->null()->default(null)->graphqlField()
            ->column('deletable')->boolean()->notNull()->default(true)->graphqlField();

        $db->table('fond_de_roulement_periodique')
            ->column('fond_de_roulement_id')->references('fond_de_roulement')->graphqlField()
            ->column('iteration')->integer()->notNull()->graphqlField()->endGraphql()
            ->then()->primaryKey(['fond_de_roulement_id', 'iteration'])
            ->column('valeur')->float(4)->null()->default(null)->graphqlField();

        $db->table('fond_de_roulement_parametre')
            ->column('id')->guid()->primaryKey()->comment('@UUID')->graphqlField()
            ->column('simulation_id')->references('simulations')->graphqlField()
            ->column('potentiel_financier_termination')->float(4)->null()->default(null)->graphqlField()->endGraphql()
            ->column('fonds_propres_sur_operation')->float(4)->null()->default(null)->graphqlField()
            ->column('depot_de_garantie')->float(4)->null()->default(null)->graphqlField();

    }
    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
