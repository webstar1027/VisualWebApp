<?php

declare(strict_types=1);

namespace App\Controller\GraphQL;

use App\Controller\AbstractVisialWebController;
use App\Dao\ProfilEvolutionLoyerParametreDao;
use App\Dao\SimulationDao;
use App\Dao\UtilisateurDao;
use App\Event\SimulationUpdatedEvent;
use App\Exceptions\HTTPException;
use App\Model\ProfilEvolutionLoyer;
use App\Model\ProfilEvolutionLoyerParametre;
use App\Model\ProfilEvolutionLoyerPeriodique;
use App\Security\SerializableUser;
use App\Services\ExportService;
use App\Services\ProfilEvolutionLoyerService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\TDBM\ResultIterator;
use TheCodingMachine\TDBM\TDBMException;
use Throwable;

class ProfilsEvolutionLoyersController extends AbstractVisialWebController
{
    /** @var ProfilEvolutionLoyerService */
    private $profilEvolutionLoyerService;

    /** @var ProfilEvolutionLoyerParametreDao */
    private $profilEvolutionLoyerParametreDao;

    /** @var ExportService */
    private $exportService;

    /** @var SimulationDao */
    private $simulationDao;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(UtilisateurDao $utilisateurDao, ProfilEvolutionLoyerParametreDao $profilEvolutionLoyerParametreDao, ProfilEvolutionLoyerService $profilEvolutionLoyerService, ExportService $exportService, SimulationDao $simulationDao, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($utilisateurDao);
        $this->profilEvolutionLoyerService = $profilEvolutionLoyerService;
        $this->exportService = $exportService;
        $this->profilEvolutionLoyerParametreDao = $profilEvolutionLoyerParametreDao;
        $this->simulationDao = $simulationDao;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return ProfilEvolutionLoyer[]|ResultIterator
     *
     * @throws HTTPException
     *
     * @Query()
     * @Logged()
     */
    public function profilsEvolutionLoyers(string $simulationID): ResultIterator
    {
        /** @var SerializableUser|null $user */
        $user = $this->getUser();
        if ($user === null || empty($user->getRoles())) {
            throw HTTPException::unauthorized("Vos droits ne vous permettent pas d'effectuer cette opération.");
        }

        return $this->profilEvolutionLoyerService->fetchBySimulationId($simulationID);
    }

    /**
     * @Mutation()
     * @Logged()
     */
    public function saveProfilEvolutionLoyer(ProfilEvolutionLoyer $profilEvolutionLoyer): ProfilEvolutionLoyer
    {
        $this->profilEvolutionLoyerService->save($profilEvolutionLoyer);

        $event = new SimulationUpdatedEvent($profilEvolutionLoyer->getSimulation());
        $this->eventDispatcher->dispatch($event);

        return $profilEvolutionLoyer;
    }

    /**
     * @throws TDBMException
     *
     * @Mutation()
     */
    public function saveProfilEvolutionLoyerParametre(string $idParametre, bool $plafonnement): ProfilEvolutionLoyerParametre
    {
        $updateParametre = $this->profilEvolutionLoyerParametreDao->getById($idParametre);
        $updateParametre->setPlafonnementDesLoyersPratiquesAuLoyerPlafond($plafonnement);
        $this->profilEvolutionLoyerParametreDao->save($updateParametre);

        return $updateParametre;
    }

    /**
     * @return ResultIterator|ProfilEvolutionLoyerParametre[]
     *
     * @Query()
     * @Logged()
     */
    public function profilEvolutionLoyerParametre(string $simulationID): ResultIterator
    {
            return $this->profilEvolutionLoyerParametreDao->findBySimulationID($simulationID);
    }

    /**
     * @throws TDBMException
     *
     * @Mutation()
     * @Logged()
     */
    public function saveProfilEvolutionLoyerPeriodique(
        string $uuid,
        int $iteration,
        float $s1,
        float $s2
    ): ProfilEvolutionLoyerPeriodique {
        $profilEvolutionLoyerPeriodique = $this->profilEvolutionLoyerService->findProfilEvolutionLoyerPeriodique($uuid, $iteration);
        if ($profilEvolutionLoyerPeriodique === null) {
            throw new TDBMException('No row found for ProfilEvolutionLoyerPeriodique');
        }
        $profilEvolutionLoyerPeriodique->setS1($s1);
        $profilEvolutionLoyerPeriodique->setS2($s2);
        $this->profilEvolutionLoyerService->saveProfilEvolutionLoyerPeriodique($profilEvolutionLoyerPeriodique);

        return $profilEvolutionLoyerPeriodique;
    }

    /**
     * @throws TDBMException
     * @throws HTTPException
     *
     * @Mutation()
     */
    public function removeProfilEvolutionLoyer(string $id, string $simulationId): bool
    {
        /** @var SerializableUser|null $user */
        $user = $this->getUser();

        if ($user === null || empty($user->getRoles())) {
            throw HTTPException::unauthorized("Vos droits ne vous permettent pas d'effectuer cette opération.");
        }

        try {
            $this->profilEvolutionLoyerService->remove($id);

            $simulation = $this->simulationDao->getById($simulationId);
            $event = new SimulationUpdatedEvent($simulation);
            $this->eventDispatcher->dispatch($event);
        } catch (Throwable $e) {
            throw HTTPException::forbidden('Ce profil ne peut être supprimé car il est déjà utilisé', $e);
        }

        return true;
    }

    /**
     * @Route("/export-profils-evolution-loyers/{simulationId}", name="exportProfilsEvolutionLoyers")
     */
    public function exportProfilsEvolutionLoyers(string $simulationId): Response
    {
        $file = $this->exportService->export([$this->exportService::SHEETLIST[5]], $simulationId);

        return $this->file($file['file'], $file['name'], ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     *  @Route("/import-profils-evolution-loyers/{simulationId}", name="importProfilsEvolutionLoyers")
     */
    public function importProfilsEvolutionLoyers(Request $request, string $simulationId): Response
    {
        $notification = $this->exportService->import([$this->exportService::SHEETLIST[5]], $request, $simulationId);

        return new Response($notification);
    }
}
