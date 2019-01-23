<?php

namespace App\Util\Twig\Extension;

use App\Model\RentPeriod;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class RentPeriodExtension
 * @package App\Util\Twig\Extension
 */
class RentPeriodExtension extends AbstractExtension
{
    /**
     * @return array
     */
    public function getFunctions(): array
    {
        return array(
            new TwigFunction('rentPeriod', array($this, 'rentPeriod')),
        );
    }

    public function rentPeriod($period)
    {
        return RentPeriod::getTypeNames()[$period];
    }
}