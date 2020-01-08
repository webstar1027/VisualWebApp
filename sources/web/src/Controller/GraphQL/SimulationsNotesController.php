<?php

declare(strict_types=1);

namespace App\Controller\GraphQL;

use App\Controller\AbstractVisialWebController;
use App\Dao\SimulationDao;
use App\Dao\SimulationNoteDao;
use App\Dao\UtilisateurDao;
use App\Exceptions\HTTPException;
use App\Model\SimulationNote;
use DateTimeImmutable;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\TDBM\ResultIterator;
use TheCodingMachine\TDBM\TDBMException;

final class SimulationsNotesController extends AbstractVisialWebController
{
    /** @var SimulationDao */
    private $simulationDao;
    /** @var SimulationNoteDao */
    private $simulationNoteDao;

    public function __construct(UtilisateurDao $utilisateurDao, SimulationDao $simulationDao, SimulationNoteDao $simulationNoteDao)
    {
        parent::__construct($utilisateurDao);
        $this->utilisateurDao = $utilisateurDao;
        $this->simulationDao = $simulationDao;
        $this->simulationNoteDao = $simulationNoteDao;
    }

    /**
     * @return SimulationNote[]|ResultIterator
     *
     * @throws TDBMException
     *
     * @Query
     */
    public function simulationNotes(string $simulationId): ResultIterator
    {
        $simulation = $this->simulationDao->getById($simulationId);

        return $this->simulationNoteDao->getBySimulation($simulation);
    }

    /**
     * @throws TDBMException
     * @throws HTTPException
     *
     * @Mutation
     */
    public function saveSimulationNote(?string $id, string $simulationId, string $note): SimulationNote
    {
        $simulation = $this->simulationDao->getById($simulationId);
        $authenticatedUser = $this->mustGetUtilisateur();

        if (empty($id)) {
            // If the note doesn't exist yet
            $simulationNote = new SimulationNote($simulation, $authenticatedUser, $note, new DateTimeImmutable());
        } else {
            // Modify the existing note - only the author of the note can do it
            $simulationNote = $this->simulationNoteDao->getById($id);
            if ($authenticatedUser !== $simulationNote->getCreePar()) {
                throw HTTPException::unauthorized("Seul l'auteur de la note peut effectuer cette opération.");
            }
            $simulationNote->setNote($note);
            $simulationNote->setDateModification(new DateTimeImmutable());
        }
        $this->simulationNoteDao->save($simulationNote);

        return $simulationNote;
    }

    /**
     * @throws HTTPException
     *
     * @Mutation
     */
    public function removeSimulationNote(string $id, string $simulationId): ?bool
    {
        $authenticatedUser = $this->mustGetUtilisateur();
        $simulationOwner = $this->simulationDao->getById($simulationId)->getUtilisateur();
        $simulationNote = $this->simulationNoteDao->getById($id);
        $simulationNoteOwner = $simulationNote->getCreePar();

        // Check if the user can delete the note
        if ($authenticatedUser->getEstAdministrateurSimulation()
            || $authenticatedUser === $simulationOwner
            || $authenticatedUser === $simulationNoteOwner
        ) {
            $this->simulationNoteDao->delete($simulationNote);

            return true;
        }

        throw HTTPException::unauthorized("Vos droits ne vous permettent pas d'effectuer cette opération.");
    }
}
