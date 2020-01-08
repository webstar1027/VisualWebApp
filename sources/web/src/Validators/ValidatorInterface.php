<?php

declare(strict_types=1);

namespace App\Validators;

use App\Exceptions\ValidatorException;

interface ValidatorInterface
{
    /**
     * @throws ValidatorException
     */
    public function check(): void;
}
