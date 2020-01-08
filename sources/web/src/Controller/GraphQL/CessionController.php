<?php

declare(strict_types=1);

namespace App\Controller\GraphQL;

use App\Controller\AbstractVisialWebController;
use App\Dao\SimulationDao;
use App\Dao\UtilisateurDao;
use App\Event\SimulationUpdatedEvent;
use App\Exceptions\HTTPException;
use App\Model\Cession;
use App\Security\SerializableUser;
use App\Services\CessionService;
use App\Services\ExportService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\TDBM\ResultIterator;
use Throwable;

class CessionController extends AbstractVisialWebController
{
    /** @var CessionService */
    private $cessionService;
    /** @var ExportService */
    private $exportService;

    /** @var SimulationDao */
    private $simulationDao;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(UtilisateurDao $utilisateurDao, CessionService $cessionService, ExportService $exportService, SimulationDao $simulationDao, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($utilisateurDao);
        $this->cessionService = $cessionService;
        $this->exportService = $exportService;
        $this->simulationDao = $simulationDao;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return Cession[]|ResultIterator
     *
     * @Query()
     */
    public function cessions(string $simulationId): ResultIterator
    {
        $this->validateUser();

        return $this->cessionService->findBySimulation(
            $simulationId
        );
    }

    /**
     * @Mutation()
     */
    public function saveCession(Cession $cession): Cession
    {
        $this->validateUser();

        try {
            $this->cessionService->save($cession);

            $event = new SimulationUpdatedEvent($cession->getSimulation());
            $this->eventDispatcher->dispatch($event);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Cette Cession existe déjà', $e);
        }

        return $cession;
    }

    /**
     * @Mutation()
     */
    public function removeCession(string $cessionUUID, string $simulationId): bool
    {
        $this->validateUser();

        try {
            $this->cessionService->remove($cessionUUID);

            $simulation = $this->simulationDao->getById($simulationId);
            $event = new SimulationUpdatedEvent($simulation);
            $this->eventDispatcher->dispatch($event);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Cette Cession existe déjà', $e);
        }

        return true;
    }

    /**
     * @throws HTTPException
     */
    protected function validateUser(): void
    {
        /** @var SerializableUser $user */
        $user = $this->getUser();

        if (empty($user) || empty($user->getRoles())) {
            throw HTTPException::unauthorized("Vos droits ne vous permettent pas d'effectuer cette opération.");
        }
    }

    /**
     * @Route("/export-cession-identifiees/{simulationId}", name="exportCessionIdentifiees")
     */
    public function exportCessionIdentifiees(string $simulationId): Response
    {
        $file = $this->exportService->export([$this->exportService::SHEETLIST[19]], $simulationId);

        return $this->file($file['file'], $file['name'], ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     *  @Route("/import-cession-identifiees/{simulationId}", name="importCessionIdentifiees")
     */
    public function importCessionIdentifiees(Request $request, string $simulationId): Response
    {
        $notification = $this->exportService->import([$this->exportService::SHEETLIST[19]], $request, $simulationId);

        return new Response($notification);
    }
}
