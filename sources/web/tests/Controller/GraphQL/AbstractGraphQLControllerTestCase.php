<?php

declare(strict_types=1);

namespace App\Tests\Controller\GraphQL;

use App\Dao\DroitDao;
use App\Dao\EnsembleDao;
use App\Dao\EntiteDao;
use App\Dao\RoleDao;
use App\Dao\UtilisateurDao;
use App\Fixtures\CreateUtilisateurs;
use Safe\Exceptions\FilesystemException;
use Safe\Exceptions\JsonException;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use function basename;
use function Safe\file_get_contents;
use function Safe\json_decode;
use function Safe\json_encode;

abstract class AbstractGraphQLControllerTestCase extends WebTestCase
{
    /** @var string */
    private $assetsGraphqlDirectory;

    /** @var Client */
    protected $client;

    /** @var UtilisateurDao */
    protected $utilisateurDao;

    /** @var EntiteDao */
    protected $entiteDao;

    /** @var EnsembleDao */
    protected $ensembleDao;
    /** @var RoleDao */
    protected $roleDao;

    /** @var DroitDao */
    protected $droitDao;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->assetsGraphqlDirectory = static::$container->get('kernel')->getProjectDir() . '/assets/vue/graphql/';
        $this->client = static::createClient();
        $this->utilisateurDao = static::$container->get('App\Dao\UtilisateurDao');
        $this->entiteDao = static::$container->get('App\Dao\EntiteDao');
        $this->ensembleDao = self::$container->get('App\Dao\EnsembleDao');
        $this->roleDao = self::$container->get('App\Dao\RoleDao');
        $this->droitDao = self::$container->get('App\Dao\DroitDao');
    }

    /**
     * @throws JsonException
     */
    protected function login(string $username = CreateUtilisateurs::ADMIN_CENTRAL_EMAIL, string $password = CreateUtilisateurs::ADMIN_PASSWORD): void
    {
        $payload = json_encode([
            'username' => $username,
            'password' => $password,
        ]);
        $this->client->request('POST', 'api/security/login', [], [], ['CONTENT_TYPE' => 'application/json'], $payload);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @param array<string,mixed>|null $variables
     *
     * @return mixed[]
     *
     * @throws FilesystemException
     * @throws JsonException
     */
    protected function mustSuccessGraphQL(string $filePath, ?array $variables = null): array
    {
        $response = $this->sendGraphQL($filePath, $variables);
        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
        $jsonResponse = json_decode($response->getContent(), true);
        $this->assertArrayNotHasKey('errors', $jsonResponse, 'Erreur retournée par la requête GraphQL: ' . $response->getContent());
        $this->assertArrayHasKey('data', $jsonResponse);

        return $jsonResponse['data'];
    }

    /**
     * @param array<string,mixed>|null $variables
     *
     * @throws FilesystemException
     * @throws JsonException
     */
    protected function mustFailGraphQL(string $filePath, ?array $variables = null, int $statusCode = 500): Response
    {
        $response = $this->sendGraphQL($filePath, $variables);
        $this->assertEquals($statusCode, $response->getStatusCode());

        return $response;
    }

    /**
     * @param array<string,mixed>|null $variables
     *
     * @throws FilesystemException
     * @throws JsonException
     */
    private function sendGraphQL(string $filePath, ?array $variables = null): Response
    {
        $payload = $this->createGraphQLPayload($filePath, $variables);
        $this->client->request('POST', '/graphql', [], [], [], $payload);

        return $this->client->getResponse();
    }

    /**
     * @param array<string,mixed>|null $variables
     *
     * @throws FilesystemException
     * @throws JsonException
     */
    private function createGraphQLPayload(string $filePath, ?array $variables = null): string
    {
        $operationName = basename($filePath, '.gql');
        $query = file_get_contents($this->assetsGraphqlDirectory . $filePath);
        $payload = [
            'operationName' => $operationName,
            'query' => $query,
        ];
        if (! empty($variables)) {
            $payload['variables'] = $variables;
        }

        return json_encode($payload);
    }
}
