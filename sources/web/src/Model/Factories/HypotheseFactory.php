<?php

declare(strict_types=1);

namespace App\Model\Factories;

use App\Dao\HypotheseDao;
use App\Dao\SimulationDao;
use App\Exceptions\HTTPException;
use App\Model\Hypothese;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException as SymfonyHttpException;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\TDBM\TDBMException;
use Throwable;

class HypotheseFactory
{
    /** @var SimulationDao */
    private $simulationDao;

    /** @var HypotheseDao */
    private $hypotheseDao;

    public function __construct(SimulationDao $simulationDao, HypotheseDao $hypotheseDao)
    {
        $this->simulationDao = $simulationDao;
        $this->hypotheseDao = $hypotheseDao;
    }

    /**
     * @throws HTTPException
     * @throws SymfonyHttpException
     * @throws TDBMException
     *
     * @Factory()
     */
    public function createHypotheses(
        string $simulationId,
        ?string $id,
        ?bool $mobilisation,
        ?float $maintenance,
        ?float $maintenanceDiffere,
        ?float $grosEntretien,
        ?float $grosEntretienDiffere,
        ?float $provisionGros,
        ?float $provisionGrosDiffere,
        ?float $tauxVacance,
        ?float $tauxVacanceGarages,
        ?float $tauxVacanceCommerces,
        ?float $applicationFrais,
        ?float $fraisPersonnel,
        ?float $fraisGestion,
        ?float $seuilDeclenchement,
        ?float $tauxDirecte,
        ?float $tauxVefa,
        ?float $tauxRehabilitation
    ): Hypothese {
        $simulation = $this->simulationDao->getById($simulationId);

        if (empty($simulation)) {
            throw HTTPException::notFound("La simulation n'existe pas");
        }

        if ($id !== null) {
            try {
                $hypothese = $this->hypotheseDao->getById($id);
                if (empty($hypothese)) {
                    throw HTTPException::notFound("Ce Hypothèse n'existe pas");
                }
            } catch (Throwable $e) {
                throw new SymfonyHttpException(Response::HTTP_NOT_FOUND, "Ce Hypothèse n'existe pas", $e);
            }
        } else {
            $hypothese = new Hypothese($simulation);
        }

        $hypothese->setMobilisation($mobilisation ??false);
        $hypothese->setMaintenance($maintenance ??0.00);
        $hypothese->setMaintenanceDiffere($maintenanceDiffere ??0.00);
        $hypothese->setGrosEntretien($grosEntretien ??0.00);
        $hypothese->setGrosEntretienDiffere($grosEntretienDiffere ??0.00);
        $hypothese->setProvisionGros($provisionGros ??0.00);
        $hypothese->setProvisionGrosDiffere($provisionGrosDiffere ??0.00);
        $hypothese->setTauxVacance($tauxVacance ??0.00);
        $hypothese->setTauxVacanceGarages($tauxVacanceGarages ??0.00);
        $hypothese->setTauxVacanceCommerces($tauxVacanceCommerces ??0.00);
        $hypothese->setApplicationFrais($applicationFrais ??1);
        $hypothese->setFraisPersonnel($fraisPersonnel ??0.00);
        $hypothese->setFraisGestion($fraisGestion ??0.00);
        $hypothese->setSeuilDeclenchement($seuilDeclenchement ??0.00);
        $hypothese->setTauxDirecte($tauxDirecte ??0.00);
        $hypothese->setTauxVefa($tauxVefa ??0.00);
        $hypothese->setTauxRehabilitation($tauxRehabilitation ??0.00);

        return $hypothese;
    }
}
