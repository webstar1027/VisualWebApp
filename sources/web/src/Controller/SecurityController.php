<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dao\UtilisateurDao;
use App\Services\UtilisateurService;
use Exception;
use RuntimeException;
use Safe\Exceptions\JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use TheCodingMachine\GraphQLite\Annotations\Query;
use function is_string;
use function Safe\json_decode;

final class SecurityController extends AbstractVisialWebController
{
    /** @var UtilisateurService */
    private $utilisateurService;

    public function __construct(UtilisateurDao $utilisateurDao, UtilisateurService $utilisateurService)
    {
        parent::__construct($utilisateurDao);
        $this->utilisateurService = $utilisateurService;
    }

    /**
     * @Route("/api/security/login", name="login")
     */
    public function loginAction(): JsonResponse
    {
        $utilisateur = $this->mustGetUtilisateur();

        return new JsonResponse($utilisateur->toSecurityMap());
    }

    /**
     * @throws RuntimeException
     *
     * @Route("/api/security/logout", name="logout")
     */
    public function logoutAction(): void
    {
        throw new RuntimeException('This should not be reached!');
    }

    /**
     * @throws JsonException
     *
     * @Route("/api/security/token", name="finalize_user")
     */
    public function finalizeUserByToken(Request $request): JsonResponse
    {
        $info = $request->getContent();
        if (is_string($info)) {
            $info = json_decode($info, true);
        }

        if (empty($info['password']) || empty($info['token'])) {
            throw new JsonException('Invalid parameters');
        }

        $utilisateur = $this->utilisateurService->setUtilisateurPassword($info['password'], $info['token']);

        if (empty($utilisateur)) {
            throw new JsonException('Utilisateur Failed');
        }

        return new JsonResponse($utilisateur->toSecurityMap());
    }

    /**
     * @Query()
     */
    public function getEmailFromToken(string $token): string
    {
        return $this->utilisateurService->getUtilisateurMailFromToken($token);
    }

    /**
     * @throws Exception
     *
     * @Route("/api/security/reset-password", name="reset")
     */
    public function resetPasswordAction(Request $request): JsonResponse
    {
        $info = $request->getContent();
        if (is_string($info)) {
            $info = json_decode($info, true);
        }
        $utilisateur = $this->utilisateurDao->getUtilisateurByEmail($info['email']);
        if (empty($utilisateur)) {
            return new JsonResponse(['status' => false, 'message' => 'l\'email n\'existe pas']);
        }

        $this->utilisateurService->generateAndSendToken($utilisateur);

        return new JsonResponse(['status' => 'true']);
    }
}
