<?php

declare(strict_types=1);

namespace App\Controller\GraphQL;

use App\Controller\AbstractVisialWebController;
use App\Dao\SimulationDao;
use App\Dao\UtilisateurDao;
use App\Event\SimulationUpdatedEvent;
use App\Model\CessionFoyer;
use App\Security\SerializableUser;
use App\Services\CessionFoyerService;
use App\Services\ExportService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\TDBM\ResultIterator;
use Throwable;

class CessionFoyersController extends AbstractVisialWebController
{
    /** @var CessionFoyerService */
    private $cessionFoyerService;

    /** @var SimulationDao */
    private $simulationDao;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var ExportService */
    private $exportService;

    public function __construct(UtilisateurDao $utilisateurDao, CessionFoyerService $cessionFoyerService, SimulationDao $simulationDao, EventDispatcherInterface $eventDispatcher, ExportService $exportService)
    {
        parent::__construct($utilisateurDao);
        $this->cessionFoyerService = $cessionFoyerService;
        $this->simulationDao = $simulationDao;
        $this->eventDispatcher = $eventDispatcher;
        $this->exportService = $exportService;
    }

    /**
     * @return CessionFoyer[]|ResultIterator
     *
     * @Query()
     */
    public function cessionFoyers(string $simulationId): ResultIterator
    {
        $this->validateUser();

        return $this->cessionFoyerService->findBySimulation($simulationId);
    }

    /**
     * @Mutation()
     */
    public function saveCessionFoyer(CessionFoyer $cessionFoyer): CessionFoyer
    {
        $this->validateUser();

        try {
            $this->cessionFoyerService->save($cessionFoyer);

            $event = new SimulationUpdatedEvent($cessionFoyer->getSimulation());
            $this->eventDispatcher->dispatch($event);
        } catch (Throwable $e) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Cette Cession foyer existe déjà', $e);
        }

        return $cessionFoyer;
    }

    /**
     * @Mutation()
     */
    public function removeCessionFoyer(string $cessionFoyerUUID, string $simulationId): bool
    {
        $this->validateUser();

        try {
            $this->cessionFoyerService->remove($cessionFoyerUUID);

            $simulation = $this->simulationDao->getById($simulationId);
            $event = new SimulationUpdatedEvent($simulation);
            $this->eventDispatcher->dispatch($event);
        } catch (Throwable $e) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Cette Cession foyer n\'existe pas', $e);
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

    /**
     * @Route("/export-cession-foyers/{simulationId}", name="exportCessionFoyers")
     */
    public function exportCessionFoyers(string $simulationId): Response
    {
        $file = $this->exportService->export([$this->exportService::SHEETLIST[24]], $simulationId);

        return $this->file($file['file'], $file['name'], ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     *  @Route("/import-cession-foyers/{simulationId}", name="importCessionFoyers")
     */
    public function importCessionFoyers(Request $request, string $simulationId): Response
    {
        $notification = $this->exportService->import([$this->exportService::SHEETLIST[24]], $request, $simulationId);

        return new Response($notification);
    }
}
