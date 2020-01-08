<?php

declare(strict_types=1);

namespace App\Tests\Controller\GraphQL;

use App\Model\Droit;
use App\Model\Role;
use Safe\Exceptions\FilesystemException;
use Safe\Exceptions\JsonException;

final class RolesControllerTest extends AbstractGraphQLControllerTestCase
{
    // Test GraphQL queries

    public const NOM_ROLE_USER = 'Utilisateur';

    /**
     * @throws FilesystemException
     * @throws JsonException
     */
    public function testThatWeHaveOneRole(): void
    {
        $this->login();
        $response = $this->mustSuccessGraphQL('administration/roles/allRoles.gql');
        $this->assertCount(1, $response['allRoles']);
    }

    // FIXME: Failed asserting that 500 matches expected 200
    /*
     * @throws FilesystemException
     * @throws JsonException

    public function testRolesFilter(): void
    {
        $this->login();
        $response = $this->mustSuccessGraphQL('administration/roles/roles.gql', [
            'limit' => 10,
            'offset' => 0,
            'sortColumn' => 'nom',
            'sortOrder' => 'ASC',
            'nom' => self::NOM_ROLE_USER,
        ]);
        $this->assertSame(self::NOM_ROLE_USER, $response['roles']['items'][0]['nom']);
    }
*/

    /**
     * @throws FilesystemException
     * @throws JsonException
     */
    public function testTheRoleQuery(): void
    {
        $this->login();
        /** @var Role $role */
        $role = $this->roleDao->findOneByNom(self::NOM_ROLE_USER);
        $this->assertNotNull($role);
        $response = $this->mustSuccessGraphQL('administration/roles/role.gql', [
            'roleID' => $role->getId(),
        ]);
        $this->assertSame(self::NOM_ROLE_USER, $response['role']['nom']);
    }

    // Test GraphQL mutations

    /**
     * @throws FilesystemException
     * @throws JsonException
     */
    public function testThatNewRoleIsAdded(): void
    {
        $this->login();
        /** @var Droit $droits */
        $droit = $this->droitDao->findAll()->first();
        $response = $this->mustSuccessGraphQL('administration/roles/saveRole.gql', [
            'nom' => 'foo',
            'droits' => [$droit->getId()],
        ]);
        $this->assertArrayHasKey('id', $response['saveRole']);
    }

    /**
     * @throws FilesystemException
     * @throws JsonException
     */
    public function testBadRequestIfWeAttemptToAddAnExistingRole(): void
    {
        $this->login();
        /** @var Droit $droits */
        $droit = $this->droitDao->findAll()->first();
        $this->mustFailGraphQL('administration/roles/saveRole.gql', [
            'nom' => self::NOM_ROLE_USER,
            'droits' => [$droit->getId()],
        ], 400);
    }

    /**
     * @throws FilesystemException
     * @throws JsonException
     */
    public function testThatRoleMutationUpdatesTheRole(): void
    {
        $this->login();
        /** @var Droit $droits */
        $droit = $this->droitDao->findAll()->first();
        /** @var Role $role */
        $role = $this->roleDao->findOneByNom(self::NOM_ROLE_USER);
        $this->assertNotNull($role);
        $response = $this->mustSuccessGraphQL('administration/roles/saveRole.gql', [
            'roleID' => $role->getId(),
            'nom' => self::NOM_ROLE_USER . ' Updated',
            'droits' => [$droit->getId()],
        ]);
        $this->assertArrayHasKey('id', $response['saveRole']);
        $response = $this->mustSuccessGraphQL('administration/roles/role.gql', [
            'roleID' => $role->getId(),
        ]);
        $this->assertSame(self::NOM_ROLE_USER . ' Updated', $response['role']['nom']);
    }
}
