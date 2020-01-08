<?php

declare(strict_types=1);

namespace App\Controller\GraphQL;

use App\Controller\AbstractVisialWebController;
use App\Dao\DroitDao;
use App\Dao\UtilisateurDao;
use App\Model\Droit;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\TDBM\ResultIterator;

class DroitsController extends AbstractVisialWebController
{
    /** @var DroitDao */
    private $droitDao;

    public function __construct(UtilisateurDao $utilisateurDao, DroitDao $droitDao)
    {
        parent::__construct($utilisateurDao);
        $this->droitDao = $droitDao;
    }

    /**
     * @return Droit[]|ResultIterator
     *
     * @Query()
     */
    public function droits()
    {
        return $this->droitDao->findAll();
    }
}
