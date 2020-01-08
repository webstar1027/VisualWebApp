<?php

declare(strict_types=1);

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

final class ValidatorExceptionListener
{
    public function onKernelException(GetResponseForExceptionEvent $event): void
    {
        $exception = $event->getException();
        if (! ($exception instanceof ValidatorException)) {
            return;
        }

        $event->setException(HTTPException::badRequest($exception->getMessage()));
    }
}
