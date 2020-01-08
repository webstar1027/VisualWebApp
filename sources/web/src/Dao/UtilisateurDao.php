<?php

declare(strict_types=1);

namespace App\Dao;

use App\Dao\Generated\AbstractUtilisateurDao;
use App\Model\Utilisateur;
use Safe\Exceptions\StringsException;
use TheCodingMachine\TDBM\ResultIterator;
use function Safe\sprintf;

/**
 * The UtilisateurDao class will maintain the persistence of Utilisateur class into the utilisateurs table.
 */
class UtilisateurDao extends AbstractUtilisateurDao
{
    /**
     * @return Utilisateur[]|ResultIterator
     *
     * @throws StringsException
     */
    public function findByFilters(
        ?string $nom,
        ?string $prenom,
        ?string $email,
        ?string $telephone,
        ?string $entite,
        ?string $role,
        ?string $fonction,
        bool $estAdministrateurCentral,
        string $sortColumn,
        string $sortOrder
    ): ResultIterator {
        // We need to specify this in order to prevent ambiguous error type because 'nom' is a field of multiple tables bellow
        if ($sortColumn === 'nom') {
            $sortColumn = 'utilisateurs.nom';
        }

        // Administrateur central user must be able to see inactive users
        // That's why we use estAdministrateurCentral in the code bellow
        return $this->findFromSql(
            'utilisateurs
            LEFT JOIN  utilisateurs_roles_entites ON utilisateurs.id = utilisateurs_roles_entites.utilisateur_id
            LEFT JOIN entites ON utilisateurs_roles_entites.entite_id = entites.id
            LEFT JOIN roles ON utilisateurs_roles_entites.role_id = roles.id',
            'utilisateurs.nom LIKE :nom
            AND prenom LIKE :prenom
            AND email LIKE :email
            AND telephone LIKE :telephone
            AND fonction LIKE :fonction
            AND (est_administrateur_central = false OR est_administrateur_central = :estAdministrateurCentral)
            AND (est_administrateur_simulation = false OR est_administrateur_simulation = :estAdministrateurCentral)
            AND entites.nom LIKE :entite
            AND (utilisateurs.est_active = true OR utilisateurs.est_active = :estPasAdministrateurCentral)
            AND roles.id = :roleId',
            [
                'nom' => $nom ? sprintf('%%%s%%', $nom) : null,
                'prenom' => $prenom ? sprintf('%%%s%%', $prenom) : null,
                'email' => $email ? sprintf('%%%s%%', $email) : null,
                'telephone' => $telephone ? sprintf('%%%s%%', $telephone) : null,
                'fonction' => $fonction ? sprintf('%%%s%%', $fonction) : null,
                'entite' => $entite ? sprintf('%%%s%%', $entite) : null,
                'estPasAdministrateurCentral' => ! $estAdministrateurCentral,
                'estAdministrateurCentral' => $estAdministrateurCentral,
                'roleId' => $role,
            ],
            sprintf('%s %s', $sortColumn, $sortOrder)
        );
    }

    /**
     * @return Utilisateur[]|ResultIterator
     *
     * @throws StringsException
     */
    public function fetchByFilters(
        ?string $nom,
        ?string $entite,
        ?string $ensemble,
        string $simulationID,
        ?string $currentUtilisateurId,
        string $ownerEntiteId,
        string $sortColumn,
        string $sortOrder
    ): ResultIterator {
        // We need to specify this in order to prevent ambiguous error type because 'nom' is a field of multiple tables bellow
        if ($sortColumn === 'nom') {
            $sortColumn = 'utilisateurs.nom';
        }

        return $this->findFromSql(
            'utilisateurs
            LEFT JOIN  utilisateurs_roles_entites ON utilisateurs.id = utilisateurs_roles_entites.utilisateur_id
            LEFT JOIN entites ON utilisateurs_roles_entites.entite_id = entites.id
            LEFT JOIN ensembles_entites ON entites.id = ensembles_entites.entite_id
            LEFT JOIN ensembles ON ensembles_entites.ensemble_id = ensembles.id
            LEFT JOIN partagers ON utilisateurs.id = partagers.utilisateur_id AND partagers.simulation_id = :simulationID',
            'utilisateurs.nom LIKE :nom
            AND utilisateurs.id <> :currentUtilisateurId
            AND entites.nom LIKE :entite
            AND ensembles.nom LIKE :ensemble
            AND partagers.utilisateur_id IS NULL
            AND entites.code <> "ADMIN"
            AND entites.id <> :ownerEntiteId',
            [
                'nom' => $nom ? sprintf('%%%s%%', $nom) : null,
                'entite' => $entite ? sprintf('%%%s%%', $entite) : null,
                'ensemble' => $ensemble? sprintf('%%%s%%', $ensemble) : null,
                'simulationID' => $simulationID,
                'currentUtilisateurId' => $currentUtilisateurId,
                'ownerEntiteId' => $ownerEntiteId,
            ],
            sprintf('%s %s', $sortColumn, $sortOrder)
        );
    }

    /**
     * @return Utilisateur[]|ResultIterator
     *
     * @throws StringsException
     */
    public function fetchByEntite(
        ?string $nom,
        ?string $entite,
        ?string $ensemble,
        ?string $simulationID,
        ?string $currentEntite,
        string $sortColumn,
        string $sortOrder
    ): ResultIterator {
        // We need to specify this in order to prevent ambiguous error type because 'nom' is a field of multiple tables bellow
        if ($sortColumn === 'nom') {
            $sortColumn = 'utilisateurs.nom';
        }

        return $this->findFromSql(
            'utilisateurs
            LEFT JOIN  utilisateurs_roles_entites ON utilisateurs.id = utilisateurs_roles_entites.utilisateur_id
            LEFT JOIN entites ON utilisateurs_roles_entites.entite_id = entites.id
            LEFT JOIN ensembles_entites ON entites.id = ensembles_entites.entite_id
            LEFT JOIN ensembles ON ensembles_entites.ensemble_id = ensembles.id
            LEFT JOIN partagers ON utilisateurs.id = partagers.utilisateur_id AND partagers.simulation_id = :simulationID',
            'utilisateurs.nom LIKE :nom
            AND entites.id <> :currentEntite
            AND entites.nom LIKE :entite
            AND ensembles.nom LIKE :ensemble
            AND partagers.utilisateur_id IS NULL
            AND entites.code <> "ADMIN"',
            [
                'nom' => $nom ? sprintf('%%%s%%', $nom) : null,
                'entite' => $entite ? sprintf('%%%s%%', $entite) : null,
                'ensemble' => $ensemble ? sprintf('%%%s%%', $ensemble) : null,
                'simulationID' => $simulationID,
                'currentEntite' => $currentEntite,
            ],
            sprintf('%s %s', $sortColumn, $sortOrder)
        );
    }

    public function getUtilisateurByToken(string $token): ?Utilisateur
    {
        return $this->findOne(['token' => $token]);
    }

    public function getUtilisateurByEmail(string $email): ?Utilisateur
    {
        return $this->findOne(['email' => $email]);
    }
}
