<?php

declare(strict_types=1);

namespace App\Controller\GraphQL;

use App\Controller\AbstractVisialWebController;
use App\Dao\SimulationDao;
use App\Dao\UtilisateurDao;
use App\Event\SimulationUpdatedEvent;
use App\Model\Psla;
use App\Security\SerializableUser;
use App\Services\ExportService;
use App\Services\PslaService;
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

class PslaController extends AbstractVisialWebController
{
    /** @var PslaService */
    private $pslaService;

    /** @var SimulationDao */
    private $simulationDao;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var ExportService */
    private $exportService;

    public function __construct(UtilisateurDao $utilisateurDao, PslaService $pslaService, SimulationDao $simulationDao, EventDispatcherInterface $eventDispatcher, ExportService $exportService)
    {
        parent::__construct($utilisateurDao);
        $this->pslaService = $pslaService;
        $this->simulationDao = $simulationDao;
        $this->eventDispatcher = $eventDispatcher;
        $this->exportService = $exportService;
    }

    /**
     * @return Psla[]|ResultIterator
     *
     * @Query()
     */
    public function pslas(string $simulationId, ?int $type): ResultIterator
    {
        $this->validateUser();

        return $this->pslaService->findBySimulationAndType(
            $simulationId,
            $type ?? Psla::TYPE_IDENTIFIEE
        );
    }

    /**
     * @Mutation()
     */
    public function savePsla(Psla $psla): Psla
    {
        $this->validateUser();

        try {
            $this->pslaService->save($psla);

            $event = new SimulationUpdatedEvent($psla->getSimulation());
            $this->eventDispatcher->dispatch($event);
        } catch (Throwable $e) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Ce Psla existe déjà', $e);
        }

        return $psla;
    }

    /**
     * @Mutation()
     */
    public function removePsla(string $pslaUUID, string $simulationId): bool
    {
        $this->validateUser();

        try {
            $this->pslaService->remove($pslaUUID);

            $simulation = $this->simulationDao->getById($simulationId);
            $event = new SimulationUpdatedEvent($simulation);
            $this->eventDispatcher->dispatch($event);
        } catch (Throwable $e) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Ce Psla existe déjà', $e);
        }

        return true;
    }

    /**
     * @Mutation()
     */
    public function removeTypeDempruntPsla(string $typesEmpruntsUUID, string $pslaUUID): bool
    {
        $this->validateUser();

        try {
            $this->pslaService->removeTypeDempruntPsla($typesEmpruntsUUID, $pslaUUID);
        } catch (Throwable $e) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Ce TypeDemprunt Psla existe déjà', $e);
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
     * @Route("/export-psla-identifiees/{simulationId}", name="exportPslaIdentifiees")
     */
    public function exportPslaIdentifiees(string $simulationId): Response
    {
        $file = $this->exportService->export([$this->exportService::SHEETLIST[28]], $simulationId);

        return $this->file($file['file'], $file['name'], ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     *  @Route("/import-psla-identifiees/{simulationId}", name="importPslaIdentifiees")
     */
    public function importPslaIdentifiees(Request $request, string $simulationId): Response
    {
        $notification = $this->exportService->import([$this->exportService::SHEETLIST[28]], $request, $simulationId);

        return new Response($notification);
    }
}
