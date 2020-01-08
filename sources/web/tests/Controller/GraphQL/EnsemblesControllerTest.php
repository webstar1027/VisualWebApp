<?php

declare(strict_types=1);

namespace App\Tests\Controller\GraphQL;

use App\Model\Ensemble;
use App\Model\Entite;
use Safe\Exceptions\FilesystemException;
use Safe\Exceptions\JsonException;

final class EnsemblesControllerTest extends AbstractGraphQLControllerTestCase
{
    /**
     * @throws FilesystemException
     * @throws JsonException
     */
    public function testEnsembles(): void
    {
        $this->login();
        $response = $this->mustSuccessGraphQL('administration/ensembles/ensembles.gql', [
            'limit' => 10,
            'offset' => 0,
            'sortColumn' => 'nom',
            'sortOrder' => 'ASC',
        ]);
        $this->assertSame(3, $response['ensembles']['count']);
    }

    /**
     * @throws FilesystemException
     * @throws JsonException
     */
    public function testSaveEnsembleNotLogged(): void
    {
        $this->login();
        /** @var Entite $entite */
        $entite = $this->entiteDao->findAll()->first();
        $response = $this->mustFailGraphQL('administration/ensembles/saveEnsemble.gql', [
            'ensembleID' => 1,
            'nom' => 'foo',
            'description' => 'bar',
            'estActive'  => true,
            'referents' => [$entite->getId()],
        ]);
        $this->assertContains('errors', $response->getContent());
    }

    /**
     * @throws FilesystemException
     * @throws JsonException
     */
    public function testSaveEnsembleAssertNewEnsemble(): void
    {
        $this->login();
        /** @var Entite $entite */
        $entite = $this->entiteDao->findAll()->first();
        $response = $this->mustSuccessGraphQL('administration/ensembles/saveEnsemble.gql', [
            'nom' => 'foo',
            'description' => 'bar',
            'estActive'  => true,
            'referents' => [$entite->getId()],
        ]);
        $this->assertArrayHasKey('id', $response['saveEnsemble']);
    }

    /**
     * @throws FilesystemException
     * @throws JsonException
     */
    public function testSaveEnsembleAssertUpdateEnsembleDoesNotExist(): void
    {
        $this->login();
        /** @var Entite $entite */
        $entite = $this->entiteDao->findAll()->first();
        $this->mustFailGraphQL('administration/ensembles/saveEnsemble.gql', [
            'ensembleID' => 1,
            'nom' => 'foo',
            'description' => 'bar',
            'estActive'  => true,
            'referents'   => [$entite->getId()],
        ]);
    }

    /**
     * @throws FilesystemException
     * @throws JsonException
     */
    public function testThatEnsembleMutationUpdatesTheEnsemble(): void
    {
        /** @var Ensemble $ensemble */
        $ensemble = $this->ensembleDao->findAll()->first();
        /** @var Entite $entite */
        $entite = $this->entiteDao->findAll()->first();
        $this->assertNotEmpty($ensemble);
        $this->login();
        $response = $this->mustSuccessGraphQL('administration/ensembles/saveEnsemble.gql', [
            'ensembleID' => $ensemble->getId(),
            'nom' => 'baz',
            'description' => 'qux',
            'estActive'  => true,
            'referents'   => [$entite->getId()],
        ]);
        $this->assertArrayHasKey('id', $response['saveEnsemble']);
        $response = $this->mustSuccessGraphQL('administration/ensembles/ensemble.gql', [
            'ensembleID' => $ensemble->getId(),
        ]);
        $this->assertSame('baz', $response['ensemble']['nom']);
        $this->assertSame('qux', $response['ensemble']['description']);
    }

    /**
     * @throws FilesystemException
     * @throws JsonException
     */
    public function testEnsembleAssertExceptionIfWrongID(): void
    {
        $this->login();
        $this->mustFailGraphQL('administration/ensembles/ensemble.gql', ['ensembleID' => 'foo']);
    }

    /**
     * @throws FilesystemException
     * @throws JsonException
     */
    public function testEnsemble(): void
    {
        $this->login();
        /** @var Ensemble $ensemble */
        $ensemble = $this->ensembleDao->findAll()->first();
        $this->mustSuccessGraphQL('administration/ensembles/ensemble.gql', [
            'ensembleID' => $ensemble->getId(),
        ]);
    }

    /**
     * @throws FilesystemException
     * @throws JsonException
     */
    public function testDeleteEnsemble(): void
    {
        /** @var Ensemble $ensemble */
        $ensemble = $this->ensembleDao->findAll()->first();
        /** @var Entite $entite */
        $entite = $this->entiteDao->findAll()->first();
        $this->assertNotEmpty($ensemble);
        $this->login();
        $this->mustSuccessGraphQL('administration/ensembles/saveEnsemble.gql', [
            'ensembleID' => $ensemble->getId(),
            'nom' => $ensemble->getNom(),
            'description' => $ensemble->getDescription(),
            'estActive' => false,
            'referents'   => [$entite->getId()],
        ]);
    }
}
