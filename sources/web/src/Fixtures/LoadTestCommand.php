<?php

declare(strict_types=1);

namespace App\Fixtures;

use App\Dao\EnsembleDao;
use App\Dao\EntiteDao;
use App\Dao\RoleDao;
use App\Dao\UtilisateurDao;
use App\Dao\UtilisateurRoleEntiteDao;

final class LoadTestCommand extends LoadCommand
{
    /** @var string  */
    protected static $defaultName = 'fixtures:load:test';

    public function __construct(UtilisateurDao $utilisateurDao, EnsembleDao $ensembleDao, EntiteDao $entiteDao, RoleDao $roleDao, UtilisateurRoleEntiteDao $utilisateurRoleEntiteDao)
    {
        parent::__construct();
        $this->fixtures = [
            new CreateEntites($utilisateurDao, $entiteDao),
            new CreateUtilisateurs($utilisateurDao, $entiteDao),
            new CreateEnsembles($utilisateurDao, $ensembleDao, $entiteDao),
            new JoinUtilisateursRolesEntites($utilisateurRoleEntiteDao, $utilisateurDao, $roleDao, $entiteDao),
        ];
    }

    protected function configure(): void
    {
        $this->setDescription('Loads test fixtures.');
    }
}
