<?php

declare(strict_types=1);

namespace App\Controller\GraphQL;

use App\Controller\AbstractVisialWebController;
use App\Dao\DroitDao;
use App\Dao\RoleDao;
use App\Dao\UtilisateurDao;
use App\Exceptions\HTTPException;
use App\Model\Role;
use Cake\Chronos\Chronos;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\TDBM\ResultIterator;
use TheCodingMachine\TDBM\TDBMException;
use function array_map;

final class RolesController extends AbstractVisialWebController
{
    /** @var RoleDao */
    private $roleDao;

    /** @var DroitDao */
    private $droitDao;

    public function __construct(UtilisateurDao $utilisateurDao, RoleDao $roleDao, DroitDao $droitDao)
    {
        parent::__construct($utilisateurDao);
        $this->roleDao = $roleDao;
        $this->droitDao = $droitDao;
    }

    /**
     * @param string[] $droits
     *
     * @return Role[]|ResultIterator
     *
     * @Query()
     * @Logged()
     */
    public function roles(?string $nom, ?string $description, ?array $droits, string $sortColumn, string $sortOrder): ResultIterator
    {
        return $this->roleDao->findByFilters($nom, $description, $droits, $sortColumn, $sortOrder);
    }

    /**
     * @return Role[]|ResultIterator
     *
     * @Query()
     * @Logged()
     */
    public function allRoles()
    {
        return $this->roleDao->findAll();
    }

    /**
     * @throws TDBMException
     *
     * @Query()
     * @Logged()
     */
    public function role(string $roleID): Role
    {
        return $this->roleDao->getById($roleID);
    }

    /**
     * @param string[] $droits
     *
     * @throws HTTPException
     * @throws TDBMException
     *
     * @Mutation()
     * @Logged()
     */
    public function saveRole(?string $roleID, string $nom, ?string $description, array $droits): Role
    {
        if (empty($roleID)) {
            // we are creating a role.
            $newRole = new Role(
                $nom,
                Chronos::now()
            );
            $newRole->setDescription($description);

            $droitsObjects = array_map(function ($id) {
                if ($id !== null) {
                    return $this->droitDao->getById($id);
                }
            }, $droits);

            $newRole->setDroits($droitsObjects);

            $existingRoleNom = $this->roleDao->findOneByNom($nom);
            if (isset($existingRoleNom)) {
                throw HTTPException::badRequest('Ce rôle existe déjà.');
            }
            $this->roleDao->save($newRole);

            return $newRole;
        }
        // we are updating a Role.
        $existingRole = $this->roleDao->getById($roleID);
        $existingRole->setNom($nom);
        $existingRole->setDescription($description);
        $existingRole->setDateModification(Chronos::now());

        $droitsObjects = array_map(function ($id) {
            if ($id !== null) {
                return $this->droitDao->getById($id);
            }
        }, $droits);

        $existingRole->setDroits($droitsObjects);

        $this->roleDao->save($existingRole);

        return $existingRole;
    }

    /**
     * @Mutation()
     * @Logged()
     */
    public function deleteRole(string $roleID): Role
    {
        $role = $this->roleDao->getById($roleID);
        $role->setEstVisible(false);
        $this->roleDao->save($role);

        return $role;
    }

    /**
     * @Mutation()
     * @Logged()
     */
    public function activateRole(string $roleID): Role
    {
        $role = $this->roleDao->getById($roleID);
        $role->setEstVisible(true);
        $this->roleDao->save($role);

        return $role;
    }
}
