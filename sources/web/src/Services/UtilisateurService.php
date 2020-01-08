<?php

declare(strict_types=1);

namespace App\Services;

use App\Dao\UtilisateurDao;
use App\Exceptions\MailNotSentException;
use App\Model\Utilisateur;
use Exception;
use Swift_Mailer;
use Swift_Message;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use function getenv;
use function hash;
use function random_bytes;

class UtilisateurService
{
    /** @var UtilisateurDao */
    private $utilisateurDao;
    /** @var RequestStack */
    private $requestStack;
    /** @var UserPasswordEncoderInterface */
    private $encoder;
    /** @var Swift_Mailer */
    private $mailer;
    /** @var Environment */
    private $templating;

    public function __construct(
        UtilisateurDao $utilisateurDao,
        RequestStack $requestStack,
        UserPasswordEncoderInterface $encoder,
        Swift_Mailer $mailer,
        Environment $templating
    ) {
        $this->utilisateurDao = $utilisateurDao;
        $this->requestStack = $requestStack;
        $this->encoder = $encoder;
        $this->mailer = $mailer;
        $this->templating = $templating;
    }

    /**
     * @throws Exception
     */
    public function generateAndSendToken(Utilisateur $utilisateur): Utilisateur
    {
        if (empty($utilisateur->getToken())) {
            $utilisateur->setToken($this->generateToken());
        }

        $this->utilisateurDao->save($utilisateur);

        if (! empty($this->requestStack->getMasterRequest())) {
            $url = $this->requestStack->getMasterRequest()->getSchemeAndHttpHost() . '/confirmation/' . $utilisateur->getToken();

            $this->sendEmail($url, $utilisateur->getEmail());
        }

        return $utilisateur;
    }

    /**
     * @throws MailNotSentException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function sendEmail(string $url, string $email): void
    {
        $from = ! empty(getenv('MAIL_SENDER')) ? getenv('MAIL_SENDER') : 'noreply@visualweb.com';

        $message = (new Swift_Message('Invitation Email'));

        $emailData = [];
        $emailData['url'] = $url;

        $message->setFrom($from)
            ->setTo($email)
            ->setBody(
                $this->templating->render(
                    'emails/invitation.html.twig',
                    $emailData
                ),
                'text/html'
            );
        if ($this->mailer->send($message) === 0) {
            throw new MailNotSentException('Une erreur inconnue est survenue, le mail n\'a pas pu être envoyé.');
        }
    }

    public function getUtilisateurMailFromToken(string $token): string
    {
        $utilisateur = $this->utilisateurDao->getUtilisateurByToken($token);

        return $utilisateur && ! empty($utilisateur->getEmail()) ? $utilisateur->getEmail() : '';
    }

    /**
     * @throws Exception
     */
    protected function generateToken(): string
    {
        $tokenString = random_bytes(32);

        return hash('sha512', $tokenString);
    }

    public function setUtilisateurPassword(string $password, string $token): ?Utilisateur
    {
        $utilisateur = $this->utilisateurDao->getUtilisateurByToken($token);
        if ($utilisateur) {
            if ($utilisateur instanceof UserInterface) {
                $encoded = $this->encoder->encodePassword($utilisateur, $password);

                $utilisateur->setMotDePasse($encoded);
                $utilisateur->setToken(null);
            }
            $this->utilisateurDao->save($utilisateur);
        }

        return $utilisateur;
    }
}
