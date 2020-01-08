<?php

declare(strict_types=1);

namespace App\Validators;

use App\Exceptions\ValidatorException;

final class Validator
{
    /**
     * @param mixed $value
     *
     * @throws ValidatorException
     */
    public static function shouldBeNull($value, string $errorMessage): void
    {
        if ($value !== null) {
            throw new ValidatorException($errorMessage);
        }
    }

    /**
     * @param mixed $value
     *
     * @throws ValidatorException
     */
    public static function shouldNotBeNull($value, string $errorMessage): void
    {
        if ($value === null) {
            throw new ValidatorException($errorMessage);
        }
    }
}
