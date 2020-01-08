<?php

declare(strict_types=1);

namespace App\Controller\GraphQL;

use App\Controller\AbstractVisialWebController;
use App\Dao\SimulationDao;
use App\Dao\UtilisateurDao;
use App\Event\SimulationUpdatedEvent;
use App\Exceptions\HTTPException;
use App\Model\ProduitAutre;
use App\Security\SerializableUser;
use App\Services\ExportService;
use App\Services\ProduitAutreService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\TDBM\ResultIterator;
use TheCodingMachine\TDBM\TDBMException;

class ProduitAutreController extends AbstractVisialWebController
{
    /** @var ProduitAutreService */
    private $produitAutreService;

    /** @var ExportService */
    private $exportService;

    /** @var SimulationDao */
    private $simulationDao;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(UtilisateurDao $utilisateurDao, ProduitAutreService $produitAutreService, ExportService $exportService, SimulationDao $simulationDao, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($utilisateurDao);
        $this->produitAutreService = $produitAutreService;
        $this->exportService = $exportService;
        $this->simulationDao = $simulationDao;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return ProduitAutre[]|ResultIterator
     *
     * @throws HTTPException
     *
     * @Query()
     */
    public function produitAutres(string $simulationId, ?int $type): ResultIterator
    {
        $this->validateUser();

        return $this->produitAutreService->findBySimulationAndType($simulationId, $type ??ProduitAutre::TYPE_PRODUIT_EXCEPTIONNEL);
    }

    /**
     * @throws HTTPException
     *
     * @Mutation()
     */
    public function saveProduitAutre(ProduitAutre $produitAutre): ProduitAutre
    {
        $this->validateUser();
        $this->produitAutreService->save($produitAutre);

        $event = new SimulationUpdatedEvent($produitAutre->getSimulation());
        $this->eventDispatcher->dispatch($event);

        return $produitAutre;
    }

    /**
     * @throws TDBMException
     * @throws HTTPException
     *
     * @Mutation()
     */
    public function removeProduitAutre(string $produitAutreUUID, string $simulationId): bool
    {
        $this->validateUser();
        $this->produitAutreService->remove($produitAutreUUID);

        $simulation = $this->simulationDao->getById($simulationId);
        $event = new SimulationUpdatedEvent($simulation);
        $this->eventDispatcher->dispatch($event);

        return true;
    }

    /**
     * @throws HTTPException
     */
    protected function validateUser(): void
    {
        /** @var SerializableUser|null $user */
        $user = $this->getUser();

        if ($user === null || empty($user->getRoles())) {
            throw HTTPException::unauthorized("Vos droits ne vous permettent pas d'effectuer cette opération.");
        }
    }

    /**
     * @Route("/export-produit-autres/{simulationId}", name="exportProduitAutres")
     */
    public function exportProduitAutres(string $simulationId): Response
    {
        $file = $this->exportService->export([$this->exportService::SHEETLIST[15]], $simulationId);

        return $this->file($file['file'], $file['name'], ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     *  @Route("/import-produit-autres/{simulationId}", name="importProduitAutres")
     */
    public function importProduitAutres(Request $request, string $simulationId): Response
    {
        $notification = $this->exportService->import([$this->exportService::SHEETLIST[15]], $request, $simulationId);

        return new Response($notification);
    }
}
