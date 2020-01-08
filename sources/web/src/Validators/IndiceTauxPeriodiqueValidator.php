<?php

declare(strict_types=1);

namespace App\Validators;

use App\Exceptions\ValidatorException;
use App\Model\IndiceTaux;
use App\Model\IndiceTauxPeriodique;
use Safe\Exceptions\StringsException;
use function Safe\sprintf;

final class IndiceTauxPeriodiqueValidator implements ValidatorInterface
{
    /** @var IndiceTauxPeriodique */
    private $indiceTauxPeriodique;

    public function __construct(IndiceTauxPeriodique $indiceTauxPeriodique)
    {
        $this->indiceTauxPeriodique = $indiceTauxPeriodique;
    }

    /**
     * @throws ValidatorException
     * @throws StringsException
     */
    public function check(): void
    {
        $indiceTaux = $this->indiceTauxPeriodique->getIndiceTaux();
        $type = $indiceTaux->getType();
        $valeur = $this->indiceTauxPeriodique->getValeur();
        $iteration = $this->indiceTauxPeriodique->getIteration();
        if ($iteration === 0) {
            switch ($type) {
                case IndiceTaux::TAUX_LIVRET_A_TYPE:
                    Validator::shouldNotBeNull($valeur, sprintf("%s ne peut pas avoir une valeur null pour l'itération %d", $type, $iteration));
                    break;
                default:
                    Validator::shouldBeNull($valeur, sprintf("%s ne peut pas avoir une valeur null pour l'itération %d", $type, $iteration));
                    break;
            }

            return;
        }
        Validator::shouldNotBeNull($valeur, sprintf("%s ne peut pas avoir une valeur null pour l'itération %d", $type, $iteration));
    }
}
