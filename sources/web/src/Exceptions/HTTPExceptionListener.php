<?php

declare(strict_types=1);

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

final class HTTPExceptionListener
{
    public function onKernelException(GetResponseForExceptionEvent $event): void
    {
        $exception = $event->getException();
        if (! ($exception instanceof HTTPException)) {
            return;
        }

        $response = new JsonResponse($exception->getMessage());
        $response->setStatusCode($exception->getCode());
        $event->setResponse($response);
    }
}
