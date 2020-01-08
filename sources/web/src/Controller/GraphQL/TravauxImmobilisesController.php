<?php

declare(strict_types=1);

namespace App\Controller\GraphQL;

use App\Controller\AbstractVisialWebController;
use App\Dao\SimulationDao;
use App\Dao\UtilisateurDao;
use App\Event\SimulationUpdatedEvent;
use App\Exceptions\HTTPException;
use App\Model\TravauxImmobilise;
use App\Security\SerializableUser;
use App\Services\ExportService;
use App\Services\TravauxImmobilisesService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\TDBM\ResultIterator;
use TheCodingMachine\TDBM\TDBMException;
use Throwable;

class TravauxImmobilisesController extends AbstractVisialWebController
{
    /** @var TravauxImmobilisesService */
    private $travauxImmobilisesService;

     /** @var ExportService */
    private $exportService;

    /** @var SimulationDao */
    private $simulationDao;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(UtilisateurDao $utilisateurDao, TravauxImmobilisesService $travauxImmobilisesService, ExportService $exportService, SimulationDao $simulationDao, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($utilisateurDao);
        $this->travauxImmobilisesService = $travauxImmobilisesService;
        $this->exportService = $exportService;
        $this->simulationDao = $simulationDao;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return TravauxImmobilise[]|ResultIterator
     *
     * @throws HTTPException
     *
     * @Query()
     */
    public function travauxImmobilises(string $simulationId): ResultIterator
    {
        $this->validateUser();

        return $this->travauxImmobilisesService->findBySimulation(
            $simulationId
        );
    }

    /**
     * @throws HTTPException
     *
     * @Mutation()
     */
    public function saveTravauxImmobilise(TravauxImmobilise $travauxImmobilise): TravauxImmobilise
    {
        $this->validateUser();

        try {
            $this->travauxImmobilisesService->save($travauxImmobilise);

            $event = new SimulationUpdatedEvent($travauxImmobilise->getSimulation());
            $this->eventDispatcher->dispatch($event);
        } catch (Throwable $e) {
            throw HTTPException::badRequest('Ce Lotissement existe déjà', $e);
        }

        return $travauxImmobilise;
    }

    /**
     * @throws TDBMException
     * @throws HTTPException
     *
     * @Mutation()
     */
    public function removeTravauxImmobilise(string $travauxImmobiliseUUID, string $simulationId): bool
    {
        $this->validateUser();

        $this->travauxImmobilisesService->remove($travauxImmobiliseUUID);

        $simulation = $this->simulationDao->getById($simulationId);
        $event = new SimulationUpdatedEvent($simulation);
        $this->eventDispatcher->dispatch($event);

        return true;
    }

    /**
     * @throws HTTPException
     *
     * @Mutation()
     */
    public function removeTypeDempruntTravauxImmobilise(string $typesEmpruntsUUID, string $travauxImmobiliseUUID): bool
    {
        $this->validateUser();

        $this->travauxImmobilisesService->removeTypeDempruntTravauxImmobilise($typesEmpruntsUUID, $travauxImmobiliseUUID);

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
     * @Route("/export-travaux-immobilises-identifiees/{simulationId}", name="exportTravauxImmobilisesIdentifiees")
     */
    public function exportTravauxImmobilisesIdentifiees(string $simulationId): Response
    {
        $file = $this->exportService->export([$this->exportService::SHEETLIST[17]], $simulationId);

        return $this->file($file['file'], $file['name'], ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     *  @Route("/import-travaux-immobilises-identifiees/{simulationId}", name="importTravauxImmobilisesIdentifiees")
     */
    public function importTravauxImmobilisesIdentifiees(Request $request, string $simulationId): Response
    {
        $notification = $this->exportService->import([$this->exportService::SHEETLIST[17]], $request, $simulationId);

        return new Response($notification);
    }

    /**
     * @Route("/export-travaux-immobilises-non-identifiees/{simulationId}", name="exportTravauxImmobilisesNonIdentifiees")
     */
    public function exportTravauxImmobilisesNonIdentifiees(string $simulationId): Response
    {
        $file = $this->exportService->export([$this->exportService::SHEETLIST[18]], $simulationId);

        return $this->file($file['file'], $file['name'], ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     *  @Route("/import-travaux-immobilises-non-identifiees/{simulationId}", name="importTravauxImmobilisesNonIdentifiees")
     */
    public function importTravauxImmobilisesNonIdentifiees(Request $request, string $simulationId): Response
    {
        $notification = $this->exportService->import([$this->exportService::SHEETLIST[18]], $request, $simulationId);

        return new Response($notification);
    }

    /**
     * @Route("/export-renouvellement-composant/{simulationId}", name="exportRenouvellementComposant")
     */
    public function exportRenouvellementComposant(string $simulationId): Response
    {
        $file = $this->exportService->export([$this->exportService::SHEETLIST[21]], $simulationId);

        return $this->file($file['file'], $file['name'], ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     *  @Route("/import-renouvellement-composant/{simulationId}", name="importRenouvellementComposant")
     */
    public function importRenouvellementComposant(Request $request, string $simulationId): Response
    {
        $notification = $this->exportService->import([$this->exportService::SHEETLIST[21]], $request, $simulationId);

        return new Response($notification);
    }
}
