<?php

declare(strict_types=1);

namespace App\Tests\Controller\GraphQL;

use Safe\Exceptions\JsonException;
use function Safe\json_encode;

final class IndexControllerTest extends AbstractGraphQLControllerTestCase
{
    // Test login
    /**
     * @throws JsonException
     */
    public function testLoginWithWrongCredentialsAssertUnauthorized(): void
    {
        $payload = json_encode([
            'username' => 'Foo',
            'password' => 'Bar',
        ]);
        $this->client->request('POST', 'api/security/login', [], [], ['CONTENT_TYPE' => 'application/json'], $payload);
        $this->assertEquals(401, $this->client->getResponse()->getStatusCode());
    }
}
