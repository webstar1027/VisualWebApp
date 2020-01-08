<?php

declare(strict_types=1);

namespace App\Model\Factories;

use App\Dao\PortageTresorerieDao;
use App\Dao\PortageTresoreriePeriodiqueDao;
use App\Model\PortageTresorerie;
use App\Model\PortageTresoreriePeriodique;
use Safe\Exceptions\JsonException;
use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\TDBM\TDBMException;
use function floatval;
use function Safe\json_decode;

class PortageTresorerieFactory
{
    /** @var PortageTresorerieDao */
    private $portageTresorerieDao;

    /** @var PortageTresoreriePeriodiqueDao */
    private $portageTresoreriePeriodiqueDao;

    public function __construct(
        PortageTresoreriePeriodiqueDao $portageTresoreriePeriodiqueDao,
        PortageTresorerieDao $portageTresorerieDao
    ) {
        $this->portageTresoreriePeriodiqueDao = $portageTresoreriePeriodiqueDao;
        $this->portageTresorerieDao = $portageTresorerieDao;
    }

    /**
     * @throws JsonException
     * @throws TDBMException
     *
     * @Factory()
     */
    public function createPortageTresorerie(
        string $id,
        string $periodique
    ): PortageTresorerie {
        $portageTresorerie = $this->portageTresorerieDao->getById($id);
        $this->createPortageTresoreriePeriodique($periodique, $portageTresorerie);

        return $portageTresorerie;
    }

    /**
     * @throws JsonException
     */
    private function createPortageTresoreriePeriodique(?string $periodique, PortageTresorerie $portageTresorerie): void
    {
        if ($periodique) {
            $periodique = json_decode($periodique);
        }

        foreach ($periodique->periodique as $key => $value) {
            /** @var PortageTresoreriePeriodique $portageTresoreriePerioque */
            $portageTresoreriePerioque = $portageTresorerie->getPortageTresoreriePeriodique()->offsetGet($key);
            $portageTresoreriePerioque->setValeur($value->valeur ? floatval($value->valeur) : null);
            $this->portageTresoreriePeriodiqueDao->save($portageTresoreriePerioque);
        }
    }
}
