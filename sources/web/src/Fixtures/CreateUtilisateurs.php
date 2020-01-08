<?php

declare(strict_types=1);

namespace App\Fixtures;

use App\Dao\EntiteDao;
use App\Dao\UtilisateurDao;
use App\Model\Entite;
use App\Model\Utilisateur;
use Cake\Chronos\Chronos;
use Safe\Exceptions\PasswordException;
use const PASSWORD_BCRYPT;
use function Safe\password_hash;

final class CreateUtilisateurs implements Fixture
{
    public const UTILISATEUR_1_EMAIL = 'user1@visialweb.com';
    public const UTILISATEUR_2_EMAIL = 'user2@visialweb.com';
    public const UTILISATEUR_3_EMAIL = 'user3@visialweb.com';
    public const UTILISATEUR_4_EMAIL = 'user4@visialweb.com';
    public const UTILISATEUR_5_EMAIL = 'user5@visialweb.com';
    public const UTILISATEUR_6_EMAIL = 'user6@visialweb.com';
    public const UTILISATEUR_7_EMAIL = 'user7@visialweb.com';
    public const UTILISATEUR_8_EMAIL = 'user8@visialweb.com';
    public const UTILISATEUR_9_EMAIL = 'user9@visialweb.com';
    public const UTILISATEUR_10_EMAIL = 'user10@visialweb.com';
    public const UTILISATEUR_11_EMAIL = 'user11@visialweb.com';
    public const UTILISATEUR_12_EMAIL = 'user12@visialweb.com';
    public const UTILISATEUR_13_EMAIL = 'Elisabeth.Tessier@predicthlm.fr';
    public const UTILISATEUR_14_EMAIL = 'Paco.Rabanne@futurhlm.fr';

    // Utilisateurs de test Acial
    public const UTILISATEUR_15_EMAIL = 'referent@acial.fr';
    public const UTILISATEUR_16_EMAIL = 'utilisateur@acial.fr';

    public const UTILISATEUR_PASSWORD = 'secret';

    public const ADMIN_CENTRAL_EMAIL = 'admin.central@visialweb.com';
    public const ADMIN_SIMU_EMAIL = 'admin.simulation@visialweb.com';
    public const ADMIN_PASSWORD = 'admin';

    /** @var UtilisateurDao */
    private $utilisateurDao;
    /** @var EntiteDao */
    private $entiteDao;

    public function __construct(UtilisateurDao $utilisateurDao, EntiteDao $entiteDao)
    {
        $this->utilisateurDao = $utilisateurDao;
        $this->entiteDao = $entiteDao;
    }

    /**
     * @throws PasswordException
     */
    public function up(): void
    {
        $this->createUtilisateur('Hadari', 'Yassine', self::UTILISATEUR_1_EMAIL, self::UTILISATEUR_PASSWORD, CreateEntites::CODE_1, false);
        $this->createUtilisateur('Romao', 'Valdo', self::UTILISATEUR_2_EMAIL, self::UTILISATEUR_PASSWORD, CreateEntites::CODE_1, false);
        $this->createUtilisateur('Bujoli', 'Jean-François', self::UTILISATEUR_3_EMAIL, self::UTILISATEUR_PASSWORD, CreateEntites::CODE_1, true);
        $this->createUtilisateur('Lopez', 'Boris', self::UTILISATEUR_4_EMAIL, self::UTILISATEUR_PASSWORD, CreateEntites::CODE_1, false);
        $this->createUtilisateur('Leclerc', 'Amélie', self::UTILISATEUR_5_EMAIL, self::UTILISATEUR_PASSWORD, CreateEntites::CODE_2, true);
        $this->createUtilisateur('Fellous', 'Cédric', self::UTILISATEUR_6_EMAIL, self::UTILISATEUR_PASSWORD, CreateEntites::CODE_3, true);
        $this->createUtilisateur('Hélaine', 'Gilles', self::UTILISATEUR_7_EMAIL, self::UTILISATEUR_PASSWORD, CreateEntites::CODE_4, true);
        $this->createUtilisateur('Laloum', 'Karen', self::UTILISATEUR_8_EMAIL, self::UTILISATEUR_PASSWORD, CreateEntites::CODE_5, true);
        $this->createUtilisateur('Pidancier', 'Kévin', self::UTILISATEUR_9_EMAIL, self::UTILISATEUR_PASSWORD, CreateEntites::CODE_6, true);
        $this->createUtilisateur('Linossier', 'Nicolas', self::UTILISATEUR_10_EMAIL, self::UTILISATEUR_PASSWORD, CreateEntites::CODE_6, false);
        $this->createUtilisateur('Manenc', 'Philippe', self::UTILISATEUR_11_EMAIL, self::UTILISATEUR_PASSWORD, CreateEntites::CODE_7, true);
        $this->createUtilisateur('Demonti', 'Vincent', self::UTILISATEUR_12_EMAIL, self::UTILISATEUR_PASSWORD, CreateEntites::CODE_7, false);
        $this->createUtilisateur('Tessier', 'Elisabeth', self::UTILISATEUR_13_EMAIL, self::UTILISATEUR_PASSWORD, CreateEntites::CODE_3, true);
        $this->createUtilisateur('Rabanne', 'Paco', self::UTILISATEUR_14_EMAIL, self::UTILISATEUR_PASSWORD, CreateEntites::CODE_4, true);

        // Utilisateur Acial
        $this->createUtilisateur('Acial', 'Référent', self::UTILISATEUR_15_EMAIL, self::UTILISATEUR_PASSWORD, CreateEntites::CODE_7, true);
        $this->createUtilisateur('Acial', 'Utilisateur', self::UTILISATEUR_16_EMAIL, self::UTILISATEUR_PASSWORD, CreateEntites::CODE_7, true);
    }

    /**
     * @throws PasswordException
     */
    private function createUtilisateur(string $nom, string $prenom, string $email, string $password, string $entiteCode, bool $isReferent): void
    {
        /** @var Entite $entite */
        $entite = $this->entiteDao->findOneByCode($entiteCode);
        $utilisateur = new Utilisateur(
            $nom,
            $prenom,
            $email,
            Chronos::now()
        );
        $utilisateur->setMotDePasse(password_hash($password, PASSWORD_BCRYPT));
        if ($isReferent) {
            $utilisateur->addEntiteByReferentsEntites($entite);
        }
        $this->utilisateurDao->save($utilisateur);
    }

    public function getName(): string
    {
        return 'Create utilisateurs';
    }
}
