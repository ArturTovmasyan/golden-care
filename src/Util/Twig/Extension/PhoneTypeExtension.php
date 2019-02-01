<?php

namespace App\Util\Twig\Extension;

use App\Model\Phone;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class PhoneTypeExtension
 * @package App\Util\Twig\Extension
 */
class PhoneTypeExtension extends AbstractExtension
{
    /**
     * @return array
     */
    public function getFunctions(): array
    {
        return array(
            new TwigFunction('phoneType', array($this, 'phoneType')),
        );
    }

    public function phoneType($phoneType)
    {
        return Phone::getTypeNames()[$phoneType];
    }
}