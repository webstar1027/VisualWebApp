<?php

declare(strict_types=1);

namespace App\Controller\GraphQL;

use App\Controller\AbstractVisialWebController;
use App\Dao\EnsembleDao;
use App\Dao\EntiteDao;
use App\Dao\InvitationEnsembleDao;
use App\Dao\InvitationEntiteDao;
use App\Dao\RoleDao;
use App\Dao\UtilisateurDao;
use App\Dao\UtilisateurRoleEntiteDao;
use App\Exceptions\HTTPException;
use App\Model\Ensemble;
use App\Model\Entite;
use App\Model\InvitationEnsemble;
use App\Model\InvitationEntite;
use App\Model\Role;
use App\Model\UtilisateurRoleEntite;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\TDBM\ResultIterator;
use TheCodingMachine\TDBM\TDBMException;

final class InvitationsController extends AbstractVisialWebController
{
    /** @var InvitationEnsembleDao */
    private $invitationDao;

    /** @var InvitationEntiteDao */
    private $invitationEntiteDao;

    /** @var EnsembleDao */
    private $ensembleDao;

    /** @var EntiteDao */
    private $entiteDao;

    /** @var UtilisateurRoleEntiteDao */
    private $utilisateurRoleEntiteDao;

    /** @var RoleDao */
    private $roleDao;

    public function __construct(
        UtilisateurDao $utilisateurDao,
        EnsembleDao $ensembleDao,
        EntiteDao $entiteDao,
        InvitationEnsembleDao $invitationDao,
        InvitationEntiteDao $invitationEntiteDao,
        UtilisateurRoleEntiteDao $utilisateurRoleEntiteDao,
        RoleDao $roleDao
    ) {
        parent::__construct($utilisateurDao);
        $this->invitationDao = $invitationDao;
        $this->ensembleDao = $ensembleDao;
        $this->entiteDao = $entiteDao;
        $this->invitationEntiteDao = $invitationEntiteDao;
        $this->utilisateurRoleEntiteDao = $utilisateurRoleEntiteDao;
        $this->roleDao = $roleDao;
    }

    /**
     * @return InvitationEnsemble[]|ResultIterator
     *
     * @throws TDBMException
     *
     * @Query()
     * @Logged()
     */
    public function invitationEnsembles(string $ensembleID): ResultIterator
    {
        return $this->invitationDao->findByEnsemble($ensembleID);
    }

    /**
     * @param string[] $entites
     *
     * @throws HTTPException
     * @throws TDBMException
     *
     * @Mutation()
     * @Logged()
     */
    public function saveInvitationEnsemble(string $ensembleID, array $entites): ?Ensemble
    {
        $authenticatedUtilisateur = $this->mustGetUtilisateur();
        if (! $this->ensembleDao->isReferentEnsemble($authenticatedUtilisateur, $ensembleID)) {
            throw HTTPException::unauthorized("Vous devez être référent de l'ensemble.");
        }
        $ensemble = $this->ensembleDao->getById($ensembleID);

        foreach ($entites as $entiteId) {
            $entite = $this->entiteDao->getById($entiteId);
            $invitation = new InvitationEnsemble(
                $ensemble,
                $entite,
                'En attente'
            );
            // TODO: parameter 'statut' value must be set to "Rattachée" if the invitation is accepted (from notifications)
            $this->invitationDao->save($invitation);
        }

        return $ensemble;
    }

    /**
     * @throws HTTPException
     *
     * @Mutation()
     * @Logged()
     */
    public function acceptInvitationEnsemble(string $ensembleID, string $entiteID): InvitationEnsemble
    {
        $authenticatedUtilisateur = $this->mustGetUtilisateur();
        if (! $this->entiteDao->isReferentEntite($authenticatedUtilisateur, $entiteID)) {
            throw HTTPException::unauthorized("Vous devez être référent de l'entite.");
        }
        /** @var InvitationEnsemble $invitation */
        $invitation = $this->invitationDao->findOneByEnsembleAndEntite($ensembleID, $entiteID);
        if (empty($invitation)) {
            throw HTTPException::notFound('Cette invitation a déjà été supprimée');
        } else {
            /** @var Ensemble $ensemble */
            $ensemble = $this->ensembleDao->getById($ensembleID);
            /** @var Entite $entite */
            $entite = $this->entiteDao->getById($entiteID);
            $ensemble->addEntiteByEnsemblesEntites($entite);
            $this->ensembleDao->save($ensemble);

            $invitation->setStatut('Rattachée');
        }
        $this->invitationDao->save($invitation);

        return $invitation;
    }

    /**
     * @throws HTTPException
     *
     * @Mutation()
     * @Logged()
     */
    public function denyInvitationEnsemble(string $ensembleID, string $entiteID): InvitationEnsemble
    {
        $authenticatedUtilisateur = $this->mustGetUtilisateur();
        if (! $this->entiteDao->isReferentEntite($authenticatedUtilisateur, $entiteID)) {
            throw HTTPException::unauthorized("Vous devez être référent de l'entite.");
        }
        /** @var InvitationEnsemble $invitation */
        $invitation = $this->invitationDao->findOneByEnsembleAndEntite($ensembleID, $entiteID);
        if (empty($invitation)) {
            throw HTTPException::notFound('Cette invitation a déjà été supprimée');
        } else {
            $invitation->setStatut('Refuser');
        }
        $this->invitationDao->save($invitation);

        return $invitation;
    }

    /**
     * @throws HTTPException
     *
     * @Mutation()
     * @Logged()
     */
    public function deleteInvitationEnsemble(string $ensembleID, string $entiteID): InvitationEnsemble
    {
        $authenticatedUtilisateur = $this->mustGetUtilisateur();
        if (! $this->ensembleDao->isReferentEnsemble($authenticatedUtilisateur, $ensembleID)) {
            throw HTTPException::unauthorized("Vous devez être référent de l'ensemble.");
        }
        /** @var InvitationEnsemble $invitation */
        $invitation = $this->invitationDao->findOneByEnsembleAndEntite($ensembleID, $entiteID);
        if (empty($invitation)) {
            throw HTTPException::notFound('Cette invitation a déjà été supprimée');
        }
        $this->invitationDao->delete($invitation);

        return $invitation;
    }

    /**
     * @return InvitationEntite[]|ResultIterator
     *
     * @throws TDBMException
     *
     * @Query()
     * @Logged()
     */
    public function invitationEntites(string $entiteID): ResultIterator
    {
        return $this->invitationEntiteDao->findByEntite($entiteID);
    }

    /**
     * @param string[] $utilisateurs
     *
     * @throws HTTPException
     * @throws TDBMException
     *
     * @Mutation()
     * @Logged()
     */
    public function saveInvitationEntite(string $entiteID, array $utilisateurs): ?Entite
    {
        $authenticatedUtilisateur = $this->mustGetUtilisateur();
        if (! $this->entiteDao->isReferentEntite($authenticatedUtilisateur, $entiteID)) {
            throw HTTPException::unauthorized("Vous devez être référent de l'ensemble.");
        }
        $entite = $this->entiteDao->getById($entiteID);

        foreach ($utilisateurs as $utilisateurId) {
            $utilisateur = $this->utilisateurDao->getById($utilisateurId);
            $invitation = new InvitationEntite(
                $entite,
                $utilisateur,
                'En attente'
            );
            // TODO: parameter 'statut' value must be set to "Rattachée" if the invitation is accepted (from notifications)
            $this->invitationEntiteDao->save($invitation);
        }

        return $entite;
    }

    /**
     * @throws HTTPException
     *
     * @Mutation()
     * @Logged()
     */
    public function deleteInvitationEntite(string $entiteID, string $utilisateurID): ?InvitationEntite
    {
        $authenticatedUtilisateur = $this->mustGetUtilisateur();
        if (! $this->entiteDao->isReferentEntite($authenticatedUtilisateur, $entiteID)) {
            throw HTTPException::unauthorized("Vous devez être référent de l'ensemble.");
        }

        /** @var Role $role */
        $role = $this->roleDao->findOneByNom('Utilisateur');

        // Kick the user if the user belongs to the entite.
        $utilisateurRoleEntite = $this->utilisateurRoleEntiteDao->findOneByFilter($entiteID, $utilisateurID, $role->getId());
        if (! empty($utilisateurRoleEntite)) {
            $this->utilisateurRoleEntiteDao->delete($utilisateurRoleEntite);
        }

        /** @var InvitationEntite $invitation */
        $invitation = $this->invitationEntiteDao->findOneByEntiteAndUtilisateur($entiteID, $utilisateurID);
        if (empty($invitation)) {
            return null;
        }
        $this->invitationEntiteDao->delete($invitation);

        return $invitation;
    }

    /**
     * @throws HTTPException
     *
     * @Mutation()
     * @Logged()
     */
    public function acceptInvitationEntite(string $entiteID, string $utilisateurID): InvitationEntite
    {
        $authenticatedUtilisateur = $this->mustGetUtilisateur();
        /** @var InvitationEntite $invitation */
        $invitation = $this->invitationEntiteDao->findOneByEntiteAndUtilisateur($entiteID, $utilisateurID);
        if (empty($invitation)) {
            throw HTTPException::notFound('Cette invitation a déjà été supprimée');
        } else {
            $utilisateur = $this->utilisateurDao->getById($utilisateurID);
            /** @var Role $role */
            $role = $this->roleDao->findOneByNom('Utilisateur');
            /** @var Entite $entite */
            $entite = $this->entiteDao->getById($entiteID);

            $utilisateurRoleEntite = new UtilisateurRoleEntite($utilisateur, $role, $entite);

            $this->utilisateurRoleEntiteDao->save($utilisateurRoleEntite);

            $invitation->setStatut('Rattachée');
        }
        $this->invitationEntiteDao->save($invitation);

        return $invitation;
    }

    /**
     * @throws HTTPException
     *
     * @Mutation()
     * @Logged()
     */
    public function denyInvitationEntite(string $entiteID, string $utilisateurID): InvitationEntite
    {
        /** @var InvitationEntite $invitation */
        $invitation = $this->invitationEntiteDao->findOneByEntiteAndUtilisateur($entiteID, $utilisateurID);
        if (empty($invitation)) {
            throw HTTPException::notFound('Cette invitation a déjà été supprimée');
        } else {
            $invitation->setStatut('Refuser');
        }
        $this->invitationEntiteDao->save($invitation);

        return $invitation;
    }

    /**
     * @throws HTTPException
     *
     * @Mutation()
     * @Logged()
     */
    public function resendInvitationEntite(string $entiteID, string $utilisateurID): InvitationEntite
    {
        /** @var InvitationEntite $invitation */
        $invitation = $this->invitationEntiteDao->findOneByEntiteAndUtilisateur($entiteID, $utilisateurID);
        if (empty($invitation)) {
            throw HTTPException::notFound('Cette invitation a déjà été supprimée');
        } else {
            $invitation->setStatut('En attente');
        }
        $this->invitationEntiteDao->save($invitation);

        return $invitation;
    }
}
