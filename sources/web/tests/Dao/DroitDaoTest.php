<?php

declare(strict_types=1);

namespace App\Tests\Controller\GraphQL;

use App\Model\Droit;
use Safe\Exceptions\FilesystemException;
use Safe\Exceptions\JsonException;

final class DroitDaoTest extends AbstractGraphQLControllerTestCase
{
    /**
     * @throws FilesystemException
     * @throws JsonException
     */
    public function testDroitDaoFilter(): void
    {
        /** @var Droit[] $droits */
        $droits = $this->droitDao->findByFilters('create_', null, 'Utilisateurs', null, null)->toArray();
        $this->assertNotNull($droits);
        $this->assertSame('Créer un utilisateur', $droits[0]->getLibelle());
    }
}
