<?php

declare(strict_types=1);

namespace App\Controller\GraphQL;

use App\Controller\AbstractVisialWebController;
use App\Dao\EnsembleDao;
use App\Dao\EntiteDao;
use App\Dao\InvitationEnsembleDao;
use App\Dao\NotificationDao;
use App\Dao\UtilisateurDao;
use App\Exceptions\HTTPException;
use App\Model\Ensemble;
use App\Model\InvitationEnsemble;
use App\Model\Notification;
use Cake\Chronos\Chronos;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\TDBM\ResultIterator;
use TheCodingMachine\TDBM\TDBMException;
use function array_map;
use function array_search;

final class EnsemblesController extends AbstractVisialWebController
{
    /** @var EnsembleDao */
    private $ensembleDao;

    /** @var EntiteDao */
    private $entiteDao;

    /** @var InvitationEnsembleDao */
    private $invitationEnsembleDao;

    /** @var NotificationDao */
    private $notificationDao;

    public function __construct(UtilisateurDao $utilisateurDao, EnsembleDao $ensembleDao, EntiteDao $entiteDao, InvitationEnsembleDao $invitationEnsembleDao, NotificationDao $notificationDao)
    {
        parent::__construct($utilisateurDao);
        $this->ensembleDao = $ensembleDao;
        $this->entiteDao = $entiteDao;
        $this->invitationEnsembleDao = $invitationEnsembleDao;
        $this->notificationDao = $notificationDao;
    }

    /**
     * @return Ensemble[]|ResultIterator
     *
     * @Query()
     * @Logged()
     */
    public function ensembles(
        ?string $nom,
        ?string $description,
        ?string $creePar,
        ?string $modifiePar,
        ?string $entite,
        ?string $referent,
        string $sortColumn,
        string $sortOrder
    ): ResultIterator {
        $estAdministrateurCentral = $this->mustGetUtilisateur()->getEstAdministrateurCentral();

        return $this->ensembleDao->findByFilters($nom, $description, $creePar, $modifiePar, $entite, $referent, $estAdministrateurCentral, $sortColumn, $sortOrder);
    }

    /**
     * @throws TDBMException
     *
     * @Query()
     * @Logged()
     */
    public function ensemble(string $ensembleID): Ensemble
    {
        return $this->ensembleDao->getById($ensembleID);
    }

    /**
     * @throws TDBMException
     *
     * @Query()
     * @Logged()
     */
    public function isReferentEnsemble(string $ensembleID): bool
    {
        $authenticatedUtilisateur = $this->mustGetUtilisateur();

        return $this->ensembleDao->isReferentEnsemble($authenticatedUtilisateur, $ensembleID);
    }

    /**
     * @param string[] $referents
     *
     * @throws HTTPException
     * @throws TDBMException
     *
     * @Mutation()
     * @Logged()
     */
    public function saveEnsemble(?string $ensembleID, string $nom, ?string $description, bool $estActive, array $referents): Ensemble
    {
        $authenticatedUtilisateur = $this->mustGetUtilisateur();
        $existingEnsembleNom = $this->ensembleDao->findOneByNom($nom);
        if (empty($ensembleID)) {
            // we are creating an Ensemble.
            $ensemble = new Ensemble(
                $authenticatedUtilisateur,
                $nom,
                Chronos::now()
            );
            $ensemble->setDescription($description);
            $ensemble->setEstActive(true);

            $referentsObjects = array_map(function ($id) {
                if ($id !== null) {
                    return $this->entiteDao->getById($id);
                }
            }, $referents);

            if (isset($existingEnsembleNom)) {
                throw HTTPException::badRequest('Cet ensemble existe déjà.');
            }
            $ensemble->setEntitesByReferentsEnsembles($referentsObjects);

            foreach ($referentsObjects as $entite) {
                $ensemble->addEntiteByEnsemblesEntites($entite);
            }

            $this->ensembleDao->save($ensemble);

            return $ensemble;
        }

        // we are updating an Ensemble.
        $ensemble = $this->ensembleDao->getById($ensembleID);
        $ensemble->setNom($nom);
        $ensemble->setDescription($description);
        $ensemble->setModifiePar($authenticatedUtilisateur);
        $ensemble->setDateModification(Chronos::now());
        $ensemble->setEstActive($estActive);

        // Ready notification for removed entities.
        $oldReferents = $ensemble->getEntitesByReferentsEnsembles();
        foreach ($oldReferents as $entite) {
            if (array_search($entite->getId(), $referents)) {
                continue;
            }

            $entiteReferents = $entite->getUtilisateurs();
            foreach ($entiteReferents as $utilisateur) {
                $notification = new Notification(
                    $utilisateur,
                    'REMOVE_REFERENT_ENTITE',
                    'The entite ' . $entite->getNom() . ' is removed from ' . $ensemble->getNom(),
                    'unread'
                );
                $this->notificationDao->save($notification);
            }
        }

        $referentsObjects = array_map(function ($id) {
            if ($id !== null) {
                return $this->entiteDao->getById($id);
            }
        }, $referents);

        $ensemble->setEntitesByReferentsEnsembles($referentsObjects);

        foreach ($referentsObjects as $entite) {
            if ($ensemble->hasEntiteByEnsemblesEntites($entite)) {
                continue;
            }
            $ensemble->addEntiteByEnsemblesEntites($entite);
        }

        $this->ensembleDao->save($ensemble);

        return $ensemble;
    }

    /**
     * @throws TDBMException
     *
     * @Query()
     * @Logged()
     */
    public function leaveEnsemble(string $ensembleID): bool
    {
        $authenticatedUtilisateur = $this->mustGetUtilisateur();
        $ensemble = $this->ensembleDao->getById($ensembleID);
        $entites = $this->entiteDao->getEntitiesInEmsembleByReferentEntite($authenticatedUtilisateur, $ensembleID);
        foreach ($entites as $entite) {
            $ensemble->removeEntiteByEnsemblesEntites($entite);
            $this->ensembleDao->save($ensemble);
            /** @var InvitationEnsemble $invitation */
            $invitation = $this->invitationEnsembleDao->findOneByEnsembleAndEntite($ensembleID, $entite->getId());
            if (empty($invitation)) {
                continue;
            }

            $invitation->setStatut('Laisser');
            $this->invitationEnsembleDao->save($invitation);
        }

        return true;
    }

    /**
     * @return Ensemble[]|ResultIterator
     *
     * @Query()
     * @Logged()
     */
    public function allEnsembles(): ResultIterator
    {
        return $this->ensembleDao->findAll();
    }
}
