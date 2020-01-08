<?php

declare(strict_types=1);

namespace App\Controller\GraphQL;

use App\Controller\AbstractVisialWebController;
use App\Dao\SimulationDao;
use App\Dao\UtilisateurDao;
use App\Event\SimulationUpdatedEvent;
use App\Exceptions\HTTPException;
use App\Model\Simulation;
use App\Model\TypeEmprunt;
use App\Model\TypeEmpruntPeriodique;
use App\Security\SerializableUser;
use App\Services\ExportService;
use App\Services\TypeEmpruntService;
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

class TypesEmpruntsController extends AbstractVisialWebController
{
    /** @var TypeEmpruntService */
    private $typeEmpruntService;

    /** @var ExportService */
    private $exportService;

    /** @var SimulationDao */
    private $simulationDao;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(UtilisateurDao $utilisateurDao, TypeEmpruntService $typeEmpruntService, ExportService $exportService, SimulationDao $simulationDao, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($utilisateurDao);
        $this->typeEmpruntService = $typeEmpruntService;
        $this->exportService = $exportService;
        $this->simulationDao = $simulationDao;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return TypeEmprunt[]|ResultIterator
     *
     * @throws HTTPException
     *
     * @Query()
     */
    public function typesEmprunts(string $simulationID): ResultIterator
    {
        /** @var SerializableUser|null $user */
        $user = $this->getUser();

        if ($user === null || empty($user->getRoles())) {
            throw HTTPException::unauthorized("Vos droits ne vous permettent pas d'effectuer cette opération.");
        }

        return $this->typeEmpruntService->fetchBySimulationId($simulationID);
    }

    /**
     * @throws HTTPException
     *
     * @Mutation()
     */
    public function saveTypeEmprunt(TypeEmprunt $typeEmprunt): TypeEmprunt
    {
        $this->typeEmpruntService->save($typeEmprunt);

        /** @var Simulation $simulation */
        $simulation = $typeEmprunt->getSimulation();
        $event = new SimulationUpdatedEvent($simulation);
        $this->eventDispatcher->dispatch($event);

        return $typeEmprunt;
    }

    /**
     * @throws TDBMException
     *
     * @Mutation()
     */
    public function saveTypeEmpruntPeriodique(
        string $id,
        int $iteration,
        float $tauxInteretInitial,
        float $tauxPremiereAnnuitePayee
    ): TypeEmpruntPeriodique {
        $typeEmpruntPeriodique = $this->typeEmpruntService->findTypeEmpruntPeriodique($id, $iteration);
        $typeEmpruntPeriodique->setTauxInteretInitial($tauxInteretInitial);
        $typeEmpruntPeriodique->setTauxPremiereAnnuitePayee($tauxPremiereAnnuitePayee);

        $this->typeEmpruntService->saveTypeEmpruntPeriodique($typeEmpruntPeriodique);

        return $typeEmpruntPeriodique;
    }

    /**
     * @throws TDBMException
     * @throws HTTPException
     *
     * @Mutation()
     */
    public function removeTypeEmprunt(string $id, string $simulationId): bool
    {
        /** @var SerializableUser|null $user */
        $user = $this->getUser();

        if ($user === null || empty($user->getRoles())) {
            throw HTTPException::unauthorized("Vos droits ne vous permettent pas d'effectuer cette opération.");
        }

        try {
            $this->typeEmpruntService->remove($id);

            $simulation = $this->simulationDao->getById($simulationId);
            $event = new SimulationUpdatedEvent($simulation);
            $this->eventDispatcher->dispatch($event);
        } catch (Throwable $e) {
            throw HTTPException::forbidden('Ce type d\'emprunt ne peut être supprimé car il est utilisé', $e);
        }

        return true;
    }

     /**
      * @Route("/export-types-emprunts/{simulationId}", name="exportTypesEmprunts")
      */
    public function exportTypesEmprunts(string $simulationId): Response
    {
        $file = $this->exportService->export([$this->exportService::SHEETLIST[6]], $simulationId);

        return $this->file($file['file'], $file['name'], ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     *  @Route("/import-types-emprunts/{simulationId}", name="importTypesEmprunts")
     */
    public function importTypesEmprunts(Request $request, string $simulationId): Response
    {
        $notification = $this->exportService->import([$this->exportService::SHEETLIST[6]], $request, $simulationId);

        return new Response($notification);
    }
}
