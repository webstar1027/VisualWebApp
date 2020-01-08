<?php

declare(strict_types=1);

namespace App\Controller\GraphQL;

use App\Controller\AbstractVisialWebController;
use App\Dao\InvitationEnsembleDao;
use App\Dao\InvitationEntiteDao;
use App\Dao\NotificationDao;
use App\Dao\UtilisateurDao;
use App\Model\InvitationEnsemble;
use App\Model\InvitationEntite;
use App\Model\Notification;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\TDBM\ResultIterator;
use TheCodingMachine\TDBM\TDBMException;
use function Safe\json_encode;

final class NotificationsController extends AbstractVisialWebController
{
    /** @var InvitationEntiteDao */
    private $invitationEntiteDao;

    /** @var InvitationEnsembleDao */
    private $invitationEnsembleDao;

    /** @var NotificationDao */
    private $notificationDao;

    public function __construct(
        UtilisateurDao $utilisateurDao,
        InvitationEntiteDao $invitationEntiteDao,
        InvitationEnsembleDao $invitationEnsembleDao,
        NotificationDao $notificationDao
    ) {
        parent::__construct($utilisateurDao);
        $this->invitationEntiteDao = $invitationEntiteDao;
        $this->invitationEnsembleDao = $invitationEnsembleDao;
        $this->notificationDao = $notificationDao;
    }

    /**
     * @return InvitationEntite[]|ResultIterator
     *
     * @throws TDBMException
     *
     * @Query()
     * @Logged()
     */
    public function notificationEntites(): ResultIterator
    {
        $authenticatedUtilisateur = $this->mustGetUtilisateur();

        return $this->invitationEntiteDao->findByUtilisateur($authenticatedUtilisateur);
    }

    /**
     * @return InvitationEnsemble[]|ResultIterator
     *
     * @throws TDBMException
     *
     * @Query()
     * @Logged()
     */
    public function notificationEnsembles(): ResultIterator
    {
        $authenticatedUtilisateur = $this->mustGetUtilisateur();

        return $this->invitationEnsembleDao->findByUtilisateur($authenticatedUtilisateur);
    }

    /**
     * @return InvitationEnsemble[]|ResultIterator
     *
     * @throws TDBMException
     *
     * @Query()
     * @Logged()
     */
    public function notificationLeaveEnsembles(): ResultIterator
    {
        $authenticatedUtilisateur = $this->mustGetUtilisateur();

        return $this->invitationEnsembleDao->findLeaveEnsemblesByUtilisateur($authenticatedUtilisateur);
    }

    /**
     * @return Notification[]|ResultIterator
     *
     * @throws TDBMException
     *
     * @Query()
     * @Logged()
     */
    public function notifications(): ResultIterator
    {
        $authenticatedUtilisateur = $this->mustGetUtilisateur();

        return $this->notificationDao->findByUtilisateur($authenticatedUtilisateur);
    }

    /**
     * @throws TDBMException
     *
     * @Mutation()
     * @Logged()
     */
    public function readNotification(string $notificationID): Notification
    {
        /** @var Notification $notification */
        $notification = $this->notificationDao->getById($notificationID);
        $notification->setStatut('read');
        $this->notificationDao->save($notification);

        return $notification;
    }

    /**
     * @Query()
     * @Logged()
     */
    public function fetchAllNotifications(): string
    {
        $authenticatedUtilisateur = $this->mustGetUtilisateur();

        $generalNotifications = $this->notificationDao->findByUtilisateur($authenticatedUtilisateur);
        $notificationEntites = $this->invitationEntiteDao->findByUtilisateur($authenticatedUtilisateur);
        $notificationEnsembles = $this->invitationEnsembleDao->findByUtilisateur($authenticatedUtilisateur);
        $notificationLeaveEnsembles = $this->invitationEnsembleDao->findLeaveEnsemblesByUtilisateur($authenticatedUtilisateur);

        $allNotfications = [
            'general' => $generalNotifications,
            'inviteEntite' => $notificationEntites,
            'inviteEnsemble' => $notificationEnsembles,
            'leaveEnsemble' => $notificationLeaveEnsembles,
        ];

        return json_encode($allNotfications);
    }
}
