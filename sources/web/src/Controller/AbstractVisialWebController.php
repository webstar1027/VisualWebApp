<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dao\UtilisateurDao;
use App\Model\Utilisateur;
use App\Security\SerializableUser;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class AbstractVisialWebController extends AbstractController
{
    /** @var UtilisateurDao */
    protected $utilisateurDao;

    public function __construct(UtilisateurDao $utilisateurDao)
    {
        $this->utilisateurDao = $utilisateurDao;
    }

    protected function mustGetUtilisateur(): Utilisateur
    {
        /** @var SerializableUser $user */
        $user = $this->getUser();
        if (empty($user)) {
            throw new RuntimeException('This should not be reached!');
        }
        $utilisateur = $this->utilisateurDao->findOneByEmail($user->getUsername());
        if (empty($utilisateur)) {
            throw new RuntimeException('This should not be reached!');
        }

        return $utilisateur;
    }
}
