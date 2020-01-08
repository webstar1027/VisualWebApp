<?php

declare(strict_types=1);

namespace App\Tests\Controller\GraphQL;

use App\Controller\GraphQL\EntitesController;
use App\Exceptions\HTTPException;
use App\Fixtures\CreateEntites;
use App\Fixtures\CreateUtilisateurs;
use App\Model\Entite;
use App\Model\Utilisateur;
use Safe\Exceptions\FilesystemException;
use Safe\Exceptions\JsonException;
use TheCodingMachine\TDBM\TDBMException;

final class EntitesControllerTest extends AbstractGraphQLControllerTestCase
{
    // Test GraphQL queries
    /**
     * @throws FilesystemException
     * @throws JsonException
     */
    public function testThatWeHaveSevenEntites(): void
    {
        $this->login();
        $response = $this->mustSuccessGraphQL('administration/entites/allEntites.gql');
        $this->assertCount(7, $response['allEntites']);
    }

    /**
     * @throws FilesystemException
     * @throws JsonException
     */
    public function testEntitesFilter(): void
    {
        $this->login();
        $response = $this->mustSuccessGraphQL('administration/entites/entites.gql', [
            'limit' => 10,
            'offset' => 0,
            'sortColumn' => 'nom',
            'sortOrder' => 'ASC',
            'nom' => CreateEntites::NOM_1,
        ]);
        $this->assertSame(CreateEntites::NOM_1, $response['entites']['items'][0]['nom']);
    }

    /**
     * @throws FilesystemException
     * @throws JsonException
     */
    public function testTheEntiteQuery(): void
    {
        $this->login();
        /** @var Entite $entite */
        $entite = $this->entiteDao->findOneByCode(CreateEntites::CODE_1);
        $this->assertNotNull($entite);
        $response = $this->mustSuccessGraphQL('administration/entites/entite.gql', [
            'entiteID' => $entite->getId(),
        ]);
        $this->assertSame('TCM56', $response['entite']['code']);
    }

    // Test GraphQL mutations

    /**
     * @throws FilesystemException
     * @throws JsonException
     */
    public function testThatNewEntiteIsAdded(): void
    {
        $this->login();
        /** @var Utilisateur $referent */
        $referent = $this->utilisateurDao->findOneByEmail(CreateUtilisateurs::UTILISATEUR_1_EMAIL);
        $this->assertNotNull($referent);

        $response = $this->mustSuccessGraphQL('administration/entites/saveEntite.gql', [
            'nom' => 'foo',
            'siren' => 'bar',
            'code' => 'baz',
            'type' => 'qux',
            'referents' => [$referent->getId()],
            'estActivee' => true,
        ]);
        $this->assertArrayHasKey('id', $response['saveEntite']);
    }

    /**
     * @throws FilesystemException
     * @throws JsonException
     */
    public function testThatEntiteMutationUpdatesTheEntite(): void
    {
        $this->login();
        /** @var Entite $entite */
        $entite = $this->entiteDao->findOneByCode(CreateEntites::CODE_2);
        $this->assertNotNull($entite);
        /** @var Utilisateur $referent */
        $referent = $this->utilisateurDao->findOneByEmail(CreateUtilisateurs::UTILISATEUR_2_EMAIL);
        $this->assertNotNull($referent);

        $response = $this->mustSuccessGraphQL('administration/entites/saveEntite.gql', [
            'entiteID' => $entite->getId(),
            'nom' => CreateEntites::NOM_2 . ' Updated',
            'siren' => CreateEntites::SIREN_2,
            'code' => CreateEntites::CODE_2,
            'type' => CreateEntites::TYPE_2,
            'referents' => [$referent->getId()],
            'estActivee' => true,
        ]);
        $this->assertArrayHasKey('id', $response['saveEntite']);
        $response = $this->mustSuccessGraphQL('administration/entites/entite.gql', [
            'entiteID' => $entite->getId(),
        ]);
        $this->assertSame(CreateEntites::NOM_2 . ' Updated', $response['entite']['nom']);
    }

    /**
     * @throws FilesystemException
     * @throws JsonException
     */
    public function testBadRequestIfWeAttemptToAddAnExistingEntite(): void
    {
        $this->login();
        $this->mustFailGraphQL('administration/entites/saveEntite.gql', [
            'nom' => CreateEntites::NOM_3,
            'siren' => CreateEntites::SIREN_3,
            'code' => CreateEntites::CODE_3,
            'type' => CreateEntites::TYPE_3,
            'estActivee' => true,
        ], 400);
    }

    /**
     * @throws HTTPException
     * @throws JsonException
     * @throws TDBMException
     */
    public function testThatRegularUserCanNotDeleteAnEntite(): void
    {
        $this->login(CreateUtilisateurs::UTILISATEUR_1_EMAIL, CreateUtilisateurs::UTILISATEUR_PASSWORD);
        /** @var Entite $entite */
        $entite = $this->entiteDao->findOneByCode(CreateEntites::CODE_4);
        /** @var Utilisateur $referent */
        $referent = $this->utilisateurDao->findOneByEmail(CreateUtilisateurs::UTILISATEUR_2_EMAIL);
        $this->assertNotNull($referent);

        /** @var EntitesController $entitesController */
        $entitesController = self::$container->get(EntitesController::class);
        $this->expectException(HTTPException::class);
        $this->expectExceptionCode(403);
        $entitesController->saveEntite($entite->getId(), CreateEntites::NOM_4, CreateEntites::SIREN_4, CreateEntites::CODE_4, CreateEntites::TYPE_4, null, null, false, [$referent->getId()]);
    }
}
