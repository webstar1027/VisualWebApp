<?php

declare(strict_types=1);

namespace App\Model\Factories;

use App\Dao\CcmiDao;
use App\Dao\CcmiPeriodiqueDao;
use App\Dao\SimulationDao;
use App\Exceptions\HTTPException;
use App\Model\Ccmi;
use App\Model\CcmiPeriodique;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException as SymfonyHttpException;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\TDBM\TDBMException;
use Throwable;
use function Safe\json_decode;

class CcmiFactory
{
    /** @var CcmiDao */
    private $ccmiDao;
    /** @var SimulationDao */
    private $simulationDao;
    /** @var CcmiPeriodiqueDao */
    private $ccmiPeriodiqueDao;

    public function __construct(
        CcmiDao $ccmiDao,
        SimulationDao $simulationDao,
        CcmiPeriodiqueDao $ccmiPeriodiqueDao
    ) {
        $this->ccmiDao = $ccmiDao;
        $this->simulationDao = $simulationDao;
        $this->ccmiPeriodiqueDao = $ccmiPeriodiqueDao;
    }

    /**
     * @throws HTTPException
     * @throws TDBMException
     *
     * @Factory()
     */
    public function createCcmi(
        ?string $uuid,
        string $simulationId,
        int $numero,
        string $nomOperation,
        ?float $prixVente,
        ?float $tauxMargeBrute,
        ?float $portageFondsPropres,
        ?float $coutsInternesStockes,
        ?string $periodique
    ): Ccmi {
        $simulation = $this->simulationDao->getById($simulationId);

        if (empty($simulation)) {
            throw HTTPException::notFound("La simulation n'existe pas");
        }

        if ($uuid !== null) {
            // Updating the existing one
            try {
                $ccmi = $this->ccmiDao->getById($uuid);
                $ccmi->setSimulation($simulation);
                $ccmi->setNumero($numero);
                $ccmi->setNomOperation($nomOperation);
                if (empty($ccmi)) {
                    throw HTTPException::notFound("Ce CCMI n'existe pas");
                }
            } catch (Throwable $e) {
                throw new SymfonyHttpException(Response::HTTP_NOT_FOUND, "Ce CCMI n'existe pas", $e);
            }
        } else {
            if ($this->ccmiDao->findOneByNumero($numero) !== null) {
                throw HTTPException::badRequest('Ce numéro de CCMI est déjà utilisé');
            }

            // Creating a new object
            $ccmi = new Ccmi(
                $simulation,
                $numero,
                $nomOperation
            );
        }
        $ccmi->setCoutsInternesStockes($coutsInternesStockes);
        $ccmi->setTauxMargeBrute($tauxMargeBrute);
        $ccmi->setPrixVente($prixVente);
        $ccmi->setPortageFondsPropres($portageFondsPropres);

        if ($periodique !== null) {
            $this->handleCcmiPeriodique($periodique, $ccmi, $uuid);
        }

        return $ccmi;
    }

    private function handleCcmiPeriodique(string $periodique, Ccmi $ccmi, ?string $edit): void
    {
        $periodique = json_decode($periodique);

        foreach ($periodique->periodique as $index => $value) {
            $iteration = $index + 1;

            /** @var Ccmi $ccmiPeriodique */
            if ($edit !== null) {
                $ccmiPeriodique = $ccmi->getCcmiPeriodique()->offsetGet($index);
            } else {
                $ccmiPeriodique = new CcmiPeriodique($ccmi, $iteration);
            }

            $ccmiPeriodique->setNombreMaisonsLivrees($value ? (float) $value : null);
            $this->ccmiPeriodiqueDao->save($ccmiPeriodique);
        }
    }
}
