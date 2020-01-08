<?php

declare(strict_types=1);

namespace App\Controller\GraphQL;

use App\Controller\AbstractVisialWebController;
use App\Dao\IndiceTauxDao;
use App\Dao\IndiceTauxPeriodiqueDao;
use App\Dao\SimulationDao;
use App\Dao\UtilisateurDao;
use App\Event\SimulationUpdatedEvent;
use App\Exceptions\ValidatorException;
use App\Model\IndiceTaux;
use App\Model\IndiceTauxPeriodique;
use App\Services\ExportService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\TDBM\AlterableResultIterator;
use TheCodingMachine\TDBM\TDBMException;

final class IndicesTauxController extends AbstractVisialWebController
{
    /** @var IndiceTauxDao */
    private $indicesTauxDao;

    /** @var IndiceTauxPeriodiqueDao*/
    private $indicesTauxPeriodiqueDao;
    /** @var SimulationDao */
    private $simulationDao;
     /** @var ExportService */
    private $exportService;
    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(IndiceTauxDao $indicesTauxDao, IndiceTauxPeriodiqueDao $indicesTauxPeriodiqueDao, SimulationDao $simulationDao, UtilisateurDao $utilisateurDao, ExportService $exportService, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($utilisateurDao);
        $this->indicesTauxDao = $indicesTauxDao;
        $this->indicesTauxPeriodiqueDao = $indicesTauxPeriodiqueDao;
        $this->simulationDao = $simulationDao;
        $this->exportService = $exportService;
        $this->eventDispatcher = $eventDispatcher;
    }

   /**
    * @return IndiceTaux[]|AlterableResultIterator
    *
    * @throws TDBMException
    *
    * @Query()
    */
    public function indicesTaux(string $simulationID): AlterableResultIterator
    {
            return $this->simulationDao->getById($simulationID)->getIndicesTaux();
    }

    /**
     * @Route("/export-indices-taux/{simulationId}", name="exportIndicesTaux")
     */
    public function exportIndicesTaux(string $simulationId): Response
    {
        $file = $this->exportService->export([$this->exportService::SHEETLIST[1]], $simulationId);

        return $this->file($file['file'], $file['name'], ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     * @throws TDBMException
     * @throws ValidatorException
     *
     * @Mutation()
     */
    public function saveIndiceTaux(string $indiceTauxID, ?bool $indexSurInflation, ?float $ecart, string $simulationId): IndiceTaux
    {
        $indiceTaux = $this->indicesTauxDao->getById($indiceTauxID);
        $indiceTaux->setIndexationSurInflation($indexSurInflation);
        $indiceTaux->setEcart($ecart);
        $this->indicesTauxDao->save($indiceTaux);

        $simulation = $this->simulationDao->getById($simulationId);
        $event = new SimulationUpdatedEvent($simulation);
        $this->eventDispatcher->dispatch($event);

        return $indiceTaux;
    }

    /**
     * @throws TDBMException
     * @throws ValidatorException
     *
     * @Mutation()
     */
    public function saveIndiceTauxPeriodique(string $indiceTauxID, int $iteration, ?float $valeur, string $simulationId): IndiceTauxPeriodique
    {
        $indiceTauxPeriodique = $this->indicesTauxPeriodiqueDao->findOneByIndiceTauxIDAndIteration($indiceTauxID, $iteration);
        $indiceTauxPeriodique->setValeur($valeur);
        $this->indicesTauxPeriodiqueDao->save($indiceTauxPeriodique);

        $simulation = $this->simulationDao->getById($simulationId);
        $event = new SimulationUpdatedEvent($simulation);
        $this->eventDispatcher->dispatch($event);

        return $indiceTauxPeriodique;
    }
}
