<?php

declare(strict_types=1);

namespace App\Controller\GraphQL;

use App\Controller\AbstractVisialWebController;
use App\Dao\SimulationDao;
use App\Dao\UtilisateurDao;
use App\Event\SimulationUpdatedEvent;
use App\Model\PortageTresorerie;
use App\Model\PortageTresorerieParametre;
use App\Services\ExportService;
use App\Services\PortageTresorerieService;
use Safe\Exceptions\StringsException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\TDBM\AlterableResultIterator;
use TheCodingMachine\TDBM\TDBMException;

class PortageTresorerieController extends AbstractVisialWebController
{
    /** @var SimulationDao */
    private $simulationDao;

    /** @var PortageTresorerieService */
    private $portageTresorerieService;

    /** @var ExportService */
    private $exportService;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        UtilisateurDao $utilisateurDao,
        SimulationDao $simulationDao,
        PortageTresorerieService $portageTresorerieService,
        ExportService $exportService,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($utilisateurDao);
        $this->simulationDao = $simulationDao;
        $this->portageTresorerieService = $portageTresorerieService;
        $this->exportService = $exportService;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return PortageTresorerie[]|AlterableResultIterator
     *
     * @throws TDBMException
     *
     * @Query()
     */
    public function portageTresorerie(string $simulationID): AlterableResultIterator
    {
        return $this->simulationDao->getById($simulationID)->getPortageTresorerie();
    }

    /**
     * @throws TDBMException
     * @throws StringsException
     *
     * @Mutation()
     */
    public function savePortageTresoreriePeriodique(PortageTresorerie $portageTresorerie): PortageTresorerie
    {
        $this->portageTresorerieService->save($portageTresorerie);

        $event = new SimulationUpdatedEvent($portageTresorerie->getSimulation());
        $this->eventDispatcher->dispatch($event);

        return $portageTresorerie;
    }

    /**
     * @return PortageTresorerieParametre[]|AlterableResultIterator
     *
     * @throws TDBMException
     *
     * @Query()
     */
    public function portageTresorerieParametre(string $simulationID): AlterableResultIterator
    {
        return $this->simulationDao->getById($simulationID)->getPortageTresorerieParametre();
    }

    /**
     * @throws TDBMException
     * @throws StringsException
     *
     * @Mutation()
     */
    public function savePortageTresorerieParametre(string $simulationID, ?float $soldeEmplois, ?float $detteFournisseurs, ?float $promotionAccession, ?float $tauxInteretFinancement, ?float $tauxInteretConcours): ?PortageTresorerieParametre
    {
        return $this->portageTresorerieService->saveParametre($simulationID, $soldeEmplois, $detteFournisseurs, $promotionAccession, $tauxInteretFinancement, $tauxInteretConcours);
    }

    /**
     * @Route("/export-portage-tresorerie/{simulationId}", name="exportPortageTresorerie")
     */
    public function exportPortageTresorerie(string $simulationId): Response
    {
        $file = $this->exportService->export([$this->exportService::SHEETLIST[14]], $simulationId);

        return $this->file($file['file'], $file['name'], ResponseHeaderBag::DISPOSITION_INLINE);
    }
}
