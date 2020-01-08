<?php

declare(strict_types=1);

namespace App\Fixtures;

interface Fixture
{
    public function up(): void;

    public function getName(): string;
}
