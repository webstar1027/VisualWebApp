<?php

declare(strict_types=1);

namespace App\Validators;

use App\Exceptions\ValidatorException;
use App\Model\IndiceTaux;
use Safe\Exceptions\StringsException;
use function Safe\sprintf;

final class IndiceTauxValidator implements ValidatorInterface
{
    /** @var IndiceTaux */
    private $indiceTaux;

    public function __construct(IndiceTaux $indiceTaux)
    {
        $this->indiceTaux = $indiceTaux;
    }

    /**
     * @throws ValidatorException
     * @throws StringsException
     */
    public function check(): void
    {
        $type = $this->indiceTaux->getType();
        $indexSurInflation = $this->indiceTaux->getIndexationSurInflation();
        $ecart = $this->indiceTaux->getEcart();
        switch ($type) {
            case IndiceTaux::TAUX_INFLATION_TYPE:
                Validator::shouldBeNull($indexSurInflation, sprintf('%s ne peut pas avoir un index sur inflation non null', $type));
                Validator::shouldBeNull($ecart, sprintf('%s ne peut pas avoir un écart non null', $type));
                break;
            case IndiceTaux::TAUX_LIVRET_A_TYPE_SUB_TAUX_INFLATION_TYPE:
                Validator::shouldBeNull($indexSurInflation, sprintf('%s ne peut pas avoir un index sur inflation non null', $type));
                Validator::shouldBeNull($ecart, sprintf('%s ne peut pas avoir un écart non null', $type));
                break;
            case IndiceTaux::TAUX_LIVRET_A_TYPE_SUB_TAUX_REMUNERATION_TRESORERIE_TYPE:
                Validator::shouldBeNull($indexSurInflation, sprintf('%s ne peut pas avoir un index sur inflation non null', $type));
                Validator::shouldBeNull($ecart, sprintf('%s ne peut pas avoir un écart non null', $type));
                break;
            case IndiceTaux::TAUX_LIVRET_A_TYPE_SUB_TAUX_VARIATION_IRL_TYPE:
                Validator::shouldBeNull($indexSurInflation, sprintf('%s ne peut pas avoir un index sur inflation non null', $type));
                Validator::shouldBeNull($ecart, sprintf('%s ne peut pas avoir un écart non null', $type));
                break;
            default:
                Validator::shouldNotBeNull($indexSurInflation, sprintf('%s ne peut pas avoir un index sur inflation non null', $type));
                Validator::shouldNotBeNull($ecart, sprintf('%s ne peut pas avoir un écart non null', $type));
                break;
        }
    }
}
