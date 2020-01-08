<?php

declare(strict_types=1);

namespace App\Tests\Controller\GraphQL;

use App\Fixtures\CreateEnsembles;
use App\Fixtures\CreateEntites;
use App\Fixtures\CreateUtilisateurs;
use App\Model\Ensemble;
use App\Model\Entite;
use Safe\Exceptions\FilesystemException;
use Safe\Exceptions\JsonException;

class InvitationsControllerTest extends AbstractGraphQLControllerTestCase
{
    // Test GraphQL queries
    /**
     * @throws FilesystemException
     * @throws JsonException
     */
    public function testThatWeHaveNoInvitationsAgainstFirstEnsemble(): void
    {
        $this->login();
        /** @var Ensemble $ensemble */
        $ensemble = $this->ensembleDao->findAll()->first();

        $response = $this->mustSuccessGraphQL('administration/invitations/invitationEnsembles.gql', [
            'limit' => 10,
            'offset' => 0,
            'ensembleID' => $ensemble->getId(),
        ]);
        $this->assertSame(0, $response['invitationEnsembles']['count']);
    }

    // Test GraphQL mutations

    /**
     * @throws FilesystemException
     * @throws JsonException
     */
    public function testThatNewInvitationIsAdded(): void
    {
        // UTILISATEUR_6_EMAIL corresponds to 'Cédric Fellous' User
        // 'Cédric Fellous' User is Référent of 'Nine Twenty Seven' Entité
        // 'Nine Twenty Seven' Entité is Référent of 'Ensemble 2' Ensemble
        $this->login(CreateUtilisateurs::UTILISATEUR_6_EMAIL, CreateUtilisateurs::UTILISATEUR_PASSWORD);

        /** @var Ensemble $ensemble */
        $ensemble = $this->ensembleDao->findOneByNom(CreateEnsembles::NOM_2);

        /** @var Entite $entite */
        $entite = $this->entiteDao->findAll()->first();

        $response = $this->mustSuccessGraphQL('administration/invitations/saveInvitationEnsemble.gql', [
            'ensembleID' => $ensemble->getId(),
            'entites' => [$entite->getId()],
        ]);
        $this->assertArrayHasKey('id', $response['saveInvitationEnsemble']);
    }

    /**
     * @throws FilesystemException
     * @throws JsonException
     */
    public function testThatRegularUserCanNotSaveInvitation(): void
    {
        $this->login(CreateUtilisateurs::UTILISATEUR_1_EMAIL, CreateUtilisateurs::UTILISATEUR_PASSWORD);

        /** @var Ensemble $ensemble */
        $ensemble = $this->ensembleDao->findOneByNom(CreateEnsembles::NOM_2);

        /** @var Entite $entite */
        $entite = $this->entiteDao->findAll()->first();

        $this->mustFailGraphQL('administration/invitations/saveInvitationEnsemble.gql', [
            'ensembleID' => $ensemble->getId(),
            'entites' => [$entite->getId()],
        ], 401);
    }

    /**
     * @throws FilesystemException
     * @throws JsonException
     *
     * @depends testThatNewInvitationIsAdded
     */
    public function testThatRegularUserCanNotDeleteInvitation(): void
    {
        $this->login(CreateUtilisateurs::UTILISATEUR_1_EMAIL, CreateUtilisateurs::UTILISATEUR_PASSWORD);

        /** @var Ensemble $ensemble */
        $ensemble = $this->ensembleDao->findOneByNom(CreateEnsembles::NOM_2);

        /** @var Entite $entite */
        $entite = $this->entiteDao->findOneByCode(CreateEntites::CODE_3);

        $this->mustFailGraphQL('administration/invitations/deleteInvitationEnsemble.gql', [
            'ensembleID' => $ensemble->getId(),
            'entiteID' => $entite->getId(),
        ], 401);
    }
}
