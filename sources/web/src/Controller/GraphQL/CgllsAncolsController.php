<?php

declare(strict_types=1);

namespace App\Controller\GraphQL;

use App\Controller\AbstractVisialWebController;
use App\Dao\CgllAncolDao;
use App\Dao\CgllAncolParametreDao;
use App\Dao\CgllAncolPeriodiqueDao;
use App\Dao\SimulationDao;
use App\Dao\UtilisateurDao;
use App\Event\SimulationUpdatedEvent;
use App\Model\CgllAncol;
use App\Model\CgllAncolParametre;
use App\Model\CgllAncolPeriodique;
use App\Services\ExportService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\TDBM\AlterableResultIterator;
use TheCodingMachine\TDBM\TDBMException;
use function Safe\json_decode;

class CgllsAncolsController extends AbstractVisialWebController
{
    /** @var CgllAncolDao */
    private $cgllAncolDao;

    /** @var CgllAncolParametreDao */
    private $cgllAncolParametreDao;

    /** @var CgllAncolPeriodiqueDao */
    private $cgllsAncolsPeriodiqueDao;

    /** @var SimulationDao */
    private $simulationDao;

    /** @var ExportService */
    private $exportService;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(UtilisateurDao $utilisateurDao, CgllAncolDao $cgllAncolDao, CgllAncolParametreDao $cgllAncolParametreDao, CgllAncolPeriodiqueDao $cgllsAncolsPeriodiqueDao, ExportService $exportService, SimulationDao $simulationDao, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($utilisateurDao);
        $this->cgllAncolDao = $cgllAncolDao;
        $this->cgllsAncolsPeriodiqueDao = $cgllsAncolsPeriodiqueDao;
        $this->cgllAncolParametreDao = $cgllAncolParametreDao;
        $this->exportService = $exportService;
        $this->simulationDao = $simulationDao;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return CgllAncol[]|AlterableResultIterator
     *
     * @throws TDBMException
     *
     * @Query()
     */
    public function cgllsAncols(string $simulationID): AlterableResultIterator
    {
        return $this->simulationDao->getById($simulationID)->getCgllsAncols();
    }

    /**
     * @throws TDBMException
     *
     * @Mutation()
     */
    public function saveCgllsAncol(string $cgllsAncolsID, string $simulationID, string $periodique): CgllAncol
    {
        $cgllAncol = $this->cgllAncolDao->getById($cgllsAncolsID);
        $periodique = json_decode($periodique);

        foreach ($periodique->periodique as $key => $valeur) {
            $iteration = $key + 1;

            $cgllsAncolsPeriodique = $this->cgllsAncolsPeriodiqueDao->findOneByCgllsAncolsIDAndIteration($cgllsAncolsID, $iteration);
            $cgllsAncolsPeriodique->setValeur($valeur);
            $this->cgllsAncolsPeriodiqueDao->save($cgllsAncolsPeriodique);
        }

        $simulation = $this->simulationDao->getById($simulationID);
        $event = new SimulationUpdatedEvent($simulation);
        $this->eventDispatcher->dispatch($event);

        return $cgllAncol;
    }

    /**
     * @throws TDBMException
     *
     * @Mutation()
     */
    public function saveCgllsAncolsPeriodique(string $cgllsAncolsID, int $iteration, ?float $valeur, string $simulationID): CgllAncolPeriodique
    {
        $cgllsAncolsPeriodique = $this->cgllsAncolsPeriodiqueDao->findOneByCgllsAncolsIDAndIteration($cgllsAncolsID, $iteration);
        $cgllsAncolsPeriodique->setValeur($valeur);
        $this->cgllsAncolsPeriodiqueDao->save($cgllsAncolsPeriodique);

        $simulation = $this->simulationDao->getById($simulationID);
        $event = new SimulationUpdatedEvent($simulation);
        $this->eventDispatcher->dispatch($event);

        return $cgllsAncolsPeriodique;
    }

    /**
     * @return CgllAncolParametre[]|AlterableResultIterator
     *
     * @throws TDBMException
     *
     * @Query()
     */
    public function cgllsAncolsParametres(string $simulationID): AlterableResultIterator
    {
        return $this->simulationDao->getById($simulationID)->getCgllsAncolsParametre();
    }

    /**
     * @throws TDBMException
     *
     * @Mutation()
     */
    public function saveCgllsAncolsParametres(string $cgllsAncolsParametresID, ?int $calculAutomatique, ?float $lissageNet, string $simulationID): CgllAncolParametre
    {
        $cgllsAncolsParametres = $this->cgllAncolParametreDao->getById($cgllsAncolsParametresID);
        $cgllsAncolsParametres->setCalculAutomatique($calculAutomatique);
        $cgllsAncolsParametres->setLissageNet($lissageNet);
        $this->cgllAncolParametreDao->save($cgllsAncolsParametres);

        $simulation = $this->simulationDao->getById($simulationID);
        $event = new SimulationUpdatedEvent($simulation);
        $this->eventDispatcher->dispatch($event);

        return $cgllsAncolsParametres;
    }

    /**
     * @Route("/export-cglls-ancols/{simulationId}", name="exportCgllsAncols")
     */
    public function exportCgllsAncols(string $simulationId): Response
    {
        $file = $this->exportService->export([$this->exportService::SHEETLIST[8]], $simulationId);

        return $this->file($file['file'], $file['name'], ResponseHeaderBag::DISPOSITION_INLINE);
    }
}
