<?php

declare(strict_types=1);

namespace App\Controller\GraphQL;

use App\Controller\AbstractVisialWebController;
use App\Dao\SimulationDao;
use App\Dao\UtilisateurDao;
use App\Event\SimulationUpdatedEvent;
use App\Exceptions\HTTPException;
use App\Model\Loyer;
use App\Security\SerializableUser;
use App\Services\ExportService;
use App\Services\LoyerService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\TDBM\ResultIterator;
use TheCodingMachine\TDBM\TDBMException;

class LoyerController extends AbstractVisialWebController
{
    /** @var LoyerService */
    private $loyerService;

    /** @var ExportService */
    private $exportService;

    /** @var SimulationDao */
    private $simulationDao;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(UtilisateurDao $utilisateurDao, LoyerService $loyerService, ExportService $exportService, SimulationDao $simulationDao, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($utilisateurDao);
        $this->loyerService = $loyerService;
        $this->exportService = $exportService;
        $this->simulationDao = $simulationDao;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return Loyer[]|ResultIterator
     *
     * @throws HTTPException
     *
     * @Query()
     */
    public function loyers(string $simulationId, ?int $type): ResultIterator
    {
        /** @var SerializableUser|null $user */
        $user = $this->getUser();

        if ($user === null || empty($user->getRoles())) {
            throw HTTPException::unauthorized("Vos droits ne vous permettent pas d'effectuer cette opération.");
        }

        return $this->loyerService->findBySimulationAndType($simulationId, $type ??Loyer::TYPE_AUTRES_LOYER);
    }

    /**
     * @throws HTTPException
     *
     * @Mutation()
     */
    public function saveLoyer(Loyer $loyer): Loyer
    {
        /** @var SerializableUser|null $user */
        $user = $this->getUser();

        if ($user === null || empty($user->getRoles())) {
            throw HTTPException::unauthorized("Vos droits ne vous permettent pas d'effectuer cette opération.");
        }

        $this->loyerService->save($loyer);

        $event = new SimulationUpdatedEvent($loyer->getSimulation());
        $this->eventDispatcher->dispatch($event);

        return $loyer;
    }

    /**
     * @throws TDBMException
     * @throws HTTPException
     *
     * @Mutation()
     */
    public function removeLoyer(string $loyerUUID, string $simulationId): bool
    {
        /** @var SerializableUser|null $user */
        $user = $this->getUser();

        if ($user === null || empty($user->getRoles())) {
            throw HTTPException::unauthorized("Vos droits ne vous permettent pas d'effectuer cette opération.");
        }

        $this->loyerService->remove($loyerUUID);

        $simulation = $this->simulationDao->getById($simulationId);
        $event = new SimulationUpdatedEvent($simulation);
        $this->eventDispatcher->dispatch($event);

        return true;
    }

    /**
     * @Route("/export-produit-loyer/{simulationId}", name="exportProduitLoyer")
     */
    public function exportProduitLoyer(string $simulationId): Response
    {
        $file = $this->exportService->export([$this->exportService::SHEETLIST[13]], $simulationId);

        return $this->file($file['file'], $file['name'], ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     *  @Route("/import-produit-loyer/{simulationId}", name="importProduitLoyer")
     */
    public function importProduitLoyer(Request $request, string $simulationId): Response
    {
        $notification = $this->exportService->import([$this->exportService::SHEETLIST[13]], $request, $simulationId);

        return new Response($notification);
    }
}
