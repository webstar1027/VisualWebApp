<?php

declare(strict_types=1);

namespace App\Controller\GraphQL;

use App\Controller\AbstractVisialWebController;
use App\Dao\UtilisateurDao;
use App\Model\TableauDeBord;
use App\Security\SerializableUser;
use App\Services\TableauBordService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use Throwable;

class TableauBordController extends AbstractVisialWebController
{
    /** @var TableauBordService */
    private $tableauBordService;

    public function __construct(UtilisateurDao $utilisateurDao, TableauBordService $tableauBordService)
    {
        parent::__construct($utilisateurDao);
        $this->tableauBordService = $tableauBordService;
    }

    /**
     * @Query()
     */
    public function tableauDeBord(string $simulationId): ?TableauDeBord
    {
        $this->validateUser();

        return $this->tableauBordService->findBySimulation($simulationId);
    }

    /**
     * @Mutation()
     */
    public function saveTableauDeBord(TableauDeBord $tableauDeBord): TableauDeBord
    {
        $this->validateUser();

        try {
            $this->tableauBordService->save($tableauDeBord);
        } catch (Throwable $e) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Ce Tableau De Bord existe déjà', $e);
        }

        return $tableauDeBord;
    }

    /**
     * @Mutation()
     */
    public function removeTableauDeBord(string $tableauDeBordUUID): bool
    {
        $this->validateUser();

        try {
            $this->tableauBordService->remove($tableauDeBordUUID);
        } catch (Throwable $e) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Ce Tableau De Bord existe déjà', $e);
        }

        return true;
    }

    /**
     * @throws HttpException
     */
    protected function validateUser(): void
    {
        /** @var SerializableUser $user */
        $user = $this->getUser();

        if (empty($user) || empty($user->getRoles())) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "Vos droits ne vous permettent pas d'effectuer cette opération.");
        }
    }
}
