<?php

declare(strict_types=1);

namespace App\Tests\Controller\GraphQL;

use App\Fixtures\CreateUtilisateurs;
use App\Model\Entite;
use App\Model\Role;
use App\Model\Utilisateur;
use Safe\Exceptions\FilesystemException;
use Safe\Exceptions\JsonException;

final class UtilisateursControllerTest extends AbstractGraphQLControllerTestCase
{
    /**
     * @throws FilesystemException
     * @throws JsonException
     */
    public function testUtilisateursListAsRegularUser(): void
    {
        $this->login(CreateUtilisateurs::UTILISATEUR_1_EMAIL, CreateUtilisateurs::UTILISATEUR_PASSWORD);
        $response = $this->mustSuccessGraphQL('administration/utilisateurs/utilisateurs.gql', [
            'limit' => 10,
            'offset' => 0,
            'sortColumn' => 'nom',
            'sortOrder' => 'ASC',
        ]);
        $this->assertSame(16, $response['utilisateurs']['count']);
    }

    /**
     * @throws FilesystemException
     * @throws JsonException
     */
    public function testUtilisateursListAsAdminUser(): void
    {
        $this->login();
        $response = $this->mustSuccessGraphQL('administration/utilisateurs/utilisateurs.gql', [
            'limit' => 10,
            'offset' => 0,
            'sortColumn' => 'nom',
            'sortOrder' => 'ASC',
        ]);
        $this->assertSame(19, $response['utilisateurs']['count']);
    }

    /**
     * @throws FilesystemException
     * @throws JsonException
     */
    public function testUtilisateur(): void
    {
        $this->login();
        /** @var Utilisateur $utilisateur */
        $utilisateur = $this->utilisateurDao->findOneByEmail(CreateUtilisateurs::ADMIN_CENTRAL_EMAIL);
        $this->assertNotNull($utilisateur);
        $response = $this->mustSuccessGraphQL('administration/utilisateurs/utilisateur.gql', [
            'utilisateurID' => $utilisateur->getId(),
        ]);
        $this->assertSame(CreateUtilisateurs::ADMIN_CENTRAL_EMAIL, $response['utilisateur']['email']);
    }

    /**
     * @throws FilesystemException
     * @throws JsonException
     */
    public function testUtilisateurAssertExceptionIfWrongID(): void
    {
        $this->login();
        $this->mustFailGraphQL('administration/utilisateurs/utilisateur.gql', ['utilisateurID' => 'foo']);
    }

    /**
     * @throws FilesystemException
     * @throws JsonException
     */
    public function testSaveUtilisateurNewUtilisateur(): void
    {
        /** @var Role $role */
        $role = $this->roleDao->findAll()->first();
        /** @var Entite $entite */
        $entite = $this->entiteDao->findAll()->first();
        $this->login();
        $response = $this->mustSuccessGraphQL('administration/utilisateurs/saveUtilisateur.gql', [
            'nom' => 'foo',
            'prenom' => 'bar',
            'email' => 'foo@bar.com',
            'motDePasse' => 'baz',
            'estActive' => true,
            'roleID' => $role->getId(),
            'entiteID' => [$entite->getId()],
        ]);
        $this->assertArrayHasKey('id', $response['saveUtilisateur']);
    }

    // FIXME: Failed asserting that 400 matches expected 200.
    /*
        /**
         * @throws FilesystemException
         * @throws JsonException

        public function testSaveUtilisateurUpdateUtilisateur(): void
        {
            /** @var Utilisateur $utilisateur
            $utilisateur = $this->utilisateurDao->findOneByEmail(CreateUtilisateurAdmin::SUPER_ADMIN_EMAIL);
            $this->assertNotNull($utilisateur);
            /** @var Role $role
            $role = $this->roleDao->findAll()->first();
            /** @var Entite $entite
            $entite= $this->entiteDao->findAll()->first();
            $this->login();
            $response = $this->mustSuccessGraphQL('administration/utilisateurs/saveUtilisateur.gql', [
                'nom' => 'foo',
                'prenom' => 'bar',
                'email' => 'foo@bar.com',
                'motDePasse' => 'baz',
                'estActive' => true,
                'roleID' => $role->getId(),
                'entiteID' => [
                    $entite->getId()
                ]
            ]);
            $this->assertArrayHasKey('id', $response['saveUtilisateur']);
            $response = $this->mustSuccessGraphQL('administration/utilisateurs/utilisateur.gql', [
                'utilisateurID' => $utilisateur->getId(),
            ]);
            $this->assertSame('foo', $response['utilisateur']['nom']);
        }
    */

    /**
     * @throws FilesystemException
     * @throws JsonException
     */
    public function testSaveUtilisateurAssertBadRequestIfNewUtilisateurWithExistingEmail(): void
    {
        /** @var Role $role */
        $role = $this->roleDao->findAll()->first();
        /** @var Entite $entite */
        $entite = $this->entiteDao->findAll()->first();
        $this->login();
        $this->mustFailGraphQL('administration/utilisateurs/saveUtilisateur.gql', [
            'nom' => 'foo',
            'prenom' => 'bar',
            'email' => CreateUtilisateurs::ADMIN_CENTRAL_EMAIL,
            'motDePasse' => 'toto',
            'estActive' => true,
            'roleID' => $role->getId(),
            'entiteID' => [$entite->getId()],
        ], 400);
    }

    /**
     * @throws FilesystemException
     * @throws JsonException
     */
    public function testThatUserCanNotDisableItself(): void
    {
        $this->login();

        /** @var Role $role */
        $role = $this->roleDao->findAll()->first();
        /** @var Entite $entite */
        $entite = $this->entiteDao->findAll()->first();

        /** @var Utilisateur $utilisateur */
        $utilisateur = $this->utilisateurDao->findOneByEmail(CreateUtilisateurs::ADMIN_CENTRAL_EMAIL);
        $this->assertNotNull($utilisateur);
        $this->mustFailGraphQL('administration/utilisateurs/saveUtilisateur.gql', [
            'utilisateurID' => $utilisateur->getId(),
            'nom' => 'foo',
            'prenom' => 'bar',
            'email' => $utilisateur->getEmail(),
            'motDePasse' => 'toto',
            'estActive' => false,
            'roleID' => $role->getId(),
            'entiteID' => [$entite->getId()],
        ], 401);
    }
}
