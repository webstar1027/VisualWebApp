<?php

declare(strict_types=1);

namespace App\Tests\Controller\GraphQL;

use Safe\Exceptions\FilesystemException;
use Safe\Exceptions\JsonException;

final class DroitsControllerTest extends AbstractGraphQLControllerTestCase
{
    // Test GraphQL queries
    /**
     * @throws FilesystemException
     * @throws JsonException
     */
    public function testDroits(): void
    {
        $this->login();
        $this->mustSuccessGraphQL('administration/droits/droits.gql');
    }
}
