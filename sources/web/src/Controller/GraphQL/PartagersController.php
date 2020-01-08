<?php

declare(strict_types=1);

namespace App\Controller\GraphQL;

use App\Controller\AbstractVisialWebController;
use App\Dao\EntiteDao;
use App\Dao\NotificationDao;
use App\Dao\PartagerDao;
use App\Dao\SimulationDao;
use App\Dao\UtilisateurDao;
use App\Model\Notification;
use App\Model\Partager;
use Cake\Chronos\Chronos;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\TDBM\ResultIterator;
use TheCodingMachine\TDBM\TDBMException;
use function count;

final class PartagersController extends AbstractVisialWebController
{
    /** @var PartagerDao */
    private $partagerDao;

    /** @var EntiteDao */
    private $entiteDao;

    /** @var SimulationDao */
    private $simulationDao;

    /** @var NotificationDao */
    private $notificationDao;

    public function __construct(
        PartagerDao $partagerDao,
        EntiteDao $entiteDao,
        UtilisateurDao $utilisateurDao,
        SimulationDao $simulationDao,
        NotificationDao $notificationDao
    ) {
            parent::__construct($utilisateurDao);
            $this->partagerDao = $partagerDao;
            $this->entiteDao = $entiteDao;
            $this->simulationDao = $simulationDao;
            $this->notificationDao = $notificationDao;
    }

    /**
     * @return Partager[]|ResultIterator
     *
     * @Query()
     * @Logged()
     */
    public function fetchPartage(?string $simulationID): ResultIterator
    {
        return $this->partagerDao->filterBySimulation($simulationID);
    }

    /**
     * @Query()
     * @Logged()
     */
    public function checkStatus(?string $simulationID): bool
    {
        $authenticatedUtilisateur = $this->mustGetUtilisateur();
        $readOnlyShare = $this->partagerDao->findReadOnlyPartager($authenticatedUtilisateur->getId(), $simulationID);

        return count($readOnlyShare) <= 0;
    }

    /**
     * @return Partager[]|ResultIterator
     *
     * @Query()
     * @Logged()
     */
    public function allPartagers(?string $entiteID): ResultIterator
    {
        return $this->partagerDao->filterByEnite($entiteID);
    }

    /**
     * @param string[] $utilisateurs
     * @param string[] $entites
     *
     * @throws TDBMException
     *
     * @Mutation()
     * @Logged()
     */
    public function savePartage(
        ?string $id,
        string $simulationID,
        array $utilisateurs,
        array $entites,
        string $owner,
        string $partagerType
    ): ?Partager {
        if ($id) {
            $newPartager = $this->partagerDao->getById($id);
            $newPartager->setPartageType($partagerType);
            $this->partagerDao->save($newPartager);
        } else {
            $simulation = $this->simulationDao->getById($simulationID);
            $ownerEntite = $this->entiteDao->getById($owner);
            $currentUtilisateurId = $this->mustGetUtilisateur()->getId();
            $currentUtilisateur = $this->utilisateurDao->getById($currentUtilisateurId);
            $count = 0;

            foreach ($utilisateurs as $utilisateurId) {
                $utilisateur = $this->utilisateurDao->getById($utilisateurId);
                $entite = $this->entiteDao->getById($entites[$count]);

                $newPartager = new Partager(
                    $simulation,
                    $utilisateur,
                    $entite,
                    $ownerEntite,
                    $currentUtilisateur,
                    $partagerType,
                    Chronos::now()
                );

                $this->partagerDao->save($newPartager);

                $notification = new Notification(
                    $utilisateur,
                    'SHARE_SIMULATION',
                    'La simulation ' . $simulation->getNom() . ' a été partagée avec vous',
                    'unread'
                );
                $this->notificationDao->save($notification);
            }
        }

        return $newPartager ?? null;
    }

    /**
     * @Mutation()
     * @Logged()
     */
    public function deletePartage(string $id): Partager
    {
        $authenticatedUtilisateur = $this->mustGetUtilisateur();
        $partager = $this->partagerDao->getById($id);
        $this->partagerDao->delete($partager);

        return $partager;
    }
}
