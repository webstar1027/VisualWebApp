<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dao\UtilisateurDao;
use App\Security\SerializableUser;
use Safe\Exceptions\JsonException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function Safe\json_encode;

final class IndexController extends AbstractController
{
    /** @var UtilisateurDao */
    private $utilisateurDao;

    public function __construct(UtilisateurDao $utilisateurDao)
    {
        $this->utilisateurDao = $utilisateurDao;
    }

    /**
     * @throws JsonException
     *
     * @Route("/{vueRouting}", requirements={"vueRouting"="^(?!api|graphiql|graphql|_(profiler|wdt)).*"}, name="index")
     */
    public function indexAction(): Response
    {
        /** @var SerializableUser|null $user */
        $user = $this->getUser();
        $utilisateur = ! empty($user) ? $this->utilisateurDao->findOneByEmail($user->getUsername()) : null;

        return $this->render('base.html.twig', [
            'isAuthenticated' => json_encode(! empty($utilisateur)),
            'utilisateur' => json_encode(! empty($utilisateur) ? $utilisateur->toSecurityMap() : null),
        ]);
    }
}
