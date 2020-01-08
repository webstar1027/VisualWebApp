<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Throwable;

final class HTTPException extends Exception
{
    private const MULTIPLE_CHOICES = "L'URI demandée se rapporte à plusieurs ressources.";

    private const MOVED_PERMANENTLY = 'Document déplacé de façon permanente.';

    private const FOUND = 'Document déplacé de façon temporaire.';

    private const BAD_REQUEST = 'La syntaxe de la requête est erronée.';

    private const UNAUTHORIZED = 'Une authentification est nécessaire pour accéder à la ressource.';

    private const FORBIDDEN = "Le serveur a compris la requête, mais refuse de l'exécuter.";

    private const NOT_FOUND = 'Ressource non trouvée.';

    private const METHOD_NOT_ALLOWED = 'Méthode de requête non autorisée.';

    private const INTERNAL_SERVER_ERROR = 'Erreur interne du serveur.';

    private const NOT_IMPLEMENTED = 'Fonctionnalité réclamée non supportée par le serveur.';

    private const BAD_GATEWAY = 'Le serveur a reçu une réponse invalide depuis le serveur distant.';

    private const SERVICE_UNAVAILABLE = 'Service temporairement indisponible ou en maintenance.';

    private const GATEWAY_TIMEOUT = "Temps d'attente d'une réponse d'un serveur à un serveur intermédiaire écoulé.";

    public static function multipleChoices(string $message = self::MULTIPLE_CHOICES, ?Throwable $previous = null): self
    {
        return new self($message, 300, $previous);
    }

    public static function movedPermanently(string $message = self::MOVED_PERMANENTLY, ?Throwable $previous = null): self
    {
        return new self($message, 301, $previous);
    }

    public static function found(string $message = self::FOUND, ?Throwable $previous = null): self
    {
        return new self($message, 302, $previous);
    }

    public static function badRequest(string $message = self::BAD_REQUEST, ?Throwable $previous = null): self
    {
        return new self($message, 400, $previous);
    }

    public static function unauthorized(string $message = self::UNAUTHORIZED, ?Throwable $previous = null): self
    {
        return new self($message, 401, $previous);
    }

    public static function forbidden(string $message = self::FORBIDDEN, ?Throwable $previous = null): self
    {
        return new self($message, 403, $previous);
    }

    public static function notFound(string $message = self::NOT_FOUND, ?Throwable $previous = null): self
    {
        return new self($message, 404, $previous);
    }

    public static function methodNotAllowed(string $message = self::METHOD_NOT_ALLOWED, ?Throwable $previous = null): self
    {
        return new self($message, 405, $previous);
    }

    public static function internalServerError(string $message = self::INTERNAL_SERVER_ERROR, ?Throwable $previous = null): self
    {
        return new self($message, 500, $previous);
    }

    public static function notImplemented(string $message = self::NOT_IMPLEMENTED, ?Throwable $previous = null): self
    {
        return new self($message, 501, $previous);
    }

    public static function badGateway(string $message = self::BAD_GATEWAY, ?Throwable $previous = null): self
    {
        return new self($message, 502, $previous);
    }

    public static function serviceUnavailable(string $message = self::SERVICE_UNAVAILABLE, ?Throwable $previous = null): self
    {
        return new self($message, 503, $previous);
    }

    public static function gatewayTimeout(string $message = self::GATEWAY_TIMEOUT, ?Throwable $previous = null): self
    {
        return new self($message, 504, $previous);
    }
}
