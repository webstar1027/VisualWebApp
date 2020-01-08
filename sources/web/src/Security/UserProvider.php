<?php

declare(strict_types=1);

namespace App\Security;

use App\Dao\UtilisateurDao;
use App\Model\Utilisateur;
use Safe\Exceptions\StringsException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use function get_class;
use function Safe\sprintf;

class UserProvider implements UserProviderInterface
{
    /** @var UtilisateurDao */
    private $utilisateurDao;

    public function __construct(UtilisateurDao $utilisateurDao)
    {
        $this->utilisateurDao = $utilisateurDao;
    }

    /**
     * @param mixed $email
     *
     * @throws UsernameNotFoundException
     * @throws StringsException
     */
    public function loadUserByUsername($email): SerializableUser
    {
        $utilisateur = $this->utilisateurDao->findOneByEmail($email);
        if (! empty($utilisateur) && $utilisateur->getEstActive()) {
            $password = $utilisateur->getMotDePasse();
            $rolesPerEntites = $utilisateur->getUtilisateursRolesEntites();
            $droitsPerEntites = [];
            $symfonyRoles = [];
            foreach ($rolesPerEntites as $roleEntite) {
                $droits = $roleEntite->getRole()->getDroits();
                foreach ($droits as $droit) {
                    $droitsPerEntites[$roleEntite->getEntite()->getId()] = $droit->getLibelle();
                    $symfonyRoles[] = $droit->getLibelle();
                }
            }
            if ($utilisateur->getEstAdministrateurCentral()) {
                $symfonyRoles[] = 'ROLE_ADMINISTRATEUR_CENTRAL';
            }
            if ($utilisateur->getEstAdministrateurSimulation()) {
                $symfonyRoles[] = 'ROLE_ADMINISTRATEUR_SIMULATION';
            }
            // The bcrypt algorithm doesn't require a separate salt.
            $salt = null;

            return new SerializableUser($email, $password, $salt, $symfonyRoles);
        }
        throw new UsernameNotFoundException(
            sprintf('"%s" does not exist.', $email)
        );
    }

    /**
     * @throws UsernameNotFoundException
     * @throws UnsupportedUserException
     * @throws StringsException
     */
    public function refreshUser(UserInterface $user): SerializableUser
    {
        if (! $user instanceof SerializableUser) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * @param mixed $class
     */
    public function supportsClass($class): bool
    {
        return $class === Utilisateur::class || $class === SerializableUser::class;
    }
}
