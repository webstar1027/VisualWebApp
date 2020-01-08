<?php

declare(strict_types=1);

namespace App\Fixtures;

use App\Dao\EntiteDao;
use App\Dao\UtilisateurDao;
use App\Model\Entite;
use App\Model\Utilisateur;
use Cake\Chronos\Chronos;
use RuntimeException;

final class CreateEntites implements Fixture
{
    public const NOM_1 = 'TheCodingMachine';
    public const SIREN_1 = '164 128 494';
    public const CODE_1 = 'TCM56';
    public const TYPE_1 = 'Organisme';
    public const TYPE_ORGANISME_1 = 'ESH';
    public const CODE_ORGANISME_1 = 'TMC10';

    public const NOM_2 = 'Visial';
    public const SIREN_2 = '458 502 684';
    public const CODE_2 = 'VISIA';
    public const TYPE_2 = 'Partenaire';
    public const TYPE_ORGANISME_2 = 'OPH';
    public const CODE_ORGANISME_2 = 'VISIA10';

    public const NOM_3 = 'Predicthlm';
    public const SIREN_3 = '165 874 125';
    public const CODE_3 = '12550';
    public const TYPE_3 = 'Organisme';
    public const TYPE_ORGANISME_3 = 'Coopérative';
    public const CODE_ORGANISME_3 = '05521';

    public const NOM_4 = 'Futurhlm';
    public const SIREN_4 = '874 265 842';
    public const CODE_4 = '20550';
    public const TYPE_4 = 'Organisme';
    public const TYPE_ORGANISME_4 = 'SC';
    public const CODE_ORGANISME_4 = '05502';

    public const NOM_5 = 'The Coders Community';
    public const SIREN_5 = '325 651 980';
    public const CODE_5 = 'CODER';
    public const TYPE_5 = 'Partenaire';
    public const TYPE_ORGANISME_5 = 'SEM';
    public const CODE_ORGANISME_5 = 'REDOC';

    public const NOM_6 = 'Alphabet';
    public const SIREN_6 = '685 125 362';
    public const CODE_6 = 'ALPHA';
    public const TYPE_6 = 'Holding';
    public const TYPE_ORGANISME_6 = 'MOI';
    public const CODE_ORGANISME_6 = 'AHPLA';

    public const NOM_7 = 'Acial';
    public const SIREN_7 = '453 122 061';
    public const CODE_7 = 'ACIAL';
    public const TYPE_7 = 'Confédération';
    public const TYPE_ORGANISME_7 = 'SVHLM';
    public const CODE_ORGANISME_7 = 'LAICA';

    /** @var Utilisateur */
    private $adminCentral;

    /** @var UtilisateurDao */
    private $utilisateurDao;

    /** @var EntiteDao */
    private $entiteDao;

    public function __construct(UtilisateurDao $utilisateurDao, EntiteDao $entiteDao)
    {
        $this->utilisateurDao = $utilisateurDao;
        $this->entiteDao = $entiteDao;
    }

    public function up(): void
    {
        $utilisateur = $this->utilisateurDao->findOneByEmail(CreateUtilisateurs::ADMIN_CENTRAL_EMAIL);
        if (empty($utilisateur)) {
            throw new RuntimeException('The admin user does not exist!');
        } else {
            $this->adminCentral = $utilisateur;
        }

        $this->createEntite($this->adminCentral, self::SIREN_1, self::NOM_1, self::CODE_1, self::TYPE_1, self::CODE_ORGANISME_1, self::TYPE_ORGANISME_1);
        $this->createEntite($this->adminCentral, self::SIREN_2, self::NOM_2, self::CODE_2, self::TYPE_2, self::CODE_ORGANISME_2, self::TYPE_ORGANISME_2);
        $this->createEntite($this->adminCentral, self::SIREN_3, self::NOM_3, self::CODE_3, self::TYPE_3, self::CODE_ORGANISME_3, self::TYPE_ORGANISME_3);
        $this->createEntite($this->adminCentral, self::SIREN_4, self::NOM_4, self::CODE_4, self::TYPE_4, self::CODE_ORGANISME_4, self::TYPE_ORGANISME_4);
        $this->createEntite($this->adminCentral, self::SIREN_5, self::NOM_5, self::CODE_5, self::TYPE_5, self::CODE_ORGANISME_5, self::TYPE_ORGANISME_5);
        $this->createEntite($this->adminCentral, self::SIREN_6, self::NOM_6, self::CODE_6, self::TYPE_6, self::CODE_ORGANISME_6, self::TYPE_ORGANISME_6);
        $this->createEntite($this->adminCentral, self::SIREN_7, self::NOM_7, self::CODE_7, self::TYPE_7, self::CODE_ORGANISME_7, self::TYPE_ORGANISME_7);
    }

    private function createEntite(Utilisateur $creePar, string $siren, string $nom, string $code, string $type, ?string $codeOrganisme, ?string $typeOrganisme): void
    {
        $entite = new Entite(
            $creePar,
            $siren,
            $nom,
            $code,
            $type,
            Chronos::now()
        );

        $entite->setCodeOrganisme($codeOrganisme);
        $entite->setTypeOrganisme($typeOrganisme);
        $entite->setCreePar($creePar);
        $entite->setEstActivee(true);

        $this->entiteDao->save($entite);
    }

    public function getName(): string
    {
        return 'Create entités';
    }
}
